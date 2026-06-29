<?php

namespace App\Modules\Estate\Services;

use App\Modules\Estate\Config\Estate;

/**
 * All cryptographic and session operations for owner authentication.
 *
 * Design principles (Faz 2 spec):
 * - Raw tokens are NEVER stored in DB; only SHA-256 hashes.
 * - Invite token: 32 random bytes → hex → URL-safe, 60-90 day TTL, reusable until revoked.
 * - Session token: 32 random bytes → hex, stored as hash in owner_sessions, HttpOnly cookie.
 * - Magic link token: 32 random bytes → hex, 15-min TTL, single-use (used_at stamp).
 * - OTP (step-up): 6-digit numeric, 10-min TTL, stored as bcrypt hash in session.
 * - All security events → owner_security_events (append-only).
 */
class OwnerAuthService
{
    private const COOKIE_NAME     = 'estate_owner_token';
    private const COOKIE_MAX_AGE  = 60 * 60 * 24 * 90;   // 90 days
    private const INVITE_TTL_DAYS = 60;
    private const MAGIC_TTL_MIN   = 15;
    private const STEPUP_VALID_S  = 900;                  // 15 min step-up window
    private const OTP_VALID_S     = 600;                  // 10 min OTP

    private \CodeIgniter\Database\BaseConnection $db;
    private Estate $config;

    public function __construct()
    {
        $this->db     = \Config\Database::connect();
        $this->config = config(Estate::class);
    }

    // ─── Invite token ────────────────────────────────────────────────────────

    /**
     * Generate a new invite token for an owner, store hash, return raw URL.
     * Revokes any existing active invite first (one active invite per owner).
     */
    public function generateInviteToken(int $ownerId, int $ttlDays = self::INVITE_TTL_DAYS): string
    {
        $this->revokeAllInvites($ownerId);

        $raw       = $this->secureToken();
        $hash      = $this->hashToken($raw);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$ttlDays} days"));

        $this->db->table('owner_invites')->insert([
            'owner_id'   => $ownerId,
            'type'       => 'invite',
            'token_hash' => $hash,
            'expires_at' => $expiresAt,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logSecurityEvent('invite_generated', $ownerId, ['ttl_days' => $ttlDays]);

        return $raw;
    }

    /**
     * Validate a raw invite token. Returns owner row or null.
     * Does NOT consume the token (invite links are reusable until revoked/expired).
     */
    public function validateInviteToken(string $raw): ?array
    {
        $hash = $this->hashToken($raw);

        $row = $this->db->table('owner_invites')
            ->where('token_hash', $hash)
            ->where('type', 'invite')
            ->where('status', 'active')
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (! $row) {
            return null;
        }

        return $this->db->table('owners')->where('id', $row['owner_id'])->get()->getRowArray();
    }

    public function revokeAllInvites(int $ownerId): void
    {
        $this->db->table('owner_invites')
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);
    }

    // ─── Owner session ────────────────────────────────────────────────────────

    /**
     * Create a long-lived session for an owner.
     * Sets HttpOnly/Secure/SameSite=Lax cookie; stores hash in owner_sessions.
     *
     * Returns the raw token (useful for tests; caller can ignore).
     */
    public function createSession(int $ownerId): string
    {
        $raw       = $this->secureToken();
        $hash      = $this->hashToken($raw);
        $expiresAt = date('Y-m-d H:i:s', time() + self::COOKIE_MAX_AGE);

        $this->db->table('owner_sessions')->insert([
            'owner_id'     => $ownerId,
            'session_hash' => $hash,
            'user_agent'   => $this->userAgent(),
            'ip'           => $this->clientIp(),
            'expires_at'   => $expiresAt,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $isSecure = ENVIRONMENT !== 'development';

        setcookie(self::COOKIE_NAME, $raw, [
            'expires'  => time() + self::COOKIE_MAX_AGE,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        return $raw;
    }

    /**
     * Validate current request's session cookie.
     * Returns owner_id or null. Prunes expired sessions lazily.
     */
    public function validateSession(): ?int
    {
        $raw = $_COOKIE[self::COOKIE_NAME] ?? null;
        if (! $raw) {
            return null;
        }

        $hash = $this->hashToken($raw);

        $row = $this->db->table('owner_sessions')
            ->where('session_hash', $hash)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (! $row) {
            return null;
        }

        // Verify owner is still active (not disabled)
        $owner = $this->db->table('owners')->where('id', $row['owner_id'])->get()->getRowArray();
        if (! $owner || $owner['status'] === 'disabled') {
            return null;
        }

        return (int) $row['owner_id'];
    }

    /** Clear the session cookie and invalidate the DB row. */
    public function destroySession(): void
    {
        $raw = $_COOKIE[self::COOKIE_NAME] ?? null;
        if ($raw) {
            $hash = $this->hashToken($raw);
            $this->db->table('owner_sessions')->where('session_hash', $hash)->delete();
        }

        setcookie(self::COOKIE_NAME, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /** Rotate session: destroy old, create new. Call on privilege change. */
    public function rotateSession(int $ownerId): void
    {
        $this->destroySession();
        $this->createSession($ownerId);
    }

    // ─── Magic link ───────────────────────────────────────────────────────────

    /**
     * Generate a single-use 15-minute magic login link token.
     * Returns raw token; caller sends it as /owner/magiclink/{raw}.
     */
    public function generateMagicLink(int $ownerId): string
    {
        // Invalidate any existing unused magic links for this owner
        $this->db->table('magic_login_tokens')
            ->where('owner_id', $ownerId)
            ->where('used_at', null)
            ->update(['used_at' => date('Y-m-d H:i:s')]);

        $raw       = $this->secureToken();
        $hash      = $this->hashToken($raw);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::MAGIC_TTL_MIN . ' minutes'));

        $this->db->table('magic_login_tokens')->insert([
            'owner_id'   => $ownerId,
            'token_hash' => $hash,
            'expires_at' => $expiresAt,
            'used_at'    => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logSecurityEvent('magic_link_generated', $ownerId);

        return $raw;
    }

    /**
     * Validate and consume a magic link token. Returns owner_id or null.
     * Token is consumed (used_at set) on success — single use.
     */
    public function validateMagicLink(string $raw): ?int
    {
        $hash = $this->hashToken($raw);

        $row = $this->db->table('magic_login_tokens')
            ->where('token_hash', $hash)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->where('used_at', null)
            ->get()->getRowArray();

        if (! $row) {
            return null;
        }

        // Consume immediately (single-use)
        $this->db->table('magic_login_tokens')
            ->where('id', $row['id'])
            ->update(['used_at' => date('Y-m-d H:i:s')]);

        $this->logSecurityEvent('magic_link_used', (int) $row['owner_id']);

        return (int) $row['owner_id'];
    }

    // ─── Step-up authentication ───────────────────────────────────────────────

    /**
     * Generate a 6-digit OTP, store its bcrypt hash + expiry in CI4 session,
     * and return the plaintext OTP to send via email.
     */
    public function generateOtp(int $ownerId): string
    {
        $otp  = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash = password_hash($otp, PASSWORD_BCRYPT);

        session()->set('estate_stepup_otp', [
            'owner_id'   => $ownerId,
            'hash'       => $hash,
            'expires_at' => time() + self::OTP_VALID_S,
        ]);

        return $otp;
    }

    /**
     * Verify OTP against the session-stored hash.
     * On success: marks step-up verified in session + clears OTP.
     */
    public function verifyOtp(int $ownerId, string $input): bool
    {
        $stored = session()->get('estate_stepup_otp');

        if (! $stored
            || (int) $stored['owner_id'] !== $ownerId
            || $stored['expires_at'] < time()
        ) {
            return false;
        }

        if (! password_verify($input, $stored['hash'])) {
            $this->logSecurityEvent('stepup_otp_failed', $ownerId);
            return false;
        }

        session()->remove('estate_stepup_otp');
        session()->set('estate_stepup_verified_at', time());
        $this->logSecurityEvent('stepup_verified', $ownerId);

        return true;
    }

    /**
     * Verify password for step-up (owners who have set a password).
     */
    public function verifyPassword(int $ownerId, string $input): bool
    {
        $owner = $this->db->table('owners')->where('id', $ownerId)->get()->getRowArray();

        if (! $owner || empty($owner['password_hash'])) {
            return false;
        }

        if (! password_verify($input, $owner['password_hash'])) {
            $this->logSecurityEvent('stepup_password_failed', $ownerId);
            return false;
        }

        session()->set('estate_stepup_verified_at', time());
        $this->logSecurityEvent('stepup_verified', $ownerId);

        return true;
    }

    /** Returns true if a step-up was completed within the last 15 minutes. */
    public function isStepUpValid(): bool
    {
        $at = session()->get('estate_stepup_verified_at');
        return $at && (time() - (int) $at) < self::STEPUP_VALID_S;
    }

    // ─── Upgrade owner account ────────────────────────────────────────────────

    /**
     * Upgrade owner from invite-only to email/password login.
     * Revokes all active invite tokens, updates owner record, rotates session.
     */
    public function upgradeOwner(int $ownerId, array $data): void
    {
        $update = ['status' => 'active'];

        if (! empty($data['email'])) {
            $update['email']        = $data['email'];
            $update['login_method'] = 'magic_link';
        }

        if (! empty($data['password'])) {
            $update['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            $update['login_method']  = 'password';
        }

        if (! empty($data['phone'])) {
            $update['phone'] = $data['phone'];
        }

        $this->db->table('owners')->where('id', $ownerId)->update($update);
        $this->revokeAllInvites($ownerId);
        $this->rotateSession($ownerId);

        $this->logSecurityEvent('owner_upgraded', $ownerId, ['method' => $update['login_method'] ?? '?']);
    }

    // ─── Security event log ───────────────────────────────────────────────────

    public function logSecurityEvent(string $type, ?int $ownerId = null, array $meta = []): void
    {
        $this->db->table('owner_security_events')->insert([
            'owner_id'   => $ownerId,
            'type'       => $type,
            'ip'         => $this->clientIp(),
            'user_agent' => $this->userAgent(),
            'meta'       => $meta ? json_encode($meta) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** Cryptographically secure URL-safe token (64 hex chars = 256 bits). */
    private function secureToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function hashToken(string $raw): string
    {
        return hash('sha256', $raw);
    }

    private function clientIp(): string
    {
        return service('request')->getIPAddress();
    }

    private function userAgent(): string
    {
        return (string) service('request')->getUserAgent();
    }
}

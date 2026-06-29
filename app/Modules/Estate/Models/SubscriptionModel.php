<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table         = 'subscriptions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'seeker_id', 'provider_customer_id', 'plan', 'status',
        'current_period_end', 'stripe_subscription_id', 'stripe_session_id',
        'stripe_customer_id',
    ];
    protected $useTimestamps = true;

    public function findActiveForSeeker(int $seekerId): ?array
    {
        return $this->where('seeker_id', $seekerId)
                    ->whereIn('status', ['active', 'trial'])
                    ->first();
    }

    public function isActiveForSeeker(int $seekerId): bool
    {
        return (bool) $this->findActiveForSeeker($seekerId);
    }

    /** Create a trial subscription (BILLING_ENABLED=false path). */
    public function createTrial(int $seekerId): void
    {
        $existing = $this->where('seeker_id', $seekerId)->first();
        $now      = date('Y-m-d H:i:s');

        if ($existing) {
            if (! in_array($existing['status'], ['active', 'trial'], true)) {
                $this->update($existing['id'], [
                    'status'             => 'trial',
                    'current_period_end' => date('Y-m-d H:i:s', strtotime('+30 days')),
                ]);
            }
            return;
        }

        $this->insert([
            'seeker_id'          => $seekerId,
            'plan'               => 'trial',
            'status'             => 'trial',
            'current_period_end' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);
    }

    public function findBySessionId(string $sessionId): ?array
    {
        return $this->where('stripe_session_id', $sessionId)->first();
    }
}

<?php

namespace App\Modules\Estate\Controllers\Stripe;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Services\StripeService;
use App\Modules\Estate\Models\SubscriptionModel;
use App\Modules\Estate\Models\OwnerListingChargeModel;
use App\Modules\Estate\Models\AuditLogModel;
use Stripe\Exception\SignatureVerificationException;

/**
 * POST /webhooks/stripe
 *
 * Receives Stripe events, verifies the Stripe-Signature header, and
 * updates the local DB. No long-running processes; returns 200 fast.
 *
 * Idempotency: each handled event checks for an existing stripe_session_id /
 * stripe_subscription_id before writing, so duplicate deliveries are safe.
 */
class WebhookController extends BaseWebController
{
    public function handle(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rawBody   = $this->request->getBody();
        $sigHeader = $this->request->getHeaderLine('Stripe-Signature');

        // ── 1. Signature verification ────────────────────────────────────────
        try {
            $stripe = new StripeService();
            $event  = $stripe->constructWebhookEvent($rawBody, $sigHeader);
        } catch (SignatureVerificationException $e) {
            log_message('warning', '[Stripe webhook] Invalid signature: ' . $e->getMessage());
            return $this->response->setStatusCode(400)->setBody('Invalid signature');
        } catch (\Throwable $e) {
            log_message('error', '[Stripe webhook] Parse error: ' . $e->getMessage());
            return $this->response->setStatusCode(400)->setBody('Bad payload');
        }

        // ── 2. Event routing ─────────────────────────────────────────────────
        try {
            match ($event->type) {
                'checkout.session.completed'        => $this->handleCheckoutComplete($event->data->object),
                'customer.subscription.updated'     => $this->handleSubscriptionUpdate($event->data->object),
                'customer.subscription.deleted'     => $this->handleSubscriptionDelete($event->data->object),
                'invoice.payment_succeeded'         => $this->handleInvoicePaid($event->data->object),
                default                             => null,  // unknown events → ignore, return 200
            };
        } catch (\Throwable $e) {
            // Log but still return 200 to prevent Stripe from retrying indefinitely
            log_message('error', '[Stripe webhook] Handler error for ' . $event->type . ': ' . $e->getMessage());
        }

        return $this->response->setStatusCode(200)->setBody('ok');
    }

    // ─── Handlers ────────────────────────────────────────────────────────────

    private function handleCheckoutComplete(object $session): void
    {
        $type = $session->metadata->type ?? '';

        if ($type === 'seeker_subscription') {
            $this->handleSeekerSubscriptionCheckout($session);
        } elseif ($type === 'owner_listing_charge') {
            $this->handleOwnerListingChargeCheckout($session);
        }
    }

    private function handleSeekerSubscriptionCheckout(object $session): void
    {
        $db            = db_connect();
        $sessionId     = $session->id;
        $seekerEmail   = $session->metadata->seeker_email ?? $session->customer_email ?? '';
        $customerId    = $session->customer;
        $subscriptionId= $session->subscription;

        if (! $seekerEmail) {
            log_message('warning', "[Stripe webhook] seeker_subscription missing email for session {$sessionId}");
            return;
        }

        // Idempotency: skip if already processed
        $existing = $db->table('subscriptions')->where('stripe_session_id', $sessionId)->get()->getRowArray();
        if ($existing) {
            return;
        }

        // Find or create seeker
        $seeker = $db->table('seekers')->where('email', $seekerEmail)->get()->getRowArray();
        if (! $seeker) {
            $seekerId = $db->table('seekers')->insert([
                'email'               => $seekerEmail,
                'subscription_status' => 'active',
                'login_method'        => 'magic_link',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);
        } else {
            $seekerId = $seeker['id'];
        }

        // Retrieve subscription period from Stripe object (may be in session->subscription)
        $periodEnd = null;
        if ($subscriptionId && is_string($subscriptionId)) {
            try {
                $sub       = \Stripe\Subscription::retrieve($subscriptionId);
                $periodEnd = date('Y-m-d H:i:s', $sub->current_period_end);
            } catch (\Throwable) {
                // non-fatal
            }
        }

        $now = date('Y-m-d H:i:s');

        // Upsert subscription record
        $existing = $db->table('subscriptions')->where('seeker_id', $seekerId)->get()->getRowArray();
        if ($existing) {
            $db->table('subscriptions')->where('id', $existing['id'])->update([
                'status'                => 'active',
                'provider_customer_id'  => $customerId,
                'stripe_subscription_id'=> $subscriptionId,
                'stripe_session_id'     => $sessionId,
                'stripe_customer_id'    => $customerId,
                'current_period_end'    => $periodEnd,
                'updated_at'            => $now,
            ]);
        } else {
            $db->table('subscriptions')->insert([
                'seeker_id'             => $seekerId,
                'plan'                  => 'plus',
                'status'                => 'active',
                'provider_customer_id'  => $customerId,
                'stripe_subscription_id'=> $subscriptionId,
                'stripe_session_id'     => $sessionId,
                'stripe_customer_id'    => $customerId,
                'current_period_end'    => $periodEnd,
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);
        }

        // Update seeker.subscription_status
        $db->table('seekers')->where('id', $seekerId)->update([
            'subscription_status' => 'active',
            'updated_at'          => $now,
        ]);

        (new AuditLogModel())->record('seeker.subscribed', "seekers/{$seekerId}", [
            'stripe_session_id' => $sessionId,
        ], 'stripe');
    }

    private function handleOwnerListingChargeCheckout(object $session): void
    {
        $db        = db_connect();
        $sessionId = $session->id;
        $ownerId   = (int) ($session->metadata->owner_id ?? 0);
        $listingId = (int) ($session->metadata->listing_id ?? 0);

        if (! $ownerId || ! $listingId) {
            log_message('warning', "[Stripe webhook] owner_listing_charge missing owner/listing IDs for session {$sessionId}");
            return;
        }

        // Idempotency
        $existing = $db->table('owner_listing_charges')->where('stripe_session_id', $sessionId)->get()->getRowArray();
        if ($existing) {
            if ($existing['status'] !== 'paid') {
                $db->table('owner_listing_charges')->where('id', $existing['id'])->update([
                    'status'              => 'paid',
                    'provider_payment_id' => $session->payment_intent ?? null,
                ]);
            }
            return;
        }

        $now = date('Y-m-d H:i:s');
        $db->table('owner_listing_charges')->insert([
            'owner_id'            => $ownerId,
            'listing_id'          => $listingId,
            'amount_cents'        => $session->amount_total ?? config(\App\Modules\Estate\Config\Estate::class)->ownerExtraListingCents,
            'status'              => 'paid',
            'provider_payment_id' => $session->payment_intent ?? null,
            'stripe_session_id'   => $sessionId,
            'created_at'          => $now,
        ]);

        (new AuditLogModel())->record('owner.listing_charge_paid', "listings/{$listingId}", [
            'stripe_session_id' => $sessionId,
            'owner_id'          => $ownerId,
        ], 'stripe');
    }

    private function handleSubscriptionUpdate(object $subscription): void
    {
        $db             = db_connect();
        $subscriptionId = $subscription->id;
        $status         = $subscription->status; // active|past_due|canceled|unpaid

        $normalised = match ($status) {
            'active', 'trialing' => 'active',
            'canceled', 'unpaid' => 'canceled',
            default              => 'past_due',
        };

        $now = date('Y-m-d H:i:s');

        $record = $db->table('subscriptions')
            ->where('stripe_subscription_id', $subscriptionId)
            ->get()->getRowArray();

        if (! $record) {
            return;
        }

        $db->table('subscriptions')->where('id', $record['id'])->update([
            'status'             => $normalised,
            'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end),
            'updated_at'         => $now,
        ]);

        $db->table('seekers')->where('id', $record['seeker_id'])->update([
            'subscription_status' => $normalised,
            'updated_at'          => $now,
        ]);
    }

    private function handleSubscriptionDelete(object $subscription): void
    {
        $db             = db_connect();
        $subscriptionId = $subscription->id;
        $now            = date('Y-m-d H:i:s');

        $record = $db->table('subscriptions')
            ->where('stripe_subscription_id', $subscriptionId)
            ->get()->getRowArray();

        if (! $record) {
            return;
        }

        $db->table('subscriptions')->where('id', $record['id'])->update([
            'status'     => 'canceled',
            'updated_at' => $now,
        ]);

        $db->table('seekers')->where('id', $record['seeker_id'])->update([
            'subscription_status' => 'free',
            'updated_at'          => $now,
        ]);

        (new AuditLogModel())->record('seeker.subscription_canceled', "seekers/{$record['seeker_id']}", [
            'stripe_subscription_id' => $subscriptionId,
        ], 'stripe');
    }

    private function handleInvoicePaid(object $invoice): void
    {
        // Extend subscription period on renewal — subscription.updated handles this too,
        // but we log it for auditing completeness.
        $subscriptionId = $invoice->subscription ?? null;
        if (! $subscriptionId) {
            return;
        }

        (new AuditLogModel())->record('seeker.invoice_paid', "stripe/invoices/{$invoice->id}", [
            'stripe_subscription_id' => $subscriptionId,
            'amount_paid'            => $invoice->amount_paid,
        ], 'stripe');
    }
}

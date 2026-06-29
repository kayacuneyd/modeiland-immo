<?php

namespace App\Modules\Estate\Services;

use App\Modules\Estate\Config\Estate;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

/**
 * Thin wrapper around the Stripe PHP SDK.
 * All Stripe API keys are read from Estate config (never hardcoded).
 *
 * Requires: composer require stripe/stripe-php
 */
class StripeService
{
    private Estate $config;

    public function __construct()
    {
        $this->config = config(Estate::class);
        Stripe::setApiKey($this->config->stripeSecretKey);
        Stripe::setAppInfo('modeiland', '3.0', 'https://modeiland.de');
    }

    /**
     * Create a Stripe Checkout Session for a seeker subscription (5 €/month).
     * On success Stripe redirects to $successUrl with ?session_id={CHECKOUT_SESSION_ID}.
     */
    public function createSeekerCheckout(
        string $seekerEmail,
        string $successUrl,
        string $cancelUrl
    ): StripeSession {
        return StripeSession::create([
            'mode'                => 'subscription',
            'customer_email'      => $seekerEmail,
            'line_items'          => [[
                'price'    => $this->config->stripeSeekerPriceId,
                'quantity' => 1,
            ]],
            'success_url'         => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'          => $cancelUrl,
            'metadata'            => [
                'type'         => 'seeker_subscription',
                'seeker_email' => $seekerEmail,
            ],
            'allow_promotion_codes' => true,
            'tax_id_collection'   => ['enabled' => true],  // EU VAT
        ]);
    }

    /**
     * Create a one-off Checkout Session for owner extra listing (~20 €).
     * Uses dynamic price (amount_cents from config) to avoid creating a Stripe Price object.
     */
    public function createOwnerListingCheckout(
        int    $ownerId,
        int    $listingId,
        string $ownerEmail,
        string $successUrl,
        string $cancelUrl
    ): StripeSession {
        $amountCents = config(Estate::class)->ownerExtraListingCents;

        return StripeSession::create([
            'mode'           => 'payment',
            'customer_email' => $ownerEmail ?: null,
            'line_items'     => [[
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => $amountCents,
                    'product_data' => [
                        'name'        => 'modeiland — Zusätzliches Inserat',
                        'description' => "Einmalige Gebühr für Inserat #{$listingId}",
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url'    => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'     => $cancelUrl,
            'metadata'       => [
                'type'       => 'owner_listing_charge',
                'owner_id'   => (string) $ownerId,
                'listing_id' => (string) $listingId,
            ],
        ]);
    }

    /**
     * Redirect owner/seeker to Stripe Customer Portal for subscription management.
     */
    public function createPortalSession(string $customerId, string $returnUrl): PortalSession
    {
        return PortalSession::create([
            'customer'   => $customerId,
            'return_url' => $returnUrl,
        ]);
    }

    /**
     * Verify Stripe-Signature header and return the parsed event.
     * Throws SignatureVerificationException on tampered payload.
     */
    public function constructWebhookEvent(string $rawBody, string $sigHeader): \Stripe\Event
    {
        return Webhook::constructEvent($rawBody, $sigHeader, $this->config->stripeWebhookSecret);
    }

    /**
     * Retrieve a Checkout Session by ID (used on success redirect to get customer email).
     */
    public function retrieveSession(string $sessionId): StripeSession
    {
        return StripeSession::retrieve([
            'id'     => $sessionId,
            'expand' => ['subscription', 'customer'],
        ]);
    }
}

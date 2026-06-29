<?php

namespace App\Modules\Estate\Config;

use CodeIgniter\Config\BaseConfig;

class Estate extends BaseConfig
{
    // Feature flags
    public bool $billingEnabled = false;

    // AI provider: 'openai' | 'anthropic'
    public string $aiProvider = 'openai';

    public string $aiApiKey  = '';
    public string $aiModel   = 'gpt-4o-mini';

    // Pricing (cents) — configurable, never hardcoded elsewhere
    public int $seekerPriceCents          = 500;   // 5 €/month
    public int $ownerExtraListingCents    = 2000;  // 20 € one-off per extra listing

    // Consent version — bump when consent text changes
    public string $consentVersion = '1.0';

    // Invite token TTL in days (Faz 2 will enforce on auth; stored for reference)
    public int $inviteTokenTtlDays = 60;

    // Owner session TTL in days
    public int $ownerSessionTtlDays = 90;

    // Message polling interval hint for JS (seconds)
    public int $messagePollingIntervalSeconds = 30;

    // Stripe (Faz 3)
    public string $stripeSecretKey      = '';
    public string $stripePublishableKey = '';
    public string $stripeWebhookSecret  = '';
    public string $stripeSeekerPriceId  = '';  // recurring price ID from Stripe dashboard

    // Cloudflare Images (Faz 4)
    public string $cloudflareAccountId      = '';
    public string $cloudflareImagesToken    = '';
    public string $cloudflareImagesDelivery = '';  // e.g. https://imagedelivery.net/{account_hash}

    public function __construct()
    {
        parent::__construct();

        $this->billingEnabled = (bool) env('BILLING_ENABLED', false);
        $this->aiProvider     = env('AI_PROVIDER', 'openai');
        $this->aiApiKey       = env('AI_API_KEY', '');
        $this->aiModel        = env('AI_MODEL', 'gpt-4o-mini');

        $seekerPrice = (int) env('SEEKER_PRICE_CENTS', 0);
        if ($seekerPrice > 0) {
            $this->seekerPriceCents = $seekerPrice;
        }

        $ownerExtra = (int) env('OWNER_EXTRA_LISTING_CENTS', 0);
        if ($ownerExtra > 0) {
            $this->ownerExtraListingCents = $ownerExtra;
        }

        $this->stripeSecretKey      = env('STRIPE_SECRET_KEY', '');
        $this->stripePublishableKey = env('STRIPE_PUBLISHABLE_KEY', '');
        $this->stripeWebhookSecret  = env('STRIPE_WEBHOOK_SECRET', '');
        $this->stripeSeekerPriceId  = env('STRIPE_SEEKER_PRICE_ID', '');

        $this->cloudflareAccountId      = env('CLOUDFLARE_ACCOUNT_ID', '');
        $this->cloudflareImagesToken    = env('CLOUDFLARE_IMAGES_TOKEN', '');
        $this->cloudflareImagesDelivery = env('CLOUDFLARE_IMAGES_DELIVERY_URL', '');
    }
}

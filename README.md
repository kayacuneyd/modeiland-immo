# modeiland — Maklerfrei Immobilien-Plattform

KI-gestützte Plattform für private, maklerfreie Immobilienanbieter (Deutschland).
Gebaut auf CekirdekCMS (CodeIgniter 4 + SQLite).

> **Rechtlicher Hinweis:** Vor Aktivierung des Zahlungsbetriebs (BILLING_ENABLED=true)
> unbedingt rechtliche Prüfung durch einen deutschen Anwalt (§ 34c GewO, Maklerrecht, DSGVO).
> Diese Codebasis stellt keine Rechtsberatung dar.

---

## Faz 1 — MVP Core (implementiert)

- Admin Import-Pipeline: Owner-Lead anlegen → Outreach-Vorlage → KI-Import → Inserat
- Owner Einwilligungs-Flow: Vorschau + Consent-Popup + Freigabe (6 Checkboxen, 3 Pflicht)
- Seeker: Suche + Filter + Detailseite + Paywall-Punkt + Nachricht senden
- Owner Panel: Nachrichteneingang + HTTP-Polling alle 30 s (kein WebSocket)
- Consent-Log: append-only, DSGVO-konform (consent_version, accepted_at, ip, ua)
- Rechtsskelett: Impressum, Datenschutz, AGB (Platzhalter — bitte vor Live-Betrieb ersetzen!)

## Faz 2 — Owner-Authentifizierung (implementiert)

- **Einladungslink-Flow**: Admin generiert 60-Tage-Token → sicherer Versand → Anbieter öffnet Link → Session-Cookie (90 Tage, HttpOnly, Secure, SameSite=Lax)
- **Magic Link**: Anbieter fordert Login-Link per E-Mail an (15-Min-TTL, single-use)
- **Step-up Auth**: Sensible Aktionen (Inserat entfernen, E-Mail/Passwort ändern, Konto schließen) erfordern OTP- oder Passwort-Bestätigung (15-Min-Fenster)
- **OTP**: 6 Stellen, bcrypt-Hash in CI4-Session (nicht DB), 10-Min-TTL
- **Profil-Upgrade**: Anbieter kann E-Mail und Passwort (min. 10 Zeichen) hinzufügen
- **Security-Log**: Alle Auth-Ereignisse in `security_log` (invite_accepted, magic_link_sent, stepup_verified …)
- **Sicherheitsprinzipien**: Rohe Token werden NIEMALS gespeichert — nur SHA-256-Hash. Tokens sind 256 Bit (bin2hex(random_bytes(32))).

### Auth-Flow (Übersicht)

```
Admin → [POST /admin/estate/owners/{id}/generate-invite]
         ↓  raw token in Flash (einmal anzeigen!) → Admin sendet Link per sicherem Kanal
Anbieter → [GET /einladung/{rawToken}]
            ↓  Token validiert → Session-Cookie gesetzt → Weiterleitung zum offenen Entwurf oder Panel
Panel → sensible Aktion (Entfernen)?
        ↓  isStepUpValid()? Nein → [GET /owner/stepup]
           OTP per E-Mail oder Passwort eingeben → stepup_verified_at in Session (15 Min gültig)
```

### Security-Events Tabelle

| Event | Auslöser |
|---|---|
| `invite_generated` | Admin generiert neuen Invite-Link |
| `invite_accepted` | Anbieter öffnet Invite-Link erstmalig |
| `magic_link_sent` | Anbieter fordert Magic-Link an |
| `magic_link_accepted` | Anbieter öffnet Magic-Link |
| `stepup_verified` | OTP/Passwort-Bestätigung erfolgreich |
| `owner_upgraded` | E-Mail oder Passwort gesetzt |
| `listing_removed` | Inserat entfernt (nach Step-up) |
| `session_rotated` | Session-Token rotiert (Sicherheitsrotation) |
| `session_destroyed` | Abmeldung |

## Faz 3 — Seeker Aboneliği, Ödeme & Mesajlaşma (implementiert)

- **Paywall**: Değer-öncelikli tasarım — Plus (5€/ay, önerilen) / Pro (yer tutucu). Güven sinyalleri: Stripe, kein Makler, jederzeit kündbar.
- **Stripe Checkout**: Seeker aboneliği (recurring 5€/ay) + owner extra ilan ücreti (one-off ~20€). Hosted Checkout — kart verisi kendi sunucuna gelmiyor.
- **Stripe Webhook** (`POST /webhooks/stripe`): İmza doğrulamalı, idempotent. `checkout.session.completed`, `customer.subscription.updated/.deleted`, `invoice.payment_succeeded` handle edilir.
- **BILLING_ENABLED=false**: Trial bypass — Stripe'a gitmeden ücretsiz seeker ve owner akışları aktif.
- **Seeker Panel**: `/seeker/panel` — Nachrichten, Gespeicherte Suchen, abonelik durumu + Stripe Customer Portal linki.
- **Saved Searches + Alarm**: Alert toggle → cron günlük yeni ilanları e-posta ile bildirir.
- **Owner Extra Listing**: 2. ve sonraki ilanlar `BILLING_ENABLED=true` iken Stripe Checkout'a yönlendirir. İlk ilan her zaman ücretsiz.
- **E-posta bildirimleri**: Owner → yeni mesaj bildirimi. Seeker → arama alarmı.
- **EU VAT**: Stripe Tax etkin (`tax_id_collection`). Fiyatlar VAT-dahil gösterilir.

### Stripe Kurulum Adımları

1. [dashboard.stripe.com](https://dashboard.stripe.com) → API Keys → `.env`'e kopyala
2. Stripe Dashboard → Products → "modeiland Plus" → Recurring price 500 cent/month → Price ID'yi `STRIPE_SEEKER_PRICE_ID`'ye yaz
3. Webhooks → Add endpoint → `https://yourdomain.de/webhooks/stripe`
   - Events: `checkout.session.completed`, `customer.subscription.updated`, `customer.subscription.deleted`, `invoice.payment_succeeded`
   - Webhook Secret → `STRIPE_WEBHOOK_SECRET`'e yaz
4. `BILLING_ENABLED=true` yap → canlı ödeme akışı aktif

> **EU VAT Notu:** Stripe Tax ile fiyatlar otomatik olarak müşteri konumuna göre KDV içerir.
> Alman KDV (19%) için vergi kaydı (Umsatzsteuer-ID) ve bir Alman avukat onayı gereklidir.
> Bu belge hukuki danışmanlık değildir.

> **composer notu:** `composer require stripe/stripe-php` — deploy öncesi çalıştırın.

---

## Faz 4 — Cloudflare Images, AI Eşleştirme & Bewerbungspaket (implementiert)

### Cloudflare Images

Fotoğraflar ilk yüklemede sunucuda `writable/uploads/` altına kaydedilir.
Cron, onaylı fotoğrafları CF Images API'sine aktarır ve `listing_images.cf_url` günceller.
`cf_url` henüz yoksa görünüm otomatik olarak yerel yola döner — cron çalışana kadar sorun olmaz.

**Kurulum:**

1. Cloudflare Dashboard → Images → API token oluştur (Images:Edit izni)
2. `.env`'e kopyala:
   ```
   CLOUDFLARE_ACCOUNT_ID        = <32-char-hex>
   CLOUDFLARE_IMAGES_TOKEN      = <token>
   CLOUDFLARE_IMAGES_DELIVERY_URL = https://imagedelivery.net/<hash>
   ```
3. Hostinger Scheduled Tasks → her 10 dakikada bir:
   ```
   php /home/<user>/public_html/spark estate:upload-images
   ```

> **Not:** CF Images sadece `approved_photos` consent kaydı olan ilanlar için aktif edilir (GDPR).
> CF Images olmadan (env boş) platform tam çalışır — fotoğraflar yerel URL ile sunulur.

### AI Fit Score Eşleştirme

- Kural tabanlı, saf PHP — gerçek zamanlı embedding yok (paylaşımlı hosting kısıtı).
- Puan hesaplama: Warmmiete 35p + Zimmer 25p + m² 20p + Ort 15p + Typ 5p = max 100.
- Puan ≥ 40 ise AI (GPT-4o-mini / Claude Haiku) kısa bir Almanca açıklama üretir.
- **Maliyet kısıtı:** AI açıklaması `match_reasons` tablosunda `(listing_id, filters_hash)` çifti başına 7 gün önbelleğe alınır — tekrar çağrı yapılmaz.
- Puanlar arama sonuçlarında badge olarak gösterilir; ilan detay sayfasında açıklama metniyle birlikte.

### AI Bewerbungspaket

- Seeker, `/inserate/{id}/bewerben` sayfasında Almanca Anschreiben + belge checklist alır.
- AI çağrısı `application_drafts` tablosunda `(seeker_id, listing_id)` başına 30 gün önbelleğe alınır.
- "Neu generieren" butonu önbelleği temizleyip yeni çağrı yapar (token maliyeti seeker'ın tercihi).
- Seeker profili eksikse (`/seeker/profil`) fallback metin döner — asla hata sayfası çıkmaz.
- **Hukuki not:** Oluşturulan metin AI çıktısıdır, hukuki bağlayıcılığı yoktur. Kullanıcıya gösterim sırasında açıkça belirtilir ("kein Rechtsrat").

### Yeni Cron Komutu

| Komut | Amaç | Sıklık |
|---|---|---|
| `php spark estate:upload-images` | Onaylı fotoğrafları CF Images API'sine aktar | Her 10 dk |

---

## Kurulum (Faz 1 + 2 + 3 + 4)

```bash
# 1. Composer bağımlılıkları
composer install

# 2. .env oluştur
cp .env.example .env
# → AI_API_KEY, SMTP ayarları, app.baseURL doldur

# 3. Encryption key üret
php spark key:generate

# 4. Estate modülü migration'larını çalıştır
php spark migrate --all

# 5. CSS derle (Node sadece yerel build için — sunucuda gerekmez)
npm install
npm run build   # → public/css/style.css (statik, repoya commit'le)

# 6. Geliştirme sunucusu
php spark serve
# → http://localhost:8080
```

### .env Değişkenleri (Estate modülü)

| Değişken | Varsayılan | Açıklama |
|---|---|---|
| `BILLING_ENABLED` | `false` | Ödeme akışını aktif/pasif yapar |
| `AI_PROVIDER` | `openai` | `openai` veya `anthropic` |
| `AI_API_KEY` | — | AI sağlayıcı API anahtarı |
| `AI_MODEL` | `gpt-4o-mini` | Model adı |
| `SEEKER_PRICE_CENTS` | `500` | Abonelik ücreti (cent) |
| `OWNER_EXTRA_LISTING_CENTS` | `2000` | Ek ilan ücreti (cent) |
| `email.SMTPHost` | — | SMTP sunucu |
| `email.SMTPPass` | — | SMTP şifre |
| `STRIPE_SECRET_KEY` | — | Stripe gizli anahtar (sk_live_...) |
| `STRIPE_PUBLISHABLE_KEY` | — | Stripe yayın anahtarı (pk_live_...) |
| `STRIPE_WEBHOOK_SECRET` | — | Webhook imza anahtarı (whsec_...) |
| `STRIPE_SEEKER_PRICE_ID` | — | Stripe recurring price ID (5€/ay) |
| `CLOUDFLARE_ACCOUNT_ID` | — | CF account hash (Faz 4) |
| `CLOUDFLARE_IMAGES_TOKEN` | — | CF Images API token (Faz 4) |
| `CLOUDFLARE_IMAGES_DELIVERY_URL` | — | CF delivery base URL (Faz 4) |

### Cron Tanımları (Hostinger Scheduled Tasks)

```bash
# Süresi dolmuş owner invite token'larını 'expired' işaretle + 7 gün kala uyarı e-postası (günlük)
0 2 * * * /usr/bin/php /home/user/domains/example.com/spark estate:expire-invites

# Süresi dolmuş ve kullanılmış magic link token'larını sil (saatlik)
0 * * * * /usr/bin/php /home/user/domains/example.com/spark estate:cleanup-magic-links

# Seeker arama alarmları — yeni ilanları e-posta ile bildir (günlük, sabah + akşam)
0 7 * * * /usr/bin/php /home/user/domains/example.com/spark estate:send-search-alerts
0 18 * * * /usr/bin/php /home/user/domains/example.com/spark estate:send-search-alerts

# Onaylı fotoğrafları Cloudflare Images'a aktar (Faz 4 — CF env dolu ise aktif)
*/10 * * * * /usr/bin/php /home/user/domains/example.com/spark estate:upload-images
```

> **Not:** Hostinger'da `spark` dosyasının tam yolunu kullanın. `public_html` üst dizininde bulunur.
> Çalıştırma izni: `chmod +x spark` (gerekirse).

### Test Adımları

**Faz 1 — Temel Akış**

1. `php spark migrate --all` — migration hatasız tamamlanmalı
2. `GET /admin/estate/owners` — admin login sonrası görünmeli
3. Owner lead oluştur → Outreach şablonu görünmeli
4. Listing oluştur → AI Import → `ai_import_status = done` → Publish
5. `GET /inserate` — ilan listesi görünmeli
6. İlan detayı → "Anbieter kontaktieren" → mesaj formu (`BILLING_ENABLED=false`: direkt erişim)
7. Mesaj gönder → owner e-posta bildirimi → `/owner/panel` → mesaj görünmeli
8. `/impressum`, `/datenschutz`, `/agb` route'ları 200 dönmeli

**Faz 3 — Ödeme & Seeker Panel**

16. `GET /abonnieren` — paywall görünmeli (Plus / Pro iki kolon)
17. E-posta gir → "Zugang freischalten" → `BILLING_ENABLED=false`: direkt panel'e → seeker session aktif
18. `GET /seeker/panel` — Nachrichten + Gespeicherte Suchen + abonelik durumu
19. `/inserate` → "Suche speichern" → `/seeker/panel` → alarm toggle → `php spark estate:send-search-alerts`
20. Owner 2. ilan → `approve()` → `BILLING_ENABLED=false`: checkout bypass → ilan live
21. `BILLING_ENABLED=true` → Stripe test keys → `/abonnieren/checkout` → Stripe Checkout test card → success → panel
22. `stripe listen --forward-to localhost:8080/webhooks/stripe` ile webhook doğrula (local test)
23. `php spark estate:cleanup-magic-links` → eski tokenlar silinmeli
24. `php spark estate:expire-invites` → süresi dolmuş invite'lar expired işaretlenmeli

**Faz 2 — Auth Akışı**

9. Admin → `/admin/estate/owners/{id}` → "Einladungslink generieren" → link gösterilmeli (bir kez)
10. Link kopyala → yeni sekmede `/einladung/{token}` → `/owner/panel`'e yönlendirmeli
11. Panel: profil tamamlanmamış uyarısı → `/owner/profil` → e-posta + şifre gir → kaydet
12. `/owner/logout` → `/owner/login` → magic link talep et → e-posta gelmeli → linke tıkla → panel
13. Panel: "Inserat entfernen" → `/owner/stepup` → OTP veya şifre gir → ilan kaldırılmalı
14. `php spark estate:expire-invites` — süresi dolmuş token varsa `expired` işaretlenmeli
15. `php spark estate:cleanup-magic-links` — eski token'lar silinmeli

### SQLite Dosyası

Dosya: `writable/database/cekirdek.db` — `public/` dışında, web-erişilemez.
WAL modu: migration 000100 tarafından otomatik açılır.

### E-posta (SPF/DKIM/DMARC)

Transaksiyonel e-posta için Resend / Brevo / Postmark önerilir.
DNS kayıtlarını (SPF, DKIM, DMARC) gönderim domeniniz için yapılandırın.
Hostinger SMTP da kullanılabilir: `smtp.hostinger.com:587 TLS`.

### Tailwind CSS Build

```bash
npm run build   # minified static CSS → public/css/style.css
npm run watch   # development watch
```

Build çıktısı `public/css/style.css`'e commit'lenir — sunucuda Node runtime gerekmez.

---

## Gereksinimler

- PHP 8.2+
- SQLite3 PHP extension (`php-sqlite3`)
- Composer
- Node.js (yalnızca yerel CSS derlemesi için — sunucuda gerekmez)

---

## Kurulum

```bash
# 1. Repoyu klonla
git clone https://github.com/kullanici/cekirdekcms.git
cd cekirdekcms

# 2. PHP bağımlılıkları
composer install

# 3. CSS derle (Node gerektirir)
npm install
npm run build

# 4. İlk kurulum (tek komut)
php spark setup
```

`php spark setup` şunları yapar:
- `.env` oluşturur
- Migration'ları çalıştırır (tüm tablolar oluşur)
- Varsayılan rolleri ve ayarları idempotent seed eder
- Admin kullanıcısı oluşturur (soran form)

---

## Geliştirme

```bash
# Geliştirme sunucusu
php spark serve
# → http://localhost:8080

# CSS watch modu
npm run watch

# Yedek al
php spark db:backup
```

---

## Yapı

```
app/Core/       → Değişmez çekirdek (auth, settings, media, base classes)
app/Modules/    → Proje modülleri (Pages, Blog, Contact, ...)
app/Views/      → Layout ve component'ler
docs/           → Ajan sözleşme dosyaları (CLAUDE.md, ARCHITECTURE.md, ...)
```

**Altın kural:** `app/Core/` asla değiştirilmez. Tüm proje işi `app/Modules/` altında.

---

## Admin Panel

`/admin/login` → E-posta + şifre ile giriş.

Varsayılan rotalar:
| URL | Açıklama |
|-----|----------|
| `/admin/dashboard` | Gösterge paneli |
| `/admin/pages` | Sayfa yönetimi |
| `/admin/blog` | Blog yönetimi |
| `/admin/contact` | Mesaj yönetimi |
| `/admin/media` | Medya kütüphanesi |
| `/admin/settings` | Sistem ayarları |

---

## Yeni Modül Ekleme

`docs/RECIPES.md` dosyasına bak. Kısaca:

1. `Blog` modülünü kopyala → yeniden adlandır
2. Migration + model + controller + view + routes
3. `module.json` içindeki `routes`, `routePriority` ve `adminMenu` alanlarını düzenle
4. `php spark migrate --all`

Merkezi `Routes.php`, `Autoload.php`, `composer.json` veya admin layout dosyasına yeni modül için dokunma. Modül manifest'i route ve sidebar keşfi için yeterlidir.

---

## Hostinger'a Deploy (ZIP)

1. Yerel: `npm run build`
2. ZIP oluştur: `vendor/`, `app/`, `public/`, `spark`, `composer.json`, `.env`
3. `node_modules/`, `.git/`, `writable/database/*.db` hariç tut
4. Hostinger File Manager'a yükle → çıkar
5. SSH: `php spark setup`
6. Document Root → `public/` klasörüne yönlendir

Detay: `docs/RECIPES.md` → Bölüm 6

---

## Dokümantasyon

| Dosya | İçerik |
|-------|--------|
| `docs/CLAUDE.md` | Ajan sözleşmesi, mutlak kurallar, intake protokolü |
| `docs/ARCHITECTURE.md` | Klasör yapısı, DB şeması, request lifecycle |
| `docs/CONVENTIONS.md` | Kodlama kuralları, isimlendirme |
| `docs/UI-KIT.md` | Renk token'ları, component listesi ve kullanımı |
| `docs/RECIPES.md` | Adım adım reçeteler |
| `AGENTS.md` | Ajanlar için kısa başlangıç dosyası |
| `PROJECT_SPEC.md` | Proje hedefi ve operasyon sözleşmesi |

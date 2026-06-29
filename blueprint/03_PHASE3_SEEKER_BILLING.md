# FAZ 3 — SEEKER ABONELİĞİ, ÖDEME & MESAJLAŞMA

> Önce `00_MASTER_PROMPT.md` oku. Bu faz, geliri ve etkileşimi açar. Ödeme bir
> **yazılım kullanım ücretidir**, asla komisyon/başarı ücreti değildir.

## Hedef
Seeker ücretsiz göz atar; iletişim/mesajlaşma ve AI başvuru paketi için aylık 5 €
abone olur. Owner ikinci ve sonraki ilanlar için ilan başına ~20 € öder. Deneme
sürümünde her şey ücretsizdir (`BILLING_ENABLED=false`).

## İş modeli (config'den okunan sabitler — koda gömme)
- Seeker Free: göz atma + sınırlı AI özeti, **iletişim yok**.
- Seeker Plus: **5 €/ay** → iletişim, mesajlaşma, kayıtlı arama/alarm, temel AI eşleştirme.
- Owner: **ilk ilan ücretsiz**; **2.+ ilan = ~20 € one-off** (abonelik değil, ilan başı).
- Tüm fiyatlar VAT-dahil gösterilir (EU). Sabitler: `SEEKER_PRICE_CENTS=500`,
  `OWNER_EXTRA_LISTING_CENTS=2000`.

## Ödeme entegrasyonu (Stripe) — shared hosting uyumlu
- Stripe **Checkout** (hosted sayfa) kullan; kart verisini kendi sunucuna alma.
- Abonelik için Stripe Customer Portal (iptal/yönetim).
- **Webhook = tek bir PHP endpoint** (`/webhooks/stripe`). İmza doğrulaması yap.
  Uzun process yok; webhook geldiğinde DB'yi güncelle (`subscriptions`,
  `owner_listing_charges`), audit_log'a yaz.
- Idempotency: aynı event iki kez gelirse çift kayıt oluşturma.
- EU VAT için Stripe Tax notu README'ye.
- **Feature flag:** `BILLING_ENABLED=false` iken tüm "öde" aksiyonları kullanıcıyı
  doğrudan erişime geçirir (trial), ama akış/ekranlar görünür kalır. `true` olunca
  gerçek Checkout devreye girer. Kod her iki durumu da temiz biçimde ele almalı.

## Paywall çerçevelemesi (önemli — algı riski)
- Paywall ekranında **önce değer, sonra fiyat**: "Kontaktieren Sie Anbieter direkt +
  automatisch erstellte Bewerbungsunterlagen." Fiyatı **AI başvuru paketi ve kazanılan
  zamana** çıpala, sadece "iletişim açma"ya değil.
- İki katman göster (Plus / Pro yer tutucu); önerileni vurgula. (Pro içeriği Faz 4'te.)
- Güven sinyalleri: güvenli ödeme (Stripe), gizli Provision yok, istediğin zaman iptal.

## Mesajlaşma (shared hosting → polling)
- Seeker → owner platform içi mesaj; abonelik/trial gerektirir.
- Konuşma thread'leri, okundu bilgisi (`read_at`).
- **Gerçek zamanlı yok:** panel periyodik **polling** ile yeni mesajı çeker. WebSocket KURMA.
- Yeni mesajda karşı tarafa e-posta bildirimi (SMTP).
- Doğrudan iletişim bilgisi yalnızca owner izin verdiyse VE seeker abone ise görünür.

## Seeker paneli (bu fazda tamamlanır)
- Arama + filtreler, **AI eşleştirme yer tutucusu** (gerçek skor Faz 4).
- Kayıtlı aramalar + alarm toggle'ı (alarm gönderimi cron ile).
- Mesaj thread'leri.
- Abonelik durumu / yönetim (Stripe Portal linki).

## Dönüşüm psikolojisi (owner yolculuğu — uygula)
1. Önce değer: "İlanınızı hazırladık — kontrol edip onaylayın."
2. Trafik göster: "İlanınıza talepler geliyor."
3. Sonra upgrade: "Talepleri kaçırmamak için hesabınızı güvenceye alın."
4. Sonra ödeme: "İkinci ilan veya gelişmiş yönetim için ödeme gerekir."

## Çıktı / kabul kriterleri
- [ ] `BILLING_ENABLED=false` iken trial: tüm akış ücretsiz, ekranlar görünür.
- [ ] `true` iken Stripe Checkout ile 5 €/ay seeker aboneliği ve 20 € owner ilan ücreti çalışıyor.
- [ ] Webhook imza-doğrulamalı, idempotent, DB+audit güncelliyor.
- [ ] Paywall değer-öncelikli çerçeveleniyor; Makler/Provision dili yok.
- [ ] Mesajlaşma polling ile çalışıyor; e-posta bildirimi gidiyor; WebSocket yok.
- [ ] Kayıtlı arama alarmları cron ile gönderiliyor.

## Bir sonraki faza devreden
- Pro katman içeriği (AI başvuru paketi, semantik eşleştirme) Faz 4.

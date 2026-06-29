# FAZ 5 — BÜYÜME, HATIRLATMALAR, YASAL & SERTLEŞTİRME

> Önce `00_MASTER_PROMPT.md` oku. Bu faz, ürünü konumlandırmayı bozmadan büyütür ve
> yayına/ödeme açmaya hazır hale getirir.

## Hedef
Owner elde tutma (akıllı hatırlatmalar), büyüme özellikleri, eksiksiz yasal sayfalar
ve güvenlik/operasyon sertleştirmesi.

## Akıllı profil-tamamlama hatırlatmaları (agresif günlük pop-up DEĞİL)
Katmanlı model uygula:
- **Dashboard banner:** sürekli ama rahatsız etmeyen.
- **Küçük modal:** günde en fazla 1 kez, "Bugün tekrar gösterme" seçenekli.
- **Bağlamsal hatırlatma:** ilk talep geldiğinde, ikinci talepte, ilanı düzenlerken,
  iletişim tercihini değiştirirken, yeni ilan eklerken.
- **Önerilen sıklık:** 1. gün göster · 2. gün gösterme · 3. gün küçük · 7. gün tekrar ·
  14. gün güvenlik odaklı · 30. gün link/güvenlik · son 7 gün süre uyarısı.
- Tüm zamanlanmış e-postalar **cron (Hostinger Scheduled Tasks)** ile; worker daemon yok.

## Büyüme özellikleri (konumlandırmayı bozmadan)
- Owner premium (aylık) + featured/boost ilan yer tutucuları (komisyon değil, görünürlük ücreti).
- Güvenilir owner'lar için self-serve ilan oluşturma (admin döngüsü olmadan).
- İngilizce/expat locale (i18n zaten Faz 1'de hazırlandı — şimdi doldur).
- Owner için temel analitik (görüntülenme, talep dönüşümü).

## Yasal sayfalar (placeholder → gerçek içerik)
- **Impressum, Datenschutzerklärung, AGB** gerçek içerikle doldurulur (avukat onayı notu).
- GDPR hakları: erişim, silme (cascade), veri taşınabilirliği; silme talebi akışı.
- Rıza ve güvenlik logları **export edilebilir** (belirli owner'ın belirli ilan
  versiyonunu belirli zamanda onayladığının kanıtı).
- Tek-tık ilan kaldırma; hesap silme yolu; yasal süreler içinde erasure.

## Sertleştirme & operasyon (shared hosting)
- Rate limiting (giriş, mesaj, AI çağrıları) — uygulama katmanında.
- SQLite yedekleme cron'u; DB dosyası web-erişilemez doğrulaması.
- Hata/loglama: kullanıcıya ham stack trace gösterme; "Erneut versuchen" + iletişim yolu.
- Boş/yükleniyor/hata durumları (skeleton, spinner yerine), her iki yüzey için.
- Güvenlik başlıkları (.htaccess): HTTPS zorla, HSTS, secure cookie, dizin listeleme kapalı.
- `BILLING_ENABLED=true`'ya geçiş öncesi kontrol listesi (avukat onayı, fiyat config,
  webhook testi, VAT).

## Çıktı / kabul kriterleri
- [ ] Akıllı hatırlatma cadence'i çalışıyor; günlük agresif pop-up yok; cron tabanlı.
- [ ] Yasal sayfalar gerçek; GDPR silme/erişim akışları çalışıyor; consent export var.
- [ ] Rate limiting, yedekleme, güvenlik başlıkları aktif.
- [ ] Tüm empty/loading/error durumları tutarlı.
- [ ] Ödeme açma öncesi kontrol listesi README'de.

## Proje vizyonu (hatırlatma)
"Özel emlak sahiplerinin maklersiz, kontrolü kendilerinde kalacak şekilde ciddi ev
arayanlara ulaşmasına yardımcı oluyoruz; ilanı AI ile hazırlıyor, arayanlara daha hızlı
ve akıllı bir bulma/başvuru yolu sunuyoruz."

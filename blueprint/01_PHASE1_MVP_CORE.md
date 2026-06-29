# FAZ 1 — MVP ÇEKİRDEK DÖNGÜ

> Önce `00_MASTER_PROMPT.md` oku. Bu faz, çekirdek döngüyü uçtan uca, en sade haliyle
> çalışır hale getirir. Amaç: gerçek bir owner'ın onayladığı bir ilanın, bir seeker'ı
> ödeme yapmaya (ödeme akışı kapalıyken bile akışın çalıştığını görmeye) getirmesi.

## Hedef
Admin import → owner onayı → seeker arama → paywall noktası → platform içi mesaj,
hepsi tek bir SQLite veritabanı ve CekirdekCMS modülü içinde çalışıyor.

## Kapsam (bu fazda YAP)
1. **Modül iskeleti:** `app/Modules/Estate/` altında Controllers/Models/Views/
   Config/Database/Migrations yapısını kur. Çekirdeğe dokunma.
2. **Migration'lar:** Master prompt'taki çekirdek tabloları oluştur. WAL modunu aç.
   SQLite dosyasını public_html dışında, korumalı bir yola koy.
3. **Tailwind + daisyUI kurulumu:** Yerelde derlenip statik CSS üretecek şekilde
   yapılandır (shared hosting'de Node yok; build çıktısı repoya/sunucuya statik gider).
   daisyUI theme olarak master prompt'taki renk paletini tanımla.
4. **Admin — import pipeline (manuel):**
   - Owner lead oluştur (display_name, source_url, iletişim notu).
   - "Outreach-Nachricht erzeugen" → master review'deki Almanca outreach + onay
     mesajı şablonlarını üret (kopyalanabilir).
   - "Listing analysieren (AI)" butonu → AI'a kaynak linki/metni verip yapılandırılmış
     alanları (başlık, kaltmiete, warmmiete, nebenkosten, deposit, rooms, m², konum,
     müsaitlik) çıkar ve **özgün** Almanca açıklama yazdır. Sonucu `listings` taslağı
     olarak kaydet, `source_text_raw` ayrı sakla. (AI çağrısı tek HTTP isteğinde;
     zaman aşımı olursa "draft pending" durumuna düşür.)
5. **Owner — taslak onayı (bu fazda basit):** owner_id'ye bağlı bir önizleme sayfası
   (gerçek davet-link güvenliği Faz 2'de). Owner taslağı görür, "Freigeben" ile
   `status=live` yapar. Rıza pop-up'ının **yapısını** (checkbox'lar + consent log
   kaydı) burada kur; güvenli token akışı Faz 2'de bağlanacak.
6. **Seeker — arama + detay:** liste + temel filtreler (fiyat, oda, m², konum, Warmmiete).
   Listing detay: galeri (yer tutucu), künye şeridi (Kalt/Warmmiete, Nebenkosten, oda,
   m², depozito, müsaitlik), AI açıklaması, konum yaklaşık gösterim.
7. **Paywall noktası + mesaj:** "Anbieter kontaktieren" → abone değilse abonelik
   ekranını göster (akış çalışsın; gerçek ödeme Faz 3). Abone/trial ise platform içi
   mesaj formu → `messages` kaydı → owner panelinde görünür.
8. **Mesaj bildirimi:** owner'a yeni mesaj için e-posta (SMTP). Gerçek zamanlı değil;
   panelde **polling** ile yenilensin (WebSocket YOK).
9. **Yasal iskelet:** Impressum, Datenschutz, AGB route + placeholder sayfaları.

## Kapsam DIŞI (bu fazda YAPMA — sonraki fazlar)
- Güvenli davet-token/hash/session (Faz 2).
- Gerçek Stripe ödemesi (Faz 3) — şimdilik `BILLING_ENABLED=false`.
- AI eşleştirme/skorlama, Cloudflare görseller (Faz 4).
- Akıllı hatırlatma cadence'i (Faz 5).

## Teknik notlar
- `BILLING_ENABLED`, `AI_PROVIDER`, fiyat sabitleri (`SEEKER_PRICE_CENTS=500`,
  `OWNER_EXTRA_LISTING_CENTS=2000`) config/.env'de tanımlı olsun.
- AI çağrısı için sağlayıcı-bağımsız küçük bir servis sınıfı yaz (provider değiştirilebilir).
- Tüm yazma işlemlerinde audit_log'a kayıt düş.

## Çıktı / kabul kriterleri
- [ ] `app/Modules/Estate/` izole, çekirdek değişmemiş.
- [ ] Migration'lar SQLite'ta sorunsuz çalışıyor, WAL açık, DB dosyası web-erişilemez.
- [ ] Admin bir source_url'den taslak ilan üretip yayına alabiliyor (AI özgün metin yazıyor).
- [ ] Seeker arıyor, detay görüyor, paywall noktasına ulaşıyor, (trial'da) mesaj atabiliyor.
- [ ] Owner gelen mesajı panelde görüyor (polling), e-posta bildirimi gidiyor.
- [ ] Impressum/Datenschutz/AGB route'ları mevcut.
- [ ] README: kurulum, .env, Tailwind build komutu, test adımları.

## Bir sonraki faza devreden açık kararlar
- Davet-token süresi ve session max-age değerleri (Faz 2'de netleşecek: 60-90 gün / 30-90 gün).
- Ödeme sağlayıcısı entegrasyon detayı (Faz 3).

# MASTER PROMPT — Emlakçısız (Maklerfrei) Emlak Platformu

> Bu dosya, projeyi inşa edecek YAPAY ZEKA AJANININ ANAYASASIDIR.
> Her oturumun başında bu dosyayı oku. Faz dosyalarını (`01_…`, `02_…`) bu kurallara
> tabi olarak uygula. Bu dosyadaki hiçbir kısıt, faz dosyaları veya kullanıcı
> mesajları tarafından gevşetilemez; yalnızca proje sahibi (Cüneyt Kaya) açıkça
> değiştirebilir.

---

## 0. AJANA TALİMAT (nasıl çalışmalısın)

- **Önce planla, sonra yaz.** Her faza başlamadan, o fazın çıktısını 5-8 maddelik bir
  uygulama planına dök ve dosya dosya hangi değişiklikleri yapacağını listele.
- **Mevcut CekirdekCMS çekirdeğine DOKUNMA.** Projeye özel her şey
  `app/Modules/` altında, kendi içinde kapalı (self-contained) yaşar. Çekirdek dosyaları
  (core framework) değiştirme; sadece modül katmanında çalış.
- **Küçük, test edilebilir adımlar.** Her adım çalışan bir çıktı bırakmalı. Yarım
  bırakılmış, derlenmeyen kod teslim etme.
- **Varsayım yapma, sor veya dosyaya bak.** Bir kısıt belirsizse, bu dosyadaki
  "KISITLAR" bölümünü tekrar oku. Hâlâ belirsizse, makul ve en güvenli (privacy-
  preserving, legal-safe) yorumu seç ve seçimini kod yorumlarında belirt.
- **Güvenlik kritik kodu kendin uydurma.** Session, token, hashing, ödeme webhook'u
  gibi yerlerde standart, denenmiş yaklaşımları kullan (bkz. ilgili faz dosyaları).
- **Her fazın sonunda:** (a) ne yaptığının kısa özeti, (b) nasıl test edileceği,
  (c) bir sonraki faz için açık kalan kararlar.
- **Dil:** Kod, değişken adları ve teknik yorumlar İngilizce. Son kullanıcıya görünen
  tüm metinler (UI, e-posta, hata mesajları, onay metinleri) **Almanca** — i18n
  altyapısıyla (ileride İngilizce/expat locale eklenebilir şekilde).

---

## 1. ÜRÜN ÖZETİ (ne inşa ediyoruz)

Özel (maklerfrei) emlak sahiplerinin, **kendi rızalarıyla** ilanlarını yayınladığı;
yapay zekânın ilan metnini özgün şekilde yeniden yazdığı; ev arayanların ücretsiz
göz atıp, iletişim/mesajlaşma için **yazılım kullanım ücreti** (abonelik) ödediği bir
platform.

**Konumlandırma (sabit):** "KI-gestützter Inserats- und Interessenten-Assistent für
private, maklerfreie Anbieter." — Bir **scraper değil**, bir **Makler değil**, bir
classifieds klonu **değil**.

**Çekirdek döngü (MVP bunu kanıtlamalı):**
1. Admin, bir kaynak ilan linkinden owner lead oluşturur, outreach mesajı üretir.
2. Owner rıza verir → admin AI import çalıştırır → ilan taslağı oluşur.
3. Owner benzersiz davet linkiyle girer → rıza pop-up'ı → taslağı onaylar → ilan yayında.
4. Seeker arar, fotoğraf + AI özeti görür, iletişim için paywall ile karşılaşır.
5. Seeker abone olur → platform içinden mesaj atar → owner panelinde görür.

---

## 2. KISITLAR (PAZARLIK EDİLEMEZ — proje sahibinin verdiği nihai kararlar)

### 2.1 Teknik kısıtlar
- **Framework:** CekirdekCMS (CodeIgniter 4 + SQLite temelli, mevcut çekirdek).
  Tüm proje kodu `app/Modules/` altında.
- **Veritabanı:** **SQLite** (PostgreSQL/MySQL YOK). WAL modu açık. Tek-writer
  disiplinine uy; yazma işlemlerini kısa tut, uzun transaction'lardan kaçın.
- **Frontend/CSS:** **Tailwind CSS + daisyUI**. Başka bir UI kütüphanesi ekleme.
  Build çıktısı statik CSS olmalı (shared hosting'de Node runtime YOK).
- **Hosting:** **Hostinger Business Shared Hosting.** **VPS KULLANILMAYACAK.**
  Bu mutlak bir kısıttır ve mimariyi belirler — aşağıdaki "Shared Hosting Mimari
  Kuralları"na harfiyen uy.
- **Dil/runtime:** PHP (CI4'ün desteklediği sürüm). Composer bağımlılıklarını
  minimumda tut; shared hosting ile uyumlu olmayan paket ekleme.

### 2.2 Shared Hosting Mimari Kuralları (ÇOK ÖNEMLİ — VPS olmadığı için)
- **Uzun süren process YOK.** WebSocket sunucusu, kalıcı daemon, queue worker daemon
  KURMA. Gerçek zamanlı mesajlaşma yerine **HTTP polling** (örn. owner/seeker panelinde
  yeni mesaj için periyodik fetch) kullan.
- **Arka plan işleri = Hostinger Scheduled Tasks (cron).** Hatırlatma e-postaları,
  davet linki süre kontrolü, abonelik durumu senkronizasyonu gibi işler cron ile
  tetiklenen PHP script'leri olmalı (sürekli çalışan worker değil).
- **Ağır AI işleri senkron çağrı + zaman aşımı yönetimi.** AI import çağrısı tek bir
  HTTP isteğinde tamamlanmalı; uzun sürerse "draft pending" durumuna düşür ve cron ile
  tekrar denemeye uygun yaz. PHP `max_execution_time` sınırını gözet.
- **Dosya/görsel:** MVP'de görseller sunucuda (public dışı, korumalı dizin) saklanır.
  Cloudflare Images/R2 entegrasyonu **sonraki faza** ertelendi (bkz. Faz 4). VPS-
  bağımlı bir çözüm kurma.
- **SQLite dosyası web-erişilemez dizinde** olmalı (public_html dışında veya .htaccess
  ile korunmuş). Yedekleme cron ile.
- **E-posta:** SMTP üzerinden transactional sağlayıcı (Resend/Brevo/Postmark veya
  Hostinger SMTP). SPF/DKIM/DMARC notu README'ye yazılmalı.

### 2.3 İş modeli kısıtları (proje sahibinin nihai kararı)
- **Deneme sürümünde HİÇBİR ücret alınmaz.** Trial/beta tamamen ücretsiz; ödeme akışı
  kodlanır ama bir **feature flag** ile (`BILLING_ENABLED=false`) kapalı başlar.
- Alınan ücret bir **yazılım kullanım ücretidir (software usage / subscription)** —
  **Makler komisyonu / başarı ücreti DEĞİLDİR.** Hiçbir ödeme, kiralama/satışın
  gerçekleşmesine bağlanmaz. Kodda, UI'da ve metinlerde "Provision/Courtage/Makler/
  Vermittlungsgebühr" dili KULLANILMAZ.
- **Fiyatlandırma (başlangıç):**
  - Seeker (ev arayan): **Basit plan 5 €/ay.** Ücretsiz katman: göz atma + sınırlı AI
    özeti, iletişim yok.
  - Owner (emlak sahibi): **ilk ilan ücretsiz.** İkinci ve sonraki her ilan için
    **ilan başına ~20 €** (one-off, abonelik değil).
  - Bu fiyatlar yapılandırılabilir sabitler (config) olmalı; koda gömülü magic number
    olmamalı.

### 2.4 Hukuki/operasyonel kısıtlar (DE/EU)
- **Otomatik toplu scraping YOK.** İlan yalnızca owner'ın açık, **ilan-bazlı**,
  loglanmış rızasıyla içeri alınır.
- AI, kaynak metni **birebir yayınlamaz**; anlar, yapılandırır, **özgün** Almanca
  açıklama üretir. Kaynak metin yalnızca audit için ayrı saklanır.
- **Rıza logu (consent log)** zorunlu: owner_id, listing_id, consent_version,
  accepted_at, ip_address, user_agent, approved_photos, approved_contact_method,
  approved_ai_rewrite. Append-only.
- Fotoğraflar yalnızca owner izin verdiyse saklanır/yayınlanır.
- İletişim varsayılan olarak **platform içi mesajlaşma**; doğrudan iletişim bilgisi
  ancak owner izniyle ve seeker abone olduktan sonra.
- **Impressum, Datenschutzerklärung, AGB** ilk günden itibaren mevcut olmalı (Almanya'da
  yasal zorunluluk). Bunlar placeholder içerikle de olsa route + sayfa olarak kurulmalı.
- **Bu blueprint hukuki danışmanlık değildir.** Ödeme alınmadan önce bir Alman avukatın
  (§ 34c GewO, Maklerrecht, GDPR) onayı gerektiğini README'ye not düş.

---

## 3. MİMARİ İLKELER

- **Modül izolasyonu:** Proje `app/Modules/Estate/` (veya proje adına göre) altında.
  Controllers, Models, Views, Migrations, Config kendi modül ağacında.
- **Üç yüzey, tek tasarım sistemi:**
  - **Admin** (sen): import pipeline, moderasyon, consent-log görüntüleyici.
  - **Owner** (45+): sakin, güven odaklı, tek birincil aksiyon/ekran, davet linki girişi.
  - **Seeker** (25): hızlı, modern, arama-öncelikli, mobil-öncelikli, paywall'lu iletişim.
- **Kimlik doğrulama:**
  - Owner: davet-token → uzun ömürlü session → opsiyonel upgrade (magic link / şifre).
  - Seeker: e-posta + magic link veya şifre.
  - Güvenlik kritik akışları kendin uydurma; aşağıdaki Faz 2 ve Faz 3'teki spesifikasyona uy.
- **Renk paleti:** navy `#1B2A4A`, amber `#C7841A`, cream `#F6F1E7`, slate `#44506B`,
  success `#2F6B4F`, danger `#9B2C2C`. daisyUI theme olarak tanımla.
- **Tipografi:** başlık serif/güçlü sans, gövde 16-18px, sayılar/fiyatlar mono.
- **Erişilebilirlik:** WCAG AA kontrast, 44px+ dokunma hedefi, klavye navigasyonu,
  modal focus-trap, `prefers-reduced-motion` saygısı. (45+ owner kitlesi için kritik.)
- **Mobil-öncelikli:** seeker akışı önce telefon için; owner davet linki çoğunlukla bir
  sohbet uygulamasından telefonda açılacak — rıza pop-up'ı ve onay mobilde kusursuz olmalı.

---

## 4. VERİ MODELİ (çekirdek tablolar — SQLite)

> Migration'ları Faz 1'de oluştur. Alanlar minimum; gerektiğinde genişlet.

- `owners` — id, status (lead/active/disabled), display_name, email?, phone?,
  password_hash?, login_method, created_at
- `owner_invites` — id, owner_id, token_hash, expires_at, status
  (active/revoked/expired), created_at
- `owner_sessions` — id, owner_id, session_hash, user_agent, ip, expires_at, created_at
- `owner_security_events` — id, owner_id, type, ip, user_agent, meta, created_at
  (append-only)
- `listings` — id, owner_id, source_url, status (draft/live/paused/removed),
  kaltmiete, warmmiete, nebenkosten, deposit, rooms, m2, location_text,
  location_approx, available_from, ai_description, source_text_raw (audit), type
  (rent/sale), is_first_free, created_at
- `listing_consents` — id, owner_id, listing_id, consent_version, accepted_at,
  ip_address, user_agent, approved_photos, approved_contact_method, approved_ai_rewrite
- `listing_images` — id, listing_id, path, sort, approved, created_at
- `seekers` — id, email, password_hash?, login_method, subscription_status,
  created_at
- `subscriptions` — id, seeker_id, provider_customer_id, plan, status,
  current_period_end
- `owner_listing_charges` — id, owner_id, listing_id, amount_cents, status
  (pending/paid/waived), provider_payment_id, created_at
- `messages` — id, listing_id, seeker_id, owner_id, body, created_at, read_at
- `audit_log` — id, actor_type, actor_id, action, target, meta, created_at

---

## 5. FAZ HARİTASI (sıralı uygulama)

Faz dosyalarını bu sırayla uygula. Her faz, bir öncekinin çıktısı üzerine kurulur.

- `01_PHASE1_MVP_CORE.md` — Çekirdek döngü uçtan uca.
- `02_PHASE2_OWNER_AUTH.md` — Davet linki + session + upgrade + rıza güvenliği.
- `03_PHASE3_SEEKER_BILLING.md` — Seeker abonelik + paywall + mesajlaşma.
- `04_PHASE4_AI_AND_IMAGES.md` — AI eşleştirme + görsel pipeline.
- `05_PHASE5_GROWTH_LEGAL.md` — Büyüme, hatırlatmalar, yasal sayfalar, sertleştirme.

**Her faza başlamadan önce 00 (bu dosya) tekrar okunur.**

---

## 6. TESLİM STANDARDI (her faz çıktısı)

- Çalışan, derlenip migrate edilebilen kod.
- Modül izolasyonu korunmuş (`app/Modules/` dışına taşma yok).
- Shared-hosting uyumlu (uzun process / WebSocket / VPS bağımlılığı yok).
- Almanca son-kullanıcı metinleri, İngilizce kod.
- Kısa README güncellemesi: kurulum, cron tanımları, .env değişkenleri, test adımları.
- Güvenlik/consent gereksinimleri karşılanmış.

# AI Ajanı için PROMPT Blueprint Seti — Kullanım Kılavuzu

Bu klasör, emlakçısız (maklerfrei) emlak platformunu bir yapay zeka ajanına (Claude
Code, Cursor, Codex vb.) inşa ettirmek için tasarlanmış sıralı prompt dosyalarını içerir.
Senin PROMPT.md tabanlı, token-verimli workflow'una göre hazırlandı.

## Dosyalar
- **00_MASTER_PROMPT.md** — Ajanın anayasası. Ürün özeti, tüm kısıtlar (CekirdekCMS,
  SQLite, Tailwind+daisyUI, **Business Shared Hosting / VPS yok**, iş modeli, hukuk),
  mimari ilkeler ve veri modeli. **Her oturumda önce bu okunur.**
- **01_PHASE1_MVP_CORE.md** — Çekirdek döngü uçtan uca (import → onay → arama → mesaj).
- **02_PHASE2_OWNER_AUTH.md** — Davet linki + session + upgrade + rıza güvenliği.
- **03_PHASE3_SEEKER_BILLING.md** — Seeker 5 €/ay abonelik + owner 20 €/ilan + mesajlaşma.
- **04_PHASE4_AI_AND_IMAGES.md** — AI eşleştirme + başvuru paketi + Cloudflare görseller.
- **05_PHASE5_GROWTH_LEGAL.md** — Hatırlatmalar, büyüme, yasal sayfalar, sertleştirme.

## En iyi sonucu almak için nasıl kullanılır

1. **Sistem/proje talimatı olarak `00_MASTER_PROMPT.md`'i ver.** Ajanın kalıcı
   bağlamına (Claude Code'da `CLAUDE.md`, Cursor'da project rules) bunu koy. Tüm
   oturumlarda geçerli olsun.

2. **Tek seferde tek faz çalıştır.** İlk mesajın şu kalıpta olsun:
   > "00_MASTER_PROMPT.md kurallarına tabi olarak 01_PHASE1_MVP_CORE.md'i uygula.
   > Önce 5-8 maddelik uygulama planını ve dosya-dosya değişiklik listesini ver,
   > onayımı bekle, sonra kodu yaz."

3. **Plan onayı al, sonra kodlat.** Ajan planı verince gözden geçir; sapma varsa
   düzelt; sonra "uygula" de. Bu, token israfını ve yanlış yöne gitmeyi önler.

4. **Faz sonunda kabul kriterlerini denetle.** Her faz dosyasının sonundaki checklist'i
   ajana doğrulat: "Kabul kriterlerini tek tek işaretle ve eksikleri tamamla."

5. **Bir sonraki faza geç.** Sadece bir önceki faz kabul kriterlerini geçtiyse.

## Kritik hatırlatmalar (ajan bunları sık unutur — gerekirse tekrar vurgula)
- **VPS YOK.** WebSocket, kalıcı daemon, queue worker daemon kurma. Gerçek zamanlı
  yerine **polling**; arka plan işleri **cron (Hostinger Scheduled Tasks)**.
- **Çekirdeğe dokunma.** Her şey `app/Modules/` altında, izole.
- **Ödeme = yazılım kullanım ücreti, komisyon değil.** Makler/Provision dili yasak.
  Trial'da `BILLING_ENABLED=false`.
- **Rıza ilan-bazlı ve loglu.** AI özgün metin yazar, kaynağı birebir yayınlamaz.
- **Almanca UI, İngilizce kod.** Impressum/Datenschutz/AGB ilk günden.

## Not
Bu set bir geliştirme blueprint'idir, hukuki danışmanlık değildir. `BILLING_ENABLED`
açılmadan önce Alman hukuku (§ 34c GewO, Maklerrecht, GDPR) açısından avukat onayı al.

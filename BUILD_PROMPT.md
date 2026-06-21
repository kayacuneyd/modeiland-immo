# CekirdekCMS — Çekirdek İnşa Promptu (Master Build Prompt)

> Bu dosyayı Claude Code / Codex ajanına ver. Ajan, bu repo kökünde **CekirdekCMS** adlı yeniden kullanılabilir çekirdeği baştan sona inşa edecek. Çekirdek, gelecekteki her müşteri projesinin başlangıç noktası olacak.

---

## 0. ROL VE GÖREV

Sen, kıdemli bir PHP/CodeIgniter 4 mimarısın. Görevin: **CekirdekCMS** adında, yeniden kullanılabilir, ajan-dostu, token-verimli bir CMS çekirdeği inşa etmek.

Bu çekirdek bir "WordPress core" mantığında çalışacak: bir kez kurulacak, sonra her yeni müşteri projesinde klonlanıp üzerine modüller eklenecek. **Çekirdek (Core) asla elle değiştirilmeyecek; tüm proje işi Modules altında yapılacak.**

Çalışmaya başlamadan önce **Bölüm 9'daki soru-cevap sürecini** uygula: belirsiz noktaları bana sor, cevaplarımı al, sonra inşa et. Tahmin yürütüp ilerleme; netleştir.

---

## 1. HEDEFLER (Bu çekirdek neyi çözüyor?)

1. **Hız:** Yeni proje 2 dakikada ayağa kalksın (`clone + setup`).
2. **Tutarlılık:** Her proje aynı mimari, isimlendirme ve tasarım dilini paylaşsın.
3. **Token verimliliği:** Gelecekteki ajan, "nasıl yapılır"ı keşfetmek için dosya gezmesin; sözleşme dosyalarından okusun.
4. **Ajan-dostu olma:** Gelecekte bir ajana proje brief'i + bu çekirdek verildiğinde, soru-cevapla netleşip sorunsuz ürün çıkarabilsin.

---

## 2. TEKNOLOJİ YIĞINI (kesin)

- **Backend:** CodeIgniter 4 (PHP 8.1+)
- **Veritabanı:** SQLite (shared hosting / Hostinger Business uyumlu)
- **Frontend:** Tailwind CSS + DaisyUI (lokalde derlenir → statik CSS; sunucuda Node gerekmez)
- **Şablon:** CI4 native view + tekrar kullanılabilir view partial/component'ler
- **Dağıtım hedefi:** Shared hosting (ZIP-deploy), Node.js çalıştırılamaz varsayımıyla

---

## 3. MİMARİ: CORE / MODULES AYRIMI (en kritik kural)

Aşağıdaki sınır mutlaktır:

```
app/
  Core/        → namespace App\Core — DEĞİŞMEZ. Projeden projeye aynı kalır.
  Modules/     → namespace App\Modules — Projeye özel her şey burada yaşar.
```

**Test:** "Bunu bir sonraki projede de aynen kullanacak mıyım?" → Evet = Core, Hayır = Module.

Her modül **self-contained** olmalı: kendi Controllers, Models, Views, Config/Routes, Database/Migrations klasörlerini içinde barındırmalı. Bir modülü silmek = tek klasörü silmek; projenin başka yerinde iz bırakmamalı.

---

## 4. KLASÖR YAPISI (hedef)

```
cekirdekcms/
├── app/
│   ├── Core/
│   │   ├── Controllers/
│   │   │   ├── BaseAdminController.php      # auth + panel layout + yetki kontrolü
│   │   │   ├── BaseApiController.php        # standart JSON response
│   │   │   └── BaseWebController.php        # frontend layout + SEO
│   │   ├── Models/
│   │   │   └── BaseModel.php                # soft delete, timestamps, SQLite pragma'lar
│   │   ├── Auth/                            # login, logout, session, RBAC
│   │   ├── Settings/
│   │   │   └── SettingsService.php          # key-value settings (DB), nokta notasyon
│   │   ├── Media/
│   │   │   └── MediaService.php             # upload, resize, library
│   │   ├── Support/
│   │   │   └── helpers (component(), setting(), seo() vb.)
│   │   └── Config/                          # core route'lar, filters
│   ├── Modules/
│   │   ├── Pages/                           # REFERANS MODÜL (statik sayfalar) — şablon
│   │   └── Blog/                            # REFERANS MODÜL (CRUD) — şablon
│   ├── Views/
│   │   ├── admin/
│   │   │   ├── layout.php                   # panel iskeleti (sidebar, navbar)
│   │   │   └── components/                  # admin partial'ları
│   │   └── web/
│   │       ├── layout.php                   # frontend iskelet (head, SEO, footer)
│   │       └── components/                  # card, hero, button, form-field vb.
│   └── Language/                            # tr, de (i18n)
├── public/
│   ├── index.php
│   └── css/style.css                        # derlenmiş Tailwind+DaisyUI (build çıktısı)
├── resources/
│   └── css/input.css                        # Tailwind giriş dosyası
├── writable/
│   └── database/cekirdek.db                 # SQLite (web root dışı/korumalı)
├── spark                                    # CLI
├── tailwind.config.js
├── package.json
├── composer.json
├── .env.example
├── README.md
└── docs/                                    # AJAN SÖZLEŞME DOSYALARI (bkz. Bölüm 7)
    ├── CLAUDE.md  (kökte AGENTS.md/CLAUDE.md olarak da)
    ├── ARCHITECTURE.md
    ├── CONVENTIONS.md
    ├── UI-KIT.md
    └── RECIPES.md
```

---

## 5. BACKEND ÇEKİRDEĞİ — İNŞA EDİLECEK PARÇALAR

### 5.1 Base Class'lar
- **BaseModel:** `useTimestamps`, `useSoftDeletes`, ortak query helper'ları. Bağlantıda SQLite pragma'ları açılır: `PRAGMA foreign_keys=ON`, `PRAGMA journal_mode=WAL`.
- **BaseAdminController:** Constructor'da auth kontrolü (giriş yoksa login'e yönlendir), RBAC yetki kontrolü, admin layout yükleme. Tüm admin controller'lar bundan türer; güvenlik kodu tekrar yazılmaz.
- **BaseApiController:** `respond($data, $status)`, `respondError($msg, $code)` ile standart JSON zarfı.
- **BaseWebController:** SEO meta enjeksiyonu, frontend layout, dil seçimi.

### 5.2 Auth + RBAC
Login/logout, şifre hash'leme, session, basit rol-yetki (admin/editor gibi). Referans olarak `gilangheavy/CI4-StarterPanel` incelenebilir; ancak olduğu gibi alınmaz — sadeleştirilip Core'a uyarlanır. Demo/örnek kod alınmaz.

### 5.3 Settings Sistemi
DB tabanlı key-value. Nokta notasyon: `site.title`, `mail.smtp_host`. `setting('site.title')` helper'ı + admin panelde düzenleme ekranı.

### 5.4 Media Manager
Upload, otomatik resize/thumbnail, basit medya kütüphanesi (admin panelden).

### 5.5 Migration Altyapısı
SQLite uyarısı: `ALTER TABLE` kısıtlıdır. Migration'lar baştan doğru kurulmalı. Core tabloları: `users`, `roles`, `settings`, `media`. Referans modüller kendi migration'larını taşır.

### 5.6 Eksik Çekirdek Parçaları (hepsi dahil edilecek)
- Mail katmanı (CI4 Email + SMTP, `.env`'den config)
- Tekrar kullanılabilir form validation kuralları
- Hazır error/404/maintenance sayfaları (Tailwind temalı)
- SEO partial'ı (meta, OpenGraph, sitemap, robots)
- Basit cache layer (file-based; Redis yok varsayımı)
- `spark db:backup` (SQLite dosyasını kopyalar)
- i18n altyapısı (TR + DE dil dosyaları)

---

## 6. FRONTEND ÇEKİRDEĞİ — TAILWIND + DAISYUI

### 6.1 Build Pipeline
- `resources/css/input.css` → Tailwind giriş.
- `tailwind.config.js` → `content` yolları tüm view'ları taramalı (purge için).
- `package.json` script'leri: `build` (minify), `watch`.
- Çıktı: `public/css/style.css`. **Sunucuda Node çalışmaz; CSS lokalde derlenir, ZIP'e dahil edilir.**

### 6.2 DaisyUI Custom Theme (Cüneyt paleti)
DaisyUI custom theme tanımla:
- `primary` / navy: `#1B2D42`
- `base`/arka plan cream: `#F5F0E8`
- `accent` amber: `#E07B39`
- Karanlık/aydınlık varyantları DaisyUI değişkenleriyle.
> Not: Bu palet **varsayılan**. Gelecekte her proje UI-KIT.md üzerinden override edebilir.

### 6.3 Component Sistemi (token tasarrufunun kalbi)
Tekrar kullanılabilir view component'leri + bir `component()` helper'ı yaz. Ajan sıfırdan Tailwind dizmek yerine `component('card', [...])` çağırsın. En az şunlar hazır gelsin: `button`, `card`, `hero`, `navbar`, `footer`, `form-field`, `alert`, `section`, `cta`. Hepsi DaisyUI + custom theme ile.

---

## 7. AJAN SÖZLEŞME DOSYALARI — `docs/` (mutlaka üret)

Bu dosyalar çekirdeğin "beyni"dir; gelecekteki ajanın token harcamadan doğru iş yapmasını sağlar.

### 7.1 `CLAUDE.md` (ayrıca kökte `AGENTS.md`)
- Stack özeti, mutlak kurallar ("Core'a dokunma, Modules'ta çalış").
- `component()` kullanımı, base class'lardan türeme zorunluluğu.
- **Yeni proje intake akışına yönlendirme** (Bölüm 8'deki soru-cevap protokolü buraya gömülür).

### 7.2 `ARCHITECTURE.md`
Klasör yapısı + neyin nerede olduğu. Ajan keşif için dosya gezmesin.

### 7.3 `CONVENTIONS.md`
- Core/Modules sınırı.
- İsimlendirme: tablo (çoğul snake_case) · model (tekil + `Model`) · controller (çoğul + `Controller`) · route (kebab-case) · settings (nokta notasyon).
- Tüm admin controller → `BaseAdminController`; tüm model → `BaseModel`.
- Yeni modül = `Pages` veya `Blog` modülünü şablon al, isimleri değiştir.
- Settings erişimi yalnız `SettingsService`/`setting()` üzerinden.

### 7.4 `UI-KIT.md`
Renk token'ları (navy/cream/amber), mevcut component listesi ve kullanımı, spacing, typography. Gelecekte proje bazlı override edilir. Stitch (stitch.withgoogle.com) tasarım bağlantısı verildiğinde nasıl yorumlanacağına dair not.

### 7.5 `RECIPES.md`
Adım-adım reçeteler: "Yeni CRUD modülü ekle", "Yeni statik sayfa ekle", "Settings'e alan ekle", "Yeni dil ekle", "Yeni component ekle".

---

## 8. GELECEKTEKİ PROJE INTAKE AKIŞI (çekirdeğe gömülecek protokol)

`CLAUDE.md` içine, gelecekte yeni proje başlatılırken ajanın izleyeceği şu protokolü yaz:

> **Yeni Proje Intake Protokolü**
> Kullanıcı yeni proje brief'i verdiğinde, KOD YAZMADAN ÖNCE şunları netleştir (eksikse sor):
> 1. **Proje tipi & amaç** (tanıtım sitesi, blog, katalog, panel ağırlıklı uygulama...)
> 2. **Sayfa/ekran listesi** (frontend + admin)
> 3. **İçerik modelleri** (hangi CRUD'lar? alanları neler?)
> 4. **Diller** (TR/DE/...?)
> 5. **Tasarım dili:** Stitch linki var mı? Renk paleti override? Ton (kurumsal/oyunbaz/minimal)?
> 6. **Özel entegrasyon** (mail formu, harita, ödeme, harici API?)
> 7. **Admin yetki seviyeleri**
>
> Eksik veya belirsiz her madde için TEK TEK soru sor. Cevaplar netleşince:
> - UI-KIT.md'yi proje paletine göre güncelle (override).
> - `Pages`/`Blog` referans modüllerini şablon alarak gerekli modülleri üret.
> - SADECE `app/Modules/` altında çalış; `app/Core/`'a dokunma.
> - Tüm görseli `component()` ve DaisyUI ile kur; sıfırdan Tailwind dizme.

---

## 9. İNŞA ÖNCESİ SORU-CEVAP (şimdi, bana sor)

Çekirdeği inşa etmeye **başlamadan önce** aşağıdaki konularda bana net soru sor (varsayım yapma):

1. **Auth kapsamı:** Tek admin mi, çok kullanıcılı + roller mi? Hangi roller (admin, editor)?
2. **Referans modüller:** `Pages` + `Blog` yeterli mi, yoksa `Contact` (form→mail) gibi üçüncü bir örnek de istiyor musun?
3. **Media:** Resize/thumbnail boyutları için varsayılan setlerin ne olsun?
4. **Çoklu dil:** Çekirdek baştan iki dilli (TR+DE) mi gelsin, yoksa altyapı hazır ama tek dil aktif mi?
5. **Admin panel temeli:** `CI4-StarterPanel`'i gerçekten temel alayım mı, yoksa daha temiz/sıfırdan minimal bir panel mi tercih edersin?
6. **Versiyonlama:** Bu çekirdeği gelecekte GitHub template mı, Composer package mı, yoksa ikisi birden olarak mı kullanacaksın? (composer.json'u ona göre kurayım.)
7. **Frontend başlangıç:** Çekirdek boş bir landing ile mi gelsin, yoksa demo bir anasayfa + örnek sayfalarla mı?

Bu soruları sor, cevaplarımı bekle. Sonra **adım adım, açıklayarak** çekirdeği inşa et. Her büyük adımdan sonra ne yaptığını özetle.

---

## 10. ÇIKTI BEKLENTİSİ

- Çalışan bir CI4 + SQLite uygulaması: admin panel girişi, ayarlar ekranı, medya, `Pages` + `Blog` referans modülleri çalışır halde.
- Derlenebilir Tailwind+DaisyUI pipeline'ı + custom theme.
- Eksiksiz `docs/` sözleşme dosyaları.
- `php spark setup` ile sıfırdan kurulum komutu.
- `README.md`: kurulum, build, deploy (Hostinger ZIP) adımları.
- Temiz `.gitignore` (writable/db, node_modules, vendor, .env hariç tutulsun).

İnşa bittiğinde, bu repo GitHub'a `cekirdekcms` olarak push edilmeye hazır olacak.

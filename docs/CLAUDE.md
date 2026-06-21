# CekirdekCMS — Ajan Sözleşmesi

Bu dosya CekirdekCMS projesini devralan her yapay zeka ajanı için **birincil başvuru kaynağıdır**.
Kod yazmadan önce bu dosyayı oku. Diğer `docs/` dosyalarını gerektiğinde oku.

---

## STACK (kesin, değiştirme)

| Katman      | Teknoloji                                      |
|-------------|------------------------------------------------|
| Backend     | CodeIgniter 4 (PHP 8.2+)                       |
| Veritabanı  | SQLite3 (`writable/database/cekirdek.db`)      |
| Frontend    | Tailwind CSS v3 + DaisyUI v4                   |
| CSS build   | `npm run build` → `public/css/style.css`       |
| i18n        | CI4 native Language (TR / DE / EN)             |
| Mail        | CI4 native Email (SMTP, DB'den config)         |
| Dağıtım     | Shared hosting ZIP (Hostinger Business)        |

---

## MUTLAK KURALLAR

1. **`app/Core/` asla değiştirilmez.** Tüm proje işi `app/Modules/` altında yapılır.
2. **Tüm admin controller'lar** `BaseAdminController`'dan türetilir.
3. **Tüm model'lar** `BaseModel`'dan türetilir (soft delete, WAL, timestamps dahil).
4. **Görsel çalışma** `component()` ve DaisyUI ile yapılır — sıfırdan Tailwind yazılmaz.
5. **Settings'e erişim** yalnızca `setting('key')` helper'ı veya `SettingsService` üzerinden.
6. **SQLite ALTER TABLE** kısıtlıdır: kolon silme/adını değiştirme yapma. Şemaları baştan doğru kur.

---

## HIZLI REFERANS

### Helper'lar

```php
setting('site.title')                    // DB ayarını oku
setting('mail.smtp_host', 'localhost')   // varsayılanla oku

component('card', ['title' => '...'])    // web component yükle
admin_component('flash')                 // admin component yükle
seo_tags($seoData)                       // SEO meta tagları render et
media_url($mediaRow, 'thumb')            // medya URL'i al (original|thumb|medium|large)
slug('Türkçe Başlık')                    // TR/DE duyarlı slug üret
```

### Base class kullanımı

```php
// Web controller
class MyController extends BaseWebController {
    public function index(): string {
        $this->setSeo(['title' => 'Sayfa Başlığı', 'description' => '...']);
        return $this->render('web/my-view', ['data' => $data]);
    }
}

// Admin controller
class MyAdminController extends BaseAdminController {
    public function index(): string {
        $this->requirePermission('mymodule.view'); // opsiyonel
        return $this->render('admin/my-view', ['pageTitle' => '...', 'data' => $data]);
    }
}

// API controller
class MyApiController extends BaseApiController {
    public function show(int $id): ResponseInterface {
        return $this->respond(['id' => $id]);
        // veya: $this->respondError('Not found', 404);
    }
}
```

### Modül ve route ekleme

Her modül `app/Modules/MyModule/module.json` dosyasıyla keşfedilir. Normal yeni modül eklerken `app/Config/Routes.php`, `app/Config/Autoload.php`, `composer.json` veya admin layout değiştirme.

`routePriority` küçükten büyüğe yüklenir. Catch-all route içeren Pages modülü gibi modüller yüksek öncelik değeriyle en sona bırakılır.

### Migration

```bash
php spark migrate --all    # Tüm namespace'leri tarar (Core + Modules)
php spark db:seed InitialDataSeeder
php spark setup            # İlk kurulum (tek komut)
php spark db:backup        # SQLite yedek al
```

---

## YENI PROJE INTAKE PROTOKOLÜ

Yeni bir proje brief'i aldığında **KOD YAZMADAN ÖNCE** şunları netleştir:

1. **Proje tipi & amaç** — tanıtım sitesi, blog, katalog, panel ağırlıklı uygulama?
2. **Sayfa/ekran listesi** — frontend + admin hangi ekranlar olacak?
3. **İçerik modelleri** — hangi CRUD'lar? Alanları neler?
4. **Dil(ler)** — TR/DE/EN hangisi aktif? Hepsi mi?
5. **Tasarım dili** — Stitch linki var mı? Renk paleti override? Ton (kurumsal/oyunbaz/minimal)?
6. **Özel entegrasyon** — mail formu, harita, ödeme, harici API?
7. **Admin yetki seviyeleri** — tek admin mi, çok kullanıcılı mı?

Her eksik/belirsiz madde için **TEK TEK soru sor**. Cevaplar netleşince:

- `docs/UI-KIT.md`'yi proje paletine göre güncelle.
- `Pages` veya `Blog` modülünü şablon alarak gerekli modülleri üret.
- **Yalnızca** `app/Modules/` altında çalış. `app/Core/`'a dokunma.
- Tüm görseli `component()` ve DaisyUI ile kur.

---

## NEREDE NE VAR

| Dosya/Klasör                          | Ne içeriyor                              |
|---------------------------------------|------------------------------------------|
| `app/Core/Controllers/Base*.php`      | Tüm controller'ların türetileceği base'ler |
| `app/Core/Auth/`                      | Login, logout, session, RBAC             |
| `app/Core/Settings/SettingsService.php` | DB key-value ayarlar                   |
| `app/Core/Media/MediaService.php`     | Upload, resize, library                  |
| `app/Core/Mail/MailService.php`       | SMTP mail gönderme                       |
| `app/Helpers/cekirdek_helper.php`     | setting(), component(), slug() vb.       |
| `app/Modules/Pages/`                  | Referans modül: statik sayfalar          |
| `app/Modules/Blog/`                   | Referans modül: CRUD blog                |
| `app/Modules/Contact/`                | Referans modül: form → mail              |
| `app/Views/web/components/`           | 9 frontend component                     |
| `app/Views/admin/`                    | Admin panel layout + views               |
| `app/Database/Migrations/`            | Core tablo migration'ları                |
| `app/Database/Seeds/`                 | InitialDataSeeder                        |
| `docs/`                               | Bu sözleşme dosyaları                    |

Daha fazla detay için: `ARCHITECTURE.md`, `CONVENTIONS.md`, `UI-KIT.md`, `RECIPES.md`

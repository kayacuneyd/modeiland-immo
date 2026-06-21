# ARCHITECTURE.md — CekirdekCMS Mimari Rehberi

## Klasör Yapısı

```
cekirdekcms/
├── app/
│   ├── Config/               CI4 config (Autoload, Database, Filters, Routes)
│   ├── Helpers/
│   │   └── cekirdek_helper.php   setting(), component(), slug(), media_url()
│   ├── Database/
│   │   ├── Migrations/       Core tabloları (roles, users, settings, media)
│   │   └── Seeds/
│   │       └── InitialDataSeeder.php
│   ├── Core/                 namespace App\Core — DEĞİŞMEZ
│   │   ├── Controllers/
│   │   │   ├── BaseWebController.php     frontend controller base
│   │   │   ├── BaseAdminController.php   admin auth + layout
│   │   │   ├── BaseApiController.php     JSON response helpers
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── SettingsAdminController.php
│   │   │   ├── MediaAdminController.php
│   │   │   ├── SitemapController.php     sitemap.xml + robots.txt
│   │   │   └── HomeController.php        demo landing
│   │   ├── Models/
│   │   │   └── BaseModel.php             soft delete, WAL, timestamps
│   │   ├── Auth/
│   │   │   ├── AuthController.php        login/logout
│   │   │   ├── AuthModel.php             kullanıcı sorgulama
│   │   │   ├── AuthService.php           session, RBAC, can()
│   │   │   └── AuthFilter.php            CI4 filter (auth guard)
│   │   ├── Settings/
│   │   │   └── SettingsService.php       DB key-value, cache
│   │   ├── Media/
│   │   │   └── MediaService.php          upload, resize, library
│   │   ├── Mail/
│   │   │   └── MailService.php           SMTP mail gönderme
│   │   ├── Modules/
│   │   │   └── ModuleRegistry.php        module.json discovery
│   │   ├── Config/
│   │   │   └── Routes.php               core rotalar
│   │   └── Commands/
│   │       ├── Setup.php                php spark setup
│   │       └── DbBackup.php             php spark db:backup
│   ├── Modules/              namespace App\Modules — Proje işi burada
│   │   ├── Pages/            Referans modül: statik sayfalar
│   │   ├── Blog/             Referans modül: CRUD blog
│   │   └── Contact/          Referans modül: form → mail
│   ├── Views/
│   │   ├── web/
│   │   │   ├── layout.php               frontend iskelet
│   │   │   ├── home.php                 demo landing
│   │   │   └── components/              9 component + seo
│   │   ├── admin/
│   │   │   ├── layout.php               admin panel iskelet
│   │   │   ├── login.php
│   │   │   ├── dashboard.php
│   │   │   ├── settings/
│   │   │   ├── media/
│   │   │   └── components/              page-header, flash, stat-card
│   │   └── errors/html/                 özel 404/500 sayfaları
│   └── Language/
│       ├── tr/   (Auth, Common, Blog, Contact, Validation)
│       ├── de/
│       └── en/
├── public/
│   ├── index.php             CI4 giriş noktası
│   ├── css/style.css         derlenmiş Tailwind+DaisyUI (git'e dahil)
│   └── uploads/              medya dosyaları (web erişilebilir)
├── resources/css/input.css   Tailwind giriş dosyası
├── writable/
│   ├── database/cekirdek.db  SQLite veritabanı (web root dışı)
│   ├── backups/              DB yedekleri
│   ├── cache/                CI4 dosya cache
│   └── logs/                 CI4 logları
├── docs/                     Ajan sözleşme dosyaları
├── tailwind.config.js
├── package.json
└── composer.json
```

## İstek Yaşam Döngüsü

```
HTTP İsteği
    → public/index.php (CI4 başlatma)
    → app/Config/Routes.php (+ Core + Modül route dosyaları)
    → Filters (AuthFilter, MaintenanceFilter vb.)
    → Controller (BaseAdminController / BaseWebController türevi)
        → Model (BaseModel türevi, SQLite)
        → Service (SettingsService, MediaService, MailService)
        → View (layout → component → içerik)
    → HTTP Yanıt
```

## Veritabanı Şeması

### Core tabloları

**roles**
- id, name, slug, permissions (JSON TEXT), created_at, updated_at

**users**
- id, name, email, password_hash, role_id (FK roles.id), is_active
- created_at, updated_at, deleted_at (soft delete)

**settings**
- id, key (UNIQUE, nokta notasyon: `site.title`), value, group, type (string|bool|int|json), label
- created_at, updated_at

**media**
- id, filename, original_name, path, mime_type, size, width, height, alt_text
- created_at, updated_at, deleted_at

### Modül tabloları

**pages** — Pages modülü
- id, title, slug, lang, content, meta_title, meta_description, status, sort_order, media_id
- created_at, updated_at, deleted_at — UNIQUE(slug, lang)

**categories** — Blog modülü
- id, name, slug, lang, created_at, updated_at — UNIQUE(slug, lang)

**posts** — Blog modülü
- id, title, slug, lang, excerpt, content, category_id, media_id
- status, published_at, meta_title, meta_description
- created_at, updated_at, deleted_at — UNIQUE(slug, lang)

**contact_messages** — Contact modülü
- id, name, email, subject, message, ip_address, is_read, created_at, updated_at

## RBAC Sistemi

- `admin` rolü: `permissions = ["*"]` → tam yetki
- `editor` rolü: `permissions = ["pages.*","blog.*","media.*"]`
- `AuthService::can('pages.edit')` — wildcard desteği ile kontrol
- `BaseAdminController::requirePermission('pages.create')` — controller'da kullan

## Modül Anatomi

Her modül kendi klasörünün içinde self-contained:

```
app/Modules/MyModule/
├── module.json
├── Config/Routes.php
├── Controllers/
│   ├── MyController.php         (extends BaseWebController)
│   └── Admin/MyAdminController.php  (extends BaseAdminController)
├── Models/MyModel.php           (extends BaseModel)
├── Database/Migrations/
│   └── 2024-01-01-000050_CreateMyTable.php
└── Views/
    ├── index.php
    ├── show.php
    └── admin/
        ├── index.php
        └── edit.php
```

`module.json`, route dosyası ve admin menü kaydını tanımlar:

```json
{
  "name": "MyModule",
  "slug": "mymodule",
  "enabled": true,
  "routes": "Config/Routes.php",
  "routePriority": 50,
  "adminMenu": {
    "label": "My Module",
    "url": "admin/my-module",
    "icon": "M4 6h16M4 12h16M4 18h16",
    "order": 50
  }
}
```

Admin menüde düz metin için `label`, dil dosyası anahtarı için `labelKey` kullanılır.

Modülü silmek = klasörü silmek. Merkezi route, autoload, composer veya admin layout dosyasında normalde ek kayıt yoktur.

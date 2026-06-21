# CONVENTIONS.md — CekirdekCMS Kodlama Kuralları

## Core / Modules Sınırı

| Soru                                           | Cevap          |
|------------------------------------------------|----------------|
| Bunu bir sonraki projede de aynen kullanacak mısın? | Core (app/Core/) |
| Projeye özel mi?                               | Module (app/Modules/) |

`app/Core/` hiçbir zaman değiştirilmez. Tüm proje kodu `app/Modules/` altındadır.

---

## İsimlendirme Kuralları

### Veritabanı Tabloları
- **Çoğul snake_case**: `contact_messages`, `blog_posts`, `product_variants`
- Örnek: `pages`, `posts`, `categories`, `media`, `users`, `roles`, `settings`

### Model'lar
- **Tekil + Model**: `PageModel`, `PostModel`, `ContactMessageModel`
- Dosya: `app/Modules/Blog/Models/PostModel.php`
- Namespace: `App\Modules\Blog\Models\PostModel`

### Controller'lar
- **Modül adı + Controller**: `BlogController`, `PagesController`, `ContactController`
  - Public controller modül adını taşır (tekil veya çoğul — modül adına bağlı): `Blog` → `BlogController`, `Pages` → `PagesController`
- Admin: `BlogAdminController` → `app/Modules/Blog/Controllers/Admin/`
- Core: `AdminDashboardController` → `app/Core/Controllers/`

### Route'lar
- **kebab-case**: `/blog-posts`, `/contact-form`, `/admin/site-settings`
- İngilizce değil: `/admin/sayfalar` yerine `/admin/pages`

### View Dosyaları
- snake_case: `admin/edit.php`, `admin/index.php`
- Component dosyaları kebab-case: `form-field.php`, `stat-card.php`, `page-header.php`

### Settings Anahtarları
- **Nokta notasyon, grup.anahtar**: `site.title`, `mail.smtp_host`, `seo.robots_txt`
- Grup adları: `general`, `mail`, `seo`, `footer`, `contact`
- Yeni grup: `InitialDataSeeder`'a ekle

### Migration Dosyaları
- `YYYY-MM-DD-NNNNNN_VerbNounTable.php`
- Örnek: `2024-01-01-000050_CreateProductsTable.php`
- Numara aralıkları: Core=0001-0009, Pages=0010-0019, Blog=0020-0029, Contact=0030-0039

### Dil Anahtarları
- `Common.save`, `Blog.read_more`, `Auth.login_title`
- Dosya: `app/Language/{tr|de|en}/{Common|Auth|Blog|Contact|Validation}.php`

---

## Her Zaman Uyulacak Kurallar

### Controller'lar
```php
// Admin controller — MUTLAKA BaseAdminController'dan türet
class MyAdminController extends BaseAdminController { ... }

// Web controller — MUTLAKA BaseWebController'dan türet
class MyController extends BaseWebController { ... }
```

### Model'lar
```php
// MUTLAKA BaseModel'dan türet (soft delete, WAL, timestamps gelir)
class MyModel extends BaseModel {
    protected $table      = 'my_table';
    protected $allowedFields = ['name', 'slug', ...];
}
```

### View render etme
```php
// Admin controller'da:
return $this->render('App\Modules\MyModule\Views\admin\index', [
    'pageTitle' => 'Başlık',
    'items'     => $items,
]);

// Web controller'da:
return $this->render('App\Modules\MyModule\Views\index', ['data' => $data]);
```

### Settings'e erişim
```php
// DOĞRU:
$title = setting('site.title');
$port  = setting('mail.smtp_port', 587);

// YANLIŞ — direkt DB sorgusu yapma:
$db->table('settings')->where('key', 'site.title')->get(); // ❌
```

### Component kullanımı
```php
// DOĞRU:
<?= component('card', ['title' => '...', 'body' => '...']) ?>
<?= component('form-field', ['name' => 'email', 'type' => 'email', 'required' => true]) ?>

// YANLIŞ — sıfırdan Tailwind yazma:
<div class="bg-white rounded-xl p-4 shadow..."> ... </div>  // ❌
```

### Yeni modül ekleme
1. `app/Modules/MyModule/` klasörünü oluştur
2. `Pages` veya `Blog` modülünü şablon al, isimler değiştir
3. Migration'ı `app/Modules/MyModule/Database/Migrations/` altına yaz
4. `module.json` ekle ve `routes`, `routePriority`, `adminMenu` alanlarını doldur
5. `php spark migrate --all` çalıştır

Normal modül eklemede `app/Config/Autoload.php`, `composer.json`, `app/Config/Routes.php` ve admin layout değiştirilmez. Pages gibi catch-all route içeren modüllerde yüksek `routePriority` kullanılır.

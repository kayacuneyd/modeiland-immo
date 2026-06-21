# RECIPES.md — CekirdekCMS Adım Adım Reçeteler

## 1. Yeni CRUD Modülü Ekleme

`Blog` modülünü şablon al:

```bash
cp -r app/Modules/Blog app/Modules/Products
```

Sonra:
1. `app/Modules/Products/` içindeki tüm dosyalarda `Blog` → `Products`, `Post` → `Product`, `posts` → `products` yap
2. Migration dosyasını düzenle (tablo adı, alanlar)
3. Numarayı güncelle: `2024-01-01-000050_CreateProductsTable.php`
4. `app/Modules/Products/module.json` oluştur:
```json
{
  "name": "Products",
  "slug": "products",
  "enabled": true,
  "routes": "Config/Routes.php",
  "routePriority": 40,
  "adminMenu": {
    "label": "Products",
    "url": "admin/products",
    "icon": "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
    "order": 40
  }
}
```
5. `php spark migrate --all` çalıştır

Merkezi `app/Config/Routes.php`, `app/Config/Autoload.php`, `composer.json` ve `app/Views/admin/layout.php` dosyalarına normal modül ekleme sırasında dokunma. Route ve sidebar keşfi `module.json` üzerinden otomatik yapılır.

---

## 2. Yeni Statik Sayfa Ekleme

1. Admin panele gir → Sayfalar → Yeni Sayfa
2. Başlık ve içerik gir, status = `published` seç
3. Slug otomatik oluşur: `hakkimizda`
4. `/{slug}` URL'inde görünür (Pages modülü catch-all yönlendirir)

Programatik ekleme (seed içinde):
```php
db_connect()->table('pages')->insert([
    'title'   => 'Hakkımızda',
    'slug'    => 'hakkimizda',
    'lang'    => 'tr',
    'content' => '<p>İçerik...</p>',
    'status'  => 'published',
]);
```

---

## 3. Yeni Setting Alanı Ekleme

1. `app/Database/Seeds/InitialDataSeeder.php`'deki `$settings` dizisine ekle:
```php
['key' => 'mymodule.api_key', 'value' => '', 'group' => 'mymodule', 'type' => 'string', 'label' => 'API Anahtarı'],
```

2. Yeni grubu `app/Views/admin/settings/index.php`'deki `$groupLabels` dizisine ekle:
```php
'mymodule' => 'MyModule Ayarları',
```

3. Kullanmak için:
```php
$apiKey = setting('mymodule.api_key');
```

4. `php spark db:seed InitialDataSeeder` ile mevcut DB'ye ekle (idempotent).

---

## 4. Yeni Dil Ekleme

Örnek: Fransızca (fr) eklemek için:

1. `app/Language/fr/` dizini oluştur
2. `tr/` içindeki tüm dosyaları kopyalayıp fransızcaya çevir
3. `app/Config/App.php`'deki `$supportedLocales`'e ekle:
```php
public array $supportedLocales = ['tr', 'de', 'en', 'fr'];
```
4. Blog ve Contact route'larına FR prefix'li route ekle:
```php
$routes->get('fr/blog', '...');
```
5. Navbar `$navLinks`'i locale'e göre dinamik yap (gerekirse)

---

## 5. Yeni Frontend Component Ekleme

1. `app/Views/web/components/` altına dosya oluştur: `testimonial.php`
2. Parametreler `$name`, `$quote` gibi:
```php
<?php
$name  = $name  ?? '';
$quote = $quote ?? '';
?>
<figure class="...">
    <blockquote><?= esc($quote) ?></blockquote>
    <figcaption><?= esc($name) ?></figcaption>
</figure>
```
3. Kullan:
```php
<?= component('testimonial', ['name' => 'Ahmet Bey', 'quote' => '...']) ?>
```
4. `docs/UI-KIT.md`'ye kullanım örneğini ekle
5. `npm run build` ile CSS'i yeniden derle (yeni Tailwind class'ları taranır)

---

## 6. Hostinger'a ZIP Deploy

### Dahil edilecekler (ZIP'e ekle)
```
app/
public/
vendor/
spark
composer.json
composer.lock
.htaccess (public/ içindeki)
.env (production değerleriyle dolu)
```

### Dahil edilmeyecekler
```
node_modules/
.git/
writable/database/*.db  ← sunucuda yeni oluşacak
writable/cache/
writable/logs/
resources/
tailwind.config.js
package.json
package-lock.json
BUILD_PROMPT.md
```

### Adımlar
1. Yerel makinede CSS derle: `npm run build`
2. `public/css/style.css` ZIP'e dahil edilmeli
3. ZIP oluştur, Hostinger File Manager'a yükle, çıkar
4. `.env` dosyasını düzenle: `CI_ENVIRONMENT = production`, `app.baseURL`
5. SSH ile: `php spark setup` (migration + seed + admin kullanıcı)
6. CI4'ün `public/` klasörünü document root olarak ayarla

---

## 7. CSS Güncelleme (Tailwind Rebuild)

Sunucuda Node.js çalışmaz. Lokal makinede:

```bash
# Geliştirme sırasında (watch modu):
npm run watch

# Deploy öncesi (minify):
npm run build
```

`public/css/style.css` her zaman git'e commit edilir.

---

## 8. Yedek Alma

```bash
php spark db:backup
# → writable/backups/cekirdek-YYYYMMDD-HHiiss.db
```

Otomatik yedek için: sunucuda cron job ile çalıştır (Hostinger Cron Jobs paneli).

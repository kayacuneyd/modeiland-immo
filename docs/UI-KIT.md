# UI-KIT.md — CekirdekCMS Görsel Sistem

## DaisyUI Tema

Tema adı: `cekirdekcms` (`data-theme="cekirdekcms"` — layout.php'de tanımlı)

### Renk Token'ları

| Token           | Hex       | Kullanım                         |
|-----------------|-----------|----------------------------------|
| `primary`       | `#1B2D42` | Navy — başlıklar, butonlar, nav  |
| `primary-content`| `#F5F0E8` | Cream — primary üzerindeki metin |
| `secondary`     | `#2E4A6B` | Koyu mavi                        |
| `accent`        | `#E07B39` | Amber — CTA butonlar, vurgu      |
| `base-100`      | `#F5F0E8` | Cream — sayfa arka planı         |
| `base-200`      | `#EDE8DF` | Hafif gri-krem — card, section   |
| `base-300`      | `#D9D3C8` | Kenarlıklar                      |
| `base-content`  | `#1B2D42` | Ana metin rengi                  |

**Proje bazlı override:** `tailwind.config.js`'deki `cekirdekcms` tema bloğundan renkleri değiştir.
Gelecek projeler bu UI-KIT.md'yi proje paletine göre güncelleyebilir.

---

## Frontend Component'ler

Tüm component'ler `app/Views/web/components/` altında.
Kullanım: `<?= component('component-adi', ['param' => 'değer']) ?>`

### `hero`
```php
<?= component('hero', [
    'headline'    => 'Başlık metni',
    'subheadline' => 'Alt başlık / açıklama',
    'cta_label'   => 'Buton etiketi',
    'cta_href'    => site_url('hedef'),
    'cta2_label'  => 'İkinci buton (opsiyonel)',
    'cta2_href'   => '#',
    'bg_image'    => '',  // opsiyonel arka plan görseli URL
]) ?>
```

### `card`
```php
<?= component('card', [
    'title'     => 'Kart Başlığı',
    'body'      => 'Kart açıklama metni',
    'image'     => '',            // opsiyonel görsel URL
    'image_alt' => '',
    'badge'     => 'Kategori',    // opsiyonel üst badge
    'cta_label' => 'Devamını Oku',
    'cta_href'  => '#',
]) ?>
```

### `button`
```php
<?= component('button', [
    'label'   => 'Kaydet',
    'type'    => 'submit',     // submit|button
    'href'    => null,         // link olarak kullanmak için
    'variant' => 'primary',    // primary|accent|ghost|outline|error
    'size'    => 'md',         // sm|md|lg
    'full'    => false,        // w-full
]) ?>
```

### `form-field`
```php
<?= component('form-field', [
    'name'        => 'email',
    'label'       => 'E-posta',
    'type'        => 'email',    // text|email|password|textarea|select|number|tel
    'value'       => '',
    'placeholder' => '',
    'required'    => false,
    'error'       => '',         // hata mesajı
    'helper'      => '',         // yardım metni
    'options'     => [],         // type='select' için ['value' => 'label']
    'rows'        => 4,          // type='textarea' için
]) ?>
```

### `alert`
```php
<?= component('alert', [
    'type'        => 'success',   // success|error|warning|info
    'message'     => 'Kaydedildi.',
    'dismissible' => true,
]) ?>
```

### `section`
```php
<?= component('section', [
    'title'    => 'Bölüm Başlığı',
    'subtitle' => 'Açıklama metni',
    'content'  => '...HTML...',
    'align'    => 'center',    // center|left
    'bg'       => '',          // ''|'base-200'|'primary' vb.
    'id'       => '',          // anchor için
]) ?>
```

### `cta`
```php
<?= component('cta', [
    'headline'  => 'Harekete geçin',
    'body'      => 'Açıklama',
    'cta_label' => 'Başlayın',
    'cta_href'  => site_url('contact'),
    'bg'        => 'accent',   // accent|primary|base-200
]) ?>
```

### `navbar` & `footer`
Layout tarafından otomatik yüklenir. İçerik `settings` tablosundan beslenir:
- `site.title`, `site.email`, `footer.copyright`

---

## Admin Component'ler

`app/Views/admin/components/` altında.
Kullanım: `<?= admin_component('component-adi', [...]) ?>`

### `page-header`
```php
<?= admin_component('page-header', [
    'title'        => 'Sayfalar',
    'breadcrumbs'  => [['label' => 'Blog', 'href' => site_url('admin/blog')]],
    'action_label' => 'Yeni Sayfa',
    'action_href'  => site_url('admin/pages/new'),
]) ?>
```

### `flash`
Otomatik — admin layout'ta her sayfada yüklenir. Flash mesajları gösterir.

### `stat-card`
```php
<?= admin_component('stat-card', [
    'title' => 'Toplam Sayfa',
    'value' => 42,
    'desc'  => 'Yayında',
    'color' => 'primary',    // primary|accent|secondary|neutral
    'icon'  => 'M9 12h6...',  // SVG path d değeri
]) ?>
```

---

## Tipografi & Spacing

- Sayfa genişliği: `.ck-container` = `max-w-6xl mx-auto px-4 sm:px-6 lg:px-8`
- Bölüm dikey boşluk: `.ck-section` = `py-16 md:py-24`
- Font: DaisyUI/Tailwind varsayılanı (system font stack)
- Başlıklar: `font-bold text-base-content` — `text-4xl` (H1), `text-3xl` (H2), `text-xl` (H3)

---

## Stitch / Tasarım Linki Geldiğinde

Bir Stitch linki verildiğinde:
1. Ana renk paletini `tailwind.config.js`'deki `cekirdekcms` tema bloğundan override et
2. `UI-KIT.md`'deki renk tablosunu güncelle
3. Varsa tipografi veya spacing notları ekle
4. `npm run build` ile CSS'i yeniden derle

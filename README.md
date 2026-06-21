# CekirdekCMS

Yeniden kullanılabilir CodeIgniter 4 + SQLite CMS çekirdeği. Her yeni müşteri projesinin başlangıç noktası.

> Clone et → setup yap → modül ekle → yayınla.

---

## Gereksinimler

- PHP 8.1+
- SQLite3 PHP extension (`php-sqlite3`)
- Composer
- Node.js (yalnızca yerel CSS derlemesi için — sunucuda gerekmez)

---

## Kurulum

```bash
# 1. Repoyu klonla
git clone https://github.com/kullanici/cekirdekcms.git
cd cekirdekcms

# 2. PHP bağımlılıkları
composer install

# 3. CSS derle (Node gerektirir)
npm install
npm run build

# 4. İlk kurulum (tek komut)
php spark setup
```

`php spark setup` şunları yapar:
- `.env` oluşturur
- Migration'ları çalıştırır (tüm tablolar oluşur)
- Varsayılan rolleri ve ayarları seed eder
- Admin kullanıcısı oluşturur (soran form)

---

## Geliştirme

```bash
# Geliştirme sunucusu
php spark serve
# → http://localhost:8080

# CSS watch modu
npm run watch

# Yedek al
php spark db:backup
```

---

## Yapı

```
app/Core/       → Değişmez çekirdek (auth, settings, media, base classes)
app/Modules/    → Proje modülleri (Pages, Blog, Contact, ...)
app/Views/      → Layout ve component'ler
docs/           → Ajan sözleşme dosyaları (CLAUDE.md, ARCHITECTURE.md, ...)
```

**Altın kural:** `app/Core/` asla değiştirilmez. Tüm proje işi `app/Modules/` altında.

---

## Admin Panel

`/admin/login` → E-posta + şifre ile giriş.

Varsayılan rotalar:
| URL | Açıklama |
|-----|----------|
| `/admin/dashboard` | Gösterge paneli |
| `/admin/pages` | Sayfa yönetimi |
| `/admin/blog` | Blog yönetimi |
| `/admin/contact` | Mesaj yönetimi |
| `/admin/media` | Medya kütüphanesi |
| `/admin/settings` | Sistem ayarları |

---

## Yeni Modül Ekleme

`docs/RECIPES.md` dosyasına bak. Kısaca:

1. `Blog` modülünü kopyala → yeniden adlandır
2. Migration + model + controller + view + routes
3. `app/Config/Routes.php`'e require satırı ekle
4. `php spark migrate --all`

---

## Hostinger'a Deploy (ZIP)

1. Yerel: `npm run build`
2. ZIP oluştur: `vendor/`, `app/`, `public/`, `spark`, `composer.json`, `.env`
3. `node_modules/`, `.git/`, `writable/database/*.db` hariç tut
4. Hostinger File Manager'a yükle → çıkar
5. SSH: `php spark setup`
6. Document Root → `public/` klasörüne yönlendir

Detay: `docs/RECIPES.md` → Bölüm 6

---

## Dokümantasyon

| Dosya | İçerik |
|-------|--------|
| `docs/CLAUDE.md` | Ajan sözleşmesi, mutlak kurallar, intake protokolü |
| `docs/ARCHITECTURE.md` | Klasör yapısı, DB şeması, request lifecycle |
| `docs/CONVENTIONS.md` | Kodlama kuralları, isimlendirme |
| `docs/UI-KIT.md` | Renk token'ları, component listesi ve kullanımı |
| `docs/RECIPES.md` | Adım adım reçeteler |

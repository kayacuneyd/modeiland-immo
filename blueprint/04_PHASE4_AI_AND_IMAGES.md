# FAZ 4 — AI EŞLEŞTİRME & GÖRSEL PIPELINE

> Önce `00_MASTER_PROMPT.md` oku. Bu faz, AI'ı gerçek bir farklılaştırıcıya dönüştürür
> ve görselleri ölçeklenebilir hale getirir — ama shared hosting kısıtları içinde.

## Hedef
AI sadece metin yeniden yazan değil; eşleştiren, gerekçe gösteren ve seeker'a hazır
başvuru paketi üreten bir katman olur. Görseller korumalı ve performanslı sunulur.

## AI eşleştirme (matching)
- Seeker kriterleri (fiyat, oda, m², konum yarıçapı, Warmmiete tavanı, tür) ile ilanları
  eşleştir; her eşleşme için **fit skoru + tek satırlık gerekçe** üret
  (örn. "3 Zimmer, in Ihrem Budget, 8 Min. zur Uni").
- **Shared hosting gerçeği:** Ağır embedding hesabını her istekte yapma.
  - Basit/orta ölçek: kural-tabanlı skor + hafif AI gerekçesi (tek kısa çağrı) yeterli.
  - Semantik arama istenirse: embedding'leri **önceden hesapla** (ilan oluşturulurken/
    güncellenirken) ve SQLite'ta sakla; sorguda yalnızca benzerlik karşılaştır. Gerçek
    zamanlı toplu embedding hesabı YAPMA.
- AI maliyetini gözet: önbellekle, gereksiz çağrıdan kaçın, çağrı başı token sınırı koy.

## AI başvuru paketi (Pro katmanı içeriği)
- Seeker profili + ilan verisinden **özgün** Almanca başvuru/ön yazı taslağı üret.
- Belge checklist'i (örn. Schufa, gelir belgesi) — bilgi amaçlı, hukuki tavsiye değil.
- Bu özellik Seeker Pro katmanını (Faz 3 yer tutucusu) doldurur.

## Görsel pipeline (artık devreye alınıyor)
- **MVP'de sunucuda korumalı dizindeydi; şimdi Cloudflare Images / R2'ye geç.**
  - **Sadece owner izin verdiyse** (consent log'da `approved_photos`) yükle.
  - Yükleme owner onayı anında veya kısa süreli; shared hosting'de uzun process yok,
    büyük yüklemeleri parça parça veya cron destekli işle.
  - Cloudflare URL'lerini `listing_images.path` olarak sakla.
- **VPS gerektiren çözüm kurma.** Cloudflare tamamen harici servis olarak kullanılır;
  origin shared hosting kalır.
- Galeri: listing detayda lazy-load, responsive, erişilebilir (alt text).

## Çıktı / kabul kriterleri
- [ ] Seeker'a fit skoru + tek satır gerekçe ile sıralı eşleşmeler gösteriliyor.
- [ ] Embedding'ler (kullanılıyorsa) önceden hesaplanıp SQLite'ta saklanıyor; istek
      başına ağır hesap yok.
- [ ] Pro katman: AI başvuru paketi + checklist çalışıyor (özgün metin).
- [ ] Görseller yalnızca izinli olarak Cloudflare'a gidiyor; origin shared hosting'de kalıyor.
- [ ] AI maliyeti sınırlanmış (önbellek, token limiti).

## Bir sonraki faza devreden
- Featured/boost mantığı ve owner premium (Faz 5).

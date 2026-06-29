# FAZ 2 — OWNER GİRİŞİ, DAVET LİNKİ & RIZA GÜVENLİĞİ

> Önce `00_MASTER_PROMPT.md` oku. Bu faz, projenin **en güvenlik-kritik** parçasıdır.
> Spesifikasyona harfiyen uy; kendi kripto/oturum şemanı uydurma.

## Hedef
Owner'lar klasik üyelik zorunluluğu olmadan, benzersiz davet linkiyle sürtünmesizce
girer; profilini güvenceye alınca davet linki iptal olur ve magic link / şifreye geçer.
Tüm bunlar shared hosting üzerinde, güvenli ve loglanmış şekilde çalışır.

## Davet linki sistemi (uçtan uca spesifikasyon)
1. **Üret:** Owner lead oluşturulunca ≥ 32 bayt kriptografik rastgele, URL-güvenli token üret.
   Link: `/einladung/<token>`.
2. **Hash'le:** DB'de yalnızca `token_hash` (SHA-256 veya keyed hash) sakla. Ham token
   asla saklanmaz. Ziyarette geleni hashleyip ara.
3. **Süre:** Davet token'ı **60-90 gün** geçerli. Süresi dolmuş / iptal / zaten upgrade
   edilmişse reddet.
4. **İlk açılış → session:** İlk geçerli açılışta uzun ömürlü owner session cookie üret.
   Link bir **giriş kapısıdır**, tekrar kullanılan bir şifre değil.
5. **Oturumda kal:** Aynı tarayıcı tekrar geldiğinde session cookie ile otomatik panele.
6. **Upgrade davet linkini iptal eder:** Owner e-posta/telefon/şifre eklediğinde
   `invite_token_status=revoked`, `owner_status=active`, `login_method` kaydet. Bundan
   sonra magic link / şifre ile giriş.
7. **Hassas işlemlerde step-up:** E-posta/telefon değiştirme, doğrudan iletişimi açma,
   ilan silme, hesap kapatma → session geçerli olsa bile yeniden doğrulama (e-posta OTP /
   şifre) iste.
8. **İptal tetikleyicileri:** şifre kuruldu / e-posta doğrulandı / telefon doğrulandı /
   admin manuel iptal / hesap disabled / şüpheli kullanım / süre doldu.

## İki ayrı link türü (asla karıştırma)
| Tür | Amaç | Ömür | Tekrar kullanım |
|-----|------|------|------------------|
| Davet linki | İlk giriş + ilan onayı (hesap tamamlanmamış owner) | 60-90 gün | upgrade/iptale kadar |
| Magic login link | Hesap tamamlandıktan sonra güvenli giriş | 15 dk | tek kullanımlık |

## Cookie / session ayarları
- `HttpOnly: true`, `Secure: true`, `SameSite: Lax` (veya Strict), `Max-Age: 30-90 gün`.
- Session hash'i DB'de `owner_sessions`'ta tutulur; privilege değişiminde rotate et.

## Rıza pop-up'ı (yasal olarak en kritik ekran)
- **Tek blanket checkbox DEĞİL**; ayrı checkbox'lar (Almanca):
  - Eigentümer/berechtigt olduğumu onaylıyorum.
  - İlan bilgilerimin yayınlanmasına izin veriyorum.
  - Yüklenen fotoğrafların kullanımına izin veriyorum.
  - Açıklamanın AI ile yeniden yazılabileceğini kabul ediyorum.
  - İlgilenenlerin platform üzerinden iletişime geçebileceğini kabul ediyorum.
  - İlanımı her zaman düzenleyip kaldırabileceğimi biliyorum.
- **Her ilan için ayrı** yayın izni (tek seferlik genel onay değil).
- Birincil buton, zorunlu kutular işaretlenene kadar **disabled**.
- Submit'te `listing_consents`'a yaz: consent_version, accepted_at, ip_address,
  user_agent, approved_photos, approved_contact_method, approved_ai_rewrite.
- Mobilde kusursuz; focus-trap'li; "Jederzeit widerrufbar" güvencesi.

## Owner paneli (bu fazda tamamlanır)
- Üç bölge: **Meine Inserate** / **Freigabe ausstehend** / **Anfragen**.
- Üstte sakin, engellemeyen profil-tamamlama banner'ı (agresif değil; akıllı cadence Faz 5).
- Düzenle / pausieren / entfernen aksiyonları (entfernen → step-up doğrulama).

## Shared hosting notları
- Magic link ve davet süre kontrolleri **cron (Hostinger Scheduled Tasks)** ile:
  süresi dolanları `expired` işaretle, son 7 günde uyarı e-postası kuyruğa al.
- OTP/magic link e-postaları SMTP üzerinden; uzun process yok.

## Çıktı / kabul kriterleri
- [ ] Davet linki üretiliyor, hash'li saklanıyor, süreli, ilk açılışta session kuruyor.
- [ ] Upgrade sonrası davet linki otomatik iptal, magic link/şifre devrede.
- [ ] Hassas işlemler step-up doğrulama istiyor.
- [ ] Rıza pop-up'ı ilan-bazlı, ayrı checkbox'lı, log'lu çalışıyor.
- [ ] `owner_security_events` append-only doluyor; tüm güvenlik olayları loglanıyor.
- [ ] Cron tabanlı süre/iptal işleri tanımlı ve README'de belgeli.

## Bir sonraki faza devreden
- Profil-tamamlama hatırlatma sıklığı (Faz 5 cadence).

# MiU PHP Framework

Laravel veya baska buyuk bir framework kullanmadan, uzerine gelistirme yapabilecegimiz basit bir temel.

## Icerik

- Basit router
- Controller yapisi
- Dosya tabanli cache
- HTML tabanli, placeholder parse eden template sistemi
- Kucuk service container
- Admin ve site tarafinin ortak kullanacagi global servis katmani
- Session tabanli admin auth yapisi
- JSON response ve servis katmani destegi
- Degistirilebilir header ile API token auth
- MySQL odakli veritabani yapisi
- Bootstrap ve jQuery frontend bagimliliklari
- Validation ve CSRF katmani
- Veritabanindan yonetilen coklu dil destegi
- App controller ve servisleri icin buyuk oranda otomatik container cozumleme
- Rol tabanli menu ve ekran yetkilendirmesi
- Admin rol, log, dil, upload ve API dokumani ekranlari
- Temel test kosucusu ve migration baslangici

## Dokumanlar

Bu repo icin ana rehber artik sadece bu dosyadir:

- [README.md](/Users/mucahityenen/Desktop/Php-framework/README.md)
- HTML framework referansi: [dev/docs/framework-reference.html](/Users/mucahityenen/Desktop/Php-framework/dev/docs/framework-reference.html)
- Request lifecycle semasi: [dev/docs/request-lifecycle.html](/Users/mucahityenen/Desktop/Php-framework/dev/docs/request-lifecycle.html)
- View / tema semasi: [dev/docs/view-lifecycle.html](/Users/mucahityenen/Desktop/Php-framework/dev/docs/view-lifecycle.html)
- API semasi: [dev/docs/api-lifecycle.html](/Users/mucahityenen/Desktop/Php-framework/dev/docs/api-lifecycle.html)

## Klasor yapisi

- `public/` giris noktasi
- `config/` uygulama ayarlari, bootstrap ve route tanimlari
- `core/` framework cekirdegi
- `dev/` gelistirme araclari, testler, stub'lar ve teknik dokumanlar
- `app/Controllers/Site/` site controller alanlari
- `app/Controllers/Admin/` admin controller alanlari
- `app/Controllers/Api/` API controller alanlari
- `resources/views/site/` site template koku
- `resources/views/admin/` admin template koku
- `resources/views/admin/pages/login.html` admin login ekrani
- `app/Services/GlobalTools.php` ortak upload ve mail servisleri
- `app/Services/AuthService.php` admin auth servisi
- `app/Services/UserAuthService.php` site auth servisi
- `app/Services/IdentityService.php` ortak kimlik, parola ve API token servisi
- `app/Services/SystemInfoService.php` ornek servis katmani
- `core/Services/ServiceResult.php` ortak servis sonucu yapisi
- `database/schema.sql` temel veritabani semasi
- `database/seed.sql` ornek baslangic verileri
- `database/mysql-empty.sql` MySQL icin bos kurulum semasi
- `core/Orm/Models.php` hafif model katmani
- `composer.json` ve `vendor/` PHPMailer bagimliligi
- `package.json` ve `node_modules/` frontend bagimliliklari
- `public/assets/vendor/` Bootstrap ve jQuery assetleri
- `storage/cache/` cache dosyalari
- `public/uploads/` yuklenen dosyalar
- `storage/logs/` mail loglari
- `resources/views/mail/site/` site mail template koku
- `resources/views/mail/admin/` admin mail template koku

## Otomatik Cozumleme

Framework cekirdegi, `App\\Controllers\\...` ve `App\\Services\\...` altindaki siniflarin buyuk cogunlugunu reflection ile otomatik cozer.

Bu sayede yeni bir controller ya da servis eklerken cogu durumda:

- `config/bootstrap.php` dosyasina donmeniz gerekmez
- constructor bagimliliklari otomatik cozulur
- sadece `app/` ve `resources/` tarafinda gelistirmeye devam edebilirsiniz

Istisna olarak, config tabanli ozel servisler halen cekirdek baglama gerektirebilir:

- mail altyapisi
- loglama
- view servisleri
- config bagimli cekirdek servisler

API tarafinda public/protected endpoint karari da artik `config` icinden degil, controller sinifi icinden verilir.

`Core\\ApiController` kullanan bir API controller, public action'larini su sekilde tanimlayabilir:

```php
public static function publicActions(): array
{
    return ['index', 'login'];
}
```

Bu sayede yeni public API endpoint acarken `config/app.php` dosyasina donmeniz gerekmez.

## Base Katmanlar

Controller tarafinda tekrar eden sayfa akislarini toplamak icin su temel siniflar eklenmistir:

- `Core\\SiteController`
- `Core\\AdminController`
- `Core\\ApiController`

`SiteController` ve `AdminController`, servis sonucunu otomatik render/redirect akisina ceviren ortak yardimcilari sunar.

Servis tarafinda da su temel siniflar kullanilabilir:

- `Core\\Services\\BasePageService`
- `Core\\Services\\BaseApiService`

`BasePageService` ile:

- flash old input yazma
- flash validation hata yazma
- redirect success/error sonucu uretme

tek yerde toplanir.

Validation tekrarini azaltmak icin `Core\\Validation\\FormRequest` ve `app/Requests/` yapisi eklendi.

Ornekler:

- `app/Requests/Admin/AdminLoginRequest.php`
- `app/Requests/Site/SiteLoginRequest.php`
- `app/Requests/Admin/UserStoreRequest.php`
- `app/Requests/Admin/UserUpdateRequest.php`

Boylece yeni form akislarinda kurallar `app/Requests/` icinde yazilir; controller ve page service daha ince kalir.

## Make Komutlari

Yeni iskelet dosyalari olusturmak icin `dev/bin/make.php` ve composer script'leri eklenmistir.

Dogrudan PHP ile:

```bash
php dev/bin/make.php site-page Contact
php dev/bin/make.php admin-page Reports
php dev/bin/make.php api-endpoint Orders
php dev/bin/make.php service SmsService
php dev/bin/make.php request ContactFormRequest
```

Composer uzerinden:

```bash
composer make:site-page -- Contact
composer make:admin-page -- Reports
composer make:api-endpoint -- Orders
composer make:service -- SmsService
composer make:request -- ContactFormRequest
composer template:import -- --input=/tam/yol/index.html --name=modern-admin --area=admin
```

`site-page`, `admin-page`, `api-endpoint`, `service` ve `request` komutlari gerekli dosyalari `app/` ve `resources/` altinda olusturur. `site-page`, `admin-page` ve `api-endpoint` komutlari buna ek olarak `config/routes.php` icine temel route kaydini da otomatik yazar. Dinamik route ve otomatik container cozumleme ise fallback olarak calismaya devam eder. `template:import` ise frameworke dokunmadan sadece `dev/` altinda analiz cikisi uretir.

Hangi komut ne olusturur:

- `site-page Contact`
  - `app/Controllers/Site/Contact.php`
  - `app/Services/Site/ContactPageService.php`
  - `resources/views/site/pages/contact.html`
  - `config/routes.php` icine `GET /contact`
- `admin-page Reports`
  - `app/Controllers/Admin/Reports.php`
  - `app/Services/Admin/ReportsPageService.php`
  - `resources/views/admin/pages/reports.html`
  - `config/routes.php` icine `GET /admin/reports`
- `api-endpoint Orders`
  - `app/Controllers/Api/Orders.php`
  - `app/Services/Api/OrdersService.php`
  - `config/routes.php` icine `GET /api/v1/orders`
- `service SmsService`
  - `app/Services/SmsService.php`
- `request ContactFormRequest`
  - `app/Requests/ContactFormRequest.php`
- `template:import modern-admin`
  - `dev/template-breakdowns/<theme>/layouts/<theme>.html`
  - `dev/template-breakdowns/<theme>/partials/header.html`
  - `dev/template-breakdowns/<theme>/partials/sidebar.html`
  - `dev/template-breakdowns/<theme>/partials/footer.html`
  - `dev/template-breakdowns/<theme>/pages/<theme>-index.html`
  - `dev/template-breakdowns/<theme>/assets.json`
  - `dev/template-breakdowns/<theme>/summary.json`
- `composer make:migration -- create_example_table`
  - `database/migrations/<timestamp>_create_example_table.sql`

Harici bir HTML template setini parcali view yapisina cevirmek icin:

```bash
php dev/bin/import-template.php --input=/tam/yol/index.html --name=modern-admin --area=admin
```

Bu arac:
- layout dosyasi olusturur
- header / sidebar / footer gibi parcali alanlari ayirmaya calisir
- ana icerigi page dosyasina tasir
- CSS / JS / image referanslarini oldugu gibi korur
- local ve external asset referanslarini `assets.json` icinde listeler
- framework dosyalarina dogrudan import etmez; sadece `dev/template-breakdowns/` altinda analiz cikisi uretir

## View Helper'lari

Template tarafinda `resources/` icinde kalmayi kolaylastirmak icin bazi yardimci syntax'lar eklendi.

Partial include:

```html
{>partials/header}
```

Bu yapi ilgili view kokunde `partials/header.html` dosyasini ayni veri seti ile iceri alir.

## Core Yardimcilari

Core altinda static dizi yardimcisi da vardir:

```php
$city = Arr::get($payload, 'user.profile.city');
$sorted = Arr::sortByKey($items, 'order');
```

Core altinda static string yardimcisi da vardir:

```php
$slug = Str::slug('MiU Framework');
$camel = Str::camel('user_profile');
```

`config/bootstrap.php` icinde `class_alias` tanimli oldugu icin `Arr` ve `Str` icin ayri `use` yazmak zorunlu degildir.

URL helper:

```html
<a href="{url:/login}">Login</a>
<img src="{asset:/image/logo.png}" alt="Logo">
```

Ek olarak su ortak view degerleri otomatik gelir:

- `{app_url}`
- `{assets_url}`
- `{current_locale}`
- `{current_locale_name}`
- site tarafinda `{site_user_logged_in}`, `{site_user_name}`, `{site_user_email}`
- admin tarafinda `{admin_logged_in}`, `{admin_name}`, `{admin_email}`

## Debug ve Hata Yonetimi

Hata davranisi `config/app.php` icindeki `debug` bolumunden yonetilir:

```php
'debug' => [
    'enabled' => true,
    'log_file' => '',
],
```

Mantik:

- `debug.enabled = true` ise local ortamda detayli hata HTML'i veya API icin detayli JSON doner
- `debug.enabled = false` ise production icin sade hata sayfasi / sade JSON doner
- tum yakalanan beklenmeyen hatalar ayrica `storage/logs/app.log` dosyasina yazilir

## Bakim Modu

Bakim modu `config/app.php` icindeki `security.maintenance` bolumunden acilip kapatilabilir:

```php
'security' => [
    'maintenance' => [
        'enabled' => false,
        'status' => 503,
        'message' => 'Sistem gecici olarak bakimdadir. Lutfen daha sonra tekrar deneyin.',
        'retry_after' => 600,
        'allowed_paths' => [
            '/api/v1/status',
        ],
        'allowed_ips' => [],
    ],
],
```

Mantik:

- `enabled = true` oldugunda sistem bakim moduna girer
- web isteklerinde sade bir bakim sayfasi doner
- API isteklerinde standart JSON hata yapisi doner
- `Retry-After` header'i otomatik eklenir
- `allowed_paths` icindeki yollar bakim modundan etkilenmez
- `allowed_ips` icindeki IP'ler bakim modunu bypass eder

## API Standarti ve Rate Limit

API yanitlari artik ortak bir standartta doner:

```json
{
  "success": true,
  "message": "Islem basarili.",
  "data": {},
  "error": null,
  "meta": {
    "timestamp": 1234567890
  }
}
```

Hata durumunda `error.status` dolu gelir ve `meta` icinde path/method gibi bilgiler de olabilir.

Rate limit varsayilanlari:

```php
'api' => [
    'header' => 'X-Api-Token',
    'token_ttl' => 600,
    'rate_limit' => [
        'enabled' => true,
        'max_attempts' => 120,
        'decay_seconds' => 60,
        'login_max_attempts' => 10,
        'login_decay_seconds' => 60,
    ],
],
```

Mantik:

- genel API istekleri: 60 saniyede 120 istek
- login endpointleri: 60 saniyede 10 istek
- limit asildiginda `429` ve `Retry-After` header'i doner

## API Request Validation

API form ve JSON dogrulamalari da artik `app/Requests/Api/` altinda tutulur.

Ornekler:

- [AuthLoginRequest.php](/Users/mucahityenen/Desktop/Php-framework/app/Requests/Api/AuthLoginRequest.php)
- [UserCreateRequest.php](/Users/mucahityenen/Desktop/Php-framework/app/Requests/Api/UserCreateRequest.php)
- [UserUpdateRequest.php](/Users/mucahityenen/Desktop/Php-framework/app/Requests/Api/UserUpdateRequest.php)

Controller tarafinda kullanim mantigi:

```php
$validation = $this->validateApiRequest($this->createRequest, $request->all());

if ($validation->fails()) {
    return $this->apiValidationError($validation);
}
```

Bu sayede yeni API endpoint yazarken kurallar `app/Requests/Api/` icinde kalir, controller ise sade kalir.

## Rol ve Yetki Kontrolu

Yetki kontrolu artik veritabanindaki `userRole.auth` alani uzerinden calisir.

Mantik:

- `all` veya `*` tum yetkileri verir
- izinler tercihen `view`, `edit`, `delete` standardiyla tanimlanir
- `users.edit` gibi tam yetki anahtari tanimlanabilir
- `users.*` gibi wildcard tanimi kullanilabilir

Controller tarafinda action bazli yetki tanimi yapilabilir:

```php
public static function actionPermissions(): array
{
    return [
        'index' => 'users.view',
        'create' => 'users.edit',
        'delete' => 'users.delete',
    ];
}
```

Bu tanim varsa router merkezi olarak kontrol eder:

- admin tarafinda session kimligi
- site tarafinda user session kimligi
- api tarafinda token ile cozulmus kimlik

uzerinden DB rol kaydindaki yetkilere bakilir.

Template tarafinda gorunurluk icin permission bloklari da kullanilabilir:

```html
{?can:users.edit}
<a href="/admin/users">Kullanicilar</a>
{/can:users.edit}
```

Ek olarak basit truthy bloklari da desteklenir:

```html
{?admin_logged_in}
<span>{admin_name}</span>
{/admin_logged_in}

{?!site_user_logged_in}
<a href="/login">Login</a>
{/!site_user_logged_in}
```

## Calistirma

```bash
php -S localhost:8000 -t public
```

Ardindan `http://localhost:8000` adresini acin.

Apache kullaniyorsaniz [public/.htaccess](/Users/mucahityenen/Desktop/Php-framework/public/.htaccess) dosyasi temiz URL yonlendirmesini yapar. Document root `public/` olmali ve `mod_rewrite` acik olmalidir.

Eger paylasimli hostingde document root'u `public/` yapamiyorsaniz, kok dizindeki [/.htaccess](/Users/mucahityenen/Desktop/Php-framework/.htaccess) gelen istekleri `public/` altina yonlendirir ve kritik klasorlere dogrudan erisimi engeller.

## URL ayarlari

Uygulama ve asset URL'leri `config/app.php` icinden tanimlanir:

```php
'app' => [
    'active' => 'local',
    'urls' => [
        'local' => 'http://localhost:8000',
        'server' => 'https://example.com',
    ],
],
'assets' => [
    'path' => '/assets',
],
```

Aktif ortam `environment` degerine gore secilir. Asset URL'i ayrica yazilmaz; otomatik olarak `app.url + assets.path` ile olusturulur.

## Veritabani

Veritabani ayari `local` ve `server` olarak iki profile ayrildi.

Secim mantigi artik otomatik host tahmini degil, dogrudan config uzerinden yapilir.

Aktif ortam:

```php
'environment' => 'local',
```

Aktif veritabani profili:

```php
'database' => [
    'active' => 'local',
    'connections' => [
        'local' => [...],
        'server' => [...],
    ],
],
```

Local icin:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `mini_framework`
- Username: `root`
- Password: ``

Sunucuda ise:

- `environment` degerini `server`
- `database.active` degerini `server`

yapmaniz yeterli.

Desteklenen suruculer:

- `sqlite`
- `mysql`
- `pgsql`

Varsayilan MySQL ornegi:

```php
'database' => [
    'active' => 'local',
    'connections' => [
        'local' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'mini_framework',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
        'server' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'sunucu_veritabani_adi',
            'username' => 'sunucu_kullanici_adi',
            'password' => 'sunucu_sifre',
            'charset' => 'utf8mb4',
        ],
    ],
],
```

SQLite ornegi:

```php
'database' => [
    'driver' => 'sqlite',
    'sqlite_database' => dirname(__DIR__) . '/database/app.sqlite',
    'schema_file' => dirname(__DIR__) . '/database/schema.sql',
    'seed_file' => dirname(__DIR__) . '/database/seed.sql',
],
```

PostgreSQL ornegi:

```php
'database' => [
    'driver' => 'pgsql',
    'host' => '127.0.0.1',
    'port' => 5432,
    'database' => 'mini_framework',
    'username' => 'postgres',
    'password' => 'secret',
    'schema' => 'public',
],
```

## Hafif model katmani

Veritabanina bagli, bagimsizlik eklemeden kullanabileceginiz hafif bir model sinifi eklendi.

Ornek:

```php
$user = models::get('users');
$user->first_name = 'Ada';
$user->last_name = 'Lovelace';
$user->email = 'ada@example.com';
$user->status = 'active';
$user->save();
```

Bulma:

```php
$user = models::get('users');
$result = $user->find(1);
```

Altinda PDO kullanir ve tablo kolonlarini okuyup model nesnesini bellekte olusturur. Dosya uretmez.

Her `models::get()` cagrisi ayri bir model nesnesi dondurur; ust uste cagrilarda state cakismasi olmaz.

Zincir sorgu ornekleri:

```php
$users = models::get('users')
    ->where('status', 'active')
    ->orderBy('id', 'DESC')
    ->limit(10)
    ->all();
```

```php
$user = models::get('users')
    ->where('email', 'ada@example.com')
    ->first();
```

```php
$users = models::get('users')
    ->whereLike('email', 'example')
    ->orderBy('id', 'DESC')
    ->all();
```

Ek yardimcilar:

```php
$count = models::get('users')
    ->where('status', 'active')
    ->count();
```

```php
$exists = models::get('users')
    ->where('email', 'ada@example.com')
    ->exists();
```

## Mimari Not

Projede temel akis su sekildedir:

- `controller` sadece request/response ile ilgilenir
- `service` is kurallarini tasir
- `model` veri erisimini yapar

Auth mantigi ortak olarak `IdentityService` icinde toplanir. Site ve admin oturum yonetimi ise kendi kucuk session servislerinde kalir.

```php
$emails = models::get('users')
    ->pluck('email');
```

```php
$tokens = models::get('api_tokens')
    ->whereIn('type', ['mobile', 'web'])
    ->all();
```

```php
$page = models::get('users')
    ->orderBy('id', 'DESC')
    ->paginate(1, 10);
```

## Frontend bagimliliklari

Bootstrap ve jQuery npm ile kuruldu ve yerel olarak `public/assets/vendor/` altina kopyalandi.

- Bootstrap CSS: `/assets/vendor/bootstrap/css/bootstrap.min.css`
- Bootstrap JS: `/assets/vendor/bootstrap/js/bootstrap.bundle.min.js`
- jQuery: `/assets/vendor/jquery/jquery.min.js`

Kendi statik dosyalariniz icin hazir klasorler:

- `/assets/image/`
- `/assets/css/`
- `/assets/js/`

Template icinde dosyalari su sekilde cagirin:

```html
<img src="{assets_url}/image/logo.png" alt="Logo">
<link rel="stylesheet" href="{assets_url}/css/site.css">
<script src="{assets_url}/js/app.js"></script>
```

## Template tipleri

- `resources/views/site/` tamamen site tarafina aittir
- `resources/views/admin/` tamamen admin tarafina aittir
- `/` site ornek sayfasi
- `/api/v1/status` ornek JSON endpoint
- `/api/v1/secure-status` token korumali JSON endpoint
- `/admin/login` admin giris ekrani
- `/admin` admin panel ornek sayfasi

Site ve admin birbirinden su seviyelerde ayridir:

- Ayrı controller namespace
- Ayrı view/template koku
- Ayrı layout dosyalari
- Ayrı cache key isimlendirmesi

Ama ortak servisler tek yerde toplanir:

- Upload islemleri
- Mail gonderimi
- Sonradan eklenecek diger global yardimcilar

## Dinamik route yapisi

Static route tanimlari once calisir. `make:*` komutlariyla uretilen yeni sayfa ve endpointler varsayilan olarak `config/routes.php` icine de yazilir. Eger static route bulunamazsa framework path'i fallback olarak su mantikla cozer:

- ilk segment: class
- ikinci segment varsa: method
- kalan segmentler: method parametreleri

Ornek:

- `/ornek-url` -> `App\Controllers\Site\OrnekUrl::index()`
- `/ornek-url/about-us` -> `App\Controllers\Site\OrnekUrl::aboutUs()`
- `/urun/detay/15` -> `App\Controllers\Site\Urun::detay($id)`
- `/admin/raporlar/list` -> `App\Controllers\Admin\Raporlar::list()`
- `/api/v1/kullanici/liste` -> `App\Controllers\Api\Kullanici::liste()`

Eger ayni isimli method bulunamazsa sirayla su fallback denenir:

- `camelCase` method
- `index()`

## Admin auth

Admin girisi session tabanlidir ve veritabani uzerinden dogrulanir.

- E-posta: `admin@example.com`
- Sifre: `123456`

## Validation ve CSRF

Admin form akisinda temel validation ve CSRF korumasi aktiftir.

- CSRF token form icine `_token` olarak eklenir
- tum POST isteklerinde token router seviyesinde ortak dogrulanir
- Validation hatalari field bazli gosterilir

Mevcut kurallar:

- `required`
- `email`
- `min`
- `max`

## JSON ve servis katmani

Mobil uygulamalar icin controller'lar JSON donebilir. Ortak servisler `ServiceResult` ile veri dondurur, controller da bunu JSON response'a cevirir.

Ornek:

```php
return $this->jsonResult($this->systemInfo->status());
```

## API token auth

API erisimi icin header ve token degeri `config/app.php` icindeki `api` alanindan degistirilebilir.

```php
'api' => [
    'header' => 'X-Api-Token',
],
```

Varsayilan korumali endpoint:

- `GET /api/v1/secure-status`

Ornek istek:

```text
X-Api-Token: change-this-api-token
```

Bu token artik `api_tokens` tablosundan okunur.

## Placeholder yapisi

Template dosyalari duz HTML olarak yazilir. Dinamik alanlar su formatla belirtilir:

```html
<h1>{welcome_text}</h1>
```

PHP tarafinda controller bu alanlari dizi olarak gonderir:

```php
return $this->render('home.index', [
    'welcome_text' => 'Merhaba dunya',
]);
```

Controller tarafindan veri gonderilen normal template alanlari tek suslu parantez ile kullanilir:

```html
<h1>{site_title}</h1>
<p>{welcome_text}</p>
```

Ceviri icin ise cift suslu parantez kullanilir. Isterseniz anahtar yerine dogrudan metni yazabilirsiniz:

```html
<label>{{Lutfen alaninizi seciniz}}</label>
```

Bu durumda sistem:

- `translation_key` olarak `{{Lutfen alaninizi seciniz}}`
- varsayilan dilde `translation_value` olarak `Lutfen alaninizi seciniz`
- diger aktif dillerde bos `translation_value`

kayitlarini otomatik olusturur.

Dil secmek icin URL uzerinden `lang` query degeri kullanilabilir:

```text
/?lang=en
```

Dil tablolari:

- `languages`
- `language_translations`

## Mail template yapisi

Mail tarafinda da site ve admin ayri template koklerine sahiptir:

- `resources/views/mail/site/`
- `resources/views/mail/admin/`

HTML dosyalari yine placeholder ile yazilir:

```html
<h1>{title}</h1>
<p>{message}</p>
```

Gonderim:

```php
$this->globalTools->sendTemplatedMail(
    'site',
    'user@example.com',
    'Hos geldiniz',
    'pages/welcome',
    [
        'app_name' => 'MiU',
        'title' => 'Hos geldiniz',
        'message' => 'Kaydiniz alindi.',
        'button_text' => 'Panele git',
        'button_url' => 'https://example.com/panel',
        'footer_note' => 'Bu bir bilgilendirme mesajidir.',
    ]
);
```

## Mail config

Mail gonderimi artik PHPMailer uzerinden calisir ve ayarlar `config/app.php` icindeki `mail` alanindan degistirilir.

```php
'mail' => [
    'mailer' => 'smtp',
    'host' => 'smtp.example.com',
    'port' => 587,
    'username' => 'noreply@example.com',
    'password' => 'change-this-mail-password',
    'encryption' => 'tls',
    'auth' => true,
    'from_email' => 'noreply@example.com',
    'from_name' => 'MiU',
],
```

`mailer` degerini isterseniz `mail` yapip PHP `mail()` transport'una da donebilirsiniz.

## Dokumantasyon

Docblock'lardan otomatik API referansi uretmek icin:

```bash
composer docs:generate
```

Uretilen dosya:

```text
dev/docs/api-reference.md
```

## Sonraki adimlar

- Middleware sistemi
- Route parametreleri
- Request validation
- Veritabani/ORM katmani
- CLI komutlari

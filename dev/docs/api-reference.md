# API Referansi

Bu dosya `dev/bin/generate-docs.php` tarafindan otomatik uretilir.

## Icerik

- [Global Fonksiyonlar](#global-fonksiyonlar)
- [App\Controllers\Admin\DashboardController](#appcontrollersadmindashboardcontroller)
- [App\Controllers\Admin\Docs](#appcontrollersadmindocs)
- [App\Controllers\Admin\Languages](#appcontrollersadminlanguages)
- [App\Controllers\Admin\LoginController](#appcontrollersadminlogincontroller)
- [App\Controllers\Admin\Logs](#appcontrollersadminlogs)
- [App\Controllers\Admin\Roles](#appcontrollersadminroles)
- [App\Controllers\Admin\Uploads](#appcontrollersadminuploads)
- [App\Controllers\Admin\Users](#appcontrollersadminusers)
- [App\Controllers\Api\Auth](#appcontrollersapiauth)
- [App\Controllers\Api\StatusController](#appcontrollersapistatuscontroller)
- [App\Controllers\Api\Users](#appcontrollersapiusers)
- [App\Controllers\Site\HomeController](#appcontrollerssitehomecontroller)
- [App\Controllers\Site\LoginController](#appcontrollerssitelogincontroller)
- [App\Controllers\Site\OrnekUrl](#appcontrollerssiteornekurl)
- [App\Requests\Admin\AdminLoginRequest](#apprequestsadminadminloginrequest)
- [App\Requests\Admin\RoleUpdateRequest](#apprequestsadminroleupdaterequest)
- [App\Requests\Admin\UserStoreRequest](#apprequestsadminuserstorerequest)
- [App\Requests\Admin\UserUpdateRequest](#apprequestsadminuserupdaterequest)
- [App\Requests\Api\AuthLoginRequest](#apprequestsapiauthloginrequest)
- [App\Requests\Api\UserCreateRequest](#apprequestsapiusercreaterequest)
- [App\Requests\Api\UserUpdateRequest](#apprequestsapiuserupdaterequest)
- [App\Requests\Site\SiteLoginRequest](#apprequestssitesiteloginrequest)
- [App\Services\Admin\AdminLoginPageService](#appservicesadminadminloginpageservice)
- [App\Services\Admin\ApiDocsPageService](#appservicesadminapidocspageservice)
- [App\Services\Admin\DashboardPageService](#appservicesadmindashboardpageservice)
- [App\Services\Admin\LanguageManagementPageService](#appservicesadminlanguagemanagementpageservice)
- [App\Services\Admin\LogManagementPageService](#appservicesadminlogmanagementpageservice)
- [App\Services\Admin\RoleManagementPageService](#appservicesadminrolemanagementpageservice)
- [App\Services\Admin\UploadManagementPageService](#appservicesadminuploadmanagementpageservice)
- [App\Services\Admin\UserManagementPageService](#appservicesadminusermanagementpageservice)
- [App\Services\AuthService](#appservicesauthservice)
- [App\Services\AuthorizationService](#appservicesauthorizationservice)
- [App\Services\FileUploadService](#appservicesfileuploadservice)
- [App\Services\GlobalTools](#appservicesglobaltools)
- [App\Services\IdentityService](#appservicesidentityservice)
- [App\Services\Site\DynamicRouteService](#appservicessitedynamicrouteservice)
- [App\Services\Site\HomePageService](#appservicessitehomepageservice)
- [App\Services\Site\SiteLoginPageService](#appservicessitesiteloginpageservice)
- [App\Services\SystemInfoService](#appservicessysteminfoservice)
- [App\Services\UserAuthService](#appservicesuserauthservice)
- [App\Services\UserService](#appservicesuserservice)
- [Core\AdminController](#coreadmincontroller)
- [Core\ApiController](#coreapicontroller)
- [Core\Application](#coreapplication)
- [Core\Cache\CacheInterface](#corecachecacheinterface)
- [Core\Cache\FileCache](#corecachefilecache)
- [Core\Container](#corecontainer)
- [Core\Controller](#corecontroller)
- [Core\Database](#coredatabase)
- [Core\Http\ApiResponse](#corehttpapiresponse)
- [Core\Http\Request](#corehttprequest)
- [Core\Http\Response](#corehttpresponse)
- [Core\Localization\LanguageService](#corelocalizationlanguageservice)
- [Core\Logging\RequestLogger](#coreloggingrequestlogger)
- [Core\Orm\Models](#coreormmodels)
- [Core\PageController](#corepagecontroller)
- [Core\RateLimit\RateLimiter](#coreratelimitratelimiter)
- [Core\Router](#corerouter)
- [Core\Security\Csrf](#coresecuritycsrf)
- [Core\Services\BaseApiService](#coreservicesbaseapiservice)
- [Core\Services\BasePageService](#coreservicesbasepageservice)
- [Core\Services\BaseService](#coreservicesbaseservice)
- [Core\Services\ServiceResult](#coreservicesserviceresult)
- [Core\Session](#coresession)
- [Core\SiteController](#coresitecontroller)
- [Core\Validation\FormRequest](#corevalidationformrequest)
- [Core\Validation\ValidationResult](#corevalidationvalidationresult)
- [Core\Validation\Validator](#corevalidationvalidator)
- [Core\View\RawValue](#coreviewrawvalue)
- [Core\View\View](#coreviewview)

## Global Fonksiyonlar

### bootstrapApplication

- Dosya: `config/bootstrap.php`
- Aciklama: Uygulama kapsayicisini ve calisma zamani servislerini olusturur ve ayarlar.
- Imza: `bootstrapApplication(string $basePath)`
- Donus: Application
- Parametreler:
  - `$basePath` (string): Proje kok yolu.

### buildAssetUrl

- Dosya: `config/bootstrap.php`
- Aciklama: Uygulama URL'i ve asset yolu kullanarak tam asset adresini olusturur.
- Imza: `buildAssetUrl(string $appUrl, string $assetPath = '/assets')`
- Donus: string
- Parametreler:
  - `$appUrl` (string): Uygulamanin temel URL'i.
  - `$assetPath` (string): Asset kok yolu.

### resolveDatabaseConfig

- Dosya: `config/bootstrap.php`
- Aciklama: Aktif veritabani yapilandirma profilini cozer.
- Imza: `resolveDatabaseConfig(array $databaseConfig, string $environment = 'local')`
- Donus: array
- Parametreler:
  - `$databaseConfig` (array): Veritabani yapilandirma dizisi.
  - `$environment` (string): Aktif ortam adi.

### resolveDebugConfig

- Dosya: `config/bootstrap.php`
- Aciklama: Debug yapilandirmasini ortama gore cozer.
- Imza: `resolveDebugConfig(array $debugConfig, string $environment, string $basePath)`
- Donus: array
- Parametreler:
  - `$debugConfig` (array): Debug ayarlari.
  - `$environment` (string): Aktif ortam adi.
  - `$basePath` (string): Proje kok yolu.

### resolveUrlConfig

- Dosya: `config/bootstrap.php`
- Aciklama: Aktif ortam icin URL ayarini cozer.
- Imza: `resolveUrlConfig(array $urlConfig, string $environment = 'local')`
- Donus: string
- Parametreler:
  - `$urlConfig` (array): URL yapilandirma dizisi.
  - `$environment` (string): Aktif ortam adi.

## App\Controllers\Admin\DashboardController

- Dosya: `app/Controllers/Admin/DashboardController.php`
- Aciklama: Korumali admin panelini goruntuler.
- Metod sayisi: 3

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\Admin\DashboardPageService $dashboardPage)`
- Parametreler:
  - `$view` (View): Admin view goruntuleyicisi.
  - `$dashboardPage` (DashboardPageService): Dashboard servis katmani.

### actionPermissions

- Erisim: `public`
- Imza: `actionPermissions(): array`

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Dogrulanmis admin kullanicilari icin paneli gosterir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

## App\Controllers\Admin\Docs

- Dosya: `app/Controllers/Admin/Docs.php`
- Aciklama: Admin dokumantasyon sayfalarini yonetir.
- Metod sayisi: 3

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\Admin\ApiDocsPageService $docs)`

### actionPermissions

- Erisim: `public`
- Imza: `actionPermissions(): array`

### api

- Erisim: `public`
- Imza: `api(Core\Http\Request $request): Core\Http\Response`

## App\Controllers\Admin\Languages

- Dosya: `app/Controllers/Admin/Languages.php`
- Aciklama: Admin dil ve ceviri ekranlarini yonetir.
- Metod sayisi: 10

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\Admin\LanguageManagementPageService $languages)`

### actionPermissions

- Erisim: `public`
- Imza: `actionPermissions(): array`

### buildAlert

- Erisim: `private`
- Imza: `buildAlert(string $success, string $error): string`

### buildFields

- Erisim: `private`
- Imza: `buildFields(array $fields): string`

### buildRows

- Erisim: `private`
- Imza: `buildRows(array $rows): string`

### edit

- Erisim: `public`
- Imza: `edit(Core\Http\Request $request, string $id = ''): Core\Http\Response`

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`

### prepareFormData

- Erisim: `private`
- Imza: `prepareFormData(array $data): array`

### prepareIndexData

- Erisim: `private`
- Imza: `prepareIndexData(array $data): array`

### update

- Erisim: `public`
- Imza: `update(Core\Http\Request $request, string $id = ''): Core\Http\Response`

## App\Controllers\Admin\LoginController

- Dosya: `app/Controllers/Admin/LoginController.php`
- Aciklama: Admin giris ve cikis islemlerini yonetir.
- Metod sayisi: 4

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\Admin\AdminLoginPageService $loginPage)`
- Parametreler:
  - `$view` (View): Admin kimlik dogrulama view goruntuleyicisi.
  - `$loginPage` (AdminLoginPageService): Admin giris servis katmani.

### login

- Erisim: `public`
- Imza: `login(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Gonderilen bilgileri dogrular ve bir admin oturumu olusturur.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### logout

- Erisim: `public`
- Imza: `logout(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Mevcut admin kullanicisinin oturumunu kapatir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### show

- Erisim: `public`
- Imza: `show(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Admin giris formunu gosterir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

## App\Controllers\Admin\Logs

- Dosya: `app/Controllers/Admin/Logs.php`
- Aciklama: Admin log ekranlarini yonetir.
- Metod sayisi: 8

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\Admin\LogManagementPageService $logs)`

### actionPermissions

- Erisim: `public`
- Imza: `actionPermissions(): array`

### buildRows

- Erisim: `private`
- Imza: `buildRows(array $logs): string`
- Donus: string

### formatTimestamp

- Erisim: `private`
- Imza: `formatTimestamp(mixed $value): string`

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`

### prepareData

- Erisim: `private`
- Imza: `prepareData(array $data): array`
- Donus: array<string, mixed>

### prepareDetailData

- Erisim: `private`
- Imza: `prepareDetailData(array $data): array`

### show

- Erisim: `public`
- Imza: `show(Core\Http\Request $request, string $id = ''): Core\Http\Response`

## App\Controllers\Admin\Roles

- Dosya: `app/Controllers/Admin/Roles.php`
- Aciklama: Admin rol ve yetki ekranlarini yonetir.
- Metod sayisi: 10

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\Admin\RoleManagementPageService $roles)`

### actionPermissions

- Erisim: `public`
- Imza: `actionPermissions(): array`

### buildAlert

- Erisim: `private`
- Imza: `buildAlert(string $success, string $error): string`

### buildPermissionGroups

- Erisim: `private`
- Imza: `buildPermissionGroups(array $catalog, array $selected): string`
- Donus: string

### buildRows

- Erisim: `private`
- Imza: `buildRows(array $roles): string`
- Donus: string

### edit

- Erisim: `public`
- Imza: `edit(Core\Http\Request $request, string $id = ''): Core\Http\Response`

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`

### prepareFormData

- Erisim: `private`
- Imza: `prepareFormData(array $data): array`
- Donus: array<string, mixed>

### prepareIndexData

- Erisim: `private`
- Imza: `prepareIndexData(array $data): array`
- Donus: array<string, mixed>

### update

- Erisim: `public`
- Imza: `update(Core\Http\Request $request, string $id = ''): Core\Http\Response`

## App\Controllers\Admin\Uploads

- Dosya: `app/Controllers/Admin/Uploads.php`
- Aciklama: Admin upload ekranlarini yonetir.
- Metod sayisi: 7

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\AuthService $auth, App\Services\Admin\UploadManagementPageService $uploads)`

### actionPermissions

- Erisim: `public`
- Imza: `actionPermissions(): array`

### buildAlert

- Erisim: `private`
- Imza: `buildAlert(string $success, string $error): string`

### buildRows

- Erisim: `private`
- Imza: `buildRows(array $uploads, string $csrfToken, bool $canManageUploads): string`

### delete

- Erisim: `public`
- Imza: `delete(Core\Http\Request $request, string $id = ''): Core\Http\Response`

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`

### prepareData

- Erisim: `private`
- Imza: `prepareData(array $data): array`

## App\Controllers\Admin\Users

- Dosya: `app/Controllers/Admin/Users.php`
- Aciklama: Admin kullanici yonetim ekranlarini yonetir.
- Metod sayisi: 14

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\AuthService $auth, App\Services\Admin\UserManagementPageService $userPages)`
- Parametreler:
  - `$view` (View): Admin view goruntuleyicisi.
  - `$userPages` (UserManagementPageService): Kullanici yonetim servis katmani.

### actionPermissions

- Erisim: `public`
- Imza: `actionPermissions(): array`

### buildAlert

- Erisim: `private`
- Imza: `buildAlert(string $success, string $error): string`
- Aciklama: Basari veya hata mesajini HTML blok olarak olusturur.
- Donus: string
- Parametreler:
  - `$success` (string): Basari mesaji.
  - `$error` (string): Hata mesaji.

### buildRoleOptions

- Erisim: `private`
- Imza: `buildRoleOptions(array $roles, string $selected): string`
- Aciklama: Rol secim kutusu HTML seceneklerini olusturur.
- Donus: string
- Parametreler:
  - `$selected` (string): Secili rol degeri.

### buildStatusOptions

- Erisim: `private`
- Imza: `buildStatusOptions(array $statuses, string $selected): string`
- Aciklama: Durum secim kutusu HTML seceneklerini olusturur.
- Donus: string
- Parametreler:
  - `$selected` (string): Secili durum degeri.

### buildUsersRows

- Erisim: `private`
- Imza: `buildUsersRows(array $users, string $csrfToken, bool $canEditUsers, bool $canDeleteUsers): string`
- Aciklama: Kullanici tablosu satirlarini olusturur.
- Donus: string
- Parametreler:
  - `$csrfToken` (string): Silme formlari icin CSRF token.
  - `$canEditUsers` (bool): Satir duzenleme aksiyonlarinin gosterilip gosterilmeyecegi.
  - `$canDeleteUsers` (bool): Satir silme aksiyonlarinin gosterilip gosterilmeyecegi.

### create

- Erisim: `public`
- Imza: `create(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Yeni kullanici formunu gosterir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### delete

- Erisim: `public`
- Imza: `delete(Core\Http\Request $request, string $id = ''): Core\Http\Response`
- Aciklama: Kullanici kaydini siler.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.
  - `$id` (string): Kullanici id segmenti.

### edit

- Erisim: `public`
- Imza: `edit(Core\Http\Request $request, string $id = ''): Core\Http\Response`
- Aciklama: Kullanici duzenleme formunu gosterir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.
  - `$id` (string): Kullanici id segmenti.

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Kullanici liste ekranini gosterir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### prepareFormData

- Erisim: `private`
- Imza: `prepareFormData(array $data): array`
- Aciklama: Form sayfalari icin ortak view verisini hazirlar.
- Donus: array<string, mixed>

### prepareIndexData

- Erisim: `private`
- Imza: `prepareIndexData(array $data): array`
- Aciklama: Liste sayfasi icin ham servis verisini view'a uygun hale getirir.
- Donus: array<string, mixed>

### store

- Erisim: `public`
- Imza: `store(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Yeni kullanici olusturma istegini isler.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### update

- Erisim: `public`
- Imza: `update(Core\Http\Request $request, string $id = ''): Core\Http\Response`
- Aciklama: Var olan kullaniciyi gunceller.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.
  - `$id` (string): Kullanici id segmenti.

## App\Controllers\Api\Auth

- Dosya: `app/Controllers/Api/Auth.php`
- Aciklama: API istemcileri icin giris ve kimlik islemlerini yonetir.
- Metod sayisi: 3

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\IdentityService $identity, App\Requests\Api\AuthLoginRequest $loginRequest)`
- Parametreler:
  - `$view` (View): Controller bagimlilik tutarliligi icin eklenen view.
  - `$identity` (IdentityService): Ortak kimlik servisi.

### login

- Erisim: `public`
- Imza: `login(Core\Http\Request $request): Core\Http\Response`
- Aciklama: API kullanicisi icin token tabanli giris yapar.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### publicActions

- Erisim: `public`
- Imza: `publicActions(): array`

## App\Controllers\Api\StatusController

- Dosya: `app/Controllers/Api/StatusController.php`
- Aciklama: API istemcileri icin temel saglik ve korumali durum endpoint'lerini sunar.
- Metod sayisi: 4

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\SystemInfoService $systemInfo)`
- Parametreler:
  - `$view` (View): Controller otomatik baglama tutarliligi icin tutulan view bagimliligi.
  - `$systemInfo` (SystemInfoService): Sistem durum verisini donduren servis.

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Herkese acik sistem bilgisini dondurur.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### publicActions

- Erisim: `public`
- Imza: `publicActions(): array`

### secure

- Erisim: `public`
- Imza: `secure(Core\Http\Request $request): Core\Http\Response`
- Aciklama: API token dogrulamasindan sonra korumali sistem bilgisini dondurur.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

## App\Controllers\Api\Users

- Dosya: `app/Controllers/Api/Users.php`
- Aciklama: Kullanici verilerini API istemcilerine sunar.
- Metod sayisi: 7

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\UserService $users, App\Requests\Api\UserCreateRequest $createRequest, App\Requests\Api\UserUpdateRequest $updateRequest)`
- Parametreler:
  - `$view` (View): Controller bagimlilik tutarliligi icin eklenen view.
  - `$users` (UserService): Kullanici servis katmani.

### actionPermissions

- Erisim: `public`
- Imza: `actionPermissions(): array`

### create

- Erisim: `public`
- Imza: `create(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Yeni kullanici olusturur.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### delete

- Erisim: `public`
- Imza: `delete(Core\Http\Request $request, string $id = ''): Core\Http\Response`
- Aciklama: Kullanici kaydini siler.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.
  - `$id` (string): Kullanici id segmenti.

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Korumali kullanici listesini dondurur.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### show

- Erisim: `public`
- Imza: `show(Core\Http\Request $request, string $id = ''): Core\Http\Response`
- Aciklama: Tekil kullanici kaydini dondurur.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.
  - `$id` (string): Kullanici id segmenti.

### update

- Erisim: `public`
- Imza: `update(Core\Http\Request $request, string $id = ''): Core\Http\Response`
- Aciklama: Kullanici kaydini gunceller.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.
  - `$id` (string): Kullanici id segmenti.

## App\Controllers\Site\HomeController

- Dosya: `app/Controllers/Site/HomeController.php`
- Aciklama: Genel site ana sayfasini goruntuler.
- Metod sayisi: 2

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\Site\HomePageService $homePage)`
- Parametreler:
  - `$view` (View): Site view goruntuleyicisi.
  - `$homePage` (HomePageService): Site ana sayfa servis katmani.

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Site acilis sayfasini gosterir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

## App\Controllers\Site\LoginController

- Dosya: `app/Controllers/Site/LoginController.php`
- Aciklama: Site kullanici giris ve cikis akisini yonetir.
- Metod sayisi: 4

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\Site\SiteLoginPageService $loginPage)`
- Parametreler:
  - `$view` (View): Site view goruntuleyicisi.
  - `$loginPage` (SiteLoginPageService): Site login servis katmani.

### login

- Erisim: `public`
- Imza: `login(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Site kullanici girisini isler.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### logout

- Erisim: `public`
- Imza: `logout(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Site kullanici oturumunu kapatir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### show

- Erisim: `public`
- Imza: `show(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Site login formunu gosterir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

## App\Controllers\Site\OrnekUrl

- Dosya: `app/Controllers/Site/OrnekUrl.php`
- Aciklama: Dinamik rota yapisini gostermek icin kullanilan ornek controller.
- Metod sayisi: 3

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view, App\Services\Site\DynamicRouteService $dynamicRoute)`
- Parametreler:
  - `$view` (View): Site view goruntuleyicisi.
  - `$dynamicRoute` (DynamicRouteService): Dinamik rota servis katmani.

### aboutUs

- Erisim: `public`
- Imza: `aboutUs(Core\Http\Request $request, string ...$segments): Core\Http\Response`
- Aciklama: Iki segmentli dinamik rota isteklerini isler ve sondaki URL segmentlerini alir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### index

- Erisim: `public`
- Imza: `index(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Tek segmentli dinamik rota isteklerini isler.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

## App\Requests\Admin\AdminLoginRequest

- Dosya: `app/Requests/Admin/AdminLoginRequest.php`
- Aciklama: Admin login formu dogrulama kurallarini tanimlar.
- Metod sayisi: 1

### rules

- Erisim: `protected`
- Imza: `rules(): array`
- Donus: array<string, string>

## App\Requests\Admin\RoleUpdateRequest

- Dosya: `app/Requests/Admin/RoleUpdateRequest.php`
- Aciklama: Admin rol guncelleme formu icin dogrulama kurallarini tanimlar.
- Metod sayisi: 1

### rules

- Erisim: `protected`
- Imza: `rules(): array`
- Donus: array<string, string>

## App\Requests\Admin\UserStoreRequest

- Dosya: `app/Requests/Admin/UserStoreRequest.php`
- Aciklama: Yeni kullanici olusturma formu kurallarini tanimlar.
- Metod sayisi: 1

### rules

- Erisim: `protected`
- Imza: `rules(): array`
- Donus: array<string, string>

## App\Requests\Admin\UserUpdateRequest

- Dosya: `app/Requests/Admin/UserUpdateRequest.php`
- Aciklama: Kullanici guncelleme formu kurallarini tanimlar.
- Metod sayisi: 1

### rules

- Erisim: `protected`
- Imza: `rules(): array`
- Donus: array<string, string>

## App\Requests\Api\AuthLoginRequest

- Dosya: `app/Requests/Api/AuthLoginRequest.php`
- Aciklama: API login istegi icin dogrulama kurallarini tanimlar.
- Metod sayisi: 1

### rules

- Erisim: `protected`
- Imza: `rules(): array`
- Donus: array<string, string>

## App\Requests\Api\UserCreateRequest

- Dosya: `app/Requests/Api/UserCreateRequest.php`
- Aciklama: API kullanici olusturma istegi icin dogrulama kurallarini tanimlar.
- Metod sayisi: 1

### rules

- Erisim: `protected`
- Imza: `rules(): array`
- Donus: array<string, string>

## App\Requests\Api\UserUpdateRequest

- Dosya: `app/Requests/Api/UserUpdateRequest.php`
- Aciklama: API kullanici guncelleme istegi icin dogrulama kurallarini tanimlar.
- Metod sayisi: 1

### rules

- Erisim: `protected`
- Imza: `rules(): array`
- Donus: array<string, string>

## App\Requests\Site\SiteLoginRequest

- Dosya: `app/Requests/Site/SiteLoginRequest.php`
- Aciklama: Site login formu dogrulama kurallarini tanimlar.
- Metod sayisi: 1

### rules

- Erisim: `protected`
- Imza: `rules(): array`
- Donus: array<string, string>

## App\Services\Admin\AdminLoginPageService

- Dosya: `app/Services/Admin/AdminLoginPageService.php`
- Aciklama: Admin giris ekraninin ve giris akislarinin servis katmanini yonetir.
- Metod sayisi: 4

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\AuthService $auth, Core\Session $session, Core\Security\Csrf $csrf, App\Requests\Admin\AdminLoginRequest $loginRequest)`
- Parametreler:
  - `$auth` (AuthService): Admin kimlik dogrulama servisi.
  - `$session` (Session): Flash ve oturum verisi.
  - `$csrf` (Csrf): CSRF token servisi.
  - `$loginRequest` (AdminLoginRequest): Login form request nesnesi.

### form

- Erisim: `public`
- Imza: `form(string $path): Core\Services\ServiceResult`
- Aciklama: Login formu icin view verisini hazirlar.
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): Mevcut istek yolu.

### login

- Erisim: `public`
- Imza: `login(array $payload): Core\Services\ServiceResult`
- Aciklama: Admin giris denemesini servis katmaninda isler.
- Donus: ServiceResult

### logout

- Erisim: `public`
- Imza: `logout(): Core\Services\ServiceResult`
- Aciklama: Admin oturumunu kapatir.
- Donus: ServiceResult

## App\Services\Admin\ApiDocsPageService

- Dosya: `app/Services/Admin/ApiDocsPageService.php`
- Aciklama: Admin API dokumantasyon sayfasinin servis katmanini uretir.
- Metod sayisi: 2

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\AuthService $auth)`

### page

- Erisim: `public`
- Imza: `page(string $path): Core\Services\ServiceResult`

## App\Services\Admin\DashboardPageService

- Dosya: `app/Services/Admin/DashboardPageService.php`
- Aciklama: Admin panel anasayfa verisini ve erisim kurallarini yonetir.
- Metod sayisi: 2

### __construct

- Erisim: `public`
- Imza: `__construct(Core\Cache\FileCache $cache, App\Services\GlobalTools $globalTools, App\Services\AuthService $auth, Core\Security\Csrf $csrf, App\Services\UserService $users)`
- Parametreler:
  - `$cache` (FileCache): Panel istatistik cache servisi.
  - `$globalTools` (GlobalTools): Ortak yardimci servisler.
  - `$auth` (AuthService): Admin kimlik dogrulama servisi.
  - `$csrf` (Csrf): Form token servisi.

### page

- Erisim: `public`
- Imza: `page(string $path): Core\Services\ServiceResult`
- Aciklama: Dashboard ekrani icin gerekli veriyi uretir.
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): Mevcut istek yolu.

## App\Services\Admin\LanguageManagementPageService

- Dosya: `app/Services/Admin/LanguageManagementPageService.php`
- Aciklama: Admin dil ve ceviri ekranlarinin servis katmanini yonetir.
- Metod sayisi: 10

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\AuthService $auth, Core\Session $session, Core\Security\Csrf $csrf)`

### editForm

- Erisim: `public`
- Imza: `editForm(string $path, int $id): Core\Services\ServiceResult`
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): 
  - `$id` (int): 

### groupTranslationsByKey

- Erisim: `private`
- Imza: `groupTranslationsByKey(array $rows): array`
- Donus: array<int, array<string, mixed>>

### guard

- Erisim: `private`
- Imza: `guard(): Core\Services\ServiceResult`
- Donus: ServiceResult

### index

- Erisim: `public`
- Imza: `index(string $path, array $filters = array (
)): Core\Services\ServiceResult`
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): 

### languageRows

- Erisim: `private`
- Imza: `languageRows(): array`
- Donus: array<int, array<string, mixed>>

### normalizeRows

- Erisim: `private`
- Imza: `normalizeRows(object|array|false $rows): array`
- Donus: array<int, array<string, mixed>>
- Parametreler:
  - `$rows` (object|array|false): 

### translationRows

- Erisim: `private`
- Imza: `translationRows(): array`
- Donus: array<int, array<string, mixed>>

### translationsForKey

- Erisim: `private`
- Imza: `translationsForKey(string $key): array`
- Donus: array<int, array<string, mixed>>
- Parametreler:
  - `$key` (string): 

### update

- Erisim: `public`
- Imza: `update(int $id, array $payload): Core\Services\ServiceResult`
- Donus: ServiceResult
- Parametreler:
  - `$id` (int): 

## App\Services\Admin\LogManagementPageService

- Dosya: `app/Services/Admin/LogManagementPageService.php`
- Aciklama: Admin log listeleme ekraninin servis katmanini yonetir.
- Metod sayisi: 4

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\AuthService $auth)`

### detail

- Erisim: `public`
- Imza: `detail(string $path, int $id): Core\Services\ServiceResult`
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): 
  - `$id` (int): 

### index

- Erisim: `public`
- Imza: `index(string $path, array $filters = array (
)): Core\Services\ServiceResult`
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): 

### normalizeRows

- Erisim: `private`
- Imza: `normalizeRows(object|array|false $rows): array`
- Donus: array<int, array<string, mixed>>
- Parametreler:
  - `$rows` (object|array|false): 

## App\Services\Admin\RoleManagementPageService

- Dosya: `app/Services/Admin/RoleManagementPageService.php`
- Aciklama: Admin rol ve yetki ekranlarinin servis katmanini yonetir.
- Metod sayisi: 6

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\AuthService $auth, Core\Session $session, Core\Security\Csrf $csrf, App\Services\AuthorizationService $authorization, App\Services\UserService $users, App\Requests\Admin\RoleUpdateRequest $updateRequest)`

### editForm

- Erisim: `public`
- Imza: `editForm(string $path, int $id): Core\Services\ServiceResult`
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): 
  - `$id` (int): 

### guard

- Erisim: `private`
- Imza: `guard(): Core\Services\ServiceResult`
- Donus: ServiceResult

### index

- Erisim: `public`
- Imza: `index(string $path): Core\Services\ServiceResult`
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): 

### selectedPermissions

- Erisim: `private`
- Imza: `selectedPermissions(string $auth): array`
- Donus: array<int, string>
- Parametreler:
  - `$auth` (string): 

### update

- Erisim: `public`
- Imza: `update(int $id, array $payload): Core\Services\ServiceResult`
- Donus: ServiceResult
- Parametreler:
  - `$id` (int): 

## App\Services\Admin\UploadManagementPageService

- Dosya: `app/Services/Admin/UploadManagementPageService.php`
- Aciklama: Admin upload listeleme ve silme ekranlarinin servis katmanini yonetir.
- Metod sayisi: 5

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\AuthService $auth, Core\Session $session, Core\Security\Csrf $csrf)`

### delete

- Erisim: `public`
- Imza: `delete(int $id, string $basePath): Core\Services\ServiceResult`

### guard

- Erisim: `private`
- Imza: `guard(): Core\Services\ServiceResult`

### index

- Erisim: `public`
- Imza: `index(string $path, array $filters = array (
)): Core\Services\ServiceResult`

### normalizeRows

- Erisim: `private`
- Imza: `normalizeRows(object|array|false $rows): array`

## App\Services\Admin\UserManagementPageService

- Dosya: `app/Services/Admin/UserManagementPageService.php`
- Aciklama: Admin kullanici yonetim ekranlarinin servis katmanini yonetir.
- Metod sayisi: 15

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\AuthService $auth, Core\Session $session, Core\Security\Csrf $csrf, App\Services\UserService $users, App\Requests\Admin\UserStoreRequest $storeRequest, App\Requests\Admin\UserUpdateRequest $updateRequest)`
- Parametreler:
  - `$auth` (AuthService): Admin kimlik dogrulama servisi.
  - `$session` (Session): Flash veri yonetimi.
  - `$csrf` (Csrf): Form token servisi.
  - `$users` (UserService): Kullanici is kurallari servisi.
  - `$storeRequest` (UserStoreRequest): Yeni kullanici request nesnesi.
  - `$updateRequest` (UserUpdateRequest): Guncelleme request nesnesi.

### buildFormData

- Erisim: `private`
- Imza: `buildFormData(string $path, array $overrides = array (
)): array`
- Aciklama: Form sayfalari icin ortak veri setini olusturur.
- Donus: array<string, mixed>
- Parametreler:
  - `$path` (string): Mevcut istek yolu.

### createForm

- Erisim: `public`
- Imza: `createForm(string $path): Core\Services\ServiceResult`
- Aciklama: Yeni kullanici formu icin view verisini dondurur.
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): Mevcut istek yolu.

### delete

- Erisim: `public`
- Imza: `delete(int $id): Core\Services\ServiceResult`
- Aciklama: Kullanici kaydini siler.
- Donus: ServiceResult
- Parametreler:
  - `$id` (int): Kullanici id degeri.

### editForm

- Erisim: `public`
- Imza: `editForm(string $path, int $id): Core\Services\ServiceResult`
- Aciklama: Var olan kullanici icin duzenleme formu verisini hazirlar.
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): Mevcut istek yolu.
  - `$id` (int): Kullanici id degeri.

### firstValidationMessage

- Erisim: `private`
- Imza: `firstValidationMessage(Core\Validation\ValidationResult $validation, array $payload): string`
- Aciklama: Dogrulama sonucu icin ekranda gosterilecek ilk hata mesajini belirler.
- Donus: string
- Parametreler:
  - `$validation` (\Core\Validation\ValidationResult): Dogrulama sonucu.

### formFields

- Erisim: `private`
- Imza: `formFields(): array`
- Aciklama: Flash oturumundaki eski form verisini dondurur.
- Donus: string
- Parametreler:
  - `$field` (string): Form alan adi.
  - `$default` (string): Varsayilan deger.

### guard

- Erisim: `private`
- Imza: `guard(): Core\Services\ServiceResult`
- Aciklama: Admin oturumu zorunlulugunu kontrol eder.
- Donus: ServiceResult

### handleWriteResult

- Erisim: `private`
- Imza: `handleWriteResult(Core\Services\ServiceResult $result, string $errorRedirect, string $successMessage, int $successStatus = 200): Core\Services\ServiceResult`
- Aciklama: Yazma islemleri sonrasi ortak basari/hata akislarini yonetir.
- Donus: ServiceResult
- Parametreler:
  - `$result` (ServiceResult): Is katmani sonucu.
  - `$errorRedirect` (string): Hata halinde donulecek sayfa.
  - `$successMessage` (string): Basari flash mesaji.
  - `$successStatus` (int): Onerilen HTTP durum kodu.

### index

- Erisim: `public`
- Imza: `index(string $path, array $filters = array (
)): Core\Services\ServiceResult`
- Aciklama: Kullanici liste ekrani verisini hazirlar.
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): Mevcut istek yolu.

### roleOptions

- Erisim: `private`
- Imza: `roleOptions(): array`
- Aciklama: Rol seceneklerini servis katmanindan alir.
- Donus: array<int, array<string, mixed>>

### statusOptions

- Erisim: `private`
- Imza: `statusOptions(): array`
- Aciklama: Durum seceneklerini servis katmanindan alir.
- Donus: array<int, array<string, mixed>>

### store

- Erisim: `public`
- Imza: `store(array $payload): Core\Services\ServiceResult`
- Aciklama: Yeni kullanici olusturma istegini isler.
- Donus: ServiceResult

### update

- Erisim: `public`
- Imza: `update(int $id, array $payload): Core\Services\ServiceResult`
- Aciklama: Var olan kullaniciyi gunceller.
- Donus: ServiceResult
- Parametreler:
  - `$id` (int): Kullanici id degeri.

### validateSelectValues

- Erisim: `private`
- Imza: `validateSelectValues(array $payload): bool`
- Aciklama: Formdan gelen role ve status degerlerini kontrol eder.
- Donus: bool

## App\Services\AuthService

- Dosya: `app/Services/AuthService.php`
- Aciklama: Admin kimlik dogrulama durumunu ve bilgi kontrolunu yonetir.
- Metod sayisi: 5

### __construct

- Erisim: `public`
- Imza: `__construct(Core\Session $session, App\Services\IdentityService $identity)`
- Parametreler:
  - `$session` (Session): Dogrulanmis admin verisi icin oturum deposu.
  - `$identity` (IdentityService): Ortak kimlik dogrulama servisi.

### admin

- Erisim: `public`
- Imza: `admin(): array`
- Aciklama: Oturumdaki mevcut dogrulanmis admin verisini dondurur.
- Donus: array|null

### attemptAdminLogin

- Erisim: `public`
- Imza: `attemptAdminLogin(string $email, string $password): bool`
- Aciklama: Admin kullanicisini e-posta ve parola ile dogrulamayi dener.
- Donus: bool
- Parametreler:
  - `$email` (string): Gonderilen e-posta adresi.
  - `$password` (string): Gonderilen parola.

### checkAdmin

- Erisim: `public`
- Imza: `checkAdmin(): bool`
- Aciklama: Bir admin kullanicisinin su anda dogrulanmis olup olmadigini belirtir.
- Donus: bool

### logoutAdmin

- Erisim: `public`
- Imza: `logoutAdmin(): void`
- Aciklama: Mevcut admin kullanicisinin oturumunu kapatir.

## App\Services\AuthorizationService

- Dosya: `app/Services/AuthorizationService.php`
- Aciklama: Rol kayitlarindaki auth alanini yorumlayip yetki kontrolu yapar.
- Metod sayisi: 8

### identityCan

- Erisim: `public`
- Imza: `identityCan(array $identity, array|string $required): bool`
- Aciklama: Kimlik verisindeki rol ve permission alanlarina gore yetki kontrolu yapar.
- Donus: bool

### normalizeRequiredPermissions

- Erisim: `private`
- Imza: `normalizeRequiredPermissions(array|string $required): array`
- Aciklama: Gerekli yetki tanimini listeye cevirir.
- Donus: array<int, string>

### parsePermissions

- Erisim: `private`
- Imza: `parsePermissions(string $auth): array`
- Aciklama: Rol auth metnini yetki listesine cevirir.
- Donus: array<int, string>
- Parametreler:
  - `$auth` (string): Rol kaydindaki auth alani.

### permissionCatalog

- Erisim: `public`
- Imza: `permissionCatalog(): array`
- Aciklama: Sistem genelinde kullanilabilir permission katalogunu dondurur.
- Donus: array<string, array<int, string>>

### permissionMatches

- Erisim: `private`
- Imza: `permissionMatches(array $available, string $required): bool`
- Aciklama: Kullanici yetkileri icinde gerekli yetkinin karsilanip karsilanmadigini kontrol eder.
- Donus: bool
- Parametreler:
  - `$required` (string): Gerekli yetki.

### permissionsForRole

- Erisim: `public`
- Imza: `permissionsForRole(int $roleId): array`
- Aciklama: Rol icin tanimli yetki listesini dondurur.
- Donus: array<int, string>
- Parametreler:
  - `$roleId` (int): Rol id degeri.

### sanitizePermissions

- Erisim: `public`
- Imza: `sanitizePermissions(array $permissions): array`
- Aciklama: Gelen permission listesini gecerli katalog ile temizler.
- Donus: array<int, string>

### validPermissions

- Erisim: `public`
- Imza: `validPermissions(): array`
- Aciklama: Gecerli tum permission degerlerini tek liste halinde dondurur.
- Donus: array<int, string>

## App\Services\FileUploadService

- Dosya: `app/Services/FileUploadService.php`
- Aciklama: Dosya yukleme, dizin organizasyonu ve upload metadata kaydini yonetir.
- Metod sayisi: 6

### __construct

- Erisim: `public`
- Imza: `__construct(string $uploadRoot)`
- Parametreler:
  - `$uploadRoot` (string): Herkese acik yukleme kok dizini.

### ensureDirectory

- Erisim: `private`
- Imza: `ensureDirectory(string $path): void`
- Aciklama: Dosya islemlerinden once dizinin var oldugundan emin olur.
- Donus: void
- Parametreler:
  - `$path` (string): Dizin yolu.

### publicUploadPath

- Erisim: `public`
- Imza: `publicUploadPath(string $directory = 'common', string $fileName = ''): string`
- Aciklama: Yuklenen dosya ya da klasor icin herkese acik URL yolu olusturur.
- Donus: string
- Parametreler:
  - `$directory` (string): Yukleme dizini adi.
  - `$fileName` (string): Opsiyonel dosya adi.

### resolveUniqueFileName

- Erisim: `private`
- Imza: `resolveUniqueFileName(string $directory, string $baseName, string $extension): string`
- Aciklama: Ayni klasorde cakisani olmayan dosya adini cozer.
- Donus: string
- Parametreler:
  - `$directory` (string): Hedef klasor yolu.
  - `$baseName` (string): Dosya baz adi.
  - `$extension` (string): Dosya uzantisi.

### sanitizeFileName

- Erisim: `private`
- Imza: `sanitizeFileName(string $name): string`
- Aciklama: Orijinal dosya adini guvenli ve tekrar kullanilabilir bir dosya adina cevirir.
- Donus: string
- Parametreler:
  - `$name` (string): Ham dosya adi.

### uploadFile

- Erisim: `public`
- Imza: `uploadFile(array $file, string $directory = 'common', array $options = array (
)): array`
- Aciklama: Yuklenen dosyayi yil/ay/gun klasor yapisinda kaydeder ve metadata'sini veritabanina yazar.
- Donus: array
- Parametreler:
  - `$file` (array): PHP'den gelen yuklenen dosya dizisi.
  - `$directory` (string): Yukleme kok dizini altindaki kanal klasoru.
  - `$options` (array): Izin verilen uzantilar ve azami boyut gibi yukleme kisitlari.

## App\Services\GlobalTools

- Dosya: `app/Services/GlobalTools.php`
- Aciklama: Dosya yukleme, mail render etme ve mail gonderimi icin ortak yardimci servis.
- Metod sayisi: 11

### __construct

- Erisim: `public`
- Imza: `__construct(string $mailLogFile, App\Services\FileUploadService $uploads, array $mailConfig, Core\View\View $siteMailView, Core\View\View $adminMailView)`
- Parametreler:
  - `$mailLogFile` (string): Giden mail etkinligi icin log dosyasi.
  - `$uploads` (FileUploadService): Dosya yukleme servisi.
  - `$mailConfig` (array): Mail tasima yapilandirmasi.
  - `$siteMailView` (View): Site mail sablonu goruntuleyicisi.
  - `$adminMailView` (View): Admin mail sablonu goruntuleyicisi.

### buildMailer

- Erisim: `private`
- Imza: `buildMailer(array $headers): PHPMailer\PHPMailer\PHPMailer`
- Aciklama: Proje ayarlarindan PHPMailer nesnesi olusturur ve ayarlar.
- Donus: PHPMailer
- Parametreler:
  - `$headers` (array): Ek mail header'lari.

### ensureDirectory

- Erisim: `private`
- Imza: `ensureDirectory(string $path): void`
- Aciklama: Dosya islemlerinden once dizinin var oldugundan emin olur.
- Parametreler:
  - `$path` (string): Dizin yolu.

### isHtmlMessage

- Erisim: `private`
- Imza: `isHtmlMessage(array $headers, string $message): bool`
- Aciklama: Giden mesaj govdesinin HTML olarak ele alinip alinmayacagini belirler.
- Donus: bool
- Parametreler:
  - `$headers` (array): Mail header'lari.
  - `$message` (string): Mesaj govdesi.

### logMail

- Erisim: `private`
- Imza: `logMail(array $recipients, string $subject, string $message, string $status, string $error = NULL): void`
- Aciklama: Yerel log dosyasina mail gonderim kaydi ekler.
- Parametreler:
  - `$recipients` (array): Alici listesi.
  - `$subject` (string): Mesaj konusu.
  - `$message` (string): Mesaj govdesi.
  - `$status` (string): Teslim durumu.
  - `$error` (string|null): Opsiyonel hata mesaji.

### mailView

- Erisim: `private`
- Imza: `mailView(string $channel): Core\View\View`
- Aciklama: Bir kanal icin dogru mail view goruntuleyicisini cozer.
- Donus: View
- Parametreler:
  - `$channel` (string): Sablon kanal adi.

### publicUploadPath

- Erisim: `public`
- Imza: `publicUploadPath(string $directory = 'common', string $fileName = ''): string`
- Aciklama: Yuklenen dosya ya da klasor icin herkese acik URL yolu olusturur.
- Donus: string
- Parametreler:
  - `$directory` (string): Yukleme dizini adi.
  - `$fileName` (string): Opsiyonel dosya adi.

### renderMailTemplate

- Erisim: `public`
- Imza: `renderMailTemplate(string $channel, string $template, array $data = array (
), string $layout = NULL): string`
- Aciklama: Verilen kanal icin mail sablonunu render eder.
- Donus: string
- Parametreler:
  - `$channel` (string): site ya da admin gibi sablon kanal adi.
  - `$template` (string): Sablon adi.
  - `$data` (array): Sablon verisi.
  - `$layout` (string|null): Opsiyonel layout ezmesi.

### sendMail

- Erisim: `public`
- Imza: `sendMail(array|string $to, string $subject, string $message, array $headers = array (
)): bool`
- Aciklama: Yapilandirilmis mail tasiyicisini kullanarak mail gonderir.
- Donus: bool
- Parametreler:
  - `$to` (string|array): Alici ya da alicilar.
  - `$subject` (string): Mesaj konusu.
  - `$message` (string): Mesaj govdesi.
  - `$headers` (array): Opsiyonel reply-to, cc, bcc ve content-type ipuclari.

### sendTemplatedMail

- Erisim: `public`
- Imza: `sendTemplatedMail(string $channel, array|string $to, string $subject, string $template, array $data = array (
), array $headers = array (
), string $layout = NULL): bool`
- Aciklama: HTML mail sablonunu render eder ve gonderir.
- Donus: bool
- Parametreler:
  - `$channel` (string): Sablon kanal adi.
  - `$to` (string|array): Alici ya da alicilar.
  - `$subject` (string): Mesaj konusu.
  - `$template` (string): Sablon adi.
  - `$data` (array): Sablon verisi.
  - `$headers` (array): Ek mail header'lari.
  - `$layout` (string|null): Opsiyonel layout ezmesi.

### uploadFile

- Erisim: `public`
- Imza: `uploadFile(array $file, string $directory = 'common', array $options = array (
)): array`
- Aciklama: Yuklenen dosyayi yapilandirilmis herkese acik yukleme dizinine tasir.
- Donus: array
- Parametreler:
  - `$file` (array): PHP'den gelen yuklenen dosya dizisi.
  - `$directory` (string): Yukleme kok dizini altindaki alt klasor.
  - `$options` (array): Izin verilen uzantilar ve azami boyut gibi yukleme kisitlari.

## App\Services\IdentityService

- Dosya: `app/Services/IdentityService.php`
- Aciklama: Admin, site kullanicisi ve API token kimlik dogrulama mantigini tek yerde toplar.
- Metod sayisi: 17

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\AuthorizationService $authorization, array $apiConfig = array (
))`

### apiHeaderName

- Erisim: `public`
- Imza: `apiHeaderName(): string`
- Aciklama: API icin beklenen header adini dondurur.
- Donus: string

### apiTokenTtl

- Erisim: `public`
- Imza: `apiTokenTtl(): int`
- Aciklama: API token gecerlilik suresini saniye cinsinden dondurur.
- Donus: int

### apiTokensHaveUserId

- Erisim: `private`
- Imza: `apiTokensHaveUserId(): bool`
- Aciklama: Api token tablosunda user_id kolonu olup olmadigini kontrol eder.
- Donus: bool

### authenticateAdminCredentials

- Erisim: `public`
- Imza: `authenticateAdminCredentials(string $email, string $password): Core\Services\ServiceResult`
- Aciklama: Admin kullanici kimlik bilgilerini dogrular.
- Donus: ServiceResult
- Parametreler:
  - `$email` (string): E-posta adresi.
  - `$password` (string): Girilen parola.

### authenticateApiRequest

- Erisim: `public`
- Imza: `authenticateApiRequest(Core\Http\Request $request): Core\Services\ServiceResult`
- Aciklama: API istegindeki token bilgisini dogrular.
- Donus: ServiceResult
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### authenticateUserCredentials

- Erisim: `public`
- Imza: `authenticateUserCredentials(string $email, string $password): Core\Services\ServiceResult`
- Aciklama: Site kullanicisi kimlik bilgilerini dogrular.
- Donus: ServiceResult
- Parametreler:
  - `$email` (string): E-posta adresi.
  - `$password` (string): Girilen parola.

### buildAdminIdentity

- Erisim: `private`
- Imza: `buildAdminIdentity(object $admin): array`
- Aciklama: Admin kimlik verisini ortak dizi yapisina cevirir.
- Donus: array<string, mixed>
- Parametreler:
  - `$admin` (object): Admin model nesnesi.

### buildApiIdentity

- Erisim: `private`
- Imza: `buildApiIdentity(int $userId): array`
- Aciklama: API token ile iliskili kullanicinin kimlik verisini dondurur.
- Donus: array<string, mixed>
- Parametreler:
  - `$userId` (int): Kullanici id degeri.

### buildUserIdentity

- Erisim: `private`
- Imza: `buildUserIdentity(object $user): array`
- Aciklama: Site kullanici kimlik verisini ortak dizi yapisina cevirir.
- Donus: array<string, mixed>
- Parametreler:
  - `$user` (object): Kullanici model nesnesi.

### findActiveAdminByEmail

- Erisim: `private`
- Imza: `findActiveAdminByEmail(string $email): object|false`
- Aciklama: E-posta adresine gore aktif admin kullanicisini bulur.
- Donus: object|false
- Parametreler:
  - `$email` (string): E-posta adresi.

### findActiveUserByEmail

- Erisim: `private`
- Imza: `findActiveUserByEmail(string $email): object|false`
- Aciklama: E-posta adresine gore aktif site kullanicisini bulur.
- Donus: object|false
- Parametreler:
  - `$email` (string): E-posta adresi.

### hashUserPassword

- Erisim: `public`
- Imza: `hashUserPassword(string $password): string`
- Aciklama: Site kullanicisi parolasini guvenli parola hash'i ile karmalar.
- Donus: string
- Parametreler:
  - `$password` (string): Duz parola.

### loginUserForApi

- Erisim: `public`
- Imza: `loginUserForApi(string $email, string $password, string $deviceName = 'Mobile App'): Core\Services\ServiceResult`
- Aciklama: Mobil/API istemcileri icin kullanici girisi yapar ve token uretir.
- Donus: ServiceResult
- Parametreler:
  - `$email` (string): E-posta adresi.
  - `$password` (string): Girilen parola.
  - `$deviceName` (string): Cihaz veya istemci adi.

### normalizeTimestamp

- Erisim: `private`
- Imza: `normalizeTimestamp(mixed $value): int`
- Aciklama: Tarih/zaman alanini Unix timestamp degerine cevirir.
- Donus: int|null
- Parametreler:
  - `$value` (mixed): Ham tablo degeri.

### touchApiTokenUsage

- Erisim: `private`
- Imza: `touchApiTokenUsage(int $tokenId): void`
- Aciklama: Kullanim sonrasi API token satirini gunceller.
- Donus: void
- Parametreler:
  - `$tokenId` (int): Token id degeri.

### verifyUserPassword

- Erisim: `public`
- Imza: `verifyUserPassword(string $password, string $hash): bool`
- Aciklama: Site kullanicisi parolasini kayitli karma ile dogrular.
- Donus: bool
- Parametreler:
  - `$password` (string): Duz parola.
  - `$hash` (string): Kayitli hash degeri.

## App\Services\Site\DynamicRouteService

- Dosya: `app/Services/Site/DynamicRouteService.php`
- Aciklama: Dinamik site rota ekranlari icin view verisini hazirlar.
- Metod sayisi: 1

### page

- Erisim: `public`
- Imza: `page(string $path, string $className, string $methodName, array $segments = array (
)): Core\Services\ServiceResult`
- Aciklama: Dinamik rota ornek sayfasi icin view verisini dondurur.
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): Mevcut istek yolu.
  - `$className` (string): Controller sinif adi.
  - `$methodName` (string): Calisan metod adi.

## App\Services\Site\HomePageService

- Dosya: `app/Services/Site/HomePageService.php`
- Aciklama: Site ana sayfa verisini hazirlar.
- Metod sayisi: 2

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\GlobalTools $globalTools, App\Services\UserService $users, App\Services\UserAuthService $userAuth, Core\Security\Csrf $csrf)`

### page

- Erisim: `public`
- Imza: `page(string $path): Core\Services\ServiceResult`
- Aciklama: Ana sayfa view verisini uretir.
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): Mevcut istek yolu.

## App\Services\Site\SiteLoginPageService

- Dosya: `app/Services/Site/SiteLoginPageService.php`
- Aciklama: Site kullanici giris ekranini ve akisini yonetir.
- Metod sayisi: 4

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\UserAuthService $auth, Core\Session $session, Core\Security\Csrf $csrf, App\Requests\Site\SiteLoginRequest $loginRequest)`
- Parametreler:
  - `$auth` (UserAuthService): Site kullanici auth servisi.
  - `$session` (Session): Flash veri depolamasi.
  - `$csrf` (Csrf): Form token servisi.
  - `$loginRequest` (SiteLoginRequest): Login form request nesnesi.

### form

- Erisim: `public`
- Imza: `form(string $path): Core\Services\ServiceResult`
- Aciklama: Site login formu verisini hazirlar.
- Donus: ServiceResult
- Parametreler:
  - `$path` (string): Mevcut istek yolu.

### login

- Erisim: `public`
- Imza: `login(array $payload): Core\Services\ServiceResult`
- Aciklama: Site kullanici girisini isler.
- Donus: ServiceResult

### logout

- Erisim: `public`
- Imza: `logout(): Core\Services\ServiceResult`
- Aciklama: Site kullanici oturumunu kapatir.
- Donus: ServiceResult

## App\Services\SystemInfoService

- Dosya: `app/Services/SystemInfoService.php`
- Aciklama: API yanitlari icin sistem ve saglik bilgisi saglar.
- Metod sayisi: 3

### __construct

- Erisim: `public`
- Imza: `__construct(array $apiConfig = array (
))`
- Parametreler:
  - `$apiConfig` (array): Korumali erisim davranisini aciklamak icin kullanilan API yapilandirmasi.

### protectedStatus

- Erisim: `public`
- Imza: `protectedStatus(): Core\Services\ServiceResult`
- Aciklama: Kimligi dogrulanmis API istemcileri icin korumali sistem durum verisini dondurur.
- Donus: ServiceResult

### status

- Erisim: `public`
- Imza: `status(): Core\Services\ServiceResult`
- Aciklama: Herkese acik sistem durum verisini dondurur.
- Donus: ServiceResult

## App\Services\UserAuthService

- Dosya: `app/Services/UserAuthService.php`
- Aciklama: Site kullanicisi oturumunu yoneten ince auth servisi.
- Metod sayisi: 5

### __construct

- Erisim: `public`
- Imza: `__construct(Core\Session $session, App\Services\IdentityService $identity)`
- Parametreler:
  - `$session` (Session): Kullanici oturumu icin session depolamasi.
  - `$identity` (IdentityService): Ortak kimlik dogrulama servisi.

### attemptUserLogin

- Erisim: `public`
- Imza: `attemptUserLogin(string $email, string $password): bool`
- Aciklama: Kullanici giris denemesini oturum ile birlikte tamamlar.
- Donus: bool
- Parametreler:
  - `$email` (string): E-posta adresi.
  - `$password` (string): Girilen parola.

### checkUser

- Erisim: `public`
- Imza: `checkUser(): bool`
- Aciklama: Kullanici giris yapmis mi bilgisini verir.
- Donus: bool

### logoutUser

- Erisim: `public`
- Imza: `logoutUser(): void`
- Aciklama: Kullanici oturumunu kapatir.
- Donus: void

### user

- Erisim: `public`
- Imza: `user(): array`
- Aciklama: Oturumdaki aktif kullaniciyi dondurur.
- Donus: array|null

## App\Services\UserService

- Dosya: `app/Services/UserService.php`
- Aciklama: Kullanici, rol ve profil verilerini servis katmaninda toplar.
- Metod sayisi: 26

### __construct

- Erisim: `public`
- Imza: `__construct(App\Services\IdentityService $identity, App\Services\AuthorizationService $authorization)`
- Parametreler:
  - `$identity` (IdentityService): Kimlik ve parola yardimcilari.

### applyOptionalPassword

- Erisim: `private`
- Imza: `applyOptionalPassword(object $user, string $password): void`
- Aciklama: Guncelleme isleminde parola geldiyse modele yeni sifreyi uygular.
- Donus: void
- Parametreler:
  - `$user` (object): Kullanici model nesnesi.
  - `$password` (string): Duz parola.

### buildNewUserModel

- Erisim: `private`
- Imza: `buildNewUserModel(array $payload, string $email, int $timestamp): object`
- Aciklama: Yeni kullanici kaydi icin model nesnesini hazirlar.
- Donus: object
- Parametreler:
  - `$email` (string): Normalize edilmis e-posta adresi.
  - `$timestamp` (int): Zaman damgasi.

### createUser

- Erisim: `public`
- Imza: `createUser(array $payload): Core\Services\ServiceResult`
- Aciklama: Yeni kullanici ve profil kaydi olusturur.
- Donus: ServiceResult

### deleteUser

- Erisim: `public`
- Imza: `deleteUser(int $id): Core\Services\ServiceResult`
- Aciklama: Kullanici kaydini siler.
- Donus: ServiceResult
- Parametreler:
  - `$id` (int): Kullanici id degeri.

### displayName

- Erisim: `private`
- Imza: `displayName(object $user): string`
- Aciklama: Tek kullanici nesnesinden gorunen ad uretir.
- Donus: string
- Parametreler:
  - `$user` (object|null): Kullanici nesnesi.

### emailExists

- Erisim: `private`
- Imza: `emailExists(string $email, int $exceptId = NULL): bool`
- Aciklama: E-posta adresinin daha once kullanilip kullanilmadigini kontrol eder.
- Donus: bool
- Parametreler:
  - `$email` (string): Aranacak e-posta adresi.
  - `$exceptId` (int|null): Haric tutulacak kullanici id degeri.

### enrichUsers

- Erisim: `private`
- Imza: `enrichUsers(array $users): array`
- Aciklama: Kullanici satirlarini rol ve profil verileriyle zenginlestirir.
- Donus: array<int, array<string, mixed>>

### fillUserModel

- Erisim: `private`
- Imza: `fillUserModel(object $user, array $payload, string $email): void`
- Aciklama: Ortak kullanici alanlarini gelen veriye gore modele yazar.
- Donus: void
- Parametreler:
  - `$user` (object): Kullanici model nesnesi.
  - `$email` (string): Normalize edilmis e-posta adresi.

### findRole

- Erisim: `public`
- Imza: `findRole(int $id): Core\Services\ServiceResult`
- Aciklama: Tek bir rol kaydini dondurur.
- Donus: ServiceResult
- Parametreler:
  - `$id` (int): Rol id degeri.

### findUser

- Erisim: `public`
- Imza: `findUser(int $id): Core\Services\ServiceResult`
- Aciklama: Tek bir kullaniciyi iliskili verileriyle dondurur.
- Donus: ServiceResult
- Parametreler:
  - `$id` (int): Kullanici id degeri.

### listUsers

- Erisim: `public`
- Imza: `listUsers(int $limit = 20, array $filters = array (
)): Core\Services\ServiceResult`
- Aciklama: Kullanici listesini iliskili rol ve profil verileriyle dondurur.
- Donus: ServiceResult
- Parametreler:
  - `$limit` (int): Maksimum kullanici adedi.

### normalizeObject

- Erisim: `private`
- Imza: `normalizeObject(object $item): array`
- Aciklama: Tek bir model nesnesini diziye cevirir.
- Donus: array<string, mixed>
- Parametreler:
  - `$item` (object): Model nesnesi.

### normalizeObjects

- Erisim: `private`
- Imza: `normalizeObjects(object|array|false $rows): array`
- Aciklama: Nesne ya da nesne listesini dizi listesine donusturur.
- Donus: array<int, array<string, mixed>>
- Parametreler:
  - `$rows` (object|array|false): Model sonucu.

### normalizeRolePermissions

- Erisim: `private`
- Imza: `normalizeRolePermissions(mixed $permissions): string`
- Aciklama: Rol yetkilerini standart auth metnine cevirir.
- Donus: string
- Parametreler:
  - `$permissions` (mixed): Gelen permission dizisi.

### normalizeStatus

- Erisim: `private`
- Imza: `normalizeStatus(string $status): string`
- Aciklama: Durum degerini izin verilen degerlere indirger.
- Donus: string
- Parametreler:
  - `$status` (string): Gelen durum metni.

### profilesByUserId

- Erisim: `private`
- Imza: `profilesByUserId(array $users): array`
- Aciklama: Kullanici listesi icin profil verilerini userId bazinda dondurur.
- Donus: array<int, array<string, mixed>>

### roleIsValid

- Erisim: `private`
- Imza: `roleIsValid(int $roleId): bool`
- Aciklama: Rol id degerinin gecerli olup olmadigini belirtir.
- Donus: bool
- Parametreler:
  - `$roleId` (int): Rol id degeri.

### roles

- Erisim: `public`
- Imza: `roles(): Core\Services\ServiceResult`
- Aciklama: Kullanilabilir rol listesini dondurur.
- Donus: ServiceResult

### rolesById

- Erisim: `private`
- Imza: `rolesById(array $users): array`
- Aciklama: Kullanici listesi icin rol verilerini rol id bazinda dondurur.
- Donus: array<int, array<string, mixed>>

### saveProfile

- Erisim: `private`
- Imza: `saveProfile(int $userId, array $payload, int $timestamp): void`
- Aciklama: Kullanici profil bilgisini ekler veya gunceller.
- Donus: void
- Parametreler:
  - `$userId` (int): Kullanici id degeri.
  - `$timestamp` (int): Zaman damgasi.

### statuses

- Erisim: `public`
- Imza: `statuses(): Core\Services\ServiceResult`
- Aciklama: Formlarda kullanilacak durum listesini dondurur.
- Donus: ServiceResult

### summary

- Erisim: `public`
- Imza: `summary(): Core\Services\ServiceResult`
- Aciklama: Kullanici ozet verilerini dondurur.
- Donus: ServiceResult

### updateRole

- Erisim: `public`
- Imza: `updateRole(int $id, array $payload): Core\Services\ServiceResult`
- Aciklama: Rol kaydini gunceller.
- Donus: ServiceResult
- Parametreler:
  - `$id` (int): Rol id degeri.

### updateUser

- Erisim: `public`
- Imza: `updateUser(int $id, array $payload): Core\Services\ServiceResult`
- Aciklama: Var olan kullaniciyi gunceller.
- Donus: ServiceResult
- Parametreler:
  - `$id` (int): Kullanici id degeri.

### usersHaveRoleColumn

- Erisim: `private`
- Imza: `usersHaveRoleColumn(): bool`
- Aciklama: Users tablosunda role kolonu olup olmadigini kontrol eder.
- Donus: bool

## Core\AdminController

- Dosya: `core/AdminController.php`
- Aciklama: Admin sayfalari icin anlamsal temel controller.
- Metod sayisi: 0

## Core\ApiController

- Dosya: `core/ApiController.php`
- Aciklama: API controller'lari icin public action tanimlamayi standartlastiran temel sinif.
- Metod sayisi: 7

### apiError

- Erisim: `protected`
- Imza: `apiError(string $message, int $status = 422, array $data = array (
)): Core\Http\Response`
- Aciklama: Standart API hata yaniti olusturur.
- Donus: Response
- Parametreler:
  - `$message` (string): Hata mesaji.
  - `$status` (int): HTTP durum kodu.

### apiSuccess

- Erisim: `protected`
- Imza: `apiSuccess(string $message, array $data = array (
), int $status = 200): Core\Http\Response`
- Aciklama: Standart API basari yaniti olusturur.
- Donus: Response
- Parametreler:
  - `$message` (string): Basari mesaji.
  - `$status` (int): HTTP durum kodu.

### apiValidationError

- Erisim: `protected`
- Imza: `apiValidationError(Core\Validation\ValidationResult $validation, string $message = 'Form verilerini kontrol edin.'): Core\Http\Response`
- Aciklama: Validation hatalarini standart API yaniti olarak dondurur.
- Donus: Response
- Parametreler:
  - `$validation` (ValidationResult): Dogrulama sonucu.
  - `$message` (string): Hata mesaji.

### isPublicAction

- Erisim: `public`
- Imza: `isPublicAction(string $action): bool`
- Aciklama: Verilen action'in public olarak isaretlenip isaretlenmedigini belirtir.
- Donus: bool
- Parametreler:
  - `$action` (string): Action adi.

### jsonResult

- Erisim: `protected`
- Imza: `jsonResult(Core\Services\ServiceResult $result, int $successStatus = 200): Core\Http\Response`
- Aciklama: Servis sonucunu standart API yanitina cevirir.
- Donus: Response
- Parametreler:
  - `$result` (ServiceResult): Servis sonucu.
  - `$successStatus` (int): Basarili durumda kullanilacak durum kodu.

### publicActions

- Erisim: `public`
- Imza: `publicActions(): array`
- Aciklama: Token gerektirmeyen action adlarini dondurur.
- Donus: array<int, string>

### validateApiRequest

- Erisim: `protected`
- Imza: `validateApiRequest(Core\Validation\FormRequest $request, array $payload): Core\Validation\ValidationResult`
- Aciklama: API request nesnesini dogrular.
- Donus: ValidationResult
- Parametreler:
  - `$request` (FormRequest): Request sinifi.

## Core\Application

- Dosya: `core/Application.php`
- Aciklama: Istek yasam dongusunu baslatir ve mevcut istegi router'a devreder.
- Metod sayisi: 8

### __construct

- Erisim: `public`
- Imza: `__construct(Core\Container $container, Core\Http\Request $request, array $routes, array $routerConfig = array (
), array $debugConfig = array (
))`
- Parametreler:
  - `$container` (Container): Mevcut uygulama icin ortak servis kapsayicisi.
  - `$request` (Request): Mevcut HTTP istek nesnesi.
  - `$routes` (array): Statik rota tanimlari.
  - `$routerConfig` (array): Dinamik router yapilandirmasi.
  - `$debugConfig` (array): Hata ve debug davranis ayarlari.

### buildDebugHtml

- Erisim: `private`
- Imza: `buildDebugHtml(Throwable $throwable): string`
- Aciklama: Local gelistirme ortami icin detayli hata HTML'ini olusturur.
- Donus: string
- Parametreler:
  - `$throwable` (Throwable): Yakalanan hata.

### buildProductionHtml

- Erisim: `private`
- Imza: `buildProductionHtml(): string`
- Aciklama: Production ortami icin sade hata HTML'ini olusturur.
- Donus: string

### isApiRequest

- Erisim: `private`
- Imza: `isApiRequest(): bool`
- Aciklama: Mevcut istegin API istegi olup olmadigini belirtir.
- Donus: bool

### isDebugEnabled

- Erisim: `private`
- Imza: `isDebugEnabled(): bool`
- Aciklama: Debug modunun acik olup olmadigini dondurur.
- Donus: bool

### logExceptionToFile

- Erisim: `private`
- Imza: `logExceptionToFile(Throwable $throwable): void`
- Aciklama: Hatayi dosya tabanli uygulama loguna yazar.
- Donus: void
- Parametreler:
  - `$throwable` (Throwable): Yakalanan hata.

### renderExceptionResponse

- Erisim: `private`
- Imza: `renderExceptionResponse(Throwable $throwable): Core\Http\Response`
- Aciklama: Ortama gore uygun hata yanitini uretir.
- Donus: Response
- Parametreler:
  - `$throwable` (Throwable): Yakalanan hata.

### run

- Erisim: `public`
- Imza: `run(): void`
- Aciklama: Mevcut istegi dagitirir ve uretilen yaniti gonderir.

## Core\Cache\CacheInterface

- Dosya: `core/Cache/CacheInterface.php`
- Aciklama: Framework tarafindan kullanilan asgari cache islemlerini tanimlar.
- Metod sayisi: 4

### forget

- Erisim: `public`
- Imza: `forget(string $key): void`
- Aciklama: Anahtara gore cache degerini siler.
- Parametreler:
  - `$key` (string): Silinecek cache anahtari.

### get

- Erisim: `public`
- Imza: `get(string $key, mixed $default = NULL): mixed`
- Aciklama: Anahtara gore cache degerini getirir.
- Donus: mixed
- Parametreler:
  - `$key` (string): Cache arama anahtari.
  - `$default` (mixed): Cache oge bulunmazsa kullanilacak varsayilan deger.

### put

- Erisim: `public`
- Imza: `put(string $key, mixed $value, int $ttl = NULL): void`
- Aciklama: Verilen TTL icin degeri cache'e yazar.
- Parametreler:
  - `$key` (string): Cache saklama anahtari.
  - `$value` (mixed): Cache'lenecek deger.
  - `$ttl` (int|null): Saniye cinsinden yasam suresi. Null ise varsayilan TTL kullanilir.

### remember

- Erisim: `public`
- Imza: `remember(string $key, callable $callback, int $ttl = NULL): mixed`
- Aciklama: Cache'deki degeri dondurur ya da ilk erisimde hesaplayip kaydeder.
- Donus: mixed
- Parametreler:
  - `$key` (string): Cache anahtari.
  - `$callback` (callable): Cache bos oldugunda deger uretecek cagrilabilir.
  - `$ttl` (int|null): Saniye cinsinden yasam suresi. Null ise varsayilan TTL kullanilir.

## Core\Cache\FileCache

- Dosya: `core/Cache/FileCache.php`
- Aciklama: Basit dosya tabanli cache surucusu.
- Metod sayisi: 6

### __construct

- Erisim: `public`
- Imza: `__construct(string $cachePath, int $defaultTtl = 300)`
- Parametreler:
  - `$cachePath` (string): Cache dosyalarinin tutuldugu dizin.
  - `$defaultTtl` (int): Varsayilan cache suresi saniye cinsindendir.

### filePath

- Erisim: `private`
- Imza: `filePath(string $key): string`
- Aciklama: Verilen cache anahtarini saklamak icin kullanilan dosya yolunu cozer.
- Donus: string
- Parametreler:
  - `$key` (string): Cache anahtari.

### forget

- Erisim: `public`
- Imza: `forget(string $key): void`

### get

- Erisim: `public`
- Imza: `get(string $key, mixed $default = NULL): mixed`

### put

- Erisim: `public`
- Imza: `put(string $key, mixed $value, int $ttl = NULL): void`

### remember

- Erisim: `public`
- Imza: `remember(string $key, callable $callback, int $ttl = NULL): mixed`

## Core\Container

- Dosya: `core/Container.php`
- Aciklama: Singleton baglamalari ve reflection tabanli otomatik baglama kullanan hafif servis kapsayicisi.
- Metod sayisi: 6

### build

- Erisim: `private`
- Imza: `build(string $class): object`
- Aciklama: Kurucu bagimliliklarini yinelemeli cozumleyerek bir sinif nesnesi olusturur.
- Donus: object
- Parametreler:
  - `$class` (string): Tam nitelikli sinif adi.

### get

- Erisim: `public`
- Imza: `get(string $id): mixed`
- Aciklama: Kapsayicidan bir servisi cozer.
- Donus: mixed
- Parametreler:
  - `$id` (string): Servis kimligi ya da sinif adi.

### has

- Erisim: `public`
- Imza: `has(string $id): bool`
- Aciklama: Verilen servis kimliginin cozulup cozulmeyecegini kontrol eder.
- Donus: bool
- Parametreler:
  - `$id` (string): Servis kimligi ya da sinif adi.

### resolveViewForClass

- Erisim: `private`
- Imza: `resolveViewForClass(string $class): Core\View\View`
- Aciklama: Controller namespace'ine gore dogru View servisini cozer.
- Donus: View
- Parametreler:
  - `$class` (string): Controller sinif adi.

### shouldCacheAutoBuilt

- Erisim: `private`
- Imza: `shouldCacheAutoBuilt(string $class): bool`
- Aciklama: Reflection ile uretilen sinifin kapsayicida cache'lenip cache'lenmeyecegini belirtir.
- Donus: bool
- Parametreler:
  - `$class` (string): Tam nitelikli sinif adi.

### singleton

- Erisim: `public`
- Imza: `singleton(string $id, Closure $factory): void`
- Aciklama: Verilen servis kimligi icin singleton fabrika kaydi yapar.
- Parametreler:
  - `$id` (string): Servis kimligi ya da sinif adi.
  - `$factory` (Closure): Servis nesnesini donduren fabrika.

## Core\Controller

- Dosya: `core/Controller.php`
- Aciklama: HTML, yonlendirme ve JSON yanitlari icin kolaylastirici yardimcilari olan temel controller.
- Metod sayisi: 7

### __construct

- Erisim: `public`
- Imza: `__construct(Core\View\View $view)`
- Parametreler:
  - `$view` (View): Controller alanina atanmis view goruntuleyicisi.

### actionPermissions

- Erisim: `public`
- Imza: `actionPermissions(): array`
- Aciklama: Action bazli gerekli yetki listesini dondurur.
- Donus: array<string, string|array<int, string>>

### json

- Erisim: `protected`
- Imza: `json(array $data, int $status = 200): Core\Http\Response`
- Aciklama: JSON yaniti olusturur.
- Donus: Response
- Parametreler:
  - `$data` (array): JSON verisi.
  - `$status` (int): HTTP durum kodu.

### jsonResult

- Erisim: `protected`
- Imza: `jsonResult(Core\Services\ServiceResult $result, int $successStatus = 200): Core\Http\Response`
- Aciklama: Servis sonuc nesnesini framework'un JSON yanit bicimine cevirir.
- Donus: Response
- Parametreler:
  - `$result` (ServiceResult): Yapilandirilmis servis sonucu.
  - `$successStatus` (int): Sonuc basarili oldugunda kullanilacak HTTP durum kodu.

### permissionsForAction

- Erisim: `public`
- Imza: `permissionsForAction(string $action): array`
- Aciklama: Verilen action icin gerekli yetki listesini dondurur.
- Donus: array<int, string>
- Parametreler:
  - `$action` (string): Action adi.

### redirect

- Erisim: `protected`
- Imza: `redirect(string $location, int $status = 302): Core\Http\Response`
- Aciklama: HTTP yonlendirme yaniti olusturur.
- Donus: Response
- Parametreler:
  - `$location` (string): Hedef URL ya da yol.
  - `$status` (int): Yonlendirme durum kodu.

### render

- Erisim: `protected`
- Imza: `render(string $template, array $data = array (
), string $layout = NULL): Core\Http\Response`
- Aciklama: Bir HTML sablonunu render eder ve Response nesnesine sarar.
- Donus: Response
- Parametreler:
  - `$template` (string): View adi.
  - `$data` (array): View verisi.
  - `$layout` (string|null): Opsiyonel layout ezmesi.

## Core\Database

- Dosya: `core/Database.php`
- Aciklama: Yapilandirilmis veritabani surucusu icin PDO baglantisi olusturur.
- Metod sayisi: 6

### __construct

- Erisim: `public`
- Imza: `__construct(array $config)`
- Parametreler:
  - `$config` (array): Veritabani baglanti ayarlari.

### dsn

- Erisim: `private`
- Imza: `dsn(string $driver): string`
- Aciklama: Yapilandirilmis surucu icin dogru DSN metnini olusturur.
- Donus: string
- Parametreler:
  - `$driver` (string): Veritabani surucu adi.

### mysqlDsn

- Erisim: `private`
- Imza: `mysqlDsn(): string`
- Aciklama: MySQL DSN'ini olusturur.
- Donus: string

### pdo

- Erisim: `public`
- Imza: `pdo(): PDO`
- Aciklama: Hazirlanmis PDO nesnesini dondurur.
- Donus: PDO

### pgsqlDsn

- Erisim: `private`
- Imza: `pgsqlDsn(): string`
- Aciklama: PostgreSQL DSN'ini olusturur.
- Donus: string

### sqliteDsn

- Erisim: `private`
- Imza: `sqliteDsn(): string`
- Aciklama: SQLite DSN'ini olusturur.
- Donus: string

## Core\Http\ApiResponse

- Dosya: `core/Http/ApiResponse.php`
- Aciklama: API yanitlarini tek bir standartta uretir.
- Metod sayisi: 4

### error

- Erisim: `public`
- Imza: `error(string $message, int $status = 422, array $data = array (
), array $error = array (
), array $meta = array (
), array $headers = array (
)): Core\Http\Response`
- Donus: Response
- Parametreler:
  - `$message` (string): Hata mesaji.
  - `$status` (int): HTTP durum kodu.

### fromServiceResult

- Erisim: `public`
- Imza: `fromServiceResult(Core\Services\ServiceResult $result, int $successStatus = 200, array $meta = array (
), array $headers = array (
)): Core\Http\Response`
- Donus: Response
- Parametreler:
  - `$result` (ServiceResult): Servis sonucu.
  - `$successStatus` (int): Basarili durumda kullanilacak durum kodu.

### normalizeMeta

- Erisim: `private`
- Imza: `normalizeMeta(array $meta): array`
- Donus: array<string, mixed>

### success

- Erisim: `public`
- Imza: `success(string $message, array $data = array (
), int $status = 200, array $meta = array (
), array $headers = array (
)): Core\Http\Response`
- Donus: Response
- Parametreler:
  - `$message` (string): Basari mesaji.
  - `$status` (int): HTTP durum kodu.

## Core\Http\Request

- Dosya: `core/Http/Request.php`
- Aciklama: Method, girdi, dosya ve header'lar icin degismez HTTP istek sarmalayicisi.
- Metod sayisi: 18

### __construct

- Erisim: `public`
- Imza: `__construct(string $method, string $path, array $query = array (
), array $post = array (
), array $files = array (
))`
- Parametreler:
  - `$method` (string): HTTP metodu.
  - `$path` (string): Normalize edilmis istek yolu.
  - `$query` (array): Query string verisi.
  - `$post` (array): Form verisi.
  - `$files` (array): Yuklenen dosyalar.

### all

- Erisim: `public`
- Imza: `all(): array`
- Aciklama: Tum POST verisini dondurur.
- Donus: array

### attribute

- Erisim: `public`
- Imza: `attribute(string $key, mixed $default = NULL): mixed`
- Aciklama: Daha once kaydedilen bir attribute degerini okur.
- Donus: mixed
- Parametreler:
  - `$key` (string): Attribute anahtari.
  - `$default` (mixed): Bulunmazsa donecek varsayilan deger.

### bearerToken

- Erisim: `public`
- Imza: `bearerToken(string $headerName = 'Authorization'): string`
- Aciklama: Verilen header'dan bearer token ya da ham token degerini cikarir.
- Donus: string|null
- Parametreler:
  - `$headerName` (string): Incelenecek header.

### capture

- Erisim: `public`
- Imza: `capture(): Core\Http\Request`
- Aciklama: PHP superglobal'lerinden bir Request nesnesi olusturur.
- Donus: self

### file

- Erisim: `public`
- Imza: `file(string $key): array`
- Aciklama: Alan adina gore yuklenen dosya metadatasini dondurur.
- Donus: array|null
- Parametreler:
  - `$key` (string): Dosya input adi.

### filesAll

- Erisim: `public`
- Imza: `filesAll(): array`
- Aciklama: Tum yuklenen dosya verisini dondurur.
- Donus: array

### header

- Erisim: `public`
- Imza: `header(string $key, mixed $default = NULL): mixed`
- Aciklama: Bir HTTP header degerini okur.
- Donus: mixed
- Parametreler:
  - `$key` (string): Header adi.
  - `$default` (mixed): Bulunmazsa kullanilacak varsayilan deger.

### headers

- Erisim: `private`
- Imza: `headers(): array`
- Aciklama: PHP server degiskenlerini buyuk harfli bir header haritasina donusturur.
- Donus: array

### headersAll

- Erisim: `public`
- Imza: `headersAll(): array`
- Aciklama: Tum header verisini dondurur.
- Donus: array

### input

- Erisim: `public`
- Imza: `input(string $key, mixed $default = NULL): mixed`
- Aciklama: Bir POST degerini getirir.
- Donus: mixed
- Parametreler:
  - `$key` (string): Girdi anahtari.
  - `$default` (mixed): Bulunmazsa kullanilacak varsayilan deger.

### ip

- Erisim: `public`
- Imza: `ip(): string`
- Aciklama: Istek yapan istemcinin IP adresini dondurur.
- Donus: string

### method

- Erisim: `public`
- Imza: `method(): string`
- Aciklama: HTTP metodunu dondurur.
- Donus: string

### path

- Erisim: `public`
- Imza: `path(): string`
- Aciklama: Normalize edilmis istek yolunu dondurur.
- Donus: string

### query

- Erisim: `public`
- Imza: `query(string $key, mixed $default = NULL): mixed`
- Aciklama: Bir query string degerini getirir.
- Donus: mixed
- Parametreler:
  - `$key` (string): Query anahtari.
  - `$default` (mixed): Bulunmazsa kullanilacak varsayilan deger.

### queryAll

- Erisim: `public`
- Imza: `queryAll(): array`
- Aciklama: Tum query string verisini dondurur.
- Donus: array

### setAttribute

- Erisim: `public`
- Imza: `setAttribute(string $key, mixed $value): void`
- Aciklama: Istek omru boyunca kullanilacak ek attribute degeri yazar.
- Donus: void
- Parametreler:
  - `$key` (string): Attribute anahtari.
  - `$value` (mixed): Saklanacak deger.

### userAgent

- Erisim: `public`
- Imza: `userAgent(): string`
- Aciklama: Istemcinin user agent bilgisini dondurur.
- Donus: string

## Core\Http\Response

- Dosya: `core/Http/Response.php`
- Aciklama: HTML, yonlendirme ve JSON verileri icin yardimcilari olan HTTP yanit nesnesi.
- Metod sayisi: 8

### __construct

- Erisim: `public`
- Imza: `__construct(string $body, int $status = 200, array $headers = array (
  'Content-Type' => 'text/html; charset=UTF-8',
))`
- Parametreler:
  - `$body` (string): Yanit govde icerigi.
  - `$status` (int): HTTP durum kodu.
  - `$headers` (array): Yanit header'lari.

### body

- Erisim: `public`
- Imza: `body(): string`
- Aciklama: Yanit govdesini dondurur.
- Donus: string

### headers

- Erisim: `public`
- Imza: `headers(): array`
- Aciklama: Yanit header verisini dondurur.
- Donus: array

### html

- Erisim: `public`
- Imza: `html(string $body, int $status = 200, array $headers = array (
)): Core\Http\Response`
- Aciklama: HTML yaniti olusturur.
- Donus: self
- Parametreler:
  - `$body` (string): HTML icerigi.
  - `$status` (int): HTTP durum kodu.
  - `$headers` (array): Ek header'lar.

### json

- Erisim: `public`
- Imza: `json(array $data, int $status = 200, array $headers = array (
)): Core\Http\Response`
- Aciklama: JSON yaniti olusturur.
- Donus: self
- Parametreler:
  - `$data` (array): JSON'a cevrilebilir veri.
  - `$status` (int): HTTP durum kodu.
  - `$headers` (array): Ek header'lar.

### redirect

- Erisim: `public`
- Imza: `redirect(string $location, int $status = 302): Core\Http\Response`
- Aciklama: Yonlendirme yaniti olusturur.
- Donus: self
- Parametreler:
  - `$location` (string): Hedef URL ya da yol.
  - `$status` (int): Yonlendirme durum kodu.

### send

- Erisim: `public`
- Imza: `send(): void`
- Aciklama: Yaniti istemciye gonderir.

### status

- Erisim: `public`
- Imza: `status(): int`
- Aciklama: HTTP durum kodunu dondurur.
- Donus: int

## Core\Localization\LanguageService

- Dosya: `core/Localization/LanguageService.php`
- Aciklama: Aktif dili ve veritabanindaki ceviri anahtarlarini yonetir.
- Metod sayisi: 14

### __construct

- Erisim: `public`
- Imza: `__construct(Core\Session $session, Core\Http\Request $request, array $config = array (
))`
- Parametreler:
  - `$session` (Session): Aktif dil secimini oturumda saklayan servis.
  - `$request` (Request): Mevcut HTTP istegi.
  - `$config` (array): Dil ayarlari.

### activeLanguage

- Erisim: `public`
- Imza: `activeLanguage(): array`
- Aciklama: Aktif dil kaydini dondurur.
- Donus: array<string, mixed>

### availableLanguages

- Erisim: `private`
- Imza: `availableLanguages(): array`
- Aciklama: Veritabanindan aktif dilleri listeler.
- Donus: array<int, array<string, mixed>>

### defaultLanguage

- Erisim: `private`
- Imza: `defaultLanguage(array $languages): array`
- Aciklama: Varsayilan dili dil listesi icinden bulur.
- Donus: array<string, mixed>|null

### ensurePlaceholderTranslation

- Erisim: `private`
- Imza: `ensurePlaceholderTranslation(string $key, string $value): void`
- Aciklama: Placeholder anahtari icin varsayilan ve bos ceviri kayitlarini olusturur.
- Donus: void
- Parametreler:
  - `$key` (string): Placeholder anahtari.
  - `$value` (string): Varsayilan dilde saklanacak gorunen metin.

### fallbackTranslations

- Erisim: `private`
- Imza: `fallbackTranslations(): array`
- Aciklama: Varsayilan dildeki cevirileri dondurur.
- Donus: array<string, string>

### get

- Erisim: `public`
- Imza: `get(string $key, string $default = ''): string`
- Aciklama: Verilen anahtar icin aktif dildeki karsiligi dondurur.
- Donus: string
- Parametreler:
  - `$key` (string): Ceviri anahtari.
  - `$default` (string): Ceviri bulunmazsa donulecek deger.

### insertTranslationIfMissing

- Erisim: `private`
- Imza: `insertTranslationIfMissing(int $languageId, string $key, string $value, int $timestamp): void`
- Aciklama: Ceviri kaydi yoksa yeni satir olusturur.
- Donus: void
- Parametreler:
  - `$languageId` (int): Dil kaydi id degeri.
  - `$key` (string): Ceviri anahtari.
  - `$value` (string): Ceviri degeri.
  - `$timestamp` (int): Zaman damgasi.

### loadTranslations

- Erisim: `private`
- Imza: `loadTranslations(int $languageId): array`
- Aciklama: Belirli bir dil kaydi icin ceviri verilerini yukler.
- Donus: array<string, string>
- Parametreler:
  - `$languageId` (int): Dil kaydi id degeri.

### locale

- Erisim: `public`
- Imza: `locale(): string`
- Aciklama: Aktif dil kodunu dondurur.
- Donus: string

### matchLanguage

- Erisim: `private`
- Imza: `matchLanguage(array $languages, string $code): array`
- Aciklama: Dil listesinde istenen kodu arar.
- Donus: array<string, mixed>|null
- Parametreler:
  - `$code` (string): Aranan dil kodu.

### normalizeRows

- Erisim: `private`
- Imza: `normalizeRows(object|array|false $rows): array`
- Aciklama: Model sonucunu tutarli bir satir listesine donusturur.
- Donus: array<int, array<string, mixed>>
- Parametreler:
  - `$rows` (object|array|false): Model sonuc kumesi.

### translatePlaceholder

- Erisim: `public`
- Imza: `translatePlaceholder(string $placeholder): string`
- Aciklama: Placeholder icindeki metni ceviri anahtari olarak cozer.
- Donus: string
- Parametreler:
  - `$placeholder` (string): Template icindeki placeholder metni.

### translations

- Erisim: `public`
- Imza: `translations(): array`
- Aciklama: Tum aktif dil cevirilerini dondurur.
- Donus: array<string, string>

## Core\Logging\RequestLogger

- Dosya: `core/Logging/RequestLogger.php`
- Aciklama: Gelen ve giden HTTP isteklerini veritabanindaki log tablosuna kaydeder.
- Metod sayisi: 7

### __construct

- Erisim: `public`
- Imza: `__construct(array $config = array (
))`
- Parametreler:
  - `$config` (array): Request log davranis ayarlari.

### encode

- Erisim: `private`
- Imza: `encode(array $payload): string`
- Aciklama: Veriyi JSON metnine donusturur.
- Donus: string
- Parametreler:
  - `$payload` (array): Kaydedilecek veri.

### limit

- Erisim: `private`
- Imza: `limit(string $value, int $length): string`
- Aciklama: Metin alanlarini makul boyutta sinirlar.
- Donus: string
- Parametreler:
  - `$value` (string): Kaydedilecek metin.
  - `$length` (int): Azami karakter sayisi.

### log

- Erisim: `public`
- Imza: `log(Core\Http\Request $request, Core\Http\Response $response, Throwable $exception = NULL): void`
- Aciklama: Istek ve yanit bilgisini log tablosuna yazar.
- Donus: void
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.
  - `$response` (Response): Uretilen HTTP yaniti.
  - `$exception` (Throwable|null): Varsa olusan hata nesnesi.

### maskRecursive

- Erisim: `private`
- Imza: `maskRecursive(array $payload): array`
- Aciklama: Hassas anahtarlari maskeleyerek kayit altina alinacak veriyi temizler.
- Donus: array
- Parametreler:
  - `$payload` (array): Kaydedilecek veri.

### responseBodyForLogging

- Erisim: `private`
- Imza: `responseBodyForLogging(Core\Http\Request $request, Core\Http\Response $response): string`
- Aciklama: Response body kaydini rota bazli kurallarla uretir.
- Donus: string
- Parametreler:
  - `$request` (Request): Mevcut istek.
  - `$response` (Response): Uretilen yanit.

### shouldMaskKey

- Erisim: `private`
- Imza: `shouldMaskKey(string $key): bool`
- Aciklama: Belirli bir anahtarin logda maskelenip maskelenmeyecegini belirtir.
- Donus: bool
- Parametreler:
  - `$key` (string): Veri anahtari.

## Core\Orm\Models

- Dosya: `core/Orm/Models.php`
- Aciklama: Hafif aktif-kayit tarzi model ve sorgu kurucu temel sinifi.
- Metod sayisi: 48

### __construct

- Erisim: `public`
- Imza: `__construct()`
- Aciklama: Model kullanilmadan once veritabani baglantisinin hazir oldugunu dogrular.

### all

- Erisim: `public`
- Imza: `all(): object|array|false`
- Aciklama: Olusturulan select sorgusunu calistirir ve sonucu modele doldurur.
- Donus: object|array|false Doldurulmus sonuc kumesi.

### bindParams

- Erisim: `protected`
- Imza: `bindParams(PDOStatement $stmt, array $params): void`
- Aciklama: Deger dizisini hazirlanmis PDO ifadesine baglar.
- Donus: void
- Parametreler:
  - `$stmt` (PDOStatement): Hazirlanmis ifade.

### buildAggregateQuery

- Erisim: `protected`
- Imza: `buildAggregateQuery(string $aggregateSelect): array`
- Aciklama: Mevcut WHERE kosullarini kullanarak aggregate sorgu olusturur.
- Donus: array{0: string, 1: array<string, mixed>} Sorgu SQL ifadesi ve parametreleri.
- Parametreler:
  - `$aggregateSelect` (string): Aggregate ifadesi.

### buildLimitClause

- Erisim: `protected`
- Imza: `buildLimitClause(array $params): string`
- Aciklama: Aktif veritabani surucusu icin LIMIT/OFFSET SQL parcasini olusturur.
- Donus: string LIMIT bolumu ya da bos metin.

### buildOrderClause

- Erisim: `protected`
- Imza: `buildOrderClause(): string`
- Aciklama: ORDER BY SQL parcasini olusturur.
- Donus: string ORDER BY parcasi ya da bos metin.

### buildSelectColumns

- Erisim: `protected`
- Imza: `buildSelectColumns(): string`
- Aciklama: Secilen sutunlar icin SQL parcasini olusturur.
- Donus: string SELECT sutun parcasi.

### buildSelectQuery

- Erisim: `protected`
- Imza: `buildSelectQuery(): array`
- Aciklama: Aktif sorgu icin SELECT SQL ifadesini ve bagli parametreleri olusturur.
- Donus: array{0: string, 1: array<string, mixed>} Sorgu SQL ifadesi ve parametreleri.

### count

- Erisim: `public`
- Imza: `count(): int`
- Aciklama: Mevcut sorgu kosullarina uyan satirlari sayar.
- Donus: int Eslesen satir sayisi.

### db

- Erisim: `public`
- Imza: `db(): PDO`
- Aciklama: Ortak PDO baglantisini dondurur.
- Donus: PDO Aktif PDO baglantisi.

### delete

- Erisim: `public`
- Imza: `delete(mixed $find = 'id', string $field = 'id'): object`
- Aciklama: Verilen alana ya da model id degerine gore kaydi siler.
- Donus: object Islem sonuc nesnesi.
- Parametreler:
  - `$find` (mixed): Eslesecek deger ya da mevcut model id degerini kullanmak icin "id".
  - `$field` (string): Silme kosulunda kullanilan alan adi.

### describeMysqlTable

- Erisim: `protected`
- Imza: `describeMysqlTable(string $table): array`
- Aciklama: Verilen tablo icin MySQL sutun metadatasini okur.
- Donus: array<int, array{name: string, type: string}> Sutun metadatasi.
- Parametreler:
  - `$table` (string): Tablo adi.

### describePgsqlTable

- Erisim: `protected`
- Imza: `describePgsqlTable(string $table): array`
- Aciklama: Verilen tablo icin PostgreSQL sutun metadatasini okur.
- Donus: array<int, array{name: string, type: string}> Sutun metadatasi.
- Parametreler:
  - `$table` (string): Tablo adi.

### describeSqliteTable

- Erisim: `protected`
- Imza: `describeSqliteTable(string $table): array`
- Aciklama: Verilen tablo icin SQLite sutun metadatasini okur.
- Donus: array<int, array{name: string, type: string}> Sutun metadatasi.
- Parametreler:
  - `$table` (string): Tablo adi.

### describeTable

- Erisim: `protected`
- Imza: `describeTable(string $table): array`
- Aciklama: Aktif surucuyu kullanarak bir tablonun sutun listesini okur.
- Donus: array<int, array{name: string, type: string}> Sutun metadatasi.
- Parametreler:
  - `$table` (string): Tablo adi.

### dirtyAttributes

- Erisim: `protected`
- Imza: `dirtyAttributes(): array`
- Aciklama: Son referans durumdan farkli olan alanlari dondurur.
- Donus: array<string, mixed> Degisen alanlar.

### exists

- Erisim: `public`
- Imza: `exists(): bool`
- Aciklama: Mevcut sorguya uyan en az bir kayit olup olmadigini kontrol eder.
- Donus: bool Kayit varsa true.

### fill

- Erisim: `public`
- Imza: `fill(object $data): object`
- Aciklama: Mevcut modeli kaynak nesnedeki degerlerle doldurur.
- Donus: object Mevcut model nesnesi.
- Parametreler:
  - `$data` (object): Kaynak nesne.

### filter

- Erisim: `public`
- Imza: `filter(array $allowed = array (
)): object`
- Aciklama: Sadece izin verilen alanlari iceren bir kopya dondurur.
- Donus: object Filtrelenmis model kopyasi.

### find

- Erisim: `public`
- Imza: `find(mixed $find, string $field = 'id', string $orderField = 'id', string $order = 'DESC', bool $like = false): object|array|false`
- Aciklama: Tek bir alana gore ve istege bagli LIKE aramasi ile kayit bulur.
- Donus: object|array|false Doldurulmus sonuc kumesi.
- Parametreler:
  - `$find` (mixed): Aranacak deger.
  - `$field` (string): Arama yapilacak alan.
  - `$orderField` (string): Siralama icin kullanilacak alan.
  - `$order` (string): Siralama yonu.
  - `$like` (bool): LIKE karsilastirmasi kullanilip kullanilmayacagi.

### first

- Erisim: `public`
- Imza: `first(): object|false`
- Aciklama: Eslesen ilk kaydi dondurur.
- Donus: object|false Ilk doldurulmus kayit ya da bossa false.

### get

- Erisim: `public`
- Imza: `get(string $name): object`
- Aciklama: Tablo adindan genel bir model nesnesi olusturur.
- Donus: object Tablo sutunlariyla doldurulmus genel model nesnesi.
- Parametreler:
  - `$name` (string): Tablo adi.

### getPdoType

- Erisim: `protected`
- Imza: `getPdoType(mixed $value): int`
- Aciklama: Bir deger icin uygun PDO parametre tipini belirler.
- Donus: int PDO parametre tipi sabiti.
- Parametreler:
  - `$value` (mixed): Baglanacak deger.

### hydrateResult

- Erisim: `protected`
- Imza: `hydrateResult(array $models): object|array|false`
- Aciklama: Ham sorgu satirlarini doldurulmus model nesnelerine donusturur.
- Donus: object|array|false Doldurulmus sonuc kumesi.

### limit

- Erisim: `public`
- Imza: `limit(int $limit, int $offset = 0): static`
- Aciklama: Sorguya limit ve ofset degerlerini uygular.
- Donus: static Kopyalanmis sorgu nesnesi.
- Parametreler:
  - `$limit` (int): Maksimum satir adedi.
  - `$offset` (int): Baslangic ofset degeri.

### limitClause

- Erisim: `protected`
- Imza: `limitClause(): string`
- Aciklama: Surucuye ozel LIMIT bolumu sablonunu dondurur.
- Donus: string LIMIT bolumu sablonu.

### modelAttributes

- Erisim: `protected`
- Imza: `modelAttributes(): array`
- Aciklama: Yalnizca tabloya ait model alanlarini dondurur.
- Donus: array<string, mixed> Icerik alanlari.

### orWhere

- Erisim: `public`
- Imza: `orWhere(string $field, string|int|float|bool|null $operatorOrValue, mixed $value = NULL): static`
- Aciklama: Sorguya OR where kosulu ekler.
- Donus: static Kopyalanmis sorgu nesnesi.
- Parametreler:
  - `$field` (string): Sutun adi.
  - `$operatorOrValue` (string|int|float|bool|null): Operator ya da dogrudan karsilastirma degeri.
  - `$value` (mixed): Operator verildiginde kullanilacak karsilastirma degeri.

### orderBy

- Erisim: `public`
- Imza: `orderBy(string $field, string $direction = 'ASC'): static`
- Aciklama: ORDER BY bolumu ekler.
- Donus: static Kopyalanmis sorgu nesnesi.
- Parametreler:
  - `$field` (string): Sutun adi.
  - `$direction` (string): Siralama yonu.

### paginate

- Erisim: `public`
- Imza: `paginate(int $page = 1, int $perPage = 20): object`
- Aciklama: Sorgu sonuclarini sayfalama metadatasi ile birlikte dondurur.
- Donus: object Veri ve meta alanlarini iceren sayfalama sonucu.
- Parametreler:
  - `$page` (int): Mevcut sayfa numarasi.
  - `$perPage` (int): Sayfa basina kayit adedi.

### pluck

- Erisim: `public`
- Imza: `pluck(string $column): array`
- Aciklama: Tek bir sutundaki degerleri duz bir dizi olarak dondurur.
- Donus: array<int, mixed> Sutun degerleri.
- Parametreler:
  - `$column` (string): Sutun adi.

### quoteIdentifier

- Erisim: `protected`
- Imza: `quoteIdentifier(string $identifier): string`
- Aciklama: Aktif SQL surucusu icin bir tanimlayiciyi tirnaklar.
- Donus: string Tirnaklanmis tanimlayici.
- Parametreler:
  - `$identifier` (string): Tirnaklanacak tanimlayici.

### resetQuery

- Erisim: `protected`
- Imza: `resetQuery(): static`
- Aciklama: Mevcut nesnedeki gecici sorgu kurucu durumunu temizler.
- Donus: static Mevcut model nesnesi.

### runSQL

- Erisim: `public`
- Imza: `runSQL(string $sql, array $params = array (
)): object|array|false`
- Aciklama: Opsiyonel parametrelerle ham SQL sorgusu calistirir.
- Donus: array<int, object>|false|object Sonuc satirlari, bossa false ya da hata nesnesi.
- Parametreler:
  - `$sql` (string): SQL ifadesi.

### sanitizeIdentifier

- Erisim: `protected`
- Imza: `sanitizeIdentifier(string $identifier): string`
- Aciklama: Bir tanimlayicinin yalnizca guvenli tablo/sutun karakterleri icerdigini dogrular.
- Donus: string Temizlenmis tanimlayici.
- Parametreler:
  - `$identifier` (string): Dogrulanacak tanimlayici.

### save

- Erisim: `public`
- Imza: `save(): object|string|int`
- Aciklama: Mevcut model verisini veritabanina ekler.
- Donus: object|int|string Basariliysa eklenen kaydin id degeri, hatada hata nesnesi.

### select

- Erisim: `public`
- Imza: `select(array|string $columns = array (
  0 => '*',
)): static`
- Aciklama: Sorgu icin secilecek sutunlari belirler.
- Donus: static Kopyalanmis sorgu nesnesi.

### setDb

- Erisim: `public`
- Imza: `setDb(PDO $pdo, string $modelsPath = NULL): void`
- Aciklama: Tum model nesnelerinin kullanacagi ortak PDO baglantisini kaydeder.
- Donus: void
- Parametreler:
  - `$pdo` (PDO): Aktif PDO baglantisi.
  - `$modelsPath` (string|null): Geriye donuk uyumluluk icin saklanmistir.

### setSchema

- Erisim: `public`
- Imza: `setSchema(string $schema): void`
- Aciklama: PostgreSQL tablo incelemesi icin varsayilan schema adini ayarlar.
- Donus: void
- Parametreler:
  - `$schema` (string): Schema adi.

### syncOriginalAttributes

- Erisim: `protected`
- Imza: `syncOriginalAttributes(): void`
- Aciklama: Mevcut alanlari temiz referans durum olarak kaydeder.
- Donus: void

### tableName

- Erisim: `protected`
- Imza: `tableName(): string`
- Aciklama: Mevcut model icin temizlenmis tablo adini dondurur.
- Donus: string Tablo adi.

### toarray

- Erisim: `public`
- Imza: `toarray(): array`
- Aciklama: Mevcut model nesnesini diziye cevirir.
- Donus: array<string, mixed> Model ozellikleri.

### touchTimestampsForInsert

- Erisim: `protected`
- Imza: `touchTimestampsForInsert(): void`
- Aciklama: Kayit ekleme oncesinde zaman damgalarini otomatik doldurur.
- Donus: void

### touchTimestampsForUpdate

- Erisim: `protected`
- Imza: `touchTimestampsForUpdate(): void`
- Aciklama: Kayit guncelleme oncesinde guncelleme zaman damgasini yeniler.
- Donus: void

### update

- Erisim: `public`
- Imza: `update(): object`
- Aciklama: Mevcut modeli id alanina gore gunceller.
- Donus: object Islem sonuc nesnesi.

### where

- Erisim: `public`
- Imza: `where(string $field, string|int|float|bool|null $operatorOrValue, mixed $value = NULL): static`
- Aciklama: Sorguya AND where kosulu ekler.
- Donus: static Kopyalanmis sorgu nesnesi.
- Parametreler:
  - `$field` (string): Sutun adi.
  - `$operatorOrValue` (string|int|float|bool|null): Operator ya da dogrudan karsilastirma degeri.
  - `$value` (mixed): Operator verildiginde kullanilacak karsilastirma degeri.

### whereIn

- Erisim: `public`
- Imza: `whereIn(string $field, array $values): static`
- Aciklama: Verilen alan icin IN kosulu ekler.
- Donus: static Kopyalanmis sorgu nesnesi.
- Parametreler:
  - `$field` (string): Sutun adi.

### whereLike

- Erisim: `public`
- Imza: `whereLike(string $field, string $value): static`
- Aciklama: Verilen alan icin LIKE kosulu ekler.
- Donus: static Kopyalanmis sorgu nesnesi.
- Parametreler:
  - `$field` (string): Sutun adi.
  - `$value` (string): Aranacak deger.

## Core\PageController

- Dosya: `core/PageController.php`
- Aciklama: Sayfa odakli controller'lar icin ortak render ve redirect yardimcilari sunar.
- Metod sayisi: 3

### redirectResult

- Erisim: `protected`
- Imza: `redirectResult(Core\Services\ServiceResult $result, string $defaultRedirect): Core\Http\Response`
- Aciklama: Servis sonucunu yalnizca yonlendirme yanitina cevirir.
- Donus: Response
- Parametreler:
  - `$result` (ServiceResult): Islem sonucu.
  - `$defaultRedirect` (string): Sonuc veri setinde hedef yoksa kullanilacak yol.

### renderPageResult

- Erisim: `protected`
- Imza: `renderPageResult(Core\Services\ServiceResult $result, string $template, string $layout = NULL, callable $transform = NULL, string $defaultRedirect = '/'): Core\Http\Response`
- Aciklama: Servis sonucunu sayfa render akisina cevirir.
- Donus: Response
- Parametreler:
  - `$result` (ServiceResult): Sayfa servisi sonucu.
  - `$template` (string): Render edilecek view.
  - `$layout` (string|null): Opsiyonel layout.
  - `$transform` (callable|null): Render oncesi veri donusumu.
  - `$defaultRedirect` (string): Hata halinde varsayilan yonlendirme.

### resolveRedirectStatus

- Erisim: `private`
- Imza: `resolveRedirectStatus(int $status): int`
- Aciklama: Gecersiz durum kodlarini guvenli redirect koduna indirger.
- Donus: int
- Parametreler:
  - `$status` (int): Servis sonucu durum kodu.

## Core\RateLimit\RateLimiter

- Dosya: `core/RateLimit/RateLimiter.php`
- Aciklama: Basit sabit pencere mantigiyla rate limit uygular.
- Metod sayisi: 2

### __construct

- Erisim: `public`
- Imza: `__construct(Core\Cache\FileCache $cache, array $config = array (
))`
- Parametreler:
  - `$cache` (FileCache): Rate limit durumunu saklayan cache.

### hit

- Erisim: `public`
- Imza: `hit(string $key, int $maxAttempts, int $decaySeconds): array`
- Donus: array<string, int|bool>
- Parametreler:
  - `$key` (string): Rate limit anahtari.
  - `$maxAttempts` (int): Azami istek sayisi.
  - `$decaySeconds` (int): Pencere suresi.

## Core\Router

- Dosya: `core/Router.php`
- Aciklama: Statik ve dinamik rotalari cozer, sonra eslesen controller aksiyonunu calistirir.
- Metod sayisi: 20

### __construct

- Erisim: `public`
- Imza: `__construct(Core\Container $container, array $routes, array $config = array (
), Core\Cache\CacheInterface $cache = NULL)`
- Parametreler:
  - `$container` (Container): Controller ve middleware benzeri servisleri cozen servis kapsayicisi.
  - `$routes` (array): Statik rota tanimlari.
  - `$config` (array): Dinamik rota yapilandirmasi.

### authorizationIdentity

- Erisim: `private`
- Imza: `authorizationIdentity(Core\Http\Request $request): array`
- Aciklama: Yetki kontrolu icin uygun kimlik verisini cozer.
- Donus: array<string, mixed>|null
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### cachedDynamicRoute

- Erisim: `private`
- Imza: `cachedDynamicRoute(Core\Http\Request $request): array`
- Aciklama: Cache'te tutulmus dinamik rota cozumunu dondurur.
- Donus: array<string, mixed>|null
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### canInvokeDynamicAction

- Erisim: `private`
- Imza: `canInvokeDynamicAction(ReflectionMethod $method, array $segments): bool`
- Aciklama: Cozulmus action'in verilen URL parametreleriyle cagirilip cagrilamayacagini kontrol eder.
- Donus: bool
- Parametreler:
  - `$method` (ReflectionMethod): Reflection ile cozulmus action.

### dispatch

- Erisim: `public`
- Imza: `dispatch(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Verilen istegi eslesen statik ya da dinamik rotaya yonlendirir.
- Donus: Response
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### dispatchDynamic

- Erisim: `private`
- Imza: `dispatchDynamic(Core\Http\Request $request): Core\Http\Response`
- Aciklama: Statik rota eslesmediginde dinamik rota cozumunu dener.
- Donus: Response|null
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### dispatchResolvedDynamicRoute

- Erisim: `private`
- Imza: `dispatchResolvedDynamicRoute(Core\Http\Request $request, array $resolved): Core\Http\Response`
- Aciklama: Cache'ten gelen cozulmus route'u gecerliyse calistirir.
- Donus: Response|null
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### dynamicRouteCacheKey

- Erisim: `private`
- Imza: `dynamicRouteCacheKey(Core\Http\Request $request): string`
- Aciklama: Dinamik route cache anahtarini uretir.
- Donus: string
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### ensureApiAuth

- Erisim: `private`
- Imza: `ensureApiAuth(Core\Http\Request $request, string $controllerClass = NULL, string $action = NULL): Core\Http\Response`
- Aciklama: API istekleri icin public endpoint listesi disinda token dogrulamasi uygular.
- Donus: Response|null
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.
  - `$controllerClass` (string|null): Cozulmus controller sinifi.
  - `$action` (string|null): Cozulmus action adi.

### ensureApiRateLimit

- Erisim: `private`
- Imza: `ensureApiRateLimit(Core\Http\Request $request): Core\Http\Response`
- Aciklama: API istekleri icin basit rate limit uygular.
- Donus: Response|null
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### ensureAuthorization

- Erisim: `private`
- Imza: `ensureAuthorization(Core\Http\Request $request, string $controllerClass, string $action): Core\Http\Response`
- Aciklama: Controller action icin gerekiyorsa rol/yetki kontrolu yapar.
- Donus: Response|null
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.
  - `$controllerClass` (string|null): Controller sinifi.
  - `$action` (string|null): Action adi.

### ensureCsrf

- Erisim: `private`
- Imza: `ensureCsrf(Core\Http\Request $request): Core\Http\Response`
- Aciklama: POST istekleri icin CSRF korumasi uygular.
- Donus: Response|null
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### forgetDynamicRoute

- Erisim: `private`
- Imza: `forgetDynamicRoute(Core\Http\Request $request): void`
- Aciklama: Dinamik route cache kaydini siler.
- Donus: void
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### isAdminPath

- Erisim: `private`
- Imza: `isAdminPath(string $path): bool`
- Aciklama: Verilen yolun admin alani icinde olup olmadigini belirtir.
- Donus: bool
- Parametreler:
  - `$path` (string): Istek yolu.

### isPublicApiAction

- Erisim: `private`
- Imza: `isPublicApiAction(string $controllerClass, string $action): bool`
- Aciklama: API controller action'inin public olarak isaretli olup olmadigini kontrol eder.
- Donus: bool
- Parametreler:
  - `$controllerClass` (string|null): Controller sinif adi.
  - `$action` (string|null): Action adi.

### rememberDynamicRoute

- Erisim: `private`
- Imza: `rememberDynamicRoute(Core\Http\Request $request, array $resolved): void`
- Aciklama: Basarili dinamik rota cozumunu cache'e yazar.
- Donus: void
- Parametreler:
  - `$request` (Request): Mevcut HTTP istegi.

### resolveMethodCandidates

- Erisim: `private`
- Imza: `resolveMethodCandidates(string $methodSegment, bool $hasExplicitMethodSegment): array`
- Aciklama: Method segmentinden cagri adaylarini uretir.
- Donus: array<int, string>
- Parametreler:
  - `$methodSegment` (string): URL'den cozulmus method bolumu.
  - `$hasExplicitMethodSegment` (bool): URL'de method segmenti olup olmadigi.

### resolvePublicActionMethod

- Erisim: `private`
- Imza: `resolvePublicActionMethod(object $controller, string $method): ReflectionMethod`
- Aciklama: Dinamik dispatch icin sadece public ve cagirilabilir action metodunu cozer.
- Donus: ReflectionMethod|null
- Parametreler:
  - `$controller` (object): Controller nesnesi.
  - `$method` (string): Method adi.

### segmentToCamel

- Erisim: `private`
- Imza: `segmentToCamel(string $segment): string`
- Aciklama: Bir yol segmentini camelCase bicimine cevirir.
- Donus: string
- Parametreler:
  - `$segment` (string): URL segment.

### segmentToStudly

- Erisim: `private`
- Imza: `segmentToStudly(string $segment): string`
- Aciklama: Bir yol segmentini StudlyCase bicimine cevirir.
- Donus: string
- Parametreler:
  - `$segment` (string): URL segment.

## Core\Security\Csrf

- Dosya: `core/Security/Csrf.php`
- Aciklama: CSRF token uretimi, saklama ve dogrulamayi yonetir.
- Metod sayisi: 4

### __construct

- Erisim: `public`
- Imza: `__construct(Core\Session $session)`
- Parametreler:
  - `$session` (Session): Mevcut token'i saklamak icin kullanilan oturum deposu.

### refresh

- Erisim: `public`
- Imza: `refresh(): string`
- Aciklama: Saklanan CSRF token'inin yeniden uretilmesini zorlar.
- Donus: string

### token

- Erisim: `public`
- Imza: `token(): string`
- Aciklama: Gerekirse olusturarak mevcut CSRF token'ini dondurur.
- Donus: string

### verify

- Erisim: `public`
- Imza: `verify(string $token): bool`
- Aciklama: Verilen CSRF token'ini oturum kaydina gore dogrular.
- Donus: bool
- Parametreler:
  - `$token` (string|null): Gonderilen token.

## Core\Services\BaseApiService

- Dosya: `core/Services/BaseApiService.php`
- Aciklama: API odakli servisler icin anlamsal temel servis sinifi.
- Metod sayisi: 0

## Core\Services\BasePageService

- Dosya: `core/Services/BasePageService.php`
- Aciklama: Sayfa odakli servisler icin form, flash ve yonlendirme yardimcilari sunar.
- Metod sayisi: 6

### clearInput

- Erisim: `protected`
- Imza: `clearInput(Core\Session $session, string $prefix, array $fields): void`
- Aciklama: Flash oturumundaki onceki form degerlerini temizler.
- Donus: void
- Parametreler:
  - `$session` (Session): Oturum servisi.
  - `$prefix` (string): Flash anahtar on eki.

### flashInput

- Erisim: `protected`
- Imza: `flashInput(Core\Session $session, string $prefix, array $payload, array $fields): void`
- Aciklama: Form alanlarini flash oturumuna yazar.
- Donus: void
- Parametreler:
  - `$session` (Session): Oturum servisi.
  - `$prefix` (string): Flash anahtar on eki.

### flashValidationErrors

- Erisim: `protected`
- Imza: `flashValidationErrors(Core\Session $session, string $messageKey, string $message, Core\Validation\ValidationResult $validation, array $fieldFlashMap = array (
)): void`
- Aciklama: Dogrulama hatalarini flash oturumuna yazar.
- Donus: void
- Parametreler:
  - `$session` (Session): Oturum servisi.
  - `$messageKey` (string): Genel hata mesaj anahtari.
  - `$message` (string): Genel hata mesaji.
  - `$validation` (ValidationResult): Dogrulama sonucu.

### oldInput

- Erisim: `protected`
- Imza: `oldInput(Core\Session $session, string $prefix, string $field, string $default = ''): string`
- Aciklama: Flash oturumundan onceki form degerini okur.
- Donus: string
- Parametreler:
  - `$session` (Session): Oturum servisi.
  - `$prefix` (string): Flash anahtar on eki.
  - `$field` (string): Alan adi.
  - `$default` (string): Varsayilan deger.

### redirectError

- Erisim: `protected`
- Imza: `redirectError(string $message, string $redirectTo, int $status = 422, array $data = array (
)): Core\Services\ServiceResult`
- Aciklama: Yonlendirme bekleyen hatali servis sonucu olusturur.
- Donus: ServiceResult
- Parametreler:
  - `$message` (string): Hata mesaji.
  - `$redirectTo` (string): Hedef yol.
  - `$status` (int): Onerilen HTTP durum kodu.

### redirectSuccess

- Erisim: `protected`
- Imza: `redirectSuccess(string $message, string $redirectTo, int $status = 302, array $data = array (
)): Core\Services\ServiceResult`
- Aciklama: Yonlendirme bekleyen basarili servis sonucu olusturur.
- Donus: ServiceResult
- Parametreler:
  - `$message` (string): Durum mesaji.
  - `$redirectTo` (string): Hedef yol.
  - `$status` (int): Onerilen HTTP durum kodu.

## Core\Services\BaseService

- Dosya: `core/Services/BaseService.php`
- Aciklama: Ortak servis sonuc yardimcilari sunan temel servis sinifi.
- Metod sayisi: 2

### error

- Erisim: `protected`
- Imza: `error(string $message, array $data = array (
), int $status = 422): Core\Services\ServiceResult`
- Aciklama: Hatali servis sonucu olusturur.
- Donus: ServiceResult
- Parametreler:
  - `$message` (string): Hata mesaji.
  - `$data` (array): Ek sonuc verisi.
  - `$status` (int): Onerilen HTTP durum kodu.

### success

- Erisim: `protected`
- Imza: `success(string $message, array $data = array (
), int $status = 200): Core\Services\ServiceResult`
- Aciklama: Basarili servis sonucu olusturur.
- Donus: ServiceResult
- Parametreler:
  - `$message` (string): Durum mesaji.
  - `$data` (array): Sonuc verisi.
  - `$status` (int): Onerilen HTTP durum kodu.

## Core\Services\ServiceResult

- Dosya: `core/Services/ServiceResult.php`
- Aciklama: Yapilandirilmis basari/hata yanitlari icin standart servis katmani sonuc nesnesi.
- Metod sayisi: 8

### __construct

- Erisim: `private`
- Imza: `__construct(bool $isSuccess, string $message, array $data = array (
), int $status = 200)`
- Parametreler:
  - `$isSuccess` (bool): Servis cagrisinin basarili olup olmadigini belirtir.
  - `$message` (string): Insan tarafindan okunabilir durum mesaji.
  - `$data` (array): Sonuca eklenen veri yuku.
  - `$status` (int): Onerilen HTTP durum kodu.

### data

- Erisim: `public`
- Imza: `data(): array`
- Aciklama: Sonuca ekli veri yukunu dondurur.
- Donus: array

### error

- Erisim: `public`
- Imza: `error(string $message, array $data = array (
), int $status = 422): Core\Services\ServiceResult`
- Aciklama: Hatali bir servis sonucu olusturur.
- Donus: self
- Parametreler:
  - `$message` (string): Insan tarafindan okunabilir hata mesaji.
  - `$data` (array): Ek hata verisi.
  - `$status` (int): Onerilen HTTP durum kodu.

### isSuccess

- Erisim: `public`
- Imza: `isSuccess(): bool`
- Aciklama: Sonucun basariyi temsil edip etmedigini belirtir.
- Donus: bool

### message

- Erisim: `public`
- Imza: `message(): string`
- Aciklama: Sonucun insan tarafindan okunabilir mesajini dondurur.
- Donus: string

### status

- Erisim: `public`
- Imza: `status(): int`
- Aciklama: Onerilen HTTP durum kodunu dondurur.
- Donus: int

### success

- Erisim: `public`
- Imza: `success(string $message, array $data = array (
), int $status = 200): Core\Services\ServiceResult`
- Aciklama: Basarili bir servis sonucu olusturur.
- Donus: self
- Parametreler:
  - `$message` (string): Insan tarafindan okunabilir durum mesaji.
  - `$data` (array): Sonuc verisi.
  - `$status` (int): Onerilen HTTP durum kodu.

### toArray

- Erisim: `public`
- Imza: `toArray(): array`
- Aciklama: Sonucu serilestirilebilir bir diziye cevirir.
- Donus: array

## Core\Session

- Dosya: `core/Session.php`
- Aciklama: Flash mesaj yardimcilariyla PHP oturumlari etrafinda ince bir sarmalayici.
- Metod sayisi: 8

### __construct

- Erisim: `public`
- Imza: `__construct()`
- Aciklama: Gerekli oldugunda PHP oturumunu baslatir.

### flash

- Erisim: `public`
- Imza: `flash(string $key, mixed $value): void`
- Aciklama: Tek seferlik okunmak uzere bir deger saklar.
- Parametreler:
  - `$key` (string): Flash anahtari.
  - `$value` (mixed): Flash degeri.

### forget

- Erisim: `public`
- Imza: `forget(string $key): void`
- Aciklama: Bir oturum anahtarini siler.
- Parametreler:
  - `$key` (string): Session key.

### get

- Erisim: `public`
- Imza: `get(string $key, mixed $default = NULL): mixed`
- Aciklama: Bir oturum degerini okur.
- Donus: mixed
- Parametreler:
  - `$key` (string): Session key.
  - `$default` (mixed): Bulunmazsa kullanilacak varsayilan deger.

### getFlash

- Erisim: `public`
- Imza: `getFlash(string $key, mixed $default = NULL): mixed`
- Aciklama: Flash degerini okur ve siler.
- Donus: mixed
- Parametreler:
  - `$key` (string): Flash anahtari.
  - `$default` (mixed): Bulunmazsa kullanilacak varsayilan deger.

### invalidate

- Erisim: `public`
- Imza: `invalidate(): void`
- Aciklama: Mevcut oturumu ve cerezini tamamen yok eder.

### put

- Erisim: `public`
- Imza: `put(string $key, mixed $value): void`
- Aciklama: Bir oturum degerini saklar.
- Parametreler:
  - `$key` (string): Session key.
  - `$value` (mixed): Saklanacak deger.

### regenerate

- Erisim: `public`
- Imza: `regenerate(): void`
- Aciklama: PHP oturum id'sini yeniden uretir.

## Core\SiteController

- Dosya: `core/SiteController.php`
- Aciklama: Site sayfalari icin anlamsal temel controller.
- Metod sayisi: 0

## Core\Validation\FormRequest

- Dosya: `core/Validation/FormRequest.php`
- Aciklama: Uygulama katmaninda tekrar kullanilabilir form dogrulama nesneleri icin temel sinif.
- Metod sayisi: 3

### __construct

- Erisim: `public`
- Imza: `__construct(Core\Validation\Validator $validator)`
- Parametreler:
  - `$validator` (Validator): Kural tabanli dogrulama servisi.

### rules

- Erisim: `protected`
- Imza: `rules(): array`
- Aciklama: Istege ait dogrulama kurallarini dondurur.
- Donus: array<string, string|array<int, string>>

### validate

- Erisim: `public`
- Imza: `validate(array $data): Core\Validation\ValidationResult`
- Aciklama: Veriyi kurallara gore dogrular.
- Donus: ValidationResult

## Core\Validation\ValidationResult

- Dosya: `core/Validation/ValidationResult.php`
- Aciklama: Bir dogrulama calismasinin sonucunu temsil eder.
- Metod sayisi: 5

### __construct

- Erisim: `public`
- Imza: `__construct(array $errors = array (
))`
- Parametreler:
  - `$errors` (array): Alan adina gore gruplanmis dogrulama hatalari.

### errors

- Erisim: `public`
- Imza: `errors(): array`
- Aciklama: Tum dogrulama hatalarini dondurur.
- Donus: array

### fails

- Erisim: `public`
- Imza: `fails(): bool`
- Aciklama: Dogrulamanin basarisiz olup olmadigini belirtir.
- Donus: bool

### first

- Erisim: `public`
- Imza: `first(string $field): string`
- Aciklama: Verilen alan icin ilk dogrulama hatasini dondurur.
- Donus: string
- Parametreler:
  - `$field` (string): Alan adi.

### firstOf

- Erisim: `public`
- Imza: `firstOf(array $fields): string`
- Aciklama: Verilen alan listesinde bulunan ilk dogrulama hatasini dondurur.
- Donus: string

## Core\Validation\Validator

- Dosya: `core/Validation/Validator.php`
- Aciklama: Form ve istek verileri icin hafif kural tabanli dogrulayici.
- Metod sayisi: 1

### validate

- Erisim: `public`
- Imza: `validate(array $data, array $rules): Core\Validation\ValidationResult`
- Aciklama: Girdi dizisini basit metin kurallariyla dogrular.
- Donus: ValidationResult
- Parametreler:
  - `$data` (array): Girdi verisi.
  - `$rules` (array): Alanlara gore gruplanmis dogrulama kurallari.

## Core\View\RawValue

- Dosya: `core/View/RawValue.php`
- Aciklama: HTML kacislama islemini atlamasi gereken view icerigini isaretler.
- Metod sayisi: 2

### __construct

- Erisim: `public`
- Imza: `__construct(string $value)`
- Parametreler:
  - `$value` (string): Ham HTML metni.

### value

- Erisim: `public`
- Imza: `value(): string`
- Aciklama: Ham HTML degerini dondurur.
- Donus: string

## Core\View\View

- Dosya: `core/View/View.php`
- Aciklama: Placeholder token'lari kacislanmis verilerle degistiren HTML sablon goruntuleyicisi.
- Metod sayisi: 9

### __construct

- Erisim: `public`
- Imza: `__construct(string $basePath, string $defaultLayout, array $sharedData = array (
), Core\Localization\LanguageService $language = NULL)`
- Parametreler:
  - `$basePath` (string): View sablonlarinin tutuldugu temel yol.
  - `$defaultLayout` (string): Varsayilan layout sablon adi.
  - `$sharedData` (array): Her render islemine eklenen ortak degerler.

### hasPermission

- Erisim: `private`
- Imza: `hasPermission(string $required, array $data): bool`
- Aciklama: Mevcut permission listesi icinde verilen yetkinin karsilanip karsilanmadigini kontrol eder.
- Donus: bool
- Parametreler:
  - `$required` (string): Gerekli yetki.

### joinUrl

- Erisim: `private`
- Imza: `joinUrl(string $base, string $path): string`
- Aciklama: Temel URL ve yol parcasi ile tam adres olusturur.
- Donus: string
- Parametreler:
  - `$base` (string): Temel URL.
  - `$path` (string): Yol parcasi.

### parseConditionalBlocks

- Erisim: `private`
- Imza: `parseConditionalBlocks(string $template, array $data): string`
- Aciklama: Kosul bloklarini parse eder.
- Donus: string
- Parametreler:
  - `$template` (string): Sablon icerigi.

### parseTemplate

- Erisim: `private`
- Imza: `parseTemplate(string $template, array $data): string`
- Aciklama: Verilen view verisini kullanarak placeholder token'larini parse eder.
- Donus: string
- Parametreler:
  - `$template` (string): Sablon icerigi.
  - `$data` (array): Sablon verisi.

### render

- Erisim: `public`
- Imza: `render(string $template, array $data = array (
), string $layout = NULL): string`
- Aciklama: Bir view'i layout icinde render eder.
- Donus: string
- Parametreler:
  - `$template` (string): View sablon adi.
  - `$data` (array): Sablon verisi.
  - `$layout` (string|null): Opsiyonel layout ezmesi.

### renderFile

- Erisim: `private`
- Imza: `renderFile(string $template, array $data): string`
- Aciklama: Bir sablon dosyasini yukler ve parse eder.
- Donus: string
- Parametreler:
  - `$template` (string): Sablon adi.
  - `$data` (array): Sablon verisi.

### resolveCondition

- Erisim: `private`
- Imza: `resolveCondition(string $condition, array $data): bool`
- Aciklama: Kosul metnini truthy/permission mantigiyla cozer.
- Donus: bool
- Parametreler:
  - `$condition` (string): Kosul metni.

### resolveHelper

- Erisim: `private`
- Imza: `resolveHelper(string $key, array $data): string`
- Aciklama: Ozel helper placeholder'larini cozer.
- Donus: string|null
- Parametreler:
  - `$key` (string): Placeholder anahtari.


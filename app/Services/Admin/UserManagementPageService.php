<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Requests\Admin\UserStoreRequest;
use App\Requests\Admin\UserUpdateRequest;
use App\Services\AuthService;
use App\Services\UserService;
use Core\Security\Csrf;
use Core\Services\BasePageService;
use Core\Services\ServiceResult;
use Core\Session;

/**
 * Admin kullanici yonetim ekranlarinin servis katmanini yonetir.
 */
final class UserManagementPageService extends BasePageService
{
    /**
     * @param AuthService $auth Admin kimlik dogrulama servisi.
     * @param Session $session Flash veri yonetimi.
     * @param Csrf $csrf Form token servisi.
     * @param UserService $users Kullanici is kurallari servisi.
     * @param UserStoreRequest $storeRequest Yeni kullanici request nesnesi.
     * @param UserUpdateRequest $updateRequest Guncelleme request nesnesi.
     */
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
        private readonly Csrf $csrf,
        private readonly UserService $users,
        private readonly UserStoreRequest $storeRequest,
        private readonly UserUpdateRequest $updateRequest
    ) {
    }

    /**
     * Kullanici liste ekrani verisini hazirlar.
     *
     * @param string $path Mevcut istek yolu.
     * @return ServiceResult
     */
    public function index(string $path, array $filters = []): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $list = $this->users->listUsers(100, $filters);

        return $this->success('Kullanici liste sayfasi hazir.', [
            'path' => $path,
            'csrf_token' => $this->csrf->token(),
            'flash_message' => (string) $this->session->getFlash('users.success', ''),
            'error_message' => (string) $this->session->getFlash('users.error', ''),
            'users' => $list->data()['users'] ?? [],
            'summary' => $list->data()['summary'] ?? [],
            'filters' => $list->data()['filters'] ?? ['q' => '', 'status' => ''],
        ]);
    }

    /**
     * Yeni kullanici formu icin view verisini dondurur.
     *
     * @param string $path Mevcut istek yolu.
     * @return ServiceResult
     */
    public function createForm(string $path): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        return $this->success('Yeni kullanici formu hazir.', $this->buildFormData($path, [
            'form_mode' => 'create',
            'form_action' => '/admin/users/create',
            'form_title' => 'Yeni kullanici',
            'submit_label' => 'Kullanici olustur',
            'password_required' => 'required',
            'password_help' => 'Yeni kullanici olustururken sifre zorunludur.',
        ]));
    }

    /**
     * Var olan kullanici icin duzenleme formu verisini hazirlar.
     *
     * @param string $path Mevcut istek yolu.
     * @param int $id Kullanici id degeri.
     * @return ServiceResult
     */
    public function editForm(string $path, int $id): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $user = $this->users->findUser($id);
        if (! $user->isSuccess()) {
            return $this->redirectError('Kullanici bulunamadi.', '/admin/users', 404);
        }

        $row = $user->data()['user'] ?? [];

        return $this->success('Kullanici duzenleme formu hazir.', $this->buildFormData($path, [
            'form_mode' => 'edit',
            'form_action' => '/admin/users/update/' . $id,
            'form_title' => 'Kullanici duzenle',
            'submit_label' => 'Degisiklikleri kaydet',
            'password_required' => '',
            'password_help' => 'Sifreyi degistirmek istemiyorsaniz bos birakabilirsiniz.',
            'name_value' => (string) ($row['name'] ?? ''),
            'surname_value' => (string) ($row['surname'] ?? ''),
            'email_value' => (string) ($row['email'] ?? ''),
            'phone_value' => (string) ($row['phone'] ?? ''),
            'city_value' => (string) ($row['city'] ?? ''),
            'address_value' => (string) ($row['address'] ?? ''),
            'status_value' => (string) ($row['status'] ?? 'active'),
            'role_value' => (string) ($row['role_id'] ?? '1'),
        ]));
    }

    /**
     * Yeni kullanici olusturma istegini isler.
     *
     * @param array<string, mixed> $payload Form verisi.
     * @return ServiceResult
     */
    public function store(array $payload): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $this->flashInput($this->session, 'users.old', $payload, $this->formFields());
        $validation = $this->storeRequest->validate($payload);

        if ($validation->fails() || ! $this->validateSelectValues($payload)) {
            $this->session->flash('users.error', $this->firstValidationMessage($validation, $payload));

            return $this->redirectError('Form verileri hatali.', '/admin/users/create', 422);
        }

        return $this->handleWriteResult(
            $this->users->createUser($payload),
            '/admin/users/create',
            'Kullanici basariyla olusturuldu.',
            201
        );
    }

    /**
     * Var olan kullaniciyi gunceller.
     *
     * @param int $id Kullanici id degeri.
     * @param array<string, mixed> $payload Form verisi.
     * @return ServiceResult
     */
    public function update(int $id, array $payload): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $this->flashInput($this->session, 'users.old', $payload, $this->formFields());
        $validation = $this->updateRequest->validate($payload);

        if ($validation->fails() || ! $this->validateSelectValues($payload)) {
            $this->session->flash('users.error', $this->firstValidationMessage($validation, $payload));

            return $this->redirectError('Form verileri hatali.', '/admin/users/edit/' . $id, 422);
        }

        return $this->handleWriteResult(
            $this->users->updateUser($id, $payload),
            '/admin/users/edit/' . $id,
            'Kullanici basariyla guncellendi.'
        );
    }

    /**
     * Kullanici kaydini siler.
     *
     * @param int $id Kullanici id degeri.
     * @return ServiceResult
     */
    public function delete(int $id): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $result = $this->users->deleteUser($id);

        if (! $result->isSuccess()) {
            $this->session->flash('users.error', $result->message());

            return $this->redirectError($result->message(), '/admin/users', $result->status());
        }

        $this->session->flash('users.success', 'Kullanici silindi.');

        return $this->redirectSuccess('Kullanici silindi.', '/admin/users');
    }

    /**
     * Admin oturumu zorunlulugunu kontrol eder.
     *
     * @return ServiceResult
     */
    private function guard(): ServiceResult
    {
        if ($this->auth->checkAdmin()) {
            return $this->success('Admin oturumu gecerli.');
        }

        return $this->redirectError('Admin oturumu bulunamadi.', '/admin/login', 401);
    }

    /**
     * Formdan gelen role ve status degerlerini kontrol eder.
     *
     * @param array<string, mixed> $payload Form verisi.
     * @return bool
     */
    private function validateSelectValues(array $payload): bool
    {
        $status = (string) ($payload['status'] ?? '');
        $role = (int) ($payload['role'] ?? 0);
        $statuses = array_column($this->statusOptions(), 'value');
        $roles = array_column($this->roleOptions(), 'id');

        return in_array($status, $statuses, true) && ($roles === [] || in_array($role, array_map('intval', $roles), true));
    }

    /**
     * Form sayfalari icin ortak veri setini olusturur.
     *
     * @param string $path Mevcut istek yolu.
     * @param array<string, mixed> $overrides Sayfaya ozel alanlar.
     * @return array<string, mixed>
     */
    private function buildFormData(string $path, array $overrides = []): array
    {
        return array_merge([
            'path' => $path,
            'csrf_token' => $this->csrf->token(),
            'error_message' => (string) $this->session->getFlash('users.error', ''),
            'name_value' => $this->oldInput($this->session, 'users.old', 'name'),
            'surname_value' => $this->oldInput($this->session, 'users.old', 'surname'),
            'email_value' => $this->oldInput($this->session, 'users.old', 'email'),
            'phone_value' => $this->oldInput($this->session, 'users.old', 'phone'),
            'city_value' => $this->oldInput($this->session, 'users.old', 'city'),
            'address_value' => $this->oldInput($this->session, 'users.old', 'address'),
            'status_value' => $this->oldInput($this->session, 'users.old', 'status', 'active'),
            'role_value' => $this->oldInput($this->session, 'users.old', 'role', '1'),
            'roles' => $this->roleOptions(),
            'statuses' => $this->statusOptions(),
        ], $overrides);
    }

    /**
     * Yazma islemleri sonrasi ortak basari/hata akislarini yonetir.
     *
     * @param ServiceResult $result Is katmani sonucu.
     * @param string $errorRedirect Hata halinde donulecek sayfa.
     * @param string $successMessage Basari flash mesaji.
     * @param int $successStatus Onerilen HTTP durum kodu.
     * @return ServiceResult
     */
    private function handleWriteResult(
        ServiceResult $result,
        string $errorRedirect,
        string $successMessage,
        int $successStatus = 200
    ): ServiceResult {
        if (! $result->isSuccess()) {
            $this->session->flash('users.error', $result->message());

            return $this->redirectError($result->message(), $errorRedirect, $result->status());
        }

        $this->clearInput($this->session, 'users.old', $this->formFields());
        $this->session->flash('users.success', $successMessage);

        return $this->redirectSuccess($successMessage, '/admin/users', $successStatus);
    }

    /**
     * Rol seceneklerini servis katmanindan alir.
     *
     * @return array<int, array<string, mixed>>
     */
    private function roleOptions(): array
    {
        return $this->users->roles()->data()['roles'] ?? [];
    }

    /**
     * Durum seceneklerini servis katmanindan alir.
     *
     * @return array<int, array<string, mixed>>
     */
    private function statusOptions(): array
    {
        return $this->users->statuses()->data()['statuses'] ?? [];
    }

    /**
     * Flash oturumundaki eski form verisini dondurur.
     *
     * @param string $field Form alan adi.
     * @param string $default Varsayilan deger.
     * @return string
     */
    private function formFields(): array
    {
        return ['name', 'surname', 'email', 'phone', 'city', 'address', 'status', 'role'];
    }

    /**
     * Dogrulama sonucu icin ekranda gosterilecek ilk hata mesajini belirler.
     *
     * @param \Core\Validation\ValidationResult $validation Dogrulama sonucu.
     * @param array<string, mixed> $payload Form verisi.
     * @return string
     */
    private function firstValidationMessage(\Core\Validation\ValidationResult $validation, array $payload): string
    {
        $message = $validation->firstOf(['name', 'surname', 'email', 'password']);

        if ($message !== '') {
            return $message;
        }

        if (! $this->validateSelectValues($payload)) {
            return 'Rol veya durum secimi hatali.';
        }

        return 'Form verilerini kontrol edin.';
    }
}

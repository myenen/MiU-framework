<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Requests\Api\AuthLoginRequest;
use App\Services\IdentityService;
use Core\ApiController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;

/**
 * API istemcileri icin giris ve kimlik islemlerini yonetir.
 */
final class Auth extends ApiController
{
    public static function publicActions(): array
    {
        return ['login'];
    }

    /**
     * @param View $view Controller bagimlilik tutarliligi icin eklenen view.
     * @param IdentityService $identity Ortak kimlik servisi.
     */
    public function __construct(
        View $view,
        private readonly IdentityService $identity,
        private readonly AuthLoginRequest $loginRequest
    ) {
        parent::__construct($view);
    }

    /**
     * API kullanicisi icin token tabanli giris yapar.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function login(Request $request): Response
    {
        $validation = $this->validateApiRequest($this->loginRequest, $request->all());

        if ($validation->fails()) {
            return $this->apiValidationError($validation);
        }

        $email = (string) $request->input('email', '');
        $password = (string) $request->input('password', '');
        $deviceName = (string) $request->input('device_name', 'Mobile App');

        return $this->jsonResult(
            $this->identity->loginUserForApi($email, $password, $deviceName),
            201
        );
    }
}

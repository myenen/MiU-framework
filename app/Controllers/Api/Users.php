<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Requests\Api\UserCreateRequest;
use App\Requests\Api\UserUpdateRequest;
use App\Services\UserService;
use Core\ApiController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;


/**
 * Kullanici verilerini API istemcilerine sunar.
 */
final class Users extends ApiController
{
    public static function actionPermissions(): array
    {
        return [
            'index' => 'users.view',
            'show' => 'users.view',
            'create' => 'users.edit',
            'update' => 'users.edit',
            'delete' => 'users.delete',
        ];
    }

    /**
     * @param View $view Controller bagimlilik tutarliligi icin eklenen view.
     * @param UserService $users Kullanici servis katmani.
     */
    public function __construct(
        View $view,
        private readonly UserService $users,
        private readonly UserCreateRequest $createRequest,
        private readonly UserUpdateRequest $updateRequest
    ) {
        parent::__construct($view);
    }

    /**
     * Korumali kullanici listesini dondurur.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->jsonResult($this->users->listUsers(20, [
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
        ]));
    }

    /**
     * Tekil kullanici kaydini dondurur.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param string $id Kullanici id segmenti.
     * @return Response
     */
    public function show(Request $request, string $id = ''): Response
    {
        return $this->jsonResult($this->users->findUser((int) $id));
    }

    /**
     * Yeni kullanici olusturur.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function create(Request $request): Response
    {
        $validation = $this->validateApiRequest($this->createRequest, $request->all());

        if ($validation->fails()) {
            return $this->apiValidationError($validation);
        }

        return $this->jsonResult($this->users->createUser($request->all()), 201);
    }

    /**
     * Kullanici kaydini gunceller.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param string $id Kullanici id segmenti.
     * @return Response
     */
    public function update(Request $request, string $id = ''): Response
    {
        $validation = $this->validateApiRequest($this->updateRequest, $request->all());

        if ($validation->fails()) {
            return $this->apiValidationError($validation);
        }

        return $this->jsonResult($this->users->updateUser((int) $id, $request->all()));
    }

    /**
     * Kullanici kaydini siler.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param string $id Kullanici id segmenti.
     * @return Response
     */
    public function delete(Request $request, string $id = ''): Response
    {
        return $this->jsonResult($this->users->deleteUser((int) $id));
    }
}

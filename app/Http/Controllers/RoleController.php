<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\RoleRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;


class RoleController extends Controller
{
    private $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function get()
    {
        $roles = $this->roleRepository->get();
        if ($roles) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_ALL_ROLE_SUCCESS, $roles);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_ROLE_FOUND);
        }
    }
}

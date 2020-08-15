<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\RoleRepositoryInterface;
use Illuminate\Http\Response as Response;
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
        return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_ALL_ROLE_SUCCESS, $roles);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;

class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function updatePassword(Request $request)
    {
        $id = $request->user()->id;
        $params = $request->all();
        try {
            $this->userRepository->updatePassword($id, $params);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$UPDATED);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }
}

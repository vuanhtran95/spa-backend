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

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            $user = $this->userRepository->create($params);
            if ($user) {
                return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$USER_CREATED, $user);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$USERNAME_EXIST);
        }
    }

    public function getOneById($id)
    {
        $user = $this->userRepository->getOneBy('id', $id);
        if ($user) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_USER_SUCCESS, $user);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_USER_FOUND);
        }
    }

    public function get(Request $request)
    {
        $users = $this->userRepository->get();
        if ($users) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_ALL_USER_SUCCESS, $users);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_USER_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $user = $this->userRepository->update($id, $params);
            if ($user) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$USER_UPDATED, $user);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$USER_UPDATE_FAILURE);
        }
    }

    public function delete($id)
    {
        try {
            $delete = $this->userRepository->delete($id);
            if ($delete) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DELETE_USER_SUCCESS);
            } else {
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$DELETE_USER_FAILURE);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$DELETE_USER_FAILURE);
        }
    }

    public function getUserInfo(Request $request)
    {
        $userId = $request->user()->id;
        try {
            $userInfo = $this->userRepository->getOneBy('id', $userId);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_USER_SUCCESS, $userInfo);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_USER_FOUND);
        }
    }
}

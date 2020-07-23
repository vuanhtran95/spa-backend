<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\ComboRepositoryInterface;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;


class ComboController extends Controller
{
    private $comboRepository;

    public function __construct(ComboRepositoryInterface $comboRepository)
    {
        $this->comboRepository = $comboRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            $combo = $this->comboRepository->create($params);
            if ($combo) {
                return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$COMBO_CREATED, $combo);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$INPUT_ERROR);
        }
    }

    //TODO:
    public function getOneById($id)
    {

    }

    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $combos = $this->comboRepository->get($params);
            if (!empty($combos)) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_ALL_COMBO_SUCCESS, $combos);
            } else {
                return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_COMBO_FOUND);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$SYSTEM_ERROR);
        }

    }

    public function update(Request $request, $id)
    {
        $params = $request->only('is_active');
        try {
            $combo = $this->comboRepository->update($id, $params);
            if ($combo) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$COMBO_UPDATED, $combo);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$COMBO_UPDATE_FAILURE);
        }
    }

    public function delete($id)
    {
        try {
            $delete = $this->customerRepository->delete($id);
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
            $userInfo = $this->customerRepository->getOneBy('id', $userId);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_USER_SUCCESS, $userInfo);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_USER_FOUND);
        }
    }
}

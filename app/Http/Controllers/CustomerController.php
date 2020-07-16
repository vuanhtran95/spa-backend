<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;


class CustomerController extends Controller
{
    private $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            $customer = $this->customerRepository->create($params);
            if ($customer === 1062) {
                return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$PHONE_EXIST);

            } elseif ($customer) {
                return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$CUSTOMER_CREATED, $customer);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$INPUT_ERROR);
        }
    }

    public function getOneById($id)
    {
        $customer = $this->customerRepository->getOneBy('id', $id);
        if ($customer) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_USER_SUCCESS, $customer);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_USER_FOUND);
        }
    }

    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $customers = $this->customerRepository->get($params);
            if (!empty($customers)) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_ALL_CUSTOMER_SUCCESS, $customers);
            } else {
                return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_CUSTOMER_FOUND);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$SYSTEM_ERROR);
        }

    }

    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $user = $this->customerRepository->update($id, $params);
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
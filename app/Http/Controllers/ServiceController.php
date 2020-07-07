<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\ServiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;

class ServiceController extends Controller
{
    private $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            $service = $this->serviceRepository->create($params);
            if ($service) {
                return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$SERVICE_CREATED, $service);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$SERVICE_CREATE_ERROR);
        }
    }

    public function get()
    {
        $services = $this->serviceRepository->get();
        if (!empty($services)) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_ALL_SERVICE_SUCCESS, $services);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_SERVICE_FOUND);
        }
    }

    // TODO:
    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $user = $this->serviceRepository->update($id, $params);
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

    // TODO:
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

}

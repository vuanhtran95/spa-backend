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
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$SERVICE_CREATED, $service);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function get(Request $request)
    {
        $params = $request->all();
        try {
            $services = $this->serviceRepository->get($params);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_LIST_SUCCESS, $services);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $service = $this->serviceRepository->update($id, $params);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$UPDATE_SUCCESS, $service);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $this->serviceRepository->delete($id);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DELETE_SUCCESS);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }
}

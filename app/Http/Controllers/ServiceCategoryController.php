<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\ServiceCategoryRepositoryInterface;
use App\Repositories\ServiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;

class ServiceCategoryController extends Controller
{
    private $serviceCategoryRepository;

    public function __construct(ServiceCategoryRepositoryInterface $serviceCategoryRepository)
    {
        $this->serviceCategoryRepository = $serviceCategoryRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            $service = $this->serviceCategoryRepository->create($params);
            if ($service) {
                return HttpResponse::toJson(
                    true,
                    Response::HTTP_CREATED,
                    Translation::$SERVICE_CATEGORY_CREATED,
                    $service
                );
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$SERVICE_CATEGORY_CREATE_ERROR);
        }
    }

    public function get()
    {
        $service_categories = $this->serviceCategoryRepository->get();
        return HttpResponse::toJson(
            true,
            Response::HTTP_OK,
            Translation::$GET_ALL_SERVICE_CATEGORY_SUCCESS,
            $service_categories
        );
    }

    // TODO:
    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $user = $this->serviceCategoryRepository->update($id, $params);
            if ($user) {
                return HttpResponse::toJson(
                    true,
                    Response::HTTP_OK,
                    Translation::$SERVICE_CATEGORY_UPDATED,
                    $user
                );
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(
                    false,
                    Response::HTTP_BAD_REQUEST,
                    Translation::$SYSTEM_ERROR
                );
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(
                false,
                Response::HTTP_CONFLICT,
                Translation::$SERVICE_CATEGORY_UPDATED_FAILURE
            );
        }
    }

    // TODO:
    public function delete($id)
    {
        try {
            $delete = $this->serviceCategoryRepository->delete($id);
            if ($delete) {
                return HttpResponse::toJson(
                    true,
                    Response::HTTP_OK,
                    Translation::$DELETE_SERVICE_CATEGORY_SUCCESS
                );
            } else {
                return HttpResponse::toJson(
                    false,
                    Response::HTTP_BAD_REQUEST,
                    Translation::$DELETE_SERVICE_CATEGORY_FAILURE
                );
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(
                false,
                Response::HTTP_CONFLICT,
                Translation::$DELETE_SERVICE_CATEGORY_FAILURE
            );
        }
    }
}

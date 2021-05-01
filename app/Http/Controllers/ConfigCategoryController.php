<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\ConfigCategoryRepositoryInterface;
use App\Repositories\ConfigRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;

class ConfigCategoryController extends Controller
{
    private $configCategoryRepository;

    public function __construct(ConfigCategoryRepositoryInterface $configCategoryRepository)
    {
        $this->configCategoryRepository = $configCategoryRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            $config = $this->configCategoryRepository->create($params);
            if ($config) {
                return HttpResponse::toJson(
                    true,
                    Response::HTTP_CREATED,
                    Translation::$SERVICE_CATEGORY_CREATED,
                    $config
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
        $config_categories = $this->configCategoryRepository->get();
        return HttpResponse::toJson(
            true,
            Response::HTTP_OK,
            Translation::$GET_ALL_SERVICE_CATEGORY_SUCCESS,
            $config_categories
        );
    }

    // TODO:
    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $user = $this->configCategoryRepository->update($id, $params);
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
            $delete = $this->configCategoryRepository->delete($id);
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

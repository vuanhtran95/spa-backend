<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\PackageRepositoryInterface;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\EmployeeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;

class PackageController extends Controller
{
    private $packageRepository;

    public function __construct(PackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        $params['user_id'] = $request->user()->id;

        try {
            $package = $this->packageRepository->create($params);
            if ($package) {
                return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$PACKAGE_CREATED, $package);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $packages = $this->packageRepository->get($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$GET_ALL_PACKAGE_SUCCESS,
                $packages['Data'],
                $packages['Pagination']
            );
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $params = $request->only('is_valid');
        try {
            $package = $this->packageRepository->update($id, $params);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$PACKAGE_UPDATED, $package);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $this->packageRepository->delete($id);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DELETE_SUCCESS);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function getOneById($id)
    {
        $order = $this->packageRepository->getOneBy('id', $id);
        if ($order) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_SUCCESS, $order);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NOT_FOUND);
        }
    }
}

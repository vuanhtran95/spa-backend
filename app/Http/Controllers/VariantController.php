<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\VariantRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;

class VariantController extends Controller
{
    private $variantRepository;

    public function __construct(VariantRepositoryInterface $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            $variant = $this->variantRepository->create($params);
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$CREATED, $variant);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function get()
    {
        try {
            $variant = $this->variantRepository->get();
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_LIST_SUCCESS, $variant);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function getOneById($id)
    {
        $variant = $this->variantRepository->getOneBy('id', $id);
        if ($variant) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_ALL_COMBO_SUCCESS, $variant);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_INTAKE_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $variant = $this->variantRepository->update($id, $params);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$UPDATED, $variant);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $this->variantRepository->delete($id);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DELETED);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

}

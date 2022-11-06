<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\ComboRepositoryInterface;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\EmployeeRepositoryInterface;
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
        $params['user_id'] = $request->user()->id;

        try {
            $combo = $this->comboRepository->create($params);
            if ($combo) {
                return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$COMBO_CREATED, $combo);
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
            $combos = $this->comboRepository->get($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$GET_ALL_COMBO_SUCCESS,
                $combos['Data'],
                $combos['Pagination']
            );
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $params = $request->only('is_valid');
        try {
            $combo = $this->comboRepository->update($id, $params);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$COMBO_UPDATED, $combo);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $delete = $this->comboRepository->delete($id);
            if ($delete) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DELETE_SUCCESS);
            } else {
                return HttpResponse::toJson(false, Response::HTTP_OK, Translation::$DELETE_NOTHING);
            }
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function getOneById($id)
    {
        $order = $this->comboRepository->getOneBy('id', $id);
        if ($order) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_SUCCESS, $order);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NOT_FOUND);
        }
    }
}

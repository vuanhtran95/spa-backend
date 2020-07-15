<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\IntakeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;


class IntakeController extends Controller
{
    private $intakeRepository;

    public function __construct(IntakeRepositoryInterface $intakeRepository)
    {
        $this->intakeRepository = $intakeRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            $intake = $this->intakeRepository->create($params);
            if ($intake !== false) {
                return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$INTAKE_CREATED, $intake);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$INPUT_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $intake = $this->intakeRepository->update($id, $params);
            if ($intake) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$INTAKE_UPDATED, $intake);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$INTAKE_UPDATE_FAILURE);
        }
    }

    public function delete($id)
    {

    }

    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $intakes = $this->intakeRepository->get($params);
            if (!empty($intakes)) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_INTAKE_SUCCESS, $intakes);
            } else {
                return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_INTAKE_FOUND);
            }
        } catch (Exception $e) {
            die(var_dump($e->getMessage()));
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$SYSTEM_ERROR);
        }

    }
}

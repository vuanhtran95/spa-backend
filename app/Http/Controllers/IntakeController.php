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
        $params['user_id'] = $request->user()->id;

        try {
            $intake = $this->intakeRepository->create($params);
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$INTAKE_CREATED, $intake);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $intake = $this->intakeRepository->update($id, $params);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$INTAKE_UPDATED, $intake);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        $data = $request->all();
        try {
            $intake = $this->intakeRepository->approve($id, $data);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$INTAKE_UPDATED, $intake);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function intake_pay_up(Request $request, $id)
    {
        $validatedData = $request->validate([
            'payment_method_id' => 'required',
            'reward_points' => 'required|numeric',
        ]);
        try {
            $intake = $this->intakeRepository->intake_pay_up($id, $validatedData);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$INTAKE_UPDATED, $intake);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $this->intakeRepository->delete($id);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DELETE_INTAKE_SUCCESS);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $intakes = $this->intakeRepository->get($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$GET_INTAKE_SUCCESS,
                $intakes['Data'],
                $intakes['Pagination']
            );
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$SYSTEM_ERROR);
        }
    }

    public function getOneById($id)
    {
        $user = $this->intakeRepository->getOneBy('id', $id);
        if ($user) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_INTAKE_SUCCESS, $user);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_INTAKE_FOUND);
        }
    }
}

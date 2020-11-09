<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\EmployeeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;


class EmployeeController extends Controller
{
    private $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            $employee = $this->employeeRepository->create($params);
            if ($employee === 1062) {
                return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$USERNAME_EXIST);
            } else if ($employee) {
                return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$USER_CREATED, $employee);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$INPUT_ERROR);
        }
    }

    public function getOneById(Request $request, $id)
    {
        $params = $request->all();
        $employee = $this->employeeRepository->getOneBy('id', $id, $params);
        if ($employee) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_USER_SUCCESS, $employee);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_USER_FOUND);
        }
    }

    public function get(Request $request)
    {
        $params = $request->all();
        try {
            $employees = $this->employeeRepository->get($params);
            return HttpResponse::toJson(true,
                Response::HTTP_OK,
                Translation::$GET_ALL_USER_SUCCESS,
                $employees['Data'], $employees['Pagination']);
        } catch (\Exception $e) {
            die(var_dump($e->getMessage()));
        }
    }

    public function update(Request $request, $id)
    {
        $params = $request->all();
        try {
            $employees = $this->employeeRepository->update($id, $params);
            if ($employees) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$USER_UPDATED, $employees);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$USER_UPDATE_FAILURE);
        }
    }

    // Do not work now
    public function delete($id)
    {
        try {
            $delete = $this->employeeRepository->delete($id);
            if ($delete) {
                return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DELETE_USER_SUCCESS);
            } else {
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$DELETE_USER_FAILURE);
            }
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$DELETE_USER_FAILURE);
        }
    }

    public function getEmployeeInfo(Request $request)
    {
        // UserId
        $id = $request->user()->id;
        $params = $request->all();
        try {
            $employeeInfo = $this->employeeRepository->getOneBy('user_id', $id, $params);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_USER_SUCCESS, $employeeInfo);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, $e->getMessage());
        }
    }
}

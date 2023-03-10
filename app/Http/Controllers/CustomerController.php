<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\CustomerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;

class CustomerController extends Controller
{
	private $customerRepository;

	public function __construct(CustomerRepositoryInterface $customerRepository)
	{
		$this->customerRepository = $customerRepository;
	}

	public function create(Request $request)
	{
		$params = $request->all();
		try {
			$customer = $this->customerRepository->create($params);
			return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$CUSTOMER_CREATED, $customer);
		} catch (Exception $e) {
			return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
		}
	}

	public function getOneById($id)
	{
		$customer = $this->customerRepository->getOneBy('id', $id);
		if ($customer) {
			return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_USER_SUCCESS, $customer);
		} else {
			return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_USER_FOUND);
		}
	}

	public function get(Request $request)
	{
		$params = $request->all();

		try {
			$customers = $this->customerRepository->get($params);
			return HttpResponse::toJson(
				true,
				Response::HTTP_OK,
				Translation::$GET_ALL_CUSTOMER_SUCCESS,
				$customers['Data'],
				$customers['Pagination']
			);
		} catch (Exception $e) {
			return HttpResponse::toJson(false, $e->getMessage());
		}
	}

	public function getRanks()
	{
		try {
			$ranks = $this->customerRepository->getRanks();
			return HttpResponse::toJson(
				true,
				Response::HTTP_OK,
				Translation::$GET_ALL_CUSTOMER_SUCCESS,
				$ranks['Data'],
				$ranks['Pagination']
			);
		} catch (Exception $e) {
			return HttpResponse::toJson(false, $e->getMessage());
		}
	}

	public function update(Request $request, $id)
	{
		$params = $request->all();
		try {
			$customer = $this->customerRepository->update($id, $params);
			if ($customer === 1062) {
				return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$PHONE_EXIST);
			} elseif ($customer) {
				return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$USER_UPDATED, $customer);
			} else {
				//TODO: Need to improve
				return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
			}
		} catch (Exception $e) {
			return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$USER_UPDATE_FAILURE);
		}
	}

	public function delete($id)
	{
		try {
			$delete = $this->customerRepository->delete($id);
			if ($delete) {
				return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DELETE_USER_SUCCESS);
			} else {
				return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$DELETE_USER_FAILURE);
			}
		} catch (Exception $e) {
			return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$DELETE_USER_FAILURE);
		}
	}

	public function getUserInfo(Request $request)
	{
		$userId = $request->user()->id;
		try {
			$userInfo = $this->customerRepository->getOneBy('id', $userId);
			return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_SINGLE_USER_SUCCESS, $userInfo);
		} catch (Exception $e) {
			return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_USER_FOUND);
		}
	}

	public function cashOut(Request $request, $id)
	{
		$params = $request->all();
		try {
			$customer = $this->customerRepository->cashOut($id, $params);
			if ($customer) {
				return HttpResponse::toJson(true, Response::HTTP_OK, 'CASHOUT_SUCCESS', $customer);
			} else {
				//TODO: Need to improve
				return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
			}
		} catch (Exception $e) {
			return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
		}
	}

	public function checkCashPoint(Request $request, $id)
	{
		try {
			$customer = $this->customerRepository->checkCashPoint($id);
			if ($customer) {
				return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$USER_UPDATED, $customer);
			} else {
				//TODO: Need to improve
				return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
			}
		} catch (Exception $e) {
			return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$USER_UPDATE_FAILURE);
		}
	}
	public function getInProgressIntake($id)
    {
        $intake = $this->customerRepository->getInProgressIntake($id);
        if ($intake) {
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_INTAKE_SUCCESS, $intake);
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$NO_INTAKE_FOUND);
        }
    }
}

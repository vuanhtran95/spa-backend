<?php

namespace App\Repositories;

use App\Discount;
use App\Customer;
use App\Employee;
use App\Helper\Translation;
use App\Order;
use App\Service;
use App\User;
use App\Variant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class DiscountRepository implements DiscountRepositoryInterface
{
	public function create(array $attributes = [])
	{
		DB::beginTransaction();
		try {
			$return = $this->save($attributes, false);
			DB::commit();
			return $return;
		} catch (\Exception $exception) {
			DB::rollBack();
			throw new \Exception($exception->getMessage());
		}
	}

	public function save($discount_data, $is_update = true, $id = null)
	{
		$discount_instance = null;
		if ($is_update) {
			$discount_instance = Discount::find($id);
			if (empty($discount_instance)) 	throw new \Exception('No Discount Found');
		} else {
			$discount_instance = new Discount();
			$discount_instance->is_active = true;
		}
		foreach ($discount_data  as $property => $value) {
			$discount_instance->$property = $value;
		}
		$discount_instance->save();
		return true;
	}

	public function get(array $condition = [])
	{
		$perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
		$page = isset($condition['page']) ? $condition['page'] : 1;

		$query = new Discount();
		$discounts = $query->offset(($page - 1) * $perPage)
			->limit($perPage)
			->orderBy('id', 'desc')
			->get()
			->toArray();
		return [
			"Data" => $discounts,
			"Pagination" => [
				"CurrentPage" => $page,
				"PerPage" => $perPage,
				"TotalItems" => $query->count()
			]
		];
	}

	public function saveById($id, $data)
	{
		$update_discount = Discount::find($id);
		foreach ($data as $property => $value) {
			$update_discount->$property = $value;
		}
		$update_discount->save();
		return $update_discount;
	}

	public function update($id, $data)
	{
		DB::beginTransaction();
		try {
			$return = $this->saveById($id, $data);
			DB::commit();
			return $return;
		} catch (\Exception $exception) {
			DB::rollBack();
		}
	}

	public function delete($id)
	{
		$discount = Discount::find($id);
		if ($discount !== null) {
			if ($discount->is_valid) {
				throw new \Exception('Can not delete valid discount');
			} else {
				DB::beginTransaction();
				try {
					$destroy = Discount::destroy($id);
					DB::commit();
					return ['id' => $id];
				} catch (\Exception $exception) {
					DB::rollBack();
					throw $exception;
				}
			}
		} else {
			throw new \Exception('No Discount Found');
		}
	}
}

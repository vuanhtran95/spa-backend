<?php

namespace App\Repositories;

use App\Order;

class OrderRepository implements OrderRepositoryInterface
{
	public function get(array $condition = [])
	{
		$employeeId = isset($condition['employee_id']) ? $condition['employee_id'] : null;
		$isValid = isset($condition['is_valid']) ? $condition['is_valid'] : null;

		$perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
		$page = isset($condition['page']) ? $condition['page'] : 1;

		$fromDate = isset($condition['from_date']) ? $condition['from_date'] : null;
		$toDate = isset($condition['to_date']) ? $condition['to_date'] : null;

		$query = new Order();

		if ($employeeId) {
			$query = $query->where('employee_id', '=', $employeeId);
		}
		if ($isValid !== null) {
			$query = $query->whereHas('intake', function ($query) use ($isValid) {
				$query->where('is_valid', $isValid);
			});
		}

		if ($fromDate) {
			$query = $query->where('created_at', '>=', $fromDate);
		}

		if ($toDate) {
			$query = $query->where('created_at', '<=', $toDate);
		}

		$orders = $query
			->with(['variant' => function ($vQuery) {
				$vQuery->with(['service' => function ($sQuery) {
					$sQuery->with('serviceCategory');
				}]);
			}, 'intake' => function ($query) {
				$query->with('customer');
			}])
			->orderBy('id', 'desc')
			->paginate($perPage, ['*'], 'page', $page);

		return [
			"Data" => $orders->items(),
			"Pagination" => [
				"CurrentPage" => $page,
				"PerPage" => $perPage,
				"TotalItems" => $orders->total()
			]
		];
	}

	public function getOneBy($by, $value)
	{
		return Order::where($by, '=', $value)->with('employee')->first();
	}

	// Not working now
	public function update($id, array $attributes = [])
	{
		return $this->save($attributes, true, $id);
	}

	public function save($data, $is_update, $id = null)
	{
		if ($is_update) {
			$order = Order::find($id);
			if (isset($data['note'])) {
				$order->note = $data['note'];
			}
			$order->save();

			return $order;
		} else {
			return false;
		}
	}
}

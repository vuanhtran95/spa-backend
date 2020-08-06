<?php

namespace App\Repositories;

use App\Combo;
use App\Intake;
use App\Order;

class OrderRepository implements OrderRepositoryInterface
{
    public function get(array $condition = [])
    {
        $employeeId = isset($condition['employee_id']) ? $condition['employee_id'] : null;
        $isValid = isset($condition['is_valid']) ? $condition['is_valid'] : null;

        $perPage = isset($condition['perPage']) ? $condition['perPage'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Order();

        if ($employeeId) {
            $query = $query->where('employee_id', '=', $employeeId);
        }
        if ($isValid !== null) {
            $query = $query->whereHas('intake', function ($query) use ($isValid) {
                $query->where('is_valid', $isValid);
            });
        }

        $orders = $query->with(['service', 'intake' => function ($query) {
            $query->with('customer');
        }])->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return [
            "Data" => $orders,
            "Pagination" => [
                "CurrentPage" => $page,
                "PerPage" => $perPage,
                "TotalItems" => $query->count()
            ]
        ];
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

<?php

namespace App\Repositories;

use App\Intake;
use App\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IntakeRepository implements IntakeRepositoryInterface
{
    public function create(array $attributes = [])
    {
        return $this->save($attributes, false);
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            $intake = Intake::find($id);
            if (isset($data['is_valid'])) {
                $intake->is_valid = $data['is_valid'];
                return $intake->save() ? $intake : false;
            } else if (isset($data['orders']) && !empty($data['orders'])) {
                foreach ($data['orders'] as $order) {
                    if ($order['id'] !== null) {
                        // Update order
                        $orderId = Order::find($order['id']);
                    } else {
                        // Create new order
                    }
                }
            } else {
                //TODO:
                return false;
            }
        } else {
            $intake = new Intake();
            isset($data['customer_id']) ? $intake->customer_id = $data['customer_id'] : null;
            $intake->user_id = $data['user_id'];

            DB::beginTransaction();

            try {
                if ($intake->save()) {
                    $orders = $data['orders'];
                    foreach ($orders as $key => $order) {
                        $orders[$key]['intake_id'] = $intake->id;
                        $orders[$key]['created_at'] = Carbon::now();
                        $orders[$key]['updated_at'] = Carbon::now();
                    }
                    Order::insert($orders);
                    DB::commit();
                    // Return Intake with order
                    return Intake::with('orders')->find($intake->id);
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }
        }
    }

    public function get(array $condition = [])
    {
        if (empty($condition)) {
            return Intake::all();
        } else {
            $userId = asset($condition['user_id']) ? $condition['user_id'] : null;

            return Intake::where('user_id', '=', $userId)
                ->with('orders')->get()->toArray();
        }
    }

    public function getOneBy($by, $value)
    {
    }

    public function update($id, array $attributes = [])
    {
        return $this->save($attributes, true, $id);
    }

    public function delete($id)
    {
    }
}

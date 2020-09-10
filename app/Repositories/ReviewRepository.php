<?php

namespace App\Repositories;

use App\Combo;
use App\Employee;
use App\Intake;
use App\Order;
use App\Review;
use App\Service;
use Illuminate\Support\Facades\DB;

class ReviewRepository implements ReviewRepositoryInterface
{

    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $service = $this->save($attributes, false);
            DB::commit();
            return $service;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            // TODO: no need to do
        } else {
            // Create
            $data = [
                "orders" => [
                    [
                        "order_id" => 1,
                        "skill" => 5,
                        "attitude" => 5,
                        "facility" => 5
                    ],
                    [
                        "order_id" => 1,
                        "skill" => 5,
                        "attitude" => 5,
                        "facility" => 5
                    ],
                ],
                "intake_id" => 1
            ];

            $intake_id = $data['intake_id'];
            $intake = Intake::find($intake_id);
            die(var_dump($intake));

            $orders = $data['orders'];

            foreach ($data as $review) {

                $order = Order::with('service')->find($review->order_id);
                $employee = Employee::find($order->employee_id);

                if ($order->combo_id) {
                    // Case order use combo

                    $combo = Combo::find($order->combo_id);

                    // Collect commission for employee in combo used case
                    $employee->working_commission =
                        $employee->working_commission + ($order->service->order_commission / 100) * ($combo->total_price / $combo->amount);
                    $employee->save();

                } else {
                    // Case order doesn't use combo
                    // Collect commission for employee in money pay case
                    $employee->working_commission =
                        $employee->working_commission + ($order->service->order_commission / 100) * $order->service->price;
                    $employee->save();
                }
                Review::insert($review);
            }
        }
    }
}

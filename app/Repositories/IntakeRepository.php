<?php

namespace App\Repositories;

use App\Employee;
use App\Intake;
use App\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IntakeRepository implements IntakeRepositoryInterface
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
            return false;
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            // Change Orders
            if (isset($data['orders'])) {
                $allOrdersOfIntake = Order::where('intake_id', '=', $id)->get()->toArray();
                $updateIds = array_values(array_map("\\App\\Helper\\Common::getIds", $data['orders']));
                foreach ($allOrdersOfIntake as $order) {
                    $key = array_search($order['id'], $updateIds);
                    if ($key !== false) {
                        // Need to update
                        $updateData = $data['orders'][$key];
                        $orderData = Order::find($updateData['id']);

                        if (isset($updateData['employee_id'])) $orderData->employee_id = $updateData['employee_id'];
                        if (isset($updateData['amount'])) $orderData->amount = $updateData['amount'];
                        if (isset($updateData['note'])) $orderData->note = $updateData['note'];
                        if (isset($updateData['combo_id'])) $orderData->combo_id = $updateData['combo_id'];
                        if (isset($updateData['service_id'])) $orderData->service_id = $updateData['service_id'];
                        $orderData->save();
                    } else {
                        // Need to delete
                        Order::destroy($order['id']);
                    }
                }

                foreach ($data['orders'] as $order) {
                    if ($order['id'] === null) {
                        // Need to create order
                        $orderData = new Order();
                        $orderData->service_id = $order['service_id'];
                        $orderData->employee_id = $order['employee_id'];
                        $orderData->amount = $order['amount'];
                        $orderData->note = $order['note'];
                        $orderData->intake_id = $id;
                        $orderData->combo_id = isset($order['combo_id']) ? $order['combo_id'] : null;
                        $orderData->save();
                    }
                }
                return Intake::with(['orders' => function ($query) {
                    $query->with('combo');
                }])->find($id);
            } else {
                //TODO:
                return false;
            }
        } else {

            $employeeId = Employee::where('user_id', $data['user_id'])->first()->toArray()['id'];
            unset($data['user_id']);

            $intake = new Intake();
            $intake->customer_id = $data['customer_id'];
            $intake->employee_id = $employeeId;

            if ($intake->save()) {
                $orders = $data['orders'];
                foreach ($orders as $key => $order) {
                    $orders[$key]['intake_id'] = $intake->id;
                    $orders[$key]['created_at'] = Carbon::now();
                    $orders[$key]['updated_at'] = Carbon::now();
                    $orders[$key]['combo_id'] = isset($orders[$key]['combo_id']) ? $orders[$key]['combo_id'] : null;
                    $order[$key]['note'] = isset($orders[$key]['note']) ? $orders[$key]['note'] : null;
                }
                Order::insert($orders);
                // Return Intake with order
                return Intake::with('orders')->find($intake->id);
            } else {
                return false;
            }

        }
    }

    public function get(array $condition = [])
    {
        $perPage = isset($condition['perPage']) ? $condition['perPage'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $employeeId = isset($condition['employee_id']) ? $condition['employee_id'] : null;
        $isValid = isset($condition['is_valid']) ? (int) $condition['is_valid'] : null;

        $query = new Intake();

        if ($employeeId) {
            $query = $query::where('employee_id', $employeeId);
        }

        if ($isValid !== null && ($isValid === 0 || $isValid === 1)) {
            if ($employeeId) {
                $query =$query->where('is_valid', $isValid);
            } else {
                $query = $query::where('is_valid', $isValid);
            }
        }

        $intakes = $query->limit($perPage)
            ->with(['customer'])
            ->offset(($page - 1) * $perPage)
            ->get()
            ->toArray();

        return [
            "Data" => $intakes,
            "Pagination" => [
                "CurrentPage" => $page,
                "PerPage" => $perPage,
                "TotalItems" => $query->count()
            ]
        ];
    }

    public function getOneBy($by, $value)
    {
        return Intake::with(['orders', 'customer'])->where('id', $value)->first();
    }

    public function update($id, array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, true, $id);
            DB::commit();
            return $return;
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    public function delete($id)
    {
    }
}

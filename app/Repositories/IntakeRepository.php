<?php

namespace App\Repositories;

use App\Combo;
use App\Customer;
use App\Employee;
use App\Helper\Translation;
use App\Intake;
use App\Order;
use App\Variant;
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
            throw new \Exception($exception->getMessage());
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
                        if (isset($updateData['variant_id'])) $orderData->variant_id = $updateData['variant_id'];
                        if (isset($updateData['gender'])) $orderData->gender = $updateData['gender'];
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
                        $orderData->variant_id = $order['variant_id'];
                        $orderData->employee_id = $order['employee_id'];
                        $orderData->amount = $order['amount'];
                        $orderData->note = $order['note'];
                        $orderData->gender = $order['gender'];
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
                    $order[$key]['gender'] = isset($orders[$key]['gender']) ? $orders[$key]['gender'] : 'both';
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
        $perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $employeeId = isset($condition['employee_id']) ? $condition['employee_id'] : null;
        $isValid = isset($condition['is_valid']) ? (int)$condition['is_valid'] : null;
        $customerId = isset($condition['customer_id']) ? $condition['customer_id'] : null;

        $fromDate = isset($condition['from_date']) ? $condition['from_date'] : null;
        $toDate = isset($condition['to_date']) ? $condition['to_date'] : null;

        $query = new Intake();

        if ($employeeId) {
            $query = $query::where('employee_id', $employeeId);
        }

        if ($customerId) {
            $query = $query::where('customer_id', $customerId);
        }

        if ($isValid !== null && ($isValid === 0 || $isValid === 1)) {
            $query = $query->where('is_valid', $isValid);
        }

        if ($fromDate) {
            $query = $query->where('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query = $query->where('created_at', '<=', $toDate);
        }

        $intakes = $query->limit($perPage)
            ->with(['customer', 'employee'])
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
        return Intake::with(['orders' => function ($query) {
            $query->with(['employee', 'variant' => function($vQuery) {$vQuery->with(['service' => function($sQuery) {
                $sQuery->with('serviceCategory');
            }]);}, 'combo', 'review']);
        }, 'customer', 'employee', 'reviewForm'])->where('id', $value)->first();
    }

    public function update($id, array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, true, $id);
            DB::commit();
            return $return;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
            DB::rollBack();
        }
    }

    public function approve($id, $data)
    {
        $intake = Intake::with(['orders' => function ($query) {
            $query->with(['variant' => function($subQuery) {$subQuery->with('service');}]);
        }])->find($id);

        if ($intake->is_valid) {
            throw new \Exception("Intake already approved");
        }

        DB::beginTransaction();
        try {

            // 2. Use combo and Calc price
            $totalPrice = 0;
            if (!empty($intake->orders)) {
                foreach ($intake->orders as $order) {

                    if ($order->combo_id) {
                        // Use combo, won't pay money
                        $combo = Combo::find($order->combo_id);
                        // Minus combo
                        $combo->number_used = (int)$combo->number_used + (int)$order->amount;
                        if ($combo->number_used > $combo->amount) {
                            throw new Exception('You have run out of use this combo');
                        }
                        $combo->save();
                    } else {
                        // Pay money
                        $updateOrder = Order::find($order->id);
                        $variant = Variant::find($updateOrder->variant_id);

                        $updateOrder->price = $variant->price;
                        // Store price to order
                        $updateOrder->save();
                        $totalPrice = $totalPrice + $variant->price * $order->amount;
                    }
                }
            }
            // If intake has customer
            if ($intake->customer_id) {
                $customer = Customer::find($intake->customer_id);
                // If has discount
                if ($data['discount_point'] > 0) {

                    if ($customer->points < ($data['discount_point'])) {
                        throw new \Exception(Translation::$CUSTOMER_DO_NOT_HAVE_ENOUGH_POINT);
                    }
//                $intake->discount_price = $data['discount_point'] * env('MONEY_POINT_RATIO');
                    // Currently 50 points = 200k VND
                    $intake->discount_price = $data['discount_point'] * 4;
                    $intake->final_price = $totalPrice - $intake->discount_price;

                    // Minus customer point
                    $customer->points = $customer->points - $data['discount_point'];
                    $customer->save();
                } else {
                    $intake->final_price = $totalPrice;
                }
            } else {
                $intake->final_price = $totalPrice;
            }

            // Check if has additional discount price
            if (isset($data['additional_discount_price']) && $data['additional_discount_price'] > 0) {
                $intake->final_price = $intake->final_price  - $data['additional_discount_price'];
                $intake->additional_discount_price = $data['additional_discount_price'];
                $intake->discount_note = $data['discount_note'];
            }

            // Check price negative
            if ($intake->final_price < 0) {
                $intake->final_price = 0;
            }

            // Collect point for customer
            if ($intake->final_price > 0 && $intake->customer_id !== null) {
                // Plus customer point
//                $customer->points = $customer->points + (int)($totalPrice / env('MONEY_POINT_RATIO'));\
                // Currently 50k VND = 1 point
                $customer->points = $customer->points + (int)($intake->final_price / 50);
                $customer->save();
            }

            // Update Status For Intake
            $intake->is_valid = 1;
            $intake->save();
            DB::commit();
            return Intake::with('orders')->find($id);
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function delete($id)
    {
    }
}

<?php

namespace App\Repositories;

use App\Combo;
use App\Customer;
use App\Repositories\CustomerRepository;
use App\Employee;
use App\Helper\Translation;
use App\Helper\Common;
use App\Constants\PaymentType;
use App\Constants\Invoice as InvoiceConstant;
use App\Intake;
use App\Order;
use App\Variant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Repositories\InvoiceRepository;

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
            $intake = Intake::with(
                ['orders' => function ($query) {
                    $query->with('combo');
                }, 'invoice']
            )->find($id);
            if ($intake->is_valid) {
                throw new \Exception("Intake already approved");
            }
            if (isset($data['orders'])) {
                $allOrdersOfIntake = Order::where('intake_id', '=', $id)->get()->toArray();
                $updateIds = array_values(array_map("\\App\\Helper\\Common::getIds", $data['orders']));

                foreach ($allOrdersOfIntake as $order) {
                    $key = array_search($order['id'], $updateIds);
                    if ($key !== false) {
                        // Need to update
                        $updateData = $data['orders'][$key];
                        $orderData = Order::find($updateData['id']);

                        if (isset($updateData['employee_id'])) {
                            $orderData->employee_id = $updateData['employee_id'];
                        }
                        if (isset($updateData['amount'])) {
                            $orderData->amount = $updateData['amount'];
                        }
                        if (isset($updateData['note'])) {
                            $orderData->note = $updateData['note'];
                        }
                        if (isset($updateData['combo_id'])) {
                            $orderData->combo_id = $updateData['combo_id'];
                        }
                        if (isset($updateData['variant_id'])) {
                            $orderData->variant_id = $updateData['variant_id'];
                        }
                        //                        if (isset($updateData['gender'])) $orderData->gender = $updateData['gender'];
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
                        //                        $orderData->gender = $order['gender'];
                        $orderData->intake_id = $id;
                        $orderData->combo_id = isset($order['combo_id']) ? $order['combo_id'] : null;
                        $orderData->promotion_hash = $order['promotion_hash'];
                        $orderData->save();
                    }
                }
            }
            // Update payment_type
            if (!empty($data['payment_type'])) {
                $intake->payment_type = $data['payment_type'];
                if ($intake->payment_type === 'credit' & empty($intake->invoice)) {
                    $invoiceRepository = app(InvoiceRepository::class);
                    $params = [
                        'customer_id' => $intake->customer_id,
                        'employee_id' => $intake->employee_id,
                        'intake_id' => $intake->id,
                        'amount' => 0,
                        'type' => 'deduction',
                    ];
                    $invoice = $invoiceRepository->create($params);
                }
                $intake->save();
            }
            return $intake;
        } else {
            $employeeId = Employee::where('user_id', $data['user_id'])->first()->toArray()['id'];
            unset($data['user_id']);

            $intake = new Intake();
            $intake->customer_id = $data['customer_id'];
            $intake->employee_id = $employeeId;
            $intake->payment_type = $data['payment_type'];

            if ($intake->save()) {
                $invoiceRepository = app(InvoiceRepository::class);

                // Create invoice
                if (!empty($data['payment_type']) && PaymentType::CREDIT === $data['payment_type']) {
                    // if (empty($data['signature'])) {
                    //     throw new \Exception('Signature cannot be empty.');
                    // }

                    $params = [
                        'customer_id' => $intake->customer_id,
                        'employee_id' => $intake->employee_id,
                        'intake_id' => $intake->id,
                        'amount' => 0,
                        'type' => 'deduction',
                        // 'signature' => $data['signature']
                    ];

                    $invoice = $invoiceRepository->create($params);
                }

                $orders = $data['orders'];
                foreach ($orders as $key => $order) {
                    $orders[$key]['intake_id'] = $intake->id;
                    $orders[$key]['created_at'] = Carbon::now();
                    $orders[$key]['updated_at'] = Carbon::now();
                    $orders[$key]['combo_id'] = isset($orders[$key]['combo_id']) ? $orders[$key]['combo_id'] : null;
                    $order[$key]['note'] = isset($orders[$key]['note']) ? $orders[$key]['note'] : null;
                    //                    $order[$key]['gender'] = isset($orders[$key]['gender']) ? $orders[$key]['gender'] : 'both';
                }
                Order::insert($orders);
                // Return Intake with order
                return Intake::with(['orders', 'invoice'])->find($intake->id);
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
        return Intake::with(
            ['orders' => function ($query) {
                $query->with(
                    ['employee', 'variant' => function ($vQuery) {
                        $vQuery->with(
                            ['service' => function ($sQuery) {
                                $sQuery->with('serviceCategory');
                            }]
                        );
                    }, 'combo', 'review']
                );
            }, 'customer', 'employee', 'reviewForm', 'invoice']
        )->where('id', $value)->first();
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
        $intake = Intake::with(
            ['orders' => function ($query) {
                $query->with(
                    ['variant' => function ($subQuery) {
                        $subQuery->with('service');
                    }]
                );
            }]
        )->find($id);

        if ($intake->is_valid) {
            throw new \Exception("Intake already approved");
        }

        if ($intake->payment_type === PaymentType::CREDIT && empty($intake->customer_id)) {
            throw new \Exception("Payment method is not allowed");
        }

        DB::beginTransaction();
        try {
            /* 0. Get customer */
            $customer = null;
            if ($intake->customer_id) {
                $customer = Customer::find($intake->customer_id);
            }

            /* 1. Calculate Total Price And Update Combo Amount */
            $totalPrice = 0;
            $payment_method = $intake->payment_type;

            if (!empty($intake->orders)) {
                /* 1.1 calculate total price */
                $paid_orders = $intake->orders->filter(
                    function ($order) {
                        return empty($order->combo_id);
                    }
                )->values();
               
                foreach ($paid_orders as $order) {

                        // Pay money
                    $updateOrder = Order::find($order->id);
                    $variant = Variant::find($updateOrder->variant_id);
                    $is_free_variant = $variant->is_free;
                    // Not Calculate the free variant
                    if (!$is_free_variant) {
                        // Store price to order
                        if ($payment_method ===  PaymentType::CREDIT) {
                            $totalPrice = $totalPrice + $variant->credit_price * $order->amount;
                        } else {
                            $totalPrice = $totalPrice + $variant->price * $order->amount;
                        }
                        $updateOrder->price = $variant->price;
                        $updateOrder->credit_price = $variant->credit_price;
                        $updateOrder->save();
                    }
                }

                /* 1.2 Check user Balance if using credit */
                if ($payment_method ===  PaymentType::CREDIT && $customer->balance <  $totalPrice) {
                    throw new \Exception("Not enough credit");
                }

                /* 1.3 Calculate combo amount */
                $combo_orders = $intake->orders->filter(
                    function ($order) {
                        return !empty($order->combo_id);
                    }
                )->values();
    
                foreach ($combo_orders as $order) {
    
                    // Use combo, won't pay money
                    $combo = Combo::find($order->combo_id);
                    // Minus combo
                    $combo->number_used = (int)$combo->number_used + (int)$order->amount;
                    if ($combo->number_used > $combo->amount) {
                        throw new Exception('You have run out of use this combo');
                    }
                    $combo->save();
                }
            }

            /* 2. Check for discount */
            if (isset($data['additional_discount_price'])) {
                $totalPrice = $totalPrice - $data['additional_discount_price'];
                $intake->additional_discount_price = $data['additional_discount_price'];
                $intake->discount_note = $data['discount_note'];
            }

            /* 3. Check negative price and set Intake Price */
            $intake->final_price = $totalPrice;
            if ($intake->final_price < 0) {
                $intake->final_price = 0;
            }
            

            /* 4. Collect point for customer */
            if ($intake->final_price > 0 && !empty($customer) && $payment_method !==  PaymentType::CREDIT) {
                $customer->points = $customer->points + (int)($intake->final_price / 50);
                $customer->save();
            }

            /* 5. Process credit payment */
            $invoice = $intake->invoice;
            if ($payment_method ===  PaymentType::CASH && !empty($invoice)) {
                $invoice->delete();
            }

            if ($payment_method ===  PaymentType::CREDIT) {
                if (empty($invoice)) {
                    throw new Exception('Missing invoice');
                }
                if ($invoice->status === InvoiceConstant::PAID_STATUS) {
                    throw new Exception('Payment Failed! Invoice has been proceeded');
                }
                $invoice->amount = $intake->final_price;
                $invoice->status =InvoiceConstant::PAID_STATUS;
                $customer->balance = $customer->balance - $invoice->amount;
                if ($invoice->save()) {
                    $customer->save();
                };
            }

            // Update Status For Intake
            $intake->is_valid = 1;
            $intake->save();
            //TODO: UP RANK
            $up_rank = false;
            if (!empty($customer) || $payment_method ===  PaymentType::CASH) {
                $up_rank = Common::upRank($customer);
            }
            $result = Intake::with(['orders', 'invoice'])->find($id);
            $result['up_rank_result'] = $up_rank;
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function delete($id)
    {
        $intake = Intake::where('is_valid', false)->find($id);
        if ($intake) {
            $intake->delete();
        } else {
            throw new \Exception(Translation::$NO_INTAKE_FOUND);
        }
    }
}
// TODO: DISCOUNT POINT
            // if ($intake->customer_id) {
            //     $customer = Customer::find($intake->customer_id);
            //     // If has discount
            //     if ($data['discount_point'] > 0) {

            //         if ($customer->points < ($data['discount_point'])) {
            //             throw new \Exception(Translation::$CUSTOMER_DO_NOT_HAVE_ENOUGH_POINT);
            //         }
            //         //                $intake->discount_price = $data['discount_point'] * env('MONEY_POINT_RATIO');
            //         // Currently 50 points = 200k VND
            //         $intake->discount_price = $data['discount_point'] * 4;
            //         $intake->final_price = $totalPrice - $intake->discount_price;

            //         // Minus customer point
            //         $customer->points = $customer->points - $data['discount_point'];
            //         $customer->save();
            //     } else {
            //         $intake->final_price = $totalPrice;
            //     }
            // } else {
            //     $intake->final_price = $totalPrice;
            // }

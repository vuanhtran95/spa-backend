<?php

namespace App\Repositories;

use App\Combo;
use App\Customer;
use App\Employee;
use App\Helper\CustomerHelper;
use App\Helper\Translation;
use App\Helper\Common;
use App\Helper\IntakeHelper;
use App\Constants\PaymentType;
use App\Constants\Invoice as InvoiceConstant;
use App\Intake;
use App\Order;
use App\Variant;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\Carbon;

class IntakeRepository implements IntakeRepositoryInterface
{
    public function __construct(CustomerHelper $rewardRuleHelper)
    {
    }

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
		DB::beginTransaction();
		try {
			if ($is_update) {
				// Change Orders
				$intake = Intake::with(
					['orders' => function ($query) {
						$query->with('combo');
					}, 'invoice']
				)->find($id);
				// Todo: comment for now
				// if ($intake->is_valid) {
				// 	throw new \Exception("Intake already approved");
				// }
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
							if (isset($updateData['is_owner'])) {
								$orderData->is_owner = $updateData['is_owner'];
							}
							if (isset($updateData['owner_name'])) {
								$orderData->owner_name = $updateData['owner_name'];
							}
							if (isset($updateData['variant_id'])) {
								$variant = Variant::where('id', '=', $updateData['variant_id'])->first();

								if (empty($variant)) throw new \Exception("Service not found");

								$orderData->variant_id = $variant->id;
								$orderData->name = $variant->name;
								$orderData->unit_price = isset($updateData['combo_id']) ? 0 :  $variant->price;
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
							$orderData->intake_id = $id;

							$variant = Variant::where('id', '=', $order['variant_id'])->first();
							$orderData->variant_id = $variant->id;
							$orderData->unit_price = isset($order['combo_id']) ? 0 : $variant->price;
							$orderData->name = isset($order['name']) ? $order['name'] : $variant->name;

							$orderData->employee_id = $order['employee_id'];
							$orderData->amount = $order['amount'];
							$orderData->note = $order['note'];
							$orderData->combo_id = isset($order['combo_id']) ? $order['combo_id'] : null;
							$orderData->promotion_hash = isset($order['promotion_hash']) ? $order['promotion_hash'] : null;
							$orderData->is_owner = isset($order['is_owner']) ? $order['is_owner'] : true;
							$orderData->owner_name = isset($order['owner_name']) ? $order['owner_name'] : null;
							$orderData->customer_id = $intake->customer_id;
							$orderData->save();
						}
					}
				}
				if (isset($data['payment_received_amount'])) {
					$intake->payment_received_amount = $data['payment_received_amount'];
				}
				if (isset($data['payment_method_id'])) {
					$intake->payment_method_id = $data['payment_method_id'];
				}
				$intake->save();
				DB::commit();
				return $intake;
			} else {
				$employeeId = Employee::where('user_id', $data['user_id'])->first()->toArray()['id'];
				unset($data['user_id']);

				$intake = new Intake();
				$intake->customer_id = $data['customer_id'];
				$intake->employee_id = $employeeId;
				if (isset($data['payment_method_id'])) {
					$intake->payment_method_id = $data['payment_method_id'];
				}

				if ($intake->save()) {

					$orders = $data['orders'];
					foreach ($orders as $order) {
						$orderData = new Order();
						$orderData->intake_id = $intake->id;


						$variant = Variant::where('id', '=', $order['variant_id'])->first();
						$orderData->variant_id = $variant->id;
						$orderData->unit_price = isset($order['combo_id']) ? 0 : $variant->price;
						$orderData->name = isset($order['name']) ? $order['name'] : $variant->name;

						$orderData->employee_id = $order['employee_id'];
						$orderData->amount = $order['amount'];
						$orderData->note = $order['note'];
						$orderData->combo_id = isset($order['combo_id']) ? $order['combo_id'] : null;
						$orderData->promotion_hash = isset($order['promotion_hash']) ? $order['promotion_hash'] : null;
						$orderData->is_owner = isset($order['is_owner']) ? $order['is_owner'] : true;
						$orderData->owner_name = isset($order['owner_name']) ? $order['owner_name'] : null;
						$orderData->customer_id = $data['customer_id'];
						$orderData->save();
					}
					// Return Intake with order
					DB::commit();
					return Intake::with(['orders', 'invoice'])->find($intake->id);
				} else {
					DB::commit();
					return false;
				}
			}
		} catch (\Exception $exception) {
			DB::rollBack();
			throw new \Exception($exception->getMessage());
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

		$hasReviewForm = isset($condition['has_review']) ? $condition['has_review'] : null;
		
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
		if ($hasReviewForm !== null) {
			if ($hasReviewForm) {
				$query = $query->has('reviewForm');
			} else {
				$query = $query->doesnthave('reviewForm');
			}
		}
		$intakes = $query->limit($perPage)
			->with(['customer', 'employee', 'reviewForm', 'orders' => function ($o) {
				$o->with(['variant', 'employee']);
			}])
			->paginate($perPage, ['*'], 'page', $page);

		return [
			"Data" => $intakes->items(),
			"Pagination" => [
				"CurrentPage" => $page,
				"PerPage" => $perPage,
				"TotalItems" => $intakes->total()
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
		/* 0. Get Intake detail by ID */
		$intake = Intake::with(
			['orders' => function ($query) {
				$query->with(
					['variant' => function ($subQuery) {
						$subQuery->with(['service' => function ($eQuery) {
							$eQuery->with('serviceCategory');
						}]);
					}]
				);
			}]
		)->find($id);

		if ($intake->is_valid) {
			throw new \Exception("Intake already approved");
		}

		if ($intake->payment_method_id === PaymentType::CREDIT && empty($intake->customer_id)) {
			throw new \Exception("Payment method is not allowed");
		}

		DB::beginTransaction();
		try {
			/* 0. Get Customer */
			$customer = NULL; // Guest
			if ($intake->customer_id) {
				$customer = Customer::find($intake->customer_id); // Customer
			}

			/* 1. Create Intake Helper */
			$helper = new IntakeHelper($customer, $intake->created_at);

			/* 2. Calculate Total Price And Update Combo Amount */
			$totalPrice = 0;

			if (!empty($intake->orders)) {
				$intake->orders->each(
					function ($order) use ($helper, &$totalPrice) {
						/* 2.1 Process combo order */
						if ($order->combo_id) {
							$combo = Combo::find($order->combo_id);
							if ($combo->number_used >= $combo->amount) {
								throw new Exception('You have run out of use this combo');
							}
						}
						/* 2.2 Process normal order */ else {
							$helper->process_order($order);
							$totalPrice = $totalPrice  + $order->price * $order->amount;
						}
						$order->save();
					}
				);
			}

			/* 3. Apply whole bill discount */
			// TODO: To be discussed
			// $whole_bill_discount = $helper->calculate_whole_bill_discount($totalPrice);
			// $totalPrice = $totalPrice - $whole_bill_discount['amount'];
			// $intake->discount_note = $whole_bill_discount['description'];

			/* 4. Apply additional discount */
			if ($data['additional_discount_price'] <= $totalPrice) {
				$totalPrice = $totalPrice - $data['additional_discount_price'];
				$intake->additional_discount_price = $data['additional_discount_price'];
				$intake->discount_note ?  $intake->discount_note .= ", {$data['discount_note']}" :  $intake->discount_note = $data['discount_note'];
			}

			/* 5. Check negative price and set Intake Final Price */
			$intake->final_price = $totalPrice;
			if ($intake->final_price < 0) {
				$intake->final_price = 0;
			}

			/* 7. Collect point for customer */
			if ($intake->final_price > 0 && !empty($customer)) {
				$intake->customer_earned_points =  $helper->get_points();
			}

			/* 10. Update intake Status and save to DB */
			$intake->save();
			DB::commit();
			$result = $this->getOneBy('id', $id);
			return $result;
		} catch (\Exception $exception) {
			DB::rollBack();
			throw new \Exception($exception->getMessage());
		}
	}

	public function intake_pay_up($id, array $data = [])
	{
		/* 0. Get Intake detail by ID */
		$intake = Intake::with(
			['orders' => function ($query) {
				$query->with(
					['variant' => function ($subQuery) {
						$subQuery->with(['service' => function ($eQuery) {
							$eQuery->with('serviceCategory');
						}]);
					}]
				);
			}]
		)->find($id);

		if ($intake->is_valid) {
			throw new \Exception("Intake Paid");
		}

		if (empty($data['payment_method_id'])) {
			throw new \Exception("Missing payment method");
		}

		$payment_method_id = $data['payment_method_id'];

		if ($payment_method_id === PaymentType::CREDIT && empty($intake->customer_id)) {
			throw new \Exception("Payment method is not allowed");
		}

		DB::beginTransaction();
		try {
			/* 0. Get Customer */
			$customer = NULL; // Guest
			if ($intake->customer_id) {
				$customer = Customer::find($intake->customer_id); // Customer
			}
			/* 1. Create Intake Helper */
			$helper = new IntakeHelper($customer, $intake->created_at);

			if (!empty($intake->orders)) {
				$intake->orders->each(
					function ($order) use ($helper) {
						/* 2.1 Process combo order */
						if ($order->combo_id) {
							$combo = Combo::find($order->combo_id);
							$combo->number_used = (int)$combo->number_used + (int)$order->amount;
							if ($combo->number_used > $combo->amount) {
								throw new Exception('You have run out of use this combo');
							}
							$combo->save();
						}
						/* 2.3 Service Reminder */
						$helper->add_service_reminder($order);
						$order->save();
					}
				);
			}

			if ($payment_method_id ===  PaymentType::CREDIT && $customer->balance <  $intake->final_price) {
				throw new \Exception("Not enough credit");
			}

			/* 7. Collect point for customer */
			if ($intake->final_price > 0 && !empty($customer)) {
				$customer->cash_point = $customer->cash_point + $intake->customer_earned_points;
				$customer->save();
			}

			/* 9. Create Credit Invoice */
			if ($payment_method_id ===  PaymentType::CREDIT) {
				$invoiceRepository = app(InvoiceRepository::class);
				$params = [
					'customer_id' => $intake->customer_id,
					'employee_id' => $intake->employee_id,
					'intake_id' => $intake->id,
					'amount' => $intake->final_price,
					'type' => 'withdraw',
					'status' => InvoiceConstant::PAID_STATUS
					// 'signature' => $data['signature']
				];

				$invoice = $invoiceRepository->create($params);
				$customer->balance = $customer->balance - $invoice->amount;
				$customer->save();
			}
			/* 10. Update intake Status and save to DB */
			$intake->payment_method_id = $payment_method_id;
			$intake->is_valid = 1;
			$intake->approved_date = Carbon::now();
			$intake->save();

			/* 11. Upgrade rank for user */
			$up_rank = false;
			if (!empty($customer) && $payment_method_id ===  PaymentType::CASH) {
				$up_rank = Common::upRank($customer);
				if (!empty($up_rank)) {
					$intake->special_note = $up_rank;
					$intake->save();
				}
			}
			DB::commit();

			$result = $this->getOneBy('id', $id);

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

<?php

namespace App\Repositories;

use App\Combo;
use App\Customer;
use App\Repositories\CustomerRepository;
use App\Employee;
use App\Helper\Translation;
use App\Helper\Common;
use App\Helper\IntakeHelper;
use App\Constants\PaymentType;
use App\Constants\Invoice as InvoiceConstant;
use App\Intake;
use App\Order;
use App\Variant;
use App\Variable;
use Exception;
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
		DB::beginTransaction();
		try {
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
								$variant = Variant::where('id', '=', $updateData['variant_id'])->first();

								if (empty($variant)) throw new \Exception("Service not found");

								$orderData->variant_id = $variant->id;
								$orderData->name = $variant->name;
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
							$orderData->name = isset($order['name']) ? $order['name'] : $variant->name;

							$orderData->employee_id = $order['employee_id'];
							$orderData->amount = $order['amount'];
							$orderData->note = $order['note'];
							$orderData->combo_id = isset($order['combo_id']) ? $order['combo_id'] : null;
							$orderData->promotion_hash = isset($order['promotion_hash']) ? $order['promotion_hash'] : null;
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
						$invoiceRepository->create($params);
					}
					$intake->save();
				}
				DB::commit();
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
					foreach ($orders as $order) {
						$orderData = new Order();
						$orderData->intake_id = $intake->id;


						$variant = Variant::where('id', '=', $order['variant_id'])->first();
						$orderData->variant_id = $variant->id;
						$orderData->name = isset($order['name']) ? $order['name'] : $variant->name;

						$orderData->employee_id = $order['employee_id'];
						$orderData->amount = $order['amount'];
						$orderData->note = $order['note'];
						$orderData->combo_id = isset($order['combo_id']) ? $order['combo_id'] : null;
						$orderData->promotion_hash = isset($order['promotion_hash']) ? $order['promotion_hash'] : null;
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
			->with(['customer', 'employee', 'orders' => function ($o) {
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
		// Get Intake and orders within variants and service
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
			$customer = NULL;
			// Init customer rank
			$customer_rank = NULL;

			if ($intake->customer_id) {
				$customer = Customer::find($intake->customer_id);
				/* 0.5 Get System Discount List From Config */
				if (!empty($customer) && !empty($customer->rank)) {
					$customer_rank = $customer->rank;
				}
			}

			// Create Intake Helper
			$helper = new IntakeHelper($customer_rank);


			/* 1. Calculate Total Price And Update Combo Amount */
			$totalPrice = 0;
			$payment_method = $intake->payment_type;

			if (!empty($intake->orders)) {
				/* 1 calculate total price */
				// TODO: Calculate discount for Promotion Packs ()
				$intake->orders->each(
					function ($order) use ($helper, &$totalPrice, $customer) {
						// Get order id information
						$updateOrder = Order::find($order->id);
						//Get Variant information
						$variant = Variant::where('id', '=', $updateOrder->variant_id)->with(['service' => function ($query) {
							$query->with('serviceCategory');
						}])->first();
						$updateOrder->name = $variant->name;
						// Pre Process Order Additional Logic
						$helper->order_pre_process($updateOrder,  $variant, $customer);
						// Handle paid order
						if (empty($order->combo_id)) {
							$price = $helper->processOrderPrice($updateOrder,  $variant);
							$totalPrice = $totalPrice  + $price * $order->amount;
						}
						// Handle Combo order
						else {
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
				);
			}

			/* 1.2 Check user Balance if using credit */
			if ($payment_method ===  PaymentType::CREDIT && $customer->balance <  $totalPrice) {
				throw new \Exception("Not enough credit");
			}

			/* 2. Check for discount */
			if (
				!empty($data['additional_discount_price'])
				&& $data['additional_discount_price'] <= $totalPrice
			) {
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
			//
			if ($intake->final_price > 0 && !empty($customer)) {
				// $customer->points = $customer->points + (int)($intake->final_price / 50);
				// $customer->save();
				$point_rate_id = 'POINT_RATE';
				if ($customer_rank) $point_rate_id .= '_' . strtoupper($customer_rank);
				$rate = Variable::find($point_rate_id);
				if (!empty($rate)) {
					$customer->cash_point = $customer->cash_point + $intake->final_price * (floatval($rate->value) / 100);
					$customer->save();
				}
			}

			/* 5. Process credit payment */
			$invoice = $intake->invoice;
			if ($payment_method ===  PaymentType::CASH && !empty($invoice)) {
				$invoice->delete();
			}

			if ($payment_method ===  PaymentType::CREDIT) {
				if (empty($invoice)) {
					throw new \Exception('Missing invoice');
				}
				if ($invoice->status === InvoiceConstant::PAID_STATUS) {
					throw new \Exception('Payment Failed! Invoice has been proceeded');
				}
				$invoice->amount = $intake->final_price;
				$invoice->status = InvoiceConstant::PAID_STATUS;
				$customer->balance = $customer->balance - $invoice->amount;
				if ($invoice->save()) {
					$customer->save();
				};
			}
			// $intake->is_valid = 1;
			$intake->save();
			DB::commit();
			//TODO: UP RANK
			$up_rank = false;
			if (!empty($customer) || $payment_method ===  PaymentType::CASH) {
				$up_rank = Common::upRank($customer);
				if (!empty($up_rank)) {
					DB::beginTransaction();
					$intake->special_note = $up_rank;
					$intake->save();
					DB::commit();
				}
			}
			// Update Status For Intake
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

<?php

namespace App\Helper;

use App\Order;
use App\Variant;
use App\Discount;
use App\Variable;
use App\Repositories\TaskAssignmentRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helper\Common;

class IntakeHelper
{
	public $variables = [
		'rank_discount_active' => 'RANK_EXTRA_DISCOUNT_ACTIVE',
		'diamond' => 'DIAMOND_EXTRA_DISCOUNT',
		'gold' => 'GOLD_EXTRA_DISCOUNT',
		'silver' => 'SILVER_EXTRA_DISCOUNT',
	];
	public $RANK_EXTRA_DISCOUNT_ACTIVE = 0;
	public $RANK_EXTRA_DISCOUNT = 0;
	public $rank = null;
	public $customer = true;
	public $discounts =  ['whole_bill' => [], 'individual' => []];
	public $service_reminders = [];

	public function __construct($customer, $created_at)
	{
		if (!empty($customer)) {
			$this->customer = $customer;
			$this->rank = $customer->rank;
			$this->set_rank_discount_active();
			if (!empty($this->RANK_EXTRA_DISCOUNT_ACTIVE)) {
				$this->set_rank_extra_discount();
			}
		}
		$this->get_discounts($created_at);
	}

	public function set_rank_discount_active()
	{
		$id = $this->extra_rank_discount_variables['rank_discount_active'];
		$found = Variable::find($id);
		if (!empty($found)) {
			$this->RANK_EXTRA_DISCOUNT_ACTIVE = floatval($found->value);
		}
	}

	public function set_rank_extra_discount()
	{
		if (!empty($this->rank)) {
			$id = $this->variables[$this->rank];
			$found = Variable::find($id);
			if (!empty($found)) {
				$this->RANK_EXTRA_DISCOUNT = floatval($found->value);
			}
		}
	}

	public function get_discounts($created_at)
	{
		$date = Carbon::parse($created_at)->setTimezone('Asia/Ho_Chi_Minh');
		$day = strtolower($date->shortEnglishDayOfWeek);
		$query = new Discount();
		$discount_array = $query->where('is_active', '=', 1)
			->where('fromDate', '<=', $date->format('Y-m-d'))
			->where('toDate', '>=', $date->format('Y-m-d'))
			->where('fromTime', '<=', $date->format('H:i:s'))
			->where('toTime', '>=', $date->format('H:i:s'))
			->where($day, '=', 1)
			->get()->toArray();
		$this->discounts = array_reduce($discount_array, function ($accumulator, $discount) {
			if (!$discount['whole_bill']) {
				$accumulator['individual'][] = $discount;
			} else {
				$accumulator['whole_bill'][] = $discount;
			}
			return $accumulator;
		}, ['whole_bill' => [], 'individual' => []]);
	}

	public function process_orders()
	{
		$orders = $this->intake->orders;
	}

	public function calculateNormalOrderPrice($updateOrder, $variant)
	{
		$price = $variant->price;
		$updateOrder->unit_price = $variant->price;
		$amount = 0;
		$percentage = 0;
		$discount_notes = array();
		if (!empty($this->discounts)) {
			foreach ($this->discounts as $discount) {
				if (
					$discount['variant_id'] === null
					&& $discount['service_id'] === null
					&& $discount['service_category_id'] === null
				) {
					$this->apply_discount($discount, $amount, $percentage, $discount_notes, $price);
					break;
				}

				if ($discount['variant_id'] !== null) {
					if ($discount['variant_id'] === $variant->id) {
						$this->apply_discount($discount, $amount, $percentage, $discount_notes, $price);
					}
					break;
				}

				if ($discount['service_id'] !== null) {
					if ($discount['service_id'] === $variant->service_id) {
						$this->apply_discount($discount, $amount, $percentage, $discount_notes, $price);
					}
					break;
				}

				if ($discount['service_category_id'] !== null) {
					if ($discount['service_category_id'] === $variant->service->service_category_id) {
						$this->apply_discount($discount, $amount, $percentage, $discount_notes, $price);
					}
					break;
				}
			}
		}
		if ($amount) {
			$price -= $amount;
		}
		if ($percentage) {
			$price = $price * ((100 - $percentage) / 100);
		}
		$updateOrder->price = $price;
		$updateOrder->discount_amount = $amount;
		// $updateOrder->discount_percentage = $percentage;
		// $updateOrder->discount_note = join("<br>", $discount_notes);
		$updateOrder->save();
		return $price;
	}

	public function calculatePromotionOrderPrice($updateOrder, $variant)
	{
		$price = $variant->price;
		$updateOrder->unit_price = $variant->price;
		if (
			$this->rank
			&& $this->RANK_EXTRA_DISCOUNT_ACTIVE
			&& $this->RANK_EXTRA_DISCOUNT
		) {
			$discount_amount = $price * ($this->RANK_EXTRA_DISCOUNT / 100);
			$price = $price -  $discount_amount;
			$updateOrder->discount_note = 'Extra discount (' . $this->rank . ') ' . $this->RANK_EXTRA_DISCOUNT . '%' . ' : -' . Common::currency_format($discount_amount * 1000);
		}
		$updateOrder->price = $price;
		$updateOrder->save();
		return $price;
	}

	public function processOrderPrice($updateOrder,  $variant)
	{
		// Not Calculate the free variant
		if ($variant->is_free) {
			return 0;
		}
		// Handle Service Order
		if (!empty($updateOrder->promotion_hash)) {
			return $this->calculatePromotionOrderPrice($updateOrder, $variant);
		}
		return $this->calculateNormalOrderPrice($updateOrder, $variant);
	}

	public function add_service_reminder($order)
	{
		$service_category = $order->variant->service->serviceCategory->name;
		$is_owner = $order->is_owner;
		if ($service_category === 'facials' && $is_owner) {
			$service_reminders[] = $order->variant->name;
		}
	}

	public function apply_extra_discounts($order)
	{
	}

	public function calculate_discount($order, $discount)
	{
		$discount_amount = ($discount->type === 'percentage' ? ($discount->amount / 100) : $discount->amount) * $order->unit_price;
		$order->discount_description ? $order->discount_description .= ",{$discount->name}" : $order->discount_description .= $discount->name;
		if (
			$this->rank
			&& $this->RANK_EXTRA_DISCOUNT_ACTIVE
			&& $this->RANK_EXTRA_DISCOUNT
		) {
			$discount_amount += $order->unit_price * ($this->RANK_EXTRA_DISCOUNT / 100);
			$order->discount_description .= " + extra({$this->RANK_EXTRA_DISCOUNT}%)";
		}
		$order->discount_amount += $discount_amount;
		$order->discount_description .= (' :' . Common::currency_format($discount_amount * 1000));
	}

	public function apply_individual_discount($order, $discount)
	{
		$condition = $discount->conditions;
		// Check customer condition: 
		// (1) apply for non-member
		// (2) apply for member only 
		// (3) apply all
		$customer_condition = ($condition['apply_to'] === 'non-member' && empty($this->rank))
			|| (!$condition['apply_to'] === 'member' && in_array($this->rank, $condition['ranks']))
			|| ($condition['apply_to'] === 'all');

		if ($customer_condition) {
			// Check service condition: 
			// (1) apply all services
			if ($discount['variant_id'] === null && $discount['service_id'] === null && $discount['service_category_id'] === null) {
				$this->calculate_discount($order, $discount);
				return;
			}

			// (2) apply for a variant
			if ($discount['variant_id'] !== null) {
				if ($discount['variant_id'] === $order->variant->id) {
					$this->calculate_discount($order, $discount);
				}
				return;
			}

			// (3) apply for all variants of a service
			if ($discount['service_id'] !== null) {
				if ($discount['service_id'] === $order->variant->service_id) {
					$this->calculate_discount($order, $discount);
				}
				return;
			}

			// (4) apply for all variants of all services of a service category
			if ($discount['service_category_id'] !== null) {
				if ($discount['service_category_id'] === $order->variant->service->service_category_id) {
					$this->calculate_discount($order, $discount);
				}
				return;
			}
		}
		return;
	}

	public function apply_discounts($order)
	{
		$individual_discounts = $this->discounts['individual'];
		foreach ($individual_discounts as $discount) {
			$this->apply_individual_discount($order, $discount);
		}
	}
	public function process_order($order)
	{
		if ($order->variant->is_free) {
			$order->unit_price  = 0;
			$order->price = 0;
			return;
		}
		if (empty($order->promotion_hash)) {
			$this->apply_discounts($order);
		}
		$order->price = $order->unit_price - $order->discount_amount;
		var_dump($order->price);
		var_dump($order->discount_amount);
		var_dump($order->discount_description);
		die;
	}
}

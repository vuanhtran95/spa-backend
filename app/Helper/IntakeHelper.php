<?php

namespace App\Helper;

use App\Discount;
use App\Variable;
use Illuminate\Support\Carbon;
use App\Helper\Common;

class IntakeHelper
{
	public $variables = [
		'rank_discount_active' => 'RANK_EXTRA_DISCOUNT_ACTIVE',
		'diamond' => 'DIAMOND_EXTRA_DISCOUNT',
		'gold' => 'GOLD_EXTRA_DISCOUNT',
		'silver' => 'SILVER_EXTRA_DISCOUNT',
	];
	public $point_rate_variables = [
		'non-member' => 'POINT_RATE',
		'diamond' => 'POINT_RATE_DIAMOND',
		'gold' => 'POINT_RATE_GOLD',
		'silver' => 'POINT_RATE_SILVER',
	];
	public $APPLY_ON = [
		'whole_bill' => 'whole_bill',
		'all' => 'all',
		'service_categories' => 'service_categories',
		'services' => 'services',
		'variants' => 'variants',
	];
	public $RANK_EXTRA_DISCOUNT_ACTIVE = 0;
	public $RANK_EXTRA_DISCOUNT = 0;
	public $POINT_RATE = 0;
	public $rank = null;
	public $customer = null;
	public $discounts =  ['whole_bill' => [], 'individual' => []];
	public $service_reminders = [];
	public $points = 0;

	public function __construct($customer, $created_at)
	{
		if (!empty($customer)) {
			$this->customer = $customer;
			$this->rank = $customer->rank;
			$this->set_point_rate();
			$this->set_rank_discount_active();
			if (!empty($this->RANK_EXTRA_DISCOUNT_ACTIVE)) {
				$this->set_rank_extra_discount();
			}
		}
		$this->get_discounts($created_at);
	}

	public function get_points()
	{
		return $this->points;
	}

	public function set_rank_discount_active()
	{
		$id = $this->variables['rank_discount_active'];
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

	public function set_point_rate()
	{
		$id = null;
		if (!empty($this->rank)) {
			$id = $this->point_rate_variables[$this->rank];
		} else {
			$id = $this->point_rate_variables['non-member'];
		}
		$found = Variable::find($id);
		if (!empty($found)) {
			$this->POINT_RATE = floatval($found->value / 100);
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
			$apply_on = $discount['conditions']['apply_on_conditions']['key'];
			if ($apply_on === $this->APPLY_ON['whole_bill']) {
				$accumulator['whole_bill'][] = $discount;
			} else {
				$accumulator['individual'][] = $discount;
			}
			return $accumulator;
		}, ['whole_bill' => [], 'individual' => []]);
	}

	public function add_service_reminder($order)
	{
		$service_category = $order->variant->service->serviceCategory->name;
		$is_owner = $order->is_owner;
		if ($service_category === 'facials' && $is_owner) {
			$service_reminders[] = $order->variant->name;
		}
	}

	public function calculate_discount($order, $discount, $discount_object)
	{
		$discount_amount = 0;
		$discount_description = '';
		if ($discount_object['type'] === 'percentage') {
			$discount_amount = ($discount_object['value'] / 100) * $order->unit_price;
			$discount_description = "Giảm {$discount_object['value']}%";
		} else {
			$discount_amount = $discount_object['value'];
			$discount_description = "Giảm " . Common::currency_format($discount_object['value'] * 1000);
		}

		$order->discount_description ? $order->discount_description .= ",{$discount_description}" : $order->discount_description .= $discount_description;

		$order->discount_amount += $discount_amount;
	}

	public function apply_individual_discount($order, $discount)
	{

		$condition = $discount['conditions'];
		$apply_to_key = $condition['apply_to_conditions']['key'];
		$apply_to_value = $condition['apply_to_conditions']['value'];
		// Check customer condition: 
		// (1) apply for non-member
		// (2) apply for member only 
		// (3) apply all
		$customer_condition = ($apply_to_key === 'non-member' && empty($this->rank))
			|| ($apply_to_key  === 'member' && in_array($this->rank, $apply_to_value))
			|| ($apply_to_key  === 'all');
		// Can apply discount to this user
		if ($customer_condition) {
			$apply_on_key = $condition['apply_on_conditions']['key'];
			$apply_on_value = $condition['apply_on_conditions']['value'];
			// Check specific cases:
			$id = null;
			switch ($apply_on_key) {
				case $this->APPLY_ON['variants']:
					$id =  $order->variant->id;
					break;
				case $this->APPLY_ON['services']:
					$id = $order->variant->service_id;
					break;
				case $this->APPLY_ON['service_categories']:
					$id = $order->variant->service->service_category_id;
					break;
				default:
					break;
			}
			// Check if we have specific discount for current case
			$found_key = array_search($id, array_column($apply_on_value, 'id'));
			if ($found_key !== false) {
				$this->calculate_discount($order, $discount, $apply_on_value[$found_key]);
				return true;
			}
			// If no specific case, check if apply on all case is possible
			if ($apply_on_key === $this->APPLY_ON['all']) {
				$this->calculate_discount($order, $discount, $apply_on_value);
				return true;
			}
		}
		return false;
	}

	public function apply_discounts($order)
	{
		$individual_discounts = $this->discounts['individual'];
		$isAppliedDiscount = false;
		if (empty($individual_discounts)) {
			$this->points += $order->unit_price * $this->POINT_RATE;
		} else {
			foreach ($individual_discounts as $discount) {
				$isAppliedDiscount = $this->apply_individual_discount($order, $discount);
				if ($isAppliedDiscount) {
					if ($this->rank && $this->RANK_EXTRA_DISCOUNT_ACTIVE && $this->RANK_EXTRA_DISCOUNT) {
						$order->discount_amount += ($order->unit_price - $order->discount_amount) * ($this->RANK_EXTRA_DISCOUNT / 100);
						$order->discount_description .= " + extra({$this->RANK_EXTRA_DISCOUNT}%)";
					}
					$order->discount_description .= (': -' . Common::currency_format($order->discount_amount * 1000));
					break;
				};
			}
		}
		if (!$isAppliedDiscount) {
			$this->points += $order->unit_price * $this->POINT_RATE;
		}
		$order->price = $order->unit_price - $order->discount_amount;
	}
	public function process_order($order)
	{
		// Free service
		if ($order->variant->is_free) {
			$order->unit_price  = 0;
			$order->price = 0;
			return;
		}
		//Promotion service
		if ($order->promotion_hash) {
			$order->price = $order->unit_price;
			return;
		}
		// Normal Service
		$this->apply_discounts($order);
	}

	public function calculate_whole_bill_discount($totalPrice)
	{
		$result = [
			'amount' => 0,
			'description' => '',
		];
		$whole_bill = $this->discounts['whole_bill'];
		foreach ($whole_bill as $discount) {
			$condition = $discount['conditions'];
			$apply_to_key = $condition['apply_to_conditions']['key'];
			$apply_to_value = $condition['apply_to_conditions']['value'];
			// Check customer condition: 
			// (1) apply for non-member
			// (2) apply for member only 
			// (3) apply all
			$customer_condition = ($apply_to_key === 'non-member' && empty($this->rank))
				|| ($apply_to_key  === 'member' && in_array($this->rank, $apply_to_value))
				|| ($apply_to_key  === 'all');
			if ($customer_condition) {
				$discount_description = '';
				$discount_object =  $condition['apply_on_conditions']['value'];
				if ($discount_object['type'] === 'percentage') {
					$result['amount'] += ($discount_object['value'] / 100) * $totalPrice;
					$discount_description = "Giảm {$discount_object['value']}% tổng bill";
				} else {
					$result['amount'] += $discount_object['value'];
					$discount_description = "Giảm " . Common::currency_format($discount_object['value'] * 1000) . ' tổng bill';
				}
				$result['description'] ? $result['description'] .= ",{$discount_description}" : $result['description'] .= $discount_description;
			}
		}
	}
}

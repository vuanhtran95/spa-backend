<?php

namespace App\Helper;

use App\Intake;
use App\Invoice;
use App\Package;

class Common
{
	public static function getIds($n)
	{
		return $n['id'];
	}

	public static function calCommissionPercent($attitude, $skill)
	{
		switch ((int)$attitude + (int)$skill) {
			case 10:
				return 1;
			case 9:
				return 0.9;
			case 8:
				return 0.8;
			case 7:
				return 0.7;
			default:
				return 0.5;
		}
	}
	public static function upRank($customer)
	{
		$new_rank = false;
		$current_rank = empty($customer->rank) ? 'non-member' : $customer->rank;
		$customerId = $customer->id;
		$intake_spending = Intake::where('customer_id', $customerId)
			->where('is_valid', '=', 1)
			->where('payment_type', '=', 'cash')
			->sum('final_price');

		$package_spending = Package::where('customer_id', $customerId)
			->where('is_valid', '=', 1)
			->sum('total_price');

		$invoice_spending = Invoice::where('customer_id', $customerId)
			->where('type', '=', 'deposit')
			->where('status', '=', 'paid')
			->sum('amount');
		$total_spending = $intake_spending + $package_spending + $invoice_spending;
		if ($total_spending < 10000 || $customer->rank === 'diamond') {
			return  false;
		}
		switch ($customer->rank) {
			case "gold":
				if ($total_spending >= 50000) {
					$customer->rank = 'diamond';
					//   $customer->save();
					$new_rank = 'diamond';
				}
				break;
			case "silver":
				if ($total_spending >= 50000) {
					$customer->rank = 'diamond';
					$customer->save();
					$new_rank = 'diamond';
					break;
				}
				if ($total_spending >= 20000) {
					$customer->rank = 'gold';
					$customer->save();
					$new_rank = 'gold';
					break;
				}
			case NULL:
				if ($total_spending >= 50000) {
					$customer->rank = 'diamond';
					$customer->save();
					$new_rank = 'diamond';
					break;
				}
				if ($total_spending >= 20000) {
					$customer->rank = 'gold';
					$customer->save();
					$new_rank = 'gold';
					break;
				}
				if ($total_spending >= 10000) {
					$customer->rank = 'silver';
					$customer->save();
					$new_rank = 'silver';
					break;
				}
			default:
				break;
		}
		return empty($new_rank) ?  false : json_encode([
			'from' => $current_rank,
			'to' => $new_rank,
			'total_spending' => $total_spending
		]);
	}
	public static function currency_format($number, $postfix = '₫', $suffix = '')
	{
		if (!empty($number)) {
			return " {$postfix} " . number_format($number, 0, ',', '.') . " {$suffix}";
		}
	}
}

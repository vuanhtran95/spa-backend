<?php

use Illuminate\Database\Seeder;
use App\Intake;
use App\Helper\IntakeHelper;
use Illuminate\Support\Facades\DB;
use App\Customer;

class CustomerCashPointSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Customer::where('cash_point', '>', 0)
			->update([
				'cash_point' => 0
			]);
		$query = new Intake();
		$intakes = $query->where('created_at', '>=', '2021-09-06 08:38:30')->where('is_valid', '=', 1)
			->with(
				['orders' => function ($query) {
					$query->with(
						['variant' => function ($subQuery) {
							$subQuery->with(['service' => function ($eQuery) {
								$eQuery->with('serviceCategory');
							}]);
						}]
					);
				}]
			)->get();
		if (empty($intakes)) {
			var_dump('Not found');
			return;
		}
		foreach ($intakes as $intake) {
			$this->processRecord($intake);
		}
		var_dump('done !');
	}
	public function processRecord($intake)
	{
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
						}
						/* 2.2 Process normal order */ else {
							$helper->process_order($order);
							$totalPrice = $totalPrice  + $order->price * $order->amount;
						}
					}
				);
			}

			/* 7. Collect point for customer */
			if ($intake->final_price > 0 && !empty($customer)) {
				$intake->customer_earned_points =  $helper->get_points();
				$customer->cash_point = $customer->cash_point + $helper->get_points();
				var_dump($customer->id . ': ' . $helper->get_points());
				$intake->save();
				$customer->save();
			}
			DB::commit();
		} catch (\Exception $exception) {
			DB::rollBack();
			var_dump($exception->getMessage());
		}
	}
}

<?php

use App\Order;
use App\Variable;
use App\Intake;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AddOverTimeColumnToOrdersTable extends Migration
{
	public static $OVERTIME_COMMISSION = 'OVERTIME_COMMISSION';
	public static $OVERTIME_COMMISSION_RATE = 'OVERTIME_COMMISSION_RATE';
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('orders', function (Blueprint $table) {
			$table->timestamp('approved_time')->nullable();
			$table->boolean('is_overtime')->default(false);
		});
		DB::beginTransaction();
		try {
			$overtime = Variable::find(self::$OVERTIME_COMMISSION);
			$overtime_rate = Variable::find(self::$OVERTIME_COMMISSION_RATE);
			if (!empty($overtime) && !empty($overtime_rate)) {
				$query = new Order();
				$orders = $query::get();
				foreach ($orders as $order) {
					$intake = Intake::find($order->intake_id);
					if (!$intake) {
						var_dump('delete order ' . $order->id);
						Order::destroy($order->id);
						continue;
					}
					$approved_date = Carbon::parse($intake->approved_date, 'UTC')->setTimezone('Asia/Ho_Chi_Minh');
					$day = $approved_date->day;
					$month = $approved_date->month;
					$year = $approved_date->year;
					$over_time =  Carbon::createFromFormat('Y-m-d H:i:s', $year . '-' . $month . '-' . $day . ' ' . $overtime->value, 'Asia/Ho_Chi_Minh');
					$is_overtime = $approved_date->greaterThanOrEqualTo($over_time);
					$order->approved_time = $intake->approved_date;
					$order->is_overtime = 	$is_overtime;
					$order->save();
				}
			}
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			throw new \Exception('Unable to run migration');
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('orders', function (Blueprint $table) {
			$table->dropColumn('approved_time');
			$table->dropColumn('is_overtime');
		});
	}
}

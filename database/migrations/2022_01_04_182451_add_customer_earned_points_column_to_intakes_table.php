<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerEarnedPointsColumnToIntakesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('intakes', function (Blueprint $table) {
			$table->float('customer_earned_points')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('intakes', function (Blueprint $table) {
			$table->dropColumn('customer_earned_points');
		});
	}
}

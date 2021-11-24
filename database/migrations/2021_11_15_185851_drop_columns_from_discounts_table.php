<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DropColumnsFromDiscountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('discounts', function (Blueprint $table) {
			DB::statement('SET FOREIGN_KEY_CHECKS=0;');
			$table->dropColumn('type');
			$table->dropColumn('value');
			$table->dropColumn('all_variants');
			$table->dropForeign(['service_category_id']);
			$table->dropColumn('service_category_id');
			$table->dropForeign(['service_id']);
			$table->dropColumn('service_id');
			$table->dropForeign(['variant_id']);
			$table->dropColumn('variant_id');
			DB::statement('SET FOREIGN_KEY_CHECKS=1;');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('discounts', function (Blueprint $table) {
			$table->boolean('all_variants')->default(0);
			$table->unsignedBigInteger('service_category_id')->nullable();
			$table->unsignedBigInteger('service_id')->nullable();
			$table->unsignedBigInteger('variant_id')->nullable();
			$table->enum('type', ['percentage', 'amount']);
			$table->float('value')->default(0);
			$table->foreign('service_category_id')
				->references('id')
				->on('service_categories')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->foreign('service_id')
				->references('id')
				->on('services')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->foreign('variant_id')
				->references('id')
				->on('variants')
				->onUpdate('cascade')
				->onDelete('cascade');
		});
	}
}

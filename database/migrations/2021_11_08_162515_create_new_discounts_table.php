<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNewDiscountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('discounts', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name');
			$table->string('fromTime');
			$table->string('toTime');
			$table->date('fromDate');
			$table->date('toDate');
			$table->json('conditions');
			$table->enum('type', ['percentage', 'amount']);
			$table->float('value')->default(0);
			$table->boolean('is_active')->default(1);
			$table->unsignedBigInteger('service_category_id')->nullable();
			$table->unsignedBigInteger('service_id')->nullable();
			$table->unsignedBigInteger('variant_id')->nullable();
			$table->boolean('mon')->default(0);
			$table->boolean('tue')->default(0);
			$table->boolean('wed')->default(0);
			$table->boolean('thu')->default(0);
			$table->boolean('fri')->default(0);
			$table->boolean('sat')->default(0);
			$table->boolean('sun')->default(0);
			$table->boolean('whole_bill')->default(0);
			$table->boolean('all_variants')->default(0);
			$table->timestamps();

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

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		Schema::drop('discounts');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}
}

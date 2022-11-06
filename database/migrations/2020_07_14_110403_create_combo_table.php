<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComboTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('expiry_date')->nullable();
            $table->unsignedSmallInteger('amount');
            $table->unsignedSmallInteger('number_used')->default(0);
            $table->boolean('is_valid')->default(false);
            $table->float('total_price')->default(0);
            $table->float('sale_commission')->default(0);
            $table->timestamps();
        });

        Schema::table('combos', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id');

            $table->foreign('variant_id')
                ->references('id')
                ->on('variants')
                ->onUpdate('cascade');
        });

        Schema::table('combos', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onUpdate('cascade');
        });

        Schema::table('combos', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id');

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('combos', function (Blueprint $table) {
            $table->dropForeign(['employee_id', 'customer_id', 'variant_id']);
        });
        Schema::dropIfExists('combos');
    }
}

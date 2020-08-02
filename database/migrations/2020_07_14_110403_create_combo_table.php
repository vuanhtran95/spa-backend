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
            $table->string('name')->nullable();
            $table->longText('descriptions')->nullable();
            $table->date('expiry_date');
            $table->unsignedSmallInteger('amount');
            $table->unsignedSmallInteger('number_used')->default(0);
            $table->boolean('is_valid')->default(false);
            $table->float('total_price')->default(0);
            $table->timestamps();
        });

        Schema::table('combos', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id');

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
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
            $table->dropForeign(['employee_id', 'customer_id', 'service_id']);
        });
        Schema::dropIfExists('combo');
    }
}

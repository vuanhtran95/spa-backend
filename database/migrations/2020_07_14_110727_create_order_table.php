<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedSmallInteger('amount')->default(1);
            $table->longText('note')->nullable();
            $table->float('price')->default(0);
            $table->string('gender')->default('both');
            $table->float('working_commission')->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id');

            $table->foreign('variant_id')
                ->references('id')
                ->on('variants')
                ->onUpdate('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('intake_id');

            $table->foreign('intake_id')
                ->references('id')
                ->on('intakes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id');

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onUpdate('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('combo_id')->nullable();

            $table->foreign('combo_id')
                ->references('id')
                ->on('combos')
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
            $table->dropForeign(['combo_id', 'user_id', 'intake_id', 'service_id']);
        });
        Schema::dropIfExists('order');
    }
}

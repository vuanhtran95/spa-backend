<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntakeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intakes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('is_valid')->default(false);
            $table->float('total_price')->default(0);
            $table->timestamps();
        });

        Schema::table('intakes', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onUpdate('cascade');
        });

        Schema::table('intakes', function (Blueprint $table) {
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
        Schema::table('intakes', function (Blueprint $table) {
            $table->dropForeign(['employee_id', 'customer_id']);
        });
        Schema::dropIfExists('intakes');
    }
}

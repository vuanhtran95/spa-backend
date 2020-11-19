<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSomeColumnsFromCombosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('combos', function (Blueprint $table) {
            // $table->dropForeign(['customer_id']);
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
            // $table->dropColumn('customer_id');
            $table->dropColumn('is_valid');
            $table->dropColumn('sale_commission');
            $table->dropColumn('expiry_date');
            $table->boolean('is_promotion_combo')->default(false);
            $table->unsignedBigInteger('package_id');

            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
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
            $table->date('expiry_date')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->float('sale_commission')->default(0);
            $table->dropColumn('is_promotion_combo');
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
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
}

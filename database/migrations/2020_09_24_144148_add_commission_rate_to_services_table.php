<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionRateToServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('order_commission');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->integer('commission_rate_male')->nullable();
            $table->integer('commission_rate_female')->nullable();
            $table->integer('commission_rate_both')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('commission_rate_male');
            $table->dropColumn('commission_rate_female');
            $table->dropColumn('commission_rate_both');
        });
    }
}

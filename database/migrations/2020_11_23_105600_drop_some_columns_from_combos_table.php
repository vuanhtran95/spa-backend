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
            $table->dropColumn('is_valid');
            $table->dropColumn('sale_commission');
            $table->dropColumn('expiry_date');
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
            $table->float('sale_commission')->default(0);
            $table->boolean('is_valid')->default(false);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DropPaymentTypeColumnsFromIntakes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE intakes SET intakes.payment_method_id = intakes.payment_type");

        Schema::table('intakes', function (Blueprint $table) {
            $table->dropColumn('payment_type');
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
            $table->string('payment_type');
        });
        DB::statement("UPDATE intakes SET intakes.payment_type = intakes.payment_method_id");

    }
}

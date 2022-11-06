<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Intake;
use Illuminate\Support\Facades\DB;

class AddPaymentReceivedAmountColumnToIntakesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intakes', function (Blueprint $table) {
            $table->float('payment_received_amount')->default(0);
        });
        Intake::where('is_valid', 1)
			->update([
				'payment_received_amount' => DB::raw('`final_price`')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intakes', function (Blueprint $table) {
            $table->dropColumn('payment_received_amount');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPaymentMethodIdColumn extends Migration
{
    private $TABLES = ['intakes', 'invoices', 'packages'];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->TABLES as $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->string('payment_method_id')->nullable();
                $table->foreign('payment_method_id')
                    ->references('id')
                    ->on('payment_methods')
                    ->onUpdate('cascade');
            });
        }
        DB::statement("UPDATE invoices SET invoices.payment_method_id = 'cash' WHERE invoices.type = 'deposit'");
        DB::statement("UPDATE packages SET packages.payment_method_id = 'cash'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->TABLES as $table_name) {
            if (Schema::hasColumn($table_name, 'payment_method_id')) {
                Schema::table($table_name, function (Blueprint $table) {
                    $table->dropForeign(['payment_method_id']);
                    $table->dropColumn('payment_method_id');
                });
            }
        }
    }
}

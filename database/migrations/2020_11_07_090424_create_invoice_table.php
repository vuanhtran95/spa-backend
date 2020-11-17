<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')
            ->references('id')
            ->on('customers')
            ->onUpdate('cascade');

            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')
            ->references('id')
            ->on('employees')
            ->onUpdate('cascade');

            $table->unsignedBigInteger('intake_id')->nullable();
            $table->foreign('intake_id')
            ->references('id')
            ->on('intakes')
            ->onUpdate('cascade');

            $table->integer('amount');
            $table->integer('promotion_amount')->nullable();
            $table->text('note')->nullable();
            $table->binary('signature')->nullable();
            $table->string('payment_type');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_employee_id_foreign');
            $table->dropForeign('invoices_customer_id_foreign');
            $table->dropForeign('invoices_intake_id_foreign');
        });
        Schema::dropIfExists('invoices');
    }
}

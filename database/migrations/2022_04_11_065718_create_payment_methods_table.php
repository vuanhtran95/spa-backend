<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->json('metadata')->nullable();
        });
        $data = [
            ['id'=>'cash', 'name'=> 'Cash'],
            ['id'=>'momo', 'name'=> 'MoMo'],
            ['id'=>'bank_transfer', 'name'=> 'Bank Transfer'],
            ['id'=>'credit', 'name'=> 'Credit'],
            ['id'=>'card', 'name'=> 'Card'],
        ];
        DB::table('payment_methods')->insert($data); // Query Builder approach
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
}

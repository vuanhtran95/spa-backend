<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->nullable();
            $table->string('description')->default('');
            $table->integer('amount')->default(0);
            $table->float('sale_price')->default(0);
            $table->float('price')->default(0);
            $table->json('metadata')->nullable();
            $table->string('mapper')->nullable();
            $table->timestamps();
            
            $table->unsignedBigInteger('variant_id');
            $table->foreign('variant_id')
				->references('id')
				->on('variants')
            ->onUpdate('cascade');

            $table->unsignedBigInteger('intake_id')->nullable();
            $table->foreign('intake_id')
				->references('id')
            ->on('intakes')
            ->onUpdate('cascade');

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')
				->references('id')
            ->on('customers')
            ->onUpdate('cascade');

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')
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
        Schema::dropIfExists('product_logs');
    }
}

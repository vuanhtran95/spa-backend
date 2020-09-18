<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->longText('descriptions')->nullable();
            $table->boolean('is_combo_sold');
            $table->integer('order_commission');
            $table->integer('combo_commission');
            $table->float('combo_ratio')->default(1.2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('service_category_id');

            $table->foreign('service_category_id')
                ->references('id')
                ->on('services')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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
            $table->dropForeign(['service_category_id']);
        });
        Schema::dropIfExists('services');
    }
}

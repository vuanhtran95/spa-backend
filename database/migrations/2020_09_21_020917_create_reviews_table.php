<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('service_point')->default(0);
            $table->integer('skill')->default(0);
            $table->integer('attitude')->default(0);
            $table->timestamps();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id');

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('review_form_id');

            $table->foreign('review_form_id')
                ->references('id')
                ->on('review_forms')
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
        Schema::table('review_forms', function (Blueprint $table) {
            $table->dropForeign(['review_form_id', 'order_id']);
        });
        Schema::dropIfExists('reviews');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('facility')->default(0);
            $table->integer('customer_satisfy')->default(0);
            $table->longText('note')->nullable();
            $table->timestamps();
        });

        Schema::table('review_forms', function (Blueprint $table) {
            $table->unsignedBigInteger('intake_id');

            $table->foreign('intake_id')
                ->references('id')
                ->on('intakes')
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
        Schema::table('review_forms', function (Blueprint $table) {
            $table->dropForeign(['intake_id']);
        });
        Schema::dropIfExists('review_forms');
    }
}

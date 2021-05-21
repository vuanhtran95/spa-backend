<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->dateTime('from');
            $table->dateTime('to');
            $table->enum('type', ['percentage', 'amount']);
            $table->float('value')->default(0);
            $table->boolean('is_active')->default(1);
            $table->string('rank_name')->nullable();
            $table->unsignedBigInteger('service_category_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->boolean('mon')->default(1);
            $table->boolean('tue')->default(1);
            $table->boolean('wed')->default(1);
            $table->boolean('thu')->default(1);
            $table->boolean('fri')->default(1);
            $table->boolean('sat')->default(1);
            $table->boolean('sun')->default(1);
            $table->timestamps();

            $table->foreign('rank_name')
                ->references('name')
                ->on('ranks')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->foreign('service_category_id')
                ->references('id')
                ->on('service_categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('variant_id')
                ->references('id')
                ->on('variants')
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
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropForeign(['rank_name', 'service_category_id', 'service_id', 'variant_id']);
        });
        Schema::dropIfExists('discounts');
    }
}

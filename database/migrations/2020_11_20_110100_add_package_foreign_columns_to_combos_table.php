<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPackageForeignColumnsToCombosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('combos', function (Blueprint $table) {
            $table->boolean('is_promotion_combo')->default(false);
            $table->unsignedBigInteger('package_id')->nullable();

            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
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
        Schema::table('combos', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
            $table->dropColumn('is_promotion_combo');
        });
    }
}

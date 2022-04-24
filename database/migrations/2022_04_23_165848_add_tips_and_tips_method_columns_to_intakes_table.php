<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipsAndTipsMethodColumnsToIntakesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intakes', function (Blueprint $table) {
            $table->float('tips')->default(0);
            $table->string('tips_method')->nullable();
            $table->foreign('tips_method')
                ->references('id')
                ->on('payment_methods')
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
        Schema::table('intakes', function (Blueprint $table) {
            $table->dropForeign(['tips_method']);
            $table->dropColumn('tips');
            $table->dropColumn('tips_method');
        });
    }
}

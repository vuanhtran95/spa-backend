<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('point');

            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id')
            ->references('id')
            ->on('tasks')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')
            ->references('id')
            ->on('employees')
            ->onUpdate('cascade')
            ->onDelete('cascade');

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
        if (Schema::hasTable('task_history')) {
            Schema::table('task_history', function (Blueprint $table) {
                $table->dropForeign('task_history_task_id_foreign');
                $table->dropForeign('task_history_employee_id_foreign');
            });
        }

        Schema::dropIfExists('task_history');
    }
}

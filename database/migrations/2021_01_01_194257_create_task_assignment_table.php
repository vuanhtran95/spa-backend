<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskAssignmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->string('title');
            $table->boolean('mon')->default(0);
            $table->boolean('tue')->default(0);
            $table->boolean('wed')->default(0);
            $table->boolean('thu')->default(0);
            $table->boolean('fri')->default(0);
            $table->boolean('sat')->default(0);
            $table->boolean('sun')->default(0);

            $table->unsignedBigInteger('task_id')->nullable();
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
        if (Schema::hasTable('task_assignments')) {
            Schema::table('task_assignments', function (Blueprint $table) {
                $table->dropForeign('task_assignments_task_id_foreign');
                $table->dropForeign('task_assignments_employee_id_foreign');
            });
        }
        
        Schema::dropIfExists('task_assignments');
    }
}

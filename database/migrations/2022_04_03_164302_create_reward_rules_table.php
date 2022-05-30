<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Constants\RewardRule;

class CreateRewardRulesTable extends Migration
{
    private $tableName = 'reward_rules';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('left_over_point_expired_date');
            $table->enum('status', [RewardRule::ACTIVE, RewardRule::EXPIRED])->default(RewardRule::ACTIVE);
            $table->timestamps();
        });

        // Call seeder
        Artisan::call('db:seed', [
            '--class' => 'RewardRulesTableSeeder',
            '--force' => true // force to run on Production
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}

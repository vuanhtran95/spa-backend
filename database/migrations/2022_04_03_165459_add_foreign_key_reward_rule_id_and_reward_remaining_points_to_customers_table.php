<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\RewardRule as RewardRuleStatus;
use App\RewardRule;

class AddForeignKeyRewardRuleIdAndRewardRemainingPointsToCustomersTable extends Migration
{
    private $tableName = 'customers';
    private $foreignKeyName = 'reward_rule_id';

    /**
     * @throws Exception
     */
    public function up()
    {
        $activeRewardRule = RewardRule::where('status', RewardRuleStatus::ACTIVE)->firstOrFail()->toArray();

        if (empty($activeRewardRule['id'])) {
            throw new Exception('Unable to find an ACTIVE reward rule. Please rerun RewardRulesTableSeeder.');
        }

        Schema::table($this->tableName, function (Blueprint $table) use ($activeRewardRule) {
            $table->unsignedBigInteger($this->foreignKeyName)->default($activeRewardRule['id']);

            $table->foreign($this->foreignKeyName)
                ->references('id')
                ->on('reward_rules')
                ->onUpdate('cascade'); // This will throw a foreign key error if someone tries to update this id to something not existing in reward rule table

            $table->float('reward_remaining_points')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn($this->tableName, $this->foreignKeyName)) //check the column
        {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropForeign('customers_' . $this->foreignKeyName . '_foreign');
                $table->dropColumn([$this->foreignKeyName, 'reward_remaining_points']);
            });
        }
    }
}

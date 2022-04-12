<?php

namespace App\Repositories;

use App\RewardRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Constants\RewardRule as RewardRuleStatus;

class RewardRuleRepository
{
    /**
     * @param $rewardRuleId
     * @throws ModelNotFoundException
     * @return RewardRule
     */
    public function findOne($rewardRuleId)
    {
        return RewardRule::findOrFail($rewardRuleId);
    }

    /**
     * @param $rewardRuleId
     * @param $updatedData
     * @return RewardRule | null
     * @throws \Exception
     */
    public function update($rewardRuleId, $updatedData) {
        if (!is_array($updatedData)) {
            throw new \Exception('The updated data for reward rule is not in correct format.');
        }

        $rewardRule = null;
        try {
            DB::beginTransaction();
            $rewardRule = RewardRule::where('status', RewardRuleStatus::ACTIVE)->update($updatedData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return $rewardRule;
    }
}

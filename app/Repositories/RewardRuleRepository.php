<?php

namespace App\Repositories;

use App\RewardRule;
use Illuminate\Support\Facades\DB;

class RewardRuleRepository
{
    /**
     * @param array $condition
     * @return mixed
     */
    public function findBy($condition) {
        return RewardRule::where($condition)->firstOrFail();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create($data) {
        return RewardRule::create($data);
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
            $rewardRule = RewardRule::where('id', $rewardRuleId)->update($updatedData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return $rewardRule;
    }
}

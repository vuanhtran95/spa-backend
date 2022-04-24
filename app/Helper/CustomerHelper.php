<?php

namespace App\Helper;

use App\Constants\RewardRule as RewardRuleStatus;
use App\Customer;
use App\Constants\Common;
use App\Repositories\RewardRuleRepository;
use Carbon\Carbon;

class CustomerHelper
{
    private $customer;

    private $rewardRule;

    private $rewardRuleRepository;

    public function __construct(RewardRuleRepository $rewardRuleRepository)
    {
        $this->rewardRuleRepository = $rewardRuleRepository;
    }

    public function setCustomer(Customer $customer) {
        $this->customer = $customer;
    }

    public function setRewardRule() {
        if (empty($this->customer->rewardRule)) {
            throw new \Exception('Customer is not linked with any reward rules');
        }

        // Handle rewardRule reassignment
        if (RewardRuleStatus::EXPIRED === $this->customer->rewardRule->status) {
            // Find an active reward rule
            $activeRewardRule = $this->rewardRuleRepository->findBy([
                'status' => RewardRuleStatus::ACTIVE
            ]);

            // Assign customer's reward rule
            $this->customer->reward_rule_id = $activeRewardRule->id;
        }

        // Recheck this logic
        $this->rewardRule = $this->customer->rewardRule;
    }

    public function updateCustomerPoints($currentPoints) {
        if (empty($this->customer->rewardRule)) {
            throw new \Exception('Customer is not linked with any reward rules');
        }

        if (RewardRuleStatus::ACTIVE === $this->customer->rewardRule->status) {
            $today = Carbon::now(Common::SYSTEM_TIMEZONE);

            $validToDate = Carbon::parse($this->customer->rewardRule->end_date);

            if ($today->isAfter($validToDate)) {
                // Reset customer point
                $this->customer->cash_point = 0;

                // Set current reward rule configuration to EXPIRED
                $currentLeftOverPointDate = $this->customer->rewardRule->left_over_point_expired_date;
                $this->rewardRuleRepository->update($this->customer->rewardRule->id, ['status' => RewardRuleStatus::EXPIRED]);

                // Insert new reward rule configuration
                $startOfYear = $today->copy()->startOfYear()->toDateTime();
                $endOfYear = $today->copy()->endOfYear()->toDateTime();

                $newRewardRule = $this->rewardRuleRepository->create([
                    'start_date' => $startOfYear,
                    'end_date' => $endOfYear,
                    'left_over_point_expired_date' => $currentLeftOverPointDate,
                    'status' => RewardRuleStatus::ACTIVE
                ]);

                // Link customer to the newly created reward rule
                $this->customer->rewardRule->save($newRewardRule);
            } else {
                $this->customer->cash_point += $currentPoints;
            }
        } else {
            // Find the current "ACTIVE" reward rule
            

            $this->customer->cash_point = 0;
        }


    }

    public static function isRewardRemainingPointValid() {

    }
}

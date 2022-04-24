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
        if (!empty($customer)) {
            $this->customer = $customer;
        }
    }

    public function getCustomer() {
        return $this->customer;
    }

    private function handleCustomerPoints() {
        // Store customer's reward remaining points
        if (!empty($this->customer->cash_point)) {
            $this->customer->reward_remaining_points = $this->customer->cash_point;
        }

        // Reset customer point
        $this->customer->cash_point = 0;
    }

    public function updateRewardPointsBasedOnRewardRule() {
        if (empty($this->customer)) {
            return;
        }

        if (empty($this->customer->rewardRule)) {
            throw new \Exception('Customer is not linked with any reward rules');
        }

        // 1. If the customer's reward rule is "ACTIVE", means that we have to verify if this reward rule is still valid (by checking its date)
        if (RewardRuleStatus::ACTIVE === $this->customer->rewardRule->status) {
            $today = Carbon::now(Common::SYSTEM_TIMEZONE);

            $validToDate = Carbon::parse($this->customer->rewardRule->end_date, Common::SYSTEM_TIMEZONE);

            // 1.1 If today is after the reward rule valid date
            if ($today->isAfter($validToDate)) {
                // 1.1.1 First, handle the customer points
                $this->handleCustomerPoints();

                // 1.1.2 Set current reward rule configuration to EXPIRED
                $currentLeftOverPointDate = Carbon::parse($this->customer->rewardRule->left_over_point_expired_date, Common::SYSTEM_TIMEZONE)->addYear()->toDateTime();
                $this->rewardRuleRepository->update($this->customer->rewardRule->id, ['status' => RewardRuleStatus::EXPIRED]);

                // 1.1.3 Insert new reward rule configuration
                $startOfYear = $today->copy()->startOfYear()->toDateTime();
                $endOfYear = $today->copy()->endOfYear()->toDateTime();

                $newRewardRule = $this->rewardRuleRepository->create([
                    'start_date' => $startOfYear,
                    'end_date' => $endOfYear,
                    'left_over_point_expired_date' => $currentLeftOverPointDate,
                    'status' => RewardRuleStatus::ACTIVE
                ]);

                // 1.1.4 Link customer to the newly created reward rule
                $this->customer->rewardRule()->associate($newRewardRule);
            }
            // 1.2.1 Handle customer's reward remaining points
            else {
                $currentLeftOverPointDate = Carbon::parse($this->customer->rewardRule->left_over_point_expired_date, Common::SYSTEM_TIMEZONE);

                if ($this->isRewardRemainingPointInvalid()) {
                    // Reset remaining points
                    $this->customer->reward_remaining_points = 0;
                }
            }
        }
        // 2. When the reward rule of the customer is "EXPIRED" then we have to update the reward rule * points for this customer
        else {
            // Find the current "ACTIVE" reward rule
            $activeRewardRule = $this->rewardRuleRepository->findBy([
                'status' => RewardRuleStatus::ACTIVE
            ]);

            // Link the customer with the newly "ACTIVE" reward rule
            $this->customer->rewardRule()->associate($activeRewardRule);

            // Handle the customer point
            $this->handleCustomerPoints();
        }
    }

    public function saveChanges($changes) {
        if (!empty($changes['cash_point'])) {
            $this->updateRewardPointsBasedOnRewardRule();
        }

        $this->customer->save();
    }

    public function isRewardRemainingPointInvalid() {
        $today = Carbon::now(Common::SYSTEM_TIMEZONE);

        $validLeftOverPointDate = Carbon::parse(
            $this->customer->rewardRule->left_over_point_expired_date, Common::SYSTEM_TIMEZONE
        )->subYear();

        return $today->isAfter($validLeftOverPointDate);
    }
}

<?php

namespace App\Helper;

use App\Customer;
use App\Repositories\RewardRuleRepository;

class RewardRuleHelper
{
    private $customer;

    private $rewardRule;

    public function __construct(RewardRuleRepository $rewardRuleRepository)
    {
    }

    public function setCustomer(Customer $customer) {
        $this->customer = $customer;
    }

    public function getRewardRuleByCustomer() {
        if (empty($this->customer->rewardRule)) {
            throw new \Exception('Customer is not linked with any reward rules');
        }

        $this->rewardRule = $this->customer->rewardRule;
    }

    public function updateCustomerPoints() {

    }

    public static function isRewardRemainingPointValid() {

    }

    public function testnhe() {
        echo 'Tuan dep trai';
    }
}

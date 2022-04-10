<?php

namespace App\Helper;

use App\Customer;

class RewardRuleHelper
{
    private $customer;

    public function setCustomer(Customer $customer) {
        $this->customer = $customer;
    }

    public function getCustomer() {
        return $this->customer;
    }

    public function updateCustomerPoints() {

    }

    public static function isRewardRemainingPointValid() {

    }
}

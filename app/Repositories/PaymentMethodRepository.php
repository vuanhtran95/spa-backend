<?php

namespace App\Repositories;

use App\PaymentMethod;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    public function get(array $condition = [])
    {
        return PaymentMethod::all();
    }
}

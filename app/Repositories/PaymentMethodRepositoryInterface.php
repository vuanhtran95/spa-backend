<?php

namespace App\Repositories;

interface PaymentMethodRepositoryInterface
{
    public function get(array $condition = []);
}

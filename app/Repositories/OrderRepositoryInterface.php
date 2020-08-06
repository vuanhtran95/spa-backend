<?php

namespace App\Repositories;

interface OrderRepositoryInterface
{
    public function get(array $condition = []);

    public function update($id, array $attributes = []);

}

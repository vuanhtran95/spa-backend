<?php

namespace App\Repositories;

interface ProductLogRepositoryInterface
{
    public function create(array $attributes = []);

    public function save($data, $is_update);

    public function get(array $condition = []);
}

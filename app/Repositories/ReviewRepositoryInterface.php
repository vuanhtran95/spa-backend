<?php

namespace App\Repositories;

interface ReviewRepositoryInterface
{
    public function create(array $attributes = []);

    public function save($data, $is_update, $id = null);

}

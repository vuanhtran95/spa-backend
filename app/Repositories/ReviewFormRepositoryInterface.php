<?php

namespace App\Repositories;

interface ReviewFormRepositoryInterface
{
    public function create(array $attributes = []);

    public function save($data, $is_update, $id = null);

}

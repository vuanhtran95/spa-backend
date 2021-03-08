<?php

namespace App\Repositories;

interface TaskHistoryRepositoryInterface
{
    public function create(array $attributes = []);

    public function save($data, $is_update);

    public function remove($id);
}

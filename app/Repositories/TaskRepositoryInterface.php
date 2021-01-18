<?php

namespace App\Repositories;

interface TaskRepositoryInterface
{
    public function create(array $attributes = []);

    public function save($data, $is_update);

    public function delete($id);
}

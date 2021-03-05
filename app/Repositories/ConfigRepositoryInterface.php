<?php

namespace App\Repositories;

interface ConfigRepositoryInterface
{
    public function create(array $attributes = []);

    public function get(array $condition = []);
    
    public function save($data, $is_update);

    public function remove($id);
}

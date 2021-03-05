<?php

namespace App\Repositories;

interface JudgementRepositoryInterface
{
    public function create(array $attributes = []);

    public function get(array $condition = []);
    
    public function save($data, $is_update);

    public function remove($id);
}

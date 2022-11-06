<?php

namespace App\Repositories;

interface EmployeeRepositoryInterface
{
    public function create(array $attributes = []);

    public function get(array $condition = []);

    public function getOneBy($by, $value, $config);

    public function update($id, array $attributes = []);

    public function delete($id);

    public function save($data, $is_update, $id = null);
}

<?php

namespace App\Repositories;

interface ServiceCategoryRepositoryInterface
{
    public function create(array $attributes = []);

    public function save($data, $is_update, $id = null);

    public function get();

    public function getOneBy($by, $value);

    public function update($id, array $attributes = []);

    public function delete($id);
}

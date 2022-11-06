<?php

namespace App\Repositories;

interface CustomerRepositoryInterface
{
    public function create(array $attributes = []);

    public function get(array $condition = []);
		
    public function getRanks();

    public function getOneBy($by, $value);

    public function update($id, array $attributes = []);

    public function cashOut($id, array $attributes = []);

    public function delete($id);

    public function save($data, $is_update, $id = null);

    public function checkCashPoint($id);
}

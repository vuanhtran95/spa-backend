<?php

namespace App\Repositories;

interface   DiscountRepositoryInterface
{
    public function create(array $attributes = []);

    public function get(array $condition = []);
    
    // public function update(array $attributes = []);
    public function update($id, $data);

    public function delete($id);
}

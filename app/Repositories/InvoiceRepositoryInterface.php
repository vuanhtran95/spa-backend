<?php

namespace App\Repositories;

interface InvoiceRepositoryInterface
{
    public function create(array $attributes = []);

    public function save($data, $is_update);
}

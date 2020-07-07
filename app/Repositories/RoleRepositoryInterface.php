<?php

namespace App\Repositories;

interface RoleRepositoryInterface
{
    public function get(array $condition = []);
}

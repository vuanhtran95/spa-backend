<?php

namespace App\Repositories;

use App\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function get(array $condition = [])
    {
        return Role::all();
    }
}

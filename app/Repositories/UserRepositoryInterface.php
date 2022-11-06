<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function updatePassword($id, array $attributes = []);
}

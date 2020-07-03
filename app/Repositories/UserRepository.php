<?php

namespace App\Repositories;

use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;

class UserRepository implements UserRepositoryInterface
{

    public function create(array $attributes = [])
    {
        return $this->save($attributes, false);
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            $user = User::find($id);
        } else {
            $user = new User();
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);

        if ($user->save()) {
            return $user;
        } else {
            return false;
        }
    }

    public function get(array $condition = [])
    {
        // TODO: Implement get() method.
    }

    public function getOneBy($by, $value)
    {
        // TODO: Implement getOneBy() method.
    }

    public function update($id, array $attributes = [])
    {
        return $this->save($attributes, true, $id);
    }

    public function delete($id)
    {
        return User::destroy($id);
    }
}

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
            $user->email = $data['email'];
        }
        $user->name = $data['name'];
        $user->password = Hash::make($data['password']);
        $user->role_id = $data['role_id'];

        if ($user->save()) {
            return $user;
        } else {
            return false;
        }
    }

    public function get(array $condition = [])
    {
        return User::all();
    }

    public function getOneBy($by, $value)
    {
        return User::where($by, '=', $value)->first();
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

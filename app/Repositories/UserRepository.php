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
        $user->phone = $data['phone'];

        if ($user->save()) {
            return $user;
        } else {
            return false;
        }
    }

    public function get(array $condition = [])
    {
        if (empty($condition)) {
            return User::all();
        } else {
            $roleId = $condition['roleId'];
            $perPage = $condition['perPage'];

            return User::where('role_id', $roleId)
                ->with('role')
                ->limit($perPage)
                ->get()->toArray();
        }
    }

    public function getOneBy($by, $value)
    {
        return User::where($by, '=', $value)->with('role')->first();
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

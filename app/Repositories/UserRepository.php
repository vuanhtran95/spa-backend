<?php

namespace App\Repositories;

use App\Customer;
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
        $roleId = isset($condition['roleId']) ? $condition['roleId'] : null;
        $perPage = isset($condition['perPage']) ? $condition['perPage'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new User();

        if ($roleId) {
            $query = $query::where('role_id', $roleId);
        }

        $users = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return [
            "Data" => $users,
            "Pagination" => [
                "CurrentPage" => $page,
                "PerPage" => $perPage,
                "TotalItems" => $query->count()
            ]
        ];

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

<?php

namespace App\Repositories;

use App\Customer;
use App\User;
use Illuminate\Support\Facades\Hash;

class CustomerRepository implements CustomerRepositoryInterface
{

    public function create(array $attributes = [])
    {
        return $this->save($attributes, false);
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            $customer = Customer::find($id);
        } else {
            $customer = new Customer();
        }

        $customer->name = $data['name'];
        $customer->email = $data['email'];
        $customer->phone = $data['phone'];
        if (isset($data['points'])) {
            $customer->points = $data['points'];
        }

        if ($customer->save()) {
            return $customer;
        } else {
            return false;
        }
    }

    public function get(array $condition = [])
    {
        if (empty($condition)) {
            return Customer::all();
        } else {
            $phone = $condition['phone'];
            $perPage = $condition['perPage'];

            return Customer::where('phone', 'LIKE', $phone . '%')
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

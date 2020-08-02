<?php

namespace App\Repositories;

use App\Combo;
use App\Customer;
use App\User;
use Illuminate\Support\Facades\DB;

class ComboRepository implements ComboRepositoryInterface
{

    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, false);
            DB::commit();
            return $return;
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            // Update
            $combo = Combo::with('service')->find($id);
            if ($combo->is_active) {
                // Decrease combo
                foreach ($data as $key => $value) {
                    $combo->$key = $value;
                }
            } else {
                // Activate combo
                if ($data['is_active']) {
                    $price = ($combo->service->price * $combo->amount) / $combo->service->combo_ratio;
                    $combo->price = $price;
                    $combo->is_active = $data['is_active'];
                } else {
                    return false;
                }
            }

        } else {
            // Create Combo
            $combo = new Combo();
            foreach ($data as $key => $value) {
                $combo->$key = $value;
            }
        }


        if ($combo->save()) {
            if ($id) {
                return Combo::find($id);
            } else {
                return Combo::find($combo->id);
            }
        } else {
            return false;
        }
    }

    public function get(array $condition = [])
    {
        $service_id = isset($condition['service_id']) ? $condition['service_id'] : null;
        $customer_id = isset($condition['customer_id']) ? $condition['customer_id'] : null;

        $perPage = isset($condition['perPage']) ? $condition['perPage'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Combo();

        if ($service_id) {
            $query = $query::where('service_id', '=', $service_id);
        }
        if ($customer_id) {
            if ($service_id) {
                $query = $query->where('customer_id', '=', $customer_id);
            } else {
                $query = $query::where('customer_id', '=', $customer_id);
            }
        }

        $combos = $query->with(['service', 'customer'])
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return [
            "Data" => $combos,
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
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, true, $id);
            DB::commit();
            return $return;
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    public function delete($id)
    {
        return User::destroy($id);
    }
}

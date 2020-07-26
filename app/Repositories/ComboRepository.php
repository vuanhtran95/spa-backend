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
                    $combo->is_active = data['is_active'];
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
        if (empty($condition)) {
            return Combo::with('service')
                ->get()->toArray();
        } else {
            $service_id = $condition['service_id'];
            $customer_id = $condition['customer_id'];

            return Combo::where('service_id', '=', $service_id)
                ->where('customer_id', '=', $customer_id)
                ->with('service')
                ->get()
                ->toArray();
        }
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

<?php

namespace App\Repositories;

use App\Combo;
use App\Customer;
use App\Employee;
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
            // Activate combo
            $combo = Combo::with('service')->find($id);
            if (isset($data['is_valid'])) {
                $total_price = ($combo->service->total_price * $combo->amount) / $combo->service->combo_ratio;
                $combo->total_price = $total_price;
                $combo->is_valid = $data['is_valid'];
            }

        } else {
            $employeeId = Employee::where('user_id', $data['user_id'])->first()->toArray()['id'];
            unset($data['user_id']);

            // Create Combo
            $combo = new Combo();
            foreach ($data as $key => $value) {
                $combo->$key = $value;
            }
            $combo->employee_id = $employeeId;
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
        $serviceId = isset($condition['service_id']) ? $condition['service_id'] : null;
        $customerId = isset($condition['customer_id']) ? $condition['customer_id'] : null;
        $employee_id = isset($condition['employee_id']) ? $condition['employee_id'] : null;
        $isValid = isset($condition['is_valid']) ? $condition['is_valid'] : null;

        $perPage = isset($condition['perPage']) ? $condition['perPage'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Combo();

        if ($serviceId) {
            $query = $query->where('service_id', '=', $serviceId);
        }
        if ($customerId) {
            $query = $query->where('customer_id', '=', $customerId);
        }
        if ($employee_id) {
            $query = $query->where('employee_id', '=', $employee_id);
        }
        if ($isValid) {
            $query = $query->where('is_valid', '=', $isValid);
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
        return User::where($by, '=', $value)->first();
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

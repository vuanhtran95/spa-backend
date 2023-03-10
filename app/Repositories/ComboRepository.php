<?php

namespace App\Repositories;

use App\Combo;
use App\Customer;
use App\Employee;
use App\Helper\Translation;
use App\Order;
use App\Service;
use App\User;
use App\Variant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
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
            throw new \Exception($exception->getMessage());
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            // Approve combo
            $combo = Combo::with(['variant' => function ($query) {
                $query->with('service');
            }])->find($id);
            if ($combo->is_valid) {
                throw new \Exception(Translation::$COMBO_ALREADY_VALID);
            }

            if (isset($data['is_valid'])) {
                // Calculate total price
                $total_price = ($combo->variant->sale_price * $combo->amount) / $combo->variant->service->combo_ratio;
                $combo->is_valid = $data['is_valid'];

                // Store Price to combo in case service price change
                $combo->total_price = $total_price;
                $combo->sale_commission = $total_price * $combo->variant->service->combo_commission / 100;

                // Add Expired Date
                $now = Carbon::now();
                $combo->expiry_date = date('Y-m-d H:m:s', strtotime("+3 months", strtotime($now)));
            } else {
                throw new \Exception("Please pass is_valid value");
            }
        } else {
            // Create combo
            $userId = $data['user_id'];
            $employee = Employee::where('user_id', $userId)->first();
            unset($data['user_id']);

            // Create Combo
            $combo = new Combo();
            foreach ($data as $key => $value) {
                $combo->$key = $value;
            }
            $combo->employee_id = $employee->id;
        }

        $combo->save();
        if ($id) {
            return Combo::find($id);
        } else {
            return Combo::find($combo->id);
        }
    }

    public function get(array $condition = [])
    {
        $variantId = isset($condition['variant_id']) ? $condition['variant_id'] : null;

        $perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Combo();

        if ($variantId) {
            $query = $query->where('variant_id', '=', $variantId);
        }

        $combos = $query->with(['variant' => function ($vQuery) {
            $vQuery->with(['service' => function ($sQuery) {
                $sQuery->with('serviceCategory');
            }]);
        }, 'customer', 'orders' => function ($query) {
            $query->whereHas('intake', function ($query) {
                $query->where('is_valid', 1);
            });
        }])
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
        return Combo::where($by, $value)->with(['orders' => function ($query) {
            $query->whereHas('intake', function ($query) {
                $query->where('is_valid', 1);
            });
        }, 'variant'])->first();
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
        $combo = Combo::find($id);
        if ($combo !== null) {
            if ($combo->is_valid) {
                throw new \Exception('Can not delete valid combo');
            } else {
                DB::beginTransaction();
                try {
                    $destroy = Combo::destroy($id);
                    DB::commit();
                    return $destroy;
                } catch (\Exception $exception) {
                    DB::rollBack();
                    throw $exception;
                }
            }
        } else {
            throw new \Exception('No Combo Found');
        }
    }
}

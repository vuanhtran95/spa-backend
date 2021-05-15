<?php

namespace App\Repositories;

use App\Discount;
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

class DiscountRepository implements DiscountRepositoryInterface
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

    public function save($data, $is_update = true, $id = null)
    {
        if ($is_update && is_array($data)) {
            foreach ($data as $discount) {
                if(empty($discount['id'])) break;
                $update_discount = Discount::find($discount['id']);
                foreach ($discount as $property => $value) {
                    $update_discount->$property = $value;
                }
                $update_discount->save();
            }
            return true;
        }
        if (!$is_update && is_array($data)) {
            $result = [];
            foreach ($data as $discount_data) {
                // Need to create order
                $new_discount = new Discount();
                foreach ($discount_data as $property => $value) {
                    if ($property === 'from' || $property === 'to') {
                        $new_discount->$property = date('Y-m-d H:m:s', strtotime($value));
                    } else {
                        $new_discount->$property = $value;
                    }
                }
                $new_discount->save();
            }
            return true;
        }
        throw new \Exception('No Discount Created');
    }

    public function get(array $condition = [])
    {
        $perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Discount();
        $discounts = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return [
            "Data" => $discounts,
            "Pagination" => [
                "CurrentPage" => $page,
                "PerPage" => $perPage,
                "TotalItems" => $query->count()
            ]
        ];
    }

    public function update(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, true);
            DB::commit();
            return $return;
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    public function delete($id)
    {
        $discount = Discount::find($id);
        if ($discount !== null) {
            if ($discount->is_valid) {
                throw new \Exception('Can not delete valid discount');
            } else {
                DB::beginTransaction();
                try {
                    $destroy = Discount::destroy($id);
                    DB::commit();
                    return $destroy;
                } catch (\Exception $exception) {
                    DB::rollBack();
                    throw $exception;
                }
            }
        } else {
            throw new \Exception('No Discount Found');
        }
    }
}

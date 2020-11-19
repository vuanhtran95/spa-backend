<?php

namespace App\Repositories;

use App\Package;
use App\Customer;
use App\Employee;
use App\Combo;
use App\Helper\Translation;
use App\Order;
use App\Service;
use App\User;
use App\Variant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class PackageRepository implements PackageRepositoryInterface
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
            //Approve package

            $package = Package::with(['combos' => function ($comboQuery) {
                $comboQuery->with(['variant' => function ($variantQuery) {
                    $variantQuery->with(['service' => function ($serviceQuery) {
                        $serviceQuery->with('serviceCategory');
                    }]);
                }]);
            }])->find($id);

            if ($package->is_valid) {
                throw new \Exception(Translation::$COMBO_ALREADY_VALID);
            }
            if(isset($data['is_valid'])) {
                $total_price = 0;
                $sale_commission = 0;
                $combos = $package->combos->toArray();
                foreach ($combos as $key => $combo) {
                    if(!$combo['is_promotion_combo']) {
                        $sale_commission_rate = $combo['variant']['service']['combo_commission'] / 100;
                        $sale_commission += $sale_commission_rate*$combo['total_price'];
                        $total_price +=  $combo['total_price'];
                    }
                }
                $package->total_price = $total_price;
                $package->sale_commission = $sale_commission;
                $package->is_valid = $data['is_valid'];
            }
            $package->save();
            return $package;
        } else {
            // Create package
            

            $userId = $data['user_id'];
            $employee = Employee::where('user_id', $userId)->first();
            unset($data['user_id']);
            $combos = $data['combos'];
            unset($data['combos']);
            // Create Package
            $package = new Package();
            foreach ($data as $key => $value) {
                $package->$key = $value;
            }
            
            $package->employee_id = $employee->id;
            if ($package->save()) {
                foreach ($combos as $key => $combo) {
                    $combos[$key]['package_id'] = $package->id;
                    $combos[$key]['created_at'] = Carbon::now();
                    $combos[$key]['updated_at'] = Carbon::now();
                    $combos[$key]['number_used'] = 0;
                    $variant_price = Variant::find($combos[$key]['variant_id'])->price;
                    $combos[$key]['total_price'] = $combos[$key]['amount']*$variant_price;
                }

                Combo::insert($combos);
                // Return Intake with order
                return Package::with('combos')->find($package->id);
            } else {
                return false;
            }
        }
    }

    public function get(array $condition = [])
    {
        $variantId = isset($condition['variant_id']) ? $condition['variant_id'] : null;
        $customerId = isset($condition['customer_id']) ? $condition['customer_id'] : null;
        $employee_id = isset($condition['employee_id']) ? $condition['employee_id'] : null;
        $isValid = isset($condition['is_valid']) ? $condition['is_valid'] : null;

        $perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Package();

        if ($variantId) {
            $query = $query->where('variant_id', '=', $variantId);
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
        // 'customer', 'orders' => function ($query) {
        //     $query->whereHas('intake', function ($query) {
        //         $query->where('is_valid', 1);
        //     });
        // }
        $package = $query->with(['combos' => function ($vQuery) {
            $vQuery->with(['orders' => function ($query) {
                $query->whereHas('intake', function ($query) {
                    $query->where('is_valid', 1);
                });
            }]);
            $vQuery->with(['variant' => function ($sQuery) {
                $sQuery->with(['service' => function ($cQuery) {
                    $cQuery->with('serviceCategory');
                }]);
            }]);
        },'customer'])
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return [
            "Data" => $package,
            "Pagination" => [
                "CurrentPage" => $page,
                "PerPage" => $perPage,
                "TotalItems" => $query->count()
            ]
        ];
    }

    public function getOneBy($by, $value)
    {
        // return Combo::where($by, $value)->with(['orders' => function ($query) {
        //     $query->whereHas('intake', function ($query) {
        //         $query->where('is_valid', 1);
        //     });
        // }, 'variant'])->first();
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

<?php

namespace App\Repositories;

use App\Customer;
use App\Employee;
use App\Order;
use App\Review;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;

class EmployeeRepository implements EmployeeRepositoryInterface
{

    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, false);
            DB::commit();
            return $return;
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                DB::rollBack();
                return 1062;
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
//            $user = User::find($id);
        } else {
            $user = new User();
            $user->email = $data['username'];
            $user->password = Hash::make($data['password']);

            if ($user->save()) {
                $employee = new Employee();
                $employee->name = $data['name'];
                $employee->role_id = $data['role_id'];
                $employee->phone = $data['phone'];
                $employee->gender = $data['gender'];
                $employee->user_id = $user->id;

                if ($employee->save()) {
                    return Employee::with('user')->find($employee->id);
                } else {
                    throw new Exception("Error when storing employee");
                }
            } else {
                throw new Exception("Error when storing user");
            }
        }
    }

    public function get(array $condition = [])
    {
        $roleId = isset($condition['roleId']) ? $condition['roleId'] : null;
        $perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Employee();

        if ($roleId) {
            $query = $query::where('role_id', $roleId);
        }

        // With commissions
        $query->withCount(['order AS working_commission' => function($query) {
            $query->whereMonth('updated_at', Carbon::now()->month)
                ->select(DB::raw("SUM(working_commission)"));
        }]);

        $query->withCount(['order AS working_commission_prev' => function($query) {
            $query->whereMonth('updated_at', Carbon::now()->month - 1)
                ->select(DB::raw("SUM(working_commission)"));
        }]);

        $query->withCount(['combos AS sale_commission' => function($query) {
            $query->whereMonth('updated_at', Carbon::now()->month)
                ->select(DB::raw("SUM(sale_commission)"));
        }]);

        $query->withCount(['combos AS sale_commission_prev' => function($query) {
            $query->whereMonth('updated_at', Carbon::now()->month - 1)
                ->select(DB::raw("SUM(sale_commission)"));
        }]);

        // With points
        $query->withCount(['order AS attitude_point' => function($query){
            $query->withCount(['review AS attitude_point' => function($subQuery) {
                $subQuery->select(DB::raw("SUM(attitude)"));
            }]);
        }]);
        $query->withCount(['order AS skill_point' => function($query){
            $query->withCount(['review AS skill_point' => function($subQuery) {
                $subQuery->select(DB::raw("SUM(skill)"));
            }]);
        }]);

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

    public function getOneBy($by, $value, $config)
    {
        $query = Employee::where($by, '=', $value)->with('role');
        if (isset($config['show_commission']) && $config['show_commission'] == 1) {
            $query->withCount(['order AS working_commission' => function($query) {
                $query->whereMonth('updated_at', Carbon::now()->month)
                    ->select(DB::raw("SUM(working_commission)"));
            }]);

            $query->withCount(['order AS working_commission_prev' => function($query) {
                $query->whereMonth('updated_at', Carbon::now()->month - 1)
                    ->select(DB::raw("SUM(working_commission)"));
            }]);

            $query->withCount(['combos AS sale_commission' => function($query) {
                $query->whereMonth('updated_at', Carbon::now()->month)
                    ->select(DB::raw("SUM(sale_commission)"));
            }]);

            $query->withCount(['combos AS sale_commission_prev' => function($query) {
                $query->whereMonth('updated_at', Carbon::now()->month - 1)
                    ->select(DB::raw("SUM(sale_commission)"));
            }]);
        }

        if (isset($config['show_point']) && $config['show_point'] == 1) {
//            $query->withCount(['order AS attitude_point' => function($query){
//                $query->with(['review AS attitude' => function($subQuery) {
//                    $subQuery->select(DB::raw("SUM(attitude)"));
//                }]);
//            }]);
//            $query->withCount(['order AS skill_point' => function($query){
//                $query->withCount(['review AS attitude' => function($subQuery) {
//                    $subQuery->select(DB::raw("SUM(skill)"));
//                }]);
//            }]);

            // Attitude
            $employeeId = 9;
            $attitude_point = Review::whereHas('order', function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })->avg('attitude');

            // Skill
            $employeeId = 9;
            $skill_point = Review::whereHas('order', function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })->avg('skill');

        }
        $employee = $query->first();
        $employee->attitude_point = $attitude_point;
        $employee->skill_point = $skill_point;
        return $employee;
    }


    // Not working now
    public function update($id, array $attributes = [])
    {
        return $this->save($attributes, true, $id);
    }

    // Not working now
    public function delete($id)
    {
//        return Employee::destroy($id);
    }
}

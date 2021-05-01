<?php

namespace App\Repositories;

use App\Helper\Translation;
use Illuminate\Support\Facades\DB;
use App\Judgement;
use App\Employee;

class JudgementRepository implements JudgementRepositoryInterface
{
    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $entity = $this->save($attributes, false);

            DB::commit();
            return $entity;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function get(array $condition = [])
    {
        $employeeId = isset($condition['employee_id']) ? $condition['employee_id'] : null;

        $fromDate = isset($condition['from_date']) ? $condition['from_date'] : null;
        $toDate = isset($condition['to_date']) ? $condition['to_date'] : null;

        $query = new Judgement();

        if ($employeeId) {
            $query = $query->where('employee_id', '=', $employeeId);
        }

        if ($fromDate) {
            $query = $query->where('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query = $query->where('created_at', '<=', $toDate);
        }

        $judgements = $query->orderBy('id', 'desc')
                            ->get()
                            ->toArray();
        return [
            "Data" => $judgements,
            "Pagination" => [
                "TotalItems" => $query->count()
            ]
        ];
    }

    public function save($data, $is_update = true, $id = null)
    {
        $judgement = null;

        if ($is_update && !is_array($data)) {
            $judgement = Judgement::find($id);
            foreach ($data as $property => $value) {
                $judgement->$property = $value;
            }
            $judgement->save();
            return $judgement;
        }
        if (!$is_update && is_array($data)) {
            $result = [];
            foreach ($data as $judgement) {
                $employee = Employee::find($judgement['employee_id']);
                if (empty($employee)) {
                    continue;
                }
                // Need to create order
                $judgementData = new Judgement();
                $judgementData->employee_id = $judgement['employee_id'];
                $judgementData->reason = $judgement['reason'];
                $judgementData->point = $judgement['point'];
                $judgementData->save();
                array_push($result, $judgement);
            }
            return $result;
        }
        if (empty($judgement)) {
            throw new \Exception('Unable to find the task');
        }
    }

    public function remove($id)
    {
        $data = Judgement::find($id);

        if (empty($data)) {
            throw new \Exception('Unable to find the task history.');
        }

        return $data->delete();
    }
}

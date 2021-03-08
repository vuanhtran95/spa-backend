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
    
        $perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

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

        $judgements = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return [
            "Data" => $judgements,
            "Pagination" => [
                "CurrentPage" => $page,
                "PerPage" => $perPage,
                "TotalItems" => $query->count()
            ]
        ];
    }

    public function save($data, $is_update = true, $id = null)
    {
        $task_history = null;

        if (!$is_update) {
            $task_history = new Judgement();
        } else {
            $task_history = Judgement::find($id);
        }

        $task = Task::find($data['task_id']);

        if (empty($task)) {
            throw new \Exception('Unable to find the task');
        }

        $employee = Employee::find($data['employee_id']);

        if (empty($employee)) {
            throw new \Exception('Unable to find the employee');
        }

        foreach ($data as $property => $value) {
            $task_history->$property = $value;
        }

        $task_history->save();

        return $task_history;
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

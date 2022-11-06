<?php

namespace App\Repositories;

use App\Helper\Translation;
use Illuminate\Support\Facades\DB;
use App\TaskHistory;
use App\Task;
use App\Employee;

class TaskHistoryRepository implements TaskHistoryRepositoryInterface
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

    public function save($data, $is_update = true, $id = null)
    {
        $task_history = null;

        if (!$is_update) {
            $task_history = new TaskHistory();
        } else {
            $task_history = TaskHistory::find($id);
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
        $task_history = TaskHistory::find($id);

        if (empty($task_history)) {
            throw new \Exception('Unable to find the task history.');
        }

        return $task_history->delete();
    }
}

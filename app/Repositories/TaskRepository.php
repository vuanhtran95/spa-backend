<?php

namespace App\Repositories;

use App\Helper\Translation;
use Illuminate\Support\Facades\DB;
use App\Employee;
use App\Task;

class TaskRepository implements TaskRepositoryInterface
{
    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $entity = $this->save([
                'name' => $attributes['name']
            ], false);

            DB::commit();
            return $entity;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function save($data, $is_update = true, $id = null)
    {
        $task = null;

        if (!$is_update) {
            $task = new Task();
        } else {
            $task = Task::find($id);
        }

        foreach ($data as $property => $value) {
            $task->$property = $value;
        }

        $task->save();

        return $task;
    }
}

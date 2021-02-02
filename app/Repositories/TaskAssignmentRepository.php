<?php

namespace App\Repositories;

use App\Helper\Translation;
use Illuminate\Support\Facades\DB;
use App\Employee;
use App\TaskAssignment;
use Carbon\Carbon;

class TaskAssignmentRepository implements TaskAssignmentRepositoryInterface
{
    public function get($params)
    {
        $from = Carbon::createFromFormat('Y-m-d',  $params['from']);
        $to = Carbon::createFromFormat('Y-m-d',  $params['to']);

        $period = [
            $from->startOfDay()->toDateString(),
            $to->endOfDay()->toDateString()
        ];

        return TaskAssignment::whereBetween('updated_at', $period)->get();
    }

    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {

            $schedule = $attributes['schedule'] ?? null;
            if (empty($schedule) || !is_array($schedule)) {
                throw new \Exception('Schedule cannot be empty');
            }

            if (!empty($schedule)) {
                foreach ($schedule as $scheduleData) {
                    $condition = [
                        ${strtolower($scheduleData['day'])} => true,
                        'begin_time' => $scheduleData['begin_time'],
                        'end_time' => $scheduleData['end_time'],
                        'employee_id' => $scheduleData['employee_id']
                    ];
                    
                    if (!TaskAssignment::where($condition)->exists()) {
                        $taskAssignment = new TaskAssignment();
                        $taskAssignment->title = $attributes['name'];
                        $taskAssignment->task_id = $attributes['task_id'];
                        $day = strtolower($scheduleData['day']);
                        $taskAssignment->{$day} = true;
                        $taskAssignment->employee_id = $scheduleData['employee_id'];
                        $taskAssignment->begin_time = $scheduleData['begin_time'];
                        $taskAssignment->end_time = $scheduleData['end_time'];
                        $taskAssignment->save();
                    } else {
                        continue;
                    }
                }
                DB::commit();

                return true;
            }

            return false;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function save($data, $task_assignment_id = null)
    {
        $taskAssignment = null;
        $emp_id = !empty($data['emp_id']) ? $data['emp_id'] : null;

        if (!empty($emp_id)) {
            $taskAssignment = TaskAssignment::where('employee_id', $emp_id)->get();
        } else {
            $taskAssignment = TaskAssignment::find($task_assignment_id);
        }

        if (empty($taskAssignment)) {
            throw new \Exception('Unable to find the assignment');
        }

        try {
            DB::beginTransaction();
            foreach ($data as $prop => $val) {
                if ('day' === $prop) {
                    $taskAssignment->{strtolower($val)} = true;
                } else {
                    $taskAssignment->$prop = $val;
                }
            }
    
            DB::commit();
            return $taskAssignment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function delete($task_assignment_id, $emp_id = null)
    {
        $taskAssignment = null;
        $emp_id = !empty($data['emp_id']) ? $data['emp_id'] : null;

        if (!empty($emp_id)) {
            $taskAssignment = TaskAssignment::where('employee_id', $emp_id)->get();
        } else {
            $taskAssignment = TaskAssignment::find($task_assignment_id);
        }

        if (empty($taskAssignment)) {
            throw new \Exception('Unable to find the task assignment.');
        }

        $taskAssignment->delete();
    }
}

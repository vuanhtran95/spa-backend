<?php

namespace App\Repositories;

use App\Helper\Translation;
use Illuminate\Support\Facades\DB;
use App\Employee;
use App\TaskAssignment;
use Carbon\Carbon;

class TaskAssignmentRepository implements TaskAssignmentRepositoryInterface
{
    public function get()
    {
        $task_assignments = TaskAssignment::with(['employee'])->where('type', '=', 'chore')->get();
        $days_of_week = [
            'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'
        ];
        $result = array();
        foreach ($task_assignments as $task_assignment) {
            if (empty($result[$task_assignment['task_id']])) {
                $result[$task_assignment['task_id']]['name'] =  $task_assignment->title;
                $result[$task_assignment['task_id']]['id'] =  $task_assignment->task_id;
                foreach ($days_of_week as $day) {
                    $result[$task_assignment['task_id']][$day] = [];
                }
            }
            foreach ($days_of_week as $day) {
                if ($task_assignment->$day) {
                    $result[$task_assignment['task_id']][$day][] = [
                                'employee' => $task_assignment->employee,
                                'employee_id' => $task_assignment->employee_id,
                                'id' => $task_assignment->id
                            ];
                }
            }
        }
        return array_values($result);
    }

    public function getReminder()
    {
        $task_assignments = TaskAssignment::with(['employee'])->where('type', '=', 'reminder')->get()->toArray();
        return $task_assignments;
    }

    public function createReminder(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $new_reminder = new TaskAssignment();
            foreach ($attributes as $property => $value) {
                $new_reminder->$property = $value;
            }
            $new_reminder->type = 'reminder';
            $new_reminder->save();
            DB::commit();
            $result = TaskAssignment::with(['employee'])->where('id', '=', $new_reminder->id)->first();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
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
                        'task_id'=> $attributes['task_id'],
                        strtolower($scheduleData['day']) => true,
                        'employee_id' => $scheduleData['employee_id']
                    ];
                    
                    if (!TaskAssignment::where($condition)->exists()) {
                        $taskAssignment = new TaskAssignment();
                        $taskAssignment->title = $attributes['name'];
                        $taskAssignment->task_id = $attributes['task_id'];
                        $day = strtolower($scheduleData['day']);
                        $taskAssignment->{$day} = true;
                        $taskAssignment->employee_id = $scheduleData['employee_id'];
                        $taskAssignment->type = 'chore';
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

    public function delete($task_assignment_id)
    {
        $taskAssignment = TaskAssignment::find($task_assignment_id);

        if (empty($taskAssignment)) {
            throw new \Exception('Unable to find the task assignment.');
        }

        $taskAssignment->delete();
    }
}

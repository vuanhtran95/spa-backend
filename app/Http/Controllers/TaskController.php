<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\Translation;
use App\Repositories\TaskRepository;
use App\Repositories\TaskAssignmentRepository;
use App\Http\HttpResponse;
use Illuminate\Http\Response as Response;

class TaskController extends Controller
{
    private $taskRepository;

    private $taskAssignmentRepository;

    public function __construct(
        TaskRepository $taskRepository, 
        TaskAssignmentRepository $taskAssignmentRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->taskAssignmentRepository = $taskAssignmentRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();

        Validator::make($params, [
            'name' => 'required',
            'title' => [
                'schedule',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) {
                        $fail($attribute.' is invalid.');
                    }
                },
            ],
        ]);

        try {
            // 1. Create task
            $task = $this->taskRepository->create($params);
            // 2. Create task assignments
            $params['task_id'] = $task->id;
            $taskAssignment = $this->taskAssignmentRepository->create($params);

            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$TASK_CREATED, $task);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request, $task_id)
    {
        $params = $request->all();

        $validatedData = $request->validate([
            'name' => 'required',
        ]);

        try {
            // Update Task
            $task = $this->taskRepository->save($validatedData, true, $task_id);

            return HttpResponse::toJson(true, Response::HTTP_UPDATED, Translation::$TASK_UPDATED, $task);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function updateTaskAssignment(Request $request, $task_assignment_id)
    {
        $params = $request->all();

        $validatedData = $request->validate([
            'emp_id' => 'required|integer',
            'day' => 'required',
            'begin_time' => 'required',
            'end_time' => 'required'
        ]);

        try {
            // Update Task assignment
            $taskAssignment = $this->taskAssignmentRepository->save($validatedData, $task_assignment_id);

            return HttpResponse::toJson(true, Response::HTTP_UPDATED, Translation::$TASK_ASSIGNMENT_UPDATED, $task);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function deleteTaskAssignment(Request $request, $task_assignment_id)
    {
        $params = $request->all();

        $validatedData = $request->validate([
            'emp_id' => 'required|integer'
        ]);

        try {
            $this->taskAssignmentRepository->delete($id, $validatedData['emp_id'] ?? null);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$TASK_ASSIGNMENT_DELETED);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }  
}

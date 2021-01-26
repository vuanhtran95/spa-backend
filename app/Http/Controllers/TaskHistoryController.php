<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\Translation;
use App\Repositories\TaskHistoryRepository;
use App\Http\HttpResponse;
use Illuminate\Http\Response as Response;

class TaskHistoryController extends Controller
{
    private $taskHistoryRepository;

    public function __construct(TaskHistoryRepository $taskHistoryRepository)
    {
        $this->taskHistoryRepository = $taskHistoryRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();

        Validator::make($params, [
            'task_id' => 'required|integer',
            'employee_id' => 'required|integer',
            'point' => 'required|integer'
        ]);

        try {
            // Create task history
            $taskHistory = $this->taskHistoryRepository->create($params);

            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$TASK_HISTORY_CREATED, $taskHistory);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request, $task_history_id)
    {
        $params = $request->all();

        if (empty($task_history_id)) {
            throw new \Exception('Unable to find task history.');
        }

        try {
            // Update task history
            $taskHistory = $this->taskHistoryRepository->save($params, true, $task_history_id);

            return HttpResponse::toJson(true, Response::HTTP_UPDATED, Translation::$TASK_HISTORY_UPDATED, $taskHistory);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function remove(Request $request, $task_history_id)
    {
        $params = $request->all();

        try {
            $this->taskHistoryRepository->remove($task_history_id);
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$TASK_HISTORY_DELETED);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }
}

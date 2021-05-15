<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\Translation;
use App\Repositories\JudgementRepository;
use App\Http\HttpResponse;
use Illuminate\Http\Response as Response;

class JudgementController extends Controller
{
    private $JudgementRepository;

    public function __construct(JudgementRepository $JudgementRepository)
    {
        $this->JudgementRepository = $JudgementRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();

        Validator::make($params, [
            'employee_id' => 'required|integer',
            'point' => 'required|integer'
        ]);

        try {
            // Create task history
            $judgement = $this->JudgementRepository->create($params);
            $judgements = $this->JudgementRepository->get($params);

            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$JUDGEMENT_CREATED, $judgement);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $orders = $this->JudgementRepository->get($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$JUDGEMENT_GET,
                $orders['Data'],
                $orders['Pagination']
            );
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request, $judgement_id)
    {
        $params = $request->all();

        if (empty($judgement_id)) {
            throw new \Exception('Unable to find judgement.');
        }

        try {
            // Update task history
            $judgement = $this->JudgementRepository->save($params, true, $judgement_id);

            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$JUDGEMENT_UPDATED, $judgement);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function remove(Request $request, $judgement_id)
    {
        $params = $request->all();

        try {
            $deleted = $this->JudgementRepository->delete($judgement_id);
            $message = $deleted ? Translation::$JUDGEMENT_DELETED : Translation::$DELETE_NOTHING;

            return HttpResponse::toJson(true, Response::HTTP_OK, $message);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }
}

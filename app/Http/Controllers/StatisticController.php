<?php

namespace App\Http\Controllers;

use App\Helper\Translation;
use App\Http\HttpResponse;
use App\Repositories\StatisticRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StatisticController extends Controller
{
    private $statisticRepository;

    public function __construct(StatisticRepositoryInterface $statisticRepository)
    {
        $this->statisticRepository = $statisticRepository;
    }

    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $statistic = $this->statisticRepository->get($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$GET_STATISTIC_SUCCESS,
                $statistic
            );
        } catch (\Exception $e) {
            return HttpResponse::toJson(
                false,
                Response::HTTP_CONFLICT,
                $e->getMessage()
            );
        }
    }
}

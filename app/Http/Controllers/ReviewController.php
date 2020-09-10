<?php

namespace App\Http\Controllers;

use App\Helper\Translation;
use App\Http\HttpResponse;
use App\Repositories\ReviewRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;

class ReviewController extends Controller
{
    private $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();

        try {
            $this->reviewRepository->create($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_CREATED,
                Translation::$CREATED
            );
        } catch (\Exception $e) {
            return HttpResponse::toJson(
                false,
                Response::HTTP_CONFLICT,
                Translation::$UPDATE_FAILURE
            );
        }
    }
}

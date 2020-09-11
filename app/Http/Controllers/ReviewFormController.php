<?php

namespace App\Http\Controllers;

use App\Helper\Translation;
use App\Http\HttpResponse;
use App\Repositories\ReviewFormRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;

class ReviewFormController extends Controller
{
    private $reviewFormRepository;

    public function __construct(ReviewFormRepositoryInterface $reviewFormRepository)
    {
        $this->reviewFormRepository = $reviewFormRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();

        try {
            $this->reviewFormRepository->create($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_CREATED,
                Translation::$CREATED
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

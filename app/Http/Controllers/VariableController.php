<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\Translation;
use App\Repositories\VariableRepository;
use App\Http\HttpResponse;
use Illuminate\Http\Response as Response;

class VariableController extends Controller
{
    private $VariableRepository;

    public function __construct(VariableRepository $VariableRepository)
    {
        $this->VariableRepository = $VariableRepository;
    }


    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $variables = $this->VariableRepository->get();
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$DISCOUNT_GET,
                $variables['Data'],
                $variables['Pagination']
            );
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $params = $request->all();

        if (empty($params)) {
            throw new \Exception('Unable to find discounts.');
        }

        try {
            // Update task history
            $variable = $this->VariableRepository->update($params);

            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DISCOUNT_UPDATED, $discount);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }
}

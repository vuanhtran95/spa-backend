<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\Translation;
use App\Repositories\DiscountRepository;
use App\Http\HttpResponse;
use Illuminate\Http\Response as Response;

class DiscountController extends Controller
{
    private $DiscountRepository;

    public function __construct(DiscountRepository $DiscountRepository)
    {
        $this->DiscountRepository = $DiscountRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();

        try {
            // Create task history
            $discount = $this->DiscountRepository->create($params);
            $discounts = $this->DiscountRepository->get($params);

            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$DISCOUNT_CREATED, $discounts);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $orders = $this->DiscountRepository->get($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$DISCOUNT_GET,
                $orders['Data'],
                $orders['Pagination']
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
            $discount = $this->DiscountRepository->update($params);

            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$DISCOUNT_UPDATED, $discount);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function remove(Request $request, $discount_id)
    {
        $params = $request->all();

        try {
            $deleted = $this->DiscountRepository->delete($discount_id);
            $message = $deleted ? Translation::$DISCOUNT_DELETED : Translation::$DELETE_NOTHING;

            return HttpResponse::toJson(true, Response::HTTP_OK, $message);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }
}

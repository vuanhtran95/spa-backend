<?php

namespace App\Http\Controllers;

use App\Helper\Translation;
use App\Http\HttpResponse;
use App\Repositories\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;

class OrderController extends Controller
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function get(Request $request)
    {
        $params = $request->all();

        $orders = $this->orderRepository->get($params);

        if (!empty($orders['Data'])) {
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$GET_LIST_SUCCESS,
                $orders['Data'],
                $orders['Pagination']
            );
        } else {
            return HttpResponse::toJson(false, Response::HTTP_NOT_FOUND, Translation::$GET_LIST_EMPTY);
        }
    }

    public function update(Request $request, $id) {
        $params = $request->all();
        try {
            $order = $this->orderRepository->update($id, $params);
            if ($order) {
                return HttpResponse::toJson(true,Response::HTTP_OK, Translation::$UPDATE_SUCCESS, $order);
            } else {
                //TODO: Need to improve
                return HttpResponse::toJson(false, Response::HTTP_BAD_REQUEST, Translation::$SYSTEM_ERROR);
            }
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, Translation::$UPDATE_FAILURE);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Repositories\ProductLogRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\HttpResponse;
use Illuminate\Http\Response as Response;
use App\Helper\Translation;
use Exception;

class ProductLogsController extends Controller
{
    private $productLogRepository;

    public function __construct(ProductLogRepositoryInterface $productLogRepository)
    {
        $this->productLogRepository = $productLogRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        $employee_id = Employee::where('user_id', $request->user()->id)->first()->toArray()['id'];
        $params['created_by'] = $employee_id;
        try {
            $product_log = $this->productLogRepository->create($params);
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$CREATED, $product_log);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function get(Request $request)
    {
        $params = $request->all();
        try {
            $product_logs = $this->productLogRepository->get($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$GET_LIST_SUCCESS,
                $product_logs['Data'],
            $product_logs['Pagination']);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }
}

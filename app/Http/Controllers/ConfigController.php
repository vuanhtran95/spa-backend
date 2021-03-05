<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\Translation;
use App\Repositories\ConfigRepository;
use App\Http\HttpResponse;
use Illuminate\Http\Response as Response;

class ConfigController extends Controller
{
    private $ConfigRepository;

    public function __construct(ConfigRepository $ConfigRepository)
    {
        $this->ConfigRepository = $ConfigRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        try {
            // Create task history
            $create_new = $this->ConfigRepository->create($params);
            $return_data = $this->ConfigRepository->get();

            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$CREATED, $return_data);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function get(Request $request)
    {
        $params = $request->all();

        try {
            $orders = $this->ConfigRepository->get($params);
            return HttpResponse::toJson(
                true,
                Response::HTTP_OK,
                Translation::$GET,
                $orders['Data'],
                $orders['Pagination']
            );
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $params = $request->all();

        if (empty($id)) {
            throw new \Exception('Unable to find entity.');
        }

        try {
            // Update task history
            $save_request = $this->ConfigRepository->save($params, true, $id);

            return HttpResponse::toJson(true, Response::HTTP_UPDATED, Translation::$UPDATED, $save_request);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function remove(Request $request, $id)
    {
        $params = $request->all();

        try {
            $deleted = $this->ConfigRepository->delete($id);
            $message = $deleted ? Translation::$DELETED : Translation::$DELETE_NOTHING;

            return HttpResponse::toJson(true, Response::HTTP_OK, $message);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }
}

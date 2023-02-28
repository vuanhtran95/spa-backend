<?php

namespace App\Repositories;

use App\Constants\ProductLog as ConstantsProductLog;
use Illuminate\Support\Facades\DB;
use App\Repositories\ProductLogRepositoryInterface;
use App\ProductLog;
use App\Variant;
use App\Employee;

class ProductLogRepository implements ProductLogRepositoryInterface
{
	public function handle_stock_up(ProductLog $product_log) {
		$variant = Variant::find($product_log->variant_id);
		if ($variant) {
			$variant->sale_price = $product_log->sale_price;
			$variant->price = $product_log->price;
			$variant->stock = $variant->stock + $product_log->amount;
			$variant->save();
		}
		return true;
	}

	public function handle_use_product($product_log) {
		$variant = Variant::find($product_log->variant_id);
		if($variant) {
			$new_amount = $variant->stock - $product_log->amount;
			$variant->stock = $new_amount < 0 ? 0 : $new_amount;
			$variant->save();
		}
		return true;
	}

	public function handle_sell_product($product_log) {
		$variant = Variant::find($product_log->variant_id);
		if($variant) {
			$new_amount = $variant->stock - $product_log->amount;
			$variant->stock = $new_amount < 0 ? 0 : $new_amount;
			$variant->save();
		}
		return true;
	}

	public function save($data, $is_update = true, $id = null)
	{
		$product_log = null;
		$employee_id = Employee::where('user_id', $data['user_id'])->first()->toArray()['id'];
		unset($data['user_id']);

		if ($is_update) {
		} else {
			$product_log= new ProductLog();
			$data['created_by'] = $employee_id;
		}

		foreach ($data as $property => $value) {
			$product_log->$property = $value;
		}
		$product_log->save();

		if($product_log) {
			$type = $product_log->type;
			switch($type) {
				case ConstantsProductLog::STOCK_UP:
					$this->handle_stock_up($product_log);
					break;
				case ConstantsProductLog::USE:
					$this->handle_use_product($product_log);
					break;
				case ConstantsProductLog::SELL:
					$this->handle_sell_product($product_log);
					break;
				default:
					break;
			}
		}

		return $product_log;
	}

	public function create(array $attributes = [])
	{
		DB::beginTransaction();
		try {
			$entity = $this->save($attributes, false);
			DB::commit();
			return $entity;
		} catch (\Exception $exception) {
			DB::rollBack();
			throw new \Exception($exception->getMessage());
		}
	}



	public function get(array $req_query = [])
	{
		$perPage = isset($req_query['per_page']) ? $req_query['per_page'] : 10;
		$page = isset($condition['page']) ? $condition['page'] : 1;
		$product_logs = ProductLog::query()
			->type($req_query)
			->with(['customer', 'variant' => function($vQuery) {
				$vQuery->with(['service' => function($sQuery) {
					$sQuery->with(['serviceCategory']);
			}]);}, 'created_by'])
			->limit($perPage)
		->paginate($perPage, ['*'], 'page', $page);
		return [
			"Data" => $product_logs->items(),
			"Pagination" => [
				"CurrentPage" => $page,
				"PerPage" => $perPage,
				"TotalItems" => $product_logs->total()
			]
		];
	}
}

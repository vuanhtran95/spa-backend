<?php

namespace App\Repositories;

use App\Helper\Translation;
use Illuminate\Support\Facades\DB;
use App\Repositories\CustomerRepository;
use App\Invoice;
use App\Customer;
use App\Constants\Invoice as InvoiceConstant;
use App\Helper\Common;

class InvoiceRepository implements InvoiceRepositoryInterface
{
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

	public function save($data, $is_update = true, $id = null)
	{
		$invoice = null;

		if ($is_update) {
			$invoice = Invoice::find($id);
		} else {
			$invoice = new Invoice();
			$invoice->status = InvoiceConstant::PENDING_STATUS;
		}

		foreach ($data as $property => $value) {
			$invoice->$property = $value;
		}

		$invoice->save();

		return $invoice;
	}

	public function approve($invoice_id, array $data = [])
	{
		// 0.Find Invoice by Id
		$invoice = Invoice::find($invoice_id);
		if ($invoice->status === InvoiceConstant::PAID_STATUS) {
			throw new \Exception('Transaction has been verified');
		}
		// 1.Find Customer by id
		$customer = Customer::find($invoice->customer_id);
		if (empty($customer)) {
			throw new \Exception('Customer is not existed');
		}
		if (empty($data['signature'])) {
			throw new \Exception('Please add signature');
		}
		$invoice->signature = $data['signature'];

		if(isset($data['payment_method_id'])) $invoice->payment_method_id = $data['payment_method_id'];

		// 2.Update invoice status to "Paid"
		$invoice->status = InvoiceConstant::PAID_STATUS;
		$invoice->save();

		// 3.Calculate new balance
		$newBalance = $customer->balance + $invoice->amount + $invoice->promotion_amount;

		// 4.Update customer's balance
		$customer->balance = $newBalance;
		$customer->save();

		// 5. Update commission for employee
		$invoice->topup_commission = $invoice->amount * (3 / 100);
		$invoice->save();
		//TODO: UP RANK
		$up_rank = false;
		if (!empty($customer) ||  $invoice->type ===  InvoiceConstant::TOPUP) {
			$up_rank = Common::upRank($customer);
		}
		$invoice['up_rank_result'] = $up_rank;
		return $invoice;
	}

	public function get(array $condition = [])
	{
		$perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
		$page = isset($condition['page']) ? $condition['page'] : 1;
		$customer_id = isset($condition['customer_id']) ? $condition['customer_id'] : null;

		$employeeId = isset($condition['employee_id']) ? $condition['employee_id'] : null;
		$fromDate = isset($condition['from_date']) ? $condition['from_date'] : null;
		$toDate = isset($condition['to_date']) ? $condition['to_date'] : null;

		$type = isset($condition['type']) ? $condition['type'] : null;

		$status = isset($condition['status']) ? $condition['status'] : null;

		$query = new Invoice();
		if ($employeeId) {
			$query = $query::where('employee_id', $employeeId);
		}
		if ($customer_id !== null) {
			$query = $query->where('customer_id', '=', $customer_id);
		}
		if ($type !== null) {
			$query = $query->where('type', '=', $type);
		}
		if ($status !== null) {
			$query = $query->where('status', '=', $status);
		}
		if ($fromDate) {
			$query = $query->where('created_at', '>=', $fromDate);
		}

		if ($toDate) {
			$query = $query->where('created_at', '<=', $toDate);
		}
		$invoices = $query->with(['customer'])
			->with(['employee'])
			->paginate($perPage, ['*'], 'page', $page);

		return [
			"Data" => $invoices->items(),
			"Pagination" => [
				"CurrentPage" => $page,
				"PerPage" => $perPage,
				"TotalItems" => $invoices->total()
			]
		];
	}

	public function delete($id)
	{
		$invoice = Invoice::where('status', InvoiceConstant::PENDING_STATUS)->find($id);
		if ($invoice) {
			$invoice->delete();
		} else {
			throw new \Exception(Translation::$NO_INVOICE_FOUND);
		}
	}



	public function getOneBy($by, $value)
	{
		return Invoice::with(
			['customer', 'employee']
		)->where($by, '=', $value)
			->first();
	}
}

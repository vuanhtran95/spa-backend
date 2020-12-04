<?php

namespace App\Repositories;

use App\Helper\Translation;
use Illuminate\Support\Facades\DB;
use App\Repositories\CustomerRepository;
use App\Invoice;
use App\Customer;
use App\Constants\Invoice as InvoiceConstant;

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

    public function approve($invoice_id)
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

        // 2.Update invoice status to "Paid"
        $invoice->status = InvoiceConstant::PAID_STATUS;
        $invoice->save();

        // 3.Calculate new balance
        $newBalance = $customer->balance + $invoice->amount + $invoice->promotion_amount;

        // 4.Update customer's balance
        $customer->balance = $newBalance;
        $customer->save();
        

        return $invoice;
    }

    public function get(array $condition = [])
    {
        $customer_id = isset($condition['customer_id']) ? $condition['customer_id'] : null;
        $type = isset($condition['type']) ? $condition['type'] : null;
        $query = new Invoice();
        if ($customer_id !== null) {
            $query = $query->where('customer_id', '=', $customer_id);
        }
        if ($type !== null) {
            $query = $query->where('type', '=', $type);
        }

        return $query->with(['customer'])->with(['employee'])->get()->toArray();
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
}

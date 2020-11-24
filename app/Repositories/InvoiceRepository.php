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

    public function approve($customer_id, $invoice_id)
    {
        // 1.Find Customer by id
        $customer = Customer::find($customer_id);

        // 2.Update invoice status to "Paid"
        $invoice = $this->save(['status' => InvoiceConstant::PAID_STATUS], true, $invoice_id);

        // 3.Calculate new balance
        $newBalance = $customer->balance + $invoice->amount;

        // 4.Update customer's balance
        $customerRepository = app(CustomerRepository::class);
        $customerRepository->save(['balance' => $newBalance], true, $customer_id);
        

        return $invoice;
    }
}

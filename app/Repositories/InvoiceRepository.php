<?php

namespace App\Repositories;

use App\Helper\Translation;
use Illuminate\Support\Facades\DB;
use App\Invoice;
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

        if (!empty($data['promotion_amount']) && !empty($data['amount'])) {
            $data['amount'] += $data['promotion_amount'];
        }

        foreach ($data as $property => $value) {
            $invoice->$property = $value;
        }

        $invoice->save();

        return $invoice;
    }
}

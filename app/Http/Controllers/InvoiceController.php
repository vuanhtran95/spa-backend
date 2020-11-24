<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\HttpResponse;
use Illuminate\Http\Response as Response;
use App\Repositories\InvoiceRepository;
use App\Repositories\CustomerRepository;
use App\Helper\Translation;
use App\Constants\Invoice as InvoiceConstant;
use App\Customer;
use App\Invoice;

class InvoiceController extends Controller
{
    private $invoiceRepository;

    private $customerRepository;

    public function __construct(InvoiceRepository $invoiceRepository, CustomerRepository $customerRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->customerRepository = $customerRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();

        try {
            $invoice = $this->invoiceRepository->create($params);
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$INVOICE_CREATED, $invoice);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function approve(Request $request, $invoice_id)
    {
        $params = $request->all();

        if (empty($params['customer_id'])) {
            throw new \Exception('Customer Id cannot be empty');
        }

        try {
            $invoice = $this->invoiceRepository->approve($params['customer_id'], $invoice_id);

            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$INVOICE_UPDATED, $invoice);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }

    }

    public function getInvoiceByCustomerId($customerId) 
    {
        $llistOfInvoiceByCustomer = Invoice::where('customer_id', $customerId)->get();

        return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_INVOICE_SUCCESS, $llistOfInvoiceByCustomer);
        
    }
}

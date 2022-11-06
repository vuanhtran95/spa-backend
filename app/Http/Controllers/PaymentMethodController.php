<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use App\Repositories\PaymentMethodRepositoryInterface;
use Illuminate\Http\Response as Response;
use App\Helper\Translation;

class PaymentMethodController extends Controller
{
    private $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function get()
    {
        $roles = $this->paymentMethodRepository->get();
        return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$GET_ALL_ROLE_SUCCESS, $roles);
    }
}

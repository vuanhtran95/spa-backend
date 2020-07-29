<?php

namespace App\Http;

class HttpResponse
{

    public function __construct()
    {
    }

    public static function toJson($isSuccess, $statusCode, $message = '', $body = [], $pagination = null, $headers = [])
    {
        $body = [
            "IsSuccess" => $isSuccess,
            "Data" => $body,
            "Pagination" => $pagination,
            "Message" => $message,
            "StatusCode" => $statusCode,
        ];
        return response($body, $statusCode);
    }
}

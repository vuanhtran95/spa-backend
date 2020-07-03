<?php

namespace App\Http;

class HttpResponse
{

    public function __construct()
    {
    }

    public static function toJson($isSuccess, $statusCode, $message = '', $body = [], $headers = [])
    {
        $body = [
            "IsSuccess" => $isSuccess,
            "Data" => $body,
            "Message" => $message,
            "StatusCode" => $statusCode
        ];
        return response($body, $statusCode);
    }
}

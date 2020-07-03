<?php

namespace App\Http\Middleware;

use App\Helper\Translation;
use App\Http\HttpResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Response as Response;
use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (Auth::guard('api')->check()) {
            return $next($request);
        }
        return HttpResponse::toJson(false, Response::HTTP_UNAUTHORIZED, Translation::$UNAUTHORIZED);
    }
}

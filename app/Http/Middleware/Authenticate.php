<?php

namespace App\Http\Middleware;

use App\Employee;
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

            $userId = $request->user()->id;
            $userType = isset($request->header()['user-type']) ? $request->header()['user-type'][0] : null;

            if ($userType) {
                $employee = Employee::where('user_id', $userId)->with('role')->first();
                $roleName = $employee->role->name;
                if ($roleName !== 'admin') {
                    if ($roleName !== $userType) {
                        return HttpResponse::toJson(false,
                            Response::HTTP_UNAUTHORIZED,
                            Translation::$UNAUTHORIZED
                        );
                    }
                }
                return $next($request);
            } else {
                return HttpResponse::toJson(false, Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS, Translation::$USER_TYPE_REQUIRED);
            }

        }
        return HttpResponse::toJson(false, Response::HTTP_UNAUTHORIZED, Translation::$UNAUTHORIZED);
    }
}

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
            $employee = Employee::where('user_id', $userId)->with('role')->first();
           
            $roleName = $employee->role->name;

            $userType = isset($request->header()['user-type']) ? $request->header()['user-type'][0] : null;

            switch ($userType) {
                case 'ktv':
                    $allowRoles = ['admin', 'ktv', 'reviewer', 'cashier'];
                    break;
                case 'admin':
                    $allowRoles = ['admin', 'cashier'];
                    break;
                default:
                    return HttpResponse::toJson(
                        false,
                        Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS,
                        Translation::$USER_TYPE_REQUIRED
                    );
            }

            if (!in_array($roleName, $allowRoles, true)) {
                return HttpResponse::toJson(
                    false,
                    Response::HTTP_UNAUTHORIZED,
                    Translation::$NO_PERMISSION
                );
            }
            return $next($request);
        }
        return HttpResponse::toJson(false, Response::HTTP_UNAUTHORIZED, Translation::$UNAUTHORIZED);
    }
}

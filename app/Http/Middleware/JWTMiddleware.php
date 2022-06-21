<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Libraries\JWT;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $authorization = $request->header('Authorization');
        if ($authorization == null) {
            return response([
                'data' => null,
                'error' => true,
                'message' => __('system.bad_request'),
            ], 400);
        } else {
            isset(explode(' ', $authorization)[1]) ? $key = explode(' ', $authorization)[1] : $key = $authorization;

            if (JWT::verify($key, true)) {
                $auth = new JWT($key);
                app()->instance(JWT::class, $auth);

                return $next($request);
            } else {
                return response([
                    'data' => null,
                    'error' => true,
                    'message' => __('system.unauthorized'),
                ], 401);
            }
        }
    }
}

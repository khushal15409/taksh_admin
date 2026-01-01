<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class APIGuestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if($request->header('Authorization') && $request->header('Authorization') !== 'Bearer null' && app('auth')->guard('api')) {
            $request->merge(['user'=>auth('api')->user()]);
            return $next($request);
        }
        // Check for guest_id in query params (for GET requests) or request body (for POST/PUT/DELETE)
        elseif($request->guest_id || $request->query('guest_id')) {
            return $next($request);
        }
        // For cart APIs, allow without guest_id/authentication (cart_id is sufficient for update/delete)
        elseif($request->is('api/v1/customer/cart/*')) {
            return $next($request);
        }
        return response()->json(['errors' => 'Unauthorized'], 401);
    }
}

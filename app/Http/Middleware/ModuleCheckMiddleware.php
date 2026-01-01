<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Config;
use App\Models\Module;

class ModuleCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $except = [
            'api/v1/customer*', 'api/v1/banners', 'api/v1/stores/get-stores/*', 'api/v1/stores/list', 'api/v1/coupon/list', 'api/v1/categories', 'api/v1/items/reviews/submit', 'api/v1/delivery-man/reviews/submit', 'api/v1/items/popular', 'api/v1/items/recommended', 'api/v1/items/discounted', 'api/v1/items/list', 'api/v1/items/details/*'
        ];

        // Check if route is in exception list
        foreach ($except as $exceptPattern) {
            if ($request->fullUrlIs($exceptPattern) || $request->is($exceptPattern)) {
                // For exception routes, moduleId is optional
                // If provided, try to set it, but don't fail if invalid
                $module_id = $request->header('moduleId') ?? $request->query('module_id');
                if ($module_id) {
                    $module = Module::find($module_id);
                    if ($module) {
                        Config::set('module.current_module_data', $module);
                    } else {
                        // If moduleId provided but invalid, try to use first active module as fallback
                        $fallbackModule = Module::active()->first();
                        if ($fallbackModule) {
                            Config::set('module.current_module_data', $fallbackModule);
                        }
                    }
                } else {
                    // No moduleId provided, try to use first active module as fallback
                    $fallbackModule = Module::active()->first();
                    if ($fallbackModule) {
                        Config::set('module.current_module_data', $fallbackModule);
                    }
                }
                return $next($request);
            }
        }

        // Check header request and determine localizaton
        // Try header first, then query parameter as fallback
        $module_id = $request->header('moduleId') ?? $request->query('module_id');
        
        if(!$module_id)
        {
            $errors = [];
            array_push($errors, ['code' => 'moduleId', 'message' => translate('messages.module_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $module = Module::find($module_id);
        if(!$module) {
            $errors = [];
            array_push($errors, ['code' => 'moduleId', 'message' => translate('messages.not_found')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        Config::set('module.current_module_data', $module);
        return $next($request);
    }
}

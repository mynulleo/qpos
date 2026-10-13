<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Route;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $controllerAction = class_basename(Route::currentRouteName());
        if (isset($controllerAction)) {
            $user = auth()->guard('admin')->user() ?? auth()->user();
            // Role 1 (Administrator) & Role 2 (System Admin) bypass role-based restriction
            $isSuperAdmin = $user && isset($user->role_id) && in_array((int)$user->role_id, [1, 2]);

            if (!$isSuperAdmin) {
                $permitedMenuArr = App::make('permittedMenuArr');
                if (! in_array($controllerAction, $permitedMenuArr)) {
                    return abort(403);
                }
            }

            if ($request->format() == 'html' || (!$request->expectsJson() && !$request->ajax())) {
                return response(view('admin.layouts.admin_app'));
            } elseif ($request->format() == 'json' || $request->expectsJson() || $request->ajax()) {
                return $next($request);
            } else {
                return response(view('admin.layouts.admin_app'));
            }
        }

        return $next($request);
    }
}

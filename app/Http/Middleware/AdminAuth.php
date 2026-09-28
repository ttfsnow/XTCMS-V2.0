<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('admin_uid')) {
            return redirect()
                ->route('admin.login')
                ->with('status', '请先登录后台');
        }

        return $next($request);
    }
}

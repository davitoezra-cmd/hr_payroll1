<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
   public function handle($request, Closure $next)
{
    $user = $request->user();

    if (!$user instanceof \App\Models\User) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    return $next($request);
}
}
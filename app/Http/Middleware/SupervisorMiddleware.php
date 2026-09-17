<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SupervisorMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

    if (!$user instanceof \App\Models\Supervisor ){
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    return $next($request);

        }

    }

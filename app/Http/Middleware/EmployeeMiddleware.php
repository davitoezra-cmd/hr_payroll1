<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof Employee) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (! $user->is_active) {
            $user->tokens()->delete();

            return response()->json([
                'success' => false,
                'message' => 'Akun employee sudah tidak aktif.',
            ], 403);
        }

        return $next($request);
    }
}

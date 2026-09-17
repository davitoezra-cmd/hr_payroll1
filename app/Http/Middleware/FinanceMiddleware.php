<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Finance;

class FinanceMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | SUPERADMIN
        |--------------------------------------------------------------------------
        | User dari tabel users hanya digunakan untuk Superadmin.
        | Jadi Superadmin boleh mengakses Finance tanpa login ulang.
        */
        if ($user instanceof User) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | FINANCE
        |--------------------------------------------------------------------------
        | Finance tetap boleh mengakses halaman/API Finance seperti sebelumnya.
        */
        if ($user instanceof Finance) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | TIDAK DIIZINKAN
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }
}


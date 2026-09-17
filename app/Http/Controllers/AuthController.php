<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOGIN SUPER ADMIN
        |--------------------------------------------------------------------------
        */
        if (Auth::guard('user')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {

            $user = Auth::guard('user')->user();

            $user->tokens()->delete();

            $token = $user->createToken('super-admin-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Super Admin berhasil',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'guard' => 'user',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN EMPLOYEE
        |--------------------------------------------------------------------------
        */
        if (Auth::guard('employee')->attempt([
            'email' => $request->email,
            'password' => $request->password,
            'is_active' => true,
        ])) {

            $employee = Auth::guard('employee')->user();

            $employee->tokens()->delete();

            $token = $employee->createToken('employee-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Employee berhasil',
                'token' => $token,
                'user' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'guard' => 'employee',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN FINANCE
        |--------------------------------------------------------------------------
        */
        if (Auth::guard('finance')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {

            $finance = Auth::guard('finance')->user();

            $finance->tokens()->delete();

            $token = $finance->createToken('finance-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Finance berhasil',
                'token' => $token,
                'user' => [
                    'id' => $finance->id,
                    'name' => $finance->name,
                    'email' => $finance->email,
                    'guard' => 'finance',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN SUPERVISOR
        |--------------------------------------------------------------------------
        */
        if (Auth::guard('supervisor')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {

            $supervisor = Auth::guard('supervisor')->user();

            $supervisor->tokens()->delete();

            $token = $supervisor->createToken('supervisor-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Supervisor berhasil',
                'token' => $token,
                'user' => [
                    'id' => $supervisor->id,
                    'name' => $supervisor->name,
                    'email' => $supervisor->email,
                    'guard' => 'supervisor',
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah',
        ], 401);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }
}
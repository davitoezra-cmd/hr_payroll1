<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    /**
     * GET /api/profile
     * Profile Super Admin
     */
    public function edit(Request $request): JsonResponse
    {

       $user = auth()->user();


        if (!$user) {
            return response()->json([
                'success'=>false,
                'message'=>'User belum login'
            ],401);
        }


        return response()->json([
            'success'=>true,
            'data'=>[
                'user'=>$user
            ]
        ]);

    }



    /**
     * PUT/PATCH /api/profile
     * Update profile Super Admin
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User belum login'
        ], 401);
    }

    $user->fill($request->validated());

    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Profile berhasil diperbarui.',
        'data' => [
            'user' => $user->fresh()
        ]
    ]);
}
}
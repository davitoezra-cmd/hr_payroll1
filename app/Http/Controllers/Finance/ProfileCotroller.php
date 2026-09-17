<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{

    public function edit(Request $request): JsonResponse
    {

       $finance = auth()->user();


        if(!$finance){

            return response()->json([
                'success'=>false,
                'message'=>'Finance belum login'
            ],401);

        }


        return response()->json([

            'success'=>true,

            'data'=>[
                'finance'=>$finance
            ]

        ]);

    }



    public function update(Request $request): JsonResponse
    {

        $finance = auth()->user();


        if(!$finance){

            return response()->json([
                'success'=>false,
                'message'=>'Finance belum login'
            ],401);

        }



        $request->validate([

            'name'=>'required|string|max:255',

            'email'=>'required|email|unique:finances,email,'.$finance->id,

        ]);



        $finance->update([

            'name'=>$request->name,

            'email'=>$request->email

        ]);



        return response()->json([

            'success'=>true,

            'message'=>'Profile berhasil diperbarui',

            'data'=>[
                'finance'=>$finance->fresh()
            ]

        ]);

    }

}
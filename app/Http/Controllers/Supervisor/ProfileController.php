<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{

    public function edit(Request $request): JsonResponse
    {

       $supervisor = auth()->user();


        if(!$supervisor){

            return response()->json([
                'success'=>false,
                'message'=>'Supervisor belum login'
            ],401);

        }


        return response()->json([

            'success'=>true,

            'data'=> $supervisor

        ]);

    }



    public function update(Request $request): JsonResponse
    {

        $supervisor = auth()->user();


        if(!$supervisor){

            return response()->json([
                'success'=>false,
                'message'=>'Supervisor belum login'
            ],401);

        }



        $request->validate([

            'name'=>'required|string|max:255',

            'email'=>'required|email|unique:supervisors,email,'.$supervisor->id,

        ]);



        $supervisor->update([

            'name'=>$request->name,

            'email'=>$request->email

        ]);



        return response()->json([

            'success'=>true,

            'message'=>'Profile berhasil diperbarui',

            'data'=> $supervisor->fresh()

        ]);

    }

}
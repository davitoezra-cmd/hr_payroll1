<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function edit(Request $request): JsonResponse
    {

       $employee = auth()->user();


        if(!$employee){

            return response()->json([
                'success'=>false,
                'message'=>'Employee belum login'
            ],401);

        }


        return response()->json([

            'success'=>true,

            'data'=> $employee

        ]);

    }



    public function update(Request $request): JsonResponse
    {

        $employee = auth()->user();


        if(!$employee){

            return response()->json([
                'success'=>false,
                'message'=>'Employee belum login'
            ],401);

        }



        $validator = Validator::make($request->all(), [

            'name'=>'required|string|max:255',

            'email'=>'required|email|unique:employees,email,'.$employee->id,

            'nama_bank'=>'nullable|string|max:255',

            'no_rekening'=>'nullable|string|max:255',

            'nama_rekening'=>'nullable|string|max:255',

        ]);



        $employee->update([

            'name'=>$request->name,

            'email'=>$request->email,
            'nama_bank'=>$request->nama_bank,
            'no_rekening'=>$request->no_rekening,
            'nama_rekening'=>$request->nama_rekening

        ]);



        return response()->json([

            'success'=>true,

            'message'=>'Profile berhasil diperbarui',

            'data'=> $employee->fresh()

        ]);

    }

}
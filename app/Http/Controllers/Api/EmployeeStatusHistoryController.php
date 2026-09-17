<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeStatusHistorie;
use Illuminate\Http\JsonResponse;

class EmployeeStatusHistoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => EmployeeStatusHistorie::with([
                'employment.employee',
                'changedBy',
            ])->latest('effective_from')->get(),
        ]);
    }

    public function show(EmployeeStatusHistorie $employeeStatusHistory): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $employeeStatusHistory->load([
                'employment.employee',
                'changedBy',
            ]),
        ]);
    }
}

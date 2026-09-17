<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeEmployment;
use App\Models\User;
use App\Services\EmployeeLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeEmploymentController extends Controller
{
    public function __construct(private readonly EmployeeLifecycleService $lifecycleService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => EmployeeEmployment::with('employee')->latest('id')->get(),
        ]);
    }

    public function show(EmployeeEmployment $employeeEmployment): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $employeeEmployment->load([
                'employee',
                'statusHistories.changedBy',
                'separations',
                'lifecycleTasks',
            ]),
        ]);
    }

    public function startOnboarding(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        /** @var User $actor */
        $actor = $request->user();
        $employment = $this->lifecycleService->startOnboarding($employee, $validated, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding employee berhasil dimulai.',
            'data' => $employment,
        ], 201);
    }

    public function completeOnboarding(Request $request, EmployeeEmployment $employeeEmployment): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $employment = $this->lifecycleService->completeOnboarding($employeeEmployment, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding employee berhasil diselesaikan.',
            'data' => $employment,
        ]);
    }
}

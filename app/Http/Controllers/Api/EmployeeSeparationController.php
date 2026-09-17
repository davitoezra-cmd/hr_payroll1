<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSeparation;
use App\Models\User;
use App\Services\EmployeeLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeSeparationController extends Controller
{
    public function __construct(private readonly EmployeeLifecycleService $lifecycleService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => EmployeeSeparation::with('employment.employee')
                ->latest('submitted_at')
                ->get(),
        ]);
    }

    public function show(EmployeeSeparation $employeeSeparation): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $employeeSeparation->load([
                'employment.employee',
                'approvedBy',
                'processedBy',
                'lifecycleTasks',
            ]),
        ]);
    }

    public function myResignation(Request $request): JsonResponse
    {
        /** @var Employee $employee */
        $employee = $request->user();

        $separation = EmployeeSeparation::query()
            ->where('separation_type', 'RESIGNATION')
            ->whereHas('employment', fn ($query) => $query->where('employee_id', $employee->id))
            ->with('employment')
            ->latest('submitted_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $separation,
        ]);
    }

    public function requestResignation(Request $request): JsonResponse
    {
        $today = now('Asia/Jakarta')->toDateString();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'last_working_date' => ['required', 'date', 'after_or_equal:'.$today],
            'effective_date' => ['required', 'date', 'after_or_equal:last_working_date'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var Employee $employee */
        $employee = $request->user();
        $separation = $this->lifecycleService->submitResignation($employee, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan resignation berhasil dibuat.',
            'data' => $separation,
        ], 201);
    }

    public function terminate(Request $request, Employee $employee): JsonResponse
    {
        $today = now('Asia/Jakarta')->toDateString();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'last_working_date' => ['nullable', 'date', 'before_or_equal:effective_date'],
            'effective_date' => ['required', 'date', 'after_or_equal:'.$today],
            'notes' => ['nullable', 'string'],
        ]);

        $separation = $this->lifecycleService->terminate($employee, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Termination berhasil diajukan untuk approval.',
            'data' => $separation,
        ], 201);
    }

    public function approve(Request $request, EmployeeSeparation $employeeSeparation): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $separation = $this->lifecycleService->approveSeparation($employeeSeparation, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Separation berhasil disetujui.',
            'data' => $separation,
        ]);
    }

    public function reject(Request $request, EmployeeSeparation $employeeSeparation): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        /** @var User $actor */
        $actor = $request->user();
        $separation = $this->lifecycleService->rejectSeparation(
            $employeeSeparation,
            $actor,
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Separation berhasil ditolak.',
            'data' => $separation,
        ]);
    }

    public function startOffboarding(EmployeeSeparation $employeeSeparation): JsonResponse
    {
        $separation = $this->lifecycleService->startOffboarding($employeeSeparation);

        return response()->json([
            'success' => true,
            'message' => 'Offboarding berhasil dimulai.',
            'data' => $separation,
        ]);
    }

    public function complete(Request $request, EmployeeSeparation $employeeSeparation): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $separation = $this->lifecycleService->completeSeparation($employeeSeparation, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Offboarding dan separation berhasil diselesaikan.',
            'data' => $separation,
        ]);
    }
}

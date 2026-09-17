<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLifecycleTask;
use App\Models\EmployeeSeparation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeLifecycleTaskController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => EmployeeLifecycleTask::with([
                'employment.employee',
                'separation',
                'completedBy',
            ])->latest('id')->get(),
        ]);
    }

    public function show(EmployeeLifecycleTask $employeeLifecycleTask): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $employeeLifecycleTask->load([
                'employment.employee',
                'separation',
                'completedBy',
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employment_id' => ['required', 'exists:employee_employments,id'],
            'separation_id' => ['nullable', 'exists:employee_separations,id'],
            'phase' => ['required', 'in:ONBOARDING,OFFBOARDING'],
            'task_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['phase'] === 'ONBOARDING' && ! empty($validated['separation_id'])) {
            throw ValidationException::withMessages([
                'separation_id' => 'Onboarding task tidak boleh memiliki separation_id.',
            ]);
        }

        if ($validated['phase'] === 'OFFBOARDING') {
            if (empty($validated['separation_id'])) {
                throw ValidationException::withMessages([
                    'separation_id' => 'Offboarding task wajib memiliki separation_id.',
                ]);
            }

            $separationMatchesEmployment = EmployeeSeparation::query()
                ->whereKey($validated['separation_id'])
                ->where('employment_id', $validated['employment_id'])
                ->exists();

            if (! $separationMatchesEmployment) {
                throw ValidationException::withMessages([
                    'separation_id' => 'Separation tidak terkait dengan employment yang dipilih.',
                ]);
            }
        }

        $task = EmployeeLifecycleTask::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lifecycle task berhasil dibuat.',
            'data' => $task,
        ], 201);
    }

    public function update(Request $request, EmployeeLifecycleTask $employeeLifecycleTask): JsonResponse
    {
        $validated = $request->validate([
            'task_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:PENDING,IN_PROGRESS,COMPLETED,SKIPPED'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $task = DB::transaction(function () use ($validated, $employeeLifecycleTask, $request) {
            $task = EmployeeLifecycleTask::query()->lockForUpdate()->findOrFail($employeeLifecycleTask->id);
            $task->fill($validated);

            if (array_key_exists('status', $validated)) {
                if ($validated['status'] === 'COMPLETED') {
                    $task->completed_at = now();
                    $task->completed_by = $request->user()->id;
                } elseif ($validated['status'] !== 'COMPLETED') {
                    $task->completed_at = null;
                    $task->completed_by = null;
                }
            }

            $task->save();

            return $task->fresh(['employment.employee', 'separation', 'completedBy']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Lifecycle task berhasil diperbarui.',
            'data' => $task,
        ]);
    }
}

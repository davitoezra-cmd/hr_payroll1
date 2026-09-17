<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePerformance;
use App\Models\EmployeeTarget;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerformanceController extends Controller
{
    /**
     * =========================================================
     * DASHBOARD KINERJA SUPERADMIN
     * =========================================================
     *
     * Superadmin dapat melihat seluruh target dan penilaian.
     */
    public function index(Request $request)
    {
        $query = EmployeeTarget::with([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeePerformance',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Filter Supervisor
        |--------------------------------------------------------------------------
        */
        if ($request->filled('supervisor_id')) {
            $query->where(
                'supervisor_id',
                $request->supervisor_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Employee
        |--------------------------------------------------------------------------
        */
        if ($request->filled('employee_id')) {
            $query->where(
                'employee_id',
                $request->employee_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Category
        |--------------------------------------------------------------------------
        */
        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->category
            );
        }

        $targets = $query
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */
        $totalTargets = $targets->count();

        $completedTargets = $targets
            ->where('status', 'completed')
            ->count();

        $ongoingTargets = $targets
            ->where('status', 'ongoing')
            ->count();

        $notAchievedTargets = $targets
            ->where('status', 'not_achieved')
            ->count();

        $totalEmployees = $targets
            ->pluck('employee_id')
            ->unique()
            ->count();

        $averageProgress = $targets->avg(
            'progress_percent'
        );

        /*
        |--------------------------------------------------------------------------
        | Statistik Penilaian
        |--------------------------------------------------------------------------
        */
        $performances = EmployeePerformance::query();

        if ($request->filled('employee_id')) {
            $performances->where(
                'employee_id',
                $request->employee_id
            );
        }

        if ($request->filled('supervisor_id')) {
            $performances->where(
                'supervisor_id',
                $request->supervisor_id
            );
        }

        $performanceData = $performances->get();

        $averageScore = $performanceData->avg('score');

        return response()->json([
            'success' => true,

            'message' => 'Data kinerja berhasil diambil.',

            'statistics' => [
                'total_employees' => $totalEmployees,

                'total_targets' => $totalTargets,

                'completed_targets' => $completedTargets,

                'ongoing_targets' => $ongoingTargets,

                'not_achieved_targets' => $notAchievedTargets,

                'average_progress' => round(
                    (float) ($averageProgress ?? 0),
                    2
                ),

                'total_performances' => $performanceData->count(),

                'average_score' => round(
                    (float) ($averageScore ?? 0),
                    2
                ),
            ],

            'data' => $targets,
        ]);
    }


    /**
     * =========================================================
     * DAFTAR TARGET
     * =========================================================
     */
    public function targets(Request $request)
    {
        $query = EmployeeTarget::with([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeePerformance',
        ]);

        if ($request->filled('supervisor_id')) {
            $query->where(
                'supervisor_id',
                $request->supervisor_id
            );
        }

        if ($request->filled('employee_id')) {
            $query->where(
                'employee_id',
                $request->employee_id
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->category
            );
        }

        if ($request->filled('month')) {
            $query->whereMonth(
                'start_date',
                $request->month
            );
        }

        if ($request->filled('year')) {
            $query->whereYear(
                'start_date',
                $request->year
            );
        }

        $targets = $query
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'count' => $targets->count(),
            'data' => $targets,
        ]);
    }


    /**
     * =========================================================
     * BUAT TARGET
     * =========================================================
     *
     * Hanya Superadmin.
     */
    public function storeTarget(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'employee_id' => [
                    'required',
                    'exists:employees,id',
                ],

                'supervisor_id' => [
                    'required',
                    'exists:supervisors,id',
                ],

                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'category' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'target_value' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'start_date' => [
                    'required',
                    'date',
                ],

                'end_date' => [
                    'required',
                    'date',
                    'after_or_equal:start_date',
                ],

                'notes' => [
                    'nullable',
                    'string',
                ],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $target = EmployeeTarget::create([
            'employee_id' => $request->employee_id,

            'supervisor_id' => $request->supervisor_id,

            'title' => $request->title,

            'description' => $request->description,

            'category' => $request->category,

            'target_value' => $request->target_value,

            'current_value' => 0,

            'progress_percent' => 0,

            'start_date' => $request->start_date,

            'end_date' => $request->end_date,

            'status' => 'ongoing',

            'notes' => $request->notes,
        ]);

        $target->load([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
        ]);

        return response()->json([
            'success' => true,

            'message' => 'Target berhasil dibuat.',

            'data' => $target,
        ], 201);
    }


    /**
     * =========================================================
     * DETAIL TARGET
     * =========================================================
     */
    public function showTarget($id)
    {
        $target = EmployeeTarget::with([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeePerformance',
        ])->find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $target,
        ]);
    }


    /**
     * =========================================================
     * UPDATE TARGET
     * =========================================================
     */
    public function updateTarget(Request $request, $id)
    {
        $target = EmployeeTarget::find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'employee_id' => [
                    'required',
                    'exists:employees,id',
                ],

                'supervisor_id' => [
                    'required',
                    'exists:supervisors,id',
                ],

                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'category' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'target_value' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'start_date' => [
                    'required',
                    'date',
                ],

                'end_date' => [
                    'required',
                    'date',
                    'after_or_equal:start_date',
                ],

                'status' => [
                    'required',
                    'in:ongoing,completed,not_achieved',
                ],

                'notes' => [
                    'nullable',
                    'string',
                ],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $target->update([
            'employee_id' => $request->employee_id,

            'supervisor_id' => $request->supervisor_id,

            'title' => $request->title,

            'description' => $request->description,

            'category' => $request->category,

            'target_value' => $request->target_value,

            'start_date' => $request->start_date,

            'end_date' => $request->end_date,

            'status' => $request->status,

            'notes' => $request->notes,
        ]);

        $target->load([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeePerformance',
        ]);

        return response()->json([
            'success' => true,

            'message' => 'Target berhasil diperbarui.',

            'data' => $target,
        ]);
    }


    /**
     * =========================================================
     * HAPUS TARGET
     * =========================================================
     */
    public function destroyTarget($id)
    {
        $target = EmployeeTarget::find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan.',
            ], 404);
        }

        $target->delete();

        return response()->json([
            'success' => true,

            'message' => 'Target berhasil dihapus.',
        ]);
    }


    /**
     * =========================================================
     * UPDATE PROGRESS TARGET
     * =========================================================
     *
     * Hanya Superadmin.
     */
    public function updateProgress(Request $request, $id)
    {
        $target = EmployeeTarget::find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'current_value' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $target->current_value =
            $request->current_value;

        if ($target->target_value > 0) {
            $percent =
                (
                    $target->current_value /
                    $target->target_value
                ) * 100;
        } else {
            $percent = 0;
        }

        $target->progress_percent = min(
            100,
            round($percent, 2)
        );

        /*
        |--------------------------------------------------------------------------
        | Otomatis menentukan status
        |--------------------------------------------------------------------------
        */
        if ($target->progress_percent >= 100) {

            $target->status = 'completed';

        } elseif (
            $target->status === 'completed'
        ) {

            $target->status = 'ongoing';
        }

        $target->save();

        return response()->json([
            'success' => true,

            'message' => 'Progress target berhasil diperbarui.',

            'data' => $target,
        ]);
    }


    /**
     * =========================================================
     * DAFTAR PENILAIAN
     * =========================================================
     */
    public function performances(Request $request)
    {
        $query = EmployeePerformance::with([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeeTarget:id,title,employee_id,supervisor_id,progress_percent,status',
        ]);

        if ($request->filled('employee_id')) {
            $query->where(
                'employee_id',
                $request->employee_id
            );
        }

        if ($request->filled('supervisor_id')) {
            $query->where(
                'supervisor_id',
                $request->supervisor_id
            );
        }

        if ($request->filled('grade')) {
            $query->where(
                'grade',
                $request->grade
            );
        }

        $performances = $query
            ->latest()
            ->get();

        return response()->json([
            'success' => true,

            'count' => $performances->count(),

            'data' => $performances,
        ]);
    }


    /**
     * =========================================================
     * BUAT PENILAIAN
     * =========================================================
     *
     * Hanya Superadmin.
     */
    public function storePerformance(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'employee_id' => [
                    'required',
                    'exists:employees,id',
                ],

                'supervisor_id' => [
                    'required',
                    'exists:supervisors,id',
                ],

                'employee_target_id' => [
                    'required',
                    'exists:employee_targets,id',
                ],

                'score' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:100',
                ],

                'grade' => [
                    'required',
                    'string',
                    'max:10',
                ],

                'feedback' => [
                    'nullable',
                    'string',
                ],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Pastikan target memang milik employee
        |--------------------------------------------------------------------------
        */
        $target = EmployeeTarget::find(
            $request->employee_target_id
        );

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan.',
            ], 404);
        }

        if (
            (int) $target->employee_id !==
            (int) $request->employee_id
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Target bukan milik employee tersebut.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Cek apakah target sudah mempunyai penilaian
        |--------------------------------------------------------------------------
        */
        $existing = EmployeePerformance::where(
            'employee_target_id',
            $request->employee_target_id
        )->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Target tersebut sudah memiliki penilaian.',
            ], 422);
        }

        $performance = EmployeePerformance::create([
            'employee_id' =>
                $request->employee_id,

            'supervisor_id' =>
                $request->supervisor_id,

            'employee_target_id' =>
                $request->employee_target_id,

            'score' =>
                $request->score,

            'grade' =>
                $request->grade,

            'feedback' =>
                $request->feedback,
        ]);

        $performance->load([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeeTarget:id,title,employee_id,supervisor_id,progress_percent,status',
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Penilaian kinerja berhasil dibuat.',

            'data' => $performance,
        ], 201);
    }


    /**
     * =========================================================
     * DETAIL PENILAIAN
     * =========================================================
     */
    public function showPerformance($id)
    {
        $performance = EmployeePerformance::with([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeeTarget',
        ])->find($id);

        if (!$performance) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Penilaian tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,

            'data' => $performance,
        ]);
    }


    /**
     * =========================================================
     * UPDATE PENILAIAN
     * =========================================================
     */
    public function updatePerformance(
        Request $request,
        $id
    ) {
        $performance =
            EmployeePerformance::find($id);

        if (!$performance) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Penilaian tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'score' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:100',
                ],

                'grade' => [
                    'required',
                    'string',
                    'max:10',
                ],

                'feedback' => [
                    'nullable',
                    'string',
                ],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $performance->update([
            'score' =>
                $request->score,

            'grade' =>
                $request->grade,

            'feedback' =>
                $request->feedback,
        ]);

        $performance->load([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeeTarget',
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Penilaian kinerja berhasil diperbarui.',

            'data' => $performance,
        ]);
    }


    /**
     * =========================================================
     * HAPUS PENILAIAN
     * =========================================================
     */
    public function destroyPerformance($id)
    {
        $performance =
            EmployeePerformance::find($id);

        if (!$performance) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Penilaian tidak ditemukan.',
            ], 404);
        }

        $performance->delete();

        return response()->json([
            'success' => true,

            'message' =>
                'Penilaian berhasil dihapus.',
        ]);
    }


    /**
     * =========================================================
     * DAFTAR EMPLOYEE
     * =========================================================
     */
    public function employees()
    {
        $employees = Employee::where(
            'is_active',
            true
        )
            ->select(
                'id',
                'employee_code',
                'name',
                'email'
            )
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }


    /**
     * =========================================================
     * DAFTAR SUPERVISOR
     * =========================================================
     */
    public function supervisors()
    {
        $supervisors = Supervisor::where(
            'is_active',
            true
        )
            ->select(
                'id',
                'name',
                'email'
            )
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $supervisors,
        ]);
    }
}
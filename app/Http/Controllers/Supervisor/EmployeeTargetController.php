<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTarget;
use App\Models\Supervisor;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class EmployeeTargetController extends Controller
{
    /**
     * =========================================================
     * DAFTAR TARGET ANGGOTA TIM SUPERVISOR
     * =========================================================
     *
     * Supervisor hanya bisa melihat target employee
     * yang menjadi anggota timnya.
     */
    public function index(Request $request)
    {
        $supervisor = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Ambil ID Team milik Supervisor yang sedang login
        |--------------------------------------------------------------------------
        */
        $teamIds = TeamMember::where(
            'member_type',
            Supervisor::class
        )
            ->where(
                'member_id',
                $supervisor->id
            )
            ->pluck('team_id');

        /*
        |--------------------------------------------------------------------------
        | Ambil Employee yang menjadi anggota team tersebut
        |--------------------------------------------------------------------------
        */
        $employeeIds = TeamMember::whereIn(
            'team_id',
            $teamIds
        )
            ->where(
                'member_type',
                Employee::class
            )
            ->pluck('member_id');

        /*
        |--------------------------------------------------------------------------
        | Query Target
        |--------------------------------------------------------------------------
        */
        $query = EmployeeTarget::with([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeePerformance',
        ])
            ->whereIn(
                'employee_id',
                $employeeIds
            );

        /*
        |--------------------------------------------------------------------------
        | Filter Employee
        |--------------------------------------------------------------------------
        */
        if ($request->filled('employee_id')) {

            /*
             * Pastikan employee memang anggota team Supervisor.
             */
            if (!$employeeIds->contains(
                (int) $request->employee_id
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee bukan anggota tim Anda.',
                ], 403);
            }

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

        return response()->json([
            'success' => true,
            'count' => $targets->count(),
            'data' => $targets,
        ]);
    }


    /**
     * =========================================================
     * DETAIL TARGET
     * =========================================================
     *
     * Supervisor hanya dapat melihat target
     * anggota team-nya sendiri.
     */
    public function show($id)
    {
        $supervisor = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Team Supervisor
        |--------------------------------------------------------------------------
        */
        $teamIds = TeamMember::where(
            'member_type',
            Supervisor::class
        )
            ->where(
                'member_id',
                $supervisor->id
            )
            ->pluck('team_id');

        /*
        |--------------------------------------------------------------------------
        | Employee anggota team
        |--------------------------------------------------------------------------
        */
        $employeeIds = TeamMember::whereIn(
            'team_id',
            $teamIds
        )
            ->where(
                'member_type',
                Employee::class
            )
            ->pluck('member_id');

        /*
        |--------------------------------------------------------------------------
        | Cari target
        |--------------------------------------------------------------------------
        */
        $target = EmployeeTarget::with([
            'employee:id,employee_code,name,email',
            'supervisor:id,name,email',
            'employeePerformance',
        ])
            ->whereIn(
                'employee_id',
                $employeeIds
            )
            ->find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan atau bukan milik anggota tim Anda.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $target,
        ]);
    }


    /**
     * =========================================================
     * DAFTAR ANGGOTA TIM
     * =========================================================
     *
     * Endpoint ini bisa dipakai untuk halaman monitoring
     * Supervisor.
     */
    public function teamMembers()
    {
        $supervisor = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Ambil team Supervisor
        |--------------------------------------------------------------------------
        */
        $teamIds = TeamMember::where(
            'member_type',
            Supervisor::class
        )
            ->where(
                'member_id',
                $supervisor->id
            )
            ->pluck('team_id');

        /*
        |--------------------------------------------------------------------------
        | Ambil Employee anggota team
        |--------------------------------------------------------------------------
        */
        $members = TeamMember::with([
            'member',
            'team',
        ])
            ->whereIn(
                'team_id',
                $teamIds
            )
            ->where(
                'member_type',
                Employee::class
            )
            ->get();

        return response()->json([
            'success' => true,
            'count' => $members->count(),
            'data' => $members,
        ]);
    }


    /**
     * =========================================================
     * MONITORING PROGRESS
     * =========================================================
     *
     * Supervisor hanya melihat progress.
     * Tidak bisa mengubah progress.
     */
    public function monitoring(Request $request)
    {
        $supervisor = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Team Supervisor
        |--------------------------------------------------------------------------
        */
        $teamIds = TeamMember::where(
            'member_type',
            Supervisor::class
        )
            ->where(
                'member_id',
                $supervisor->id
            )
            ->pluck('team_id');

        /*
        |--------------------------------------------------------------------------
        | Employee anggota team
        |--------------------------------------------------------------------------
        */
        $employeeIds = TeamMember::whereIn(
            'team_id',
            $teamIds
        )
            ->where(
                'member_type',
                Employee::class
            )
            ->pluck('member_id');

        /*
        |--------------------------------------------------------------------------
        | Target
        |--------------------------------------------------------------------------
        */
        $query = EmployeeTarget::with([
            'employee:id,employee_code,name,email',
            'employeePerformance',
        ])
            ->whereIn(
                'employee_id',
                $employeeIds
            );

        /*
        |--------------------------------------------------------------------------
        | Filter employee
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
        | Filter status
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
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

        $averageProgress = $targets->avg(
            'progress_percent'
        );

        return response()->json([
            'success' => true,

            'statistics' => [
                'total_targets' => $totalTargets,

                'completed_targets' => $completedTargets,

                'ongoing_targets' => $ongoingTargets,

                'not_achieved_targets' => $notAchievedTargets,

                'average_progress' => round(
                    (float) ($averageProgress ?? 0),
                    2
                ),
            ],

            'data' => $targets,
        ]);
    }
}
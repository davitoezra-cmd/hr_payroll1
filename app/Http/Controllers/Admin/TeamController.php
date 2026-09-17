<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Supervisor;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Menampilkan semua tim.
     */
    public function index()
    {
        $teams = Team::with([
            'creator:id,name,email',
            'members.member',
        ])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Data tim berhasil diambil.',
            'data' => $teams,
        ]);
    }

    /**
     * Menampilkan daftar Supervisor dan Employee
     * yang dapat dimasukkan ke dalam tim.
     */
    public function availableMembers()
    {
        $supervisors = Supervisor::where('is_active', true)
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name')
            ->get()
            ->map(function ($supervisor) {
                return [
                    'id' => $supervisor->id,
                    'name' => $supervisor->name,
                    'email' => $supervisor->email,
                    'phone' => $supervisor->phone,
                    'type' => 'supervisor',
                    'model_type' => Supervisor::class,
                ];
            });

        $employees = Employee::where('is_active', true)
            ->select(
                'id',
                'employee_code',
                'name',
                'email',
                'phone'
            )
            ->orderBy('name')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'type' => 'employee',
                    'model_type' => Employee::class,
                ];
            });

        return response()->json([
            'message' => 'Daftar anggota berhasil diambil.',
            'data' => [
                'supervisors' => $supervisors,
                'employees' => $employees,
            ],
        ]);
    }

    /**
     * Membuat tim baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:teams,name',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'supervisor_id' => [
                'required',
                'integer',
                'exists:supervisors,id',
            ],

            'employee_ids' => [
                'nullable',
                'array',
            ],

            'employee_ids.*' => [
                'integer',
                'exists:employees,id',
            ],
        ]);

        $team = DB::transaction(function () use ($validated) {

            $team = Team::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
                'is_active' => true,
            ]);

            /*
             * Supervisor menjadi anggota tim.
             */
            TeamMember::create([
                'team_id' => $team->id,
                'member_id' => $validated['supervisor_id'],
                'member_type' => Supervisor::class,
            ]);

            /*
             * Tambahkan Employee ke tim.
             */
            foreach ($validated['employee_ids'] ?? [] as $employeeId) {
                TeamMember::create([
                    'team_id' => $team->id,
                    'member_id' => $employeeId,
                    'member_type' => Employee::class,
                ]);
            }

            return $team;
        });

        $team->load([
            'creator:id,name,email',
            'members.member',
        ]);

        return response()->json([
            'message' => 'Tim berhasil dibuat.',
            'data' => $team,
        ], 201);
    }

    /**
     * Detail tim.
     */
    public function show($id)
    {
        $team = Team::with([
            'creator:id,name,email',
            'members.member',
            'taskAssignments',
        ])->find($id);

        if (!$team) {
            return response()->json([
                'message' => 'Tim tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail tim berhasil diambil.',
            'data' => $team,
        ]);
    }

    /**
     * Update tim dan anggotanya.
     */
    public function update(Request $request, $id)
    {
        $team = Team::find($id);

        if (!$team) {
            return response()->json([
                'message' => 'Tim tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')
                    ->ignore($team->id),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'supervisor_id' => [
                'required',
                'integer',
                'exists:supervisors,id',
            ],

            'employee_ids' => [
                'nullable',
                'array',
            ],

            'employee_ids.*' => [
                'integer',
                'exists:employees,id',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        DB::transaction(function () use ($team, $validated) {

            $team->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? $team->is_active,
            ]);

            /*
             * Hapus anggota lama.
             */
            $team->members()->delete();

            /*
             * Tambahkan Supervisor baru.
             */
            TeamMember::create([
                'team_id' => $team->id,
                'member_id' => $validated['supervisor_id'],
                'member_type' => Supervisor::class,
            ]);

            /*
             * Tambahkan Employee baru.
             */
            foreach ($validated['employee_ids'] ?? [] as $employeeId) {
                TeamMember::create([
                    'team_id' => $team->id,
                    'member_id' => $employeeId,
                    'member_type' => Employee::class,
                ]);
            }
        });

        $team->load([
            'creator:id,name,email',
            'members.member',
        ]);

        return response()->json([
            'message' => 'Tim berhasil diperbarui.',
            'data' => $team,
        ]);
    }

    /**
     * Menghapus tim.
     */
    public function destroy($id)
    {
        $team = Team::find($id);

        if (!$team) {
            return response()->json([
                'message' => 'Tim tidak ditemukan.',
            ], 404);
        }

        $team->delete();

        return response()->json([
            'message' => 'Tim berhasil dihapus.',
        ]);
    }
}
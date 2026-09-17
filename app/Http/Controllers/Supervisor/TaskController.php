<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Models\TaskAssignment;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Tugas saya sebagai Supervisor.
     */
    public function myTasks(Request $request)
    {
        $supervisor = auth()->user();

        $teamIds = TeamMember::where(
            'member_type',
            Supervisor::class
        )
            ->where(
                'member_id',
                $supervisor->id
            )
            ->pluck('team_id');

        $query = TaskAssignment::with([
            'assigner:id,name,email',
            'team',
        ])
            ->whereIn('team_id', $teamIds);

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        $tasks = $query
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Tugas saya berhasil diambil.',
            'data' => $tasks,
        ]);
    }

    /**
     * Detail tugas.
     */
    public function show($id)
    {
        $supervisor = auth()->user();

        $teamIds = TeamMember::where(
            'member_type',
            Supervisor::class
        )
            ->where(
                'member_id',
                $supervisor->id
            )
            ->pluck('team_id');

        $task = TaskAssignment::with([
            'assigner:id,name,email',
            'team.members.member',
        ])
            ->whereIn('team_id', $teamIds)
            ->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail tugas berhasil diambil.',
            'data' => $task,
        ]);
    }

    /**
     * Update status tugas.
     */
    public function updateStatus(Request $request, $id)
    {
        $supervisor = auth()->user();

        $teamIds = TeamMember::where(
            'member_type',
            Supervisor::class
        )
            ->where(
                'member_id',
                $supervisor->id
            )
            ->pluck('team_id');

        $task = TaskAssignment::whereIn(
            'team_id',
            $teamIds
        )->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,in_progress,completed,cancelled',
            ],

            'completion_note' => [
                'nullable',
                'string',
            ],
        ]);

        $task->status = $validated['status'];

        if ($validated['status'] === 'completed') {
            $task->completed_at = now();
            $task->completion_note =
                $validated['completion_note'] ?? null;
        } else {
            $task->completed_at = null;
            $task->completion_note = null;
        }

        $task->save();

        return response()->json([
            'message' => 'Status tugas berhasil diperbarui.',
            'data' => $task,
        ]);
    }

    /**
     * Melihat anggota tim Supervisor.
     *
     * Ini nantinya bisa dipakai untuk:
     * - Target
     * - Penilaian
     * - Monitoring anggota
     */
    public function myTeamMembers()
    {
        $supervisor = auth()->user();

        $teamMembers = TeamMember::with([
            'team',
            'member',
        ])
            ->where(
                'member_type',
                Supervisor::class
            )
            ->where(
                'member_id',
                $supervisor->id
            )
            ->get();

        return response()->json([
            'message' => 'Anggota tim berhasil diambil.',
            'data' => $teamMembers,
        ]);
    }
}
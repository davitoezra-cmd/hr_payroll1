<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Tugas saya sebagai Employee.
     */
    public function myTasks(Request $request)
    {
        $employee = auth()->user();

        $teamIds = TeamMember::where(
            'member_type',
            Employee::class
        )
            ->where(
                'member_id',
                $employee->id
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
        $employee = auth()->user();

        $teamIds = TeamMember::where(
            'member_type',
            Employee::class
        )
            ->where(
                'member_id',
                $employee->id
            )
            ->pluck('team_id');

        $task = TaskAssignment::with([
            'assigner:id,name,email',
            'team',
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
        $employee = auth()->user();

        $teamIds = TeamMember::where(
            'member_type',
            Employee::class
        )
            ->where(
                'member_id',
                $employee->id
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
                'in:pending,in_progress,completed',
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
}
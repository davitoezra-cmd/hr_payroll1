<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskAssignment;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskAssignmentController extends Controller
{
    /**
     * Semua penugasan.
     */
    public function index(Request $request)
    {
        $query = TaskAssignment::with([
            'assigner:id,name,email',
            'team',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        $tasks = $query
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Data penugasan berhasil diambil.',
            'data' => $tasks,
        ]);
    }

    /**
     * Daftar tim yang dapat menerima tugas.
     */
    public function availableTeams()
    {
        $teams = Team::where('is_active', true)
            ->withCount('members')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'description',
            ]);

        return response()->json([
            'message' => 'Daftar tim berhasil diambil.',
            'data' => $teams,
        ]);
    }

    /**
     * Membuat penugasan baru untuk sebuah tim.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => [
                'required',
                'integer',
                'exists:teams,id',
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

           

            'deadline' => [
                'nullable',
                'date',
            ],
        ]);

        $team = Team::where('is_active', true)
            ->find($validated['team_id']);

        if (!$team) {
            return response()->json([
                'message' => 'Tim tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        $task = TaskAssignment::create([
            'assigned_by' => auth()->id(),
            'team_id' => $team->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
            'deadline' => $validated['deadline'] ?? null,
        ]);

        $task->load([
            'assigner:id,name,email',
            'team',
        ]);

        return response()->json([
            'message' => 'Penugasan berhasil dibuat.',
            'data' => $task,
        ], 201);
    }

    /**
     * Detail penugasan.
     */
    public function show($id)
    {
        $task = TaskAssignment::with([
            'assigner:id,name,email',
            'team.members.member',
        ])->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Penugasan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail penugasan berhasil diambil.',
            'data' => $task,
        ]);
    }

    /**
     * Update penugasan.
     */
    public function update(Request $request, $id)
    {
        $task = TaskAssignment::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Penugasan tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'team_id' => [
                'required',
                'integer',
                'exists:teams,id',
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

            

            'status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'in_progress',
                    'completed',
                    'cancelled',
                ]),
            ],

            'deadline' => [
                'nullable',
                'date',
            ],
        ]);

        $team = Team::where('is_active', true)
            ->find($validated['team_id']);

        if (!$team) {
            return response()->json([
                'message' => 'Tim tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        $task->update([
            'team_id' => $team->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? $task->status,
            'deadline' => $validated['deadline'] ?? null,
        ]);

        if ($task->status === 'completed') {
            if (!$task->completed_at) {
                $task->completed_at = now();
            }
        } else {
            $task->completed_at = null;
        }

        $task->save();

        $task->load([
            'assigner:id,name,email',
            'team',
        ]);

        return response()->json([
            'message' => 'Penugasan berhasil diperbarui.',
            'data' => $task,
        ]);
    }

    /**
     * Hapus penugasan.
     */
    public function destroy($id)
    {
        $task = TaskAssignment::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Penugasan tidak ditemukan.',
            ], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 'Penugasan berhasil dihapus.',
        ]);
    }
}
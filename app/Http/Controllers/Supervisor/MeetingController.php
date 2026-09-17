<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Supervisor;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    /**
     * Menampilkan semua rapat yang diikuti Supervisor.
     */
    public function index(Request $request)
    {
        $supervisor = $request->user();

        // Pastikan yang login memang Supervisor
        if (!$supervisor instanceof Supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk Supervisor.',
            ], 403);
        }

        $meetings = Meeting::with([
            'creator',
            'participants.participant',
        ])
        ->whereHas('participants', function ($query) use ($supervisor) {
            $query->where('participant_id', $supervisor->id)
                ->where(
                    'participant_type',
                    Supervisor::class
                );
        })
        ->latest('meeting_date')
        ->latest('start_time')
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data rapat berhasil diambil.',
            'data' => $meetings,
        ]);
    }

    /**
     * Menampilkan detail rapat yang diikuti Supervisor.
     */
    public function show(Request $request, Meeting $meeting)
    {
        $supervisor = $request->user();

        // Pastikan yang login memang Supervisor
        if (!$supervisor instanceof Supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk Supervisor.',
            ], 403);
        }

        /*
         * Cek apakah Supervisor merupakan peserta
         * dari rapat tersebut.
         */
        $isParticipant = $meeting->participants()
            ->where('participant_id', $supervisor->id)
            ->where(
                'participant_type',
                Supervisor::class
            )
            ->exists();

        if (!$isParticipant) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan peserta rapat ini.',
            ], 403);
        }

        $meeting->load([
            'creator',
            'participants.participant',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail rapat berhasil diambil.',
            'data' => $meeting,
        ]);
    }
}
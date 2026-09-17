<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    /**
     * Menampilkan semua rapat yang diikuti Finance.
     */
    public function index(Request $request)
    {
        $finance = $request->user();

        // Pastikan yang login adalah Finance
        if (!$finance instanceof Finance) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk Finance.',
            ], 403);
        }

        $meetings = Meeting::with([
            'creator',
            'participants.participant',
        ])
        ->whereHas('participants', function ($query) use ($finance) {
            $query->where('participant_id', $finance->id)
                ->where(
                    'participant_type',
                    Finance::class
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
     * Menampilkan detail rapat yang diikuti Finance.
     */
    public function show(Request $request, Meeting $meeting)
    {
        $finance = $request->user();

        // Pastikan yang login adalah Finance
        if (!$finance instanceof Finance) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk Finance.',
            ], 403);
        }

        /*
         * Pastikan Finance memang peserta
         * dari rapat tersebut.
         */
        $isParticipant = $meeting->participants()
            ->where('participant_id', $finance->id)
            ->where(
                'participant_type',
                Finance::class
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
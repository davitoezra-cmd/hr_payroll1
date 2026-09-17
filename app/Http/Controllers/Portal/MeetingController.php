<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    /**
     * Menampilkan semua rapat yang diikuti Employee.
     */
    public function index(Request $request)
    {
        $employee = $request->user();

        // Pastikan yang login adalah Employee
        if (!$employee instanceof Employee) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk Employee.',
            ], 403);
        }

        $meetings = Meeting::with([
            'creator',
            'participants.participant',
        ])
        ->whereHas('participants', function ($query) use ($employee) {
            $query->where('participant_id', $employee->id)
                ->where(
                    'participant_type',
                    Employee::class
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
     * Menampilkan detail rapat yang diikuti Employee.
     */
    public function show(Request $request, Meeting $meeting)
    {
        $employee = $request->user();

        // Pastikan yang login adalah Employee
        if (!$employee instanceof Employee) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk Employee.',
            ], 403);
        }

        /*
         * Pastikan Employee memang peserta
         * dari rapat tersebut.
         */
        $isParticipant = $meeting->participants()
            ->where('participant_id', $employee->id)
            ->where(
                'participant_type',
                Employee::class
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
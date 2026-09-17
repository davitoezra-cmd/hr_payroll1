<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Employee;
use App\Models\Supervisor;
use App\Models\Finance;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MeetingController extends Controller
{
    /**
     * Menampilkan semua rapat.
     */
    public function index()
    {
        $meetings = Meeting::with([
            'creator',
            'participants.participant',
        ])
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
     * Membuat rapat baru.
     */
    public function store(
        Request $request,
        WhatsAppService $whatsappService
    ) {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'meeting_date' => [
                'required',
                'date',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'nullable',
                'date_format:H:i',
                'after:start_time',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'agenda' => [
                'nullable',
                'string',
            ],

            'minutes' => [
                'nullable',
                'string',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'scheduled',
                    'completed',
                    'cancelled',
                ]),
            ],

            'participants' => [
                'nullable',
                'array',
            ],

            'participants.*.id' => [
                'required',
                'integer',
            ],

            'participants.*.type' => [
                'required',
                Rule::in([
                    'employee',
                    'supervisor',
                    'finance',
                ]),
            ],
        ]);

        DB::beginTransaction();

        try {
            $meeting = Meeting::create([
                'title' => $validated['title'],
                'meeting_date' => $validated['meeting_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'] ?? null,
                'location' => $validated['location'] ?? null,
                'created_by' => auth()->id(),
                'agenda' => $validated['agenda'] ?? null,
                'minutes' => $validated['minutes'] ?? null,
                'status' => $validated['status'] ?? 'scheduled',
            ]);

            if (!empty($validated['participants'])) {
                foreach ($validated['participants'] as $participant) {
                    $modelClass = $this->getParticipantModel(
                        $participant['type']
                    );

                    $modelClass::findOrFail($participant['id']);

                    MeetingParticipant::create([
                        'meeting_id' => $meeting->id,
                        'participant_id' => $participant['id'],
                        'participant_type' => $modelClass,
                    ]);
                }
            }

            DB::commit();

            /*
             * Load data rapat dan peserta setelah transaksi berhasil.
             */
            $meeting->load([
                'creator',
                'participants.participant',
            ]);

            /*
             * Kirim reminder WhatsApp setelah DB::commit().
             *
             * Jika WhatsApp gagal, data rapat tetap tersimpan.
             */
            $whatsappResult = $this->sendMeetingWhatsAppReminder(
                $meeting,
                $whatsappService
            );

            return response()->json([
                'success' => true,
                'message' => 'Rapat berhasil dibuat.',
                'data' => $meeting,
                'whatsapp' => $whatsappResult,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat rapat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengirim reminder WhatsApp ke peserta rapat.
     */
    private function sendMeetingWhatsAppReminder(
        Meeting $meeting,
        WhatsAppService $whatsappService
    ): array {
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($meeting->participants as $meetingParticipant) {

            $participant = $meetingParticipant->participant;

            /*
             * Kalau peserta tidak ditemukan, lewati.
             */
            if (!$participant) {
                $skipped++;

                continue;
            }

            /*
             * Employee, Supervisor, dan Finance
             * menggunakan field phone.
             */
            $phone = $participant->phone ?? null;

            /*
             * Kalau nomor kosong, jangan kirim.
             */
            if (empty($phone)) {
                $skipped++;

                Log::warning(
                    'Reminder WhatsApp rapat tidak dikirim karena nomor kosong.',
                    [
                        'meeting_id' => $meeting->id,
                        'participant_id' => $meetingParticipant->participant_id,
                        'participant_type' => $meetingParticipant->participant_type,
                    ]
                );

                continue;
            }

            /*
             * Nomor tidak perlu dinormalisasi di sini.
             *
             * WhatsAppService final yang menangani:
             * 08xxxxxxxx -> 628xxxxxxxx
             */
            $text = $this->buildMeetingWhatsAppMessage($meeting);

            $result = $whatsappService->send(
                $phone,
                $text
            );

            if ($result['success'] ?? false) {
                $sent++;
            } else {
                $failed++;

                Log::error(
                    'Reminder WhatsApp rapat gagal dikirim.',
                    [
                        'meeting_id' => $meeting->id,
                        'participant_id' => $meetingParticipant->participant_id,
                        'phone' => $phone,
                        'result' => $result,
                    ]
                );
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'skipped' => $skipped,
        ];
    }

    /**
     * Membuat isi pesan reminder WhatsApp.
     */
    private function buildMeetingWhatsAppMessage(Meeting $meeting): string
    {
        $date = $meeting->meeting_date
            ? $meeting->meeting_date->format('d-m-Y')
            : '-';

        $startTime = $meeting->start_time
            ? substr((string) $meeting->start_time, 0, 5)
            : '-';

        $endTime = $meeting->end_time
            ? substr((string) $meeting->end_time, 0, 5)
            : null;

        $time = $startTime;

        if ($endTime) {
            $time .= ' - ' . $endTime;
        }

        $message = "📢 *REMINDER RAPAT*\n\n";

        $message .= "Halo, Anda diundang untuk mengikuti rapat.\n\n";

        $message .= "📌 *Judul:* {$meeting->title}\n";
        $message .= "📅 *Tanggal:* {$date}\n";
        $message .= "⏰ *Waktu:* {$time}\n";

        if (!empty($meeting->location)) {
            $message .= "📍 *Lokasi:* {$meeting->location}\n";
        }

        if (!empty($meeting->agenda)) {
            $message .= "\n📝 *Agenda:*\n";
            $message .= $meeting->agenda . "\n";
        }

        $message .= "\nMohon hadir tepat waktu.\n";
        $message .= "\nTerima kasih.";

        return $message;
    }

    /**
     * Menampilkan detail rapat.
     */
    public function show(Meeting $meeting)
    {
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

    /**
     * Mengubah data rapat.
     */
    public function update(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'meeting_date' => [
                'required',
                'date',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'nullable',
                'date_format:H:i',
                'after:start_time',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'agenda' => [
                'nullable',
                'string',
            ],

            'minutes' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                Rule::in([
                    'scheduled',
                    'completed',
                    'cancelled',
                ]),
            ],

            'participants' => [
                'nullable',
                'array',
            ],

            'participants.*.id' => [
                'required',
                'integer',
            ],

            'participants.*.type' => [
                'required',
                Rule::in([
                    'employee',
                    'supervisor',
                    'finance',
                ]),
            ],
        ]);

        DB::beginTransaction();

        try {
            $meeting->update([
                'title' => $validated['title'],
                'meeting_date' => $validated['meeting_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'] ?? null,
                'location' => $validated['location'] ?? null,
                'agenda' => $validated['agenda'] ?? null,
                'minutes' => $validated['minutes'] ?? null,
                'status' => $validated['status'],
            ]);

            /*
             * Hapus peserta lama.
             */
            $meeting->participants()->delete();

            /*
             * Masukkan peserta baru.
             */
            if (!empty($validated['participants'])) {
                foreach ($validated['participants'] as $participant) {
                    $modelClass = $this->getParticipantModel(
                        $participant['type']
                    );

                    $modelClass::findOrFail($participant['id']);

                    MeetingParticipant::create([
                        'meeting_id' => $meeting->id,
                        'participant_id' => $participant['id'],
                        'participant_type' => $modelClass,
                    ]);
                }
            }

            DB::commit();

            $meeting->load([
                'creator',
                'participants.participant',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rapat berhasil diperbarui.',
                'data' => $meeting,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui rapat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus rapat.
     */
    public function destroy(Meeting $meeting)
    {
        try {
            $meeting->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rapat berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus rapat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil daftar calon peserta rapat.
     */
    public function availableParticipants()
    {
        $employees = Employee::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $supervisors = Supervisor::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $finances = Finance::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data peserta rapat berhasil diambil.',
            'data' => [
                'employees' => $employees,
                'supervisors' => $supervisors,
                'finances' => $finances,
            ],
        ]);
    }

    /**
     * Menentukan model berdasarkan tipe peserta.
     */
    private function getParticipantModel(string $type): string
    {
        return match ($type) {
            'employee' => \App\Models\Employee::class,
            'supervisor' => \App\Models\Supervisor::class,
            'finance' => \App\Models\Finance::class,

            default => throw new \InvalidArgumentException(
                'Tipe peserta tidak valid.'
            ),
        };
    }
}
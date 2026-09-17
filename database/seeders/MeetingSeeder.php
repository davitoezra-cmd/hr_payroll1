<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Finance;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeetingSeeder extends Seeder
{
    /**
     * Seed data meeting.
     */
    public function run(): void
    {
        DB::transaction(function () {

            // =====================================================
            // AMBIL USER PEMBUAT MEETING
            // =====================================================

            $creator = User::query()->first();

            if (!$creator) {
                $this->command->warn(
                    'MeetingSeeder dibatalkan: belum ada data User.'
                );

                return;
            }

            // =====================================================
            // AMBIL DATA PESERTA
            // =====================================================

            $employees = Employee::query()
                ->limit(5)
                ->get();

            $supervisors = Supervisor::query()
                ->limit(2)
                ->get();

            $finances = Finance::query()
                ->limit(2)
                ->get();

            // =====================================================
            // HAPUS DATA SEEDER SEBELUMNYA
            // =====================================================

            Meeting::query()
                ->where('title', 'like', '[SEED] %')
                ->each(function ($meeting) {
                    $meeting->participants()->delete();
                    $meeting->delete();
                });

            // =====================================================
            // MEETING 1
            // =====================================================

            $meeting1 = Meeting::create([
                'title' => '[SEED] Meeting Payroll Bulanan',
                'meeting_date' => now()->addDays(2)->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '10:30',
                'location' => 'Ruang Meeting Utama',
                'created_by' => $creator->id,
                'agenda' => 'Pembahasan payroll dan evaluasi pembayaran gaji karyawan.',
                'minutes' => null,
                'status' => 'scheduled',
            ]);

            $this->addParticipants(
                $meeting1,
                $employees,
                $supervisors,
                $finances
            );

            // =====================================================
            // MEETING 2
            // =====================================================

            $meeting2 = Meeting::create([
                'title' => '[SEED] Evaluasi Absensi Karyawan',
                'meeting_date' => now()->addDays(5)->format('Y-m-d'),
                'start_time' => '13:00',
                'end_time' => '14:00',
                'location' => 'Ruang HR',
                'created_by' => $creator->id,
                'agenda' => 'Evaluasi kehadiran, keterlambatan, dan absensi karyawan.',
                'minutes' => null,
                'status' => 'scheduled',
            ]);

            $this->addParticipants(
                $meeting2,
                $employees,
                $supervisors,
                $finances
            );

            // =====================================================
            // MEETING 3
            // =====================================================

            $meeting3 = Meeting::create([
                'title' => '[SEED] Rapat Evaluasi Kinerja',
                'meeting_date' => now()->subDays(3)->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '11:30',
                'location' => 'Ruang Supervisor',
                'created_by' => $creator->id,
                'agenda' => 'Evaluasi target dan performa karyawan.',
                'minutes' => 'Rapat telah selesai. Target dan performa karyawan telah dievaluasi.',
                'status' => 'completed',
            ]);

            $this->addParticipants(
                $meeting3,
                $employees,
                $supervisors,
                $finances
            );

            // =====================================================
            // MEETING 4
            // =====================================================

            $meeting4 = Meeting::create([
                'title' => '[SEED] Rapat Koordinasi Finance',
                'meeting_date' => now()->addDays(8)->format('Y-m-d'),
                'start_time' => '14:00',
                'end_time' => '15:30',
                'location' => 'Ruang Finance',
                'created_by' => $creator->id,
                'agenda' => 'Koordinasi keuangan dan laporan payroll.',
                'minutes' => null,
                'status' => 'scheduled',
            ]);

            $this->addParticipants(
                $meeting4,
                $employees,
                $supervisors,
                $finances
            );

            // =====================================================
            // MEETING 5
            // =====================================================

            $meeting5 = Meeting::create([
                'title' => '[SEED] Meeting Dinas Luar',
                'meeting_date' => now()->addDays(12)->format('Y-m-d'),
                'start_time' => '09:30',
                'end_time' => '11:00',
                'location' => 'Ruang Meeting 2',
                'created_by' => $creator->id,
                'agenda' => 'Persiapan dan koordinasi kegiatan dinas luar.',
                'minutes' => null,
                'status' => 'scheduled',
            ]);

            $this->addParticipants(
                $meeting5,
                $employees,
                $supervisors,
                $finances
            );

            // =====================================================
            // MEETING 6
            // =====================================================

            $meeting6 = Meeting::create([
                'title' => '[SEED] Meeting yang Dibatalkan',
                'meeting_date' => now()->addDays(15)->format('Y-m-d'),
                'start_time' => '15:00',
                'end_time' => '16:00',
                'location' => 'Ruang Meeting Utama',
                'created_by' => $creator->id,
                'agenda' => 'Meeting koordinasi internal.',
                'minutes' => null,
                'status' => 'cancelled',
            ]);

            $this->addParticipants(
                $meeting6,
                $employees,
                $supervisors,
                $finances
            );
        });

        $this->command->info(
            'MeetingSeeder berhasil membuat data meeting beserta peserta.'
        );
    }

    /**
     * =========================================================
     * MENAMBAHKAN PESERTA KE MEETING
     * =========================================================
     */
    private function addParticipants(
        Meeting $meeting,
        $employees,
        $supervisors,
        $finances
    ): void {

        // =====================================================
        // EMPLOYEE
        // =====================================================

        foreach ($employees->take(3) as $employee) {

            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'participant_id' => $employee->id,
                'participant_type' => Employee::class,
            ]);
        }

        // =====================================================
        // SUPERVISOR
        // =====================================================

        foreach ($supervisors->take(1) as $supervisor) {

            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'participant_id' => $supervisor->id,
                'participant_type' => Supervisor::class,
            ]);
        }

        // =====================================================
        // FINANCE
        // =====================================================

        foreach ($finances->take(1) as $finance) {

            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'participant_id' => $finance->id,
                'participant_type' => Finance::class,
            ]);
        }
    }
}
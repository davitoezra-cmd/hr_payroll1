<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\Team;
use Carbon\Carbon;

class TaskAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER YANG MEMBERIKAN TUGAS
        |--------------------------------------------------------------------------
        */
        $user = User::first();

        if (!$user) {
            $this->command->warn(
                'Seeder TaskAssignment dilewati: tabel users masih kosong.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | AMBIL TEAM
        |--------------------------------------------------------------------------
        */
        $teams = Team::all();

        if ($teams->isEmpty()) {
            $this->command->warn(
                'Seeder TaskAssignment dilewati: tabel teams masih kosong.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | HAPUS DATA TASK LAMA
        |--------------------------------------------------------------------------
        */
        TaskAssignment::query()->delete();

        /*
        |--------------------------------------------------------------------------
        | DATA TUGAS UNTUK TEKNISI
        |--------------------------------------------------------------------------
        */
        $tasks = [
            [
                'title' => 'Pemeriksaan dan Perawatan Mesin',
                'description' =>
                    'Melakukan pemeriksaan kondisi mesin secara menyeluruh dan memastikan mesin bekerja dengan normal.',
                'status' => 'pending',
                'deadline' => Carbon::now()->addDays(2),
                'completed_at' => null,
                'completion_note' => null,
            ],

            [
                'title' => 'Perbaikan Peralatan Rusak',
                'description' =>
                    'Melakukan pemeriksaan dan perbaikan terhadap peralatan yang mengalami kerusakan agar dapat digunakan kembali.',
                'status' => 'in_progress',
                'deadline' => Carbon::now()->addDays(1),
                'completed_at' => null,
                'completion_note' => null,
            ],

            [
                'title' => 'Pengecekan Instalasi Listrik',
                'description' =>
                    'Memeriksa kondisi instalasi listrik, kabel, panel, dan koneksi untuk memastikan keamanan dan fungsi peralatan.',
                'status' => 'pending',
                'deadline' => Carbon::now()->addDays(3),
                'completed_at' => null,
                'completion_note' => null,
            ],

            [
                'title' => 'Maintenance Peralatan Kantor',
                'description' =>
                    'Melakukan maintenance dan pengecekan rutin terhadap peralatan kantor yang membutuhkan perawatan teknis.',
                'status' => 'completed',
                'deadline' => Carbon::now()->subDays(2),
                'completed_at' => Carbon::now()->subDays(1),
                'completion_note' =>
                    'Maintenance peralatan kantor telah selesai dilakukan.',
            ],

            [
                'title' => 'Pengecekan Jaringan dan Internet',
                'description' =>
                    'Memeriksa koneksi jaringan, perangkat jaringan, dan memastikan koneksi internet dapat digunakan dengan baik.',
                'status' => 'in_progress',
                'deadline' => Carbon::now()->addDays(1),
                'completed_at' => null,
                'completion_note' => null,
            ],

            [
                'title' => 'Instalasi Perangkat Baru',
                'description' =>
                    'Melakukan pemasangan dan konfigurasi perangkat baru sesuai kebutuhan operasional.',
                'status' => 'pending',
                'deadline' => Carbon::now()->addDays(4),
                'completed_at' => null,
                'completion_note' => null,
            ],

            [
                'title' => 'Pemeriksaan Sistem Pendingin',
                'description' =>
                    'Melakukan pengecekan kondisi sistem pendingin dan memastikan temperatur peralatan tetap berada pada kondisi normal.',
                'status' => 'pending',
                'deadline' => Carbon::now()->addDays(5),
                'completed_at' => null,
                'completion_note' => null,
            ],

            [
                'title' => 'Perbaikan Gangguan Peralatan',
                'description' =>
                    'Menangani laporan gangguan peralatan dan melakukan troubleshooting sampai peralatan kembali berfungsi.',
                'status' => 'completed',
                'deadline' => Carbon::now()->subDays(3),
                'completed_at' => Carbon::now()->subDays(2),
                'completion_note' =>
                    'Gangguan berhasil diperbaiki dan peralatan sudah kembali berfungsi.',
            ],

            [
                'title' => 'Pengecekan Panel dan Komponen',
                'description' =>
                    'Melakukan pemeriksaan panel, komponen, konektor, dan bagian teknis lainnya untuk mencegah kerusakan.',
                'status' => 'in_progress',
                'deadline' => Carbon::now()->addDays(2),
                'completed_at' => null,
                'completion_note' => null,
            ],

            [
                'title' => 'Maintenance Rutin Mingguan',
                'description' =>
                    'Melaksanakan maintenance rutin terhadap seluruh peralatan yang menjadi tanggung jawab tim teknisi.',
                'status' => 'pending',
                'deadline' => Carbon::now()->addDays(6),
                'completed_at' => null,
                'completion_note' => null,
            ],

            [
                'title' => 'Pengecekan Peralatan Sebelum Operasional',
                'description' =>
                    'Memastikan seluruh peralatan dalam kondisi baik dan aman sebelum digunakan untuk kegiatan operasional.',
                'status' => 'completed',
                'deadline' => Carbon::now()->subDays(1),
                'completed_at' => Carbon::now(),
                'completion_note' =>
                    'Seluruh peralatan telah diperiksa dan dinyatakan siap digunakan.',
            ],

            [
                'title' => 'Troubleshooting Perangkat',
                'description' =>
                    'Melakukan identifikasi penyebab masalah pada perangkat dan menentukan tindakan perbaikan yang diperlukan.',
                'status' => 'pending',
                'deadline' => Carbon::now()->addDays(3),
                'completed_at' => null,
                'completion_note' => null,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | BUAT TASK
        |--------------------------------------------------------------------------
        */
        foreach ($tasks as $index => $task) {

            /*
            |--------------------------------------------------------------------------
            | Membagikan tugas ke team secara bergantian
            |--------------------------------------------------------------------------
            */
            $team = $teams[$index % $teams->count()];

            TaskAssignment::create([
                'assigned_by' => $user->id,
                'team_id' => $team->id,

                'title' => $task['title'],

                'description' => $task['description'],

                'status' => $task['status'],

                'deadline' => $task['deadline'],

                'completed_at' => $task['completed_at'],

                'completion_note' => $task['completion_note'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SELESAI
        |--------------------------------------------------------------------------
        */
        $this->command->info(
            count($tasks) . ' tugas teknisi berhasil dibuat.'
        );
    }
}
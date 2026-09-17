<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Supervisor;
use App\Models\Employee;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL SUPERVISOR DAN EMPLOYEE
        |--------------------------------------------------------------------------
        */

        $supervisors = Supervisor::query()->get();
        $employees = Employee::query()->get();

        if ($supervisors->isEmpty()) {
            $this->command->warn(
                'TeamSeeder dilewati: data supervisor belum tersedia.'
            );

            return;
        }

        if ($employees->isEmpty()) {
            $this->command->warn(
                'TeamSeeder dilewati: data employee belum tersedia.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | AMBIL USER PEMBUAT TEAM
        |--------------------------------------------------------------------------
        */

        $user = User::first();

        if (!$user) {
            $this->command->warn(
                'TeamSeeder dilewati: tabel users masih kosong.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | HAPUS DATA TEAM LAMA
        |--------------------------------------------------------------------------
        */

        TeamMember::query()->delete();
        Team::query()->delete();

        /*
        |--------------------------------------------------------------------------
        | NAMA TEAM
        |--------------------------------------------------------------------------
        */

        $teams = [
            'Team Teknisi 1',
            'Team Teknisi 2',
            'Team Teknisi 3',
            'Team Teknisi 4',
            'Team Teknisi 5',
        ];

        /*
        |--------------------------------------------------------------------------
        | BUAT TEAM + MEMBER
        |--------------------------------------------------------------------------
        */

        foreach ($teams as $index => $teamName) {

            $team = Team::create([
                'name' => $teamName,
                'created_by' => $user->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | SUPERVISOR
            |--------------------------------------------------------------------------
            |
            | Setiap team mendapatkan 1 supervisor.
            | Jika supervisor hanya ada beberapa, akan diputar kembali
            | menggunakan modulo.
            |
            */

            $supervisor = $supervisors[$index % $supervisors->count()];

            TeamMember::create([
                'team_id' => $team->id,
                'member_id' => $supervisor->id,
                'member_type' => Supervisor::class,
            ]);

            /*
            |--------------------------------------------------------------------------
            | EMPLOYEE
            |--------------------------------------------------------------------------
            |
            | Setiap team mendapatkan employee.
            | Dibagi berdasarkan urutan team.
            |
            */

            $employee = $employees[$index % $employees->count()];

            TeamMember::create([
                'team_id' => $team->id,
                'member_id' => $employee->id,
                'member_type' => Employee::class,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | INFORMASI HASIL SEEDER
        |--------------------------------------------------------------------------
        */

        $this->command->info(
            count($teams) . ' team berhasil dibuat.'
        );

        $this->command->info(
            'Supervisor yang tersedia: ' . $supervisors->count()
        );

        $this->command->info(
            'Employee yang tersedia: ' . $employees->count()
        );

        $this->command->info(
            'Total member team: ' . TeamMember::count()
        );
    }
}
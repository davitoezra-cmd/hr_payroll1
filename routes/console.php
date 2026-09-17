<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('face:consolidate-identities', function () {
    if (! \Illuminate\Support\Facades\Schema::hasTable('face_identities')
        || ! \Illuminate\Support\Facades\Schema::hasTable('face_identity_accounts')) {
        $this->error('Tabel Face Identity belum tersedia. Jalankan: php artisan migrate');
        return 1;
    }

    /** @var \App\Services\FaceIdentityManager $manager */
    $manager = app(\App\Services\FaceIdentityManager::class);

    $definitions = [
        [
            'label' => 'Employee',
            'table' => 'employee_face_templates',
            'model' => \App\Models\EmployeeFaceTemplate::class,
            'relation' => 'employee',
        ],
        [
            'label' => 'Supervisor',
            'table' => 'supervisor_face_templates',
            'model' => \App\Models\SupervisorFaceTemplate::class,
            'relation' => 'supervisor',
        ],
        [
            'label' => 'Finance',
            'table' => 'finance_face_templates',
            'model' => \App\Models\FinanceFaceTemplate::class,
            'relation' => 'finance',
        ],
        [
            'label' => 'Administrator',
            'table' => 'user_face_templates',
            'model' => \App\Models\UserFaceTemplate::class,
            'relation' => 'user',
        ],
    ];

    $stats = [
        'processed' => 0,
        'created' => 0,
        'linked' => 0,
        'already' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    $this->info('Mengonsolidasikan template wajah lama menjadi satu Face Identity per orang...');
    $this->line('Threshold penggabungan: '.config('services.face_api.identity_link_threshold', 0.60));

    foreach ($definitions as $definition) {
        if (! \Illuminate\Support\Facades\Schema::hasTable($definition['table'])) {
            $this->warn($definition['label'].': tabel legacy tidak ditemukan, dilewati.');
            continue;
        }

        $modelClass = $definition['model'];
        $relation = $definition['relation'];

        $templates = $modelClass::query()
            ->where('is_active', true)
            ->with($relation)
            ->get();

        foreach ($templates as $template) {
            $stats['processed']++;
            $account = $template->getRelation($relation);

            if (! $account) {
                $stats['skipped']++;
                $this->warn($definition['label'].' template #'.$template->getKey().': account tidak ditemukan.');
                continue;
            }

            try {
                $result = $manager->importLegacy($account, $template);
                $status = $result['status'] ?? 'unknown';

                if ($status === 'identity-created') {
                    $stats['created']++;
                } elseif ($status === 'identity-linked') {
                    $stats['linked']++;
                } elseif ($status === 'already-linked') {
                    $stats['already']++;
                } else {
                    $stats['skipped']++;
                }

                $this->line(sprintf(
                    '  [%s] %s #%s -> identity #%s%s',
                    $status,
                    $definition['label'],
                    $account->getKey(),
                    $result['identity_id'] ?? '-',
                    isset($result['similarity']) ? ' (similarity '.number_format((float) $result['similarity'], 4).')' : ''
                ));
            } catch (\Throwable $exception) {
                $stats['errors']++;
                $this->error(sprintf(
                    '  [error] %s #%s: %s',
                    $definition['label'],
                    $account->getKey(),
                    $exception->getMessage()
                ));
            }
        }
    }

    $this->newLine();
    $this->table(
        ['Processed', 'Identity Baru', 'Akun Digabung', 'Sudah Terhubung', 'Skipped', 'Error'],
        [[
            $stats['processed'],
            $stats['created'],
            $stats['linked'],
            $stats['already'],
            $stats['skipped'],
            $stats['errors'],
        ]]
    );

    if ($stats['errors'] > 0) {
        $this->warn('Ada template yang gagal dimigrasikan. Pastikan APP_KEY tidak berubah agar encrypted embedding lama dapat dibaca.');
        return 2;
    }

    $this->info('Konsolidasi Face Identity selesai.');
    return 0;
})->purpose('Gabungkan template wajah per-role menjadi satu Face Identity per orang');

<?php

namespace Database\Seeders;

use App\Models\CompanyPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanyPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil user pertama sebagai pembuat artikel
        |--------------------------------------------------------------------------
        |
        | Kalau tabel users sudah memiliki data, user pertama akan digunakan
        | sebagai created_by.
        |
        | Kalau belum ada user, created_by akan dibuat NULL.
        |
        */

        $user = User::first();

        /*
        |--------------------------------------------------------------------------
        | Data Company Posts
        |--------------------------------------------------------------------------
        */

        $posts = [
            [
                'title' => 'Pengumuman Jam Kerja Baru',
                'type' => 'announcement',
                'content' => <<<TEXT
Mulai bulan ini, perusahaan menerapkan penyesuaian jam kerja untuk seluruh karyawan.

Jam kerja:
Senin - Jumat
08:00 - 17:00 WIB

Karyawan diharapkan datang tepat waktu dan melakukan absensi sesuai dengan ketentuan perusahaan.

Terima kasih atas perhatian dan kerja samanya.
TEXT,
                'image' => null,
                'status' => 'published',
            ],

            [
                'title' => 'Update Sistem Absensi Karyawan',
                'type' => 'update',
                'content' => <<<TEXT
Sistem absensi karyawan telah mendapatkan pembaruan.

Beberapa peningkatan yang telah dilakukan antara lain:
- Perbaikan proses check-in.
- Perbaikan proses check-out.
- Peningkatan validasi lokasi.
- Peningkatan proses upload selfie.
- Perbaikan tampilan dashboard karyawan.

Silakan gunakan sistem absensi seperti biasa.
Jika menemukan kendala, segera laporkan kepada bagian HR atau administrator.
TEXT,
                'image' => null,
                'status' => 'published',
            ],

            [
                'title' => 'Tips Mengatur Keuangan dari Gaji Bulanan',
                'type' => 'article',
                'content' => <<<TEXT
Mengatur keuangan dengan baik dapat membantu karyawan mencapai kondisi finansial yang lebih sehat.

Beberapa tips sederhana yang dapat dilakukan:

1. Catat seluruh pemasukan dan pengeluaran.
2. Tentukan anggaran kebutuhan setiap bulan.
3. Sisihkan sebagian pendapatan untuk tabungan.
4. Hindari pengeluaran yang tidak diperlukan.
5. Siapkan dana darurat secara bertahap.

Dengan perencanaan yang baik, penghasilan bulanan dapat digunakan secara lebih efektif.
TEXT,
                'image' => null,
                'status' => 'published',
            ],

            [
                'title' => 'Informasi Pengajuan Cuti Karyawan',
                'type' => 'hr_info',
                'content' => <<<TEXT
Pengajuan cuti dapat dilakukan melalui sistem HR perusahaan.

Sebelum mengajukan cuti, karyawan diharapkan memastikan:
- Jumlah cuti yang tersedia.
- Tanggal cuti yang akan diajukan.
- Alasan pengajuan cuti.
- Persetujuan dari pihak yang berwenang.

Silakan mengajukan cuti melalui menu Cuti pada sistem HR Payroll.
TEXT,
                'image' => null,
                'status' => 'published',
            ],

            [
                'title' => 'Persiapan Evaluasi Kinerja Karyawan',
                'type' => 'hr_info',
                'content' => <<<TEXT
HR akan melakukan evaluasi kinerja karyawan secara berkala.

Karyawan diharapkan mempersiapkan informasi terkait pekerjaan, pencapaian, target, serta kendala yang dihadapi selama periode evaluasi.

Informasi jadwal evaluasi akan disampaikan lebih lanjut oleh bagian HR.

Harap mengikuti proses evaluasi sesuai jadwal yang telah ditentukan.
TEXT,
                'image' => null,
                'status' => 'draft',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Insert Data
        |--------------------------------------------------------------------------
        */

        foreach ($posts as $post) {
            CompanyPost::create([
                'title' => $post['title'],
                'type' => $post['type'],
                'content' => $post['content'],
                'image' => $post['image'],
                'status' => $post['status'],
                'created_by' => $user?->id,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Informasi Seeder
        |--------------------------------------------------------------------------
        */

        $this->command?->info('Company Post Seeder berhasil dijalankan.');
        $this->command?->info('Total data: ' . count($posts));
        $this->command?->info('Published: 4');
        $this->command?->info('Draft: 1');
    }
}
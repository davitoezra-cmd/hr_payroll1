<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollExpenseController extends Controller
{
    /**
     * =========================================================
     * PENGELUARAN TOTAL GAJI
     * =========================================================
     *
     * Menampilkan:
     * - Bulan
     * - Jumlah pegawai
     * - Total gaji
     *
     * Data dihitung berdasarkan payroll yang sudah PAID.
     *
     * Contoh:
     *
     * Januari | 20 pegawai | Rp60.000.000
     * Februari | 21 pegawai | Rp63.000.000
     *
     * Di bawahnya:
     * Total keseluruhan gaji selama tahun tersebut.
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Tahun
        |--------------------------------------------------------------------------
        */

        $tahun = (int) ($request->tahun ?? Carbon::now('Asia/Jakarta')->year);

        /*
        |--------------------------------------------------------------------------
        | Validasi tahun
        |--------------------------------------------------------------------------
        */

        if ($tahun < 2000 || $tahun > 2100) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun tidak valid.',
                'data' => null,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil payroll PAID
        |--------------------------------------------------------------------------
        |
        | Hanya payroll yang benar-benar sudah dibayarkan
        | yang dihitung sebagai pengeluaran gaji.
        |
        */

        $payrolls = Payroll::query()
            ->where('tahun', $tahun)
            ->where('status', 'paid')
            ->select([
                'id',
                'employee_id',
                'bulan',
                'tahun',
                'take_home_pay',
                'status',
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Rekap per bulan
        |--------------------------------------------------------------------------
        */

        $monthlyData = collect();

        for ($bulan = 1; $bulan <= 12; $bulan++) {

            $monthlyPayrolls = $payrolls->where(
                'bulan',
                $bulan
            );

            $jumlahPegawai = $monthlyPayrolls
                ->pluck('employee_id')
                ->unique()
                ->count();

            $totalGaji = $monthlyPayrolls->sum(
                fn ($payroll) =>
                    (float) $payroll->take_home_pay
            );

            $monthlyData->push([
                'bulan' => $bulan,

                'nama_bulan' => Carbon::create()
                    ->month($bulan)
                    ->locale('id')
                    ->translatedFormat('F'),

                'jumlah_pegawai' => $jumlahPegawai,

                'total_gaji' => round(
                    $totalGaji,
                    2
                ),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Total keseluruhan
        |--------------------------------------------------------------------------
        */

        $totalPegawai = $payrolls
            ->pluck('employee_id')
            ->unique()
            ->count();

        $totalGajiKeseluruhan = $payrolls->sum(
            fn ($payroll) =>
                (float) $payroll->take_home_pay
        );

        /*
        |--------------------------------------------------------------------------
        | Data untuk grafik
        |--------------------------------------------------------------------------
        */

        $chart = [
            'labels' => $monthlyData
                ->pluck('nama_bulan')
                ->values(),

            'data' => $monthlyData
                ->pluck('total_gaji')
                ->values(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' =>
                'Berhasil mengambil laporan pengeluaran total gaji.',

            'tahun' => $tahun,

            /*
            |--------------------------------------------------------------------------
            | TABEL
            |--------------------------------------------------------------------------
            */

            'data' => $monthlyData,

            /*
            |--------------------------------------------------------------------------
            | TOTAL KESELURUHAN
            |--------------------------------------------------------------------------
            */

            'summary' => [
                'tahun' => $tahun,

                'total_pegawai' =>
                    $totalPegawai,

                'total_gaji_keseluruhan' =>
                    round(
                        $totalGajiKeseluruhan,
                        2
                    ),
            ],

            /*
            |--------------------------------------------------------------------------
            | GRAFIK
            |--------------------------------------------------------------------------
            */

            'chart' => $chart,
        ], 200);
    }


    /**
     * =========================================================
     * DETAIL PENGELUARAN PER BULAN
     * =========================================================
     *
     * Opsional tetapi saya sarankan dibuat.
     *
     * Ketika admin klik Januari misalnya,
     * frontend bisa menampilkan siapa saja yang menerima gaji.
     */
    public function monthlyDetail(
        Request $request,
        $tahun,
        $bulan
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validasi bulan
        |--------------------------------------------------------------------------
        */

        if (
            !is_numeric($tahun) ||
            !is_numeric($bulan) ||
            $bulan < 1 ||
            $bulan > 12
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak valid.',
                'data' => null,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil payroll
        |--------------------------------------------------------------------------
        */

        $payrolls = Payroll::with('employee')
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('status', 'paid')
            ->orderBy('employee_id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        $totalGaji = $payrolls->sum(
            fn ($payroll) =>
                (float) $payroll->take_home_pay
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' =>
                'Berhasil mengambil detail pengeluaran gaji.',

            'periode' => [
                'tahun' => (int) $tahun,

                'bulan' => (int) $bulan,

                'nama_bulan' => Carbon::create(2000, (int) $bulan, 1)
                 ->locale('id')
                 ->translatedFormat('F'),
            ],

            'jumlah_pegawai' =>
                $payrolls
                    ->pluck('employee_id')
                    ->unique()
                    ->count(),

            'total_gaji' =>
                round(
                    $totalGaji,
                    2
                ),

            'data' => $payrolls,
        ], 200);
    }
}
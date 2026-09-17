<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\EmployeePayrollSetting;
use App\Models\LeaveRequest;
use App\Models\MedicalLeave;
use App\Models\MealAllowanceRequest;
use App\Models\Payroll;
use App\Models\EmployeeBalance;
use App\Models\BalanceTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    /**
     * =========================================================
     * INDEX
     * =========================================================
     */
    public function index(Request $request)
    {
        $query = Payroll::with('employee');

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $payrolls = $query
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar payroll.',
            'data' => $payrolls,
        ], 200);
    }

    /**
     * =========================================================
     * SHOW
     * =========================================================
     */
    public function show($id)
    {
        $payroll = Payroll::with('employee')->find($id);

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Data payroll tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil detail payroll.',
            'data' => $payroll,
        ], 200);
    }

    /**
     * =========================================================
     * GENERATE PAYROLL
     * =========================================================
     *
     * RULE:
     *
     * 1. Gaji dasar
     *    = total hadir x gaji harian
     *
     * 2. Bonus datang awal
     *    = bonus diberikan setiap hari jika datang
     *      sebelum jam mulai bonus datang.
     *
     * 3. Keterlambatan
     *    = dihitung berdasarkan MENIT keterlambatan
     *      setelah melewati batas toleransi dari work schedule.
     *
     *    Contoh:
     *
     *    Jadwal masuk : 08:00
     *    Toleransi    : 10 menit
     *
     *    Datang 08:05
     *    = tidak terlambat
     *
     *    Datang 08:10
     *    = tidak terlambat
     *
     *    Datang 08:11
     *    = terlambat 1 menit
     *
     *    Datang 08:20
     *    = terlambat 10 menit
     *
     * 4. Bonus kedisiplinan
     *    = diberikan 1x jika:
     *      - periode sudah selesai
     *      - employee memiliki attendance
     *      - tidak memiliki keterlambatan setelah toleransi
     *
     * 5. Uang makan
     *    = total nominal MealAllowanceRequest
     *      yang statusnya approved pada periode payroll.
     *
     *    Uang makan merupakan POTONGAN.
     *
     * 6. Take Home Pay
     *    = gaji dasar
     *      + bonus datang awal
     *      + bonus kedisiplinan
     *      + koreksi
     *      - denda terlambat
     *      - potongan izin
     *      - potongan cuti
     *      - uang makan
     *      - kasbon
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'data' => $validator->errors(),
            ], 422);
        }

        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;

        /**
         * =====================================================
         * PERIODE PAYROLL
         * =====================================================
         */
        $periodStartCarbon = Carbon::create(
            $tahun,
            $bulan,
            1,
            0,
            0,
            0,
            'Asia/Jakarta'
        )->startOfMonth();

        $periodEndCarbon = $periodStartCarbon
            ->copy()
            ->endOfMonth();

        $periodStart = $periodStartCarbon->toDateString();
        $periodEnd = $periodEndCarbon->toDateString();

        /**
         * =====================================================
         * CEK PERIODE SUDAH SELESAI
         * =====================================================
         */
        $today = Carbon::today('Asia/Jakarta');

        $periodFinished = $today->greaterThan(
            $periodEndCarbon->copy()->startOfDay()
        );

        /**
         * =====================================================
         * AMBIL EMPLOYEE PAYROLL SETTING
         * =====================================================
         */
        $settingsQuery = EmployeePayrollSetting::with('employee')
            ->where('aktif', true)
            ->whereHas('employee', function ($employeeQuery) use (
                $periodStart,
                $periodEnd
            ) {
                $employeeQuery->where(function ($eligibleEmployee) use (
                    $periodStart,
                    $periodEnd
                ) {
                    $eligibleEmployee
                        ->whereHas('employments', function (
                            $employmentQuery
                        ) use (
                            $periodStart,
                            $periodEnd
                        ) {
                            $employmentQuery
                                ->where(
                                    'current_status',
                                    '!=',
                                    'ONBOARDING'
                                )
                                ->whereDate(
                                    'start_date',
                                    '<=',
                                    $periodEnd
                                )
                                ->where(function ($periodQuery) use (
                                    $periodStart
                                ) {
                                    $periodQuery
                                        ->whereNull('end_date')
                                        ->orWhereDate(
                                            'end_date',
                                            '>=',
                                            $periodStart
                                        );
                                });
                        })
                        ->orWhere(function ($legacyEmployee) {
                            $legacyEmployee
                                ->where('is_active', true)
                                ->whereDoesntHave('employments');
                        });
                });
            });

        if ($request->filled('employee_id')) {
            $settingsQuery->where(
                'employee_id',
                $request->employee_id
            );
        }

        $settings = $settingsQuery->get();

        if ($settings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Tidak ada data pengaturan payroll karyawan yang aktif.',
                'data' => [],
            ], 404);
        }

        /**
         * =====================================================
         * CEK PAYROLL PAID
         * =====================================================
         */
        $employeeIds = $settings
            ->pluck('employee_id')
            ->unique()
            ->values();

        $paidPayroll = Payroll::with('employee')
            ->whereIn('employee_id', $employeeIds)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('status', 'paid')
            ->first();

        if ($paidPayroll) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Payroll periode ' .
                    str_pad(
                        $bulan,
                        2,
                        '0',
                        STR_PAD_LEFT
                    ) .
                    '/' .
                    $tahun .
                    ' untuk employee ' .
                    (
                        $paidPayroll->employee->name ??
                        $paidPayroll->employee_id
                    ) .
                    ' sudah dibayarkan dan tidak dapat di-generate ulang.',
                'data' => $paidPayroll,
            ], 422);
        }

        /**
         * =====================================================
         * MEDICAL LEAVE SCHEMA
         * =====================================================
         */
        $hasSickDateColumn = Schema::hasColumn(
            'medical_leaves',
            'sick_date'
        );

        DB::beginTransaction();

        try {

            $generatedPayrolls = [];

            foreach ($settings as $setting) {

                $employeeId = $setting->employee_id;

                /**
                 * =================================================
                 * 1. ATTENDANCE
                 * =================================================
                 */
                $attendances = Attendance::with(
                    'shiftSchedule.shift'
                )
                    ->where(
                        'employee_id',
                        $employeeId
                    )
                    ->whereMonth(
                        'attendance_date',
                        $bulan
                    )
                    ->whereYear(
                        'attendance_date',
                        $tahun
                    )
                    ->whereNotNull('check_in')
                    ->get();

                $totalHadir = $attendances->count();

                /**
                 * =================================================
                 * 2. TOTAL MENIT TERLAMBAT
                 * =================================================
                 *
                 * Keterlambatan dihitung berdasarkan:
                 *
                 * jam check-in
                 * dikurangi jam jadwal masuk
                 * dikurangi toleransi.
                 *
                 * Contoh:
                 *
                 * Jadwal     : 08:00
                 * Toleransi  : 10 menit
                 * Check-in   : 08:20
                 *
                 * Terlambat:
                 *
                 * 20 - 10 = 10 menit
                 *
                 * Jika check-in masih dalam toleransi:
                 *
                 * Jadwal     : 08:00
                 * Toleransi  : 10 menit
                 * Check-in   : 08:10
                 *
                 * Terlambat = 0 menit
                 */
                $totalMenitTerlambat = 0;

                $totalKejadianTerlambat = 0;

                foreach ($attendances as $attendance) {

                    if (!$attendance->check_in) {
                        continue;
                    }

                    /**
                     * Ambil jadwal masuk dari attendance.
                     *
                     * scheduled_check_in seharusnya sudah
                     * tersimpan ketika attendance dibuat.
                     */
                    $scheduledCheckIn =
                        $attendance->scheduled_check_in;

                    if (!$scheduledCheckIn) {

                        /**
                         * Kalau scheduled_check_in tidak tersedia,
                         * jangan menghitung denda supaya tidak salah.
                         */
                        continue;
                    }

                    $attendanceDate = Carbon::parse(
                        $attendance->attendance_date,
                        'Asia/Jakarta'
                    )->format('Y-m-d');

                    $scheduledDateTime = Carbon::parse(
                        $attendanceDate .
                        ' ' .
                        $scheduledCheckIn,
                        'Asia/Jakarta'
                    );

                    $actualCheckIn = Carbon::parse(
                        $attendanceDate .
                        ' ' .
                        $attendance->check_in,
                        'Asia/Jakarta'
                    );

                    /**
                     * Jika datang sebelum atau tepat pada
                     * jam masuk, tidak terlambat.
                     */
                    if (
                        $actualCheckIn->lessThanOrEqualTo(
                            $scheduledDateTime
                        )
                    ) {
                        continue;
                    }

                    /**
                     * Hitung selisih menit dari jam masuk.
                     */
                    $selisihMenit = $scheduledDateTime->diffInMinutes(
                        $actualCheckIn
                    );

                    /**
                     * Ambil toleransi dari attendance.
                     *
                     * Kalau null, gunakan 0.
                     */
                    $toleransi = (int) (
                        $attendance->late_tolerance_minutes ?? 0
                    );

                    /**
                     * Kurangi toleransi.
                     */
                    $menitTerlambat = max(
                        0,
                        $selisihMenit - $toleransi
                    );

                    /**
                     * Hanya dihitung sebagai terlambat
                     * kalau sudah melewati toleransi.
                     */
                    if ($menitTerlambat > 0) {

                        $totalMenitTerlambat +=
                            $menitTerlambat;

                        $totalKejadianTerlambat++;
                    }
                }

                /**
                 * Tetap simpan total terlambat sebagai
                 * jumlah kejadian yang benar-benar melewati
                 * batas toleransi.
                 *
                 * Nilai ini berguna untuk bonus kedisiplinan
                 * dan informasi payroll.
                 */
                $totalTerlambat =
                    $totalKejadianTerlambat;

                /**
                 * =================================================
                 * 3. BONUS DATANG AWAL
                 * =================================================
                 */
                $bonusDatangAwal = 0;

                $mulaiBonusDatang =
                    $setting->mulai_bonus_datang;

                $tarifBonusDatangAwal =
                    (float) (
                        $setting->bonus_datang_awal ?? 0
                    );

                if (
                    $mulaiBonusDatang &&
                    $tarifBonusDatangAwal > 0
                ) {

                    foreach ($attendances as $attendance) {

                        if (!$attendance->check_in) {
                            continue;
                        }

                        $attendanceDate = Carbon::parse(
                            $attendance->attendance_date,
                            'Asia/Jakarta'
                        )->format('Y-m-d');

                        $jamMulaiBonus = Carbon::parse(
                            $attendanceDate .
                            ' ' .
                            $mulaiBonusDatang,
                            'Asia/Jakarta'
                        );

                        $actualCheckIn = Carbon::parse(
                            $attendanceDate .
                            ' ' .
                            $attendance->check_in,
                            'Asia/Jakarta'
                        );

                        if (
                            $actualCheckIn->lessThan(
                                $jamMulaiBonus
                            )
                        ) {
                            $bonusDatangAwal +=
                                $tarifBonusDatangAwal;
                        }
                    }
                }

                /**
                 * =================================================
                 * 4. DENDA TERLAMBAT
                 * =================================================
                 *
                 * RULE:
                 *
                 * Denda dihitung PER MENIT setelah toleransi.
                 *
                 * Contoh:
                 *
                 * Jadwal 08:00
                 * Toleransi 10 menit
                 *
                 * Datang 08:05
                 * = 0 menit denda
                 *
                 * Datang 08:10
                 * = 0 menit denda
                 *
                 * Datang 08:11
                 * = 1 menit
                 *
                 * Datang 08:20
                 * = 10 menit
                 *
                 * Tarif denda:
                 *
                 * Rp1.000 / menit
                 */
                $tarifDendaPerMenit = 1000;

                $potonganTerlambat =
                    $totalMenitTerlambat *
                    $tarifDendaPerMenit;

                /**
                 * =================================================
                 * 5. BONUS KEDISIPLINAN
                 * =================================================
                 */
                $bonusKedisiplinan = 0;

                if (
                    $periodFinished &&
                    $totalHadir > 0 &&
                    $totalKejadianTerlambat === 0
                ) {

                    $bonusKedisiplinan =
                        (float) (
                            $setting->bonus_kedisiplinan ?? 0
                        );
                }

                /**
                 * =================================================
                 * 6. UANG MAKAN
                 * =================================================
                 *
                 * Hanya MealAllowanceRequest APPROVED
                 * yang masuk ke periode payroll.
                 *
                 * Uang makan = POTONGAN.
                 */
                $mealAllowances =
                    MealAllowanceRequest::where(
                        'employee_id',
                        $employeeId
                    )
                        ->where(
                            'status',
                            'approved'
                        )
                        ->whereDate(
                            'meal_date',
                            '>=',
                            $periodStart
                        )
                        ->whereDate(
                            'meal_date',
                            '<=',
                            $periodEnd
                        )
                        ->get();

                $totalUangMakan =
                    $mealAllowances->sum(
                        fn ($mealAllowance) =>
                            (float) $mealAllowance->amount
                    );

                /**
                 * =================================================
                 * 7. IZIN DAN CUTI
                 * =================================================
                 */
                $leaveRequests =
                    LeaveRequest::where(
                        'employee_id',
                        $employeeId
                    )
                        ->where(
                            'status',
                            'approved'
                        )
                        ->whereDate(
                            'start_date',
                            '<=',
                            $periodEnd
                        )
                        ->whereDate(
                            'end_date',
                            '>=',
                            $periodStart
                        )
                        ->get();

                $totalIzin = 0;
                $totalCuti = 0;

                foreach ($leaveRequests as $leave) {

                    $startDate = Carbon::parse(
                        $leave->start_date,
                        'Asia/Jakarta'
                    )->startOfDay();

                    $endDate = Carbon::parse(
                        $leave->end_date,
                        'Asia/Jakarta'
                    )->startOfDay();

                    $countStart =
                        $startDate->greaterThan(
                            $periodStartCarbon
                        )
                            ? $startDate->copy()
                            : $periodStartCarbon->copy();

                    $countEnd =
                        $endDate->lessThan(
                            $periodEndCarbon
                        )
                            ? $endDate->copy()
                            : $periodEndCarbon->copy();

                    for (
                        $date = $countStart->copy();
                        $date->lessThanOrEqualTo($countEnd);
                        $date->addDay()
                    ) {

                        if ($leave->type === 'izin') {
                            $totalIzin++;
                        }

                        if ($leave->type === 'cuti') {
                            $totalCuti++;
                        }
                    }
                }

                /**
                 * =================================================
                 * 8. POTONGAN IZIN
                 * =================================================
                 */
                $potonganIzin =
                    $totalIzin *
                    (float) (
                        $setting->potongan_izin ?? 0
                    );

                /**
                 * =================================================
                 * 9. POTONGAN CUTI
                 * =================================================
                 */
                $jatahCuti =
                    (int) (
                        $setting->jatah_cuti ?? 0
                    );

                $cutiLebih =
                    $totalCuti -
                    $jatahCuti;

                if ($cutiLebih > 0) {

                    $potonganCuti =
                        $cutiLebih *
                        (float) (
                            $setting->potongan_cuti ?? 0
                        );

                } else {

                    $potonganCuti = 0;
                }

                /**
                 * =================================================
                 * 10. SAKIT
                 * =================================================
                 */
                $totalSakit = 0;

                if ($hasSickDateColumn) {

                    $medicalLeaves =
                        MedicalLeave::where(
                            'employee_id',
                            $employeeId
                        )
                            ->where(
                                'status',
                                'approved'
                            )
                            ->whereMonth(
                                'sick_date',
                                $bulan
                            )
                            ->whereYear(
                                'sick_date',
                                $tahun
                            )
                            ->get();

                    $totalSakit =
                        $medicalLeaves->count();

                } else {

                    $medicalLeaves =
                        MedicalLeave::where(
                            'employee_id',
                            $employeeId
                        )
                            ->where(
                                'status',
                                'approved'
                            )
                            ->whereDate(
                                'start_date',
                                '<=',
                                $periodEnd
                            )
                            ->whereDate(
                                'end_date',
                                '>=',
                                $periodStart
                            )
                            ->get();

                    foreach (
                        $medicalLeaves as $medical
                    ) {

                        $startDate = Carbon::parse(
                            $medical->start_date,
                            'Asia/Jakarta'
                        )->startOfDay();

                        $endDate = Carbon::parse(
                            $medical->end_date,
                            'Asia/Jakarta'
                        )->startOfDay();

                        $countStart =
                            $startDate->greaterThan(
                                $periodStartCarbon
                            )
                                ? $startDate->copy()
                                : $periodStartCarbon->copy();

                        $countEnd =
                            $endDate->lessThan(
                                $periodEndCarbon
                            )
                                ? $endDate->copy()
                                : $periodEndCarbon->copy();

                        for (
                            $date = $countStart->copy();
                            $date->lessThanOrEqualTo($countEnd);
                            $date->addDay()
                        ) {
                            $totalSakit++;
                        }
                    }
                }

                /**
                 * =================================================
                 * 11. KASBON
                 * =================================================
                 */
                $existingPayroll =
                    Payroll::where(
                        'employee_id',
                        $employeeId
                    )
                        ->where(
                            'bulan',
                            $bulan
                        )
                        ->where(
                            'tahun',
                            $tahun
                        )
                        ->first();

                $linkedCashAdvances =
                    collect();

                if ($existingPayroll) {

                    $linkedCashAdvances =
                        CashAdvance::where(
                            'employee_id',
                            $employeeId
                        )
                            ->where(
                                'payroll_id',
                                $existingPayroll->id
                            )
                            ->where(
                                'status',
                                'approved'
                            )
                            ->where(
                                'is_paid',
                                true
                            )
                            ->get();
                }

                $pendingCashAdvances =
                    CashAdvance::where(
                        'employee_id',
                        $employeeId
                    )
                        ->where(
                            'status',
                            'approved'
                        )
                        ->where(
                            'is_paid',
                            true
                        )
                        ->where(
                            'is_deducted',
                            false
                        )
                        ->get();

                $allCashAdvances =
                    $linkedCashAdvances
                        ->merge(
                            $pendingCashAdvances
                        )
                        ->unique('id')
                        ->values();

                $totalKasbon =
                    $allCashAdvances->sum(
                        fn ($cashAdvance) =>
                            (float) $cashAdvance->amount
                    );

                /**
                 * =================================================
                 * 12. GAJI DASAR
                 * =================================================
                 */
                $gajiHarian =
                    (float) (
                        $setting->gaji_harian ?? 0
                    );

                $totalGajiDasar =
                    $totalHadir *
                    $gajiHarian;

                /**
                 * =================================================
                 * 13. KOREKSI
                 * =================================================
                 */
                $totalKoreksi =
                    (float) (
                        $setting->koreksi_gaji ?? 0
                    );

                /**
                 * =================================================
                 * 14. TOTAL POTONGAN
                 * =================================================
                 */
                $totalPotongan =
                    $potonganTerlambat +
                    $potonganIzin +
                    $potonganCuti +
                    $totalUangMakan;

                /**
                 * =================================================
                 * 15. TAKE HOME PAY
                 * =================================================
                 */
                $takeHomePay =
                    (
                        $totalGajiDasar +
                        $bonusDatangAwal +
                        $bonusKedisiplinan +
                        $totalKoreksi
                    )
                    -
                    $totalPotongan
                    -
                    $totalKasbon;

                /**
                 * Jangan sampai negatif.
                 */
                if ($takeHomePay < 0) {
                    $takeHomePay = 0;
                }

                /**
                 * =================================================
                 * 16. DATA PAYROLL
                 * =================================================
                 */
                $payrollData = [
                    'gaji_harian' =>
                        $gajiHarian,

                    'total_gaji_dasar' =>
                        $totalGajiDasar,

                    'bonus_datang_awal' =>
                        $bonusDatangAwal,

                    'bonus_kedisiplinan' =>
                        $bonusKedisiplinan,

                    'total_potongan' =>
                        $totalPotongan,

                    'total_kasbon' =>
                        $totalKasbon,

                    'total_koreksi' =>
                        $totalKoreksi,

                    'potongan_terlambat' =>
                        $potonganTerlambat,

                    'potongan_izin' =>
                        $potonganIzin,

                    'potongan_cuti' =>
                        $potonganCuti,

                    'take_home_pay' =>
                        $takeHomePay,

                    'total_hadir' =>
                        $totalHadir,

                    'total_terlambat' =>
                        $totalTerlambat,

                    'total_izin' =>
                        $totalIzin,

                    'total_cuti' =>
                        $totalCuti,

                    'total_sakit' =>
                        $totalSakit,

                    'status' =>
                        'generated',

                    'finance_id' =>
                        auth()->id(),

                    'generated_at' =>
                        Carbon::now(
                            'Asia/Jakarta'
                        ),
                ];

                /**
                 * =================================================
                 * SIMPAN TOTAL UANG MAKAN
                 * =================================================
                 */
                if (
                    Schema::hasColumn(
                        'payrolls',
                        'total_uang_makan'
                    )
                ) {
                    $payrollData['total_uang_makan'] =
                        $totalUangMakan;
                }

                /**
                 * =================================================
                 * 17. SIMPAN PAYROLL
                 * =================================================
                 */
                $payroll = Payroll::updateOrCreate(
                    [
                        'employee_id' =>
                            $employeeId,

                        'bulan' =>
                            $bulan,

                        'tahun' =>
                            $tahun,
                    ],
                    $payrollData
                );

                /**
                 * =================================================
                 * 18. HUBUNGKAN KASBON
                 * =================================================
                 */
                foreach (
                    $allCashAdvances as $cashAdvance
                ) {

                    $cashAdvance->update([
                        'is_deducted' =>
                            true,

                        'payroll_id' =>
                            $payroll->id,
                    ]);
                }

                /**
                 * =================================================
                 * 19. LOG PERHITUNGAN
                 * =================================================
                 */
                $this->logPayrollCalculation(
                    $setting,
                    $totalHadir,
                    $totalTerlambat,
                    $totalKejadianTerlambat,
                    $totalMenitTerlambat,
                    $bonusDatangAwal,
                    $bonusKedisiplinan,
                    $totalUangMakan,
                    $totalPotongan,
                    $potonganTerlambat,
                    $takeHomePay,
                    $periodFinished
                );

                $generatedPayrolls[] =
                    $payroll->fresh();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' =>
                    'Berhasil me-generate payroll.',
                'data' =>
                    $generatedPayrolls,
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' =>
                    'Gagal me-generate payroll: ' .
                    $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * =========================================================
     * LOG PERHITUNGAN
     * =========================================================
     */
    private function logPayrollCalculation(
        $setting,
        $totalHadir,
        $totalTerlambat,
        $totalKejadianTerlambat,
        $totalMenitTerlambat,
        $bonusDatangAwal,
        $bonusKedisiplinan,
        $totalUangMakan,
        $totalPotongan,
        $potonganTerlambat,
        $takeHomePay,
        $periodFinished
    ): void {

        $this->commandLog(
            sprintf(
                'Employee %s | Hadir %d | Terlambat %d kejadian / %d menit | Denda Terlambat Rp%s | Bonus Datang Awal Rp%s | Bonus Kedisiplinan Rp%s | Potongan Uang Makan Rp%s | Total Potongan Rp%s | THP Rp%s | Periode selesai: %s',
                $setting->employee_id,

                $totalHadir,

                $totalKejadianTerlambat,

                $totalMenitTerlambat,

                number_format(
                    $potonganTerlambat,
                    0,
                    ',',
                    '.'
                ),

                number_format(
                    $bonusDatangAwal,
                    0,
                    ',',
                    '.'
                ),

                number_format(
                    $bonusKedisiplinan,
                    0,
                    ',',
                    '.'
                ),

                number_format(
                    $totalUangMakan,
                    0,
                    ',',
                    '.'
                ),

                number_format(
                    $totalPotongan,
                    0,
                    ',',
                    '.'
                ),

                number_format(
                    $takeHomePay,
                    0,
                    ',',
                    '.'
                ),

                $periodFinished
                    ? 'YA'
                    : 'BELUM'
            )
        );
    }

    /**
     * =========================================================
     * COMMAND LOG
     * =========================================================
     */
    private function commandLog(
        string $message
    ): void {

        logger()->info(
            '[PAYROLL] ' .
            $message
        );
    }

    /**
     * =========================================================
     * PAID PAYROLL
     * =========================================================
     */
    public function paid(
        Request $request,
        $id
    ) {

        $validator = Validator::make(
            $request->all(),
            [
                'payment_method' =>
                    'required|in:cash,transfer',
            ]
        );

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Metode pembayaran wajib dipilih.',
                'errors' =>
                    $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {

            $payroll =
                Payroll::lockForUpdate()
                    ->find($id);

            if (!$payroll) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Data payroll tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            if (
                $payroll->status === 'paid'
            ) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Payroll ini sudah dibayarkan sebelumnya.',
                    'data' => $payroll,
                ], 422);
            }

            if (
                $payroll->status !== 'generated'
            ) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Payroll belum berada pada status generated.',
                    'data' => $payroll,
                ], 422);
            }

            $paymentMethod =
                $request->payment_method;

            /**
             * Update payroll.
             */
            $payroll->update([
                'status' =>
                    'paid',

                'payment_method' =>
                    $paymentMethod,

                'finance_id' =>
                    auth()->id(),
            ]);

            /**
             * =====================================================
             * TRANSFER
             * =====================================================
             */
            if (
                $paymentMethod === 'transfer'
            ) {

                $balance =
                    EmployeeBalance::firstOrCreate(
                        [
                            'employee_id' =>
                                $payroll->employee_id,
                        ],
                        [
                            'balance' =>
                                0,
                        ]
                    );

                $balance->balance =
                    (float) $balance->balance +
                    (float) $payroll->take_home_pay;

                $balance->save();

                BalanceTransaction::create([
                    'employee_id' =>
                        $payroll->employee_id,

                    'payroll_id' =>
                        $payroll->id,

                    'type' =>
                        'credit',

                    'amount' =>
                        $payroll->take_home_pay,

                    'description' =>
                        'Payroll Transfer ' .
                        str_pad(
                            $payroll->bulan,
                            2,
                            '0',
                            STR_PAD_LEFT
                        ) .
                        '/' .
                        $payroll->tahun,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' =>
                    $paymentMethod === 'transfer'
                        ? 'Payroll berhasil dibayar melalui transfer dan saldo employee berhasil ditambahkan.'
                        : 'Payroll berhasil dibayar secara cash.',
                'data' =>
                    $payroll->fresh(),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' =>
                    'Gagal memproses pembayaran payroll: ' .
                    $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * =========================================================
     * DELETE PAYROLL
     * =========================================================
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $payroll =
                Payroll::lockForUpdate()
                    ->find($id);

            if (!$payroll) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Data payroll tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            if (
                $payroll->status === 'paid'
            ) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Payroll yang sudah dibayar tidak dapat dihapus.',
                    'data' => $payroll,
                ], 422);
            }

            /**
             * Kembalikan status kasbon
             * supaya dapat dipakai lagi.
             */
            CashAdvance::where(
                'payroll_id',
                $payroll->id
            )->update([
                'is_deducted' =>
                    false,

                'payroll_id' =>
                    null,
            ]);

            $payroll->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' =>
                    'Data payroll berhasil dihapus.',
                'data' => null,
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' =>
                    'Gagal menghapus payroll: ' .
                    $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * =========================================================
     * EMPLOYEE PAYROLL HISTORY
     * =========================================================
     */
    public function employeeHistory(
        $employeeId
    ) {

        $employee =
            Employee::find($employeeId);

        if (!$employee) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Employee tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $history =
            Payroll::where(
                'employee_id',
                $employeeId
            )
                ->orderByDesc('tahun')
                ->orderByDesc('bulan')
                ->get();

        return response()->json([
            'success' => true,
            'message' =>
                'Berhasil mengambil riwayat payroll.',
            'employee' =>
                $employee,
            'data' =>
                $history,
        ], 200);
    }
}
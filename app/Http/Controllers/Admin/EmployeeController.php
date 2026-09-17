<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Employee::with('currentEmployment')->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Aktivasi employee dikelola oleh Employee Lifecycle agar employee baru
        // tidak dapat login sebelum onboarding selesai.
        $validated['is_active'] = false;

        $employee = Employee::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee berhasil ditambahkan. Lanjutkan proses onboarding untuk mengaktifkan akun.',
            'data' => $employee,
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $employee->load([
                'currentEmployment.statusHistories',
                'currentEmployment.separations',
            ]),
        ]);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'employee_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_code')->ignore($employee->id),
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')->ignore($employee->id),
            ],
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'is_active' => 'sometimes|boolean',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Untuk employee yang sudah masuk lifecycle, status aktif tidak boleh
        // diubah manual karena dapat membuat employment dan account tidak sinkron.
        if ($employee->employments()->exists() && array_key_exists('is_active', $validated)) {
            if ((bool) $validated['is_active'] !== (bool) $employee->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status aktif employee dikelola melalui Employee Lifecycle.',
                ], 422);
            }

            unset($validated['is_active']);
        }

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee berhasil diperbarui.',
            'data' => $employee->fresh()->load('currentEmployment'),
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        if ($this->hasHistoricalOrOperationalData($employee)) {
            return response()->json([
                'success' => false,
                'message' => 'Employee memiliki data historis/operasional dan tidak boleh dihapus. Gunakan lifecycle resignation/termination.',
            ], 409);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee berhasil dihapus.',
        ]);
    }

    private function hasHistoricalOrOperationalData(Employee $employee): bool
    {
        $employeeIdTables = [
            'attendances',
            'leave_requests',
            'medical_leaves',
            'overtime_requests',
            'cash_advances',
            'business_trips',
            'employee_payroll_settings',
            'payrolls',
            'payroll_corrections',
            'employee_targets',
            'employee_performances',
            'bpjs_payment_proofs',
            'employee_balances',
            'balance_transactions',
            'balance_withdrawals',
            'employee_face_templates',
            'employee_shift_schedules',
            'employee_employments',
        ];

        foreach ($employeeIdTables as $table) {
            if (Schema::hasTable($table) && DB::table($table)->where('employee_id', $employee->id)->exists()) {
                return true;
            }
        }

        if (Schema::hasTable('team_members')) {
            $hasTeamMembership = DB::table('team_members')
                ->where('member_type', Employee::class)
                ->where('member_id', $employee->id)
                ->exists();

            if ($hasTeamMembership) {
                return true;
            }
        }

        if (Schema::hasTable('meeting_participants')) {
            $hasMeetingHistory = DB::table('meeting_participants')
                ->where('participant_type', Employee::class)
                ->where('participant_id', $employee->id)
                ->exists();

            if ($hasMeetingHistory) {
                return true;
            }
        }

        if (Schema::hasTable('face_identity_accounts')) {
            return DB::table('face_identity_accounts')
                ->where('account_type', 'employee')
                ->where('account_id', $employee->id)
                ->exists();
        }

        return false;
    }
}

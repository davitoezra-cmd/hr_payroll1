<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShiftSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftScheduleController extends Controller
{
    private const RELATIONS = [
        'employee:id,employee_code,name,email',
        'shift:id,code,name,jam_masuk,jam_pulang,late_tolerance_minutes,batas_telat,mulai_lembur,lintas_hari,is_active',
    ];

    public function index(Request $request)
    {
        $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
            'shift_id' => 'nullable|integer|exists:work_shifts,id',
            'status' => 'nullable|in:work,off',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = EmployeeShiftSchedule::with(self::RELATIONS);

        $query->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->employee_id));
        $query->when($request->filled('shift_id'), fn ($q) => $q->where('shift_id', $request->shift_id));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('start_date'), fn ($q) => $q->whereDate('work_date', '>=', $request->start_date));
        $query->when($request->filled('end_date'), fn ($q) => $q->whereDate('work_date', '<=', $request->end_date));

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('work_date')->orderBy('employee_id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateMapping($request);
        $this->normalizeMapping($validated);

        if (EmployeeShiftSchedule::where('employee_id', $validated['employee_id'])
            ->whereDate('work_date', $validated['work_date'])
            ->exists()) {
            throw ValidationException::withMessages([
                'work_date' => ['Employee sudah memiliki jadwal pada tanggal tersebut.'],
            ]);
        }

        $validated['assigned_by'] = $request->user()?->id;
        $schedule = EmployeeShiftSchedule::create($validated)->load(self::RELATIONS);

        return response()->json([
            'success' => true,
            'message' => 'Mapping jadwal berhasil dibuat.',
            'data' => $schedule,
        ], 201);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'mappings' => 'required|array|min:1',
            'mappings.*.employee_id' => 'required|integer|exists:employees,id',
            'mappings.*.shift_id' => 'nullable|integer|exists:work_shifts,id',
            'mappings.*.work_date' => 'required|date',
            'mappings.*.status' => 'required|in:work,off',
            'mappings.*.notes' => 'nullable|string|max:255',
        ]);

        $seen = [];
        foreach ($validated['mappings'] as $index => &$mapping) {
            $this->normalizeMapping($mapping, "mappings.$index");
            $key = $mapping['employee_id'] . '|' . $mapping['work_date'];

            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    "mappings.$index.work_date" => ['Employee dan tanggal yang sama dikirim lebih dari satu kali.'],
                ]);
            }
            $seen[$key] = true;
        }
        unset($mapping);

        $ids = DB::transaction(function () use ($validated, $request) {
            return collect($validated['mappings'])->map(function ($mapping) use ($request) {
                $schedule = EmployeeShiftSchedule::updateOrCreate(
                    [
                        'employee_id' => $mapping['employee_id'],
                        'work_date' => $mapping['work_date'],
                    ],
                    [
                        'shift_id' => $mapping['shift_id'] ?? null,
                        'status' => $mapping['status'],
                        'notes' => $mapping['notes'] ?? null,
                        'assigned_by' => $request->user()?->id,
                    ]
                );

                return $schedule->id;
            });
        });

        $data = EmployeeShiftSchedule::with(self::RELATIONS)
            ->whereIn('id', $ids)
            ->orderBy('work_date')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Mapping jadwal berhasil disimpan.',
            'total' => $data->count(),
            'data' => $data,
        ], 201);
    }

    public function show(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        return response()->json([
            'success' => true,
            'data' => $employeeShiftSchedule->load(self::RELATIONS),
        ]);
    }

    public function update(Request $request, EmployeeShiftSchedule $employeeShiftSchedule)
    {
        $validated = $request->validate([
            'employee_id' => 'sometimes|required|integer|exists:employees,id',
            'shift_id' => 'nullable|integer|exists:work_shifts,id',
            'work_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:work,off',
            'notes' => 'nullable|string|max:255',
        ]);

        $merged = [
            'employee_id' => $validated['employee_id'] ?? $employeeShiftSchedule->employee_id,
            'shift_id' => array_key_exists('shift_id', $validated) ? $validated['shift_id'] : $employeeShiftSchedule->shift_id,
            'work_date' => $validated['work_date'] ?? $employeeShiftSchedule->work_date->format('Y-m-d'),
            'status' => $validated['status'] ?? $employeeShiftSchedule->status,
            'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $employeeShiftSchedule->notes,
        ];

        $this->normalizeMapping($merged);

        $duplicate = EmployeeShiftSchedule::where('employee_id', $merged['employee_id'])
            ->whereDate('work_date', $merged['work_date'])
            ->where('id', '!=', $employeeShiftSchedule->id)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'work_date' => ['Employee sudah mempunyai jadwal pada tanggal tersebut.'],
            ]);
        }

        $merged['assigned_by'] = $request->user()?->id;
        $employeeShiftSchedule->update($merged);

        return response()->json([
            'success' => true,
            'message' => 'Mapping jadwal berhasil diperbarui.',
            'data' => $employeeShiftSchedule->fresh()->load(self::RELATIONS),
        ]);
    }

    public function destroy(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if ($employeeShiftSchedule->attendance()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak dapat dihapus karena sudah memiliki data presensi.',
            ], 409);
        }

        $employeeShiftSchedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mapping jadwal berhasil dihapus.',
        ]);
    }

    private function validateMapping(Request $request): array
    {
        return $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'shift_id' => 'nullable|integer|exists:work_shifts,id',
            'work_date' => 'required|date',
            'status' => 'required|in:work,off',
            'notes' => 'nullable|string|max:255',
        ]);
    }

    private function normalizeMapping(array &$mapping, string $prefix = ''): void
    {
        if ($mapping['status'] === 'work' && empty($mapping['shift_id'])) {
            $key = $prefix ? $prefix . '.shift_id' : 'shift_id';
            throw ValidationException::withMessages([
                $key => ['Shift wajib dipilih jika status jadwal adalah work.'],
            ]);
        }

        if ($mapping['status'] === 'off') {
            $mapping['shift_id'] = null;
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkShift::query()->orderBy('jam_masuk')->orderBy('name');

        if ($request->has('active_only')) {
            $query->where('is_active', $request->boolean('active_only'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $payload = $this->preparePayload($validated);
        $shift = WorkShift::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Work shift berhasil dibuat.',
            'data' => $shift,
        ], 201);
    }

    public function show(WorkShift $workShift)
    {
        return response()->json([
            'success' => true,
            'data' => $workShift,
        ]);
    }

    public function update(Request $request, WorkShift $workShift)
    {
        $validated = $this->validatePayload($request, $workShift);
        $workShift->update($this->preparePayload($validated, $workShift));

        return response()->json([
            'success' => true,
            'message' => 'Work shift berhasil diperbarui.',
            'data' => $workShift->fresh(),
        ]);
    }

    public function destroy(WorkShift $workShift)
    {
        if ($workShift->schedules()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Work shift masih digunakan pada shift schedule. Nonaktifkan shift jika tidak ingin digunakan lagi.',
            ], 409);
        }

        $workShift->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work shift berhasil dihapus.',
        ]);
    }

    private function validatePayload(Request $request, ?WorkShift $workShift = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('work_shifts', 'code')->ignore($workShift?->id),
            ],
            'name' => 'required|string|max:100',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
            'late_tolerance_minutes' => 'sometimes|integer|min:0|max:180',
            'is_active' => 'sometimes|boolean',
        ]);
    }

    private function preparePayload(array $validated, ?WorkShift $existing = null): array
    {
        $jamMasuk = Carbon::createFromFormat('H:i', $validated['jam_masuk']);
        $jamPulang = Carbon::createFromFormat('H:i', $validated['jam_pulang']);
        $tolerance = (int) ($validated['late_tolerance_minutes'] ?? $existing?->late_tolerance_minutes ?? 10);

        return [
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'jam_masuk' => $jamMasuk->format('H:i:s'),
            'jam_pulang' => $jamPulang->format('H:i:s'),
            'late_tolerance_minutes' => $tolerance,
            'batas_telat' => $jamMasuk->copy()->addMinutes($tolerance)->format('H:i:s'),
            'mulai_lembur' => $jamPulang->format('H:i:s'),
            'lintas_hari' => $jamPulang->lessThanOrEqualTo($jamMasuk),
            'is_active' => $validated['is_active'] ?? $existing?->is_active ?? true,
        ];
    }
}

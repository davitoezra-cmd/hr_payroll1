<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeEmployment;
use App\Models\EmployeeSeparation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class EmployeeLifecycleService
{
    public function startOnboarding(Employee $employee, array $data, User $actor): EmployeeEmployment
    {
        return DB::transaction(function () use ($employee, $data, $actor) {
            $lockedEmployee = Employee::query()->lockForUpdate()->findOrFail($employee->id);

            if ($lockedEmployee->employments()->exists()) {
                throw new ConflictHttpException(
                    'Employee sudah memiliki employment history. Onboarding ulang tidak diizinkan.'
                );
            }

            $employment = $lockedEmployee->employments()->create([
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'current_status' => 'ONBOARDING',
                'onboarding_started_at' => now(),
            ]);

            $lockedEmployee->forceFill(['is_active' => false])->save();
            $lockedEmployee->tokens()->delete();

            $employment->statusHistories()->create([
                'status' => 'ONBOARDING',
                'effective_from' => now(),
                'reason' => 'Onboarding dimulai',
                'changed_by' => $actor->id,
            ]);

            return $employment->fresh(['employee', 'statusHistories', 'lifecycleTasks']);
        });
    }

    public function completeOnboarding(EmployeeEmployment $employment, User $actor): EmployeeEmployment
    {
        return DB::transaction(function () use ($employment, $actor) {
            $lockedEmployment = EmployeeEmployment::query()
                ->with('employee')
                ->lockForUpdate()
                ->findOrFail($employment->id);

            Employee::query()->whereKey($lockedEmployment->employee_id)->lockForUpdate()->firstOrFail();

            if ($lockedEmployment->current_status !== 'ONBOARDING') {
                throw new ConflictHttpException('Onboarding hanya dapat diselesaikan dari status ONBOARDING.');
            }

            if (CarbonImmutable::parse($lockedEmployment->start_date, 'Asia/Jakarta')->startOfDay()->isFuture()) {
                throw new ConflictHttpException('Onboarding belum dapat diselesaikan sebelum start_date employee.');
            }

            $unfinishedTasks = $lockedEmployment->lifecycleTasks()
                ->where('phase', 'ONBOARDING')
                ->whereNotIn('status', ['COMPLETED', 'SKIPPED'])
                ->exists();

            if ($unfinishedTasks) {
                throw new ConflictHttpException('Masih ada onboarding task yang belum selesai atau belum di-skip.');
            }

            $this->transitionEmployment($lockedEmployment, 'ACTIVE', $actor, 'Onboarding selesai');

            $lockedEmployment->forceFill([
                'onboarding_completed_at' => now(),
            ])->save();

            $lockedEmployment->employee->forceFill(['is_active' => true])->save();

            return $lockedEmployment->fresh(['employee', 'statusHistories', 'lifecycleTasks']);
        });
    }

    public function submitResignation(Employee $employee, array $data): EmployeeSeparation
    {
        return DB::transaction(function () use ($employee, $data) {
            $lockedEmployee = Employee::query()->lockForUpdate()->findOrFail($employee->id);

            if (! $lockedEmployee->is_active) {
                throw new ConflictHttpException('Employee nonaktif tidak dapat mengajukan resignation.');
            }

            $employment = $lockedEmployee->employments()
                ->where('current_status', 'ACTIVE')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if (! $employment) {
                throw new ConflictHttpException(
                    'Employment ACTIVE tidak ditemukan. Data employment harus diinisialisasi terlebih dahulu.'
                );
            }

            $this->ensureNoOpenSeparation($employment);

            $separation = $employment->separations()->create([
                'separation_type' => 'RESIGNATION',
                'reason' => $data['reason'],
                'notice_date' => CarbonImmutable::today('Asia/Jakarta')->toDateString(),
                'last_working_date' => $data['last_working_date'],
                'effective_date' => $data['effective_date'],
                'process_status' => 'SUBMITTED',
                'notes' => $data['notes'] ?? null,
                'submitted_at' => now(),
            ]);

            return $separation->fresh(['employment.employee']);
        });
    }

    public function terminate(Employee $employee, array $data): EmployeeSeparation
    {
        return DB::transaction(function () use ($employee, $data) {
            $lockedEmployee = Employee::query()->lockForUpdate()->findOrFail($employee->id);

            if (! $lockedEmployee->is_active) {
                throw new ConflictHttpException('Employee nonaktif tidak dapat ditermination.');
            }

            $employment = $lockedEmployee->employments()
                ->whereIn('current_status', ['ACTIVE', 'SUSPENDED'])
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if (! $employment) {
                throw new ConflictHttpException(
                    'Employment ACTIVE/SUSPENDED tidak ditemukan. Employee yang sudah keluar tidak dapat ditermination.'
                );
            }

            $this->ensureNoOpenSeparation($employment);

            $separation = $employment->separations()->create([
                'separation_type' => 'TERMINATION',
                'reason' => $data['reason'],
                'notice_date' => CarbonImmutable::today('Asia/Jakarta')->toDateString(),
                'last_working_date' => $data['last_working_date'] ?? $data['effective_date'],
                'effective_date' => $data['effective_date'],
                'process_status' => 'SUBMITTED',
                'notes' => $data['notes'] ?? null,
                'submitted_at' => now(),
            ]);

            return $separation->fresh(['employment.employee']);
        });
    }

    public function approveSeparation(EmployeeSeparation $separation, User $actor): EmployeeSeparation
    {
        return DB::transaction(function () use ($separation, $actor) {
            $lockedSeparation = EmployeeSeparation::query()
                ->with('employment.employee')
                ->lockForUpdate()
                ->findOrFail($separation->id);

            $employment = EmployeeEmployment::query()
                ->lockForUpdate()
                ->findOrFail($lockedSeparation->employment_id);

            if ($lockedSeparation->process_status !== 'SUBMITTED') {
                throw new ConflictHttpException('Hanya separation berstatus SUBMITTED yang dapat di-approve.');
            }

            if ($lockedSeparation->separation_type === 'RESIGNATION') {
                if ($employment->current_status !== 'ACTIVE') {
                    throw new ConflictHttpException('Resignation hanya dapat di-approve untuk employment ACTIVE.');
                }
            } elseif (! in_array($employment->current_status, ['ACTIVE', 'SUSPENDED'], true)) {
                throw new ConflictHttpException('Termination hanya dapat di-approve untuk employment ACTIVE/SUSPENDED.');
            }

            $lockedSeparation->forceFill([
                'process_status' => 'APPROVED',
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ])->save();

            if ($lockedSeparation->separation_type === 'RESIGNATION') {
                $this->transitionEmployment($employment, 'NOTICE_PERIOD', $actor, 'Resignation disetujui');
            }

            return $lockedSeparation->fresh(['employment.employee', 'approvedBy', 'lifecycleTasks']);
        });
    }

    public function rejectSeparation(EmployeeSeparation $separation, User $actor, ?string $notes = null): EmployeeSeparation
    {
        return DB::transaction(function () use ($separation, $actor, $notes) {
            $lockedSeparation = EmployeeSeparation::query()->lockForUpdate()->findOrFail($separation->id);

            if ($lockedSeparation->process_status !== 'SUBMITTED') {
                throw new ConflictHttpException('Hanya separation berstatus SUBMITTED yang dapat ditolak.');
            }

            $lockedSeparation->forceFill([
                'process_status' => 'REJECTED',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'notes' => $notes ?? $lockedSeparation->notes,
            ])->save();

            return $lockedSeparation->fresh(['employment.employee', 'approvedBy']);
        });
    }

    public function startOffboarding(EmployeeSeparation $separation): EmployeeSeparation
    {
        return DB::transaction(function () use ($separation) {
            $lockedSeparation = EmployeeSeparation::query()->lockForUpdate()->findOrFail($separation->id);

            if ($lockedSeparation->process_status !== 'APPROVED') {
                throw new ConflictHttpException('Offboarding hanya dapat dimulai setelah separation APPROVED.');
            }

            if ($lockedSeparation->offboarding_started_at !== null) {
                throw new ConflictHttpException('Offboarding sudah pernah dimulai.');
            }

            $lockedSeparation->forceFill(['offboarding_started_at' => now()])->save();

            return $lockedSeparation->fresh(['employment.employee', 'lifecycleTasks']);
        });
    }

    public function completeSeparation(EmployeeSeparation $separation, User $actor): EmployeeSeparation
    {
        return DB::transaction(function () use ($separation, $actor) {
            // Gunakan urutan lock yang sama dengan proses lifecycle lain:
            // employee -> employment -> separation. Ini mengurangi risiko deadlock.
            $snapshot = EmployeeSeparation::query()->findOrFail($separation->id);
            $employmentSnapshot = EmployeeEmployment::query()->findOrFail($snapshot->employment_id);

            $employee = Employee::query()
                ->lockForUpdate()
                ->findOrFail($employmentSnapshot->employee_id);

            $employment = EmployeeEmployment::query()
                ->with('employee')
                ->lockForUpdate()
                ->findOrFail($snapshot->employment_id);

            $lockedSeparation = EmployeeSeparation::query()
                ->with('employment.employee')
                ->lockForUpdate()
                ->findOrFail($separation->id);

            if ($lockedSeparation->process_status !== 'APPROVED') {
                throw new ConflictHttpException('Separation hanya dapat diselesaikan dari status APPROVED.');
            }

            if ($lockedSeparation->offboarding_started_at === null) {
                throw new ConflictHttpException('Offboarding harus dimulai sebelum separation diselesaikan.');
            }

            if ($lockedSeparation->offboarding_completed_at !== null) {
                throw new ConflictHttpException('Offboarding sudah pernah diselesaikan.');
            }

            if ($lockedSeparation->effective_date === null) {
                throw new ConflictHttpException('effective_date wajib tersedia sebelum separation diselesaikan.');
            }

            $effectiveDate = CarbonImmutable::parse($lockedSeparation->effective_date, 'Asia/Jakarta')->startOfDay();
            if ($effectiveDate->isFuture()) {
                throw new ConflictHttpException('Separation belum dapat diselesaikan sebelum effective_date.');
            }

            $unfinishedTasks = $lockedSeparation->lifecycleTasks()
                ->where('phase', 'OFFBOARDING')
                ->whereNotIn('status', ['COMPLETED', 'SKIPPED'])
                ->exists();

            if ($unfinishedTasks) {
                throw new ConflictHttpException('Masih ada offboarding task yang belum selesai atau belum di-skip.');
            }

            $finalStatus = $lockedSeparation->separation_type === 'RESIGNATION'
                ? 'RESIGNED'
                : 'TERMINATED';

            $this->transitionEmployment(
                $employment,
                $finalStatus,
                $actor,
                $lockedSeparation->reason
            );

            $employment->forceFill([
                'end_date' => $lockedSeparation->effective_date,
            ])->save();

            $lockedSeparation->forceFill([
                'process_status' => 'COMPLETED',
                'processed_by' => $actor->id,
                'processed_at' => now(),
                'offboarding_completed_at' => now(),
            ])->save();

            $employee->forceFill(['is_active' => false])->save();
            $employee->tokens()->delete();

            return $lockedSeparation->fresh([
                'employment.employee',
                'approvedBy',
                'processedBy',
                'lifecycleTasks',
            ]);
        });
    }

    private function ensureNoOpenSeparation(EmployeeEmployment $employment): void
    {
        $hasOpenSeparation = $employment->separations()
            ->whereIn('process_status', ['SUBMITTED', 'APPROVED'])
            ->exists();

        if ($hasOpenSeparation) {
            throw new ConflictHttpException('Employee sudah memiliki separation yang masih berjalan.');
        }
    }

    private function transitionEmployment(
        EmployeeEmployment $employment,
        string $newStatus,
        User $actor,
        ?string $reason = null
    ): void {
        $now = now();

        $employment->statusHistories()
            ->whereNull('effective_to')
            ->update(['effective_to' => $now]);

        $employment->forceFill(['current_status' => $newStatus])->save();

        $employment->statusHistories()->create([
            'status' => $newStatus,
            'effective_from' => $now,
            'reason' => $reason,
            'changed_by' => $actor->id,
        ]);
    }
}

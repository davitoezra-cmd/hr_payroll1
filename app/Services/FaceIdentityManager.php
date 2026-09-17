<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeFaceTemplate;
use App\Models\FaceIdentity;
use App\Models\FaceIdentityAccount;
use App\Models\Finance;
use App\Models\FinanceFaceTemplate;
use App\Models\Supervisor;
use App\Models\SupervisorFaceTemplate;
use App\Models\User;
use App\Models\UserFaceTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FaceIdentityManager
{
    /**
     * Return the canonical account type used in face_identity_accounts.
     */
    public function context(?Model $account): ?array
    {
        return match (true) {
            $account instanceof User => [
                'guard' => 'user',
                'label' => 'Administrator',
                'token_name' => 'super-admin-face-token',
            ],
            $account instanceof Employee => [
                'guard' => 'employee',
                'label' => 'Employee',
                'token_name' => 'employee-face-token',
            ],
            $account instanceof Supervisor => [
                'guard' => 'supervisor',
                'label' => 'Supervisor',
                'token_name' => 'supervisor-face-token',
            ],
            $account instanceof Finance => [
                'guard' => 'finance',
                'label' => 'Finance',
                'token_name' => 'finance-face-token',
            ],
            default => null,
        };
    }

    public function account(string $guard, int $accountId): ?Model
    {
        return match ($guard) {
            'user' => User::query()->find($accountId),
            'employee' => Employee::query()->find($accountId),
            'supervisor' => Supervisor::query()->find($accountId),
            'finance' => Finance::query()->find($accountId),
            default => null,
        };
    }

    public function isAccountActive(string $guard, Model $account): bool
    {
        if ($guard === 'user') {
            return true;
        }

        return (bool) $account->getAttribute('is_active');
    }

    public function identityForAccount(Model $account): ?FaceIdentity
    {
        $context = $this->context($account);
        if (! $context) {
            return null;
        }

        return FaceIdentityAccount::query()
            ->where('account_type', $context['guard'])
            ->where('account_id', $account->getKey())
            ->with('identity')
            ->first()?->identity;
    }

    /**
     * Enrollment is identity-aware:
     * - same account + same face updates the shared identity;
     * - a new role/account with the same face is linked to the existing identity;
     * - a materially different face detaches only the current account, so other
     *   accounts connected to the old identity remain untouched.
     */
    public function enroll(Model $account, array $face): array
    {
        $context = $this->context($account);
        if (! $context) {
            throw new \InvalidArgumentException('Unsupported face account type.');
        }

        $embedding = $this->validatedEmbedding($face['embedding'] ?? null);
        $threshold = $this->linkThreshold();

        return DB::transaction(function () use ($account, $context, $face, $embedding, $threshold) {
            $mapping = FaceIdentityAccount::query()
                ->where('account_type', $context['guard'])
                ->where('account_id', $account->getKey())
                ->lockForUpdate()
                ->first();

            if ($mapping) {
                $currentIdentity = FaceIdentity::query()
                    ->whereKey($mapping->face_identity_id)
                    ->lockForUpdate()
                    ->first();

                if ($currentIdentity) {
                    $similarity = $this->cosineSimilarity($embedding, $currentIdentity->embedding);

                    if ($similarity >= $threshold) {
                        $this->updateIdentityFromFace($currentIdentity, $face, $embedding);
                        $this->disableLegacyTemplate($account);

                        return [
                            'identity' => $currentIdentity->fresh('accounts'),
                            'linked_existing_identity' => true,
                            'similarity' => $similarity,
                            'account_count' => $currentIdentity->accounts()->count(),
                        ];
                    }

                    // The account is being re-enrolled with a different person.
                    // Remove only this account from the shared identity.
                    $oldIdentityId = $currentIdentity->getKey();
                    $mapping->delete();

                    if (! FaceIdentityAccount::query()->where('face_identity_id', $oldIdentityId)->exists()) {
                        $currentIdentity->delete();
                    }
                } else {
                    $mapping->delete();
                }
            }

            [$matchedIdentity, $similarity] = $this->findMatchingIdentity($embedding);

            if (! $matchedIdentity) {
                $matchedIdentity = $this->createIdentityFromFace($face, $embedding);
                $similarity = 1.0;
                $linkedExisting = false;
            } else {
                $linkedExisting = true;
            }

            FaceIdentityAccount::query()->updateOrCreate(
                [
                    'account_type' => $context['guard'],
                    'account_id' => $account->getKey(),
                ],
                ['face_identity_id' => $matchedIdentity->getKey()]
            );

            $this->disableLegacyTemplate($account);

            return [
                'identity' => $matchedIdentity->fresh('accounts'),
                'linked_existing_identity' => $linkedExisting,
                'similarity' => $similarity,
                'account_count' => $matchedIdentity->accounts()->count(),
            ];
        });
    }

    /**
     * Import a legacy role-specific template into the central identity model.
     * The legacy row is kept but marked inactive after successful migration.
     */
    public function importLegacy(Model $account, Model $template): array
    {
        $context = $this->context($account);
        if (! $context) {
            return ['status' => 'unsupported'];
        }

        $embedding = $this->validatedEmbedding($template->embedding);

        return DB::transaction(function () use ($account, $template, $context, $embedding) {
            $existingMapping = FaceIdentityAccount::query()
                ->where('account_type', $context['guard'])
                ->where('account_id', $account->getKey())
                ->first();

            if ($existingMapping) {
                $template->forceFill(['is_active' => false])->save();
                return [
                    'status' => 'already-linked',
                    'identity_id' => $existingMapping->face_identity_id,
                ];
            }

            [$identity, $similarity] = $this->findMatchingIdentity($embedding);
            $created = false;

            if (! $identity) {
                $identity = FaceIdentity::query()->create([
                    'embedding' => $embedding,
                    'engine' => (string) ($template->engine ?: 'insightface'),
                    'engine_version' => $template->engine_version,
                    'model_name' => (string) ($template->model_name ?: 'unknown'),
                    'embedding_dimension' => (int) ($template->embedding_dimension ?: count($embedding)),
                    'is_active' => true,
                    'enrolled_at' => $template->enrolled_at ?: now(),
                    'last_verified_at' => $template->last_verified_at,
                ]);
                $similarity = 1.0;
                $created = true;
            }

            FaceIdentityAccount::query()->create([
                'face_identity_id' => $identity->getKey(),
                'account_type' => $context['guard'],
                'account_id' => $account->getKey(),
            ]);

            // Preserve the old row for audit/rollback, but stop it from being
            // considered active by any legacy code.
            $template->forceFill(['is_active' => false])->save();

            return [
                'status' => $created ? 'identity-created' : 'identity-linked',
                'identity_id' => $identity->getKey(),
                'similarity' => $similarity,
            ];
        });
    }

    public function unlinkAccount(Model $account): array
    {
        $context = $this->context($account);
        if (! $context) {
            return ['removed' => false, 'identity_deleted' => false];
        }

        return DB::transaction(function () use ($account, $context) {
            $mapping = FaceIdentityAccount::query()
                ->where('account_type', $context['guard'])
                ->where('account_id', $account->getKey())
                ->lockForUpdate()
                ->first();

            if (! $mapping) {
                $this->deleteLegacyTemplate($account);
                return ['removed' => false, 'identity_deleted' => false];
            }

            $identityId = $mapping->face_identity_id;
            $mapping->delete();

            $identityDeleted = false;
            if (! FaceIdentityAccount::query()->where('face_identity_id', $identityId)->exists()) {
                FaceIdentity::query()->whereKey($identityId)->delete();
                $identityDeleted = true;
            }

            $this->deleteLegacyTemplate($account);

            return ['removed' => true, 'identity_deleted' => $identityDeleted];
        });
    }

    public function accountOptions(FaceIdentity $identity): array
    {
        return $identity->accounts
            ->map(function (FaceIdentityAccount $mapping) {
                $account = $this->account($mapping->account_type, (int) $mapping->account_id);
                if (! $account || ! $this->isAccountActive($mapping->account_type, $account)) {
                    return null;
                }

                $context = $this->context($account);
                if (! $context) {
                    return null;
                }

                return [
                    'guard' => $context['guard'],
                    'account_id' => (int) $account->getKey(),
                    'name' => (string) $account->name,
                    'role_label' => $context['label'],
                    'token_name' => $context['token_name'],
                    'account' => $account,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function linkThreshold(): float
    {
        return max(-1.0, min(1.0, (float) config('services.face_api.identity_link_threshold', 0.60)));
    }

    private function findMatchingIdentity(array $embedding): array
    {
        $bestIdentity = null;
        $bestSimilarity = -1.0;

        FaceIdentity::query()
            ->where('is_active', true)
            ->get()
            ->each(function (FaceIdentity $identity) use ($embedding, &$bestIdentity, &$bestSimilarity) {
                try {
                    $similarity = $this->cosineSimilarity($embedding, $identity->embedding);
                } catch (\Throwable) {
                    return;
                }

                if ($similarity > $bestSimilarity) {
                    $bestSimilarity = $similarity;
                    $bestIdentity = $identity;
                }
            });

        if (! $bestIdentity || $bestSimilarity < $this->linkThreshold()) {
            return [null, $bestSimilarity];
        }

        return [$bestIdentity, $bestSimilarity];
    }

    private function cosineSimilarity(array $left, array $right): float
    {
        if (count($left) !== count($right) || count($left) === 0) {
            return -1.0;
        }

        $dot = 0.0;
        $leftNorm = 0.0;
        $rightNorm = 0.0;

        foreach ($left as $index => $leftValue) {
            $a = (float) $leftValue;
            $b = (float) $right[$index];
            $dot += $a * $b;
            $leftNorm += $a * $a;
            $rightNorm += $b * $b;
        }

        if ($leftNorm <= 0.0 || $rightNorm <= 0.0) {
            return -1.0;
        }

        return $dot / (sqrt($leftNorm) * sqrt($rightNorm));
    }

    private function validatedEmbedding(mixed $embedding): array
    {
        if (! is_array($embedding) || empty($embedding)) {
            throw new \InvalidArgumentException('Face embedding is empty or invalid.');
        }

        return array_map(static fn ($value) => (float) $value, array_values($embedding));
    }

    private function createIdentityFromFace(array $face, array $embedding): FaceIdentity
    {
        return FaceIdentity::query()->create([
            'embedding' => $embedding,
            'engine' => (string) ($face['engine'] ?? 'insightface'),
            'engine_version' => $face['engine_version'] ?? null,
            'model_name' => (string) ($face['model_name'] ?? 'unknown'),
            'embedding_dimension' => (int) ($face['dimension'] ?? count($embedding)),
            'is_active' => true,
            'enrolled_at' => now(),
        ]);
    }

    private function updateIdentityFromFace(FaceIdentity $identity, array $face, array $embedding): void
    {
        $identity->forceFill([
            'embedding' => $embedding,
            'engine' => (string) ($face['engine'] ?? $identity->engine ?? 'insightface'),
            'engine_version' => $face['engine_version'] ?? $identity->engine_version,
            'model_name' => (string) ($face['model_name'] ?? $identity->model_name ?? 'unknown'),
            'embedding_dimension' => (int) ($face['dimension'] ?? count($embedding)),
            'is_active' => true,
            'enrolled_at' => now(),
        ])->save();
    }

    private function disableLegacyTemplate(Model $account): void
    {
        [$templateClass, $foreignKey] = $this->legacyTemplateDefinition($account);
        if (! $templateClass) {
            return;
        }

        $table = (new $templateClass())->getTable();
        if (! Schema::hasTable($table)) {
            return;
        }

        $templateClass::query()
            ->where($foreignKey, $account->getKey())
            ->update(['is_active' => false]);
    }

    private function deleteLegacyTemplate(Model $account): void
    {
        [$templateClass, $foreignKey] = $this->legacyTemplateDefinition($account);
        if (! $templateClass) {
            return;
        }

        $table = (new $templateClass())->getTable();
        if (! Schema::hasTable($table)) {
            return;
        }

        $templateClass::query()
            ->where($foreignKey, $account->getKey())
            ->delete();
    }

    private function legacyTemplateDefinition(Model $account): array
    {
        return match (true) {
            $account instanceof User => [UserFaceTemplate::class, 'user_id'],
            $account instanceof Employee => [EmployeeFaceTemplate::class, 'employee_id'],
            $account instanceof Supervisor => [SupervisorFaceTemplate::class, 'supervisor_id'],
            $account instanceof Finance => [FinanceFaceTemplate::class, 'finance_id'],
            default => [null, null],
        };
    }
}

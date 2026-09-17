<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeEmployment;
use App\Models\EmployeeSeparation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_lifecycle_routes_are_registered(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());

        $expected = [
            ['POST', 'api/admin/employees/{employee}/onboarding'],
            ['POST', 'api/admin/employee-employments/{employeeEmployment}/onboarding/complete'],
            ['POST', 'api/employee/resignation'],
            ['GET', 'api/employee/resignation'],
            ['POST', 'api/admin/employees/{employee}/termination'],
            ['POST', 'api/admin/employee-separations/{employeeSeparation}/approve'],
            ['POST', 'api/admin/employee-separations/{employeeSeparation}/offboarding/start'],
            ['POST', 'api/admin/employee-separations/{employeeSeparation}/offboarding/complete'],
        ];

        foreach ($expected as [$method, $uri]) {
            $this->assertTrue(
                $routes->contains(fn ($route) => $route->uri() === $uri && in_array($method, $route->methods(), true)),
                "Route {$method} /{$uri} tidak terdaftar."
            );
        }
    }

    public function test_onboarding_transitions_employee_to_active_and_duplicate_onboarding_fails(): void
    {
        $admin = $this->makeAdmin();
        $employee = $this->makeEmployee(false);
        Sanctum::actingAs($admin);

        $startDate = now('Asia/Jakarta')->toDateString();

        $start = $this->postJson("/api/admin/employees/{$employee->id}/onboarding", [
            'start_date' => $startDate,
        ]);

        $start->assertCreated()
            ->assertJsonPath('data.current_status', 'ONBOARDING');

        $employmentId = $start->json('data.id');

        $this->assertDatabaseHas('employee_status_histories', [
            'employment_id' => $employmentId,
            'status' => 'ONBOARDING',
        ]);

        $this->postJson("/api/admin/employee-employments/{$employmentId}/onboarding/complete")
            ->assertOk()
            ->assertJsonPath('data.current_status', 'ACTIVE');

        $this->assertTrue($employee->fresh()->is_active);

        $this->postJson("/api/admin/employees/{$employee->id}/onboarding", [
            'start_date' => $startDate,
        ])->assertStatus(409);
    }

    public function test_resignation_requires_approval_and_offboarding_before_employee_becomes_inactive(): void
    {
        $admin = $this->makeAdmin();
        $employee = $this->makeEmployee(true);
        $employment = $this->makeActiveEmployment($employee, $admin);

        Sanctum::actingAs($employee);

        $today = now('Asia/Jakarta')->toDateString();
        $resignationResponse = $this->postJson('/api/employee/resignation', [
            'reason' => 'Personal reason',
            'last_working_date' => $today,
            'effective_date' => $today,
        ]);

        $resignationResponse->assertCreated()
            ->assertJsonPath('data.process_status', 'SUBMITTED')
            ->assertJsonPath('data.separation_type', 'RESIGNATION');

        $separationId = $resignationResponse->json('data.id');

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/employee-separations/{$separationId}/approve")
            ->assertOk()
            ->assertJsonPath('data.process_status', 'APPROVED');

        $this->assertSame('NOTICE_PERIOD', $employment->fresh()->current_status);
        $this->assertTrue($employee->fresh()->is_active);

        $this->postJson("/api/admin/employee-separations/{$separationId}/offboarding/start")
            ->assertOk();

        $this->postJson("/api/admin/employee-separations/{$separationId}/offboarding/complete")
            ->assertOk()
            ->assertJsonPath('data.process_status', 'COMPLETED');

        $this->assertSame('RESIGNED', $employment->fresh()->current_status);
        $this->assertFalse($employee->fresh()->is_active);
    }

    public function test_termination_cannot_be_repeated_after_offboarding_completion(): void
    {
        $admin = $this->makeAdmin();
        $employee = $this->makeEmployee(true);
        $employment = $this->makeActiveEmployment($employee, $admin);
        Sanctum::actingAs($admin);

        $today = now('Asia/Jakarta')->toDateString();
        $termination = $this->postJson("/api/admin/employees/{$employee->id}/termination", [
            'reason' => 'Policy violation',
            'effective_date' => $today,
        ]);

        $termination->assertCreated()
            ->assertJsonPath('data.process_status', 'SUBMITTED')
            ->assertJsonPath('data.separation_type', 'TERMINATION');

        $separationId = $termination->json('data.id');

        $this->postJson("/api/admin/employee-separations/{$separationId}/approve")
            ->assertOk()
            ->assertJsonPath('data.process_status', 'APPROVED');

        $this->postJson("/api/admin/employee-separations/{$separationId}/offboarding/start")
            ->assertOk();
        $this->postJson("/api/admin/employee-separations/{$separationId}/offboarding/complete")
            ->assertOk();

        $this->assertSame('TERMINATED', $employment->fresh()->current_status);
        $this->assertFalse($employee->fresh()->is_active);

        $this->postJson("/api/admin/employees/{$employee->id}/termination", [
            'reason' => 'Duplicate termination',
            'effective_date' => $today,
        ])->assertStatus(409);
    }

    public function test_inactive_employee_is_blocked_by_employee_middleware(): void
    {
        $employee = $this->makeEmployee(false);
        Sanctum::actingAs($employee);

        $this->getJson('/api/employee/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'Akun employee sudah tidak aktif.');
    }

    private function makeAdmin(): User
    {
        return User::query()->create([
            'name' => 'Lifecycle Admin',
            'email' => 'lifecycle-admin@example.test',
            'password' => Hash::make('password'),
        ]);
    }

    private function makeEmployee(bool $active): Employee
    {
        return Employee::query()->create([
            'employee_code' => 'EMP-'.uniqid(),
            'name' => 'Lifecycle Employee',
            'email' => uniqid().'@example.test',
            'password' => Hash::make('password'),
            'is_active' => $active,
        ]);
    }

    private function makeActiveEmployment(Employee $employee, User $admin): EmployeeEmployment
    {
        $employment = EmployeeEmployment::query()->create([
            'employee_id' => $employee->id,
            'start_date' => now('Asia/Jakarta')->subMonth()->toDateString(),
            'current_status' => 'ACTIVE',
            'onboarding_started_at' => now()->subMonth(),
            'onboarding_completed_at' => now()->subMonth(),
        ]);

        $employment->statusHistories()->create([
            'status' => 'ACTIVE',
            'effective_from' => now()->subMonth(),
            'changed_by' => $admin->id,
        ]);

        return $employment;
    }
}

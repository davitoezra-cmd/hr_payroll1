<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ShiftScheduleRouteRegistrationTest extends TestCase
{
    public function test_shift_schedule_api_routes_are_registered_with_required_prefixes(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        $expected = [
            ['GET', 'api/employee/my-shift'],
            ['GET', 'api/admin/shift-schedules'],
            ['POST', 'api/admin/shift-schedules'],
            ['POST', 'api/admin/shift-schedules/bulk'],
            ['GET', 'api/admin/shift-schedules/{employeeShiftSchedule}'],
            ['PUT', 'api/admin/shift-schedules/{employeeShiftSchedule}'],
            ['DELETE', 'api/admin/shift-schedules/{employeeShiftSchedule}'],
        ];

        foreach ($expected as [$method, $uri]) {
            $registered = $routes->contains(function ($route) use ($method, $uri) {
                return $route->uri() === $uri && in_array($method, $route->methods(), true);
            });

            $this->assertTrue(
                $registered,
                "Route {$method} /{$uri} tidak terdaftar."
            );
        }
    }
}

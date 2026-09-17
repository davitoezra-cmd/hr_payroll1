<?php

use App\Http\Controllers\Admin\AttendanceQrController;
use App\Http\Controllers\Admin\BusinessTripApprovalController;
use App\Http\Controllers\Admin\CashAdvanceApprovalController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\LeaveApprovalController as AdminLeaveApprovalController;
use App\Http\Controllers\Admin\MedicalLeaveApprovalController;
use App\Http\Controllers\Admin\MeetingController as AdminMeetingController;
use App\Http\Controllers\Admin\MealAllowanceApprovalController;
use App\Http\Controllers\Admin\PerformanceController;
use App\Http\Controllers\Admin\SupervisorController;
use App\Http\Controllers\Admin\TaskAssignmentController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\ShiftScheduleController;
use App\Http\Controllers\Admin\WorkShiftController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\PayrollExpenseController;
use App\Http\Controllers\Admin\CompanyPostController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\AttendanceLocationController;
use App\Http\Controllers\Admin\WhatsAppGatewayController;

use App\Http\Controllers\Portal\ShiftController as PortalShiftController;

use App\Http\Controllers\Finance\BalanceWithdrawalController as FinanceBalanceWithdrawalController;
use App\Http\Controllers\Finance\BPJSPaymentProofController;
use App\Http\Controllers\Finance\CashAdvanceController as FinanceCashAdvanceController;
use App\Http\Controllers\Finance\EmployeePayrollSettingController;
use App\Http\Controllers\Finance\MeetingController as FinanceMeetingController;
use App\Http\Controllers\Finance\PayrollController;
use App\Http\Controllers\Finance\PayrollCorrectionController;
use App\Http\Controllers\Finance\OperationalExpenseController;

use App\Http\Controllers\Portal\AttendanceQrController as PortalAttendanceQrController;
use App\Http\Controllers\Portal\BalanceController;
use App\Http\Controllers\Portal\BalanceWithdrawalController;
use App\Http\Controllers\Portal\BPJSPaymentProofController as PortalBPJSPaymentProofController;
use App\Http\Controllers\Portal\BusinessTripController;
use App\Http\Controllers\Portal\CashAdvanceController as PortalCashAdvanceController;
use App\Http\Controllers\Portal\EmployeePerformanceController as PortalEmployeePerformanceController;
use App\Http\Controllers\Portal\EmployeeTargetController as PortalEmployeeTargetController;
use App\Http\Controllers\Portal\LeaveRequestController as PortalLeaveRequestController;
use App\Http\Controllers\Portal\MedicalLeaveController;
use App\Http\Controllers\Portal\MeetingController as PortalMeetingController;
use App\Http\Controllers\Portal\MealAllowanceRequestController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\Portal\TaskController as PortalTaskController;
use App\Http\Controllers\Portal\PortalOperationalExpenseController;
use App\Http\Controllers\Portal\CompanyPostController as PortalCompanyPostController;
use App\Http\Controllers\Portal\EmployeeNotificationController;


use App\Http\Controllers\Supervisor\AttendanceReportController;
use App\Http\Controllers\Supervisor\EmployeeTargetController;
use App\Http\Controllers\Supervisor\MeetingController as SupervisorMeetingController;
use App\Http\Controllers\Supervisor\ProfileController as SupervisorProfileController;
use App\Http\Controllers\Supervisor\SupervisorRequestController;
use App\Http\Controllers\Supervisor\TaskController as SupervisorTaskController;

use App\Http\Controllers\Api\EmployeeEmploymentController;
use App\Http\Controllers\Api\EmployeeLifecycleTaskController;
use App\Http\Controllers\Api\EmployeeSeparationController;
use App\Http\Controllers\Api\EmployeeStatusHistoryController;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

use App\Services\WhatsAppService;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::post(
    '/face-login',
    [\App\Http\Controllers\Api\FaceAuthController::class, 'login']
)->middleware('throttle:30,1');

Route::post(
    '/face-login/select-account',
    [\App\Http\Controllers\Api\FaceAuthController::class, 'selectAccount']
)->middleware('throttle:20,1');


/*
|--------------------------------------------------------------------------
| SUPER ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    'superadmin',
])
    ->prefix('admin')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | AUTH / LOGOUT
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', [AuthController::class, 'logout']);


        Route::get('/dashboard', [DashboardController::class, 'superAdmin']);

        /*
        |--------------------------------------------------------------------------
        | PROFILE
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [ProfileController::class, 'edit']);

        Route::put('/profile', [ProfileController::class, 'update']);


        /*
        |--------------------------------------------------------------------------
        | FACE AUTH
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/face/status',
            [\App\Http\Controllers\Api\AccountFaceController::class, 'status']
        );

        Route::post(
            '/face/enroll',
            [\App\Http\Controllers\Api\AccountFaceController::class, 'enroll']
        )->middleware('throttle:10,1');

        Route::post(
            '/face/verify',
            [\App\Http\Controllers\Api\AccountFaceController::class, 'verify']
        )->middleware('throttle:10,1');

        Route::delete(
            '/face',
            [\App\Http\Controllers\Api\AccountFaceController::class, 'destroy']
        );
            Route::get('/attendance-locations', [ AttendanceLocationController::class, 'index' ]); 
            Route::post('/attendance-locations', [ AttendanceLocationController::class, 'store' ]); 
            Route::get('/attendance-locations/{attendanceLocation}', [ AttendanceLocationController::class, 'show' ]);
             Route::put('/attendance-locations/{attendanceLocation}', [ AttendanceLocationController::class, 'update' ]); 
             Route::patch('/attendance-locations/{attendanceLocation}/toggle-status', [ AttendanceLocationController::class, 'toggleStatus' ]); 
             Route::delete('/attendance-locations/{attendanceLocation}', [ AttendanceLocationController::class, 'destroy' ]);

       

        // route admin lainnya


        // ==========================================
        // WHATSAPP GATEWAY
        // ==========================================

        Route::get('/whatsapp-gateway', [
            WhatsAppGatewayController::class,
            'index'
        ]);

        Route::post('/whatsapp-gateway', [
            WhatsAppGatewayController::class,
            'store'
        ]);

        Route::put('/whatsapp-gateway/{whatsappGateway}', [
            WhatsAppGatewayController::class,
            'update'
        ]);

        Route::patch('/whatsapp-gateway/{whatsappGateway}/toggle-status', [
            WhatsAppGatewayController::class,
            'toggleStatus'
        ]);

        Route::delete('/whatsapp-gateway/{whatsappGateway}', [
            WhatsAppGatewayController::class,
            'destroy'
        ]);

        Route::post('/whatsapp-gateway/{whatsappGateway}/test', [
    WhatsAppGatewayController::class,
    'test'
]);

Route::post('/whatsapp-gateway/{whatsappGateway}/test-send', [
    WhatsAppGatewayController::class,
    'testSend'
]);

    

        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE QR
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/attendance-qr',
            [AttendanceQrController::class, 'index']
        );

        Route::get(
            '/attendance-qr/show',
            [AttendanceQrController::class, 'show']
        );

        Route::post(
            '/attendance-qr/generate',
            [AttendanceQrController::class, 'generate']
        );

        Route::put(
            '/attendance-qr/{id}/regenerate',
            [AttendanceQrController::class, 'regenerate']
        );

        Route::put(
            '/attendance-qr/{id}/activate',
            [AttendanceQrController::class, 'activate']
        );

        Route::put(
            '/attendance-qr/{id}/deactivate',
            [AttendanceQrController::class, 'deactivate']
        );

        Route::put(
            '/attendance-qr/{id}/expired',
            [AttendanceQrController::class, 'setExpired']
        );

        Route::delete(
            '/attendance-qr/{id}',
            [AttendanceQrController::class, 'destroy']
        );

        Route::get(
            '/attendance-qr/{id}/download',
            [AttendanceQrController::class, 'download']
        );

        Route::get(
            '/attendance-qr/{id}/image',
            [AttendanceQrController::class, 'image']
        );

        Route::get(
            '/attendance-qr/statistics',
            [AttendanceQrController::class, 'statistics']
        );

         Route::get(
        '/payroll-expenses',
        [PayrollExpenseController::class, 'index']
    );

    Route::get(
        '/payroll-expenses/{tahun}/{bulan}',
        [PayrollExpenseController::class, 'monthlyDetail']
    );


        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE
        |--------------------------------------------------------------------------
        */

        Route::apiResource(
            'employees',
            EmployeeController::class
        );


        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE LIFECYCLE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/employee-employments',
            [EmployeeEmploymentController::class, 'index']
        );

        Route::get(
            '/employee-employments/{employeeEmployment}',
            [EmployeeEmploymentController::class, 'show']
        );

        Route::post(
            '/employees/{employee}/onboarding',
            [EmployeeEmploymentController::class, 'startOnboarding']
        );

        Route::post(
            '/employee-employments/{employeeEmployment}/onboarding/complete',
            [EmployeeEmploymentController::class, 'completeOnboarding']
        );


        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE SEPARATION
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/employee-separations',
            [EmployeeSeparationController::class, 'index']
        );

        Route::get(
            '/employee-separations/{employeeSeparation}',
            [EmployeeSeparationController::class, 'show']
        );

        Route::post(
            '/employees/{employee}/termination',
            [EmployeeSeparationController::class, 'terminate']
        );

        Route::post(
            '/employee-separations/{employeeSeparation}/approve',
            [EmployeeSeparationController::class, 'approve']
        );

        Route::post(
            '/employee-separations/{employeeSeparation}/reject',
            [EmployeeSeparationController::class, 'reject']
        );

        Route::post(
            '/employee-separations/{employeeSeparation}/offboarding/start',
            [EmployeeSeparationController::class, 'startOffboarding']
        );

        Route::post(
            '/employee-separations/{employeeSeparation}/offboarding/complete',
            [EmployeeSeparationController::class, 'complete']
        );


        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE LIFECYCLE TASKS
        |--------------------------------------------------------------------------
        */

        Route::apiResource(
            'employee-lifecycle-tasks',
            EmployeeLifecycleTaskController::class
        )->only([
            'index',
            'show',
            'store',
            'update',
        ]);


        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE STATUS HISTORY
        |--------------------------------------------------------------------------
        */

        Route::apiResource(
            'employee-status-histories',
            EmployeeStatusHistoryController::class
        )->only([
            'index',
            'show',
        ]);


        /*
        |--------------------------------------------------------------------------
        | FINANCE MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::apiResource(
            'finances',
            FinanceController::class
        );


        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::apiResource(
            'supervisors',
            SupervisorController::class
        );


        /*
        |--------------------------------------------------------------------------
        | MEETING
        |--------------------------------------------------------------------------
        */

        Route::get(
            'meetings/participants',
            [AdminMeetingController::class, 'availableParticipants']
        );

        Route::apiResource(
            'meetings',
            AdminMeetingController::class
        );


        /*
        |--------------------------------------------------------------------------
        | LEAVE APPROVAL
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/leave',
            [AdminLeaveApprovalController::class, 'index']
        );

        Route::get(
            '/leave/{id}',
            [AdminLeaveApprovalController::class, 'show']
        );

        Route::put(
            '/leave/{id}/approve',
            [AdminLeaveApprovalController::class, 'approve']
        );

        Route::put(
            '/leave/{id}/reject',
            [AdminLeaveApprovalController::class, 'reject']
        );


        /*
        |--------------------------------------------------------------------------
        | MEDICAL LEAVE APPROVAL
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/medical-leave',
            [MedicalLeaveApprovalController::class, 'index']
        );

        Route::get(
            '/medical-leave/{id}',
            [MedicalLeaveApprovalController::class, 'show']
        );

        Route::put(
            '/medical-leave/{id}/approve',
            [MedicalLeaveApprovalController::class, 'approve']
        );

        Route::put(
            '/medical-leave/{id}/reject',
            [MedicalLeaveApprovalController::class, 'reject']
        );


        /*
        |--------------------------------------------------------------------------
        | uang makan APPROVAL
        |--------------------------------------------------------------------------
        */
    Route::get(
        '/meal-allowance',
        [MealAllowanceApprovalController::class, 'index']
    );

    Route::get(
        '/meal-allowance/{id}',
        [MealAllowanceApprovalController::class, 'show']
    );

    Route::post(
        '/meal-allowance/{id}/approve',
        [MealAllowanceApprovalController::class, 'approve']
    );

    Route::post(
        '/meal-allowance/{id}/reject',
        [MealAllowanceApprovalController::class, 'reject']
    );

        


        /*
        |--------------------------------------------------------------------------
        | CASH ADVANCE APPROVAL
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/cash-advance',
            [CashAdvanceApprovalController::class, 'index']
        );

        Route::get(
            '/cash-advance/{id}',
            [CashAdvanceApprovalController::class, 'show']
        );

        Route::put(
            '/cash-advance/{id}/approve',
            [CashAdvanceApprovalController::class, 'approve']
        );

        Route::put(
            '/cash-advance/{id}/reject',
            [CashAdvanceApprovalController::class, 'reject']
        );


        /*
        |--------------------------------------------------------------------------
        | BUSINESS TRIP APPROVAL
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/business-trip',
            [BusinessTripApprovalController::class, 'index']
        );

        Route::get(
            '/business-trip/{id}',
            [BusinessTripApprovalController::class, 'show']
        );

        Route::put(
            '/business-trip/{id}/approve',
            [BusinessTripApprovalController::class, 'approve']
        );

        Route::put(
            '/business-trip/{id}/reject',
            [BusinessTripApprovalController::class, 'reject']
        );


        /*
        |--------------------------------------------------------------------------
        | PERFORMANCE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/performance',
            [PerformanceController::class, 'index']
        );


        /*
        |--------------------------------------------------------------------------
        | PERFORMANCE - TARGET
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/performance/targets',
            [PerformanceController::class, 'targets']
        );

        Route::post(
            '/performance/targets',
            [PerformanceController::class, 'storeTarget']
        );

        Route::get(
            '/performance/targets/{id}',
            [PerformanceController::class, 'showTarget']
        );

        Route::put(
            '/performance/targets/{id}',
            [PerformanceController::class, 'updateTarget']
        );

        Route::delete(
            '/performance/targets/{id}',
            [PerformanceController::class, 'destroyTarget']
        );

        Route::patch(
            '/performance/targets/{id}/progress',
            [PerformanceController::class, 'updateProgress']
        );


        /*
        |--------------------------------------------------------------------------
        | PERFORMANCE - EVALUATIONS
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/performance/evaluations',
            [PerformanceController::class, 'performances']
        );

        Route::post(
            '/performance/evaluations',
            [PerformanceController::class, 'storePerformance']
        );

        Route::get(
            '/performance/evaluations/{id}',
            [PerformanceController::class, 'showPerformance']
        );

        Route::put(
            '/performance/evaluations/{id}',
            [PerformanceController::class, 'updatePerformance']
        );

        Route::delete(
            '/performance/evaluations/{id}',
            [PerformanceController::class, 'destroyPerformance']
        );


        /*
        |--------------------------------------------------------------------------
        | PERFORMANCE - FORM DATA
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/performance/employees',
            [PerformanceController::class, 'employees']
        );

        Route::get(
            '/performance/supervisors',
            [PerformanceController::class, 'supervisors']
        );


        /*
        |--------------------------------------------------------------------------
        | TEAM MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/teams',
            [TeamController::class, 'index']
        );

        Route::get(
            '/teams/available-members',
            [TeamController::class, 'availableMembers']
        );

        Route::post(
            '/teams',
            [TeamController::class, 'store']
        );

        Route::get(
            '/teams/{id}',
            [TeamController::class, 'show']
        );

        Route::put(
            '/teams/{id}',
            [TeamController::class, 'update']
        );

        Route::delete(
            '/teams/{id}',
            [TeamController::class, 'destroy']
        );


        /*
        |--------------------------------------------------------------------------
        | TASK ASSIGNMENT
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/task-assignments',
            [TaskAssignmentController::class, 'index']
        );

        Route::get(
            '/task-assignments/available-teams',
            [TaskAssignmentController::class, 'availableTeams']
        );

        Route::post(
            '/task-assignments',
            [TaskAssignmentController::class, 'store']
        );

        Route::get(
            '/task-assignments/{id}',
            [TaskAssignmentController::class, 'show']
        );

        Route::put(
            '/task-assignments/{id}',
            [TaskAssignmentController::class, 'update']
        );

        Route::delete(
            '/task-assignments/{id}',
            [TaskAssignmentController::class, 'destroy']
        );


        /*
        |--------------------------------------------------------------------------
        | WORK SHIFTS
        |--------------------------------------------------------------------------
        */

        Route::apiResource(
            'work-shifts',
            WorkShiftController::class
        );


        /*
        |--------------------------------------------------------------------------
        | SHIFT SCHEDULES
        |--------------------------------------------------------------------------
        */

        Route::prefix('shift-schedules')
            ->controller(ShiftScheduleController::class)
            ->group(function () {

                Route::get('/', 'index');

                Route::post('/', 'store');

                Route::post('/bulk', 'bulkStore');

                Route::get(
                    '/{employeeShiftSchedule}',
                    'show'
                )->whereNumber('employeeShiftSchedule');

                Route::put(
                    '/{employeeShiftSchedule}',
                    'update'
                )->whereNumber('employeeShiftSchedule');

                Route::delete(
                    '/{employeeShiftSchedule}',
                    'destroy'
                )->whereNumber('employeeShiftSchedule');
            });


       
        Route::apiResource(
            'inventories',
            InventoryController::class
        );

         Route::get('/company-posts', [
        CompanyPostController::class,
        'index'
    ]);

    Route::post('/company-posts', [
        CompanyPostController::class,
        'store'
    ]);

    Route::get('/company-posts/{companyPost}', [
        CompanyPostController::class,
        'show'
    ]);

    Route::post('/company-posts/{companyPost}', [
        CompanyPostController::class,
        'update'
    ]);

    Route::delete('/company-posts/{companyPost}', [
        CompanyPostController::class,
        'destroy'
    ]);

    Route::patch('/company-posts/{companyPost}/publish', [
        CompanyPostController::class,
        'publish'
    ]);

    Route::patch('/company-posts/{companyPost}/unpublish', [
        CompanyPostController::class,
        'unpublish'

    
    ]);

     Route::get('/documents', [DocumentController::class, 'index']);

    Route::post('/documents', [DocumentController::class, 'store']);

    // =====================================================
// PREVIEW CEPAT - BUAT TEMPORARY SIGNED URL
// =====================================================
Route::get(
    '/documents/{document}/preview-url',
    [DocumentController::class, 'previewUrl']
);



// Preview file
    Route::get('/documents/{document}/preview', [DocumentController::class,'preview']);

// Detail dokumen
    Route::get('/documents/{document}', [ DocumentController::class,'show']);

// Update dokumen
    Route::post('/documents/{document}', [DocumentController::class,'update']);

// Download dokumen
    Route::get('/documents/{document}/download', [DocumentController::class,'download']);

// Hapus dokumen
    Route::delete('/documents/{document}', [DocumentController::class,'destroy']);

});
Route::get(
    '/admin/documents/{document}/preview-signed',
    [DocumentController::class, 'previewSigned']
)
    ->name('admin.documents.preview.signed')
    ->middleware('signed');
    

/*
 * |--------------------------------------------------------------------------
 * | EMPLOYEE
 * |--------------------------------------------------------------------------
 */

Route::middleware([
    'auth:sanctum',
    'employee',
])
    ->prefix('employee')
    ->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/dashboard', [DashboardController::class, 'employee']);

         Route::get(
        '/notifications',
        [EmployeeNotificationController::class, 'index']
    );

         Route::get(
        '/notifications/unread-count',
        [EmployeeNotificationController::class, 'unreadCount']
    );

        Route::post(
        '/notifications/{id}/read',
        [EmployeeNotificationController::class, 'markAsRead']
    );

    Route::post(
        '/notifications/read-all',
        [EmployeeNotificationController::class, 'markAllAsRead']
    );

        /*
         * |--------------------------------------------------------------------------
         * | PROFILE
         * |--------------------------------------------------------------------------
         */

        Route::get('/profile', [PortalProfileController::class, 'edit']);

        Route::put('/profile', [PortalProfileController::class, 'update']);

        /*
         * |--------------------------------------------------------------------------
         * | RESIGNATION
         * |--------------------------------------------------------------------------
         */
        Route::get('/resignation', [EmployeeSeparationController::class, 'myResignation']);
        Route::post('/resignation', [EmployeeSeparationController::class, 'requestResignation']);

        /*
         * |--------------------------------------------------------------------------
         * | SCAN QR
         * |--------------------------------------------------------------------------
         */

        Route::post('/attendance/scan', [PortalAttendanceQrController::class, 'scan']);

        /*
         * |--------------------------------------------------------------------------
         * | LEAVE REQUEST
         * |--------------------------------------------------------------------------
         */

        Route::get('/leave', [PortalLeaveRequestController::class, 'index']);

        Route::post('/leave', [PortalLeaveRequestController::class, 'store']);

        Route::get('/leave/{id}', [PortalLeaveRequestController::class, 'show']);

        Route::delete('/leave/{id}', [PortalLeaveRequestController::class, 'destroy']);

        Route::get('/medical-leave', [MedicalLeaveController::class, 'index']);
        Route::post('/medical-leave', [MedicalLeaveController::class, 'store']);
        Route::get('/medical-leave/{id}', [MedicalLeaveController::class, 'show']);
        Route::delete('/medical-leave/{id}', [MedicalLeaveController::class, 'destroy']);

        Route::get('/meal-allowance',[MealAllowanceRequestController::class, 'index']);

        Route::post('/meal-allowance',[MealAllowanceRequestController::class, 'store']);

        Route::get('/meal-allowance/{id}',[MealAllowanceRequestController::class, 'show']);

        Route::delete('/meal-allowance/{id}',[MealAllowanceRequestController::class, 'destroy'] );

        /*
         * |--------------------------------------------------------------------------
         * | CASH ADVANCE
         * |--------------------------------------------------------------------------
         */

        Route::get('/cash-advance', [PortalCashAdvanceController::class, 'index']);
        Route::post('/cash-advance', [PortalCashAdvanceController::class, 'store']);
        Route::get('/cash-advance/{id}', [PortalCashAdvanceController::class, 'show']);
        Route::delete('/cash-advance/{id}', [PortalCashAdvanceController::class, 'destroy']);

        /*
         * |--------------------------------------------------------------------------
         * | BUSINESS TRIP
         * |--------------------------------------------------------------------------
         */

        Route::get('/business-trip', [BusinessTripController::class, 'index']);

        Route::post('/business-trip', [BusinessTripController::class, 'store']);

        Route::get('/business-trip/{id}', [BusinessTripController::class, 'show']);

        Route::delete('/business-trip/{id}', [BusinessTripController::class, 'destroy']);

        Route::post('/business-trip/{id}/check-in', [BusinessTripController::class, 'checkIn']);

        Route::post('/business-trip/{id}/check-out', [BusinessTripController::class, 'checkOut']);

        /*
         * |--------------------------------------------------------------------------
         * | ATTENDANCE
         * |--------------------------------------------------------------------------
         */

        Route::get('/attendance', [AttendanceController::class, 'index']);

        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);

        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);

        Route::get('/bpjs-payment-proofs', [PortalBPJSPaymentProofController::class, 'index']);
        Route::get('/bpjs-payment-proofs/{id}', [PortalBPJSPaymentProofController::class, 'show']);
         Route::get('/bpjs-payment-proofs/{id}/download',[PortalBPJSPaymentProofController::class, 'download']);

        Route::get('/my-targets', [PortalEmployeeTargetController::class, 'index']);

        Route::get('/my-targets/{id}', [PortalEmployeeTargetController::class, 'show']);

        Route::put('/my-targets/{id}/progress', [PortalEmployeeTargetController::class, 'updateProgress']);

        Route::get('/my-performances', [PortalEmployeePerformanceController::class, 'index']);
        Route::get('/my-performances/{id}', [PortalEmployeePerformanceController::class, 'show']);

        Route::get('/tasks', [PortalTaskController::class, 'myTasks']);
        Route::get('/tasks/{id}', [PortalTaskController::class, 'show']);
        Route::put('/tasks/{id}/status', [PortalTaskController::class, 'updateStatus']);

        // saldo employee
        Route::get('/balance', [BalanceController::class, 'myBalance']);

        // Riwayat saldo
        Route::get('/balance/transactions', [BalanceController::class, 'myTransactions']);

        Route::get('/balance-withdrawals', [BalanceWithdrawalController::class, 'index']);

        Route::post('/balance-withdrawals', [BalanceWithdrawalController::class, 'store']);

        /*
         * |--------------------------------------------------------------------------
         * | FACE RECOGNITION
         * |--------------------------------------------------------------------------
         */
        Route::get(
            '/face/status',
            [\App\Http\Controllers\Api\AccountFaceController::class, 'status']
        );

        Route::post(
            '/face/enroll',
            [\App\Http\Controllers\Api\AccountFaceController::class, 'enroll']
        )->middleware('throttle:10,1');

        Route::post(
            '/face/verify',
            [\App\Http\Controllers\Api\AccountFaceController::class, 'verify']
        )->middleware('throttle:10,1');

        Route::delete(
            '/face',
            [\App\Http\Controllers\Api\AccountFaceController::class, 'destroy']
        );

        Route::get('meetings', [PortalMeetingController::class, 'index']);
        Route::get('meetings/{meeting}', [PortalMeetingController::class, 'show']);

        // my-shift
        Route::get('/my-shift', [PortalShiftController::class, 'today']);

          Route::get(
            '/operational-expenses',
            [PortalOperationalExpenseController::class, 'index']
        );

        Route::get(
            '/operational-expenses/summary',
            [PortalOperationalExpenseController::class, 'summary']
        );

        Route::get(
            '/operational-expenses/{id}',
            [PortalOperationalExpenseController::class, 'show']
        );

         Route::get('/company-posts', [
        PortalCompanyPostController::class,
        'index'
    ]);

    Route::get('/company-posts/{companyPost}', [
        PortalCompanyPostController::class,
        'show'
    ]);
    });

Route::prefix('finance')
    ->middleware(['auth:sanctum', 'finance'])
    ->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [DashboardController::class, 'finance']);

        Route::get('/profile', [ProfileController::class, 'edit']);
        Route::put('/profile', [ProfileController::class, 'update']);

        // face-api
        Route::get('/face/status', [\App\Http\Controllers\Api\AccountFaceController::class, 'status']);
        Route::post('/face/enroll', [\App\Http\Controllers\Api\AccountFaceController::class, 'enroll'])->middleware('throttle:10,1');
        Route::post('/face/verify', [\App\Http\Controllers\Api\AccountFaceController::class, 'verify'])->middleware('throttle:10,1');
        Route::delete('/face', [\App\Http\Controllers\Api\AccountFaceController::class, 'destroy']);

        Route::get('/employees', [EmployeeController::class, 'index']);

        Route::apiResource(
            'employee-payroll-settings',
            EmployeePayrollSettingController::class
        );

        Route::get(
            '/payroll',
            [PayrollController::class, 'index']
        );

        Route::get('/payroll/{id}', [PayrollController::class, 'show']);

        Route::post(
            '/payroll/generate',
            [PayrollController::class, 'generate']
        );

        Route::put(
            '/payroll/{payroll}/paid',
            [PayrollController::class, 'paid']
        );

        Route::delete(
            '/payroll/{payroll}',
            [PayrollController::class, 'destroy']
        );

        Route::get(
            '/payroll/employee/{employeeId}',
            [PayrollController::class, 'employeeHistory']
        );

        Route::get('/cash-advance', [FinanceCashAdvanceController::class, 'index']);

        Route::get('/cash-advance/{id}', [FinanceCashAdvanceController::class, 'show']);

        Route::put('/cash-advance/{id}/pay', [FinanceCashAdvanceController::class, 'pay']);

        Route::apiResource(
            'payroll-correction',
            PayrollCorrectionController::class
        );

        Route::get('/bpjs-payment-proofs', [BPJSPaymentProofController::class, 'index']);
        Route::post('/bpjs-payment-proofs', [BPJSPaymentProofController::class, 'store']);
        Route::get('/bpjs-payment-proofs/{id}', [BPJSPaymentProofController::class, 'show']);
        Route::put('/bpjs-payment-proofs/{id}', [BPJSPaymentProofController::class, 'update']);
        Route::delete('/bpjs-payment-proofs/{id}', [BPJSPaymentProofController::class, 'destroy']);

        Route::get('/balance-withdrawals', [FinanceBalanceWithdrawalController::class, 'index']);

        Route::post('/balance-withdrawals/{id}/approve', [FinanceBalanceWithdrawalController::class, 'approve']);

        Route::post('/balance-withdrawals/{id}/reject', [FinanceBalanceWithdrawalController::class, 'reject']);

        Route::get('meetings', [FinanceMeetingController::class, 'index']);
        Route::get('meetings/{meeting}', [FinanceMeetingController::class, 'show']);

         Route::get('/employees', [OperationalExpenseController::class, 'employees']);

          // Daftar transaksi pengeluaran
    Route::get(
        '/operational-expenses',
        [OperationalExpenseController::class, 'index']
    );

    // Tambah transaksi pengeluaran
    Route::post(
        '/operational-expenses',
        [OperationalExpenseController::class, 'store']
    );

    // Detail transaksi
    Route::get(
        '/operational-expenses/{id}',
        [OperationalExpenseController::class, 'show']
    );

    // Update transaksi
    Route::put(
        '/operational-expenses/{id}',
        [OperationalExpenseController::class, 'update']
    );

    // Hapus transaksi
    Route::delete(
        '/operational-expenses/{id}',
        [OperationalExpenseController::class, 'destroy']
    );
});
    
Route::prefix('supervisor')
    ->middleware(['auth:sanctum', 'supervisor'])
    ->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [DashboardController::class, 'supervisor']);

        Route::get('/profile', [SupervisorProfileController::class, 'edit']);
        Route::put('/profile', [SupervisorProfileController::class, 'update']);

        Route::get('/face/status', [\App\Http\Controllers\Api\AccountFaceController::class, 'status']);
        Route::post('/face/enroll', [\App\Http\Controllers\Api\AccountFaceController::class, 'enroll'])->middleware('throttle:10,1');
        Route::post('/face/verify', [\App\Http\Controllers\Api\AccountFaceController::class, 'verify'])->middleware('throttle:10,1');
        Route::delete('/face', [\App\Http\Controllers\Api\AccountFaceController::class, 'destroy']);

        Route::get('/attendance-report', [AttendanceReportController::class, 'index']);

        Route::get('/requests', [SupervisorRequestController::class, 'index']);

        Route::get('/requests/statistics', [SupervisorRequestController::class, 'statistics']);

        Route::get('/requests/{type}/{id}', [SupervisorRequestController::class, 'show']);

        Route::get('/employee-targets', [EmployeeTargetController::class, 'index']);
        Route::get('/employee-targets/{id}', [EmployeeTargetController::class, 'show']);
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::get('/tasks', [SupervisorTaskController::class, 'myTasks']);

        // Detail task
        Route::get('/tasks/{id}', [SupervisorTaskController::class, 'show']);

        // Update status task
        Route::put('/tasks/{id}/status', [SupervisorTaskController::class, 'updateStatus']);

        // Melihat anggota team supervisor
        Route::get('/team-members', [SupervisorTaskController::class, 'myTeamMembers']);

        Route::get('meetings', [SupervisorMeetingController::class, 'index']);
        Route::get('meetings/{meeting}', [SupervisorMeetingController::class, 'show']);
    });

    Route::get('/test-whatsapp', function (WhatsAppService $whatsapp) {
    return response()->json(
        $whatsapp->send(
            '6285703128742',
            'Halo, ini test WhatsApp dari sistem HR Payroll.'
        )
    );
});

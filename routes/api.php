<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Next.js Frontend APIs
Route::prefix('nextjs')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\NextJsApiController::class, 'login']);
    Route::get('/dashboard-stats', [\App\Http\Controllers\Api\NextJsApiController::class, 'getDashboardStats']);
    Route::get('/technical-users', [\App\Http\Controllers\Api\NextJsApiController::class, 'getTechnicalUsers']);
    Route::get('/roles-modules', [\App\Http\Controllers\Api\NextJsApiController::class, 'getRolesAndModules']);
    Route::get('/hod-assignments', [\App\Http\Controllers\Api\NextJsApiController::class, 'getHodAssignments']);

    // HR - Add New Staff
    Route::get('/hr/add-staff/form-data', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getFormData']);
    Route::get('/hr/add-staff/designations/{deptID}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getDesignations']);
    Route::get('/hr/add-staff/units/{deptID}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getUnits']);
    Route::get('/hr/add-staff/lgas/{stateID}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getLgas']);
    Route::post('/hr/add-staff', [\App\Http\Controllers\Api\HrStaffApiController::class, 'store']);
    Route::get('/hr/add-staff/list', [\App\Http\Controllers\Api\HrStaffApiController::class, 'list']);
    // HR - Staff Documentation Wizard
    Route::prefix('hr/documentation')->group(function () {
        Route::get('/{id}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getDocumentation']);
        Route::post('/{id}/basic', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveBasicInfo']);
        Route::post('/{id}/origin', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveOrigin']);
        Route::post('/{id}/contact', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveContact']);
        Route::post('/{id}/education', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveEducation']);
        Route::delete('/{id}/education/{educationId}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'deleteEducation']);
        Route::post('/{id}/marital', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveMarital']);
        Route::post('/{id}/next-of-kin', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveNextOfKin']);
        Route::post('/{id}/children', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveChildren']);
        Route::post('/{id}/experience', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveExperience']);
        Route::post('/{id}/account', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveAccount']);
        Route::post('/{id}/others', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveOthers']);
        Route::post('/{id}/media', [\App\Http\Controllers\Api\HrStaffApiController::class, 'savePassportSignature']);
        Route::post('/{id}/attachment', [\App\Http\Controllers\Api\HrStaffApiController::class, 'saveAttachment']);
        Route::post('/{id}/attachment-complete', [\App\Http\Controllers\Api\HrStaffApiController::class, 'completeAttachment']);
        Route::delete('/{id}/attachment/{attachmentId}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'deleteAttachment']);
        Route::post('/{id}/submit', [\App\Http\Controllers\Api\HrStaffApiController::class, 'submitDocumentation']);
        Route::get('/{id}/profile', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getProfileDetails']);
    });

    // HR - Leave Types
    Route::prefix('hr/leave-types')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getLeaveTypes']);
        Route::post('/', [\App\Http\Controllers\Api\HrStaffApiController::class, 'storeLeaveType']);
        Route::put('/{id}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'updateLeaveType']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'deleteLeaveType']);
    });

    // HR - Apply Leave (mirrors Blade LeaveCreateController)
    Route::prefix('hr/apply-leave')->group(function () {
        Route::get('/',                     [\App\Http\Controllers\Api\HrLeaveApiController::class, 'getApplyLeaveData']);
        Route::get('/records',              [\App\Http\Controllers\Api\HrLeaveApiController::class, 'getLeaveRecords']);
        Route::post('/',                    [\App\Http\Controllers\Api\HrLeaveApiController::class, 'saveApplyLeave']);
        Route::put('/{id}',                 [\App\Http\Controllers\Api\HrLeaveApiController::class, 'updateApplyLeave']);
        Route::get('/calculate-end-date',   [\App\Http\Controllers\Api\HrLeaveApiController::class, 'calculateEndDate']);
        Route::get('/hod-approve/{id}',     [\App\Http\Controllers\Api\HrLeaveApiController::class, 'hodApprove']);
        Route::get('/hod-reject/{id}',      [\App\Http\Controllers\Api\HrLeaveApiController::class, 'hodReject']);
        Route::get('/admin-approve/{id}',   [\App\Http\Controllers\Api\HrLeaveApiController::class, 'adminApprove']);
        Route::get('/admin-reject/{id}',    [\App\Http\Controllers\Api\HrLeaveApiController::class, 'adminReject']);
    });

    // HR - Apply Leave of Absence (LOA)
    Route::prefix('hr/apply-loa')->group(function () {
        Route::get('/',                     [\App\Http\Controllers\Api\HrLeaveApiController::class, 'getApplyLoaData']);
        Route::get('/records',              [\App\Http\Controllers\Api\HrLeaveApiController::class, 'getLoaRecords']);
        Route::post('/',                    [\App\Http\Controllers\Api\HrLeaveApiController::class, 'saveApplyLoa']);
        Route::put('/{id}',                 [\App\Http\Controllers\Api\HrLeaveApiController::class, 'updateApplyLoa']);
        Route::get('/hod-approve/{id}',     [\App\Http\Controllers\Api\HrLeaveApiController::class, 'hodApproveLoa']);
        Route::get('/hod-reject/{id}',      [\App\Http\Controllers\Api\HrLeaveApiController::class, 'hodRejectLoa']);
        Route::get('/admin-approve/{id}',   [\App\Http\Controllers\Api\HrLeaveApiController::class, 'adminApproveLoa']);
        Route::get('/admin-reject/{id}',    [\App\Http\Controllers\Api\HrLeaveApiController::class, 'adminRejectLoa']);
    });

    // HR - Update Staff Status & Transfers
    Route::prefix('hr/staff-status')->group(function () {
        Route::get('/',                     [\App\Http\Controllers\Api\HrStaffStatusApiController::class, 'getStaffStatusData']);
        Route::post('/find-staff',          [\App\Http\Controllers\Api\HrStaffStatusApiController::class, 'findStaff']);
        Route::post('/get-staff-by-division', [\App\Http\Controllers\Api\HrStaffStatusApiController::class, 'getStaffByDivision']);
        Route::post('/update',              [\App\Http\Controllers\Api\HrStaffStatusApiController::class, 'updateStatusOrTransfer']);
        Route::get('/pending-transfers',    [\App\Http\Controllers\Api\HrStaffStatusApiController::class, 'getPendingTransfers']);
        Route::post('/approve-transfers',   [\App\Http\Controllers\Api\HrStaffStatusApiController::class, 'approveOrRejectTransfers']);
    });

    // HR - Department & Designation submodules
    Route::get('/hr/basic/section', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'getDepartments']);
    Route::post('/hr/basic/section', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'handleDepartment']);
    Route::get('/hr/basic/designation', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'getDesignations']);
    Route::post('/hr/basic/designation', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'handleDesignation']);
    Route::post('/hr/basic/designation/edit', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'updateDesignation']);
    Route::post('/hr/basic/designation/delete', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'deleteDesignation']);

    // HR - Unit submodules
    Route::get('/hr/basic/unit', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'getUnits']);
    Route::post('/hr/basic/unit', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'handleUnit']);
    Route::post('/hr/basic/unit/edit', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'updateUnit']);
    Route::post('/hr/basic/unit/delete', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'deleteUnit']);

    // HR - LGA Covered submodules
    Route::get('/hr/lga/covered', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'getLgaCovered']);
    Route::post('/hr/lga/covered/add', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'storeLga']);
    Route::post('/hr/lga/covered/edit', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'updateLga']);
    Route::post('/hr/lga/covered/remove/{lgaId}', [\App\Http\Controllers\Api\HrBasicParameterApiController::class, 'deleteLga']);

    // Payroll
    Route::prefix('payroll')->group(function () {
        Route::get('/metadata', [\App\Http\Controllers\Api\NextJsPayrollApiController::class, 'getMetadata']);
        Route::get('/',         [\App\Http\Controllers\Api\NextJsPayrollApiController::class, 'getPayrollList']);
        Route::get('/export',   [\App\Http\Controllers\Api\NextJsPayrollApiController::class, 'exportPayroll']);
        Route::post('/compute', [\App\Http\Controllers\Api\NextJsPayrollApiController::class, 'computeSalary']);

        // Salary Structures
        Route::prefix('salary-structures')->group(function () {
            Route::get('/staff',   [\App\Http\Controllers\Api\SalaryStructureApiController::class, 'getStaffList']);
            Route::get('/',        [\App\Http\Controllers\Api\SalaryStructureApiController::class, 'index']);
            Route::post('/',       [\App\Http\Controllers\Api\SalaryStructureApiController::class, 'store']);
            Route::post('/upload', [\App\Http\Controllers\Api\SalaryStructureApiController::class, 'upload']);
        });

        // Employee Loans
        Route::prefix('loans')->group(function () {
            Route::get('/staff',   [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'getStaffList']);
            Route::get('/types',   [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'getLoanTypes']);
            Route::post('/types',  [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'storeLoanType']);
            Route::delete('/types/{id}', [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'destroyLoanType']);
            Route::get('/',        [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'index']);
            Route::post('/',       [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'store']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'destroy']);
            Route::get('/hod-approve/{id}', [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'hodApprove']);
            Route::get('/hod-reject/{id}',  [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'hodReject']);
            Route::get('/audit-approve/{id}', [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'auditApprove']);
            Route::get('/audit-reject/{id}',  [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'auditReject']);
            Route::get('/admin-approve/{id}', [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'adminApprove']);
            Route::get('/admin-reject/{id}',  [\App\Http\Controllers\Api\EmployeeLoanApiController::class, 'adminReject']);
        });
        
        // Staff Control Variables
        Route::prefix('staff-control-variables')->group(function () {
            Route::get('/staff', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'getStaffList']);
            Route::get('/variable-types', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'getVariableTypes']);
            Route::get('/descriptions/{particularId}', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'getDescriptions']);
            Route::get('/', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'store']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'destroy']);
        });

        // CV Setups
        Route::prefix('cv-setups')->group(function () {
            Route::get('/banks', [\App\Http\Controllers\Api\CvSetupApiController::class, 'getBanks']);
            Route::get('/', [\App\Http\Controllers\Api\CvSetupApiController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\CvSetupApiController::class, 'store']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\CvSetupApiController::class, 'destroy']);
        });

        // Employee IOUs
        Route::prefix('ious')->group(function () {
            Route::get('/staff', [\App\Http\Controllers\Api\IouApiController::class, 'getStaffList']);
            Route::get('/used-limit', [\App\Http\Controllers\Api\IouApiController::class, 'getUsedLimit']);
            Route::get('/', [\App\Http\Controllers\Api\IouApiController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\IouApiController::class, 'store']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\IouApiController::class, 'destroy']);
            Route::get('/hod-approve/{id}', [\App\Http\Controllers\Api\IouApiController::class, 'hodApprove']);
            Route::get('/hod-reject/{id}', [\App\Http\Controllers\Api\IouApiController::class, 'hodReject']);
            Route::get('/finance-approve/{id}', [\App\Http\Controllers\Api\IouApiController::class, 'financeApprove']);
            Route::get('/finance-reject/{id}', [\App\Http\Controllers\Api\IouApiController::class, 'financeReject']);
            Route::get('/hr-approve/{id}', [\App\Http\Controllers\Api\IouApiController::class, 'hrApprove']);
            Route::get('/hr-reject/{id}', [\App\Http\Controllers\Api\IouApiController::class, 'hrReject']);
        });
    });
});

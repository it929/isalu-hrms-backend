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

    // Roles management
    Route::get('/roles', [\App\Http\Controllers\Api\UserRoleApiController::class, 'index']);
    Route::post('/roles', [\App\Http\Controllers\Api\UserRoleApiController::class, 'store']);
    Route::get('/roles/{id}', [\App\Http\Controllers\Api\UserRoleApiController::class, 'show']);
    Route::post('/roles/update/{id}', [\App\Http\Controllers\Api\UserRoleApiController::class, 'update']);

    // Modules management
    Route::get('/modules', [\App\Http\Controllers\Api\ModuleApiController::class, 'index']);
    Route::post('/modules', [\App\Http\Controllers\Api\ModuleApiController::class, 'store']);
    Route::get('/modules/{id}', [\App\Http\Controllers\Api\ModuleApiController::class, 'show']);
    Route::post('/modules/update/{id}', [\App\Http\Controllers\Api\ModuleApiController::class, 'update']);

    // Submodules management
    Route::get('/submodules', [\App\Http\Controllers\Api\SubModuleApiController::class, 'index']);
    Route::post('/submodules', [\App\Http\Controllers\Api\SubModuleApiController::class, 'store']);
    Route::get('/submodules/{id}', [\App\Http\Controllers\Api\SubModuleApiController::class, 'show']);
    Route::post('/submodules/update/{id}', [\App\Http\Controllers\Api\SubModuleApiController::class, 'update']);
    Route::post('/submodules/delete/{id}', [\App\Http\Controllers\Api\SubModuleApiController::class, 'destroy']);

    // Assign modules to roles
    Route::get('/assign-module/metadata', [\App\Http\Controllers\Api\AssignModuleRoleApiController::class, 'metadata']);
    Route::get('/assign-module/assignments/{roleID}', [\App\Http\Controllers\Api\AssignModuleRoleApiController::class, 'assignments']);
    Route::post('/assign-module/assign', [\App\Http\Controllers\Api\AssignModuleRoleApiController::class, 'assign']);

    // Assign users to roles
    Route::get('/user-assign/metadata', [\App\Http\Controllers\Api\AssignUserRoleApiController::class, 'metadata']);
    Route::get('/user-assign/assignments', [\App\Http\Controllers\Api\AssignUserRoleApiController::class, 'assignments']);
    Route::post('/user-assign/assign', [\App\Http\Controllers\Api\AssignUserRoleApiController::class, 'assign']);

    // HR - Add New Staff
    Route::get('/hr/add-staff/form-data', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getFormData']);
    Route::get('/hr/add-staff/designations/{deptID}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getDesignations']);
    Route::get('/hr/add-staff/units/{deptID}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getUnits']);
    Route::get('/hr/add-staff/lgas/{stateID}', [\App\Http\Controllers\Api\HrStaffApiController::class, 'getLgas']);
    Route::post('/hr/add-staff', [\App\Http\Controllers\Api\HrStaffApiController::class, 'store']);
    Route::get('/hr/add-staff/list', [\App\Http\Controllers\Api\HrStaffApiController::class, 'list']);
    Route::post('/hr/add-staff/import', [\App\Http\Controllers\Api\HrStaffApiController::class, 'importStaff']);
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

        // Active Month Setup
        Route::get('/active-month', [\App\Http\Controllers\Api\ActiveMonthApiController::class, 'index']);
        Route::post('/active-month', [\App\Http\Controllers\Api\ActiveMonthApiController::class, 'store']);

        // Active Month Lock/Unlock
        Route::get('/lock-active-month', [\App\Http\Controllers\Api\ActiveMonthLockApiController::class, 'index']);
        Route::post('/lock-active-month/lock', [\App\Http\Controllers\Api\ActiveMonthLockApiController::class, 'lock']);
        Route::post('/lock-active-month/unlock', [\App\Http\Controllers\Api\ActiveMonthLockApiController::class, 'unlock']);

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

        // Cooperative Loans
        Route::prefix('coop-loans')->group(function () {
            Route::get('/staff',   [\App\Http\Controllers\Api\CoopLoanApiController::class, 'getStaffList']);
            Route::get('/approved/{staffId}', [\App\Http\Controllers\Api\CoopLoanApiController::class, 'getApprovedLoan']);
            Route::get('/',        [\App\Http\Controllers\Api\CoopLoanApiController::class, 'index']);
            Route::post('/',       [\App\Http\Controllers\Api\CoopLoanApiController::class, 'store']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\CoopLoanApiController::class, 'destroy']);
            Route::get('/hod-approve/{id}', [\App\Http\Controllers\Api\CoopLoanApiController::class, 'hodApprove']);
            Route::get('/hod-reject/{id}',  [\App\Http\Controllers\Api\CoopLoanApiController::class, 'hodReject']);
            Route::get('/audit-approve/{id}', [\App\Http\Controllers\Api\CoopLoanApiController::class, 'auditApprove']);
            Route::get('/audit-reject/{id}',  [\App\Http\Controllers\Api\CoopLoanApiController::class, 'auditReject']);
            Route::get('/admin-approve/{id}', [\App\Http\Controllers\Api\CoopLoanApiController::class, 'adminApprove']);
            Route::get('/admin-reject/{id}',  [\App\Http\Controllers\Api\CoopLoanApiController::class, 'adminReject']);
        });

        // Cooperative Loan Deduction Setup
        Route::prefix('coop-loan-deduction-setups')->group(function () {
            Route::get('/',        [\App\Http\Controllers\Api\CoopLoanDeductionSetupApiController::class, 'index']);
            Route::post('/',       [\App\Http\Controllers\Api\CoopLoanDeductionSetupApiController::class, 'store']);
            Route::post('/toggle/{id}', [\App\Http\Controllers\Api\CoopLoanDeductionSetupApiController::class, 'toggleStatus']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\CoopLoanDeductionSetupApiController::class, 'destroy']);
        });

        // Loan Deduction Setup
        Route::prefix('loan-deduction-setups')->group(function () {
            Route::get('/approved-amount/{staffId}', [\App\Http\Controllers\Api\LoanDeductionSetupApiController::class, 'getApprovedLoanAmount']);
            Route::get('/',        [\App\Http\Controllers\Api\LoanDeductionSetupApiController::class, 'index']);
            Route::get('/template', [\App\Http\Controllers\Api\LoanDeductionSetupApiController::class, 'downloadTemplate']);
            Route::post('/',       [\App\Http\Controllers\Api\LoanDeductionSetupApiController::class, 'store']);
            Route::post('/toggle/{id}', [\App\Http\Controllers\Api\LoanDeductionSetupApiController::class, 'toggleStatus']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\LoanDeductionSetupApiController::class, 'destroy']);
            Route::post('/import', [\App\Http\Controllers\Api\LoanDeductionSetupApiController::class, 'import']);
        });

        // Cooperative Savings Setup
        Route::prefix('coop-savings-setups')->group(function () {
            Route::get('/',        [\App\Http\Controllers\Api\CoopSavingsSetupApiController::class, 'index']);
            Route::get('/template', [\App\Http\Controllers\Api\CoopSavingsSetupApiController::class, 'downloadTemplate']);
            Route::post('/',       [\App\Http\Controllers\Api\CoopSavingsSetupApiController::class, 'store']);
            Route::post('/toggle/{id}', [\App\Http\Controllers\Api\CoopSavingsSetupApiController::class, 'toggleStatus']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\CoopSavingsSetupApiController::class, 'destroy']);
            Route::post('/import', [\App\Http\Controllers\Api\CoopSavingsSetupApiController::class, 'import']);
        });

        // Coop Savings → Loan Offset
        Route::prefix('coop-savings-loan-offset')->group(function () {
            Route::get('/staff-list',     [\App\Http\Controllers\Api\CoopSavingsLoanOffsetApiController::class, 'staffList']);
            Route::get('/staff-balances', [\App\Http\Controllers\Api\CoopSavingsLoanOffsetApiController::class, 'staffBalances']);
            Route::get('/history',        [\App\Http\Controllers\Api\CoopSavingsLoanOffsetApiController::class, 'history']);
            Route::post('/',              [\App\Http\Controllers\Api\CoopSavingsLoanOffsetApiController::class, 'store']);
        });

        // Medical Loan Deduction Setup
        Route::prefix('medical-loan-deduction-setups')->group(function () {
            Route::get('/',        [\App\Http\Controllers\Api\MedicalLoanDeductionSetupApiController::class, 'index']);
            Route::get('/template', [\App\Http\Controllers\Api\MedicalLoanDeductionSetupApiController::class, 'downloadTemplate']);
            Route::post('/',       [\App\Http\Controllers\Api\MedicalLoanDeductionSetupApiController::class, 'store']);
            Route::post('/toggle/{id}', [\App\Http\Controllers\Api\MedicalLoanDeductionSetupApiController::class, 'toggleStatus']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\MedicalLoanDeductionSetupApiController::class, 'destroy']);
            Route::post('/import', [\App\Http\Controllers\Api\MedicalLoanDeductionSetupApiController::class, 'import']);
        });

        // Surcharge Deduction Setup
        Route::prefix('surcharge-deduction-setups')->group(function () {
            Route::get('/',        [\App\Http\Controllers\Api\SurchargeDeductionSetupApiController::class, 'index']);
            Route::get('/template', [\App\Http\Controllers\Api\SurchargeDeductionSetupApiController::class, 'downloadTemplate']);
            Route::post('/',       [\App\Http\Controllers\Api\SurchargeDeductionSetupApiController::class, 'store']);
            Route::post('/toggle/{id}', [\App\Http\Controllers\Api\SurchargeDeductionSetupApiController::class, 'toggleStatus']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\SurchargeDeductionSetupApiController::class, 'destroy']);
            Route::post('/import', [\App\Http\Controllers\Api\SurchargeDeductionSetupApiController::class, 'import']);
        });

        // Absence Penalty Deduction Setup
        Route::prefix('absence-penalty-deduction-setups')->group(function () {
            Route::get('/',        [\App\Http\Controllers\Api\AbsencePenaltyDeductionSetupApiController::class, 'index']);
            Route::get('/template', [\App\Http\Controllers\Api\AbsencePenaltyDeductionSetupApiController::class, 'downloadTemplate']);
            Route::post('/',       [\App\Http\Controllers\Api\AbsencePenaltyDeductionSetupApiController::class, 'store']);
            Route::post('/toggle/{id}', [\App\Http\Controllers\Api\AbsencePenaltyDeductionSetupApiController::class, 'toggleStatus']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\AbsencePenaltyDeductionSetupApiController::class, 'destroy']);
            Route::post('/import', [\App\Http\Controllers\Api\AbsencePenaltyDeductionSetupApiController::class, 'import']);
        });

        // Other Deduction Setup
        Route::prefix('other-deduction-setups')->group(function () {
            Route::get('/',        [\App\Http\Controllers\Api\OtherDeductionSetupApiController::class, 'index']);
            Route::get('/template', [\App\Http\Controllers\Api\OtherDeductionSetupApiController::class, 'downloadTemplate']);
            Route::post('/',       [\App\Http\Controllers\Api\OtherDeductionSetupApiController::class, 'store']);
            Route::post('/toggle/{id}', [\App\Http\Controllers\Api\OtherDeductionSetupApiController::class, 'toggleStatus']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\OtherDeductionSetupApiController::class, 'destroy']);
            Route::post('/import', [\App\Http\Controllers\Api\OtherDeductionSetupApiController::class, 'import']);
        });

        // Coop Asset Finance Deduction Setup
        Route::prefix('coop-asset-finance-deduction-setups')->group(function () {
            Route::get('/',        [\App\Http\Controllers\Api\CoopAssetFinanceDeductionSetupApiController::class, 'index']);
            Route::get('/template', [\App\Http\Controllers\Api\CoopAssetFinanceDeductionSetupApiController::class, 'downloadTemplate']);
            Route::post('/',       [\App\Http\Controllers\Api\CoopAssetFinanceDeductionSetupApiController::class, 'store']);
            Route::post('/toggle/{id}', [\App\Http\Controllers\Api\CoopAssetFinanceDeductionSetupApiController::class, 'toggleStatus']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\CoopAssetFinanceDeductionSetupApiController::class, 'destroy']);
            Route::post('/import', [\App\Http\Controllers\Api\CoopAssetFinanceDeductionSetupApiController::class, 'import']);
        });
        
        // Staff Control Variables
        Route::prefix('staff-control-variables')->group(function () {
            Route::get('/staff', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'getStaffList']);
            Route::get('/variable-types', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'getVariableTypes']);
            Route::get('/descriptions/{particularId}', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'getDescriptions']);
            Route::get('/', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'store']);
            Route::post('/import', [\App\Http\Controllers\Api\StaffControlVariableApiController::class, 'import']);
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

        // Pension Activation
        Route::prefix('pension-activation')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\PensionActivationApiController::class, 'index']);
            Route::post('/toggle', [\App\Http\Controllers\Api\PensionActivationApiController::class, 'togglePension']);
            Route::post('/import', [\App\Http\Controllers\Api\PensionActivationApiController::class, 'importPension']);
        });

        // Retention Activation
        Route::prefix('retention-activation')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\RetentionActivationApiController::class, 'index']);
            Route::post('/toggle', [\App\Http\Controllers\Api\RetentionActivationApiController::class, 'toggleRetention']);
            Route::post('/import', [\App\Http\Controllers\Api\RetentionActivationApiController::class, 'importRetention']);
        });
    });
});

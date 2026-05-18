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
    });
});

<?php

use App\Http\Controllers\PaystackController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Import routes from other files
require __DIR__ . '/funds.php';
require __DIR__ . '/hr.php';
require __DIR__ . '/payroll.php';
require __DIR__ . '/procurement.php';


/*
|--------------------------------------------------------------------------
| Web Routes Main
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    Log::info('Application cache cleared and configurations cached successfully.');
    return redirect()->back()->with('message', 'Cleared Successfully!');
})->name('clear');


// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', 'MainController@userArea');

Route::group(['middleware' => 'guest'], function () {
    Route::view('login', 'auth.login')->name('login');
    Route::post('login', 'Auth\AuthController@login');
    //forget Password
    // Route::get('forget-password', 'PasswordController@userForgetPassword');
    // Route::post('forget-password', 'PasswordController@userResetPassword');
    // Route::get('password-reset/resets/{token}', 'PasswordController@ResetPassword');
    // Route::post('password-reset/resets/{token}', 'PasswordController@ResetPassword');
});

Route::get('logout',                                     'Auth\AuthController@logout');

//Create Technical User
Route::get('/technical/create',                    'MasterRolePermission\CreateTechnicalUserController@create')->name('createTechnicalUser');
Route::Post('/technical/create',                      'MasterRolePermission\CreateTechnicalUserController@store');
//user roles
Route::get('/user-role/create',                       'MasterRolePermission\UserRoleController@create')->name('CreateUserRole');
Route::post('/user-role/add',                         'MasterRolePermission\UserRoleController@addRole');
Route::get('/user-role/viewroles',                    'MasterRolePermission\UserRoleController@displayRoles')->name('AllRole');
Route::get('/user-role/edit/{roleID}',                'MasterRolePermission\UserRoleController@editRole')->name('EditRole');
Route::post('/user-role/update/',                     'MasterRolePermission\UserRoleController@updateRole');
//user modules
Route::get('/module/create',                          'MasterRolePermission\ModuleController@create')->name('CreateModule');
Route::post('/module/add',                            'MasterRolePermission\ModuleController@addModule');
Route::get('/module/viewmodules',                     'MasterRolePermission\ModuleController@displayModules')->name('AllModule');
Route::get('/module/edit/{moduleID}',                 'MasterRolePermission\ModuleController@editModule')->name('EditModule');
Route::post('/module/update',                         'MasterRolePermission\ModuleController@updateModule');
Route::post('/module/modify',                         'MasterRolePermission\ModuleController@edit');

//sub modules
Route::get('/sub-module/create',                      'MasterRolePermission\SubModuleController@create')->name('createSubModule');
Route::post('/sub-module/add',                        'MasterRolePermission\SubModuleController@addSubModule');
Route::get('/sub-module/view-sub-modules',            'MasterRolePermission\SubModuleController@displaySubModules')->name('AllSubModule');
Route::get('/sub-module/edit/{submoduleID}',          'MasterRolePermission\SubModuleController@editSubModule')->name('editSubModule');
Route::post('/sub-module/update',                     'MasterRolePermission\SubModuleController@updateSubModule');
Route::post('/sub-module/delete',                     'MasterRolePermission\SubModuleController@deleteSubModule');

Route::post('/module/setsession',                     'MasterRolePermission\SubModuleController@sessionset');
Route::post('/submodule/modify/',                     'MasterRolePermission\SubModuleController@edit');

//Assign modules
Route::get('/assign-module/create',                   'MasterRolePermission\AssignModuleRoleController@create')->name('AssignModule');
Route::post('/role/setsession',                       'MasterRolePermission\AssignModuleRoleController@sessionset');
Route::post('/assign-module/assign',                  'MasterRolePermission\AssignModuleRoleController@assignSubModule');
Route::get('/assign-module/view-sub-modules',         'MasterRolePermission\AssignModuleRoleController@displaySubModules')->name('ViewAssignSubModule');
Route::get('/assign-module/edit/{submoduleID}',       'MasterRolePermission\AssignModuleRoleController@editSubModule')->name('EditAssignSubModule');
Route::post('/assign-module/update',                  'MasterRolePermission\AssignModuleRoleController@updateSubModule');
//Assign Users
Route::get('/user-assign/create',                     'MasterRolePermission\AssignUserRoleController@create')->name('AssignUser');
Route::get('/user-assign/edit/{id?}',                 'MasterRolePermission\AssignUserRoleController@editUsreAssign')->name('editAssignUser');
Route::post('/user-assign/assign',                    'MasterRolePermission\AssignUserRoleController@assignUser');
Route::post('/user/display',                          'MasterRolePermission\AssignUserRoleController@displayUser');
Route::get('/user/search/{q?}',                       'MasterRolePermission\AssignUserRoleController@autocomplete');

//Assign HOD
// Route::get('/assign-hod',                     'MasterRolePermission\AssignUserRoleController@createHod')->name('AssignUser');
Route::get('/user-assign/edit/{id?}',                 'MasterRolePermission\AssignUserRoleController@editUsreAssign')->name('editAssignUser');
// Route::post('/assign-hod',                    'MasterRolePermission\AssignUserRoleController@assignHod');
Route::post('/user/display',                          'MasterRolePermission\AssignUserRoleController@displayUser');
Route::get('/user/search/{q?}',                       'MasterRolePermission\AssignUserRoleController@autocomplete');


Route::get('/assign-hod',                     'MasterRolePermission\AssignUserRoleController@createHod');
Route::post('/assign-hod',                    'MasterRolePermission\AssignUserRoleController@assignHod');

Route::get( '/staff-by-department/{dept}', 'MasterRolePermission\AssignUserRoleController@staffByDepartment')->name('staff.by.department');


//User Management
Route::get('user/register', 'Auth\UserController@registerUser');
Route::post('user/store',   'Auth\UserController@storeUser');
Route::any('users/modify',   'Auth\UserController@modifyUser');

Route::get('/verify-account', [PaystackController::class, 'verifyAccount']);

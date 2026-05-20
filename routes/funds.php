<?php

use App\Http\Controllers\funds\ActiveYearController;
use App\Http\Controllers\funds\AdvanceRetirementController;
use App\Http\Controllers\funds\AllocationApprovalController;
use App\Http\Controllers\funds\AllocationController;
use App\Http\Controllers\funds\BaseParentController;
use App\Http\Controllers\funds\CashBookController;
use App\Http\Controllers\funds\MonthlyAllocationController;
use App\Http\Controllers\funds\ReportsController;
use App\Http\Controllers\funds\CommentDisplayController;
use App\Http\Controllers\funds\ContractorController;
use App\Http\Controllers\funds\ContractorRecordController;
use App\Http\Controllers\funds\CPOController;
use App\Http\Controllers\funds\CPOExportMandateController;
use App\Http\Controllers\funds\CreateContractVoucherController;
use App\Http\Controllers\funds\DayBookLedgerController;
use App\Http\Controllers\funds\EconomicCodeController;
use App\Http\Controllers\funds\EconomicHeadController;
use App\Http\Controllers\funds\FundsProjectController;
use App\Http\Controllers\funds\LiabilityController;
use App\Http\Controllers\funds\MandateApprovalProcessController;
use App\Http\Controllers\funds\MergerController;
use App\Http\Controllers\funds\ProcurementController;
use App\Http\Controllers\funds\ReconciliationController;
use App\Http\Controllers\funds\ReconciliationNjcController;
use App\Http\Controllers\funds\StaffDesignationController;
use App\Http\Controllers\funds\TaxMatter;
use App\Http\Controllers\funds\TaxMatterController;
use App\Http\Controllers\funds\TransactionsController;
use App\Http\Controllers\funds\UpdatePaymentController;
use App\Http\Controllers\funds\VatwhtpayeeController;
use App\Http\Controllers\funds\VoucherDisplayController;
use App\Http\Controllers\funds\BasicParameterController;
use App\Http\Controllers\funds\StaffInformationSetUpController;
use Illuminate\Support\Facades\Route;
use App\Exports\ConsolidatedCpoMandateBatchExport;
use App\Exports\SingleCpoMandateBatchExport;
use App\Exports\StyledBatchCapitalExport;
use App\Exports\StyledBatchExport;
use App\Http\Controllers\funds\SalaryPersonnelController;
use App\Http\Controllers\funds\StaffOvertimeSetupController;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes for Funds
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Routes to create contractor
// Route::group(['middleware' => ['auth', 'force.password.change', 'permission']], function () {
Route::group(['middleware' => ['auth', 'force.password.change']], function () {

    // 	Route::group(['middleware' => ['role:admin|tax staff|cpo staff|audit staff|super admin|salary collator|nhf staff']], function() {
    // 		Route::get('/division/changeDivision',				 'DivisionController@changeDivisionCreate');
    // 		Route::post('/division/changeDivisionStore',		 'DivisionController@changeDivisionStore');
    //   	});

    Route::get('user/register', '\App\Http\Controllers\Auth\UserController@registerUser');
    Route::post('user/store', '\App\Http\Controllers\Auth\UserController@storeUser');

    Route::get('logout', '\App\Http\Controllers\Auth\AuthController@logout');

    //edit user account
    Route::get('/user/editAccount', '\App\Http\Controllers\Auth\UserController@editAccount');
    Route::post('user/editAccount', '\App\Http\Controllers\Auth\UserController@editAccountStore');
    //CREATE NEW STAFF / BIO-DATA
    Route::get('/staff/create', 'CreateStaffController@create');
    Route::post('/staff/store', 'CreateStaffController@store');
    Route::post('json/staff/search', 'CreateStaffController@findStaff');                    //by json
    Route::get('/profile/details/report/{fileNo?}', 'CreateStaffController@report');        //Report
    Route::get('/profile/account/report/{fileNo?}', 'CreateStaffController@accountReport'); //Report
    //search for staff record
    Route::get('/searchUser/create', 'SearchUserController@create');
    Route::post('/searchUser/create', 'SearchUserController@retrieve');
    Route::get('/searchUser/{q?}', 'SearchUserController@autocomplete');

    //staff claim
    //routes for normal staff
    Route::get('/staff-claim', 'StaffClaimController@index')->name('staffindex');
    Route::get('/staff-claim-push-to-hod', 'StaffClaimController@index')->name('pushClaimToHod');
    Route::post('/staff-claim-push-to-hod', 'StaffClaimController@pushClaimHod')->name('pushClaimToHod');
    Route::post('/staff-claim-consigency', 'StaffClaimController@AddmoreRemark');
    Route::post('/staff-claim', 'StaffClaimController@sendClaim')->name('claimSend');
    Route::post('/remove-staff-from-list', 'StaffClaimController@removeStaffFromList');
    Route::post('/add-more-staff-to-claim', 'StaffClaimController@addMoreStaffToList')->name('addMoreStaffToClaim');
    Route::get('/add-more-staff-to-claim', 'StaffClaimController@index'); //fallBack
    Route::post('/update-staff-claim-amount', 'StaffClaimController@updateStaffClaimAmount')->name('updateStaffAmount');
    Route::get('/update-staff-claim-amount', 'StaffClaimController@index'); //fallBack
    Route::post('/update-claim-details', 'StaffClaimController@updateClaimDetails')->name('updateStaffClaimDetails');
    Route::get('/update-claim-details', 'StaffClaimController@index');                        //fallBack
    Route::post('/claim-comment-for-rejection', 'StaffClaimController@claimRejection');       //AJAX Call for claim rejection
    Route::post('/remove-staff-claim', 'StaffClaimController@deleteClaim');                   //AJAX Call for claim deleteion
    Route::post('/approve-staff-claim', 'StaffClaimController@approveStaffClaim');            //AJAX Call for staff claim approval
    Route::post('/add-more-comment-claim', 'StaffClaimController@addMoreCommentClaim');       //AJAX Call for More comment
    Route::post('/claim-comment-for-rejection-by-es', 'StaffClaimController@claimRejection'); //AJAX Call Deny by ES
    Route::post('/approve-staff-claim-by-es', 'StaffClaimController@approveStaffClaim');      //AJAX Call by Es approval
    Route::post('/upload-staff-claim-files', 'StaffClaimController@uploadAttachStaffClaimFile')->name('uploadStaffFileClaim');
    Route::get('/upload-staff-claim-files', 'StaffClaimController@index')->name('uploadStaffFileClaim');

    Route::get('/add-section', 'SectionController@index');
    Route::post('/add-section', 'SectionController@addSection')->name('add-section');
    Route::get('/delete-section/{id}', 'SectionController@deleteSection')->name('delete-section');
    Route::post('/update-section', 'SectionController@updateSection')->name('update-section');

    //routes for department head
    Route::any('/claim-review', 'StaffClaimController@claimReview')->name('claimReview');
    //Route::get('/dep-claim/{id}/{num}'       ,'StaffClaimController@departmentClaim')->name('depClaim');
    Route::post('/edit-amount', 'StaffClaimController@editAmount')->name('editAmount');
    Route::post('/select-status', 'StaffClaimController@selectStatus')->name('selectStatus');

    //routes for the Executive Secretary
    Route::any('/review-es', 'StaffClaimController@reviewES')->name('reviewES');
    Route::post('/select-department', 'StaffClaimController@selectDep')->name('selectDep');
    Route::get('/es-claim/{id}/{num}', 'StaffClaimController@esClaim')->name('esClaim');

    //CREATE NEW STAFF TRANSFER
    Route::get('/staff/transfer', 'StaffTransferController@create');
    Route::post('/staff/transfer', 'StaffTransferController@create');

    //R. Variation: Records and Variation

    Route::post('/manpower/search/central', 'ManPowerController@searchCentral');
    Route::get('/earning_deduction/approval', 'EarningDeductionController@index');
    Route::post('/earning_deduction/approval', 'EarningDeductionController@index');
    Route::get('/earn_deduct_staffcv/{id?}', 'EarningDeductionController@edscv');
    Route::post('/earn_deduct_staffcv/{id?}', 'EarningDeductionController@edscv');
    Route::get('/generalrecord_earn_deduct/{id?}', 'EarningDeductionController@gred');
    Route::post('/generalrecord_earn_deduct/{id?}', 'EarningDeductionController@gred');
    Route::get('/generalrecord_earn_deduct2/{id?}', 'EarningDeductionController@gred2');
    Route::post('/generalrecord_earn_deduct2/{id?}', 'EarningDeductionController@gred2');

    Route::get('/rec/dec/{id?}', 'EarningDeductionController@ogred');
    Route::post('/rec/dec/{id?}', 'EarningDeductionController@ogred');
    Route::get('/rec/dec2/{id?}', 'EarningDeductionController@ogred2');
    Route::post('/rec/dec2/{id?}', 'EarningDeductionController@ogred2');

    Route::get('/tourslash/leave/{id?}', 'TourSlashLeaveController@index');
    Route::post('/tourslash/leave/{id?}', 'TourSlashLeaveController@index');

    Route::get('/tours/z/{s?}/{e?}', 'TourSlashLeaveController@ajax');
    Route::get('/tours/d/{s?}', 'TourSlashLeaveController@delete');




    Route::any('/create/personnel-voucher/{uniqueCode?}', [SalaryPersonnelController::class, 'createPersonnelVoucher']);
    Route::any('/personnel-unprocessed', [SalaryPersonnelController::class, 'unprocessedPersonnelVoucher']);
    Route::get('/single-personnel-voucher/{id}', [SalaryPersonnelController::class, 'singlePersonnelVoucher'])->name('viewSalaryPersonnelVoucher');
    Route::any('/create/paying-in-form', [SalaryPersonnelController::class,'payingInForm']);
    Route::get('/paying-in-voucher/{id}', [SalaryPersonnelController::class,'payingInVoucher'])->name('payingInVoucher');
    Route::any('/track-personnel-voucher', [SalaryPersonnelController::class,'trackVoucher']);
    Route::get('/completed-processed', [SalaryPersonnelController::class,'completeAndProcessedVoucher']);

    Route::any('/salary-voucher/approve-awaiting', [SalaryPersonnelController::class,'salaryApproveAwaiting']);
    Route::any('/create-personnel/contract', [SalaryPersonnelController::class, 'PrecreateSalaryContractVoucher']);
    Route::any('/clear-personnel/contract', [SalaryPersonnelController::class, 'clearPersonnelVoucher']);

    Route::any('/return/contract',                                    'CreateContractVoucherController@RejectTask_Othercharges');

    // Route::get('/voucher/continue/{id?}/{ctype?}',                  'CreateContractVoucherController@continu');
    // Route::post('/voucher/continue/{id?}/{ctype?}',                 'CreateContractVoucherController@continu');
    Route::any('/create/staff-voucher/{id}',                           'CreateContractVoucherController@statffVoucher');
    //special voucher generation
    Route::any('/create/staff-voucher-special/{id}',                'CreateContractVoucherController@statffVoucherSpecial');
    Route::any('/voucher/continue-special/{id?}',                   'CreateContractVoucherController@continuSpecial');

    Route::any('/create/driver-tour/{vid?}',                    'CreateContractVoucherController@DriverTour');

    Route::any('/new-bank', 'BasicParameterController@BankController');
    Route::any('/staff-account-update', 'BasicParameterController@StaffAccountUpdateController');

    Route::get('/tech/documentation', 'TechnicalDocumentationController@index')->name('techDocument');
    Route::post('/tech/documentation', 'TechnicalDocumentationController@index');
    Route::get('/tech/modify/{id?}', 'TechnicalDocumentationController@modify')->name('addModification');
    Route::post('/tech/modify/{id?}', 'TechnicalDocumentationController@modify');

    Route::get('/create/cate/{id?}', 'TechnicalDocumentationController@createcat')->name('createCategory');
    Route::post('/create/cate/{id?}', 'TechnicalDocumentationController@createcat');

    Route::get('/add/module/{id?}', 'TechnicalDocumentationController@addmodule')->name('addModule');
    Route::post('/add/module/{id?}', 'TechnicalDocumentationController@addmodule');

    Route::get('/tech/viewall/{id?}/{c?}/{m?}', 'TechnicalDocumentationController@viewall')->name('viewAll');
    Route::post('/add/module/{id?}/{c?}/{m?}}', 'TechnicalDocumentationController@viewall');


    //particulars of wife - date of birth
    Route::get('/particular/wife/create/{fileNo?}', 'DateOfBirthWifeController@create');
    Route::get('/remove/particular/{fileNo?}', 'DateOfBirthWifeController@delete');
    Route::post('/process/particular', 'DateOfBirthWifeController@store');
    Route::get('/particular/edit/{ID?}', 'DateOfBirthWifeController@view');
    Route::get('/profile/particular-wife/report/{fileNo?}', 'DateOfBirthWifeController@report');
    //particulars of children





    //Languages Routes
    // Route::get('/update/languages/{fileNo?}', 'LanguagesController@index');

    // Route::post('/update/languages/', 'LanguagesController@update');

    Route::get('/profile/languages/report/{fileNo?}', 'LanguagesController@report');
    // Details of service in the force
    Route::get('/update/detail-service/{fileNo?}', 'DetailOfServiceController@index');
    Route::post('/update/detailofservice/', 'DetailOfServiceController@update');
    Route::get('/remove/detailofservice/{fileNo?}/{dosid?}', 'DetailOfServiceController@destroy');
    Route::get('/update/detailofservice/view/{dosid?}', 'DetailOfServiceController@view');
    Route::get('/profile/DetailsServiceForce/report/{fileNo?}', 'DetailOfServiceController@report');

    //Education
    Route::get('/education/create/{fileNo?}', 'EducationController@index');
    Route::get('/education/remove/{id?}', 'EducationController@delete');
    Route::post('/education/create', 'EducationController@store');
    Route::get('/education/edit/{id?}', 'EducationController@view');
    Route::post('/education/edit', 'EducationController@update');
    Route::get('/profile/education/report/{fileNo?}', 'EducationController@report');
    //Record of Censures and Commendations
    Route::get('/commendations/create/{fileNo?}', 'CensureCommendationController@index');
    Route::get('/commendations/remove/{id?}', 'CensureCommendationController@delete');
    Route::post('/commendations/create', 'CensureCommendationController@store');
    Route::get('/commendations/edit/{id?}', 'CensureCommendationController@view');
    Route::post('/commendations/edit', 'CensureCommendationController@update');
    Route::get('/profile/censures-commendations/report/{fileNo?}', 'CensureCommendationController@report');




    // OPEN REGISTRY
    //create new staff
    Route::get('/new-staff/create', 'OpenRegistryController@NEW_STAFF');
    Route::post('/new-staff/store', 'OpenRegistryController@store_NEW_STAFF');
    Route::post('/staff-report/view', 'OpenRegistryController@filter_staff');
    Route::get('/staff-report/view', 'OpenRegistryController@listAll');
    Route::get('/staff/search/json/{q?}', 'OpenRegistryController@autocomplete_STAFF');

    Route::post('/new-staff/getcourt', 'OpenRegistryController@getCourt');
    Route::post('/new-staff/getdepartments', 'OpenRegistryController@getDepartments');
    Route::post('/new-staff/getdesignations', 'OpenRegistryController@getDesignations');

    Route::get('/openregistry/create/', 'OpenRegistryController@indexview');
    Route::post('/data/searchUser/showAll', 'OpenRegistryController@showAll');
    Route::get('/data/searchUser/{q?}', 'OpenRegistryController@autocomplete');
    Route::post('/data/store/', 'OpenRegistryController@store');
    Route::post('/data/personalFileData', 'OpenRegistryController@personalFileData');
    Route::get('/remove/openregistry/{userId?}', 'OpenRegistryController@destroy');
    Route::post('/create/openregistry', 'OpenRegistryController@create');
    Route::get('/openregistry/list', 'OpenRegistryController@index');
    Route::get('/openregistry/edit/{pfrID?}', 'OpenRegistryController@edit');
    Route::get('/openregistry/editout/{pfrID?}', 'OpenRegistryController@edit');
    Route::post('/openregistry/update/', 'OpenRegistryController@update');

    //START EDITING STAFF PROFILE LINKS//
    //Bio-Data

    ////ENDS HERE///

    //handling admin priveledges

    //File upload routes

    Route::get('/documents/upload', 'Uploader@documentsUpload')->name('uploader');
    Route::post('/documents/upload', 'Uploader@uploadDocuments')->name('uploader_post');
    Route::get('/documents/delete/{id}', 'Uploader@deleteDocument');
    Route::get('/documents/upload/admin', 'Uploader@adminUpload');
    Route::post('/documents/upload/admin', 'Uploader@adminUploadDocument');
    Route::get('/documents/view/user', 'Uploader@selectUserView');
    Route::post('/documents/view/user', 'Uploader@findUserDocumentsById');
    Route::get('/documents/fetch/{id}', 'Uploader@getDocsByStaffId');

    /// excel routes njc
    Route::post('/profile/save', 'ExcelController@store');
    Route::get('/profile/upload', 'ExcelController@create');

    Route::post('/profile/sendmail', 'ExcelController@sendMail');
    Route::get('/profile/mail', 'ExcelController@mail');

    Route::post('/login/details/export', 'ExcelController@ExportLoginDetails');
    Route::get('/login/details/select', 'ExcelController@export');

    //password Reset

    // offer of appointment Listing
    Route::get('/password/resets', 'PasswordAuthController@userForgetPassword');
    Route::post('/password/resets', 'PasswordAuthController@userResetPassword');
    Route::get('/promotion/update', 'PromotionController@GetPromotion');
    Route::post('/promotion/update', 'PromotionController@PostPromotion');
    Route::get('/self-promotion/update', 'PromotionSelfController@GetPromotion');
    Route::post('/self-promotion/update', 'PromotionSelfController@PostPromotion');

    //user roles
    Route::get('/user-role/create', '\\App\\Http\\Controllers\\role_setup\UserRoleController@create');
    Route::post('/user-role/add', '\\App\\Http\\Controllers\\role_setup\UserRoleController@addRole');
    Route::get('/user-role/viewroles', '\\App\\Http\\Controllers\\role_setup\UserRoleController@displayRoles');
    Route::get('/user-role/edit/{roleID}', '\\App\\Http\\Controllers\\role_setup\UserRoleController@editRole');
    Route::post('/user-role/update/', '\\App\\Http\\Controllers\\role_setup\UserRoleController@updateRole');
    //user modules
    Route::get('/module/create', '\\App\\Http\\Controllers\\role_setup\ModuleController@create');
    Route::post('/module/add', '\\App\\Http\\Controllers\\role_setup\ModuleController@addModule');
    Route::get('/module/viewmodules', '\\App\\Http\\Controllers\\role_setup\ModuleController@displayModules');
    Route::get('/module/edit/{moduleID}', '\\App\\Http\\Controllers\\role_setup\ModuleController@editModule');
    Route::post('/module/update', '\\App\\Http\\Controllers\\role_setup\ModuleController@updateModule');
    //sub modules
    Route::get('/sub-module/create', '\\App\\Http\\Controllers\\role_setup\SubModuleController@create');
    Route::post('/sub-module/add', '\\App\\Http\\Controllers\\role_setup\SubModuleController@addSubModule');
    Route::get('/sub-module/view-sub-modules', '\\App\\Http\\Controllers\\role_setup\SubModuleController@displaySubModules');
    Route::get('/sub-module/edit/{submoduleID}', '\\App\\Http\\Controllers\\role_setup\SubModuleController@editSubModule');
    Route::post('/sub-module/update', '\\App\\Http\\Controllers\\role_setup\SubModuleController@updateSubModule');

    Route::post('/module/setsession', 'SubModuleController@sessionset');
    Route::post('/submodule/modify/', 'SubModuleController@edit');

    //Assign modules
    Route::get('/assign-module/create', '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@create');
    Route::post('/role/setsession', '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@sessionset');
    Route::post('/assign-module/assign', '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@assignSubModule');
    Route::get('/assign-module/view-sub-modules', '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@displaySubModules');
    Route::get('/assign-module/edit/{submoduleID}', '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@editSubModule');
    Route::post('/assign-module/update', '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@updateSubModule');
    //Assign Users
    Route::get('/user-assign/create', '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@create');
    //Route::post('/role/setsession',                     '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@sessionset');
    Route::post('/user-assign/assign', '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@assignUser');
    Route::post('/user/display', '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@displayUser');
    Route::get('/user/search/{q?}', '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@autocomplete');

    //Basic parameter
    
    // Route::post('/basic/division', 'BasicParameterController@Divisionsetup');
    // Route::get('/basic/division', 'BasicParameterController@Divisionsetup');
    // Route::post('/basic/designation', 'BasicParameterController@ControlVariable');
    // Route::get('/basic/designation', 'BasicParameterController@ControlVariable');
    // Route::post('basic/designation/edit', 'BasicParameterController@updateDesignation');
    // Route::post('basic/designation/delete', 'BasicParameterController@deletePost');
    // Route::post('basic/rank-designation', [BasicParameterController::class, 'UpdateRankDesignation']);
    // Route::get('basic/rank-designation', [BasicParameterController::class, 'UpdateRankDesignation']);

    Route::any('setting-adjustment', 'BasicParameterController@Setting');
    //dependant parameter
    Route::post('/staff/dependant', 'DependantController@postDependant');
    Route::get('/staff/dependant', 'DependantController@getDependant');

    //user function
    Route::get('/function/create', '\\App\\Http\\Controllers\\function_setup\FunctionController@create');
    Route::post('/function/add', '\\App\\Http\\Controllers\\function_setup\FunctionController@addFunction');
    Route::get('/function/viewmodules', '\\App\\Http\\Controllers\\function_setup\FunctionController@displayFunction');
    Route::get('/function/edit/{functionID}', '\\App\\Http\\Controllers\\function_setup\FunctionController@editFunction');
    Route::post('/function/update', '\\App\\Http\\Controllers\\function_setup\FunctionController@updateFunction');
    Route::post('/function/modify', '\\App\\Http\\Controllers\\function_setup\FunctionController@edit');

    //sub functions
    Route::get('/sub-function/create', '\\App\\Http\\Controllers\\function_setup\SubFunctionController@create');
    Route::post('/sub-function/add', '\\App\\Http\\Controllers\\function_setup\SubFunctionController@addSubFunction');
    Route::get('/sub-function/view-sub-modules', '\\App\\Http\\Controllers\\function_setup\SubFunctionController@displaySubFunction');
    Route::get('/sub-function/edit/{subfunctionID}', '\\App\\Http\\Controllers\\function_setup\SubFunctionController@editSubFunction');
    Route::post('/sub-function/update', '\\App\\Http\\Controllers\\function_setup\SubFunctionController@updateSubFunction');

    Route::post('/sub-function/modify', '\\App\\Http\\Controllers\\function_setup\SubFunctionController@edit');
    Route::post('/sub-function/setsession', '\\App\\Http\\Controllers\\function_setup\SubFunctionController@sessionset');

    //Assign functions
    Route::get('/assign-function/create', '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@create');
    // Route::post('/role/setsession',                         '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@sessionset');
    Route::post('/assign-function/assign', '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@assignSubFunction');
    Route::get('/assign-function/view-sub-modules', '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@displaySubFunction');
    Route::get('/assign-function/edit/{submoduleID}', '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@editSubModule');
    Route::post('/assign-function/update', '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@updateSubFunction');

    //company information
    Route::get('/company/info', 'BasicParameterController@companyInfo');
    Route::post('/company/info', 'BasicParameterController@companyInfo');

    //Add Court
    Route::get('court/add-court', 'AddCourtController@index');
    Route::post('court/add-court/insert', 'AddCourtController@store');
    Route::post('court/add-court/update', 'AddCourtController@update');
    Route::get('court/add-court/delete{id}', 'AddCourtController@destroy');

    //bank set up
    Route::post('/session/court', 'BankController@sessionset');

   

    //compute All
    Route::post('/court/getActiveMonth', 'ComputeController@getActiveMonth');
    Route::post('/court/getDivisions', 'ComputeController@getDivisions');
    Route::post('/court/getStaff', 'ComputeController@getStaff');
    Route::post('/over-under-pay/compute', 'ComputeProcessorController@payment');

    //Payroll Report

    Route::post('/payrollReport/getBank', 'PayrollReportController@getBank');

    //Add Title
    Route::get('/title', 'AddTitleController@index');
    Route::get('/title/remove/{ID}/{title}', 'AddTitleController@destroy');
    Route::post('/title/update', 'AddTitleController@update');
    Route::post('/title/add', 'AddTitleController@store');

    //Company Profile
    Route::get('/company-profile', 'CompanyProfileController@index');
    Route::post('/company-profile/update', 'CompanyProfileController@update');

    Route::get('/beneficiary/voucher', 'BeneficiaryVoucherController@index');
    Route::post('/beneficiary/voucher', 'BeneficiaryVoucherController@index');

    Route::get('/staff-claim', 'StaffClaimController@index')->name('staffindex');
    Route::post('/staff-claim', 'StaffClaimController@sendClaim')->name('claimSend');
    Route::any('/claim-review', 'StaffClaimController@claimReview')->name('claimReview');
    Route::get('/claim-approve/{id}', 'StaffClaimController@approveClaim')->name('approveClaim');
    Route::get('/claim-deny/{id}', 'StaffClaimController@denyClaim')->name('denyClaim');
    Route::post('/edit-amount', 'StaffClaimController@editAmount')->name('editAmount');


    //mandate Approval
    Route::get('/ca/mandate', [MandateApprovalProcessController::class, 'CAMandate']);
    Route::post('/ca/mandate', [MandateApprovalProcessController::class, 'CAComment']);

    Route::get('/dd/mandate', [MandateApprovalProcessController::class, 'DDMandate']);
    Route::post('/dd/mandate', [MandateApprovalProcessController::class, 'DDComment']);

    Route::get('/df/mandate', [MandateApprovalProcessController::class, 'DFMandate']);
    Route::post('/df/mandate', [MandateApprovalProcessController::class, 'DFComment']);

    Route::get('/es/mandate', [MandateApprovalProcessController::class, 'ESMandate']);
    Route::post('/es/mandate', [MandateApprovalProcessController::class, 'ESComment']);

    Route::post('/rejection/reason', [MandateApprovalProcessController::class, 'rejectionReason']);

    Route::get('/display/comments/{batch}', [MandateApprovalProcessController::class, 'displayComments']);

    Route::get('/view/es-epayment/{id}', [CPOController::class, 'esMandateView']);

    //NJC- creating new contract from procurement
    Route::get('/create-direct-procurement', 'ProcurementContractController@createdirect')->name('loadProcurementdirect');
    Route::post('/create-direct-procurement', 'ProcurementContractController@savedirect')->name('saveProcurementdirect');

    Route::get('/create-procurement', 'ProcurementContractController@create')->name('loadProcurement');
    Route::get('/view-all-contract', 'ProcurementContractController@viewAllContract')->name('startProcess');
    Route::post('/create-procurement', 'ProcurementContractController@save')->name('saveProcurement');
    Route::get('/create-new-contract-from-liability', 'ProcurementContractController@newLiabilityContract')->name('liabilityContract');
    Route::post('/create-from-procurement', 'ProcurementContractController@saveContractLiability')->name('saveOtherContract');
    Route::post('/upload-contract-attach-files', 'ProcurementContractController@attachNewFileForContract')->name('uploadContractFile');
    Route::post('/edit-contract-information-post-json', 'ProcurementContractController@editContract')->name('editContract');
    Route::post('/process-contract-post-json', 'ProcurementContractController@processContract')->name('editContract');
    Route::post('/get-all-economic-code', 'ProcurementContractController@fetchEconomicCode');
    Route::post('/set-session-director-post-json', 'ProcurementContractController@setSessionByDirector');
    Route::post('/remove-contract-attached-file', 'ProcurementContractController@deleteAttachedFile');
    Route::post('/get-all-comment-for-all', 'ProcurementContractController@getAllComment');
    Route::post('/get-liability-contract-details', 'ProcurementContractController@getUnpaidContractDetail');
    Route::post('/get-unpaid-amount-only', 'ProcurementContractController@getUnpaidBalance');
    Route::post('/get-contract-economic-details', 'ProcurementContractController@getPreviousLiabilityEconomicDetails');
    Route::get('/is-user-head-of-expenditure-control', 'ProcurementContractController@isUserHEC');
    Route::post('/get-real-balance-json-economicCode', 'ProcurementContractController@getBalance');
    Route::get('/viewing-all-contracts', 'ProcurementContractController@getAllContractWithDetails');
    //
    //
    Route::get('/account/type', 'AccountController@AccountType');
    Route::post('/account/type', 'AccountController@AccountType');
    Route::get('/account/ledger', 'AccountController@AccounLedger');
    Route::post('/account/ledger', 'AccountController@AccounLedger');

    //staff unit
    Route::get('/staff/unit', 'UnitsController@index');
    Route::post('/staff/unit', 'UnitsController@store');



    /****File Communication***/
    Route::get('/communication/create', 'FileCommunication\CommunicationController@create')->name('creatTask');
    Route::post('/communication/create', 'FileCommunication\CommunicationController@store')->name('storeTask');
    Route::post('/communication/create-comment', 'FileCommunication\CommunicationController@storeComment')->name('storeComment');
    Route::get('/communication/create-comment', 'FileCommunication\CommunicationController@create');
    Route::get('/communication/list-task', 'FileCommunication\CommunicationController@listTask')->name('allTask');
    Route::get('/communication/add-comment', 'FileCommunication\CommunicationController@createComment')->name('createAddComment');
    Route::post('/communication/add-comment', 'FileCommunication\CommunicationController@addComment')->name('processComment');
    Route::get('/communication/archive', 'FileCommunication\CommunicationController@createArchive')->name('createArchive');
    Route::post('/communication/archive', 'FileCommunication\CommunicationController@restoreArchive')->name('restoreArchiveTask');
    Route::get('/communication/remove/{id?}', 'FileCommunication\CommunicationController@deleteTask')->name('removeTask');
    /****File COmmunication***/

    /*******Test Connection****/
    // Route::get('/ test-my-connection-on-this-server',   'FileCommunication\TestConnectionController@createTestConnection')->name('createTestConn');
    // Route::get('/test-connection',                      'FileCommunication\TestConnectionController@createTestConnection')->name('createTestConn');
    // Route::post('/test-connection',                     'FileCommunication\TestConnectionController@postTestConnection')->name('postTestConn');
    // Route::get('/delete-test-connection/{id?}',         'FileCommunication\TestConnectionController@deleteTestFile')->name('deleteTestConn');

    /*********Audit Log *********/
    Route::get('/system-audit-log', 'SystemAuditLog\AuditLogController@createAuditLog')->name('createAudit');
    Route::post('/system-audit-log', 'SystemAuditLog\AuditLogController@searchAuditLog')->name('postAudit');

    /*********Password Reset*********/
    Route::get('/password-reset', 'PasswordReset\PasswordResetController@createReset')->name('createPasswordReset');
    Route::post('/password-reset', 'PasswordReset\PasswordResetController@storeReset')->name('postPasswordReset');
    Route::get('/suspend-user', 'PasswordReset\PasswordResetController@createReset');
    Route::post('/suspend-user', 'PasswordReset\PasswordResetController@storeSuspend')->name('postSuspend');

    /********** Capital Mandate **********/
    Route::get('/view/capital-mandate/{id?}', [CPOController::class, 'capitalMandateTest']);
    Route::post('/cpo/update-purpose', [CPOController::class, 'purposeUpdate']);

    Route::any('user-management', 'BasicParameterController@Usermanagement');
    Route::any('users-management', 'BasicParameterController@Usermanagement');

    //Search Voucher by Date
    Route::get('/voucher/view', 'LiabilityController@searchVoucher');
    Route::post('/voucher/view', 'LiabilityController@searchVoucher_POST');











    // ------------------------- Adams Start ------------------------------------------------------------------------------
    //Economic Head
    Route::get('/economic-head/create', [EconomicHeadController::class, 'index'])->name('funds.economicHead.create');
    Route::post('/economic-head/create', [EconomicHeadController::class, 'store'])->name('economicHead.store');
    // Handle delete (GET with SweetAlert redirect)
    Route::get('/economic-head/delete/{id}', [EconomicHeadController::class, 'destroy'])
        ->name('economicHead.destroy');

    //Economic Code
    Route::get('/economic-code', [EconomicCodeController::class, 'index']);
    Route::post('/economic-code', [EconomicCodeController::class, 'reload']);
    Route::post('/economic-code/save', [EconomicCodeController::class, 'store']);
    Route::get('/economic-code/{ID}', [EconomicCodeController::class, 'destroy']);
    Route::post('/economic-code/update', [EconomicCodeController::class, 'update']);

    //VAT And WHT Payee
    Route::get('/vat-wht-payee', [VatwhtpayeeController::class, 'index'])->name('indexWhtVat');
    Route::post('/vat-wht-payee', [VatwhtpayeeController::class, 'store']);
    Route::get('/vat-wht-payee/delete/{lol}', [VatwhtpayeeController::class, 'destroy']);
    Route::post('/vat-wht-payee/update', [VatwhtpayeeController::class, 'update']);

    //Start payee edit route
    Route::get('/edit-vat-wht-for-payee/{payeeID?}', [VatwhtpayeeController::class, 'editPayee']);
    Route::post('/edit-vat-wht-for-payee/{payeeID?}', [VatwhtpayeeController::class, 'editPayee']);


    Route::get('/allocationtype/set', [FundsProjectController::class, 'index']);
    Route::post('/allocationtype/set', [FundsProjectController::class, 'index']);

    Route::get('/contracttype/set', [FundsProjectController::class, 'showContractType']);
    Route::post('/contracttype/set', [FundsProjectController::class, 'storeContractType'])->name('contractt');



    //special voucher generation
    Route::any('/create/staff-voucher-special/{id}', [CreateContractVoucherController::class, 'statffVoucherSpecial']);
    Route::any('/voucher/continue-special/{id?}', [CreateContractVoucherController::class, 'continuSpecial']);
    Route::any('/create/contract', [CreateContractVoucherController::class, 'PrecreateContractVoucher']);
    Route::any('/create/advances', [CreateContractVoucherController::class, 'PrecreateContractVoucherAdvances']);
    Route::any('/raise/voucher', [CreateContractVoucherController::class, 'createContractVoucher']);

    //staff raise advance voucher
    Route::any('/create/advances-staff', [CreateContractVoucherController::class, 'PrecreateContractVoucherAdvancesStaff']);

    //salary voucher
    Route::any('/create/salary-voucher', [CreateContractVoucherController::class, 'PrecreateSalaryVoucher']);

    Route::any('/return/contract', [CreateContractVoucherController::class, 'RejectTask_Othercharges']);

    Route::get('/voucher/continue/{id?}/{ctype?}', [CreateContractVoucherController::class, 'continu']);
    Route::post('/voucher/continue/{id?}/{ctype?}', [CreateContractVoucherController::class, 'continu']);
    Route::any('/create/staff-voucher/{id}', [CreateContractVoucherController::class, 'statffVoucher']);

    Route::get('/voucher/edit/{id?}', [CreateContractVoucherController::class, 'edit']);
    Route::post('/voucher/edit/{id?}', [CreateContractVoucherController::class, 'edit']);

    Route::post('/session/set', [CreateContractVoucherController::class, 'setSes']);


    Route::any('/create/driver-tour/{vid?}', [CreateContractVoucherController::class, 'DriverTour']);


    Route::any('/procurement/unpresssed-request', [ProcurementController::class, 'UnprocessedRequest']);
    Route::any('/pprocurement/unpresssed-request', [ProcurementController::class, 'UnprocessedRequest']);

    Route::get('/create/procurement', [ProcurementController::class, 'newprocurement']);
    Route::get('/company/get-tin/{id}', [ProcurementController::class, 'gettin'])->name("gettin");
    Route::post('/create/procurement', [ProcurementController::class, 'newprocurement']);




    Route::any('/create/procurement2', [ProcurementController::class, 'newprocurement2']);

    Route::any('/create/liability', [ProcurementController::class, 'LiabilityTaken']);
    Route::any('/create/liability-staff', [ProcurementController::class, 'LiabilityTakenStaff']);

    Route::any('/create/pre-procurement-staff', [ProcurementController::class, 'Pre_procurement_staff']);
    Route::get('/create/procurement-staff', [ProcurementController::class, 'newprocurement_staff']);
    Route::post('/create/procurement-staff', [ProcurementController::class, 'newprocurement_staff']);
    Route::any('/create/procurement-staff-beneficiary/{cid?}', [ProcurementController::class, 'StaffBenficiaryController']);


    Route::get('/download-beneficiary-template', [ProcurementController::class, 'downloadBeneficiaryTemplate'])->name('download.beneficiary.template');
    Route::post('/upload.beneficiaries', [ProcurementController::class, 'uploadBeneficiaries'])->name('upload.beneficiaries');


    Route::any('/procurement/approve', [ProcurementController::class, 'approveprocurement']);
    Route::any('/pprocurement/approve', [ProcurementController::class, 'approveprocurement']);

    Route::any('/pprocurement/approve-awaitingby', [ProcurementController::class, 'approveforAwaitingAction']);
    Route::any('/procurement/approve-awaitingby', [ProcurementController::class, 'approveforAwaitingAction']);
    Route::any('/procurement/approve-archived', [ProcurementController::class, 'ArchiveApproval']);

    Route::get('/claimcontract/report', [ProcurementController::class, 'contractClaimReport']);
    Route::post('/claimcontract/report', [ProcurementController::class, 'contractClaimReport']);

    Route::get('/procurement/ca-approve', [ProcurementController::class, 'approveprocurement']);
    Route::post('/procurement/ca-approve', [ProcurementController::class, 'approveprocurement']);

    Route::get('/pro/file/{name?}', [ProcurementController::class, 'viewfile']);
    Route::any('/treated-approval', [ProcurementController::class, 'ClearedApproval']);


    //voucher Display
    Route::get('/display/voucher-old/{id}/{claim?}', [VoucherDisplayController::class, 'viewVoucherOld']);
    Route::get('/display/voucher/{id}/{claim?}', [VoucherDisplayController::class, 'viewVoucher'])->name('ViewVoucherDetails');
    Route::any('/display/comment/{id}', [CommentDisplayController::class, 'viewComment']);
    Route::any('display/claim-details/{id}', [CommentDisplayController::class, 'viewClaimComment']);
    Route::any('display/claim-adjustment/{id}', [CommentDisplayController::class, 'ClaimAdjustment']);
    Route::get('/test/voucher/{id}/{claim?}', [VoucherDisplayController::class, 'testVoucher']);
    Route::get('/driver/voucher', [VoucherDisplayController::class, 'driversVoucher']);

    //Vrex Number Update
    Route::post('/update/vrefNo', [VoucherDisplayController::class, 'updateVref']);
    Route::post('/update/payeeAddress', [VoucherDisplayController::class, 'updatePayeeAddress']);

    //Routes to create contractor
    Route::get('/contractor/create', [ContractorController::class, 'index']);
    Route::post('/contractor/create', [ContractorController::class, 'index']);



    //Vote transfer module
    Route::get('/payroll-merger',                [MergerController::class, 'index']);
    Route::post('/payroll-merger',               [MergerController::class, 'Merge']);



    //Staff information
    // Route::get('update-staff-information',  'StaffInformationSetUpController@home')->name('staffInfo');
    // Route::post('update-staff-information',  'StaffInformationSetUpController@store')->name('processStaffInfo');
    // Route::post('update-staff-details',  'StaffInformationSetUpController@update')->name('processStaffInfoUpdate');
    // Route::get('update-staff-details',  'StaffInformationSetUpController@home');

    Route::get('update-staff-information',  [StaffInformationSetUpController::class, 'home'])->name('staffInfo');
    Route::post('update-staff-information',  [StaffInformationSetUpController::class, 'store'])->name('processStaffInfo');
    Route::post('update-staff-details',  [StaffInformationSetUpController::class, 'update'])->name('processStaffInfoUpdate');
    Route::get('update-staff-details',  [StaffInformationSetUpController::class, 'home']);


    Route::get('update-account-number',  [StaffInformationSetUpController::class, 'accountNumberIndex'])->name('update-account-number');
    Route::post('update-account-number',  [StaffInformationSetUpController::class, 'updateStaffAccountDetails'])->name('update-staff-account-details');
    Route::get('/search-staff', [StaffInformationSetUpController::class, 'searchStaff'])->name('search.staff');
    Route::get('/claim-bank-audit-logs', [StaffInformationSetUpController::class, 'claimBankAuditLogs'])->name('claim-bank-audit-logs');



    // ------------------------- Adams Stop ------------------------------------------------------------------------------


    //------------------------Start Pitoff Routes---------------------------------------

    //Active year
    Route::get('/active-year',                                [ActiveYearController::class, 'index']);
    Route::post('/active-year',                                [ActiveYearController::class, 'store']);
    Route::get('/active-year/delete/{ID}',                     [ActiveYearController::class, 'destroy']);

    //Routes to create allocation
    Route::get('/allocation', [AllocationController::class, 'index']);
    Route::post('/allocation', [AllocationController::class, 'index']);

    Route::get('/allocation/approval', [AllocationApprovalController::class, 'index']);
    Route::post('/allocation/approval', [AllocationApprovalController::class, 'index']);

    Route::get('/allocation/monthly', [MonthlyAllocationController::class, 'index']);
    Route::post('/allocation/monthly', [MonthlyAllocationController::class, 'index']);

    Route::get('/allocation/totalmonthly', [ReportsController::class, 'TotalMonthlyAllocation']);
    Route::post('/allocation/totalmonthly', [ReportsController::class, 'TotalMonthlyAllocation']);

    //staff designation
    Route::get('staff/designation', [StaffDesignationController::class, 'displayForm']);
    Route::post('user/assign-designation', [StaffDesignationController::class, 'assignDesignation']);
    Route::get('user/delete/{id?}', [StaffDesignationController::class, 'deleteDesignation']);

    Route::any('/voucher/final-clearance',                   [LiabilityController::class, 'FinalApproval']);

    Route::get('/voucher/liability',                        [LiabilityController::class, 'MyAssignedLiability']);
    Route::post('/voucher/liability',                       [LiabilityController::class, 'MyAssignedLiability']);
    Route::any('/voucher/liability-2',                       [LiabilityController::class, 'LiabilityFinalClearance']);

    Route::any('/past-period-for-capital', [LiabilityController::class, 'ctTypeActivePeriod']);
    Route::any('/pre-liabilty', [LiabilityController::class, 'PreLiability']);
    Route::any('/pre-liabilty2', [LiabilityController::class, 'PreLiability2']);
    Route::any('/pre-liabilty-push-to-checking', [LiabilityController::class, 'PreLiabilityForwardToChecking']);

    Route::any('/cpo/assign-task', 'CPOProcessMandateController@AssignTaskToCPOStaff');

    Route::any('/find-liabilty', [LiabilityController::class, 'FindLiability']);
    Route::any('/switch-vote-log', [LiabilityController::class, 'VoteSwitch']);
    Route::any('/voucher/editable/list', [LiabilityController::class, 'editableVoucher']);
    Route::post('/voucher/oc/send-back', [LiabilityController::class, 'processRejectedVoucher']);

    Route::any('/voucher/all', [LiabilityController::class, 'AllVoucher']);
    Route::any('/audited-voucher', [LiabilityController::class, 'AuditedVoucher']);
    Route::any('/my-audited-voucher', [LiabilityController::class, 'MyAuditedVoucher']);
    Route::any('/voucher/recall', [LiabilityController::class, 'ReversableVoucher']);
    Route::any('/voucher/all-archive', [LiabilityController::class, 'AllArchiveVoucher']);
    Route::get('/voucher/check', [LiabilityController::class, 'check']);
    Route::post('/voucher/check', [LiabilityController::class, 'check']);
    Route::get('/voucher/audit', [LiabilityController::class, 'Auditcheck']);
    Route::post('/voucher/audit', [LiabilityController::class, 'Auditcheck']);
    //Lock/Unlock
    Route::post('/voucher/lock', [LiabilityController::class, 'lockUnlockVoucher']);

    Route::any('/pre-check', [LiabilityController::class, 'PreChecking']);
    Route::any('/pre-audit', [LiabilityController::class, 'PreAudit']);
    Route::get('/checkby/voucher', [LiabilityController::class, 'checkbypage']);
    Route::post('/checkby/voucher', [LiabilityController::class, 'checkbypage']);

    //checked vouchers
    Route::any('/checked-voucher', [LiabilityController::class, 'CheckedVoucher']);

    Route::any('/advance-to-checking', [LiabilityController::class, 'AdvanceVoucherLiabilityTaken']);
    Route::any('/checked-advance-vouchers', [LiabilityController::class, 'CheckedAdvanceVoucher']);

    Route::any('/salary-clearance/voucher', [LiabilityController::class, 'Salaryclearance']);

    Route::get('/occheckby/voucher', [LiabilityController::class, 'OCclearance']);
    Route::post('/occheckby/voucher', [LiabilityController::class, 'OCclearance']);
    Route::any('/verify/voucher', [LiabilityController::class, 'Pre_Vericfication']);
    Route::any('/advance-clearance', [LiabilityController::class, 'OCclearanceAdvances']);
    Route::any('/queried/voucher', [LiabilityController::class, 'DocClearance']);
    Route::any('/voucher/all-advances', [LiabilityController::class, 'AdvanceVoucher']);
    Route::any('/voucher/all-retired-advances', [LiabilityController::class, 'AdvanceRetiredVoucher']);

    //Update Payment
    Route::get('/update-payment-transaction', [UpdatePaymentController::class, 'createPaymentKickOff'])->name('createUpdatePayment');
    Route::post('/relaod-payment-transaction', [UpdatePaymentController::class, 'createPaymentKickOff']);
    Route::post('/update-payment-transaction', [UpdatePaymentController::class, 'SavePaymentKickOff'])->name('saveUpdatePayment');
    Route::get('/create-edit-record/{ID}', [UpdatePaymentController::class, 'edit'])->name('editRecord');
    Route::get('/cancel-edit-record', [UpdatePaymentController::class, 'cancelEdit'])->name('cancelUpdate');
    Route::get('/remove-record/{ID}', [UpdatePaymentController::class, 'removeRecord'])->name('removeRecord');
    // end Update Payment

    Route::get('/getEconomicCodeJson/{contractTypeID}/{allocationTypeID}', [BaseParentController::class, 'getEconomicCode']);

    //NJC Reconciliation Report
    Route::get('/refunds-entry-for-treasury', [ReconciliationNjcController::class, 'index'])->name('createRefunds');
    Route::post('/refunds-entry-for-treasury', [ReconciliationNjcController::class, 'postRefunds'])->name('PostCreateRefunds');
    Route::get('/create-treasury-report', [ReconciliationNjcController::class, 'createTreasuryReport'])->name('treasuryReport');
    Route::post('/create-treasury-report', [ReconciliationNjcController::class, 'postReport'])->name('postTreasuryReport');
    Route::get('/treasury-cash-book-report', [ReconciliationNjcController::class, 'viewReport'])->name('viewTreasuryReport');
    Route::post('/get-economic-code-for-refound', [ReconciliationNjcController::class, 'fetchEconomicCode']);

    //advance retirement
    Route::any('/advance/retirement/{id}',                              [AdvanceRetirementController::class, 'viewComment']);
    Route::any('/unsolicited/retirement',                                  [AdvanceRetirementController::class, 'Unsolicited']);

    /********** Treasure cash book **********/
    Route::get('treasure/cashbook', [CashBookController::class, 'createCashBook'])->name('cashbook');
    Route::post('/cashbook', [CashBookController::class, 'generateReortCashBook'])->name('processCashbook');
    Route::get('/cashbook/report/{at?}/{y?}', [CashBookController::class, 'viewCashBookReport'])->name('viewReport');
    Route::get('/cashbook/refund/{ec?}/{y?}/{m?}', [CashBookController::class, 'getRefundDetails'])->name('viewRefundDetails');
    Route::get('/cashbook/payment-details/{ec?}/{eh?}/{y?}/{m?}', [CashBookController::class, 'getCashBookPaymentDetails'])->name('viewPaymentDetailsFromCashbook');

    //Contractor Record
    Route::get('/contractor-record', [ContractorRecordController::class, 'index']);
    Route::post('/contractor-record', [ContractorRecordController::class, 'show']);
    Route::get('/contractor-record/view/{ContID}', [ContractorRecordController::class, 'view']);

    Route::get('/report/voults', [ReportsController::class, 'VoultBalanceReport']);
    Route::post('/report/voults', [ReportsController::class, 'VoultBalanceReport']);
    //ote Transaction
    Route::any('/report/vote-trans', [ReportsController::class, 'VoultBalanceReport']);

    Route::any('vote-trans', [ReportsController::class, 'VoultTransReport2']);
    //Monthly vault ballance
    Route::get('/report/voults/monthly', [ReportsController::class, 'MonthlyVoultBalanceReport']);
    Route::post('/report/voults/monthly', [ReportsController::class, 'MonthlyVoultBalanceReport']);

    Route::any('/report/vote/expenditure', [ReportsController::class, 'VoultExpendictureReport']);
    Route::any('/report/range/expenditure', [ReportsController::class, 'RangeExpenditureReport']);

    Route::post('/votebook/cancel-toggle/{id}', [ReportsController::class, 'toggleCancel'])
        ->name('votebook.cancel.toggle');


    Route::any('/report/range/expenditure/balance', [ReportsController::class, 'RangeExpenditureReportBalance']);

    //Reports
    Route::get('/report/transactions', [TransactionsController::class, 'index']);
    Route::post('/report/transactions', [TransactionsController::class, 'index']);

    //day ledger book
    Route::get('/daybook/view-book', [DayBookLedgerController::class, 'dayBook']);
    Route::post('/daybook/view-book', [DayBookLedgerController::class, 'postDayBook']);
    Route::get('/ledger/view', [DayBookLedgerController::class, 'ledger']);
    Route::post('/ledger/view', [DayBookLedgerController::class, 'postLedger']);

    //Pre-CPO - Voucher assignment
    Route::any('/assign-voucher-cpo', [CPOController::class, 'unprocessedCPOVoucher']);
    //cpo
    Route::get('/cpo/report', [CPOController::class, 'auditVouchers']);
    Route::get('/cpo/process-assigned-vouchers', [CPOController::class, 'cpoProcessAssignedVouchers']);
    Route::post('/cpo/reject-assigned', [CPOController::class, 'cpoRejectAssigned']);
    Route::post('/cpo/report', [CPOController::class, 'updateSelected']);
    Route::get('/cpo/generated', [CPOController::class, 'payGenerated']);
    Route::get('/pay-generated', [CPOController::class, 'payGenerated2']);
    Route::get('/cpo/restore', [CPOController::class, 'payRestore']);
    Route::post('/cpo/restore', [CPOController::class, 'CPOPayRestore']);
    Route::get('/staff/pay-list/{id}', [CPOController::class, 'staffVoucherList']);
    Route::post('/cpo/confirm', [CPOController::class, 'confirm']);
    Route::get('/cpo/epayment', [CPOController::class, 'epayment']);
    Route::get('/cpo/batch', [CPOController::class, 'batchNo']);
    Route::post('/cpo/epayment', [CPOController::class, 'postBatch']);
    Route::get('/view/batch/{id?}', [CPOController::class, 'viewBatch']);
    Route::post('/cpo/reject', [CPOController::class, 'cpoReject']);
    Route::post('/cpo/update-account', [CPOController::class, 'updateAccountNo']);
    Route::post('/cpo/update-batch', [CPOController::class, 'updateBatchNo']);
    Route::post('/move/mandate', [CPOController::class, 'nextAction']);
    Route::post('mark-bank-payment', [CPOController::class, 'markBankPaymentAsPaid']);

    Route::get('/view/batchbytransactionid/{id?}', [CPOController::class, 'viewBatchByTransID']);
    Route::get('/view/mandateByTransactionid/{id?}', [CPOController::class, 'viewMandateByTransactionID']);

    //beneficiary and template from CPO
    Route::any('/cpo-add-beneficiaries/{transId}', [CPOController::class, 'StaffBenficiaryCPO']);
    Route::post('/cpo-submit-added-beneficiaries', [CPOController::class, 'submitAllVoucherBeneficiary']);

    //cpo confirm beneficiary
    Route::get('/voucher-beneficiary/confirm/{transId}', [CPOController::class, 'StaffBenficiaryCPOConfirm']);
    Route::post('/submit-voucher-beneficiary/confirm', [CPOController::class, 'StaffBenficiaryCPOConfirmSubmit']);

    //cpo confirm mandate individual record
    // Route::post('/epayment/toggle-amount', [CPOController::class, 'toggleAmount']);
    // Route::post('/epayment/toggle-wht', [CPOController::class, 'toggleWHT']);
    // Route::post('/epayment/toggle-vat', [CPOController::class, 'toggleVAT']);

    Route::get('/cpo/payments/sent-to-bank', [CPOController::class, 'paymentSentToBank']);
    Route::get('/cpo/payments/sent-to-bank/{batch}', [CPOController::class, 'paymentSentToBankByBatch']);
    Route::post('/cpo/submit-selected-bank-paid', [CPOController::class, 'regeneratedNeft']);
    Route::get('/view/batch/regenerated/{batch?}', [CPOController::class, 'viewRegeneratedBatch']);

    //voucher parameters setup
    Route::get('/view-voucher-parameters', [CPOController::class, 'viewVoucherParam'])->name('voucher.parameters.view');
    Route::get('/voucher-parameters', [CPOController::class, 'createVoucherParam'])->name('voucher.parameters.create');
    Route::post('/voucher-parameters', [CPOController::class, 'storeVoucherParam'])->name('voucher.parameters.store');

    // Route::post('/export/singlebatch', function (Illuminate\Http\Request $request) {

    //     return Excel::download(
    //         new SingleCpoMandateBatchExport($request->batchNo),
    //         "$request->batchNo.xlsx"
    //     );
    // });

    Route::post('/export/singlebatch', function (Illuminate\Http\Request $request) {
        return Excel::download(new StyledBatchExport($request->batchNo, $request->mandateAccNo, $request->mandateBankAddr, $request->mandateDate), 'Batch_' . $request->batchNo . '.xlsx');
    });

    Route::post('/export/singlebatchCapital', function (Illuminate\Http\Request $request) {
        return Excel::download(new StyledBatchCapitalExport($request->batchNo, $request->mandateAccNo, $request->mandateBankAddr, $request->mandateDate), 'Batch_' . $request->batchNo . '.xlsx');
    });

    Route::post('/export/singlebatchRegeratedEpayment', [CPOExportMandateController::class, 'mandateRegenerateConsolidatedExport']);

    // Route::post('/export/consolidated', function (Illuminate\Http\Request $request) {

    //     $ids = json_decode($request->ids);

    //     return Excel::download(
    //         new ConsolidatedCpoMandateBatchExport($ids),
    //         'SCN_Mandate_Consolidated_Batches.xlsx'
    //     );
    // });

    //overtime setup
    Route::get('/staff-overtime-setup', [StaffOvertimeSetupController::class, 'staffOvertimeSetup']);
    Route::post('/update-staff-overtime-setup', [StaffOvertimeSetupController::class, 'staffOvertimeSetupUpdate']);

    Route::get('/overtime-trial', [StaffOvertimeSetupController::class, 'indexTrial']);
    Route::post('/overtime-trial-run', [StaffOvertimeSetupController::class, 'runTrial']);

    Route::post('/export/consolidated', [CPOExportMandateController::class, 'mandateConsolidatedExport']);

    Route::post('/confirm/today', [CPOController::class, 'confirm2day']);

    Route::get('/display/vouchers', [CPOController::class, 'displayVoucherTest']);
    Route::post('/cpo-test/reject', [CPOController::class, 'cpoRejectTest']);
    /***********/
    Route::post('/update/pay-generated', [CPOController::class, 'updatePayGenerated']);
    /************/
    Route::post('/cpo/update-narration', [CPOController::class, 'narrationUpdate']);

    Route::get('/account/details', [CPOController::class, 'accountDetails']);
    Route::post('/account/details', [CPOController::class, 'saveAccountDetails']);
    Route::get('/edit/account/{id}', [CPOController::class, 'editAccountDetails']);
    Route::post('/update/account/{id}', [CPOController::class, 'updateAccountDetails']);
    Route::get('/delete/account/{id}', [CPOController::class, 'delete']);
    Route::post('/get-account/address', [CPOController::class, 'getAccountAddress']);
    Route::post('/toggle/account/status/{id}', [CPOController::class, 'toggleAccountStatus']);
    Route::post('/check-bank-account-unique', [CPOController::class, 'checkBankAccountUnique']);
    Route::post('/check-bank-account-unique-edit', [CPOController::class, 'checkBankAccountUniqueEdit']);

    Route::post('/update/batch', [CPOController::class, 'referenceUpdate']);
    Route::post('/update-payee-account', [CPOController::class, 'payeeAccount']);
    Route::post('/update/date', [CPOController::class, 'dateUpdate']);
    Route::post('/update/ref', [CPOController::class, 'referenceNo']);

    Route::get('/merge-payments', [CPOController::class, 'batchToMerge']);
    Route::post('/merge-payments', [CPOController::class, 'merge']);
    Route::get('/view/merge-payments/{batch?}', [CPOController::class, 'viewMerger']);

    /************** Merged Details *****************/
    Route::post('/update/batch-merged', [CPOController::class, 'referenceUpdateMerged']);
    Route::post('/update-payee-account-merged', [CPOController::class, 'payeeAccountMerged']);
    Route::post('/cpo/update-account-merged', [CPOController::class, 'updateAccountNoMerge']);
    Route::post('/cpo/update-narration-merged', [CPOController::class, 'narrationUpdateMerged']);
    Route::post('/cpo/update-batch-merged', [CPOController::class, 'updateBatchNoMerged']);

    Route::get('/merged-batch/search', [CPOController::class, 'mergedBatch']);
    Route::post('/merged-batch/search', [CPOController::class, 'postMergedBatch']);

    Route::get('/batch/search', [CPOController::class, 'batch']);
    Route::post('/batch/search', [CPOController::class, 'postBatch']);
    Route::get('/batch/{id?}/export', [CPOController::class, 'exportBatch'])->name('batch.export');
    //Signatories
    Route::post('/epay/signatory', 'CPOController@getPhone');

    //TAX MATTER SECTION
    Route::get('/tax-matter-section-report', [TaxMatterController::class, 'showTaxMatterReport'])->name('viewTaxMatterReport');
    Route::post('/tax-matter-section-report', [TaxMatterController::class, 'postSearchReport']);
    Route::post('/update_tax_matter_report', [TaxMatterController::class, 'postRecordUpdate']);
    Route::post('/revert_update_tax_matter_report', [TaxMatterController::class, 'postRevertDescription']);
    Route::get('/export_tax_matter_report', [TaxMatterController::class, 'exportRecordSoftcopy']);
    //Tax Matter Alternative
    Route::any('/tax-matter-report', [TaxMatter::class, 'Index']);

    //Reconciliation routes
    Route::get('reconciliation-date-range', [ReconciliationController::class, 'index']);
    Route::post('reconciliation-date-range', [ReconciliationController::class, 'reconciliationSearchResult']);
    Route::post('send-reconciliation-date-range-result', [ReconciliationController::class, 'sendReconciliationResult']);

    Route::get('/bank-reconciliation-reports', [ReconciliationController::class, 'getBankReconciliationReports'])
        ->name('bank.credits');

    Route::post('/bank-reconciliation-reports', [ReconciliationController::class, 'getBankReconciliationReports'])
        ->name('bank.credits');

    //-------------------------End Pitoff Routes-----------------------------------------

});

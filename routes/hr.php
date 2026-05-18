<?php

use App\Http\Controllers\hr\BasicParameterController;
use App\Http\Controllers\hr\StaffReportController;
use App\Http\Controllers\hr\LgaCoveredController;




use App\Http\Controllers\hr\CandidateController;
use App\Http\Controllers\hr\EstabAdminController;
use App\Http\Controllers\hr\DocumentationController;

use App\Http\Controllers\hr\HolidayController;
use App\Http\Controllers\hr\LeaveCreateController;
use App\Http\Controllers\hr\LeaveController;
use App\Http\Controllers\hr\AlertController;
use App\Http\Controllers\hr\StaffInfoController;
use App\Http\Controllers\hr\ManPowerController;
use App\Http\Controllers\hr\IncrementController;
use App\Http\Controllers\hr\StaffIncrementFunctionController;
use App\Http\Controllers\hr\ProcessVariationController;
use App\Http\Controllers\hr\DetailOfServiceController;
use App\Http\Controllers\hr\EducationController;
use App\Http\Controllers\hr\HospitalController;
use App\Http\Controllers\hr\InterviewScoreSheetController;
use App\Http\Controllers\hr\LanguagesController;
use App\Http\Controllers\hr\NHISController;
use App\Http\Controllers\hr\staffNhisController;
use App\Http\Controllers\hr\ParticularsOfChildrenController;
use App\Http\Controllers\hr\NextOfKinController;
use App\Http\Controllers\hr\ProfileController;
use App\Http\Controllers\hr\DateOfBirthWifeController;
use App\Http\Controllers\hr\GratuityPaymentController;
use App\Http\Controllers\hr\DetailsOfPreviousServiceController;
use App\Http\Controllers\hr\EmolumentController;
use App\Http\Controllers\hr\forPromotionsController;
use App\Http\Controllers\hr\NewProcessVariationController;
use App\Http\Controllers\hr\TerminationOfServiceController;
use App\Http\Controllers\hr\TourLeaveRecordController;
use App\Http\Controllers\hr\RecordOfServiceController;
use App\Http\Controllers\hr\RecordOfEmolumentsController;
use App\Http\Controllers\hr\PasswordController;

use App\Http\Controllers\hr\NHFReportController;
use App\Http\Controllers\hr\PromotionBriefController;
use App\Http\Controllers\hr\StaffPromotionController;
use App\Http\Controllers\payroll\DueForArrearsController;
use Dom\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Web Routes for HR
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'MainController@userArea');

Route::get('/assign-widgets', 'MainController@index')->name('role-widget.form');
Route::post('/assign-widgets', 'MainController@store')->name('role-widget.store');
Route::post('/widget/add', 'MainController@add')->name('widget.add');
Route::delete('/role-widgets/{id}', 'MainController@destroy')->name('role-widget.destroy');
Route::get('/get-widgets/{roleId}', 'MainController@getWidgetsByRole');

Route::get('/confirmation/alert', 'AlertController@confirmationList')->name('confirmAlert');
Route::post('/confirmation/alerts', 'AlertController@confirmationAlertList')->name('confirmAlerts');
Route::group(['middleware' => ['guest']], function () {
    Route::get('login', '\App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', '\App\Http\Controllers\Auth\LoginController@login');
    //forget Password
    Route::get('forget-password', 'PasswordController@userForgetPassword');
    // Route::get('forget-password', [PasswordController::class, 'userForgetPassword']);
    Route::post('forget-password', [PasswordController::class, 'userResetPassword']);
    Route::get('password-reset/resets/{token}', [PasswordController::class, 'ResetPassword']);
    Route::post('password-reset/resets/{token}', [PasswordController::class, 'ResetPassword']);
});

Route::group(['middleware' => ['auth', 'force.password.change', 'permission']], function () {
    // language & fluency
    Route::get('/create-language', "LanguageDetailController@index")->name('create-language');
    Route::post('/store-language', "LanguageDetailController@store")->name('store-language');
    Route::post('/store-fluency', "FluencyDetailController@store")->name('store-fluency');


    //Bulk file movement
    Route::get('/bulk-movement/create', 'BulkFileMovementController@create');
    Route::post('/bulk-movement/save', 'BulkFileMovementController@saveBulk');

    Route::post('/bulk-movement/save', 'BulkFileMovementController@saveBulk');
    Route::post('/bulk-movement/get-staff', 'BulkFileMovementController@getStaff');
    Route::get('/bulk-movement/accept', 'BulkFileMovementController@acceptance');
    Route::post('/bulk-movement/confirmation', 'BulkFileMovementController@confirm');

    Route::post('/bulk-movement/getUsers', 'BulkFileMovementController@getUsers');
    Route::get('/bulk-movement/searchUser/{q?}', 'BulkFileMovementController@autocomplete');
    Route::get('/view-documents/{id?}/{vol?}', 'BulkFileMovementController@viewDocuments');

    Route::post('file-tracking/reject/comment', 'BulkFileMovementController@rejectFile');
    Route::post('file-tracking/bulk/resend', 'BulkFileMovementController@resend');
    Route::get('/bulk-transfer/editfile/{bulkID}', 'BulkFileMovementController@editAndSend');
    Route::post('/bulk-transfer/updatefile', 'BulkFileMovementController@updateAndSend');

    Route::get('/bulk-transfer/move', 'BulkFileMovementController@transfer');
    Route::post('/bulk-transfer/post', 'BulkFileMovementController@postTransfer');

    Route::get('/bulk-transfer/track', 'BulkFileMovementController@trackFile');
    Route::post('/bulk-transfer/search-track', 'BulkFileMovementController@postTrackFile');
    Route::post('/bulk-transfer/track', 'BulkFileMovementController@postTrackFile');

    Route::get('/bulk-transfer/files-sent', 'BulkFileMovementController@filesSent');
    Route::post('/bulk-transfer/cancel', 'BulkFileMovementController@cancel');

    Route::get('/bulk-transfer/get-temp', 'BulkFileMovementController@tempGet');
    Route::post('/bulk-transfer/delete-temp', 'BulkFileMovementController@deleteTemp');
    Route::get('/add-new-file', 'BulkFileMovementController@newFile');
    Route::post('/add-new-file', 'BulkFileMovementController@saveNewFile');
    Route::get('/created-files', 'BulkFileMovementController@createdFiles')->name('files.created');
    Route::get('/review/file', 'BulkFileMovementController@review');
    Route::get('/edit-file/{id?}', 'BulkFileMovementController@editFile');
    Route::post('/update-file', 'BulkFileMovementController@updateFile');
    Route::post('/bulk-transfer/recall', 'BulkFileMovementController@recall');
    Route::delete('/delete-file/{id?}', 'BulkFileMovementController@deleteFile');

    Route::get('/copy/staff', 'BulkFileMovementController@copy');



    /********************** Check From Here ********************/

    //Leave Applicant and Leave Processing Routes

    Route::get('/leave-listing', 'LeaveApplicantsController@index');
    Route::post('/leave-approval', 'LeaveApplicantsController@approveLeave');
    Route::get('/leave/departmental', 'LeaveApplicantsController@show');
    Route::post('/leave/departmental', 'LeaveApplicantsController@approveLeave2');
    Route::post('/leave/departmental/reverse', 'LeaveApplicantsController@reverse2')->name('leave.departmental.reverse');
    Route::post('/move-to-nextstage', 'LeaveApplicantsController@moveToNext');
    Route::post('/reverse-decision', 'LeaveApplicantsController@reverse');
    Route::get('/senior-staffLeave', 'LeaveApplicantsController@seniorStaff');
    Route::post('/move-to-nextstage/senior-staff', 'LeaveApplicantsController@moveToNextSeniorStaff');
    Route::post('/send-memo', 'LeaveApplicantsController@sendMemo');
    Route::get('/leave-report/view', 'LeaveApplicantsController@leaveReport');
    Route::post('/leave-report/view', 'LeaveApplicantsController@leaveReportSearch');

    //Leave Applicant and Leave Processing Routes Ends

    Route::get('/division/changeDivision', 'DivisionController@changeDivisionCreate');
    Route::post('/division/changeDivisionStore', 'DivisionController@changeDivisionStore');

    Route::get('user/register', '\App\Http\Controllers\Auth\RegisterController@registerUser');
    Route::post('user/store', '\App\Http\Controllers\Auth\RegisterController@storeUser');

    //edit user account
    Route::get('/user/editAccount', '\App\Http\Controllers\Auth\RegisterController@editAccount');
    Route::post('user/editAccount', '\App\Http\Controllers\Auth\RegisterController@editAccountStore');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
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

    //CREATE STAFF CONTACT&PHONE
    Route::get('/staffContact/create', 'StaffContactController@create');
    Route::post('staffContact/update', 'StaffContactController@update');
    Route::post('staffContact/saved', 'StaffContactController@store');
    Route::get('staffContact/show-staff-delete/{id}', 'StaffContactController@destroy');

    //staff status
    Route::get('/staffStatus', 'StaffStatusController@loadView');
    Route::get('/staffStatus/pending', 'StaffStatusController@loadPending');
    Route::post('/staffStatus/update', 'StaffStatusController@update');
    Route::post('/staffStatus/findStaff', 'StaffStatusController@findStaff');
    Route::post('/approve', 'StaffStatusController@getApprove');

    //CREATE NEW STAFF TRANSFER
    Route::get('/staff/transfer', 'StaffTransferController@create');
    Route::post('/staff/transfer', 'StaffTransferController@create');

    //R. Variation: Records and Variation
    Route::get('/computer/variation/create', 'VariationController@create_variation');
    Route::post('/staff/variation/update', 'VariationController@update_variation');
    Route::get('/staff/variation/report/{fileNo?}/{variationID?}', 'VariationController@report_variation');
    Route::post('/variation/findStaff', 'VariationController@findStaff');
    Route::get('/variation/findStaff', 'VariationController@getFindStaff');
    //Route::post('/variation/findStaff/variation',             'VariationController@findVariation');
    Route::get('/staff/variation/view/', 'VariationController@listAll');
    Route::post('/variation/view-record/filter', 'VariationController@filter_staff');
    Route::get('/variation/staff/search/json/{q?}', 'VariationController@autocomplete_STAFF');
    //R. Variation : Records and Emolument
    Route::get('/personal-emolument/create', [EmolumentController::class, 'create_emolument']);
    Route::post('/staff/personal-emolument/update', [EmolumentController::class, 'update_emolument']);
    Route::get('/get-designations/{dept_id}', [EmolumentController::class, 'getDesignations']);

    Route::get('/personal-emolument/create-temp', 'EmolumentController@create_temp');
    Route::post('/staff/personal-emolument/temp-update', 'EmolumentController@update_temp');

    Route::get('/staff/personal-emolument/report/{fileNo?}', 'EmolumentController@report_emolument');
    Route::post('/personal-emolument/findStaff', [EmolumentController::class, 'findStaff']);
    Route::post('/personal-emolument/findStaffTemp', 'EmolumentController@findStaffTemp');
    Route::get('/staff/personal-emolument/view/', 'EmolumentController@listAll');
    Route::post('/personal-emolument/view-record/filter', 'EmolumentController@filter_staff');
    Route::get('/personal-emolument/new-staff', 'EmolumentController@listAllNewStaff');

    //R. Variation : mater staff list
    Route::get('/record-variation/view/cadre', [ProfileController::class, 'view_ALL_CADRE_LIST'])->name('recordVariationLoadCadre');
    Route::get('/record-variation/refresh/cadre-list', [ProfileController::class, 'view_ALL_CADRE_LIST_REFRESH']);
    Route::post('/record-variation/view/cadre', [ProfileController::class, 'view_ALL_CADRE_LIST_FILTER']);
    Route::get('/record-variation/view/increment', [ProfileController::class, 'view_ALL_INCREMENT_SO_FAR']);











    //update censors and commendations
    Route::post('/profile/update-censors-commendations', [ProfileController::class, 'updateCENSORESANDCOMMENDATION']);











    //get salary details
    Route::get('/get-salary-details', [ProfileController::class, 'loadSalaryInfo']);

    ///////////////////////////-self service admin settings-///////////////////////////////////////////////

    Route::get('/update-biodata', [ProfileController::class, 'updateRecord']);
    Route::get('/update-education', [ProfileController::class, 'updateEducations']);

    Route::get('/update-birth', [ProfileController::class, 'updateBirth']);
    Route::get('/update-language', [ProfileController::class, 'updateLanguages']);

    Route::get('/update-children', [ProfileController::class, 'updateChildrens']);
    Route::get('/update-salary', [ProfileController::class, 'updateSalarys']);

    Route::get('/update-nok', [ProfileController::class, 'updateNoks']);
    Route::get('/update-wife', [ProfileController::class, 'updateWifes']);

    Route::get('/update-publicservice', [ProfileController::class, 'updatePublicServices']);
    Route::get('/update-censors', [ProfileController::class, 'updateCensors']);

    Route::get('/update-gratuity', [ProfileController::class, 'updateGratuitys']);
    Route::get('/update-termination', [ProfileController::class, 'updateTerminations']);

    Route::get('/update-tour', [ProfileController::class, 'updateTour']);
    Route::get('/update-service', [ProfileController::class, 'updateService']);

    Route::get('/update-emolument', [ProfileController::class, 'updateEmoluments']);

    ///////////////////////////-self service controller-///////////////////////////////////////////////
    //R. Variation : mater staff list
    Route::get('/record-variation/view/cadre-s', 'SelfServiceController@view_ALL_CADRE_LIST')->name('recordVariationLoadCadre');
    Route::get('/record-variation/refresh/cadre-list-s', 'SelfServiceController@view_ALL_CADRE_LIST_REFRESH');
    Route::post('/record-variation/view/cadre-s', 'SelfServiceController@view_ALL_CADRE_LIST_FILTER');
    Route::get('/record-variation/view/increment-s', 'SelfServiceController@view_ALL_INCREMENT_SO_FAR');

    Route::get('/profile/details-s', 'SelfServiceController@details');
    Route::get('/profile/searchUser-s/{q?}', 'SelfServiceController@autocomplete')->name('profile.search'); //by json
    //Route::post('/profile/details-s',                         'SelfServiceController@details');
    Route::post('/profile/searchUser/showAll-s', 'SelfServiceController@showAll');
    Route::get('/profile/details-s/{fileNo?}', 'SelfServiceController@userCallBack');

    //update biodata
    Route::post('/profile/update-s', 'SelfServiceController@updateBIODATA');
    Route::post('/profile/update-salary-details-s', 'SelfServiceController@updateSALARYDETAILS');
    Route::get('/get-education-details-s', 'SelfServiceController@loadEducation');

    //update particulars of birth
    Route::post('/profile/update-particulars-of-birth-s', 'SelfServiceController@updatePOB');



    //update particulars of education
    Route::post('/profile/update-nok-s', 'SelfServiceController@updateNOK');

    //update particulars of wife
    Route::post('/profile/update-wife-s', 'SelfServiceController@updateWIFE');

    //update particulars of children
    Route::post('/profile/update-children-s', 'SelfServiceController@updateCHILDREN');

    //update particulars of language
    Route::post('/profile/update-language-s', 'SelfServiceController@updateLANGUAGE');

    //update previous public service
    Route::post('/profile/update-previous-service-s', 'SelfServiceController@updatePUBLICSERVICE');

    //update censors and commendations
    Route::post('/profile/update-censors-commendations-s', 'SelfServiceController@updateCENSORESANDCOMMENDATION');

    //update gratuity
    Route::post('/profile/update-gratuity-s', 'SelfServiceController@updateGRATUITY');

    //update terminate of service
    Route::post('/profile/update-terminate-s', 'SelfServiceController@updateTERMINATIONSERVICE');

    //update tour and leave
    Route::post('/profile/update-tour-leave-s', 'SelfServiceController@updateTOURLEAVE');

    //update record of service
    Route::post('/profile/update-record-service-s', 'SelfServiceController@updateSERVICERECORD');

    //update record of emolument
    Route::post('/profile/update-record-emolument-s', 'SelfServiceController@updateEMOLUMENT');

    //update profile picture
    Route::post('/profile/picture-update-s', 'SelfServiceController@updatePROFILEPICTURE');

    //get salary details
    Route::get('/get-salary-details-s', 'SelfServiceController@loadSalaryInfo');

    /********************** Check From Here up after man power comment ********************/

    //Man Power

    Route::get('/map-power/view/cadre', 'ManPowerController@view_ALL_CADRE_LIST')->name('loadCadre');
    Route::post('/map-power/view/cadre', 'ManPowerController@view_ALL_CADRE_LIST_FILTER');
    Route::get('/map-power/view/filter/cadre', 'ManPowerController@view_CENTRAL_CADRE_FILTER_CONTINUE');
    Route::get('/map-power/staff/search/json/{q?}', 'ManPowerController@search_CENTRAL_LIST_by_json');

    Route::get('/manpower/budget', 'ManPowerController@viewBudget');
    Route::get('/map-power/view/increment', 'ManPowerController@view_ALL_INCREMENT_SO_FAR');
    Route::get('/map-power/view/reload-cadre', 'ManPowerController@view_ALL_CADRE_REFRESH')->name('refreshCadre');

    Route::post('/manpower/search/central', 'ManPowerController@searchCentral');
    Route::get('/earning_deduction/approval', 'EarningDeductionController@index');
    Route::post('/earning_deduction/approval', 'EarningDeductionController@index');
    Route::get('/earn_deduct_staffcv/{id?}', 'EarningDeductionController@edscv');
    Route::post('/earn_deduct_staffcv/{id?}', 'EarningDeductionController@edscv');
    Route::get('/gotoexport', function () {
        return view('EarningDeduction.gotoexport');
    });
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








    //Record of Censures and Commendations
    Route::get('/commendations/create/{fileNo?}', 'CensureCommendationController@index');
    Route::get('/commendations/remove/{id?}', 'CensureCommendationController@delete');
    Route::post('/commendations/create', 'CensureCommendationController@store');
    Route::get('/commendations/edit/{id?}', 'CensureCommendationController@view');
    Route::post('/commendations/edit', 'CensureCommendationController@update');
    Route::get('/profile/censures-commendations/report/{fileNo?}', 'CensureCommendationController@report');




    // OPEN REGISTRY
    //create new staff
    //Route::get('/new-staff/create', 								'OpenRegistryController@NEW_STAFF');
    Route::post('/new-staff/store', 'OpenRegistryController@store_NEW_STAFF');
    Route::post('/staff-report/view', 'OpenRegistryController@filter_staff');
    Route::get('/staff-report/view', 'OpenRegistryController@listAll');
    Route::get('/staff/search/json/{q?}', 'OpenRegistryController@autocomplete_STAFF');

    Route::post('/new-staff/getcourt', 'OpenRegistryController@getCourt');
    Route::post('/new-staff/getdepartments', 'OpenRegistryController@getDepartments');
    Route::post('/new-staff/getdesignations', 'OpenRegistryController@getDesignations');

    Route::get('/openregistry/create/', 'OpenRegistryController@indexview');
    Route::post('/data/searchUser/showAll', 'OpenRegistryController@showAll');
    Route::post('/data/searchUser/showAllData', 'OpenRegistryController@showAllData');
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
    // Route::get('/profile/details/{ID?}/{fileNo?}', 'EditStaffProfileController@viewEditBioData');
    // Route::get('/profile/details/{fileNo?}', 'EditStaffProfileController@details');
    //Education
    // Route::get('/profile/details/{ID?}/{fileNo?}', 'EditStaffProfileController@viewEditBioData');
    ////ENDS HERE///

    // Pension
    Route::get('/pension/create', 'PensionController@index')->name('create');
    Route::post('/pension/displaynames', 'PensionController@showAll');
    Route::post('/pension/compute', 'PensionController@computePension');
    Route::post('/pension/compute/batch', 'PensionController@computePensionBatch');
    Route::post('/update/recordofemolument/getdetail', 'RecordOfEmolumentsController@getDetail');
    Route::post('/pension/getpension', 'PensionController@getpension');
    Route::get('/pension/report', 'PensionController@pensionReport');
    Route::post('/pension/report/view', 'PensionController@generateReport');
    Route::get('/pension/report/view', 'PensionController@pensionReport');
    Route::post('/pension/report/monthlyReport', 'PensionController@monthlyReport')->name('reportMonthly');
    Route::get('/pension/all-report', 'PensionController@allPensionReport')->name('reportAll');
    Route::post('/pension/staff/update', 'PensionController@updateStaffPension');
    Route::post('/pension/staff/delete', 'PensionController@softDeleteStaffPension');
    // Pension Manager
    Route::get('/pension-manager/create', 'PensionController@create_PFA')->name('create_PFA');
    Route::post('/pensionmanager/store', 'PensionController@store_PFA');
    Route::get('/pensionmanager/view', 'PensionController@view_PFA');
    Route::get('pension-manager/edit/{id?}', 'PensionController@view_edit_PFA');





    // Report
    Route::get('/report/selectrange', 'ReportController@index');
    Route::post('/report/show', 'ReportController@pensionReport');

    /********** Records and variation  ****************/
    // offer of appointment
    Route::get('/offerofappointment/createoffer/{candidateID?}', 'OfferOfAppointmenController@indexoffer')->name('offerAppointmentLetter');
    Route::get('/offerofappointment/createletter', 'OfferOfAppointmenController@indexletter');
    Route::get('/offerofappointment/acceptance', 'OfferOfAppointmenController@indexaccept');
    Route::get('/offerofappointment/medicalexam', 'OfferOfAppointmenController@indexmedical');
    Route::post('/offerofappointment/getfileno', 'OfferOfAppointmenController@getfileNo');
    Route::post('/offerofappointment/save', 'OfferOfAppointmenController@storeOffer');
    Route::post('/offerofappointment/addletter', 'OfferOfAppointmenController@storeletter');
    Route::post('/offerofappointment/letterfileno', 'OfferOfAppointmenController@letterfileno');
    Route::post('/offerofappointment/medicalexam', 'OfferOfAppointmenController@medicalexam');
    Route::post('/offerofappointment/add', 'OfferOfAppointmenController@storemedicalexam');
    Route::post('/offerofappointment/add-acceptance', 'OfferOfAppointmenController@storeacceptance');
    Route::post('/offerofappointment/getdata', 'OfferOfAppointmenController@acceptance');
    Route::post('/offerofappointment/bearername', 'OfferOfAppointmenController@getbearer');

    Route::post('/offerofappointment/print-letter-from-list', 'OfferOfAppointmenController@listletterprint');
    Route::post('/offerofappointment/print-offer-from-list', 'OfferOfAppointmenController@listofferprint');
    Route::post('/offerofappointment/print-medical-from-list', 'OfferOfAppointmenController@listmedicalprint');
    Route::post('/offerofappointment/print-acceptance-from-list', 'OfferOfAppointmenController@listacceptanceprint');
    // offer of appointment Listing
    Route::get('/offerofappointment/listoffer', 'OfferOfAppointmentListingController@offerlisting');
    Route::get('/offerofappointment/listletter', 'OfferOfAppointmentListingController@letterlisting');
    Route::get('/offerofappointment/listacceptance', 'OfferOfAppointmentListingController@acceptancelisting');
    Route::get('/offerofappointment/listmedicalexam', 'OfferOfAppointmentListingController@medicallisting');
    Route::post('/data/searchUser/showAll', 'OfferOfAppointmentListingController@showAll');
    Route::get('/data/searchUser/{q?}', 'OfferOfAppointmentListingController@autocomplete');
    Route::post('/offerofappointment/viewacceptance', 'OfferOfAppointmentListingController@filter_acceptance');
    Route::post('/offerofappointment/viewletters', 'OfferOfAppointmentListingController@filter_appoinmentletters');
    Route::post('/offerofappointment/viewoffers', 'OfferOfAppointmentListingController@filter_offerletters');
    Route::post('/offerofappointment/viewmedical', 'OfferOfAppointmentListingController@filter_medicals');

    //handling privileges to all users except CPO and Tax Staff
    Route::group(['middleware' => ['role:admin|salary supervisor|salary staff|audit staff|super admin|salary collator|nhf staff']], function () {
        //Bank
        Route::get('/bank/create', 'BankController@create');
        Route::post('/bank/store', 'BankController@store');
        Route::post('/bank/findBank', 'BankController@findBank');
        //Inactive staff
        Route::get('/inactivestaff', 'InactiveStaffController@loadView');
        Route::post('/inactivestaff/report', 'InactiveStaffController@loadReport');
        //Picture Viewer
        Route::get('/pictureViewer', 'PictureViewerController@loadView');
        Route::post('/pictureViewer/report', 'PictureViewerController@loadReport');
        Route::post('/pictureViewer/create', 'PictureViewerController@store');
        Route::post('/pictureViewer/findStaff', 'PictureViewerController@findStaff');
    });

    //handling privileges to all users except CPO, audit and Tax Staff
    Route::group(
        ['middleware' => ['role:admin|salary supervisor|salary staff|super admin']],
        function () {
            //SearchUser
            Route::get('/searchUser/create', 'SearchUserController@create');
            Route::post('/searchUser/create', 'SearchUserController@retrieve');
            Route::get('/searchUser/{q?}', 'SearchUserController@autocomplete');
        }
    );

    //handling admin and staff supervisor for active month priveledges
    Route::group(
        ['middleware' => ['role:admin|salary supervisor|super admin']],
        function () {
            //set active month
            //Route::get('/activeMonth/create', 					'ActiveMonthController@create');
            //Route::post('/activeMonth/create', 					'ActiveMonthController@store');
        }
    );
    //handling admin priveledges
    //Route::group(['middleware' => ['role:admin|super admin']],
    //function() {
    //Classification
    Route::get('/classcode/create', 'ClassificationController@create');
    Route::post('/classcode/store', 'ClassificationController@store');
    Route::get('/classcode/{findData}', 'ClassificationController@findData');
    Route::post('/storeupdate', 'ClassificationController@update');
    //Division
    Route::get('/division/create', 'DivisionController@create');
    Route::post('/division/store', 'DivisionController@store');
    Route::get('/division/destroy/{id?}', 'DivisionController@destroy');

    //Bank List
    Route::get('/banklist/create', 'BankListController@create');
    Route::post('/banklist/store', 'BankListController@store');
    Route::get('/banklist/remove/{bankID?}', 'BankListController@delete');
    Route::get('/role/create', 'RoleController@create');
    Route::post('/role/create', 'RoleController@store');
    //Account lock
    // Route::get('/account/lock-all', 'AccountLockController@lockAll');
    // Route::post('/account/lock-all', 'AccountLockController@lockAllStore');
    // Route::get('/account/unlock', 'AccountLockController@unlockOne');
    // Route::post('/account/unlock', 'AccountLockController@unlockOneStore');
    //});

    //handling super admin priveledges
    //Route::group(['middleware' => ['role:super admin']],
    //function() {
    //audit log
    Route::get('/auditLog/create', 'AuditLogController@create');
    Route::post('/auditLog/create', 'AuditLogController@userDetails');
    Route::post('/auditLog/query', 'AuditLogController@userQuery');
    Route::post('/auditLog/finduser', 'AuditLogController@finduser');

    Route::get('/role/userRole', 'RoleController@userRoleCreate');
    Route::post('/role/userRole', 'RoleController@userRoleStore');

    //viewing all user
    Route::get('/role/viewUser', 'RoleController@index');
    //removing role from a specific user
    Route::get('/role/{id}/user/{userid}', 'RoleController@destroy');
    //viewing role for each user
    Route::get('/role/viewUser/{id}', 'RoleController@retrieve');
    //For PERMISSION configuration using get and POST
    Route::get('/permission/create', 'PermissionController@index');
    Route::post('/permission/create', 'PermissionController@store');

    Route::get('/permission/permRole', 'PermissionController@PermRoleCreate');
    Route::post('/permission/permRole', 'PermissionController@PermRoleStore');


    Route::get('/compute/promotion/variation', 'VariationController@promotionVariation');

    Route::get('/training', 'TrainingController@index')->name('showTraining');
    Route::get('/all-created-training', 'TrainingController@viewAllCreated')->name('viewAllTraining');
    Route::post('/training', 'TrainingController@saveTraining')->name('postTraining');
    Route::get('/edit-training/{id}', 'TrainingController@editTraining');
    Route::post('/edit-training', 'TrainingController@saveEditTraining')->name('editTraining');
    Route::post('/delete-training', 'TrainingController@deleteTraining')->name('deleteTraining');
    Route::get('/training-type', 'TrainingController@createType');
    Route::post('/training-type', 'TrainingController@storeTrainingType');
    Route::post('/update-training-type', 'TrainingController@updateTrainingType');
    Route::get('/training-type/delete/{id}', 'TrainingController@deleteTrainingType');
    Route::get('/training-admin', 'TrainingController@admin')->name('adminTraining');
    Route::get('/training-secretary', 'TrainingController@secretary')->name('secretaryTraining');
    Route::post('/training-admin-approval', 'TrainingController@adminApproval')->name('pushToAdminTraining');
    Route::post('/training-push-unit', 'TrainingController@pushForTraining')->name('pushTrainingUnit');
    Route::post('/training-approval', 'TrainingController@pushForApproval')->name('secretaryApproval');
    Route::post('/training-approval-director', 'TrainingController@directorApproval')->name('trainingDirectorApproval');
    Route::post('/training-secretary-approval', 'TrainingController@secretaryApproval')->name('secretaryApprovalStage');
    Route::post('/revert-training-to-sender', 'TrainingController@revertTrainingToSender')->name('revertTraining');
    Route::get('/training-staff-department/{id}', 'TrainingController@selectStaffDepartment')->name('adminSelectDepartment');
    Route::get('/training-director', 'TrainingController@directorPage')->name('trainingDirectorPage');
    Route::get('/training-batches/{id}', 'TrainingController@batchPortions')->name('trainingBatch');
    Route::get('/training-staff', 'TrainingController@getStaff')->name('adminPostStaff');
    Route::post('/training-staff', 'TrainingController@selectStaff')->name('adminSelectStaff');
    Route::post('/training-staff-deselect', 'TrainingController@deSelectStaff')->name('adminDeSelectStaff');
    Route::post('/training-conclude', 'TrainingController@concludeTraining')->name('concludeTraining');
    Route::post('/training-reverse-conclude', 'TrainingController@reverseConcludeTraining')->name('reverseConcludeTraining');

    //training report
    Route::get('/get-report-headadmin', 'TrainingController@reportHeadAdmin');
    Route::get('/get-report-headtraining', 'TrainingController@reportHeadTraining');
    Route::get('/search-training-report', 'TrainingController@trainingReport')->name('trainingReport');
    Route::get('/get-all-report', 'TrainingController@getReport');
    Route::get('/search-training-by-title/{title}', 'TrainingController@searchReportByTitle');
    Route::get('/search-training-by-year/{year}', 'TrainingController@searchReportByYear');
    Route::get('/generate-report/{id}', 'TrainingController@generateReportByID');
    Route::get('training-comment/{id}', 'TrainingController@getComment');
    Route::get('/complete-training-and-report', 'TrainingController@completeTrainingPage');
    Route::post('/complete-training-and-report', 'TrainingController@completeTrainingAndReport')->name('completeTraining');
    Route::post('/forward-training-report', 'TrainingController@forwardTrainingReport')->name('forwardTrainingReport');

    //staff check nominated for training
    Route::get('/check-nominated-training/{user}', 'TrainingController@getStaffNominatedTraining');
    Route::get('/nomination-letter/{trainingID}/for/{userID}', 'TrainingController@nominationLetter');

    //letter of application
    Route::get('/forms/letter-of-application', 'VariationFormsController@letterOfApplication');
    Route::get('/forms/appointment-form', 'VariationFormsController@appointmentForm');
    Route::get('/forms/referee-form', 'VariationFormsController@refereeForm');
    Route::get('/forms/leave-form', 'VariationFormsController@leaveForm');

    //});

    // Open Registry 2 Routes
    Route::get('/open-file-registry/create', 'OpenRegistry2Controller@closingFileIndex');
    Route::post('/open-file-registry/save', 'OpenRegistry2Controller@saveClosingFile');
    Route::post('/update/closed-volume', 'OpenRegistry2Controller@updateClosingFile');
    Route::get('/open-file-registry/incoming-letter', 'OpenRegistry2Controller@incomingLetterIndex');
    Route::get('/mailattachment/remove/{id}', 'OpenRegistry2Controller@removeAttachment')->name('removeAttachment');
    Route::post('/open-file-registry/saveletter', 'OpenRegistry2Controller@saveIncomingLetter');
    Route::post('/update/incoming-letter', 'OpenRegistry2Controller@updateIncomingLetter');
    Route::post('/move/incoming-letter', 'OpenRegistry2Controller@moveIncomingLetterIndex');
    Route::post('/select-move-recipient', 'OpenRegistry2Controller@selectmoveRecipient');
    Route::post('/attach/incoming-letter', 'OpenRegistry2Controller@attachIncomingLetter');
    Route::get('/move/departments/{id}', 'OpenRegistry2Controller@getDepts');
    Route::get('/open-file-registry/outgoing-letter', 'OpenRegistry2Controller@outgoingLetterIndex');
    Route::post('/open-file-registry/saveoutgoing', 'OpenRegistry2Controller@saveOutgoingLetter');
    Route::get('/open-file-registry/mail', 'OpenRegistry2Controller@mailIndex');
    Route::post('/open-file-registry/savemail', 'OpenRegistry2Controller@saveMail');
    Route::get('/open-file-registry/view-mails', 'OpenRegistry2Controller@viewMails');
    Route::get('/open-file-registry/search', 'OpenRegistry2Controller@autocomplete');
    Route::post('/open-file-registry/filter', 'OpenRegistry2Controller@filter_mails');
    Route::get('/open-file-registry/view-closed-files', 'OpenRegistry2Controller@viewClosedFiles');
    Route::get('/open-file-registry/searchclosed', 'OpenRegistry2Controller@auto');
    Route::post('/open-file-registry/filterclosed', 'OpenRegistry2Controller@filterClosedFiles');
    Route::get('/move/recipients/{deptId}', 'OpenRegistry2Controller@getRecipients');

    Route::get('/open-file-registry/view-outgoing', 'OpenRegistry2Controller@viewOutgoing');
    Route::get('/open-file-registry/searchoutgoing', 'OpenRegistry2Controller@autocompleteOutgoing');
    Route::post('/open-file-registry/filter-outgoing', 'OpenRegistry2Controller@filterOutgoing');

    Route::get('/open-file-registry/view-incoming', 'OpenRegistry2Controller@viewIncoming');
    Route::get('/open-file-registry/searchincoming', 'OpenRegistry2Controller@autocompleteIncoming');
    Route::post('/open-file-registry/filter-incoming', 'OpenRegistry2Controller@filterIncoming');

    //File upload routes

    Route::get('/documents/upload', 'Uploader@documentsUpload')->name('uploader');
    Route::post('/documents/upload', 'Uploader@uploadDocuments')->name('uploader_post');
    Route::get('/documents/delete/{id}', 'Uploader@deleteDocument');
    Route::get('/documents/upload/admin', 'Uploader@adminUpload');
    Route::post('/documents/upload/admin', 'Uploader@adminUploadDocument');
    Route::get('/documents/view/user', 'Uploader@selectUserView');
    Route::post('/documents/view/user', 'Uploader@findUserDocumentsById');
    Route::get('/documents/fetch/{id}', 'Uploader@getDocsByStaffId');

    /// excel routes scn
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

    Route::post('/user-assign/assign', '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@assignUser');
    Route::post('/user/display', '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@displayUser');
    Route::get('/user/search/{q?}', '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@autocomplete');



    //dependant parameter
    Route::get('/staff/dependant', 'DependantController@getDependant');
    Route::post('/staff/dependant', 'DependantController@postDependant');
    Route::post('/staff/dependant-delete/{id}', 'DependantController@deleteDependant');

    //Create Leave

    Route::get('/Leave/leavetype',                 [LeaveCreateController::class, 'index']);
    Route::post('/saveLeave/leavetype',                       [LeaveCreateController::class, 'store']);
    Route::get('/edit-leave/leavetype/{id}',                       [LeaveCreateController::class, 'edit']);
    Route::post('/update/leavetype',                           [LeaveCreateController::class, 'update']);
    Route::get('/leave/delete/{id}',                 [LeaveCreateController::class, 'delete']);

    //leave management

    Route::get('/apply/leave',                 [LeaveCreateController::class, 'ApplyLeave']);
    Route::post('/saveapply/leave',                       [LeaveCreateController::class, 'saveApplyLeave']);
    Route::get('/calculate-end-date', [LeaveCreateController::class, 'calculateEndDate']);




    //hod approve & reject leave
    Route::get('leave/hod/approve/{id}', [LeaveCreateController::class, 'hodApprove'])
        ->name('hod.approve');
    Route::get('leave/hod/reject/{id}', [LeaveCreateController::class, 'hodReject'])
        ->name('hod.reject');

    //admin approve & reject leave
    Route::get('leave/admin/approve/{id}', [LeaveCreateController::class, 'adminApprove'])
        ->name('admin.approve');
    Route::get('leave/admin/reject/{id}', [LeaveCreateController::class, 'adminReject'])
        ->name('admin.reject');

    /////// Start Leave Of Absence /////////

    //loa management

    Route::get('/apply/loa',                 [LeaveCreateController::class, 'ApplyLoa']);
    Route::post('/saveapply/loa',                       [LeaveCreateController::class, 'saveApplyLoa']);
    Route::get('/calculate-end-date', [LeaveCreateController::class, 'calculateEndDate']);
    Route::get('/leave/loa-list', [LeaveCreateController::class, 'loaList'])->name('loa.list');

    //hod approve & reject leave of absence
    Route::get('leave/hod/approve-loa/{id}', [LeaveCreateController::class, 'hodApproveLoa'])
        ->name('hod.approve-loa');
    Route::get('leave/hod/reject-loa/{id}', [LeaveCreateController::class, 'hodRejectLoa'])
        ->name('hod.reject-loa');

    //admin approve & reject leave
    Route::get('leave/admin/approve-loa/{id}', [LeaveCreateController::class, 'adminApproveLoa'])
        ->name('admin.approve-loa');
    Route::get('leave/admin/reject-loa/{id}', [LeaveCreateController::class, 'adminRejectLoa'])
        ->name('admin.reject-loa');

    // Show edit form (or modal data)
    Route::get('/loa/edit/{id}', [LeaveCreateController::class, 'editLoa'])->name('loa.edit');

    // Submit update
    Route::post('/loa/update/{id}', [LeaveCreateController::class, 'updateLoa'])->name('loa.update');

    //// End of leave of absence


     /////// Start IOU /////////


    //IOU management

    Route::get('/apply/iou',                 [LeaveCreateController::class, 'ApplyIOU']);
    Route::post('/saveapply/loa',                       [LeaveCreateController::class, 'saveApplyLoa']);
    Route::get('/calculate-end-date', [LeaveCreateController::class, 'calculateEndDate']);
    Route::get('/leave/loa-list', [LeaveCreateController::class, 'loaList'])->name('loa.list');

    //hod approve & reject leave of absence
    Route::get('leave/hod/approve-loa/{id}', [LeaveCreateController::class, 'hodApproveLoa'])
        ->name('hod.approve-loa');
    Route::get('leave/hod/reject-loa/{id}', [LeaveCreateController::class, 'hodRejectLoa'])
        ->name('hod.reject-loa');

    //admin approve & reject leave
    Route::get('leave/admin/approve-loa/{id}', [LeaveCreateController::class, 'adminApproveLoa'])
        ->name('admin.approve-loa');
    Route::get('leave/admin/reject-loa/{id}', [LeaveCreateController::class, 'adminRejectLoa'])
        ->name('admin.reject-loa');

    // Show edit form (or modal data)
    Route::get('/loa/edit/{id}', [LeaveCreateController::class, 'editLoa'])->name('loa.edit');

    // Submit update
    Route::post('/loa/update/{id}', [LeaveCreateController::class, 'updateLoa'])->name('loa.update');

    //// End of leave of absence


    //leave management
    Route::post('/leave/definition',                          [LeaveController::class, 'postDefinition']);
    Route::get('/leave/definition',                           [LeaveController::class, 'getDefinition']);
    Route::post('/leave/application',                          [LeaveController::class, 'Application']);
    Route::get('/leave/application',                           [LeaveController::class, 'Application']);
    Route::post('/leave/query',                          [LeaveController::class, 'LeaveQuery']);
    Route::get('/leave/query',                           [LeaveController::class, 'LeaveQuery']);
    Route::post('/leave/approval',                          [LeaveController::class, 'Approval']);
    Route::get('/leave/approval',                           [LeaveController::class, 'Approval']);
    Route::get('/self-service/notification',                  [LeaveController::class, 'getNotification']);
    Route::get('/self-service/releaveaction',                  [LeaveController::class, 'ReleaveResponse']);
    Route::post('/self-service/releaveaction',                  [LeaveController::class, 'ReleaveResponse']);

    //Head of Department
    Route::get('/department/departmentHead', 'DepartmentController@index');
    Route::post('/department/departmentHeaD', 'DepartmentController@store');
    Route::post('/UpdateHeadOfdepartmentHead', 'DepartmentController@update')->name('update');

    //annual leave application
    Route::get('/annual/leave/application', 'AnnualLeaveController@ApplicationForm');
    Route::post('/annual/leave/application', 'AnnualLeaveController@saveApplicationForm');
    Route::post('/remove/application', 'AnnualLeaveController@RemoveApplication');
    Route::post('/annual/leave/reapply', 'AnnualLeaveController@reApply');
    Route::post('/annual/leave/edit', 'AnnualLeaveController@editLeave');

    //route for leave days
    Route::get('/get-leavedays', 'AnnualLeaveController@getLeaveDaysSum');

    //for hod approval
    Route::get('/annual/leave/approval', 'AnnualLeaveController@HodApproval');
    Route::post('/recommend/leave', 'AnnualLeaveController@RecommendLeave');
    Route::post('/reject/leave', 'AnnualLeaveController@RejectLeave');
    Route::post('/cancel/leave', 'AnnualLeaveController@CancelLeave');
    Route::get('/notify-staff', 'AnnualLeaveController@dontnotifyStaff');
    Route::get('/notify-staffs', 'AnnualLeaveController@notifyStaff');
    Route::get('/comments/view-hodes', 'AnnualLeaveController@viewCommentHODES'); //hod-applicant view comments
    Route::get('/comments/view', 'AnnualLeaveController@viewComment');            //hod view comments
    Route::get('/comments/view-s', 'AnnualLeaveController@viewComment2');         //admin view comments
    Route::get('/comments/view-a', 'AnnualLeaveController@viewComment3');         //admin view comments
    Route::get('/comments/view-e', 'AnnualLeaveController@viewCommente');         //ES view comments
    Route::get('/reply/view', 'AnnualLeaveController@viewHODReply');              //applicant view hod reply

    Route::post('/notify/staff', 'AnnualLeaveController@notifyApplicant')->name('notify-applicant');
    Route::post('/notify/admin', 'AnnualLeaveController@notifyAdmin')->name('notify-admin');

    //route for admin approval
    Route::get('/annual/leave/finalapproval', 'AnnualLeaveController@index');
    Route::post('/approve/leave', 'AnnualLeaveController@FinalApproveLeave');
    Route::post('/finalreject/leave', 'AnnualLeaveController@FinalRejectLeave');
    Route::post('/finalcancel/leave', 'AnnualLeaveController@FinalCancelLeave');

    //route for ES approval
    Route::get('/annual/leave/finalapproval_es', 'AnnualLeaveController@FinalApproval_ES');
    Route::post('/approve/leave_es', 'AnnualLeaveController@FinalApproveLeaveES');
    Route::post('/finalreject/leave_es', 'AnnualLeaveController@FinalRejectLeaveES');
    Route::post('/finalcancel/leave_es', 'AnnualLeaveController@FinalCancelLeaveES');
    //Route::post('/remove/application_es',                             'AnnualLeaveController@RemoveApplicationES');

    //Staff Report
    Route::get('/staff/report', 'ReportController@index');
    Route::post('/court/setsession', 'ReportController@sessionset');
    Route::post('/report/search-staff', 'ReportController@SearchStaff');
    Route::get('/report/search/{q?}', 'ReportController@SearchAutocomplete');
    Route::get('/staff/nhf/report', 'ReportController@nhf');
    Route::post('/staff/nhf/search', 'ReportController@searchNHF');

    Route::post('/report/export/excel', 'ReportController@exportToExcel');
    Route::post('/export/nhf', 'ReportController@exportNHF');

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

    //payroll:- salary setup
    Route::get('/salary/create', 'payroll\SalarySetupController@Create');
    //Route::post('/salary/create', 'payroll\SalarySetupController@display');
    Route::post('/salary/create', 'payroll\SalarySetupController@saveSalary');

    //payroll:- Consolidated salary setup
    Route::get('/con-salary/create', 'payroll\ConSalarySetupController@Create');
    //Route::post('/con-salary/create', 'payroll\ConSalarySetupController@display');
    Route::post('/con-salary/create', 'payroll\ConSalarySetupController@saveSalary');
    Route::any('/structure-upload', 'BasicParameterController@Setup');

    //New salary structure setup
    Route::get('/new-salary/structure', 'NewSalaryStructureController@Create');
    Route::post('/new-salary/structure', 'NewSalaryStructureController@saveSalary');

    Route::get('/deduction', 'payroll\Earningdeduction@Show');
    Route::post('/deduction', 'payroll\Earningdeduction@Show');
    Route::post('/deduction', 'payroll\Earningdeduction@show');
    Route::post('/deduction/delete', 'payroll\Earningdeduction@deleteEarning');
    //Route::post('/salary/create', 'payroll\arningdeduction@display');
    //Route::post('/salary/create',   'payroll\arningdeduction@saveSalary');

    //payroll:- Salary Scale
    Route::get('/salaryScale', 'payroll\SalaryScaleController@index');
    Route::post('/salaryScale', 'payroll\SalaryScaleController@getSalary');
    Route::get('/salaryScale/{type}/{court}', 'payroll\SalaryScaleController@customPaging');
    Route::post('/courts/retrieve', 'payroll\ControlVariableController@getDivisions');
    Route::post('/cv/session', 'payroll\ControlVariableController@getDStaffInfo');

    //payroll:- Control Variables
    Route::post('/courts/retrieve', 'ControlVariableController@getDivisions');
    Route::post('/cv/session', 'ControlVariableController@getDStaffInfo');
    Route::post('/variable/setSession', 'ControlVariableController@sessionset');
    Route::post('/variable/findStaff', 'ControlVariableController@findStaff');
    //Route::get('/variable/create', 'ControlVariableController@create');
    Route::post('/variable/store', 'ControlVariableController@update');
    Route::get('/variable/view/{fileNo}', 'ControlVariableController@view');
    // Test Sample

    Route::get('/variable/create', 'PayrollController@ControlVariable');
    Route::post('/variable/create', 'PayrollController@ControlVariable');

    //bank set up
    Route::post('/session/court', 'BankController@sessionset');

    //STAFF REGISTRATION
    Route::get('/new-staff/create', 'OpenRegistryController@viewRegistrationForm')->name('newStaff_court');
    Route::get('/staff-registration/court', 'OpenRegistryController@getCourtTab')->name('getCoutTab');
    Route::post('/staff-registration/court', 'OpenRegistryController@postCourtTab');
    Route::get('/staff-registration/basic-info', 'OpenRegistryController@getBasicTab')->name('getBasicTab');
    Route::post('/staff-registration/basic-info', 'OpenRegistryController@postBasicTab');
    Route::get('/staff-registration/contact-info', 'OpenRegistryController@getContactTab')->name('getContactTab');
    Route::post('/staff-registration/contact-info', 'OpenRegistryController@postContactTab');
    Route::get('/staff-registration/employment-info', 'OpenRegistryController@getEmploymentTab')->name('getEmploymentTab');
    Route::post('/staff-registration/employment-info', 'OpenRegistryController@postEmploymentTab');
    Route::get('/staff-registration/preview-info', 'OpenRegistryController@getPreviewTab')->name('getPreviewTab');
    Route::get('/staff-registration/current-staff', 'OpenRegistryController@getCurrentStaff')->name('getCurrentStaff');
    Route::post('/staff-registration/current-staff', 'OpenRegistryController@postCurrentStaff');
    Route::get('/staff-registration/new-registration', 'OpenRegistryController@newRegistration')->name('newRegistration');
    Route::get('/staff-registration/registration-complete', 'OpenRegistryController@finalRegistration')->name('finalRegistration');
    Route::post('/staff-registration/designation', 'OpenRegistryController@getDesignationJson');
    Route::post('/staff-registration/delete', 'OpenRegistryController@deleteOngoingRegistration');
    Route::post('/staff-registration/current-staff-by-court', 'OpenRegistryController@postCurrentStaffCourtID');
    Route::get('/staff-registration/browse-picture', 'OpenRegistryController@loadUploadView')->name('uploadFile');
    Route::post('/staff-registration/browse-picture', 'OpenRegistryController@uploadBrowsedPicture');

    //PRIVILEDGES
    //Create Technical User
    // Route::get('/technical/create', 'MasterRolePermission\CreateTechnicalUserController@create')->name('createTechnicalUser');
    // Route::Post('/technical/create', 'MasterRolePermission\CreateTechnicalUserController@store')->name('createTechnicalUser');
    // //user roles
    // Route::get('/user-role/create', 'MasterRolePermission\UserRoleController@create')->name('CreateUserRole');
    // Route::post('/user-role/add', 'MasterRolePermission\UserRoleController@addRole');
    // Route::get('/user-role/viewroles', 'MasterRolePermission\UserRoleController@displayRoles')->name('AllRole');
    // Route::get('/user-role/edit/{roleID}', 'MasterRolePermission\UserRoleController@editRole')->name('EditRole');
    // Route::post('/user-role/update/', 'MasterRolePermission\UserRoleController@updateRole');
    // //user modules
    // Route::get('/module/create', 'MasterRolePermission\ModuleController@create')->name('CreateModule');
    // Route::post('/module/add', 'MasterRolePermission\ModuleController@addModule');
    // Route::get('/module/viewmodules', 'MasterRolePermission\ModuleController@displayModules')->name('AllModule');
    // Route::get('/module/edit/{moduleID}', 'MasterRolePermission\ModuleController@editModule')->name('EditModule');
    // Route::post('/module/update', 'MasterRolePermission\ModuleController@updateModule');
    // Route::post('/module/modify', 'MasterRolePermission\ModuleController@edit');

    // //sub modules
    // Route::get('/sub-module/create', 'MasterRolePermission\SubModuleController@create')->name('createSubModule');
    // Route::post('/sub-module/add', 'MasterRolePermission\SubModuleController@addSubModule');
    // Route::get('/sub-module/view-sub-modules', 'MasterRolePermission\SubModuleController@displaySubModules')->name('AllSubModule');
    // Route::get('/sub-module/edit/{submoduleID}', 'MasterRolePermission\SubModuleController@editSubModule')->name('editSubModule');
    // Route::post('/sub-module/update', 'MasterRolePermission\SubModuleController@updateSubModule');
    // Route::post('/sub-module/delete', 'MasterRolePermission\SubModuleController@deleteSubModule');

    // Route::post('/module/setsession', 'MasterRolePermission\SubModuleController@sessionset');
    // Route::post('/submodule/modify/', 'MasterRolePermission\SubModuleController@edit');

    // //Assign Head of Dept. module
    // Route::get('/assign-module/head-of-dept', 'MasterRolePermission\SubModuleController@show');
    // Route::post('/assign-module/head-of-dept', 'MasterRolePermission\SubModuleController@assign')->name('Department.Head.Assign');
    // Route::patch('/assign-module/head-of-dept', 'MasterRolePermission\SubModuleController@updateHead')->name('Department.Head.Update');
    // Route::delete('/assign-module/head-of-dept/delete', 'MasterRolePermission\SubModuleController@deleteHead')->name('Department.Head.Delete');

    // //Assign modules
    // Route::get('/assign-module/create', 'MasterRolePermission\AssignModuleRoleController@create')->name('AssignModule');
    // Route::post('/role/setsession', 'MasterRolePermission\AssignModuleRoleController@sessionset');
    // Route::post('/assign-module/assign', 'MasterRolePermission\AssignModuleRoleController@assignSubModule');
    // Route::get('/assign-module/view-sub-modules', 'MasterRolePermission\AssignModuleRoleController@displaySubModules')->name('ViewAssignSubModule');
    // Route::get('/assign-module/edit/{submoduleID}', 'MasterRolePermission\AssignModuleRoleController@editSubModule')->name('EditAssignSubModule');
    // Route::post('/assign-module/update', 'MasterRolePermission\AssignModuleRoleController@updateSubModule');
    // //Assign Users
    // Route::get('/user-assign/create', 'MasterRolePermission\AssignUserRoleController@create')->name('AssignUser');
    // Route::get('/user-assign/edit/{id?}', 'MasterRolePermission\AssignUserRoleController@editUsreAssign')->name('editAssignUser');
    // Route::post('/user-assign/assign', 'MasterRolePermission\AssignUserRoleController@assignUser');
    // Route::post('/user/display', 'MasterRolePermission\AssignUserRoleController@displayUser');
    // Route::get('/user/search/{q?}', 'MasterRolePermission\AssignUserRoleController@autocomplete');



    //Salary Computation
    Route::get('/compute/{value}', 'ComputeController@loadView'); //for all computational view
    Route::post('/compute/computeAll', 'ComputeProcessorController@computeAll');
    Route::post('/compute/arrears', 'ComputeProcessorController@arrearsNew');
    Route::post('/compute/one-staff', 'ComputeProcessorController@oneStaff');
    Route::post('/compute/payment', 'ComputeProcessorController@payment');
    Route::post('/compute/suspension', 'ComputeProcessorController@suspension');
    Route::post('/compute/retirement', 'ComputeProcessorController@retirement');
    Route::post('/compute/overtime', 'ComputeProcessorController@overtime');
    Route::post('/compute/leave-grant', 'ComputeProcessorController@leaveGrant');

    Route::get('/computes/salary', 'PayrollController@ComputeSalary');
    Route::post('/computes/salary', 'PayrollController@ComputeSalary');

    Route::any('/revalidate/chart', 'PayrollController@ChartRevalidation');

    Route::get('/salary/structure-setup', 'PayrollController@SalaryStructure');
    Route::post('/salary/structure-setup', 'PayrollController@SalaryStructure');

    //compute All
    Route::post('/court/getActiveMonth', 'ComputeController@getActiveMonth');
    Route::post('/court/getDivisions', 'ComputeController@getDivisions');
    Route::post('/court/getStaff', 'ComputeController@getStaff');
    Route::post('/over-under-pay/compute', 'ComputeProcessorController@payment');

    //Payroll Report
    Route::get('/payrollReport/create', 'PayrollReportController@create');
    Route::post('/payrollReport/create', 'PayrollReportController@Retrieve');
    Route::post('/payrollReport/bulk-report', 'PayrollReportController@BulkPayRoll');
    Route::post('/payrollReport/getBank', 'PayrollReportController@getBank');
    Route::get('/payrollReport/arrears/{court}/{fileNo}/{year}/{month}', 'PayrollReportController@arrearsOearn');

    //consolited payroll report
    Route::post('/con-payrollReport/bulk-report', 'ConPayrollReportController@BulkPayRoll');
    Route::post('/con-payrollReport/getBank', 'ConPayrollReportController@getBank');
    // Route::get('/con-payrollReport/arrears/{court}/{fileNo}/{year}/{month}', 'ConPayrollReportController@arrearsOearn');
    // Route::get('/con-payrollReport/arrears-test/{court}/{fileNo}/{year}/{month}', 'ConPayrollReportController@arrearsOearnTest');

    Route::get('/payroll-breakdown/create', 'ConPayrollReportController@payrollBreakdown');
    Route::post('/payroll-breakdown/create', 'ConPayrollReportController@payrollBreakdownReport');

    Route::any('/new-payroll', 'ConPayrollReportController@newPayrollIndex');
    Route::post('/new-payroll', 'ConPayrollReportController@newPayrollReport');

    //Add Title
    Route::get('/title', 'AddTitleController@index');
    Route::get('/title/remove/{ID}/{title}', 'AddTitleController@destroy');
    Route::post('/title/update', 'AddTitleController@update');
    Route::post('/title/add', 'AddTitleController@store');

    //Payroll Summary
    // Route::get('/summary/create', 'SummaryController@create');
    // Route::post('/summary/create', 'SummaryController@retrieve');

    //Epayment
    Route::get('/epayment', 'EpaymentController@index');
    Route::post('/epayment/retrieve', 'EpaymentController@Retrieve');
    Route::get('/epayment/retrieve', 'EpaymentController@Retrieveget');
    Route::post('/epayment/fone', 'EpaymentController@getPhone');

    //consolidated Epayment
    Route::get('/con-epayment', 'ConEpaymentController@index');
    Route::post('/con-epayment/retrieve', 'ConEpaymentController@Retrieve');
    Route::get('/con-epayment/retrieve', 'ConEpaymentController@Retrieveget');

    Route::get('/generate/mandate', 'ConEpaymentController@indexNew');
    Route::post('/generate/mandate', 'ConEpaymentController@RetrieveNew');

    //Company Profile
    Route::get('/company-profile', 'CompanyProfileController@index');
    Route::post('/company-profile/update', 'CompanyProfileController@update');

    //Earning and Deduction
    Route::get('/earning-deduction', 'EarningAndDeductionController@index');
    Route::post('/earning-deduction/add', 'EarningAndDeductionController@store');
    Route::post('/earning-deduction/update', 'EarningAndDeductionController@update');

    //Control Variable
    Route::any('/report/nominal-grade-step', 'StaffReportController@NominalRollWithGradeStep');
    Route::get('/time-variables', 'TimeVariablesController@index');
    Route::post('/create-time-variables', 'TimeVariablesController@create');
    Route::post('/edit-time-variables', 'TimeVariablesController@edit');
    Route::post('/delete-time-variables', 'TimeVariablesController@delete');
    Route::post('/report/get-designation', 'StaffReportController@getDesignation');
    Route::post('/report/staff-list', 'StaffReportController@getStaffList');
    Route::get('/report/nominal', 'StaffReportController@NominalRollReport');
    Route::get('/report/staff-list-by-state-of-origin', 'StaffReportController@getStaffByStateofOrigin');
    Route::post('/report/nominal', 'StaffReportController@NominalRollReport');


    Route::get('/report/staff-status', 'StaffReportController@staffStatusReport');
    Route::post('/report/staff-status', 'StaffReportController@staffStatusReport');

    //staff Due For Arrears
    // Route::get('/staff-due/arrears/', 'DueForArrearsController@create');
    // Route::post('/staff-due/arrears/', 'DueForArrearsController@create');

    // Route::get('/staff-due/backlogs/', 'DueForArrearsController@Backlog');
    // Route::post('/staff-due/backlogs/', 'DueForArrearsController@Backlog');

    // Route::get('/staff-overdue/arrears/', 'DueForArrearsController@createOverdue');
    // Route::post('/staff-overdue/arrears/', 'DueForArrearsController@createOverdue');

    Route::post('/staff-due/store', 'DueForArrearsController@store');
    Route::post('/division/session', 'DueForArrearsController@divSession');
    // Route::any('/staff-due/all', 'DueForArrearsController@index');
    Route::get('/staff-due/delete/{id}', 'DueForArrearsController@destroy');

    Route::get('/staff-due/alert', 'DueForArrearsController@alert');
    Route::get('/staff-due/due', 'DueForArrearsController@dueForIncrement');
    Route::post('/staff-due/due', 'DueForArrearsController@dueForIncrement');

    Route::post('/increment/accept', 'DueForArrearsController@acceptIncrement');
    Route::get('/staff-due/edit/{id?}', 'DueForArrearsController@edit');

    //payslip
    // Route::get('/payslip/create', 'PaySlipController@create');
    // Route::post('/payslip/create', 'PaySlipController@Retrieve');

    //payslip Personal
    Route::get('/payslip/personal', 'PaySlipController@personal');
    Route::post('/payslip/personal', 'PaySlipController@getPersonal');

    // Staff Account Details
    Route::get('/account-info/add', 'StaffAccountDetailsController@index');
    Route::post('/account-info/add', 'StaffAccountDetailsController@store');
    Route::post('/account-info/court', 'StaffAccountDetailsController@courtSession');
    Route::get('/account-info/get-staff', 'StaffAccountDetailsController@getStaff');

    //MY STAFF DOCUMENTATION
    Route::get('/staff-documentation', 'StaffDocController@Index')->name('index');
    Route::post('/staff-documentation', 'StaffDocController@getStaffInfo');
    Route::get('/staff-documentation-basic-info', 'StaffDocController@getBasicInfo')->name('getBasicInfo');
    Route::post('/staff-documentation-basic-info', 'StaffDocController@submitBasicInfo');
    Route::get('/staff-documentation-marital-status', 'StaffDocController@getMarital')->name('getMarital');
    Route::post('/staff-documentation-marital-status', 'StaffDocController@submitMarital');
    Route::get('/staff-documentation-contact', 'StaffDocController@getContact')->name('getContact');
    Route::post('/staff-documentation-contact', 'StaffDocController@submitContact');
    Route::get('/staff-documentation-nextofkin', 'StaffDocController@getNextOfKin')->name('getNextOfKin');
    Route::post('/staff-documentation-nextofkin', 'StaffDocController@submitNextOfKin');
    Route::get('/staff-documentation-placeofbirth', 'StaffDocController@getPlaceOfBirth')->name('getPlaceOfBirth');
    Route::post('/staff-documentation-placeofbirth', 'StaffDocController@submitPlaceOfBirth');
    Route::post('/staff-documentation-getLga', 'StaffDocController@LGA');
    Route::get('/staff-documentation-account', 'StaffDocController@getAccount')->name('getAccount');
    Route::post('/staff-documentation-account', 'StaffDocController@submitAccount');

    Route::get('/staff-documentation-previous-employment', 'StaffDocController@getPrevEmployment')->name('getPrevEmployment');
    Route::post('/staff-documentation-previous-employment', 'StaffDocController@submitPrevEmployment');

    Route::get('/staff-documentation-attachment', 'StaffDocController@getAttachment')->name('getAttachment');
    Route::post('/staff-documentation-attachment', 'StaffDocController@submitAttachment');
    Route::post('/save-attachment', 'StaffDocController@saveAttachment');
    Route::get('delete-attachement/{id}', 'StaffDocController@deleteAttachement');

    Route::get('/staff-documentation-children', 'StaffDocController@getChildren')->name('getChildren');
    Route::post('/staff-documentation-children', 'StaffDocController@submitChildren');
    Route::get('/staff-documentation-others', 'StaffDocController@getOthers')->name('getOthers');
    Route::post('/staff-documentation-others', 'StaffDocController@submitOthers');
    Route::get('/staff-documentation-preview', 'StaffDocController@getPreview')->name('getPreview');
    Route::post('/staff-documentation-preview', 'StaffDocController@submitPreview');
    Route::get('/staff-documentation-complete', 'StaffDocController@getComplete')->name('getComplete');
    Route::post('/staff-documentation-complete', 'StaffDocController@submitComplete');
    Route::get('/get-designation', 'StaffDocController@loadDesignation'); //ajax

    //Salary rate function
    Route::get('/salary-rate', 'SalaryRateController@index');
    Route::post('/salary-rate', 'SalaryRateController@edit');

    //mail

    Route::get('/contact/mail', 'ContactController@show');
    Route::post('/contact/mail', 'ContactController@mailPost');

    //payroll:- consolidated Salary Scale
    Route::get('/consol/salaryScale', 'payroll\ConSalaryScaleController@index');
    Route::post('/consol/salaryScale', 'payroll\ConSalaryScaleController@getSalary');
    Route::get('consol/salaryScale/{type}/{court}', 'payroll\ConSalaryScaleController@customPaging');

    //payroll:- New Salary Scale
    Route::get('/new/salaryScale', 'NewSalaryStructureController@viewStructure');
    Route::get('/new/salaryScale/{type}', 'NewSalaryStructureController@customPaging');

    //PECARD
    Route::get('/pecard/view', 'PecardController@create');
    Route::post('/pecard/view', 'PecardController@viewCard');
    //PECARD CONSOLIDATED
    Route::get('/con-pecard/view', 'ConPecardController@create');
    Route::post('/con-pecard/view', 'ConPecardController@viewCard');
    Route::get('/con-pecard/getCard/{id?}/{year?}', 'ConPecardController@getPecard');

    //payroll Approval Process
    Route::get('/payroll/checking', 'ApprovalProcessController@checkingPayroll');
    Route::post('/payroll/checking', 'ApprovalProcessController@checkAndClear');

    Route::get('/payroll/audit', 'ApprovalProcessController@auditPayroll');
    Route::post('/payroll/audit', 'ApprovalProcessController@auditAndClear');

    Route::get('/payroll/ca', 'ApprovalProcessController@ca');
    Route::post('/payroll/ca', 'ApprovalProcessController@caProcess');

    Route::post('/payroll/recall', 'ApprovalProcessController@recall');

    Route::get('/payroll/dfa', 'ApprovalProcessController@DFA');
    Route::post('/payroll/dfa', 'ApprovalProcessController@DFAProcess');

    // Route::get('/payroll/es', 'ApprovalProcessController@es');
    // Route::post('/payroll/es', 'ApprovalProcessController@esProcess');

    Route::get('/payroll/cpo', 'ApprovalProcessController@cpoPayroll');
    Route::post('/payroll/cpo', 'ApprovalProcessController@cpoProcess');

    //consolidated payroll Approval Process
    // Route::get('/con-payroll/checking', 'ConApprovalProcessController@checkIndex');
    // Route::post('/con-payroll/checking', 'ConApprovalProcessController@checkingPayroll');
    // Route::post('/checking/clear', 'ConApprovalProcessController@checkAndClear');

    Route::get('/con-payroll/audit', 'ConApprovalProcessController@auditIndex');
    Route::post('/con-payroll/audit', 'ConApprovalProcessController@auditPayroll');
    Route::post('/audit/clear', 'ConApprovalProcessController@auditAndClear');

    Route::get('/con-payroll/ca', 'ConApprovalProcessController@ca');
    Route::post('/con-payroll/ca', 'ConApprovalProcessController@caProcess');

    Route::post('/con-payroll/recall', 'ConApprovalProcessController@recall');

    Route::get('/con-payroll/dfa', 'ConApprovalProcessController@DFA');
    Route::post('/con-payroll/dfa', 'ConApprovalProcessController@DFAProcess');

    // Route::get('/con-payroll/es', 'ConApprovalProcessController@es');
    // Route::post('/con-payroll/es', 'ConApprovalProcessController@esProcess');

    Route::get('/con-payroll/cpo', 'ConApprovalProcessController@cpoPayroll');
    Route::post('/con-payroll/cpo', 'ConApprovalProcessController@cpoProcess');

    // Route::post('basic/rank-designation', 'BasicParameterController@UpdateRankDesignation');
    // Route::get('basic/rank-designation', 'BasicParameterController@UpdateRankDesignation');

    //t209
    // Route::get('treasury209/view', 'Treasury209Controller@loadView');
    // Route::post('treasury209/view', 'Treasury209Controller@view');

    Route::get('treasuryTest/view', 'Treasury209Controller@load');
    Route::post('treasuryTest/view', 'Treasury209Controller@view');

    //tf1
    // Route::get('treasuryf1/view', 'TreasuryF1Controller@loadView');
    // Route::post('treasuryf1/view', 'TreasuryF1Controller@view');

    //payroll group by bank
    Route::get('groupby/banks-jacket', 'SummaryController@groupPayroll');
    Route::post('groupby/banks-jacket', 'SummaryController@groupPayrollDisplay');

    //Payroll Analysis
    // Route::get('payroll/analysis', 'AnalysisController@analysis');
    // Route::post('payroll/analysis', 'AnalysisController@analysisDisplay');

    Route::get('test/display', 'Treasury209Controller@test');

    //sammarise by bank
    Route::get('/summary/bybanks', 'SummaryController@summaryByBank');
    Route::post('/summary/bybanks', 'SummaryController@summaryPostBank');

    //signatory mandate route
    // Route::get('/user/signatory-mandate', 'SignatoryMandateController@displayMandateForm');
    // Route::post('/user/assign-mandate', 'SignatoryMandateController@assignMandate');

    //variation Contyroll
    Route::get('/variation-control/view', 'VariationControlController@index');
    Route::post('/variation-control/view', 'VariationControlController@load');

    //upload staff attachment; samju
    Route::get('/staff/attachment-upload', 'staffAttachmentController@displayForm');
    Route::post('attachment/save', 'staffAttachmentController@uploadAttachment');

    Route::get('/staff/attachment-upload/{id}', 'staffAttachmentController@displayRecordURL');

    //view-download staff attachment
    Route::get('/search', 'staffAttachmentController@search');
    Route::post('/find', 'staffAttachmentController@SearchStaff');
    Route::get('/live_search/action', 'staffAttachmentController@action')->name('live_search.action');

    Route::get('/staff/attachment-download', 'staffAttachmentController@searchindex');
    Route::get('/search/{searchQuerys?}', 'staffAttachmentController@StaffSearch');

    Route::get('/search/{searchQuery?}', 'staffAttachmentController@search');

    Route::get('/member/{id}', 'staffAttachmentController@viewmember');

    Route::post('/find', 'staffAttachmentController@find');
    Route::get('/attachment/{id}', 'staffAttachmentController@ViewDOC');
    Route::post('/attachment', 'staffAttachmentController@DeleteDOC');

    Route::any('/staff/backlog', 'StaffControlVariableController@backlogindex');

    Route::any('/staff/overtime_override', 'StaffControlVariableController@overrideOvertime');



    /// start tracing here up **************

    Route::get('/test/pe', 'PecardController@createIndex');
    Route::post('/personal-emolument/get-lga', [EmolumentController::class, 'getlga']);

    Route::get('/activeMonth/create', 'ActiveMonthController@create');
    Route::post('/activeMonth/create', 'ActiveMonthController@store');

    Route::get('/sotactiveMonth/create', 'ActiveMonthSOTController@create');
    Route::post('/sotactiveMonth/create', 'ActiveMonthSOTController@store');

    Route::post('/personal-emolument/division/staffs',       [EmolumentController::class, 'staffToDisplay']);
    Route::post('/collect/staff-detail', [EmolumentController::class, 'populateLGA']);
    Route::post('/collect/append', [EmolumentController::class, 'append']);

    Route::get('/bat/create', 'BatNoController@create');
    Route::post('/bat/create', 'BatNoController@store');
    Route::get('/bat/edit/{id?}', 'BatNoController@edit');
    Route::post('/bat/update', 'BatNoController@update');

    //council Members Bat
    Route::get('/council-bat/create', 'BatNoController@councilBatIndex');
    Route::post('/council-bat/create', 'BatNoController@councilBatSave');
    Route::get('/council-bat/edit/{id?}', 'BatNoController@councilBatEdit');
    Route::post('/council-bat/update', 'BatNoController@councilBatUpdate');

    //additlog search
    Route::get('/auditlog/search', 'AuditLogController@viewLog');
    Route::post('/auditlog/search', 'AuditLogController@searchLog');

    //designation Update
    Route::get('/staff/designation/update', 'EmolumentController@showDesignation');
    Route::post('/staff/designation/update', 'EmolumentController@updateDesignation');

    Route::get('/quarterly-allowance/create', 'QuarterlyAllowanceController@create');

    Route::post('/quarterly-allowance/create', 'QuarterlyAllowanceController@store');
    Route::post('/quarterly-allowance/get-data', 'QuarterlyAllowanceController@gradeAllowance');

    //Event type routing #G
    Route::get('/eventType/event', 'EventController@create');
    Route::post('/Type/save', 'EventController@storeEvent');
    Route::get('/eventType/editEvent', 'EventController@updateEvent');
    Route::post('Type/update/{id}', 'EventController@updateEvent');
    Route::get('eventType/delete-event/{id}', 'EventController@Destroy');

    //Event Application #Tola
    Route::get('/applyevent/create', 'ApplyEventController@create');
    Route::post('/applyevent/save', 'ApplyEventController@store');
    Route::get('/eventType/addEvent', 'ApplyEventController@create');
    Route::get('/delete/{id}/{id2}', 'ApplyEventController@CheckDelete');

    //additlog search
    Route::get('/auditlog/search', 'AuditLogController@viewLog');
    Route::post('/auditlog/search', 'AuditLogController@searchLog');

    //Retiremnt Alert

    Route::get('/show/staff-increment', [StaffIncrementFunctionController::class, 'showStaffDueForIncrement']);
    Route::get('/increment-notification/{id?}', [StaffIncrementFunctionController::class, 'showStaffDueForIncrement'])->name('listStaffIncrement');;
    Route::post('/search-increment', [StaffIncrementFunctionController::class, 'incrementSearch'])->name('searchIncrement');

    //peter variation flow module
    Route::get('/view-staff-variation-comment/{variationTempId}/{year}', [NewProcessVariationController::class, 'viewVariationComment']);

    Route::post('/process-increment-and-send', [NewProcessVariationController::class, 'saveRemark']);
    Route::get('/increment-sent-to-dda', [NewProcessVariationController::class, 'showStaffDueForIncrementDDA']);
    Route::post('/dda-approve-increment', [NewProcessVariationController::class, 'fowardStaffDueForInctoDA']);
    Route::post('/dda-decline-increment', [NewProcessVariationController::class, 'declineStaffDueForIncByDDA']);
    Route::get('/increment-sent-to-da', [NewProcessVariationController::class, 'showStaffDueForIncrementDA']);
    Route::post('/da-send-increment-to-audit', [NewProcessVariationController::class, 'approveStaffIncrementToAudit']);
    Route::post('/da-decline-increment', [NewProcessVariationController::class, 'declineStaffDueForIncByDA']);
    Route::get('/increment-sent-to-audit-head', [NewProcessVariationController::class, 'showStaffDueForIncrementAudit']);
    Route::post('/audit-send-increment-to-salary', [NewProcessVariationController::class, 'approveStaffIncrementToSalary']);
    Route::post('/audit-decline-increment', [NewProcessVariationController::class, 'declineStaffIncrementFromAudit']);
    Route::get('/increment-sent-to-salary-head', [NewProcessVariationController::class, 'showStaffDueForIncrementSalary']);
    Route::post('/salary-decline-increment', [NewProcessVariationController::class, 'declineStaffIncrementFromSalary']);

    // Route::get('/staff/all', [StaffInfoController::class, 'allStaffInfo']);
    Route::get('/hr/staff/documents/{id}', [StaffInfoController::class, 'getStaffDocuments'])->name('staff.documents');
    Route::get('/hr/staff/filter', [StaffInfoController::class, 'filterStaff'])->name('staff.filter');

    //staff due for arreas sent from HR ADMIN
    Route::get('/staff-due/arrears/approved-by-audit/{staffId}/{varTempID}',    [DueForArrearsController::class, 'createArreasApprovedByAudit'])->name('incrementApprovedByAudit');
    Route::post('/staff-due/arrears/approved-by-audit/{staffId}/{varTempID}',    [DueForArrearsController::class, 'createArreasApprovedByAudit']);

    //end peter variation flow module

    Route::get('/print/doc/{id?}', [AlertController::class, 'printDoc']);

    Route::get('/variation-order/approve/{id?}/{year?}', [ProcessVariationController::class, 'variationOrder']);
    Route::post('/save/arrears', 'AlertController@storeArrears');
    Route::get('/nsitf/view', 'NSITFController@index');
    Route::post('/nsitf/view', 'NSITFController@view');
    Route::post('/approve/arrears', [ProcessVariationController::class, 'approveArrears']);

    Route::post('/staff/details/get', [AlertController::class, 'variationRemark']);
    Route::post('/variation/approval/remark', [ProcessVariationController::class, 'saveRemark']);
    Route::get('/variation/approval/view-list', [ProcessVariationController::class, 'variationApproval']);
    Route::post('/variation/approval/push', [ProcessVariationController::class, 'push']);

    Route::post('/variation/approval/reverse', [ProcessVariationController::class, 'reverseRemark']);
    Route::post('/variation/approval/reject', [ProcessVariationController::class, 'reject']);
    Route::post('/variation/rejection/reason', [ProcessVariationController::class, 'rejectReason']);

    Route::get('/council-members/mandate', 'CouncilMembersController@index');
    Route::post('/council-members/mandate', 'CouncilMembersController@Retrieve');

    Route::get('/council-members/create', 'CouncilMembersController@createCouncilMember');
    Route::post('/council-members/create', 'CouncilMembersController@saveCouncilMember');
    Route::get('/edit/council-member/{id?}', 'CouncilMembersController@editCouncilMember');
    Route::post('/council-members/update', 'CouncilMembersController@updateCouncilMember');

    Route::get('/council-member/bank-schedule', 'CouncilMembersController@councilBankSchedule');
    Route::post('/council-member/bank-schedule', 'CouncilMembersController@postCouncilBankSchedule');

    Route::get('/council-members/analysis', 'CouncilMembersController@analysis');
    Route::post('/council-members/analysis', 'CouncilMembersController@viewAnalysis');

    Route::get('/council-members/payroll', 'CouncilMembersController@councilPayrollIndex');
    Route::post('/council-members/payroll', 'CouncilMembersController@councilPayrollReport');

    Route::get('/nhf/report', [NHFReportController::class, 'index']);
    Route::post('/nhf/report', [NHFReportController::class, 'retrieve']);
    Route::post('/nhf-report/export-excel', [NHFReportController::class, 'exportExcel'])->name('nhf.export.excel');

    //nhf staff list
    Route::get('/nhf-staff-list', [NHFReportController::class, 'staffList']);
    Route::get('update-staff-nhf-no', [NHFReportController::class, 'editStaffNhfNo']);
    Route::put('update-staff-nhf-no/{id}', [NHFReportController::class, 'updateStaffNhfNo']);

    //nhf staff deduction
    Route::get('nhf-staff-deduction', 'NHFReportController@nhfStaffDeduction');
    Route::get('nhf-staff-monthly-deduction', 'NHFReportController@staffMonthlyDeduction');
    Route::put('nhf-staff-monthly-deduction-update/{id}', 'NHFReportController@updateStaffMonthlyDeduction');



    Route::get('/variation-list', 'ProcessVariationController@variationList');
    Route::post('/variation/list', 'ProcessVariationController@saveVariation');
    Route::any('/con-payrollReport/compare-earning', 'ConPayrollReportController@CompareEarning');
    Route::any('/con-payrollReport/compare-pension', 'ConPayrollReportController@ComparePension');

    Route::any('/arrear-computation/create', 'PayrollArrearOnlyController@ArrearsComputation');

    //update union Dues
    Route::get('/update/union-dues', 'UpdateUnionController@updateUnion');

    //Current State
    Route::get('state/create-update-state', 'CurrentStateController@create')->name('createCurrentState');
    Route::post('state/create-add-new-state', 'CurrentStateController@store')->name('postCurrentState');
    Route::post('state/update-state', 'CurrentStateController@update')->name('updateCurrentState');
    Route::get('state/update-state', 'CurrentStateController@create');
    Route::get('state/delete-state/{id?}', 'CurrentStateController@destroy')->name('deleteState');

    // Salary Approval process
    Route::get('/approval-process', 'SalaryApprovalProcessController@salaryPush');
    Route::post('/approval-process', 'SalaryApprovalProcessController@process');
    Route::post('/process-to-variation', 'SalaryApprovalProcessController@processToVariationControl');
    Route::post('/rejection', 'SalaryApprovalProcessController@rejection');

    // Salary Approval process
    /*Route::get('/main-payroll',              'SalaryApprovalProcessController@create');
    Route::post('/main-payroll',             'SalaryApprovalProcessController@Retrieve');*/
    Route::get('/main-payroll', 'SalaryApprovalProcessController@payroll');
    Route::post('/main-payroll', 'SalaryApprovalProcessController@payrollReport');
    Route::get('payroll-analysis', 'SalaryApprovalProcessController@analysis');
    Route::post('payroll-analysis', 'SalaryApprovalProcessController@analysisDisplay');

    Route::get('/main-payroll/report', 'SalaryApprovalProcessController@payroll');
    Route::post('/main-payroll/report', 'SalaryApprovalProcessController@payrollReport');

    Route::get('/payroll-summary', 'SalaryApprovalProcessController@payrollSummary');
    Route::post('/payroll-summary', 'SalaryApprovalProcessController@viewPayrollSummary');
    Route::get('/view-pecard', 'SalaryApprovalProcessController@checkCard');
    Route::post('/view-pecard', 'SalaryApprovalProcessController@viewCard');

    Route::get('treasury209-view', 'SalaryApprovalProcessController@loadView');
    Route::post('treasury209-view', 'SalaryApprovalProcessController@view');
    Route::get('/display/minutes/{year}/{month}', 'SalaryApprovalProcessController@displayComments');

    //bank schedule
    Route::get('/bankshedule/view', 'SalaryApprovalProcessController@bankSchedule');
    Route::post('/bankshedule/view', 'SalaryApprovalProcessController@postBankSchedule');

    //Mandate Approval
    Route::get('/salary/view', 'SalaryMandateApprovalController@salaryAction');
    Route::post('/salary/view', 'SalaryMandateApprovalController@salaryHeadComment');

    Route::get('/mandate/view', 'SalaryMandateApprovalController@mandateView');
    Route::post('/mandate/view', 'SalaryMandateApprovalController@mandateComment');

    Route::get('/council-members/payroll-approval', 'SalaryApprovalProcessController@councilPayrollIndex');
    Route::post('/council-members/payroll-approval', 'SalaryApprovalProcessController@councilPayrollReport');

    Route::get('/council-members/mandate-approval', 'SalaryApprovalProcessController@councilMandateIndex');
    Route::post('/council-members/mandate-approval', 'SalaryApprovalProcessController@councilMandateRetrieve');

    Route::get('/council-member/bank-schedule-approval', 'SalaryApprovalProcessController@councilBankSchedule');
    Route::post('/council-member/bank-schedule-approval', 'SalaryApprovalProcessController@postCouncilBankSchedule');

    Route::get('/council-members/analysis-approval', 'SalaryApprovalProcessController@councilAnalysis');
    Route::post('/council-members/analysis-approval', 'SalaryApprovalProcessController@viewCouncilAnalysis');

    Route::any('/approval-report', 'SalaryApprovalProcessController@report');

    Route::get('/mandate/{year}/{month}', 'SalaryMandateApprovalController@mandate');

    Route::get('/display/comments/{year}/{month}', 'SalaryMandateApprovalController@displayComments');

    Route::get('/es/view', 'SalaryMandateApprovalController@ESView');
    Route::post('/es/view', 'SalaryMandateApprovalController@ESComment');

    //cover letter
    // Route::get('coverletter/print', 'CoverletterController@index');
    // Route::post('coverletter/print', 'CoverletterController@view');

    //council
    Route::get('councilletter/print', 'CoverletterController@council');
    Route::post('councilletter/print', 'CoverletterController@councilview');

    //deduction percentage
    Route::get('percentage/add', 'DeductionPercentageController@index');
    Route::post('percentage/add', 'DeductionPercentageController@add')->name('percentage-add');

    // Salary Approval process Testing

    Route::get('/approval-process-test', 'SalaryApprovalProcessControllerTest@salaryPush');
    Route::post('/approval-process-test', 'SalaryApprovalProcessControllerTest@process');
    Route::post('/process-to-variation-test', 'SalaryApprovalProcessControllerTest@processToVariationControl');
    Route::post('/rejection-test', 'SalaryApprovalProcessControllerTest@rejection');

    Route::get('/main-payroll-test', 'SalaryApprovalProcessControllerTest@create');
    Route::post('/main-payroll-test', 'SalaryApprovalProcessControllerTest@Retrieve');
    Route::get('payroll-analysis-test', 'SalaryApprovalProcessControllerTest@analysis');
    Route::post('payroll-analysis-test', 'SalaryApprovalProcessControllerTest@analysisDisplay');

    Route::get('/payroll-summary-test', 'SalaryApprovalProcessControllerTest@payrollSummary');
    Route::post('/payroll-summary-test', 'SalaryApprovalProcessControllerTest@viewPayrollSummary');
    Route::get('/view-pecard-test', 'SalaryApprovalProcessControllerTest@checkCard');
    Route::post('/view-pecard-test', 'SalaryApprovalProcessControllerTest@viewCard');

    Route::get('treasury209-view-test', 'SalaryApprovalProcessControllerTest@loadView');
    Route::post('treasury209-view-test', 'SalaryApprovalProcessControllerTest@view');
    Route::get('/display/minutes-test/{year}/{month}', 'SalaryApprovalProcessControllerTest@displayComments');

    //bank schedule
    Route::get('/bankshedule/view-test', 'SalaryApprovalProcessControllerTest@bankSchedule');
    Route::post('/bankshedule/view-test', 'SalaryApprovalProcessControllerTest@postBankSchedule');

    //Mandate Approval
    Route::get('/salary/view-test', 'SalaryMandateApprovalControllerTest@salaryAction');
    Route::post('/salary/view-test', 'SalaryMandateApprovalControllerTest@salaryHeadComment');

    Route::get('/mandate/view-test', 'SalaryMandateApprovalControllerTest@mandateView');
    Route::post('/mandate/view-test', 'SalaryMandateApprovalControllerTest@mandateComment');

    Route::get('/mandate-test/{year}/{month}', 'SalaryMandateApprovalControllerTest@mandate');

    Route::get('/display/comments-test/{year}/{month}', 'SalaryMandateApprovalControllerTest@displayComments');

    Route::get('/es/view-test', 'SalaryMandateApprovalControllerTest@ESView');
    Route::post('/es/view-test', 'SalaryMandateApprovalControllerTest@ESComment');

    //staff designation
    // Route::get('staff/designation', 'StaffDesignationController@displayForm');
    // Route::post('user/assign-designation', 'StaffDesignationController@assignDesignation');
    // Route::get('user/delete/{id?}', 'StaffDesignationController@deleteDesignation');

    Route::get('/assign/user', 'AuditUnitController@assignStaff');
    Route::post('/assign/user', 'AuditUnitController@auditingU');
    Route::get('/audit/assigned-records', 'AuditUnitController@assignRecords');
    Route::post('/audit/confirmation', 'AuditUnitController@confirm');
    Route::any('/salary-audit/report', 'AuditUnitController@report');

    Route::any('user-management', 'BasicParameterController@Usermanagement');
    Route::any('users-management', 'BasicParameterController@Usermanagement');

    Route::any('/api-test', 'APITestController@test');

    //Staff - Submit Roaster Application
    Route::get('leave-roaster', 'LeaveRoasterController@createLeaveRoaster')->name('createLeaveRoaster');
    Route::post('leave-roaster', 'LeaveRoasterController@storeLeaveRoaster')->name('storeLeaveRoaster');
    Route::get('submit-application/{roasterID?}', 'LeaveRoasterController@submitLeaveRoasterApplication')->name('submitApplication');
    Route::post('submit-application', 'LeaveRoasterController@updateLeaveRoasterApplication')->name('updateApplication');
    Route::get('delete-application/{roastID}', 'LeaveRoasterController@deleteLeaveRoasterApplication')->name('deleteRoasterApplication');

    //Add Year Holidays

    Route::get('/holidays',        [HolidayController::class, 'index']);
    Route::get('/fetch-holidays',    [HolidayController::class, 'fetchHolidays']);
    Route::get('/edit-holiday/{id}',      [HolidayController::class, 'edit']);
    Route::post('/add-holiday',    [HolidayController::class, 'store']);
    Route::put('/edit-holiday/{id}',     [HolidayController::class, 'update']);
    Route::get('/delete-holiday',   [HolidayController::class, 'delete'])->name('holiday.delete');

    //leave Administrator to Update staff leave
    Route::get('/leave-administration', 'LeaveAdministratorController@index2');
    Route::get('/fetch-staff-l-application', 'LeaveAdministratorController@fetchStaffLeave2');
    Route::post('/fetch-staff-l-application', 'LeaveAdministratorController@updateStaffLeave')->name('updateStaffLeaveApplication');
    Route::get('/delete-staff-l-application/{roastID}', 'LeaveAdministratorController@deleteStaffLeaveRoasterApplication')->name('deleteStaffRoasterApplication');

    //Staff - Submit Roaster Application
    Route::get('leave-roaster', 'LeaveRoasterController@createLeaveRoaster')->name('createLeaveRoaster');
    Route::post('leave-roaster', 'LeaveRoasterController@storeLeaveRoaster')->name('storeLeaveRoaster');
    Route::get('submit-application/{roasterID?}', 'LeaveRoasterController@submitLeaveRoasterApplication')->name('submitApplication');
    Route::post('submit-application', 'LeaveRoasterController@updateLeaveRoasterApplication')->name('updateApplication');
    Route::get('delete-application/{roastID}', 'LeaveRoasterController@deleteLeaveRoasterApplication')->name('deleteRoasterApplication');
    Route::any('leave-roaster-proposed-report', 'LeaveRoasterController@leaveRoasterReport')->name('leaveRoasterReport');

    //Leave Report Certificate
    Route::get('leave-certificate/{id?}', 'LeaveFormCertificateController@generateLeaveCertificate')->name('leaveCertificate');

    //Staff - Resumption Application
    Route::get('leave-resumption-application', 'LeaveResumptionController@create')->name('create');
    Route::post('leave-resumption-application', 'LeaveResumptionController@store')->name('store');
    Route::post('update-resumption-application', 'LeaveResumptionController@update')->name('update');
    Route::get('submit-leave-resumption/{id?}', 'LeaveResumptionController@submitLeaveResumptionForm')->name('submiteResumption');
    Route::get('resumption-report/{id?}', 'LeaveResumptionController@viewReport')->name('resumptionReport');
    Route::get('delete-resumption-application/{roastID}', 'LeaveResumptionController@delete')->name('deleteResumption');

    //leave application
    Route::any('/leave-application', 'NewLeaveController@LeaveApplication');
    Route::get('/check-roaster', 'NewLeaveController@checkRoaster');
    Route::get('/push-to-hod/{id}', 'NewLeaveController@pushHod');
    Route::post('/cal-numofDays', 'NewLeaveController@getNumberOfDays');

    //create memo
    Route::get('/create-memo/{id}', 'NewLeaveController@memo');
    Route::post('/save-memo', 'NewLeaveController@saveMemo')->name('saveMemo');
    Route::get('/print-memo/{id}', 'NewLeaveController@printMemo');

    //leave alert
    Route::get('/leave-alert', 'NewLeaveController@LeaveAlert');
    Route::get('/leave-alert-setting', 'NewLeaveController@LeaveAlertSettings');
    Route::post('/leave-alert-setting', 'NewLeaveController@LeaveAlertSettingsSave')->name('savePeriod');

    //delete record
    Route::get('{url}/{urlx}/{id}/{id2}', [ProfileController::class, 'deleteFunction']);

    //Leave Approval - Tola
    Route::get('/leave-approval-staff', 'LeaveApprovalController@LeaveApproval');
    Route::post('/save-leave-approval-staff', 'LeaveApprovalController@saveApproval')->name('saveLeaveApproval');
    Route::any('/edit-Approval-status/{id?}', 'LeaveApprovalController@editApproval')->name('updateLeaveApproval');
    Route::any('/delete-Approval-status/{id}', 'LeaveApprovalController@delete');

    //Volume  -Tola
    Route::get('/volume', 'volumeController@index');
    Route::post('/save-volume', 'volumeController@saveVolume')->name('saveVolume');
    Route::any('/edit-volume/{id?}', 'volumeController@editVolume')->name('editVolume');
    Route::post('/delete-volume/{id}', 'volumeController@delete');

    //fileCategory -Tola
    Route::get('/fileCategory', 'fileCategory@index');
    Route::post('/save-fileCategory', 'fileCategory@savefileCategory')->name('save-fileCategory');
    Route::any('/edit-fileCategory/{id?}', 'fileCategory@editfileCategory')->name('edit-fileCategory');
    Route::delete('/delete-fileCategory/{id}', 'fileCategory@deletefileCategory');

    //NYSC    -Tola
    Route::get('/nysc', 'nyscController@index')->name('viewNysc');
    Route::post('/nysc-save', 'nyscController@save');

    //Route::any('/nysc-edit/{id}', 'nyscController@editnysc')->name('editNysc');
    Route::get('/nysc-edit/{id}', 'nyscController@editnysc')->name('editNysc');
    Route::post('/nysc-update', 'nyscController@updatenysc');

    Route::delete('nysc-delete/{id}', 'nyscController@remove')->name('nysc.delete');

    //IT controller
    //Route::get('/IT',     'ITController@index');
    //Route::post('/IT-save',     'ITController@save');
    // Route::any('/IT-edit/{id}', 'ITController@editIT')->name('editIT');
    //Route::delete('IT-delete/{id}', 'ITController@remove');

    // Dicipline
    Route::get('/discipline', 'StaffDisciplineController@index')->name('discipline');
    Route::post('/discipline-save', 'StaffDisciplineController@store');
    Route::get('/discipline-edit/{id}', 'StaffDisciplineController@edit');
    Route::get('/discipline/staff-search', 'StaffDisciplineController@autocomplete')->name('staff.search');
    Route::post('/discipline-update', 'StaffDisciplineController@updatediscipline');
    Route::delete('discipline-delete/{id}', 'StaffDisciplineController@destroy')->name('discipline.delete');

    //start peter routes
    //Staff documentation
    Route::get('/candidate',              [DocumentationController::class, 'candidate']);
    Route::post('/candidate',              [DocumentationController::class, 'candidateSearch']);
    Route::get('/documentation/{id}',     [DocumentationController::class, 'documentStaff']);
    Route::get('/continue-staff-documentation/{id}',     [DocumentationController::class, 'constinueStaffDocumentation']);

    Route::post('/start-documentation',            [DocumentationController::class, 'Index'])->name('index');

    Route::post('/start-documentationx',            [DocumentationController::class, 'getStaffInfo']);
    Route::get('/documentation-basic-info',            [DocumentationController::class, 'getBasicInfo'])->name('getBasicInfo');
    Route::post('/documentation-basic-info',            [DocumentationController::class, 'getBasicInfo'])->name('getBasicInfo');
    Route::post('/documentation-basic-infox',            [DocumentationController::class, 'submitBasicInfo']);
    Route::get('/documentation-marital-status',            [DocumentationController::class, 'getMarital'])->name('getMarital');
    Route::post('/documentation-marital-status',            [DocumentationController::class, 'submitMarital']);
    Route::get('/documentation-contact',            [DocumentationController::class, 'getContact'])->name('getContact');
    Route::post('/documentation-contact',            [DocumentationController::class, 'submitContact']);
    Route::get('/documentation-nextofkin',            [DocumentationController::class, 'getNextOfKin'])->name('getNextOfKin');
    Route::post('/documentation-nextofkin',            [DocumentationController::class, 'submitNextOfKin']);
    Route::get('/documentation-placeofbirth',            [DocumentationController::class, 'getPlaceOfBirth'])->name('getPlaceOfBirth');
    Route::post('/documentation-placeofbirth',            [DocumentationController::class, 'submitPlaceOfBirth']);
    Route::post('/documentation-getLga',            [DocumentationController::class, 'LGA']);
    Route::get('/documentation-education',             [DocumentationController::class, 'getEducation'])->name('getEducation');
    Route::post('/documentation-education',            [DocumentationController::class, 'submitEducation']);
    Route::post('/save-document',                      [DocumentationController::class, 'saveDocument']);
    Route::get('/delete-document/{id}',               [DocumentationController::class, 'deleteDocument']);

    Route::get('/documentation-account',            [DocumentationController::class, 'getAccount'])->name('getAccount');
    Route::post('/documentation-account',            [DocumentationController::class, 'submitAccount']);

    Route::get('/documentation-previous-employment',            [DocumentationController::class, 'getPrevEmployment'])->name('getPrevEmployment');
    Route::post('/documentation-previous-employment',            [DocumentationController::class, 'submitPrevEmployment']);

    Route::get('/documentation-attachment', [DocumentationController::class, 'getAttachment'])->name('getAttachment');
    Route::post('/documentation-attachment', [DocumentationController::class, 'submitAttachment']);
    Route::post('/save-attachment', [DocumentationController::class, 'saveAttachment']);

    Route::get('delete-attachement/{id}', [DocumentationController::class, 'deleteAttachement']);

    Route::get('/documentation-children',            [DocumentationController::class, 'getChildren'])->name('getChildren');
    Route::post('/documentation-children',            [DocumentationController::class, 'submitChildren']);
    Route::get('/documentation-passport-signature',            [DocumentationController::class, 'getPassportSignature'])->name('getPassportSignature');
    Route::post('/documentation-passport-signature',            [DocumentationController::class, 'submitPassportSignature']);
    Route::get('/documentation-others',            [DocumentationController::class, 'getOthers'])->name('getOthers');
    Route::post('/documentation-others',            [DocumentationController::class, 'submitOthers']);
    Route::get('/documentation-preview',            [DocumentationController::class, 'getPreview'])->name('getPreview');
    Route::post('/documentation-preview',            [DocumentationController::class, 'submitPreview']);
    Route::get('/documentation-complete',            [DocumentationController::class, 'getComplete'])->name('getComplete');
    Route::post('/documentation-complete',            [DocumentationController::class, 'submitComplete']);
    Route::get('/get-designation',                              [DocumentationController::class, 'loadDesignation']); //ajax
    //end peter routes
    //file upload
    Route::get('/document-file-upload', 'FileDocumentController@create');
    Route::post('/document-file-upload', 'FileDocumentController@saveFile');
    Route::get('/search-file', 'FileDocumentController@searchFile');
    Route::get('/search-file/{id}', 'FileDocumentController@getSearchedFile')->where('id', '[A-Za-z0-9\s_/-]+');
    Route::get('/edit-document/{id}', 'FileDocumentController@editDocument');
    Route::put('/edit-document/{id}', 'FileDocumentController@updateDocument');
    Route::delete('/remove-document/{id}', 'FileDocumentController@removeDocument');
    Route::delete('/rm-document/{id}', 'FileDocumentController@rmDocument');
    Route::get('/document-delete-file/{id}', 'FileDocumentController@delete');
    Route::get('/select-user-document-file-upload', 'FileDocumentController@create');
    Route::post('/select-user-document-file-upload', 'FileDocumentController@getUserFileUploaded');

    //Close volume
    Route::get('/open-registry-close-volume', 'CloseVolumeFileController@create');
    Route::post('/open-registry-close-volume', 'CloseVolumeFileController@save');
    Route::post('/file-volume', 'CloseVolumeFileController@getVolumeForFile');


    // -------------------------------- adams start ------------------------------------------------------- //
    //Interview score sheet
    Route::get('/interview-score-sheet', [InterviewScoreSheetController::class, 'create']);
    Route::post('/interview-score-sheet', [InterviewScoreSheetController::class, 'save']);
    Route::post('/interview-push-score', [InterviewScoreSheetController::class, 'pushNext']);
    Route::post('/get-candidate-for-interview', [InterviewScoreSheetController::class, 'getCandidateInterview']);
    Route::get('/interview-score-sheet/approval', [InterviewScoreSheetController::class, 'secretaryFinalApproval']);
    Route::get('/interview-score-sheet/admin-approval', [InterviewScoreSheetController::class, 'adminFinalApproval']);
    Route::post('/interview-score-sheet/admin-approval', [InterviewScoreSheetController::class, 'pushToSecretaryFromAdmin']);
    // Reject a single candidate
    Route::post('/reject-candidate/{id}', [InterviewScoreSheetController::class, 'rejectCandidate'])->name('reject.candidate');

    // Reject all candidates
    Route::post('/reject-selected-candidates', [InterviewScoreSheetController::class, 'rejectSelectedCandidates'])->name('reject.selected.candidates');

    Route::post('/interview-push-approved-score', [InterviewScoreSheetController::class, 'pushBack']);
    Route::post('/interview-approval-candidate', [InterviewScoreSheetController::class, 'approveCandidate']);
    Route::post('/interview-revert-candidate', [InterviewScoreSheetController::class, 'revertApprovedCandidate']);
    Route::get('/candidate-shortlisted', [InterviewScoreSheetController::class, 'shortlistedReport']);
    Route::get('/candidate-appointment-letter', [InterviewScoreSheetController::class, 'candidateAppointmentLetter']);
    Route::get('/push-candidate-to-registry/{id?}', [InterviewScoreSheetController::class, 'pushCandidateToRegistry']);

    //delete score sheet
    Route::get('/delete-score-sheet/{id}', [InterviewScoreSheetController::class, 'deleteScoreSheet'])->name('Score.delete');

    //edit score sheet
    Route::get('/edit-score-sheet/{id}', [InterviewScoreSheetController::class, 'editScoreSheet']);

    //update score sheet
    Route::post('/update-score-sheet', [InterviewScoreSheetController::class, 'updateScoreSheet']);

    //interview
    Route::get('/interview', [CandidateController::class, 'interview']);
    Route::post('/interview', [CandidateController::class, 'saveInterview'])->name('saveInterview');
    Route::get('/candidates/interview/{id}', [CandidateController::class, 'showCandidates'])->name('candidates.interview');
    Route::get('/view-interview-and-edit/{id}',                     [CandidateController::class, 'showInterviewAndEdit'])->name('viewInterviewAndEdit');


    Route::put('/view-interview-and-edit/{id}',                     [CandidateController::class, 'updateInterview'])->name('updateInterview');
    Route::get('/delete-candidate-interview/{id}',       [CandidateController::class, 'deleteCandidateInterview']);
    Route::get('/delete-interview-document/{id}', [CandidateController::class, 'deleteInterviewDocument']);
    Route::get('/add-candidates/{id}',                      [CandidateController::class, 'candidateShorlisted'])->name('Candidate.add');
    Route::post('/shortlisted',                              [CandidateController::class, 'saveCandidateShorlisted'])->name('saveCandidateShorlisted');
    Route::get('/get-lga-from-state',                        [CandidateController::class, 'loadLGA']);
    Route::post('/delete-candidates',                     [CandidateController::class, 'deleteCandidate'])->name('candidate.delete');
    Route::get('/edit-candidates/{id}',                     [CandidateController::class, 'editCandidate']);
    Route::post('/update-shortlisted',                     [CandidateController::class, 'updateCandidateShorlisted'])->name('updateCandidateShorlisted');

    Route::get('/admin-add-new-staff', [CandidateController::class, 'adminAddNewStaff']);
    Route::post('/admin-add-new-staff', [CandidateController::class, 'adminSaveNewStaff'])->name('adminSaveNewStaff');

    //fetch ajax disgnation
    Route::get('/get-designations/{deptID}', [CandidateController::class, 'getDesignations']);
    Route::get('/ajax/get-units/{deptID}', [CandidateController::class, 'getUnits']);

    Route::get('/close-names-entering/{id}',                [CandidateController::class, 'closeCandidate']);
    Route::get('/open-names-entering/{id}',                  [CandidateController::class, 'openCandidate']);

    Route::get('/close-interview/{id}',                  [CandidateController::class, 'closeInterview']);
    Route::get('/open-interview/{id}',                  [CandidateController::class, 'openInterview']);

    Route::post('/promotion/shortlist',                    [EstabAdminController::class, 'promotionShortlist']);
    Route::get('/promotion/shortlisted-staff',             [EstabAdminController::class, 'promotionShortlistedStaff']);
    Route::get('/promotion/shortlisted-staff-da',             [EstabAdminController::class, 'promotionShortlistedStaffDA']);
    Route::get('/promotion/shortlisted-staff-cr',             [EstabAdminController::class, 'promotionShortlistedStaffCR']);
    // Fetch all comments for a given staff promotion
    Route::get('/promotion/comments/{staffID}', [EstabAdminController::class, 'getComments']);


    Route::post('/promotion/shortlisted-staff',            [EstabAdminController::class, 'processShortlisted']);
    Route::post('/promotion/shortlist-reversal',           [EstabAdminController::class, 'reversal']);
    Route::post('/promotion/approval',                    [EstabAdminController::class, 'promotionApproval']);
    Route::post('/promotion/approval/reversal',                    [EstabAdminController::class, 'promotionApprovalReversal']);
    Route::get('/promotion-exercise/scores',             [EstabAdminController::class, 'promotionScores']);

    Route::post('/confirm/promotion', [EstabAdminController::class, 'confirmPromotion']);
    Route::get('/promoted/staff', [EstabAdminController::class, 'promotedStaff']);


    Route::get('/admin/promotion-arrears/entry', [AlertController::class, 'promotionArrearsEntry']);
    Route::post('/admin/promotion-arrears/entry', [AlertController::class, 'saveEntry']);
    Route::post('/create/session', [AlertController::class, 'createSes']);



    //Promotion Variation 15-02-2022
    Route::get('/promoted/staff-variation', [AlertController::class, 'promotedStaff']);
    Route::post('/promotion-variation/details', [AlertController::class, 'savePromotionVariationDetails']);
    Route::get('/promoted/staff-variation-advice', [AlertController::class, 'promotionVariationAdvice']);
    Route::post('/promotion-variation/moveto', [AlertController::class, 'promotionNextStage']);
    Route::post('/promotion-variation/reversal', [AlertController::class, 'reverse']);

    //estab Admin Controller

    Route::get('/estab/central-list', [EstabAdminController::class, 'view_CENTRAL_LIST']);
    Route::get('/estab/test', [EstabAdminController::class, 'test']);
    Route::get('/estab/staff/profile/{fileNo}', [EstabAdminController::class, 'getProfile']);
    Route::get('/admin/promotion-brief/', [EstabAdminController::class, 'promotion']);
    Route::get('/admin/upgrading/{fileNo}', [EstabAdminController::class, 'upgrade']);
    Route::get('/admin/conversion/{fileNo}', [EstabAdminController::class, 'convert_advance']);
    Route::get('/estab/conversion', [EstabAdminController::class, 'conversionList']);
    Route::post('/estab/upgrading/update', [EstabAdminController::class, 'upgradeDetails']);
    Route::post('/estab/autocomplete', [EstabAdminController::class, 'autocomplete']);
    Route::post('/estab/listStaff', [EstabAdminController::class, 'showAll']);
    Route::post('/estab/con-adv/save', [EstabAdminController::class, 'saveAdvancement']);
    Route::get('/estab/promotion-list/{id?}', [EstabAdminController::class, 'promotionList'])->name('listStaffPromotion');
    Route::post('/estab/promotion/save', [EstabAdminController::class, 'savePromotion']);
    Route::post('/estab/promotion/confirmation', [EstabAdminController::class, 'confirm']);


    // Junior promotion
    Route::get('/estab/promotion/junior', [EstabAdminController::class, 'promotionListJunior'])->name('promotion.junior');
    Route::post('/staff-promotion-search-junior', [EstabAdminController::class, 'promotionSearchJunior'])->name('promotionSearchJunior');

    // Senior promotion
    Route::get('/estab/promotion/senior', [EstabAdminController::class, 'promotionListSenior'])->name('promotion.senior');
    Route::post('/staff-promotion-search-senior', [EstabAdminController::class, 'promotionSearchSenior'])->name('promotionSearchSenior');

    Route::get('/forpromotion-confirmation', [forPromotionsController::class, 'forPromotionConfirmation']);


    Route::get('/forpromotion/{id?}', [forPromotionsController::class, 'forPromotion']);
    Route::post('/forpromotion', [forPromotionsController::class, 'savePromotion'])->name('saveForPromotion');
    Route::post('/updateforpromotion', [forPromotionsController::class, 'saveUpdatePromotion'])->name('saveUpdateForPromotion');
    Route::post('/save-view-promotion', [forPromotionsController::class, 'saveViewPromotion'])->name('saveViewPromotion');
    Route::get('/viewpromotion/{id?}', [forPromotionsController::class, 'viewPromotion'])->name('viewPromotion');

    Route::get('/promotion/brief/{id}', [PromotionBriefController::class, 'promotionBrief']);
    Route::post('/promotion-search', [EstabAdminController::class, 'promotionSearch'])->name('searchPromotion');

    Route::post('/estab/conversion/confirmation', [EstabAdminController::class, 'promotionConfirm']);
    Route::post('/estab/profile/details', [EstabAdminController::class, 'getDetails']);
    Route::get('/staff-promotion', [EstabAdminController::class, 'promotionAlert']);


    Route::get('/cr-candidates/upload', [CandidateController::class, 'specialCandidateShorlisted'])->name('cr.upload.page');
    Route::post('/cr-candidates/import', [CandidateController::class, 'specialCandidateShorlistedImport'])->name('cr.import');
    Route::post('/delete-cr-candidates',                     [CandidateController::class, 'deleteCrCandidate'])->name('cr-candidate.delete');
    Route::post('/edit-cr-candidates',                     [CandidateController::class, 'editCrCandidate'])->name('cr-candidate.edit');
    Route::post('/cr-candidate/bulk-approve', [CandidateController::class, 'bulkApprove'])->name('cr-candidate.bulk-approve');


    //nhis routes
    Route::get('/nhis-balance/create', [NHISController::class, 'create']);
    Route::post('/nhis-balance/create', [NHISController::class, 'store']);
    Route::get('/nhis-balance/edit/{id?}', [NHISController::class, 'edit']);
    Route::post('/nhis-balance/edit', [NHISController::class, 'update'])->name('nhisBalance.edit');

    Route::get('/nhis-account/create', [NHISController::class, 'createAccount']);
    Route::post('/nhis-account/create', [NHISController::class, 'storeAccount']);

    Route::get('/nhis/deduction', [NHISController::class, 'deduction']);
    Route::post('/nhis/deduction', [NHISController::class, 'viewDeduction']);


    //Staff NHIS  -Tola
    Route::get('/staff-nhis', [staffNhisController::class, 'index'])->name('staff-nhis');

    Route::get('/staff-nhis-child/{id}', [staffNhisController::class, 'child'])->name('nhis.staff.child');
    Route::post('/staff-nhis-add', [staffNhisController::class, 'addchild'])->name('addChild');
    Route::any('/staff-nhis-delete/{id}', [staffNhisController::class, 'deleteChild'])->name('addChild');

    // Hospital
    Route::get('/hospital', [HospitalController::class, 'index'])->name('hospital');
    Route::post('/hospital-save', [HospitalController::class, 'store']);
    Route::get('/hospital-edit/{id}', [HospitalController::class, 'edit']);
    Route::post('/hospital-update', [HospitalController::class, 'updatehospital']);
    Route::post('/assign-hospital', [HospitalController::class, 'assign']);
    Route::delete('hospital-delete', [HospitalController::class, 'destroy']);
    // routes/web.php
    Route::get('/get-hospitals/{category_id}', [HospitalController::class, 'getHospitals']);

    //staff-promotion
    Route::get('/add-promotion', [StaffPromotionController::class, 'create']);
    Route::post('/add-promotion', [StaffPromotionController::class, 'savePromotion'])->name('promotionCreate');
    Route::delete('/delete-record', [StaffPromotionController::class, 'delete'])->name('position.delete');

    // -------------------------------- adams stop ------------------------------------------------------- //
















    //archives
    Route::get('/push-archives', 'DocumentArchivesController@archives');
    Route::post('/push-archives', 'DocumentArchivesController@postArchives');
    Route::get('/view-archives', 'DocumentArchivesController@viewArchives');
    Route::get('/search-archive', 'DocumentArchivesController@searchArchives');

    //Bulk archive file movement

    Route::get('/registry-forward-to-archive', 'DocumentArchivesController@registryToArchive');

    Route::post('/staff-move-file-to-archive', 'DocumentArchivesController@staffToArchive');

    Route::get('/archive-movement/create', 'ArchiveFileMovementController@create');
    Route::post('/archive-movement/save', 'ArchiveFileMovementController@saveBulk');

    Route::post('/archive-movement/retrieve', 'ArchiveFileMovementController@retrieveArchive');
    Route::post('/archive-movement/get-staff', 'ArchiveFileMovementController@getStaff');
    Route::get('/archive-movement/accept', 'ArchiveFileMovementController@acceptance');
    Route::post('/archive-movement/confirmation', 'ArchiveFileMovementController@confirm');

    Route::post('/archive-movement/getUsers', 'ArchiveFileMovementController@getUsers');
    Route::get('/archive-movement/searchUser/{q?}', 'ArchiveFileMovementController@autocomplete');
    Route::get('/view-documents/{id?}/{vol?}', 'ArchiveFileMovementController@viewDocuments');

    Route::post('file-tracking/comment', 'ArchiveFileMovementController@rejectFile');
    Route::post('file-tracking/resend', 'ArchiveFileMovementController@resend');
    Route::get('/archive-transfer/editfile/{bulkID}', 'ArchiveFileMovementController@editAndSend');
    Route::post('/archive-transfer/updatefile', 'ArchiveFileMovementController@updateAndSend');

    Route::get('/archive-transfer/move', 'ArchiveFileMovementController@transfer');
    Route::post('/archive-transfer/post', 'ArchiveFileMovementController@postTransfer');

    Route::get('/archive-transfer/track', 'ArchiveFileMovementController@trackFile');
    Route::post('/archive-transfer/search-track', 'ArchiveFileMovementController@postTrackFile');
    Route::post('/archive-transfer/track', 'ArchiveFileMovementController@postTrackFile');

    Route::get('/archive-transfer/files-sent', 'ArchiveFileMovementController@filesSent');
    Route::post('/archive-transfer/cancel', 'ArchiveFileMovementController@cancel');

    Route::get('/archive-transfer/get-temp', 'ArchiveFileMovementController@tempGet');
    Route::post('/archive-transfer/delete-temp', 'ArchiveFileMovementController@deleteTemp');
    Route::get('/add/new-file', 'ArchiveFileMovementController@newFile');
    Route::post('/add/new-file', 'ArchiveFileMovementController@saveNewFile');
    Route::get('/review-archive/file', 'ArchiveFileMovementController@review');
    Route::get('/edit/file/{id?}', 'ArchiveFileMovementController@editFile');
    Route::post('/update/file', 'ArchiveFileMovementController@updateFile');
    Route::post('/archive-transfer/recall', 'ArchiveFileMovementController@recall');
    Route::get('/copy/staff', 'ArchiveFileMovementController@copy');
    Route::get('/comment/template', 'CommentsController@commentTemplate');
    // End Archive file Movement

    //Get Comment Report
    Route::any('/comment-report', 'CommentReportController@index')->name('commentReport');



    Route::get('/file/docs/{fileNo}', [IncrementController::class, 'staffDocuments']);
    Route::get('/new-staff/variation', [IncrementController::class, 'newStaffVariation']);
    Route::post('/new-staff/save-remark', [IncrementController::class, 'saveRemarkNewAppointment']);


    // =================================Tunde========================================

    Route::post('/report/nominal', [StaffReportController::class, 'NominalRollReport']);
    Route::get('/report/staff-list-by-state-of-origin', [StaffReportController::class, 'getStaffByStateofOrigin']);
    Route::get('/report/nominal', [StaffReportController::class, 'NominalRollReport']);



    //LGA covered
    Route::get('/lga/covered', [LgaCoveredController::class, 'index']);
    Route::post('/lga/covered', [LgaCoveredController::class, 'getLgaState']);
    Route::get('/clear-all', [LgaCoveredController::class, 'clear']);
    Route::post('lga/covered/add', [LgaCoveredController::class, 'store']);
    Route::post('lga/covered/remove/{lgaId}', [LgaCoveredController::class, 'destroy']);
    Route::post('lga/covered/edit', [LgaCoveredController::class, 'update']);

    //Basic parameter
    Route::post('/basic/section', [BasicParameterController::class, 'postDepartment']);
    Route::get('/basic/section', [BasicParameterController::class, 'getDepartment']);
    Route::post('/basic/division', [BasicParameterController::class, 'Divisionsetup']);
    Route::get('/basic/division', [BasicParameterController::class, 'Divisionsetup']);
    Route::post('/basic/designation', [BasicParameterController::class, 'Designation']);
    Route::get('/basic/designation', [BasicParameterController::class, 'Designation']);
    Route::post('basic/designation/edit', [BasicParameterController::class, 'updateDesignation']);
    Route::post('basic/designation/delete', [BasicParameterController::class, 'deletePost']);

    //basic unit
    Route::post('/basic/unit', [BasicParameterController::class, 'Unit']);
    Route::get('/basic/unit', [BasicParameterController::class, 'Unit']);
    Route::post('basic/unit/edit', [BasicParameterController::class, 'updateUnit']);
    Route::post('basic/unit/delete', [BasicParameterController::class, 'deleteunit']);

    //Retiremnt alert

    Route::get('/retirement/alert', [AlertController::class, 'retireList']);
    Route::get('/retirement/notification', [AlertController::class, 'retireListNotify']);
    Route::post('/retirement/notification', [AlertController::class, 'retireListNotify']);

    Route::get('/staff/all', [StaffInfoController::class, 'allStaffInfo'])->name('staff.all');
    Route::get('/hr/staff/documents/{id}', [StaffInfoController::class, 'getStaffDocuments'])->name('staff.documents');

    Route::post('/notify-salary', [AlertController::class, 'notifySalaryDepartment'])
        ->name('notify.salary.department');


    Route::post('/retirement/alert', [AlertController::class, 'retireList']);

    Route::post('/manpower/view/central', [ManPowerController::class, 'view_CENTRAL_LIST_FILTER']);
    Route::get('/map-power/view/central', [ManPowerController::class, 'view_CENTRAL_LIST']);


    //Profile
    Route::get('/profile/configuration', [ProfileController::class, 'viewConfiguration']);
    Route::get('/profile/details', [ProfileController::class, 'view']);
    Route::get('/profile/searchUser/{q?}', [ProfileController::class, 'autocomplete']); //by json
    Route::post('/profile/details', [ProfileController::class, 'details']);
    Route::post('/profile/searchUser/showAll', [ProfileController::class, 'showAll']);
    Route::get('/profile/details/{fileNo?}', [ProfileController::class, 'userCallBack']);

    // Details of service in the force
    Route::get('/update/detail-service/{fileNo?}', [DetailOfServiceController::class, 'index']);
    Route::post('/update/detailofservice/', [DetailOfServiceController::class, 'update']);
    Route::get('/remove/detailofservice/{fileNo?}/{dosid?}', [DetailOfServiceController::class, 'destroy']);
    Route::get('/update/detailofservice/view/{dosid?}', [DetailOfServiceController::class, 'view']);
    Route::get('/profile/DetailsServiceForce/report/{fileNo?}', [DetailOfServiceController::class, 'report']);

    //Education
    Route::get('/education/create/{fileNo?}', [EducationController::class, 'index']);
    Route::get('/education/remove/{id?}', [EducationController::class, 'delete']);
    Route::post('/education/create', [EducationController::class, 'store']);
    Route::get('/education/edit/{id?}', [EducationController::class, 'view']);
    Route::post('/education/edit', [EducationController::class, 'update']);
    Route::get('/profile/education/report/{fileNo?}', [EducationController::class, 'report']);

    //update particulars of education
    Route::post('/profile/update-education', [ProfileController::class, 'updateEDU']);


    //Languages Routes
    Route::get('/update/languages/{staffid?}', [LanguagesController::class, 'index']);
    Route::get('/update/languages/view/{langid?}', [LanguagesController::class, 'view']);
    Route::post('/update/languages/', [LanguagesController::class, 'update']);

    // Route::get('/remove/languages/{fileNo?}/{langid?}', [LanguagesController::class, 'destroy']);
    Route::get('/languages/remove/{langid?}', [LanguagesController::class, 'destroy']);


    Route::get('/profile/languages/report/{fileNo?}', [LanguagesController::class, 'report']);

    //update particulars of language
    Route::post('/profile/update-language', [ProfileController::class, 'updateLANGUAGE']);

    //particulars of children
    Route::get('/children/create/{fileNo?}', [ParticularsOfChildrenController::class, 'index']);

    Route::get('/children/remove/{id}', [ParticularsOfChildrenController::class, 'delete'])->name('children.remove');
    Route::post('/children/create', [ParticularsOfChildrenController::class, 'store']);
    Route::get('/children/edit/{id?}', [ParticularsOfChildrenController::class, 'view']);
    Route::post('/children/edit', [ParticularsOfChildrenController::class, 'update']);
    Route::get('/profile/children/report/{fileNo?}', [ParticularsOfChildrenController::class, 'report']);

    //update particulars of children
    Route::post('/profile/update-children', [ProfileController::class, 'updateCHILDREN']);


    //next of kin
    Route::get('/update/next-of-kin/{fileNo?}', [NextOfKinController::class, 'index']);

    Route::get('/remove/next-of-kin/{kinID?}', [NextOfKinController::class, 'delete'])
        ->name('next-of-kin.delete');




    Route::get('/update/view/{kinID?}', [NextOfKinController::class, 'view']);
    Route::post('/process/next-of-kin/', [NextOfKinController::class, 'store']);
    Route::post('/update/next-of-kin/', [NextOfKinController::class, 'update']);
    Route::get('/profile/next-of-kin/report/{fileNo?}', [NextOfKinController::class, 'report']);

    //update particulars of next of kin
    Route::post('/profile/update-nok', [ProfileController::class, 'updateNOK']);


    //particulars of wife - date of birth
    Route::get('/particular/wife/create/{fileNo?}', [DateOfBirthWifeController::class, 'create']);
    Route::get('/remove/particular/{fileNo?}', [DateOfBirthWifeController::class, 'delete']);
    Route::post('/process/particular', [DateOfBirthWifeController::class, 'store']);
    Route::get('/particular/edit/{ID?}', [DateOfBirthWifeController::class, 'view']);
    Route::get('/profile/particular-wife/report/{fileNo?}', [DateOfBirthWifeController::class, 'report']);


    //update biodata
    Route::post('/profile/update', [ProfileController::class, 'updateBIODATA']);
    Route::post('/profile/update-salary-details', [ProfileController::class, 'updateSALARYDETAILS']);
    Route::get('/get-education-details', [ProfileController::class, 'loadEducation']);

    //update particulars of birth
    Route::post('/profile/update-particulars-of-birth', [ProfileController::class, 'updatePOB']);





    //update particulars of wife
    Route::post('/profile/update-wife', [ProfileController::class, 'updateWIFE']);


    //update previous public service
    Route::post('/profile/update-previous-service', [ProfileController::class, 'updatePUBLICSERVICE']);

    // Details of previous public service
    Route::get('/update/detailofprevservice/{userId?}/{doppsid?}', [DetailsOfPreviousServiceController::class, 'index']);
    Route::post('/update/detailofprevservice/', [DetailsOfPreviousServiceController::class, 'update']);
    Route::get('/remove/detailofprevservice/{doppsid?}', [DetailsOfPreviousServiceController::class, 'destroy']);
    Route::get('/profile/previous-service/report/{fileNo?}', [DetailsOfPreviousServiceController::class, 'report']);

    //Record of Gratuity Payment
    Route::get('/gratuity/create/{staffid?}', [GratuityPaymentController::class, 'index']);
    Route::get('/gratuity/remove/{id?}', [GratuityPaymentController::class, 'delete']);
    Route::post('/gratuity/create', [GratuityPaymentController::class, 'store']);
    Route::get('/gratuity/edit/{id?}', [GratuityPaymentController::class, 'view']);
    Route::post('/gratuity/edit', [GratuityPaymentController::class, 'update']);
    Route::get('/profile/gratuity/report/{fileNo?}', [GratuityPaymentController::class, 'report']);

    //update gratuity
    Route::post('/profile/update-gratuity', [ProfileController::class, 'updateGRATUITY']);


    // Termination of service
    Route::get('/update/termination/{userId?}', [TerminationOfServiceController::class, 'index']);
    Route::post('/update/termination/', [TerminationOfServiceController::class, 'update']);
    Route::get('/remove/termination/{terminateID?}', [TerminationOfServiceController::class, 'destroy']);
    Route::post('/getRecords', [TerminationOfServiceController::class, 'getRecord']);
    Route::post('/edit/termination/', [TerminationOfServiceController::class, 'editRecords']);
    Route::post('/modify/termination/', [TerminationOfServiceController::class, 'modifyRecords']);
    Route::get('/profile/termination-of-service/report/{fileNo?}', [TerminationOfServiceController::class, 'report']);


    //update terminate of service
    Route::post('/profile/update-terminate', [ProfileController::class, 'updateTERMINATIONSERVICE']);




    //update tour and leave
    Route::post('/profile/update-tour-leave', [ProfileController::class, 'updateTOURLEAVE']);


    // Tour and Leave routes
    Route::get('/update/tour-leave-record/{userId?}', [TourLeaveRecordController::class, 'index']);
    Route::post('/update/tour-leave-record/', [TourLeaveRecordController::class, 'update']);
    Route::get('/remove/tour-leave-record/{tourLeaveID?}', [TourLeaveRecordController::class, 'destroy']);
    Route::get('/profile/tour-leave/report/{fileNo?}', [TourLeaveRecordController::class, 'report']);
    Route::get('/update/tour-leave-record/view/{dosid?}', [TourLeaveRecordController::class, 'view']);
    // Record of service
    Route::get('/update/recordofservice/{userId?}/{recID?}', [RecordOfServiceController::class, 'index']);
    Route::post('/update/recordofservice/', [RecordOfServiceController::class, 'update']);
    Route::get('/remove/recordofservice/{recID?}', [RecordOfServiceController::class, 'destroy']);
    Route::get('/profile/record-service/report/{fileNo?}', [RecordOfServiceController::class, 'report']);


    //update record of service
    Route::post('/profile/update-record-service', [ProfileController::class, 'updateSERVICERECORD']);


    //update record of emolument
    Route::post('/profile/update-record-emolument', [ProfileController::class, 'updateEMOLUMENT']);


    // Record of Emolument
    // Route::get('/update/recordofemolument/{staffid?}/{emolumentID?}', [RecordOfEmolumentsController::class, 'index']);
    Route::get('/update/recordofemolument/{staffid}/{emolumentID?}', [RecordOfEmolumentsController::class, 'index'])->name('recordofemolument.index');
    Route::post('/update/recordofemolument/', [RecordOfEmolumentsController::class, 'update']);
    Route::get('/remove/recordofemolument/{emolumentID?}', [RecordOfEmolumentsController::class, 'destroy']);
    Route::get('/profile/record-emolument/report/{fileNo?}', [RecordOfEmolumentsController::class, 'report']);

    //update profile picture
    Route::post('/profile/picture-update', [ProfileController::class, 'updatePROFILEPICTURE']);





    // ====================================END===========================================



    //notification that gift was working on
    Route::get('/markAsRead/{id}', function ($id) {
        Auth::user()->unreadNotifications->where('id', $id)->markAsRead();
        return back();
    })->name('markread');

    Route::get('/notifications', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return view('Notification.notification');
    })->name('notifications');

    //gazetting module
    Route::get('gazette-new-staff', 'GazetteController@index');
    Route::post('gazette-new-staff-appointment', 'GazetteController@generateGazette');
    Route::post('create-staff-gazette', 'GazetteController@gazetteStaff');
    Route::get('gazette-promotion-manuscript', 'GazetteController@officialManuscript');
    Route::get('search-gazetted-staff', 'GazetteController@searchGazetted');
    Route::post('search-gazette', 'GazetteController@showGazetted');
    Route::post('save-promoted-staff-gazette', 'GazetteController@saveGazettedPromoted');



    // ===================================== John ========================================
    // Control Variable
    Route::get('/report/staff-list', [StaffReportController::class, 'staffList']);
    Route::any('/report/nominal-new', [StaffReportController::class, 'NominalRollNew']);
    Route::get('/report/staff-distribution-by-zone', [StaffReportController::class, 'getStaffByZones']);
    Route::post('/report/staff-list', [StaffReportController::class, 'getStaffList'])->name('staff.list');
    Route::any('/report/nominal-new', [StaffReportController::class, 'NominalRollNew']);
    Route::get('/report/staff-distribution-by-zone', [StaffReportController::class, 'getStaffByZones']);
    // Route::get('/report/staff-filter', [StaffReportController::class, 'ajaxStaffFilter']);
    Route::get('/report/staff-filter', [StaffReportController::class, 'staffFilter']);


    // increment
    Route::get('/increment/alert', [IncrementController::class, 'index']);

    // ===================================== End =========================================

});

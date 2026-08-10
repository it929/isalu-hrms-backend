<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\PayrollController;
use App\Http\Controllers\payroll\SearchUserController;
use App\Http\Controllers\payroll\DueForRetirementController;
// use App\Http\Controllers\hr\BasicParameterController;
use App\Http\Controllers\payroll\ActiveMonthController;
use App\Http\Controllers\payroll\ConPayrollReportController;
use App\Http\Controllers\payroll\ConSalarySetupController;
use App\Http\Controllers\payroll\ControlVariableController;
use App\Http\Controllers\payroll\DueForArrearsController;
use App\Http\Controllers\payroll\PayrollController;
use App\Http\Controllers\payroll\PaySlipController;
use App\Http\Controllers\payroll\SalarySetupController;
use App\Http\Controllers\payroll\StaffControlVariableController;
use App\Http\Controllers\payroll\StaffCooperativeController;
use App\Http\Controllers\payroll\TreasuryF1Controller;
use App\Http\Controllers\payroll\Treasury209Controller;
use App\Http\Controllers\payroll\AnalysisController;
use App\Http\Controllers\payroll\ConPecardController;
use App\Http\Controllers\payroll\SalaryScaleController;
use App\Http\Controllers\payroll\ConSalaryScaleController;
use App\Http\Controllers\payroll\ConEpaymentController;
use App\Http\Controllers\payroll\EpaymentController;
use App\Http\Controllers\payroll\SummaryController;
use App\Http\Controllers\payroll\CouncilMembersController;
use App\Http\Controllers\payroll\NHFReportController;
use App\Http\Controllers\payroll\BankSalaryScheduleController;
use App\Http\Controllers\payroll\MonthControlVariableController;
use App\Http\Controllers\payroll\NSITFController;
use App\Http\Controllers\payroll\CoverletterController;
use App\Http\Controllers\payroll\StaffStatusController;

use App\Http\Controllers\payroll\EpaymentGuidelineController;
use App\Http\Controllers\payroll\AssignSalaryStaffPayrollController;
use App\Http\Controllers\payroll\BasicParameterController;
use App\Http\Controllers\payroll\ConApprovalProcessController;
use App\Http\Controllers\payroll\CpoEpaymentController;
use App\Http\Controllers\payroll\EmolumentController;
use App\Http\Controllers\payroll\HalfPayStaffController;
use App\Http\Controllers\payroll\ReportSalaryProjectionController;
use App\Http\Controllers\payroll\VariationControlController;
use App\Http\Controllers\payroll\SalaryController;
use App\Http\Controllers\payroll\FileNumberController;
use App\Http\Controllers\payroll\forwardApprovalController;
use App\Http\Controllers\payroll\forwardCouncilMemberController;
use App\Http\Controllers\payroll\NewPensionController;
use App\Http\Controllers\payroll\UserFunctionController;
use App\Http\Controllers\StaffEducationController;
use App\Http\Controllers\UploadBankAccountDetail;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\hr\SignatoryMandateController;
use App\Http\Controllers\payroll\StaffCurrentStateController;
use App\Http\Controllers\S3UploadController;

/*
|--------------------------------------------------------------------------
| Web Routes for Payroll
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



// Route::group(['middleware' => ['auth', 'permission']], function () {
Route::group(['middleware' => ['auth']], function () {
    Route::group(
        ['middleware' => ['role:admin|tax staff|cpo staff|audit staff|super admin|salary collator|nhf staff']],
        function () {
            Route::get('/division/changeDivision',              'DivisionController@changeDivisionCreate');
            Route::post('/division/changeDivisionStore',        'DivisionController@changeDivisionStore');
        }
    );

    Route::get('hello', function () {
        dd("Hello world!");
    });
    Route::get('/division/divisionAccount',        'DivisionController@getDivisionAccount');
    Route::post('/division/addDivisionAccount',              'DivisionController@updateDivisionAccount');
    Route::post('division/divAcct-update',          'DivisionController@updateDivAccount')->name('division.update');

    //edit user account
    Route::get('/user/editAccount', [UserController::class, 'editAccount']);
    Route::post('user/editAccount', [UserController::class, 'editAccountStore']);
    Route::get('logout',                                     '\App\Http\Controllers\Auth\AuthController@logout');
    //CREATE NEW STAFF / BIO-DATA
    Route::get('/staff/create',                             'CreateStaffController@create');
    Route::post('/staff/store',                             'CreateStaffController@store');
    Route::post('json/staff/search',                         'CreateStaffController@findStaff'); //by json
    Route::get('/profile/details/report/{fileNo?}',           'CreateStaffController@report'); //Report
    Route::get('/profile/account/report/{fileNo?}',           'CreateStaffController@accountReport'); //Report
    //search for staff record
    Route::get('/searchUser/create',                         'SearchUserController@create');

    // Route::post('/searchUser/create',                       'SearchUserController@retrieve');
    // Route::get('/searchUser/{q?}',                           'SearchUserController@autocomplete');

    //CREATE STAFF CONTACT&PHONE
    Route::get('/staffContact/create',       'StaffContactController@create');
    Route::post('staffContact/update',     'StaffContactController@update');
    Route::post('staffContact/saved',     'StaffContactController@store');
    Route::get('staffContact/show-staff-delete/{id}',   'StaffContactController@destroy');



    //staff status
    Route::get('/staffStatus',  [StaffStatusController::class, 'loadView']);
    Route::get('/staffStatus/pending', [StaffStatusController::class, 'loadPending']);
    Route::post('/staffStatus/update', [StaffStatusController::class, 'update']);
    Route::post('/staffStatus/findStaff', [StaffStatusController::class, 'findStaff']);
    Route::post('/staffStatus/getStaffByDivision', [StaffStatusController::class, 'getStaffByDivision']);
    Route::post('/approve',               [StaffStatusController::class, 'getApprove']);

    //Employment Type
    Route::get('/employmentType',               'EmploymentTypeController@index')->name('employmentType.index');
    Route::get('/employmentType/create',         'EmploymentTypeController@create')->name('employmentType.create');
    Route::post('/employmentType',               'EmploymentTypeController@store')->name('employmentType.store');
    Route::post('employmentType/{id}',           'EmploymentTypeController@update')->name('employmentType.update');



    //Employment Type
    // Route::get('/retirement',               'RetirementController@index')->name('retirement.index');
    // Route::post('retirement/search',         'RetirementController@searchRecord')->name('retirement.search');

    Route::get('/retirement',               'RetirementController@index')->name('retirement.index');
    Route::post('retirement/search',         'RetirementController@searchRecord')->name('retirement.search');

    //AieFileUpload Type
    Route::get('/aiefileuploadtype',               'AieFileUploadTypeController@index')->name('aiefileuploadtype.index');
    Route::get('/aiefileuploadtype/create',        'AieFileUploadTypeController@create')->name('aiefileuploadtype.create');
    Route::post('/aiefileuploadtype',              'AieFileUploadTypeController@store')->name('aiefileuploadtype.store');
    Route::post('aiefileuploadtype-update',          'AieFileUploadTypeController@update')->name('aiefileuploadtype.update');

    // Justices Rank



    //Justices Rank
    Route::get('/justicesrank',               'JusticesRankController@index')->name('justicesrank.index');
    Route::post('/justicesrank',               'JusticesRankController@store')->name('justicesrank.store');

    //CREATE NEW STAFF TRANSFER
    Route::get('/staff/transfer',                             'StaffTransferController@create');
    Route::post('/staff/transfer',                             'StaffTransferController@create');

    //R. Variation: Records and Variation
    Route::get('/computer/variation/create',                 'VariationController@create_variation');
    Route::post('/staff/variation/update',                   'VariationController@update_variation');
    Route::get('/staff/variation/report/{fileNo?}/{variationID?}', 'VariationController@report_variation');
    Route::post('/variation/findStaff',                     'VariationController@findStaff');
    Route::get('/variation/findStaff',                      'VariationController@getFindStaff');
    //Route::post('/variation/findStaff/variation',             'VariationController@findVariation');
    Route::get('/staff/variation/view/',                     'VariationController@listAll');
    Route::post('/variation/view-record/filter',             'VariationController@filter_staff');
    Route::get('/variation/staff/search/json/{q?}',         'VariationController@autocomplete_STAFF');
    //R. Variation : Records and Emolument
    // Route::get('/personal-emolument/create',                 'EmolumentController@create_emolument');
    Route::get('/justices-emolument/create', 'EmolumentController@justices_emolument');
    // Route::post('/staff/personal-emolument/update',         'EmolumentController@update_emolument');
    Route::post('/justice/personal-emolument/update',         'EmolumentController@update_justice_emolument');
    // Get staff by division under Person Emolument page
    // Route::post('/personal-emolument/division/staffs',       'EmolumentController@staffToDisplay');


    Route::get('/personal-emolument/approve/temp',                 'EmolumentController@showTempEmoluments');
    Route::post('/staff/personal-emolument/temp/update',            'EmolumentController@update_emolument_temp');

    // Routes for Temporary Personnal Emolument page
    Route::get('/mydata',                 'EmolumentController@showTempPage');


    // Route::get('/upload/file',              'UploadFileController@showUploadFilePage')->name('show.uploadFile');
    // Route::post('/store/upload/file',             'UploadFileController@storeFileUpload')->name('store.uploadFile');
    // Route::post('/upload/file/',             'UploadFileController@RetrieveFileUpload')->name('display.uploadFile');

    // Route::post('/delete/upload/file',             'UploadFileController@DeleteFileUpload')->name('delete.fileUpload');

    Route::get('/upload/file',              'UploadFileController@showUploadFilePage')->name('show.uploadFile');
    Route::any('/upload/file/',             'UploadFileController@RetrieveFileUpload')->name('display.uploadFile');




    Route::get('/personal-emolument/create-temp',                 'EmolumentController@create_temp');
    Route::post('/staff/personal-emolument/temp-update',         'EmolumentController@update_temp');

    Route::get('/staff/personal-emolument/report/{fileNo?}', 'EmolumentController@report_emolument');
    // Route::post('/personal-emolument/findStaff',             'EmolumentController@findStaff');
    Route::post('/personal-emolument/findStaffAfterUpdate',   'EmolumentController@findStaffAfterUpdate')->name('emolumentUpdates');
    Route::post('/personal-emolument/findStaffTemp',             'EmolumentController@findStaffTemp');
    Route::get('/staff/personal-emolument/view/',           'EmolumentController@listAll');
    Route::post('/personal-emolument/view-record/filter',   'EmolumentController@filter_staff');
    Route::get('/personal-emolument/new-staff',             'EmolumentController@listAllNewStaff');
    //R. Variation : mater staff list
    // Route::get('/record-variation/view/cadre',              'ProfileController@view_ALL_CADRE_LIST')->name('recordVariationLoadCadre');
    // Route::get('/record-variation/refresh/cadre-list',      'ProfileController@view_ALL_CADRE_LIST_REFRESH');
    // Route::post('/record-variation/view/cadre',             'ProfileController@view_ALL_CADRE_LIST_FILTER');
    // Route::get('/record-variation/view/increment',          'ProfileController@view_ALL_INCREMENT_SO_FAR');

    // Route::get('/profile/configuration',                    'ProfileController@viewConfiguration');
    // Route::get('/profile/details',                          'ProfileController@view');
    // Route::get('/profile/searchUser/{q?}',                  'ProfileController@autocomplete'); //by json
    // Route::post('/profile/details',                         'ProfileController@details');
    // Route::post('/profile/searchUser/showAll',              'ProfileController@showAll');
    // Route::get('/profile/details/{fileNo?}',                'ProfileController@userCallBack');

    //update biodata
    // Route::post('/profile/update',                                    'ProfileController@updateBIODATA');

    // Route::get('/get-education-details',                              'ProfileController@loadEducation');





    //update particulars of education







    //update censors and commendations
    // Route::post('/profile/update-censors-commendations',              'ProfileController@updateCENSORESANDCOMMENDATION');










    //update profile picture
    // Route::post('/profile/picture-update',                            'ProfileController@updatePROFILEPICTURE');

    //get salary details
    // Route::get('/get-salary-details',                                 'ProfileController@loadSalaryInfo');


    ///////////////////////////-self service admin settings-///////////////////////////////////////////////

    // Route::get('/update-biodata',                              'ProfileController@updateRecord');
    // Route::get('/update-education',                              'ProfileController@updateEducations');

    // Route::get('/update-birth',                                  'ProfileController@updateBirth');
    // Route::get('/update-language',                              'ProfileController@updateLanguages');

    // Route::get('/update-children',                              'ProfileController@updateChildrens');
    // Route::get('/update-salary',                              'ProfileController@updateSalarys');

    // Route::get('/update-nok',                                  'ProfileController@updateNoks');
    // Route::get('/update-wife',                                  'ProfileController@updateWifes');

    // Route::get('/update-publicservice',                          'ProfileController@updatePublicServices');
    // Route::get('/update-censors',                              'ProfileController@updateCensors');

    // Route::get('/update-gratuity',                              'ProfileController@updateGratuitys');
    // Route::get('/update-termination',                          'ProfileController@updateTerminations');

    // Route::get('/update-tour',                                  'ProfileController@updateTour');
    // Route::get('/update-service',                              'ProfileController@updateService');

    // Route::get('/update-emolument',                              'ProfileController@updateEmoluments');

    //update gratuity
    Route::post('/profile/update-gratuity-s',                           'SelfServiceController@updateGRATUITY');

    //update terminate of service
    Route::post('/profile/update-terminate-s',                          'SelfServiceController@updateTERMINATIONSERVICE');

    //update tour and leave
    Route::post('/profile/update-tour-leave-s',                         'SelfServiceController@updateTOURLEAVE');

    //update record of service
    Route::post('/profile/update-record-service-s',                     'SelfServiceController@updateSERVICERECORD');

    //update record of emolument
    Route::post('/profile/update-record-emolument-s',                   'SelfServiceController@updateEMOLUMENT');

    //update profile picture
    Route::post('/profile/picture-update-s',                            'SelfServiceController@updatePROFILEPICTURE');

    //get salary details
    Route::get('/get-salary-details-s',                                 'SelfServiceController@loadSalaryInfo');



    //Man Power

    Route::get('/map-power/view/cadre',                      'ManPowerController@view_ALL_CADRE_LIST')->name('loadCadre');
    Route::post('/map-power/view/cadre',                    'ManPowerController@view_ALL_CADRE_LIST_FILTER');
    Route::get('/map-power/view/filter/cadre',               'ManPowerController@view_CENTRAL_CADRE_FILTER_CONTINUE');
    Route::get('/map-power/staff/search/json/{q?}',         'ManPowerController@search_CENTRAL_LIST_by_json');

    Route::get('/manpower/budget',                          'ManPowerController@viewBudget');
    Route::get('/map-power/view/increment',                 'ManPowerController@view_ALL_INCREMENT_SO_FAR');
    Route::get('/map-power/view/reload-cadre',              'ManPowerController@view_ALL_CADRE_REFRESH')->name('refreshCadre');

    Route::post('/manpower/search/central',                 'ManPowerController@searchCentral');
    Route::get('/earning_deduction/approval',                           'EarningDeductionController@index');
    Route::post('/earning_deduction/approval',                           'EarningDeductionController@index');
    Route::get('/earn_deduct_staffcv/{id?}',                           'EarningDeductionController@edscv');
    Route::post('/earn_deduct_staffcv/{id?}',                           'EarningDeductionController@edscv');
    Route::get('/gotoexport', function () {
        return view('EarningDeduction.gotoexport');
    });
    Route::get('/generalrecord_earn_deduct/{id?}',                           'EarningDeductionController@gred');
    Route::post('/generalrecord_earn_deduct/{id?}',                           'EarningDeductionController@gred');
    Route::get('/generalrecord_earn_deduct2/{id?}',                           'EarningDeductionController@gred2');
    Route::post('/generalrecord_earn_deduct2/{id?}',                           'EarningDeductionController@gred2');

    Route::get('/rec/dec/{id?}',                           'EarningDeductionController@ogred');
    Route::post('/rec/dec/{id?}',                           'EarningDeductionController@ogred');
    Route::get('/rec/dec2/{id?}',                           'EarningDeductionController@ogred2');
    Route::post('/rec/dec2/{id?}',                           'EarningDeductionController@ogred2');

    Route::get('/tourslash/leave/{id?}',                           'TourSlashLeaveController@index');
    Route::post('/tourslash/leave/{id?}',                           'TourSlashLeaveController@index');

    Route::get('/tours/z/{s?}/{e?}',                           'TourSlashLeaveController@ajax');
    Route::get('/tours/d/{s?}',                           'TourSlashLeaveController@delete');

    Route::get('/tech/documentation',                          'TechnicalDocumentationController@index')->name('techDocument');
    Route::post('/tech/documentation',                           'TechnicalDocumentationController@index');
    Route::get('/tech/modify/{id?}',                             'TechnicalDocumentationController@modify')->name('addModification');
    Route::post('/tech/modify/{id?}',                             'TechnicalDocumentationController@modify');

    Route::get('/create/cate/{id?}',                             'TechnicalDocumentationController@createcat')->name('createCategory');
    Route::post('/create/cate/{id?}',                             'TechnicalDocumentationController@createcat');

    Route::get('/add/module/{id?}',                             'TechnicalDocumentationController@addmodule')->name('addModule');
    Route::post('/add/module/{id?}',                             'TechnicalDocumentationController@addmodule');

    Route::get('/tech/viewall/{id?}/{c?}/{m?}',                             'TechnicalDocumentationController@viewall')->name('viewAll');
    Route::post('/add/module/{id?}/{c?}/{m?}}',                             'TechnicalDocumentationController@viewall');





    //Languages Routes




    // Route::get('/profile/languages/report/{fileNo?}',           'LanguagesController@report');
    // Details of service in the force
    // Route::get('/update/detail-service/{fileNo?}',          'DetailOfServiceController@index');
    // Route::post('/update/detailofservice/',                     'DetailOfServiceController@update');
    Route::get('/remove/detailofservice/{fileNo?}/{dosid?}',    'DetailOfServiceController@destroy');
    // Route::get('/update/detailofservice/view/{dosid?}',         'DetailOfServiceController@view');
    Route::get('/profile/DetailsServiceForce/report/{fileNo?}', 'DetailOfServiceController@report');

    //Education
    // Route::get('/education/create/{fileNo?}',              'EducationController@index');
    // Route::get('/education/remove/{id?}',                'EducationController@delete');
    // Route::post('/education/create',                          'EducationController@store');
    // Route::get('/education/edit/{id?}',                         'EducationController@view');
    // Route::post('/education/edit',                             'EducationController@update');
    Route::get('/profile/education/report/{fileNo?}',           'EducationController@report');
    //Record of Censures and Commendations
    Route::get('/commendations/create/{fileNo?}',            'CensureCommendationController@index');
    Route::get('/commendations/remove/{id?}',              'CensureCommendationController@delete');
    Route::post('/commendations/create',                        'CensureCommendationController@store');
    Route::get('/commendations/edit/{id?}',                     'CensureCommendationController@view');
    Route::post('/commendations/edit',                           'CensureCommendationController@update');
    Route::get('/profile/censures-commendations/report/{fileNo?}', 'CensureCommendationController@report');





    // OPEN REGISTRY
    //create new staff
    //Route::get('/new-staff/create', 								'OpenRegistryController@NEW_STAFF');
    Route::post('/new-staff/store',                 'OpenRegistryController@store_NEW_STAFF');
    Route::post('/staff-report/view',                 'OpenRegistryController@filter_staff');
    Route::get('/staff-report/view',                 'OpenRegistryController@listAll');
    Route::get('/staff/search/json/{q?}',               'OpenRegistryController@autocomplete_STAFF');

    Route::post('/new-staff/getcourt',                 'OpenRegistryController@getCourt');
    Route::post('/new-staff/getdepartments',        'OpenRegistryController@getDepartments');
    Route::post('/new-staff/getdesignations',       'OpenRegistryController@getDesignations');


    Route::get('/openregistry/create/',                         'OpenRegistryController@indexview');
    Route::post('/data/searchUser/showAll',           'OpenRegistryController@showAll');
    Route::get('/data/searchUser/{q?}',             'OpenRegistryController@autocomplete');
    Route::post('/data/store/',                 'OpenRegistryController@store');
    Route::post('/data/personalFileData',             'OpenRegistryController@personalFileData');
    Route::get('/remove/openregistry/{userId?}',         'OpenRegistryController@destroy');
    Route::post('/create/openregistry',             'OpenRegistryController@create');
    Route::get('/openregistry/list',               'OpenRegistryController@index');
    Route::get('/openregistry/edit/{pfrID?}',             'OpenRegistryController@edit');
    Route::get('/openregistry/editout/{pfrID?}',         'OpenRegistryController@edit');
    Route::post('/openregistry/update/',             'OpenRegistryController@update');

    //START EDITING STAFF PROFILE LINKS//
    //Bio-Data
    // Route::get('/profile/details/{ID?}/{fileNo?}',        'EditStaffProfileController@viewEditBioData');
    // Route::get('/profile/details/{fileNo?}',            'EditStaffProfileController@details');
    // //Education
    // Route::get('/profile/details/{ID?}/{fileNo?}',        'EditStaffProfileController@viewEditBioData');
    ////ENDS HERE///




    // Pension
    Route::get('/pension/create',                                'PensionController@index')->name('pension.create');
    Route::post('/pension/displaynames',                        'PensionController@showAll');
    Route::post('/pension/compute',                              'PensionController@computePension');
    Route::post('/pension/compute/batch',                       'PensionController@computePensionBatch');
    Route::post('/update/recordofemolument/getdetail',           'RecordOfEmolumentsController@getDetail');
    Route::post('/pension/getpension',                          'PensionController@getpension');
    Route::get('/pension/report',                                'PensionController@pensionReport');
    Route::post('/pension/report/view',                          'PensionController@generateReport');
    Route::get('/pension/report/view',                          'PensionController@pensionReport');
    Route::post('/pension/report/monthlyReport',                'PensionController@monthlyReport')->name('reportMonthly');
    Route::get('/pension/all-report',                           'PensionController@allPensionReport')->name('reportAll');
    Route::post('/pension/staff/update',                        'PensionController@updateStaffPension');
    Route::post('/pension/staff/delete',                        'PensionController@softDeleteStaffPension');
    // Pension Manager
    Route::get('/pension-manager/create',                        'PensionController@create_PFA')->name('create_PFA');
    Route::post('/pensionmanager/store',                        'PensionController@store_PFA');
    Route::get('/pensionmanager/view',                          'PensionController@view_PFA');
    Route::get('pension-manager/edit/{id?}',                    'PensionController@view_edit_PFA');
    // Report
    Route::get('/report/selectrange',                            'ReportController@index');
    Route::post('/report/show',                                 'ReportController@pensionReport');

    // Control Report Routes
    Route::get('/control-reports',                         'ReportController@showControlReportPage');
    Route::post('/control-reports',                         'ReportController@getDivisionInfo');


    // Temporary Emolument List Routes
    Route::get('/temp/get',                         'ReportController@showTempDataPage');
    Route::post('/temp/get/info',                         'ReportController@getTempDataPage');



    /********** Records and variation  ****************/
    // offer of appointment
    Route::get('/offerofappointment/createoffer',                'OfferOfAppointmenController@indexoffer');
    Route::get('/offerofappointment/createletter',              'OfferOfAppointmenController@indexletter');
    Route::get('/offerofappointment/acceptance',                'OfferOfAppointmenController@indexaccept');
    Route::get('/offerofappointment/medicalexam',               'OfferOfAppointmenController@indexmedical');
    Route::post('/offerofappointment/getfileno',                'OfferOfAppointmenController@getfileNo');
    Route::post('/offerofappointment/save',                     'OfferOfAppointmenController@storeOffer');
    Route::post('/offerofappointment/addletter',                'OfferOfAppointmenController@storeletter');
    Route::post('/offerofappointment/letterfileno',             'OfferOfAppointmenController@letterfileno');
    Route::post('/offerofappointment/medicalexam',              'OfferOfAppointmenController@medicalexam');
    Route::post('/offerofappointment/add',                      'OfferOfAppointmenController@storemedicalexam');
    Route::post('/offerofappointment/add-acceptance',           'OfferOfAppointmenController@storeacceptance');
    Route::post('/offerofappointment/getdata',                  'OfferOfAppointmenController@acceptance');
    Route::post('/offerofappointment/bearername',               'OfferOfAppointmenController@getbearer');

    Route::post('/offerofappointment/print-letter-from-list',   'OfferOfAppointmenController@listletterprint');
    Route::post('/offerofappointment/print-offer-from-list',    'OfferOfAppointmenController@listofferprint');
    Route::post('/offerofappointment/print-medical-from-list',  'OfferOfAppointmenController@listmedicalprint');
    Route::post('/offerofappointment/print-acceptance-from-list', 'OfferOfAppointmenController@listacceptanceprint');
    // offer of appointment Listing
    Route::get('/offerofappointment/listoffer',                  'OfferOfAppointmentListingController@offerlisting');
    Route::get('/offerofappointment/listletter',                'OfferOfAppointmentListingController@letterlisting');
    Route::get('/offerofappointment/listacceptance',            'OfferOfAppointmentListingController@acceptancelisting');
    Route::get('/offerofappointment/listmedicalexam',           'OfferOfAppointmentListingController@medicallisting');
    Route::post('/data/searchUser/showAll',                     'OfferOfAppointmentListingController@showAll');
    Route::get('/data/searchUser/{q?}',                         'OfferOfAppointmentListingController@autocomplete');
    Route::post('/offerofappointment/viewacceptance',           'OfferOfAppointmentListingController@filter_acceptance');
    Route::post('/offerofappointment/viewletters',               'OfferOfAppointmentListingController@filter_appoinmentletters');
    Route::post('/offerofappointment/viewoffers',               'OfferOfAppointmentListingController@filter_offerletters');
    Route::post('/offerofappointment/viewmedical',               'OfferOfAppointmentListingController@filter_medicals');





    //handling privileges to all users except CPO and Tax Staff
    Route::group(['middleware' => ['role:admin|salary supervisor|salary staff|audit staff|super admin|salary collator|nhf staff']], function () {
        //Bank
        Route::get('/bank/create',              'BankController@create');
        Route::post('/bank/store',              'BankController@store');
        Route::post('/bank/findBank',             'BankController@findBank');
        //Inactive staff
        Route::get('/inactivestaff',             'InactiveStaffController@loadView');
        Route::post('/inactivestaff/report',         'InactiveStaffController@loadReport');
        //Picture Viewer
        Route::get('/pictureViewer',             'PictureViewerController@loadView');
        Route::post('/pictureViewer/report',         'PictureViewerController@loadReport');
        Route::post('/pictureViewer/create',         'PictureViewerController@store');
        Route::post('/pictureViewer/findStaff',       'PictureViewerController@findStaff');
    });

    //handling privileges to all users except CPO, audit and Tax Staff
    Route::group(
        ['middleware' => ['role:admin|salary supervisor|salary staff|super admin']],
        function () {
            //SearchUser
            Route::get('/searchUser/create',           'SearchUserController@create');
            Route::post('/searchUser/create',           'SearchUserController@retrieve');
            Route::get('/searchUser/{q?}',             'SearchUserController@autocomplete');
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
    Route::get('/banklist/create',  'BankListController@create');
    Route::post('/banklist/store',  'BankListController@store');
    Route::post('/bank/edit', 'BankListController@update');
    Route::get('/banklist/remove/{bankID?}', 'BankListController@delete');
    Route::get('/role/create', 'RoleController@create');
    Route::post('/role/create', 'RoleController@store');
    //Account lock
    // Route::get('/account/lock-all',           'AccountLockController@lockAll');
    // Route::post('/account/lock-all',           'AccountLockController@lockAllStore');
    // Route::get('/account/unlock',             'AccountLockController@unlockOne');
    // Route::post('/account/unlock',             'AccountLockController@unlockOneStore');
    //});

    //handling super admin priveledges
    //Route::group(['middleware' => ['role:super admin']],
    //function() {
    //audit log
    Route::get('/auditLog/create',             'AuditLogController@create');
    Route::post('/auditLog/create',           'AuditLogController@userDetails');
    Route::post('/auditLog/query',             'AuditLogController@userQuery');
    Route::post('/auditLog/finduser',           'AuditLogController@finduser');

    Route::get('/role/userRole',             'RoleController@userRoleCreate');
    Route::post('/role/userRole',             'RoleController@userRoleStore');

    //viewing all user
    Route::get('/role/viewUser',             'RoleController@index');
    //removing role from a specific user
    Route::get('/role/{id}/user/{userid}',        'RoleController@destroy');
    //viewing role for each user
    Route::get('/role/viewUser/{id}',          'RoleController@retrieve');
    //For PERMISSION configuration using get and POST
    Route::get('/permission/create',           'PermissionController@index');
    Route::post('/permission/create',           'PermissionController@store');

    Route::get('/permission/permRole',           'PermissionController@PermRoleCreate');
    Route::post('/permission/permRole',         'PermissionController@PermRoleStore');

    //estab Admin Controller
    // Route::get('/estab/central-list',               'EstabAdminController@view_CENTRAL_LIST');
    // Route::get('/estab/test',                       'EstabAdminController@test');
    // Route::get('/estab/staff/profile/{fileNo}',     'EstabAdminController@getProfile');
    // Route::get('/admin/promotion-brief/',           'EstabAdminController@promotion');
    // Route::get('/admin/upgrading/{fileNo}',         'EstabAdminController@upgrade');
    // Route::get('/admin/conversion/{fileNo}',         'EstabAdminController@convert_advance');
    // Route::get('/estab/conversion',                 'EstabAdminController@conversionList');
    // Route::post('/estab/upgrading/update',          'EstabAdminController@upgradeDetails');
    // Route::post('/estab/autocomplete',              'EstabAdminController@autocomplete');
    // Route::post('/estab/listStaff',                 'EstabAdminController@showAll');
    // Route::post('/estab/con-adv/save',              'EstabAdminController@saveAdvancement');
    // Route::get('/estab/promotion-list',             'EstabAdminController@promotionList');
    // Route::post('/estab/promotion/save',            'EstabAdminController@savePromotion');
    // Route::post('/estab/promotion/confirmation',    'EstabAdminController@confirm');

    // Route::post('/estab/conversion/confirmation',   'EstabAdminController@promotionConfirm');
    // Route::post('/estab/profile/details',           'EstabAdminController@getDetails');

    Route::get('/compute/promotion/variation',       'VariationController@promotionVariation');


    //letter of application
    Route::get('/forms/letter-of-application',             'VariationFormsController@letterOfApplication');
    Route::get('/forms/appointment-form',                  'VariationFormsController@appointmentForm');
    Route::get('/forms/referee-form',                      'VariationFormsController@refereeForm');
    Route::get('/forms/leave-form',                        'VariationFormsController@leaveForm');


    //});


    // Open Registry 2 Routes
    Route::get('/open-file-registry/create',                '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@closingFileIndex');
    Route::post('/open-file-registry/save',                 '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@saveClosingFile');
    Route::get('/open-file-registry/incoming-letter',       '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@incomingLetterIndex');
    Route::post('/open-file-registry/saveletter',           '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@saveIncomingLetter');
    Route::get('/open-file-registry/outgoing-letter',       '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@outgoingLetterIndex');
    Route::post('/open-file-registry/saveoutgoing',         '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@saveOutgoingLetter');
    Route::get('/open-file-registry/mail',                  '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@mailIndex');
    Route::post('/open-file-registry/savemail',             '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@saveMail');
    Route::get('/open-file-registry/view-mails',            '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@viewMails');
    Route::get('/open-file-registry/search',                '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@autocomplete');
    Route::post('/open-file-registry/filter',               '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@filter_mails');
    Route::get('/open-file-registry/view-closed-files',     '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@viewClosedFiles');
    Route::get('/open-file-registry/searchclosed',          '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@auto');
    Route::post('/open-file-registry/filterclosed',         '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@filterClosedFiles');

    Route::get('/open-file-registry/view-outgoing',         '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@viewOutgoing');
    Route::get('/open-file-registry/searchoutgoing',        '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@autocompleteOutgoing');
    Route::post('/open-file-registry/filter-outgoing',      '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@filterOutgoing');

    Route::get('/open-file-registry/view-incoming',         '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@viewIncoming');
    Route::get('/open-file-registry/searchincoming',        '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@autocompleteIncoming');
    Route::post('/open-file-registry/filter-incoming',      '\\App\\Http\\Controllers\\hr\\OpenRegistry2Controller@filterIncoming');

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
    Route::get('/password/resets',              'PasswordAuthController@userForgetPassword');
    Route::post('/password/resets',                 'PasswordAuthController@userResetPassword');
    Route::get('/promotion/update',             'PromotionController@GetPromotion');
    Route::post('/promotion/update',                'PromotionController@PostPromotion');
    Route::get('/self-promotion/update',              'PromotionSelfController@GetPromotion');
    Route::post('/self-promotion/update',                'PromotionSelfController@PostPromotion');



    //user roles
    Route::get('/user-role/create',                       '\\App\\Http\\Controllers\\role_setup\UserRoleController@create');
    Route::post('/user-role/add',                         '\\App\\Http\\Controllers\\role_setup\UserRoleController@addRole');
    Route::get('/user-role/viewroles',                    '\\App\\Http\\Controllers\\role_setup\UserRoleController@displayRoles');
    Route::get('/user-role/edit/{roleID}',                '\\App\\Http\\Controllers\\role_setup\UserRoleController@editRole');
    Route::post('/user-role/update/',                     '\\App\\Http\\Controllers\\role_setup\UserRoleController@updateRole');
    //user modules
    Route::get('/module/create',                          '\\App\\Http\\Controllers\\role_setup\ModuleController@create');
    Route::post('/module/add',                            '\\App\\Http\\Controllers\\role_setup\ModuleController@addModule');
    Route::get('/module/viewmodules',                     '\\App\\Http\\Controllers\\role_setup\ModuleController@displayModules');
    Route::get('/module/edit/{moduleID}',                 '\\App\\Http\\Controllers\\role_setup\ModuleController@editModule');
    Route::post('/module/update',                         '\\App\\Http\\Controllers\\role_setup\ModuleController@updateModule');
    //sub modules
    Route::get('/sub-module/create',                      '\\App\\Http\\Controllers\\role_setup\SubModuleController@create');
    Route::post('/sub-module/add',                        '\\App\\Http\\Controllers\\role_setup\SubModuleController@addSubModule');
    Route::get('/sub-module/view-sub-modules',            '\\App\\Http\\Controllers\\role_setup\SubModuleController@displaySubModules');
    Route::get('/sub-module/edit/{submoduleID}',          '\\App\\Http\\Controllers\\role_setup\SubModuleController@editSubModule');
    Route::post('/sub-module/update',                     '\\App\\Http\\Controllers\\role_setup\SubModuleController@updateSubModule');

    Route::post('/module/setsession', 'SubModuleController@sessionset');
    Route::post('/submodule/modify/', 'SubModuleController@edit');


    //Assign modules
    Route::get('/assign-module/create',                   '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@create');
    Route::post('/role/setsession',                       '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@sessionset');
    Route::post('/assign-module/assign',                  '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@assignSubModule');
    Route::get('/assign-module/view-sub-modules',         '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@displaySubModules');
    Route::get('/assign-module/edit/{submoduleID}',       '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@editSubModule');
    Route::post('/assign-module/update',                  '\\App\\Http\\Controllers\\role_setup\AssignModuleRoleController@updateSubModule');
    //Assign Users

    Route::post('/user-assign/assign',                    '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@assignUser');
    Route::post('/user/display',                          '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@displayUser');
    Route::get('/user/search/{q?}',                       '\\App\\Http\\Controllers\\role_setup\AssignUserRoleController@autocomplete');

    //dependant parameter
    Route::post('/staff/dependant',                          'DependantController@postDependant');
    Route::get('/staff/dependant',                           'DependantController@getDependant');



    //Create Leave
    // Route::get('/Leave/leavetype',         'LeaveCreateController@index');
    // Route::post('/saveLeave/leavetype',                       'LeaveCreateController@store');
    // Route::get('/edit-leave/leavetype/{id}',                       'LeaveCreateController@edit');
    // Route::post('update/leavetype',                           'LeaveCreateController@update');
    // Route::get('/leave/delete/{id}',         'LeaveCreateController@delete');


    //leave management
    // Route::post('/leave/definition',                          'LeaveController@postDefinition');
    // Route::get('/leave/definition',                           'LeaveController@getDefinition');
    // Route::post('/leave/application',                          'LeaveController@Application');
    // Route::get('/leave/application',                           'LeaveController@Application');
    // Route::post('/leave/query',                          'LeaveController@LeaveQuery');
    // Route::get('/leave/query',                           'LeaveController@LeaveQuery');
    // Route::post('/leave/approval',                          'LeaveController@Approval');
    // Route::get('/leave/approval',                           'LeaveController@Approval');
    // Route::get('/self-service/notification',                  'LeaveController@getNotification');
    // Route::get('/self-service/releaveaction',                  'LeaveController@ReleaveResponse');
    // Route::post('/self-service/releaveaction',                  'LeaveController@ReleaveResponse');

    //Head of Department
    Route::get('/department/departmentHead',                          'DepartmentController@index');
    Route::post('/department/departmentHeaD',                          'DepartmentController@store');
    Route::post('/UpdateHeadOfdepartmentHead',     'DepartmentController@update')->name('departmentHead.update');



    //annual leave application
    Route::get('/annual/leave/application',                           'AnnualLeaveController@ApplicationForm');
    Route::post('/annual/leave/application',                          'AnnualLeaveController@saveApplicationForm');
    Route::post('/remove/application',                                'AnnualLeaveController@RemoveApplication');
    Route::post('/annual/leave/reapply',                              'AnnualLeaveController@reApply');
    Route::post('/annual/leave/edit',                                 'AnnualLeaveController@editLeave');

    //route for leave days
    Route::get('/get-leavedays',                                'AnnualLeaveController@getLeaveDaysSum');

    //for hod approval
    Route::get('/annual/leave/approval',                              'AnnualLeaveController@HodApproval');
    Route::post('/recommend/leave',                                   'AnnualLeaveController@RecommendLeave');
    Route::post('/reject/leave',                                      'AnnualLeaveController@RejectLeave');
    Route::post('/cancel/leave',                                      'AnnualLeaveController@CancelLeave');
    Route::get('/notify-staff',                                       'AnnualLeaveController@dontnotifyStaff');
    Route::get('/notify-staffs',                                      'AnnualLeaveController@notifyStaff');
    Route::get('/comments/view-hodes',                                'AnnualLeaveController@viewCommentHODES'); //hod-applicant view comments
    Route::get('/comments/view',                                      'AnnualLeaveController@viewComment'); //hod view comments
    Route::get('/comments/view-s',                                    'AnnualLeaveController@viewComment2'); //admin view comments
    Route::get('/comments/view-a',                                    'AnnualLeaveController@viewComment3'); //admin view comments
    Route::get('/comments/view-e',                                    'AnnualLeaveController@viewCommente'); //ES view comments
    Route::get('/reply/view',                                         'AnnualLeaveController@viewHODReply'); //applicant view hod reply

    Route::post('/notify/staff',                                      'AnnualLeaveController@notifyApplicant')->name('notify-applicant');
    Route::post('/notify/admin',                                      'AnnualLeaveController@notifyAdmin')->name('notify-admin');


    //route for admin approval
    Route::get('/annual/leave/finalapproval',                         'AnnualLeaveController@FinalApproval');
    Route::post('/approve/leave',                                     'AnnualLeaveController@FinalApproveLeave');
    Route::post('/finalreject/leave',                                 'AnnualLeaveController@FinalRejectLeave');
    Route::post('/finalcancel/leave',                                 'AnnualLeaveController@FinalCancelLeave');


    //route for ES approval
    Route::get('/annual/leave/finalapproval_es',                      'AnnualLeaveController@FinalApproval_ES');
    Route::post('/approve/leave_es',                                  'AnnualLeaveController@FinalApproveLeaveES');
    Route::post('/finalreject/leave_es',                              'AnnualLeaveController@FinalRejectLeaveES');
    Route::post('/finalcancel/leave_es',                              'AnnualLeaveController@FinalCancelLeaveES');
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
    Route::get('/function/create',                          '\\App\\Http\\Controllers\\function_setup\FunctionController@create');
    Route::post('/function/add',                            '\\App\\Http\\Controllers\\function_setup\FunctionController@addFunction');
    Route::get('/function/viewmodules',                     '\\App\\Http\\Controllers\\function_setup\FunctionController@displayFunction');
    Route::get('/function/edit/{functionID}',                '\\App\\Http\\Controllers\\function_setup\FunctionController@editFunction');
    Route::post('/function/update',                         '\\App\\Http\\Controllers\\function_setup\FunctionController@updateFunction');
    Route::post('/function/modify',                         '\\App\\Http\\Controllers\\function_setup\FunctionController@edit');


    //sub functions
    Route::get('/sub-function/create',                      '\\App\\Http\\Controllers\\function_setup\SubFunctionController@create');
    Route::post('/sub-function/add',                        '\\App\\Http\\Controllers\\function_setup\SubFunctionController@addSubFunction');
    Route::get('/sub-function/view-sub-modules',            '\\App\\Http\\Controllers\\function_setup\SubFunctionController@displaySubFunction');
    Route::get('/sub-function/edit/{subfunctionID}',        '\\App\\Http\\Controllers\\function_setup\SubFunctionController@editSubFunction');
    Route::post('/sub-function/update',                     '\\App\\Http\\Controllers\\function_setup\SubFunctionController@updateSubFunction');

    Route::post('/sub-function/modify',                     '\\App\\Http\\Controllers\\function_setup\SubFunctionController@edit');
    Route::post('/sub-function/setsession',                 '\\App\\Http\\Controllers\\function_setup\SubFunctionController@sessionset');

    //Assign functions
    Route::get('/assign-function/create',                   '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@create');
    // Route::post('/role/setsession',                         '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@sessionset');
    Route::post('/assign-function/assign',                  '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@assignSubFunction');
    Route::get('/assign-function/view-sub-modules',         '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@displaySubFunction');
    Route::get('/assign-function/edit/{submoduleID}',       '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@editSubModule');
    Route::post('/assign-function/update',                  '\\App\\Http\\Controllers\\function_setup\AssignFunctionRoleController@updateSubFunction');

    //Add Court
    Route::get('court/add-court', 'AddCourtController@index');
    Route::post('court/add-court/insert', 'AddCourtController@store');
    Route::post('court/add-court/update', 'AddCourtController@update');
    Route::get('court/add-court/delete{id}', 'AddCourtController@destroy');

    ////////////////// end judges salary setup //////////////////////////////////////////////

    //New salary structure setup
    Route::get('/new-salary/structure',  'NewSalaryStructureController@Create');
    Route::post('/new-salary/structure',   'NewSalaryStructureController@saveSalary');


    Route::get('/deduction',  'EarningDeductionController@Show');
    Route::post('/deduction',  'EarningDeductionController@Show');
    Route::post('/deduction/delete',  'EarningDeductionController@deleteEarning');
    //Route::post('/salary/create', 'arningdeduction@display');
    //Route::post('/salary/create',   'arningdeduction@saveSalary');

    //payroll:- Salary Scale
    Route::get('/salaryScale', [SalaryScaleController::class, 'index']);
    Route::post('/salaryScale', [SalaryScaleController::class, 'getSalary']);
    Route::get('/salaryScale/{type}/{court}', [SalaryScaleController::class, 'customPaging']);

    /////////////////////////////////// start staff details comparison route by adams ///////////////////////////////////////////////////
    Route::get('/staff-comparison-report', 'StaffDetailsComparisonController@index');
    Route::post('/staff-comparison-report/search', 'StaffDetailsComparisonController@search')->name('search.staffComparison');
    /////////////////////////////////// end staff details comparison route by adams ///////////////////////////////////////////////////


    // Test Sample

    Route::get('/variable/create', 'PayrollController@ControlVariable');
    Route::post('/variable/create', 'PayrollController@ControlVariable');

    //bank set up
    Route::post('/session/court',     'BankController@sessionset');

    //STAFF REGISTRATION
    Route::get('/new-staff/create',                'OpenRegistryController@viewRegistrationForm')->name('newStaff_court');
    Route::get('/staff-registration/court',              'OpenRegistryController@getCourtTab')->name('getCoutTab');
    Route::post('/staff-registration/court',             'OpenRegistryController@postCourtTab');
    Route::get('/staff-registration/basic-info',         'OpenRegistryController@getBasicTab')->name('getBasicTab');
    Route::post('/staff-registration/basic-info',        'OpenRegistryController@postBasicTab');
    Route::get('/staff-registration/contact-info',       'OpenRegistryController@getContactTab')->name('getContactTab');
    Route::post('/staff-registration/contact-info',      'OpenRegistryController@postContactTab');
    Route::get('/staff-registration/employment-info',  'OpenRegistryController@getEmploymentTab')->name('getEmploymentTab');
    Route::post('/staff-registration/employment-info',   'OpenRegistryController@postEmploymentTab');
    Route::get('/staff-registration/preview-info',       'OpenRegistryController@getPreviewTab')->name('getPreviewTab');
    Route::get('/staff-registration/current-staff',      'OpenRegistryController@getCurrentStaff')->name('getCurrentStaff');
    Route::post('/staff-registration/current-staff',     'OpenRegistryController@postCurrentStaff');
    Route::get('/staff-registration/new-registration',    'OpenRegistryController@newRegistration')->name('newRegistration');
    Route::get('/staff-registration/registration-complete', 'OpenRegistryController@finalRegistration')->name('finalRegistration');
    Route::post('/staff-registration/designation',        'OpenRegistryController@getDesignationJson');
    Route::post('/staff-registration/delete',             'OpenRegistryController@deleteOngoingRegistration');
    Route::post('/staff-registration/current-staff-by-court', 'OpenRegistryController@postCurrentStaffCourtID');
    Route::get('/staff-registration/browse-picture',        'OpenRegistryController@loadUploadView')->name('uploadFile');
    Route::post('/staff-registration/browse-picture',        'OpenRegistryController@uploadBrowsedPicture');

    //PRIVILEDGES

    //submit salary





    //Salary Computation
    //Route::get('/compute/{value}', 'ComputeController@loadView'); //for all computational view
    Route::post('/compute/computeAll', 'ComputeProcessorController@computeAll');
    Route::post('/compute/arrears', 'ComputeProcessorController@arrearsNew');
    Route::post('/compute/one-staff', 'ComputeProcessorController@oneStaff');
    Route::post('/compute/payment', 'ComputeProcessorController@payment');
    Route::post('/compute/suspension', 'ComputeProcessorController@suspension');
    Route::post('/compute/retirement', 'ComputeProcessorController@retirement');
    //Route::post('/compute/overtime', 'ComputeProcessorController@overtime');
    Route::post('/compute/leave-grant', 'ComputeProcessorController@leaveGrant');

    //compute All
    Route::post('/court/getActiveMonth',     'ComputeController@getActiveMonth');
    Route::post('/court/getDivisions',     'ComputeController@getDivisions');
    Route::post('/court/getStaff',         'ComputeController@getStaff');
    Route::post('/over-under-pay/compute',  'ComputeProcessorController@payment');

    //Payroll Report
    Route::get('/payrollReport/create', 'PayrollReportController@create');
    Route::post('/payrollReport/create', 'PayrollReportController@Retrieve');
    Route::post('/payrollReport/bulk-report',     'PayrollReportController@BulkPayRoll');
    Route::post('/payrollReport/getBank',     'PayrollReportController@getBank');
    Route::get('/payrollReport/arrears/{court}/{fileNo}/{year}/{month}',   'PayrollReportController@arrearsOearn');



    //audit sumarry report
    Route::get('/auditsummary', 'AuditSummaryReportController@create');
    Route::post('/auditsummary/retrieve', 'AuditSummaryReportController@Retrieve');


    //consolited payroll report

    Route::get('/con-payrollReport/create-revamp', 'ConPayrollReportControllerRevamp@create');
    Route::post('/con-payrollReport/create-revamp', 'ConPayrollReportControllerRevamp@Retrieve');
    Route::get('/con-payrollReport-revamp/create/{division?}/{year?}/{month?}', 'ConPayrollReportControllerRevamp@Retrieve');
    Route::post('/con-payrollReport/bulk-report',     'ConPayrollReportController@BulkPayRoll');
    Route::post('/con-payrollReport/getBank',     'ConPayrollReportController@getBank');

    Route::get('/payroll-breakdown/create', 'ConPayrollReportController@payrollBreakdown');
    Route::post('/payroll-breakdown/create', 'ConPayrollReportController@payrollBreakdownReport');

    // Get staff  bank  page
    Route::post('/staff/bank/retreive',       [ConPayrollReportController::class, 'bankToDisplay']);


    Route::any('/new-payroll', 'ConPayrollReportController@newPayrollIndex');
    Route::post('/new-payroll', 'ConPayrollReportController@newPayrollReport');


    //Add Title
    Route::get('/title', 'AddTitleController@index');
    Route::get('/title/remove/{ID}/{title}', 'AddTitleController@destroy');
    Route::post('/title/update', 'AddTitleController@update');
    Route::post('/title/add', 'AddTitleController@store');


    //cpo Epayment controller
    Route::get('/cpo-epayment', [CpoEpaymentController::class, 'index']);
    Route::post('/cpo-epayment/retrieve', [CpoEpaymentController::class, 'Retrieve']);



    Route::get('/cpo-epayment-justice', [CpoEpaymentController::class, 'justiceIndex']);
    Route::post('/cpo-epayment-justice/retrieve', [CpoEpaymentController::class, 'justiceRetrieve']);

    //Epayment by division
    Route::get('/cpo-epayment-by-division', [CpoEpaymentController::class, 'epaymentByDivision']);
    Route::post('/cpo-epayment-by-division/retrieve', [CpoEpaymentController::class, 'retrieveEpaymentByDivision']);
    

    ///////////////////SALARY MANDATE ///////////////////////////////

    Route::get('/salary/mandate', [CpoEpaymentController::class, 'salaryIndex']);
    Route::post('/salary-mandate-letter', [CpoEpaymentController::class, 'salaryMandateReport'])->name("salaryMandateReport");



    //update current state
    Route::get('/update-staff-current-state', [StaffCurrentStateController::class, 'index']);
    Route::post('/update-staff-current-state/retrieve', [StaffCurrentStateController::class, 'retrieve']);
    Route::post('/update-staff-current-state/{id}', [StaffCurrentStateController::class, 'updateAddress']);

    Route::get('/cpo-create-bank', [CpoEpaymentController::class, 'setAccountNumberIndex']);
    Route::post('/cpo-create-bank', [CpoEpaymentController::class, 'setAccountNumber']);
    Route::post('/cpo-update-bank', [CpoEpaymentController::class, 'updateAccountNumber']);
    Route::post('/cpo-delete-bank/{id}', [CpoEpaymentController::class, 'deleteAccountNumber']);
    Route::get('/cpo-deduction-payment', [CpoEpaymentController::class, 'isPayableDeduction']);
    Route::post('/cpo-deduction-payment/retrieve', [CpoEpaymentController::class, 'isPayableDeductionRetrieve']);

    Route::get('/update-wrong-account', [CpoEpaymentController::class, 'wrongAccount']);
    Route::post('/update-wrong-account/retrieve', [CpoEpaymentController::class, 'retrieveWrongAccount']);
    Route::post('/update-wrong-account/{id}', [CpoEpaymentController::class, 'updateWrongAccount']);







    ///////////////// start Epayment Guideline///////////////////////////


    Route::get('/generate/mandate', 'ConEpaymentController@indexNew');
    Route::post('/generate/mandate', 'ConEpaymentController@RetrieveNew');

    //Company Profile
    Route::get('/company-profile', 'CompanyProfileController@index');
    Route::post('/company-profile/update', 'CompanyProfileController@update');

    //update judges earning
    // Route::get('/update-judge-earning', 'JudgeEarnController@index');
    // Route::post('/compute-judge-earning', 'JudgeEarnController@compute');

    //Earning and Deduction
    Route::get('/earning-deduction', 'EarningAndDeductionController@index');
    Route::post('/earning-deduction/add', 'EarningAndDeductionController@store');
    Route::post('/earning-deduction/update', 'EarningAndDeductionController@update');

    //Control Variable Deduction

    Route::get('/controldectuction', 'ControlVariableController@getDeduction')->name("controldectuction.getDeduction");
    Route::post('/controldectuction-update/', 'ControlVariableController@updateDeduction')->name("controldectuction.updateDeduction");

    //Control Variable
    // Route::any('/report/nominal-grade-step', 'StaffReportController@NominalRollWithGradeStep');
    // Route::any('/report/nominal-new', 'StaffReportController@NominalRollNew');

    // Route::get('/report/nominal-juges', 'StaffReportController@JusticeNominalRollReport');


    // Route::any('/report/nominal-juges', 'StaffReportController@JudgesNominalRollReport');

    //staff due for variations/arears
    Route::get('/staffs-due-for-variation',                           'DueForVariationController@staffDueForVariation');
    Route::post('/staffs-due-for-variation',                           'DueForVariationController@staffDueForVariation');



    //Saperate Arrears Computation
    //arears tf1
    Route::get('/arears/treasuryF1', 'ArearsTF1Controller@loadView');
    Route::post('/arears/treasuryF1', 'ArearsTF1Controller@view');
    //Arears Treasury 209
    Route::get('/arears/treasury209', 'ArearsTreasury209Controller@loadView');
    Route::post('/arears/treasury209', 'ArearsTreasury209Controller@view');

    //compute Promotion Arears
    Route::get('/arrears/compute', 'ArearsOnlyController@showForm');
    Route::post('/arrears/compute', 'ArearsOnlyController@arrears');
    Route::get('/arrears/epayment', 'ArearsOnlyController@epaymentIndex');
    Route::post('/arrears/epayments', 'ArearsOnlyController@retrieveEpayment');
    Route::get('/arrears/epayments', 'ArearsOnlyController@Retrieveget');
    Route::get('/overdue-arrears/compute', 'ArearsOnlyController@showOverDueArrearsForm');
    Route::post('/overdue-arrears/compute', 'ArearsOnlyController@promotion');

    Route::get('/arrears/softcopy', 'ArearsOnlyController@softcopyIndex');
    Route::post('/arrears/softcopy', 'ArearsOnlyController@softcopy');

    Route::get('/arrears/bank-schedule', 'ArearsOnlyController@createBankSchedule');
    Route::post('/arrears/bank-schedules', 'ArearsOnlyController@retrieveBankSchedule');
    Route::get('/arrears/bank-schedules', 'ArearsOnlyController@retrieveSchedulePaged');

    Route::get('/arrears/bank-scheduleTest', 'ArearsOnlyController@createBankScheduleTest');
    Route::post('/arrears/bank-schedulesTest', 'ArearsOnlyController@retrieveBankScheduleTest');
    Route::get('/arrears/bank-schedulesTest', 'ArearsOnlyController@retrieveSchedulePaged');



    Route::get('/arrears-only/workings', 'ArearsOnlyController@createWorking');
    Route::post('/arrears-only/workings', 'ArearsOnlyController@viewWorking');

    Route::any('/arrears-only/list-staff', 'ArearsOnlyController@viewArrearsStaff');
    Route::get('/arrears-only/delete-staff/{id}', 'ArearsOnlyController@deleteStaff');

    Route::get('/arrears-only/epaymentTest', 'ArearsOnlyController@epaymentTest');
    Route::post('/arrears-only/epaymentTest', 'ArearsOnlyController@epaymentTestSave');

    // New Scale Arrears
    Route::get('/newscale/arrears', 'ArearsOnlyController@newScaleArrearsIndex');
    Route::post('/newscale/arrears', 'ArearsOnlyController@newScaleArrearsCompute');
    Route::get('/view/newscale-arrears', 'ArearsOnlyController@newScaleArrearsView');
    Route::post('/view/newscale-arrears', 'ArearsOnlyController@newScaleArrearsExport');

    //per find
    Route::post('/per/findStaff', 'ArearsOnlyController@findStaff');

    //payslip Personal
    Route::get('/payslip/personal', 'PaySlipController@personal');
    Route::post('/payslip/personal', 'PaySlipController@getPersonal');

    // Staff Account Details
    Route::get('/account-info/add',                   'StaffAccountDetailsController@index');
    Route::post('/account-info/add',                  'StaffAccountDetailsController@store');
    Route::post('/account-info/court',                'StaffAccountDetailsController@courtSession');
    Route::get('/account-info/get-staff',            'StaffAccountDetailsController@getStaff');


    //MY STAFF DOCUMENTATION
    Route::get('/staff-documentation',            'StaffDocController@Index')->name('index');
    Route::post('/staff-documentation',            'StaffDocController@getStaffInfo');
    Route::get('/staff-documentation-basic-info',            'StaffDocController@getBasicInfo')->name('getBasicInfo');
    Route::post('/staff-documentation-basic-info',            'StaffDocController@submitBasicInfo');
    Route::get('/staff-documentation-marital-status',            'StaffDocController@getMarital')->name('getMarital');
    Route::post('/staff-documentation-marital-status',            'StaffDocController@submitMarital');
    Route::get('/staff-documentation-contact',            'StaffDocController@getContact')->name('getContact');
    Route::post('/staff-documentation-contact',            'StaffDocController@submitContact');
    Route::get('/staff-documentation-nextofkin',            'StaffDocController@getNextOfKin')->name('getNextOfKin');
    Route::post('/staff-documentation-nextofkin',            'StaffDocController@submitNextOfKin');
    Route::get('/staff-documentation-placeofbirth',            'StaffDocController@getPlaceOfBirth')->name('getPlaceOfBirth');
    Route::post('/staff-documentation-placeofbirth',            'StaffDocController@submitPlaceOfBirth');
    Route::post('/staff-documentation-getLga',            'StaffDocController@LGA');
    Route::get('/staff-documentation-account',            'StaffDocController@getAccount')->name('getAccount');
    Route::post('/staff-documentation-account',            'StaffDocController@submitAccount');
    Route::get('/staff-documentation-previous-employment',            'StaffDocController@getPrevEmployment')->name('getPrevEmployment');
    Route::post('/staff-documentation-previous-employment',            'StaffDocController@submitPrevEmployment');
    Route::get('/staff-documentation-children',            'StaffDocController@getChildren')->name('getChildren');
    Route::post('/staff-documentation-children',            'StaffDocController@submitChildren');
    Route::get('/staff-documentation-others',            'StaffDocController@getOthers')->name('getOthers');
    Route::post('/staff-documentation-others',            'StaffDocController@submitOthers');
    Route::get('/staff-documentation-preview',            'StaffDocController@getPreview')->name('getPreview');
    Route::post('/staff-documentation-preview',            'StaffDocController@submitPreview');
    Route::get('/staff-documentation-complete',            'StaffDocController@getComplete')->name('getComplete');
    Route::post('/staff-documentation-complete',            'StaffDocController@submitComplete');

    //Salary rate function
    Route::get('/salary-rate',                           'SalaryRateController@index');
    Route::post('/salary-rate',                           'SalaryRateController@edit');


    //mail

    Route::get('/contact/mail', 'ContactController@show');
    Route::post('/contact/mail', 'ContactController@mailPost');



    //payroll:- New Salary Scale
    Route::get('/new/salaryScale', 'NewSalaryStructureController@viewStructure');
    Route::get('/new/salaryScale/{type}', 'NewSalaryStructureController@customPaging');

    //PECARD
    Route::get('/pecard/view',                           'PecardController@create');
    Route::post('/pecard/view',                          'PecardController@viewCard');


    //payroll Approval Process
    Route::get('/payroll/checking', 'ApprovalProcessController@checkingPayroll');
    Route::post('/payroll/checking', 'ApprovalProcessController@checkAndClear');

    Route::get('/payroll/audit', 'ApprovalProcessController@auditPayroll');
    Route::post('/payroll/audit', 'ApprovalProcessController@auditAndClear');

    Route::get('/payroll/ca', 'ApprovalProcessController@ca');
    Route::post('/payroll/ca', 'ApprovalProcessController@caProcess');

    Route::post('/payroll/recall', 'ApprovalProcessController@recall');

    Route::get('/payroll/dfa',  'ApprovalProcessController@DFA');
    Route::post('/payroll/dfa', 'ApprovalProcessController@DFAProcess');

    Route::get('/payroll/es',  'ApprovalProcessController@es');
    Route::post('/payroll/es', 'ApprovalProcessController@esProcess');

    Route::get('/payroll/cpo',  'ApprovalProcessController@cpoPayroll');
    Route::post('/payroll/cpo', 'ApprovalProcessController@cpoProcess');


    //consolidated payroll Approval Process
    Route::get('/con-payroll/checking', [ConApprovalProcessController::class, 'checkIndex']);
    Route::post('/con-payroll/checking', [ConApprovalProcessController::class, 'checkingPayroll']);
    Route::post('/checking/clear', [ConApprovalProcessController::class, 'checkAndClear']);

    //pitoff
    Route::any('/checking-unit', [forwardApprovalController::class, 'checkingPage']);
    Route::any('/audit-unit', [forwardApprovalController::class, 'auditPage']);
    Route::any('/cpo-unit', [forwardApprovalController::class, 'cpoPage']);

    Route::post('/council-members-payroll-location', [forwardCouncilMemberController::class, 'councilPayrollLocation']);

    Route::any('/council-checking-unit', [forwardCouncilMemberController::class, 'checkingCouncilPage']);
    Route::any('/council-audit-unit', [forwardCouncilMemberController::class, 'auditCouncilPage']);
    Route::any('/council-cpo-unit', [forwardCouncilMemberController::class, 'cpoCouncilPage']);

    Route::post('/salary-forward/payroll-report', [forwardApprovalController::class, 'salaryForwardReport']);
    Route::post('/salary-decline/payroll-report', [forwardApprovalController::class, 'salaryDeclineReport']);

    Route::post('/salary-forward-council/payroll-report', [forwardCouncilMemberController::class, 'salaryForwardCouncilReport']);

    Route::post('/checking-forward/payroll-report', [forwardApprovalController::class, 'checkingForwardReport']);
    Route::post('/checking-decline/payroll-report', [forwardApprovalController::class, 'checkingDeclineReport']);

    Route::post('/checking-forward-council/payroll-report', [forwardCouncilMemberController::class, 'checkingForwardCouncilReport']);
    Route::post('/checking-decline-council/payroll-report', [forwardCouncilMemberController::class, 'checkingDeclineCouncilReport']);

    Route::post('/audit-forward/payroll-report', [forwardApprovalController::class, 'auditForwardReport']);
    Route::post('/audit-decline/payroll-report', [forwardApprovalController::class, 'auditDeclineReport']);

    Route::post('/audit-forward-council/payroll-report', [forwardCouncilMemberController::class, 'auditForwardCouncilReport']);
    Route::post('/audit-decline-council/payroll-report', [forwardCouncilMemberController::class, 'auditDeclineCouncilReport']);

    Route::post('/cpo-approve/payroll-report', [forwardApprovalController::class, 'cpoApproveReport']);
    Route::post('/cpo-decline/payroll-report', [forwardApprovalController::class, 'cpoDeclineReport']);

    Route::post('/cpo-approve-council/payroll-report', [forwardCouncilMemberController::class, 'cpoApproveCouncilReport']);
    Route::post('/cpo-decline-council/payroll-report', [forwardCouncilMemberController::class, 'cpoDeclineCouncilReport']);

    Route::post('/checking/verified', [forwardApprovalController::class, 'checkingVerify']);
    Route::post('/audit/verified', [forwardApprovalController::class, 'auditVerify']);

    Route::post('/checking-council/verified', [forwardCouncilMemberController::class, 'checkingCouncilVerify']);
    Route::post('/audit-council/verified', [forwardCouncilMemberController::class, 'auditCouncilVerify']);

    //route for payroll comments
    Route::get('/payroll-comments', [ConPayrollReportController::class, 'payrollComments'])->name('payroll.comments');;
    // Route::get('/payroll-comments/{division}/{year}', [ConPayrollReportController::class, 'payrollComments']);


    Route::get('/payroll-council-comments/{year?}/{month?}', [forwardCouncilMemberController::class, 'payrollCouncilComments']);
    //end pitoff

    Route::get('/con-payroll/audit', 'ConApprovalProcessController@auditIndex');
    Route::post('/con-payroll/audit', 'ConApprovalProcessController@auditPayroll');
    Route::post('/audit/clear', 'ConApprovalProcessController@auditAndClear');

    Route::get('/con-payroll/ca', 'ConApprovalProcessController@ca');
    Route::post('/con-payroll/ca', 'ConApprovalProcessController@caProcess');

    Route::post('/con-payroll/recall', 'ConApprovalProcessController@recall');

    Route::get('/con-payroll/dfa',  'ConApprovalProcessController@DFA');
    Route::post('/con-payroll/dfa', 'ConApprovalProcessController@DFAProcess');

    Route::get('/con-payroll/es',  'ConApprovalProcessController@es');
    Route::post('/con-payroll/es', 'ConApprovalProcessController@esProcess');

    Route::get('/con-payroll/cpo',  'ConApprovalProcessController@cpoPayroll');
    Route::post('/con-payroll/cpo', 'ConApprovalProcessController@cpoProcess');

    //t209
    Route::get('treasury209/view-council',         'Treasury209Controller@loadViewCouncil');
    Route::post('treasury209/view-council',         'Treasury209Controller@viewCouncil');

    Route::get('treasuryTest/view',         'Treasury209Controller@load');
    Route::post('treasuryTest/view',         'Treasury209Controller@view');

    //payroll group by bank
    Route::get('groupby/banks-jacket',         'SummaryController@groupPayroll');
    Route::post('groupby/banks-jacket',         'SummaryController@groupPayrollDisplay');


    //Payroll Analysis Remark
    //Route::post('/payroll/remark',		'AnalysisController@payrollRemark')->name('payroll.rename');

    Route::get('test/display',         'Treasury209Controller@test');







    //variation Contyroll
    Route::get('/variation-control/view',         'VariationControlController@index');
    Route::post('/variation-control/view',         'VariationControlController@load');

    Route::get('/variation-control/payroll',         'VariationControlController@newPayrollIndex');
    Route::post('/variation-control/payroll',         'VariationControlController@newPayrollReport');

    //upload staff attachment; samju
    Route::get('/staff/attachment-upload',         'staffAttachmentController@displayForm');
    Route::post('attachment/save',                 'staffAttachmentController@uploadAttachment');

    Route::get('/staff/attachment-upload/{id}',      'staffAttachmentController@displayRecordURL');

    //view-download staff attachment
    Route::get('/search',                   'staffAttachmentController@search');
    Route::post('/find',                         'staffAttachmentController@SearchStaff');
    Route::get('/live_search/action',                'staffAttachmentController@action')->name('live_search.action');

    Route::get('/staff/attachment-download',         'staffAttachmentController@searchindex');
    Route::get('/search/{searchQuerys?}',            'staffAttachmentController@StaffSearch');

    Route::get('/search/{searchQuery?}',             'staffAttachmentController@search');

    Route::get('/member/{id}',                       'staffAttachmentController@viewmember');

    Route::post('/find',                             'staffAttachmentController@find');
    Route::get('/attachment/{id}',                   'staffAttachmentController@ViewDOC');
    Route::post('/attachment',                       'staffAttachmentController@DeleteDOC');




    //add new staff
    Route::get('/add-staff/create',                 [EmolumentController::class, 'newStaff']);
    Route::post('/add-staff/create',                     [EmolumentController::class, 'addNewstaff']);
    Route::get('/bank-account/update',                 [EmolumentController::class, 'accountUpdate']);


    Route::any('/staff/overtime_override',                             'StaffControlVariableController@overrideOvertime');

    //nhis routes
    // Route::get('/nhis-balance/create',                     'NHISController@create');
    // Route::post('/nhis-balance/create',                     'NHISController@store');
    // Route::get('/nhis-balance/edit/{id?}',                     'NHISController@edit');
    // Route::post('/nhis-balance/edit',                     'NHISController@update');

    // Route::get('/nhis-account/create',                 'NHISController@createAccount');
    // Route::post('/nhis-account/create',                     'NHISController@storeAccount');
    // Route::post('/alhisan-account/create',                     'NHISController@storeAlhisanAccount');


    // Route::get('/nhis/deduction',                 'NHISController@deduction');
    // Route::post('/nhis/deduction',                     'NHISController@viewDeduction');



    //Bulk file movement
    Route::get('/bulk-movement/create',                 'BulkFileMovementController@create');
    Route::post('/bulk-movement/save',                  'BulkFileMovementController@saveBulk');

    Route::post('/bulk-movement/save',                  'BulkFileMovementController@saveBulk');
    Route::post('/bulk-movement/get-staff',             'BulkFileMovementController@getStaff');
    Route::get('/bulk-movement/accept',                 'BulkFileMovementController@acceptance');
    Route::post('/bulk-movement/confirmation',          'BulkFileMovementController@confirm');

    Route::post('/bulk-movement/getUsers',              'BulkFileMovementController@getUsers');
    Route::get('/bulk-movement/searchUser/{q?}',       'BulkFileMovementController@autocomplete');



    Route::get('/bulk-transfer/move',                   'BulkFileMovementController@transfer');
    Route::post('/bulk-transfer/post',                  'BulkFileMovementController@postTransfer');

    Route::get('/bulk-transfer/track',                  'BulkFileMovementController@trackFile');
    Route::post('/bulk-transfer/search-track',          'BulkFileMovementController@postTrackFile');
    Route::post('/bulk-transfer/track',          'BulkFileMovementController@postTrackFile');

    Route::get('/bulk-transfer/files-sent',             'BulkFileMovementController@filesSent');
    Route::post('/bulk-transfer/cancel',                'BulkFileMovementController@cancel');

    Route::get('/bulk-transfer/get-temp',               'BulkFileMovementController@tempGet');
    Route::post('/bulk-transfer/delete-temp',          'BulkFileMovementController@deleteTemp');
    Route::get('/add/new-file',                        'BulkFileMovementController@newFile');
    Route::post('/add/new-file',                       'BulkFileMovementController@saveNewFile');
    Route::get('/review/file',                        'BulkFileMovementController@review');
    Route::get('/edit/file/{id?}',                       'BulkFileMovementController@editFile');
    Route::get('/update/file',                       'BulkFileMovementController@updateFile');
    Route::post('/bulk-transfer/recall',                'BulkFileMovementController@recall');

    Route::get('/copy/staff',                        'BulkFileMovementController@copy');

    Route::get('/test/pe',                        'PecardController@createIndex');
    // Route::post('/personal-emolument/get-lga',                        'EmolumentController@getlga');

    Route::get('/sotactiveMonth/create',           'ActiveMonthSOTController@create');
    Route::post('/sotactiveMonth/create',           'ActiveMonthSOTController@store');

    // Route::post('/collect/staff-detail',           'EmolumentController@populateLGA');
    // Route::post('/collect/append',           'EmolumentController@append');


    Route::get('/bat/create',           'BatNoController@create');
    Route::post('/bat/create',           'BatNoController@store');
    Route::get('/bat/edit/{id?}',               'BatNoController@edit');
    Route::post('/bat/update',           'BatNoController@update');

    //council Members Bat
    Route::get('/council-bat/create',           'BatNoController@councilBatIndex');
    Route::post('/council-bat/create',           'BatNoController@councilBatSave');
    Route::get('/council-bat/edit/{id?}',           'BatNoController@councilBatEdit');
    Route::post('/council-bat/update',           'BatNoController@councilBatUpdate');


    //additlog search
    Route::get('/auditlog/search',        'AuditLogController@viewLog');
    Route::post('/auditlog/search',        'AuditLogController@searchLog');


    //designation Update
    Route::get('/staff/designation/update',           'EmolumentController@showDesignation');
    Route::post('/staff/designation/update',           'EmolumentController@updateDesignation');

    Route::get('/quarterly-allowance/create',           'QuarterlyAllowanceController@create');

    Route::post('/quarterly-allowance/create',           'QuarterlyAllowanceController@store');
    Route::post('/quarterly-allowance/get-data',           'QuarterlyAllowanceController@gradeAllowance');

    //Event type routing #G
    Route::get('/eventType/event',     'EventController@create');
    Route::post('/Type/save',     'EventController@storeEvent');
    Route::get('/eventType/editEvent',  'EventController@updateEvent');
    Route::post('Type/update/{id}',   'EventController@updateEvent');
    Route::get('eventType/delete-event/{id}',  'EventController@Destroy');

    //Event Application #Tola
    Route::get('/applyevent/create',    'ApplyEventController@create');
    Route::post('/applyevent/save',     'ApplyEventController@store');
    Route::get('/eventType/addEvent',  'ApplyEventController@create');
    Route::get('/delete/{id}/{id2}',     'ApplyEventController@CheckDelete');

    //additlog search
    Route::get('/auditlog/search',        'AuditLogController@viewLog');
    Route::post('/auditlog/search',        'AuditLogController@searchLog');

    //Retiremnt Alert
    //     Route::get('/retirement/alert',        'AlertController@retireList');
    // Route::get('/increment/alert',        'AlertController@incrementList');

    // Route::get('/print/doc/{id?}',        'AlertController@printDoc');


    // Route::get('/variation-order/approve/{id?}',        'AlertController@variationOrder');
    Route::get('/save/arrears',        'AlertController@storeArrears');


    // Route::post('/staff/details/get',        'AlertController@variationRemark');
    // Route::post('/variation/approval/remark',        'AlertController@saveRemark');
    // Route::get('/variation/approval/view-list',        'AlertController@variationApproval');

    Route::post('/variation/approval/reverse',        'AlertController@reverseRemark');
    Route::post('/variation/approval/reject',        'AlertController@reject');
    Route::post('/variation/rejection/reason',        'AlertController@rejectReason');




    Route::get('/council-members/payroll-vc',            [VariationControlController::class, 'councilPayrollIndex']);
    Route::post('/council-members/payroll-vc',           [VariationControlController::class, 'councilPayrollReport']);




    // Route::get('/admin/promotion-arrears/entry',        'AlertController@promotionArrearsEntry');
    // Route::post('/admin/promotion-arrears/entry',        'AlertController@saveEntry');
    Route::post('/create/session',           'AlertController@createSes');

    Route::get('/variation/list',        'AlertController@variationList');
    Route::post('/variation/list',        'AlertController@saveVariation');

    Route::any('/con-payrollReport/compare-earning/remark', 'ConPayrollReportController@CompareEarningRemark');
    Route::any('/con-payrollReport/compare-pension', 'ConPayrollReportController@ComparePension');

    Route::any('/arrear-computation/create',  'PayrollArrearOnlyController@ArrearsComputation');

    //40 percent increase computation ComputePercentIncreaseController
    Route::get('/compute-percentage-increase',  'ComputePercentIncreaseController@computeIndex');
    Route::post('/compute-percentage-increase',  'ComputePercentIncreaseController@runComputation');
    Route::post('/undo-compute-percentage-increase',  'ComputePercentIncreaseController@removePercentageIncrement');

    //update union Dues
    Route::get('/update/union-dues',        'UpdateUnionController@updateUnion');

    //Current State
    Route::get('state/create-update-state',     'CurrentStateController@create')->name('createCurrentState');
    Route::post('state/create-add-new-state',   'CurrentStateController@store')->name('postCurrentState');
    Route::post('state/update-state',           'CurrentStateController@update')->name('updateCurrentState');
    Route::get('state/update-state',            'CurrentStateController@create');
    Route::get('state/delete-state/{id?}',      'CurrentStateController@destroy')->name('deleteState');

    // Salary Approval process
    Route::get('/approval-process',        'SalaryApprovalProcessControllerNew@salaryPush');
    Route::post('/approval-process',        'SalaryApprovalProcessControllerNew@process');
    Route::post('/process-to-variation',        'SalaryApprovalProcessControllerNew@processToVariationControl');
    Route::post('/rejection',                 'SalaryApprovalProcessControllerNew@rejection');

    // Salary Approval process
    /*Route::get('/main-payroll',              'SalaryApprovalProcessController@create');
    Route::post('/main-payroll',             'SalaryApprovalProcessController@Retrieve');*/
    Route::get('/main-payroll',              'SalaryApprovalProcessControllerNew@payroll');
    Route::post('/main-payroll',             'SalaryApprovalProcessControllerNew@payrollReport');
    Route::get('payroll-analysis',         'SalaryApprovalProcessControllerNew@analysis');
    Route::post('payroll-analysis',         'SalaryApprovalProcessControllerNew@analysisDisplay');

    Route::get('/main-payroll/report',              'SalaryApprovalProcessControllerNew@payroll');
    Route::post('/main-payroll/report',             'SalaryApprovalProcessControllerNew@payrollReport');

    Route::get('/payroll-summary', 'SalaryApprovalProcessControllerNew@payrollSummary');
    Route::post('/payroll-summary', 'SalaryApprovalProcessControllerNew@viewPayrollSummary');
    Route::get('/view-pecard',                           'SalaryApprovalProcessControllerNew@checkCard');
    Route::post('/division/setsession', 'SalaryApprovalProcessControllerNew@sessionset');
    Route::post('/view-pecard',                          'SalaryApprovalProcessControllerNew@viewCard');

    Route::get('treasury209-view',              'SalaryApprovalProcessControllerNew@loadView');
    Route::post('treasury209-view',         'SalaryApprovalProcessControllerNew@view');
    Route::get('/display/minutes/{year}/{month}',    'SalaryApprovalProcessControllerNew@displayComments');

    //bank schedule
    Route::get('/bankshedule/view',         'SalaryApprovalProcessControllerNew@bankSchedule');
    Route::post('/bankshedule/view',         'SalaryApprovalProcessControllerNew@postBankSchedule');

    //Mandate Approval
    Route::get('/salary/view',             'SalaryMandateApprovalControllerNew@salaryAction');
    Route::post('/salary/view',             'SalaryMandateApprovalControllerNew@salaryHeadComment');

    Route::get('/mandate/view',             'SalaryMandateApprovalControllerNew@mandateView');
    Route::post('/mandate/view',             'SalaryMandateApprovalControllerNew@mandateComment');
    

   
    Route::get('/council-members/payroll-approval',            'SalaryApprovalProcessControllerNew@councilPayrollIndex');
    Route::post('/council-members/payroll-approval',           'SalaryApprovalProcessControllerNew@councilPayrollReport');

    Route::get('/council-members/mandate-approval',        'SalaryApprovalProcessControllerNew@councilMandateIndex');
    Route::post('/council-members/mandate-approval',        'SalaryApprovalProcessControllerNew@councilMandateRetrieve');


    Route::get('/council-member/bank-schedule-approval',            'SalaryApprovalProcessControllerNew@councilBankSchedule');
    Route::post('/council-member/bank-schedule-approval',           'SalaryApprovalProcessControllerNew@postCouncilBankSchedule');

    Route::get('/council-members/analysis-approval',            'SalaryApprovalProcessControllerNew@councilAnalysis');
    Route::post('/council-members/analysis-approval',           'SalaryApprovalProcessControllerNew@viewCouncilAnalysis');

    Route::any('/approval-report',         'SalaryApprovalProcessControllerNew@report');

    //End Salaray Approval Process




    Route::get('/mandate/{year}/{month}',                 'SalaryMandateApprovalController@mandate');

    Route::get('/display/comments/{year}/{month}',             'SalaryMandateApprovalController@displayComments');


    Route::get('/es/view',             'SalaryMandateApprovalController@ESView');
    Route::post('/es/view',             'SalaryMandateApprovalController@ESComment');


    //council
    Route::get('councilletter/print',          'CoverletterController@council');
    Route::post('councilletter/print',         'CoverletterController@councilview');

    //deduction percentage
    Route::get('percentage/add',                'DeductionPercentageController@index');
    Route::post('percentage/add',               'DeductionPercentageController@add')->name('percentage-add');

    // Salary Approval process Testing

    // Route::get('/approval-process-test',        'SalaryApprovalProcessControllerTest@salaryPush');
    // Route::post('/approval-process-test',        'SalaryApprovalProcessControllerTest@process');
    // Route::post('/process-to-variation-test',        'SalaryApprovalProcessControllerTest@processToVariationControl');
    // Route::post('/rejection-test',                 'SalaryApprovalProcessControllerTest@rejection');

    // Route::get('/main-payroll-test',              'SalaryApprovalProcessControllerTest@create');
    // Route::post('/main-payroll-test',             'SalaryApprovalProcessControllerTest@Retrieve');
    // Route::get('payroll-analysis-test',         'SalaryApprovalProcessControllerTest@analysis');
    // Route::post('payroll-analysis-test',         'SalaryApprovalProcessControllerTest@analysisDisplay');

    // Route::get('/payroll-summary-test', 'SalaryApprovalProcessControllerTest@payrollSummary');
    // Route::post('/payroll-summary-test', 'SalaryApprovalProcessControllerTest@viewPayrollSummary');
    // Route::get('/view-pecard-test',                           'SalaryApprovalProcessControllerTest@checkCard');
    // Route::post('/view-pecard-test',                          'SalaryApprovalProcessControllerTest@viewCard');

    // Route::get('treasury209-view-test',         'SalaryApprovalProcessControllerTest@loadView');
    // Route::post('treasury209-view-test',         'SalaryApprovalProcessControllerTest@view');
    // Route::get('/display/minutes-test/{year}/{month}',    'SalaryApprovalProcessControllerTest@displayComments');

    //bank schedule
    // Route::get('/bankshedule/view-test',         'SalaryApprovalProcessControllerTest@bankSchedule');
    // Route::post('/bankshedule/view-test',         'SalaryApprovalProcessControllerTest@postBankSchedule');

    //Mandate Approval
    // Route::get('/salary/view-test',             'SalaryMandateApprovalControllerTest@salaryAction');
    // Route::post('/salary/view-test',             'SalaryMandateApprovalControllerTest@salaryHeadComment');

    // Route::get('/mandate/view-test',             'SalaryMandateApprovalControllerTest@mandateView');
    // Route::post('/mandate/view-test',             'SalaryMandateApprovalControllerTest@mandateComment');

    // Route::get('/mandate-test/{year}/{month}',                 'SalaryMandateApprovalControllerTest@mandate');

    // Route::get('/display/comments-test/{year}/{month}',             'SalaryMandateApprovalControllerTest@displayComments');

    // Route::get('/es/view-test',             'SalaryMandateApprovalControllerTest@ESView');
    // Route::post('/es/view-test',             'SalaryMandateApprovalControllerTest@ESComment');


    //staff designation
    // Route::get('staff/designation',             'StaffDesignationController@displayForm');
    // Route::post('user/assign-designation',     'StaffDesignationController@assignDesignation');
    // Route::get('user/delete/{id?}',             'StaffDesignationController@deleteDesignation');

    Route::get('/assign/user',             'AuditUnitControllerNew@assignStaff');
    Route::post('/assign/user',                 'AuditUnitControllerNew@auditingU');
    Route::get('/audit/assigned-records',             'AuditUnitControllerNew@assignRecords');
    Route::post('/audit/confirmation',             'AuditUnitControllerNew@confirm');
    Route::any('/salary-audit/report',             'AuditUnitControllerNew@report');

    //Audit Unit Test
    Route::get('/assign/user-test',             'AuditUnitControllerTest@assignStaff');
    Route::post('/assign/user-test',                 'AuditUnitControllerTest@auditingU');
    Route::get('/audit/assigned-records-test',             'AuditUnitControllerTest@assignRecords');
    Route::post('/audit/confirmation-test',             'AuditUnitControllerTest@confirm');
    Route::any('/salary-audit/report-test',             'AuditUnitControllerTest@report');

    Route::get('/special-overtime/pay',                         'SpecialOvertimeController@index');
    Route::post('/special-overtime/pay',                         'SpecialOvertimeController@report');

    Route::get('/special-overtime/mandate',                         'SpecialOvertimeController@indexMandate');
    Route::post('/special-overtime/mandate',                         'SpecialOvertimeController@mandateReport');

    Route::get('/special-overtime/tax',                         'SpecialOvertimeController@indexTax');
    Route::post('/special-overtime/tax',                         'SpecialOvertimeController@taxReport');

    Route::get('/report/salary-projection',                         [ReportSalaryProjectionController::class, 'createSalaryProjection']);
    Route::post('/report/salary-projection',                        [ReportSalaryProjectionController::class, 'viewSalaryProjection']);
});



// Route::group(['middleware' => ['auth', 'force.password.change', 'permission']], function () {
Route::group(['middleware' => ['auth', 'force.password.change']], function () {
    // File Number Routes
    Route::get('/file-number/search', [FileNumberController::class, 'showSearchForm'])->name('file.number.search');
    Route::post('/file-number/search', [FileNumberController::class, 'searchFileNumber'])->name('file.number.search.submit');
    Route::get('/file-number/verify-otp', [FileNumberController::class, 'showOtpVerificationForm'])->name('file.number.verify.otp.form');
    Route::post('/file-number/verify-otp', [FileNumberController::class, 'verifyOtp'])->name('file.number.verify.otp');
    Route::post('/file-number/resend-otp', [FileNumberController::class, 'resendOtp'])->name('file.number.resend.otp');
    Route::get('/payslip/selection', [FileNumberController::class, 'showPayslipSelection'])->name('payslip.selection');
    Route::post('/payslip/generate', [FileNumberController::class, 'generatePayslip'])->name('payslip.generate');


    Route::get('/other-payment',             'OtherPaymentController@index')->name('otherPayment');
    Route::post('/other-payment',             'OtherPaymentController@store')->name('saveOtherPayment');
    Route::get('/other-payment-report',           'OtherPaymentController@createReport')->name('createReport');
    Route::post('/other-payment-report',         'OtherPaymentController@ViewReport')->name('postReport');
    Route::get('/compute-payment',                 'OtherPaymentController@index');
    Route::post('/compute-payment',             'OtherPaymentController@computePayment')->name('postCreateReport');




    // Get staff by division under Person Emolument page
    Route::post('/personal-emolument/division/staffs2',       'EmolumentController@staffToDisplay2');

    Route::post('/collect/staff-detail2',           'EmolumentController@populateLGA');
    Route::post('/collect/staff-detail-temp',           'EmolumentController@populateLGA2');
    Route::post('/collect/append2',           'EmolumentController@append');
    Route::post('/collect/append/temp',           'EmolumentController@append2');
    Route::post('/personal-emolument/get-lga2',                        'EmolumentController@getlga');
    // Route::post('/personal-emolument/findStaffAfterUpdate2',   'EmolumentController@findStaffAfterUpdate2')->name('emolumentUpdates');
    Route::post('/personal-emolument/findStaffAfterInsert',   'EmolumentController@findStaffAfterInsert');
    Route::post('/personal-emolument/findStaff2',             'EmolumentController@findStaff2');
    Route::post('/staff/personal-emolument/update2',         'EmolumentController@update_emolument2');
    Route::post('/staff/personal-emolument/update3',         'EmolumentController@update_emolument3');


    Route::get('/other-payment',             'OtherPaymentController@index')->name('otherPayment');
    Route::post('/other-payment',             'OtherPaymentController@store')->name('saveOtherPayment');
    Route::get('/other-payment-report',           'OtherPaymentController@createReport')->name('createReport');
    Route::post('/other-payment-report',         'OtherPaymentController@ViewReport')->name('postReport');
    Route::get('/compute-payment',                 'OtherPaymentController@index');
    Route::post('/compute-payment',             'OtherPaymentController@computePayment')->name('postCreateReport');


    //Get payment disparity across any two selected months.


    Route::get('payment-disparity', [PayrollController::class, 'checkPaymentDifferenceByMonths'])->name('checkPaymentDifferenceByMonths');
    Route::post('payment-disparity', [PayrollController::class, 'checkNewPersonnel'])->name('checkNewPersonnel');
    // Route::get('/payment-disparity',             'OtherPaymentController@index')->name('otherPayment');



    Route::any('user-management',                         [BasicParameterController::class, 'Usermanagement']);
    Route::any('users-management',                         [BasicParameterController::class, 'Usermanagement']);
    // Route::post('basic/rank-designation',         [BasicParameterController::class, 'UpdateRankDesignation']);
    // Route::get('basic/rank-designation',         [BasicParameterController::class, 'UpdateRankDesignation']);








    Route::get('find-staff-record', [SearchUserController::class, 'searchStaff'])->name('staff.search');
    Route::post('find-staff-record/', [SearchUserController::class, 'retrieve'])->name('staff.retrieve');
    Route::get('find-staff/{q?}', [SearchUserController::class, 'autocomplete'])->name('staff.autocomplete');

    //company information
    Route::get('/company/info',       [BasicParameterController::class, 'companyInfo']);
    Route::post('/company/info',      [BasicParameterController::class, 'companyInfo']);


    ////////////////// statrt judges salary setup by adams //////////////////////////////////////////////
    Route::get('/judge-con-salary/create',  [ConSalarySetupController::class, 'judgeCreate']);
    Route::post('/judge-con-salary/create',   [ConSalarySetupController::class, 'judgeSaveSalary']);
    Route::any('/judge-structure-upload',   [BasicParameterController::class, 'judgeSetup']);

    //payroll:- salary setup
    Route::get('/salary/create',  [SalarySetupController::class, 'Create']);
    //Route::post('/salary/create', [SalarySetupController::class, 'display']);
    Route::post('/salary/create',   [SalarySetupController::class, 'saveSalary']);

    //payroll:- Control Variables
    Route::post('/courts/retrieve', [ControlVariableController::class, 'getDivisions']);
    Route::post('/cv/session',     [ControlVariableController::class, 'getDStaffInfo']);
    Route::post('/variable/setSession',     [ControlVariableController::class, 'sessionset']);
    Route::post('/variable/findStaff', [ControlVariableController::class, 'findStaff']);
    //Route::get('/variable/create', [ControlVariableController::class, 'create']);
    Route::post('/variable/store', [ControlVariableController::class, 'update']);
    Route::get('/variable/view/{fileNo}', [ControlVariableController::class, 'view']);
    //Control Variable
    Route::any('/control-variable', [ControlVariableController::class, 'index']);
    //Route::post('/control-variable/add', [ControlVariableController::class, 'index']);
    Route::post('/control-variable/update', [ControlVariableController::class, 'update']);
    Route::post('/control-variable/delete/{id}', [ControlVariableController::class, 'destroy']);

    Route::get('/computes/salary', [PayrollController::class, 'ComputeSalary']);
    Route::post('/computes/salary', [PayrollController::class, 'ComputeSalary']);

    Route::get('/computes/consolidatedsalary', [PayrollController::class, 'ComputeConsolidatedSalary']);
    Route::post('/computes/consolidatedsalary', [PayrollController::class, 'ComputeConsolidatedSalary']);
    Route::any('/computes/consolidatedsalary-council', [PayrollController::class, 'ComputeConsolidatedSalaryCouncil']);
    Route::any('/compute/special-overtime', [PayrollController::class, 'SeparateSpecialOvertime']);
    Route::any('/revalidate/chart',    [PayrollController::class, 'ChartRevalidation']);
    Route::get('/salary/structure-setup', [PayrollController::class, 'SalaryStructure']);
    Route::post('/salary/structure-setup', [PayrollController::class, 'SalaryStructure']);

    Route::get('/staffvariable', [StaffControlVariableController::class, 'index']);
    Route::get('/staffvariable-head', [StaffControlVariableController::class, 'indexHead']);
    Route::get('/staffvariable-for-head-office', [StaffControlVariableController::class, 'indexHeadOffice']);
    Route::post('/staffvariable-for-head-office', [StaffControlVariableController::class, 'indexHeadOffice']);
    Route::get('/staffvariable-approval', [StaffControlVariableController::class, 'approval']);
    Route::post('/staffvariable-approval', [StaffControlVariableController::class, 'approval']);
    Route::post('/staffvariable', [StaffControlVariableController::class, 'index']);
    Route::post('/staffvariable-head', [StaffControlVariableController::class, 'indexHead']);
    Route::get('/staffvariable/list', [StaffControlVariableController::class, 'ActiveControlVariable']);
    Route::post('/staffvariable/list', [StaffControlVariableController::class, 'ActiveControlVariable']);

    Route::any('/staff/backlog',                             [StaffControlVariableController::class, 'backlogindex']);
    // ============================================ John ========================================
    //payslip
    Route::get('/payslip/create', [PaySlipController::class, 'create']);
    Route::get('/payslip/staff-create', [PaySlipController::class, 'createStaff']);
    Route::get('/division/staff/ajax/{division_id}', [PaySlipController::class, 'loadStaffWithAjax']);
    Route::post('/payslip/create', [PaySlipController::class, 'Retrieve']);
    Route::post('/payslip/staff-create', [PaySlipController::class, 'RetrieveStaff']);

    //staffs and cooperatives
    Route::any('/staff/cooperative', [StaffCooperativeController::class, 'staffCooperative']);
    Route::get('/get-earnordeduction', [StaffCooperativeController::class, 'getEarnOrDeduction']);
    Route::get('/set-current-ed', [StaffCooperativeController::class, 'setCurED']);

    //tf1
    Route::get('treasuryf1/view',         [TreasuryF1Controller::class, 'loadView']);
    Route::get('treasuryf1-justices/view',         [TreasuryF1Controller::class, 'loadJusticesView']);
    Route::post('treasuryf1/view',         [TreasuryF1Controller::class, 'view']);
    Route::post('treasuryf1-justices/view',         [TreasuryF1Controller::class, 'viewJustices']);

    //payroll:- Consolidated salary setup
    Route::get('/con-salary/create',  [ConSalarySetupController::class, 'Create']);
    //Route::post('/con-salary/create', [ConSalarySetupController::class, 'display']);
    Route::post('/con-salary/create',   [ConSalarySetupController::class, 'saveSalary']);
    Route::any('/structure-upload',   [BasicParameterController::class, 'Setup']);

    //t209
    Route::get('treasury209/view',         [Treasury209Controller::class, 'loadView']);
    Route::get('treasury209-justices/view',         [Treasury209Controller::class, 'loadViewJustices']);
    Route::post('treasury209/view',         [Treasury209Controller::class, 'viewNew']);

    //treasury209 report
    Route::get('treasury209/report/view',         [Treasury209Controller::class, 'loadReportView']);
    Route::post('treasury209/report/view',         [Treasury209Controller::class, 'viewReportNew']);
    // Route::post('treasury209/view',         'Treasury209Controller@view');

    // Route::post('treasury209/view',         'Treasury209Controller@viewNew');

    Route::post('treasury209-justices/view',         [Treasury209Controller::class, 'viewJustices']);

    //Payroll Analysis
    Route::get('payroll/analysis',         [AnalysisController::class, 'analysis']);
    Route::post('payroll/analysis',         [AnalysisController::class, 'analysisDisplay']);

    Route::get('payroll/summary/analysis',          [AnalysisController::class, 'summaryAnalysis']);
    Route::post('payroll/summary/analysis',         [AnalysisController::class, 'summaryAnalysisDisplay']);

    Route::get('staff/cooperative-bank-sum',          [StaffCooperativeController::class, 'staffCooperativeBankSum']);
    Route::post('staff/cooperative-bank-sum',         [StaffCooperativeController::class, 'staffCooperativeBankSumDisplay']);

    Route::get('scn-payroll/summary/bank-mandate',          [AnalysisController::class, 'summaryBankMandateAnalysis']);
    Route::post('scn-payroll/summary/bank-mandate',         [AnalysisController::class, 'summaryBankMandateAnalysisDisplay']);

    //cover letter
    Route::get('coverletter/print',            [CoverletterController::class, 'index']);
    Route::post('coverletter/print',           [CoverletterController::class, 'view']);
    Route::any('/invalid/staffcontrol-variable', [StaffControlVariableController::class, 'staff']);


    // Generate Mandate Account
    Route::get('/generate/mandate/account', [ConEpaymentController::class, 'generateMandateAccountPage']);
    Route::post('/generate/mandate/account', [ConEpaymentController::class, 'generateMandateAccount']);
    Route::put('/update/mandate/account', [ConEpaymentController::class, 'updateMandateAccount']);
    Route::post('/delete/mandate/account', [ConEpaymentController::class, 'deleteMandateAccount']);

    
    Route::get('/con-epayment/export', [ConEpaymentController::class, 'exportEpaymentExcel'])
        ->name('epayment.export');

    //Payroll Summary
    Route::get('/summary/create', [SummaryController::class, 'create']);
    Route::post('/summary/create', [SummaryController::class, 'retrieve']);
    Route::get('treasury209/pension-view', [Treasury209Controller::class, 'pensionView']);

    // ------------ Tunde --------
    //Justice Payroll Summary
    Route::get('/summary/create-justice-voucher', [SummaryController::class, 'createJusticeSummaryVoucher']);
    Route::post('/summary/create-justice-voucher', [SummaryController::class, 'retrieveJusticeSummaryVoucher']);


    ///////////////// start Epayment Guideline///////////////////////////

    Route::get('/epayment/guideline', [EpaymentGuidelineController::class, 'index']);
    Route::post('/epayment/guideline/retrieve', [EpaymentGuidelineController::class, 'Retrieve']);

    // ============================================ End ==========================================


    //payslip
    // Route::get('/payslip/create', [PaySlipController::class, 'create']);
    // Route::get('/division/staff/ajax/{division_id}', [PaySlipController::class, 'loadStaffWithAjax']);
    // Route::post('/payslip/create', [PaySlipController::class, 'Retrieve']);

    //payslip Personal
    Route::get('/payslip/personal', [PaySlipController::class, 'personal']);
    Route::post('/payslip/personal', [PaySlipController::class, 'getPersonal']);

    // ================================ TUNDE =============================
    //payroll:- consolidated Salary Scale
    Route::get('/consol/salaryScale', [ConSalaryScaleController::class, 'index']);
    Route::post('/consol/salaryScale', [ConSalaryScaleController::class, 'getSalary']);
    Route::get('consol/salaryScale/{type}/{court}', [ConSalaryScaleController::class, 'customPaging']);

    Route::any('/activeMonth/lock', [PayrollController::class, 'LockPeriod']);
    Route::any('/activeMonth/unlock', [PayrollController::class, 'UnLockPeriod']);
    Route::get('/activeMonth/create1', [ActiveMonthController::class, 'create']);
    Route::post('/activeMonth/create1', [ActiveMonthController::class, 'store']);
    Route::get('payroll-salary-staff', [AssignSalaryStaffPayrollController::class, 'payroll']);

    //consolidated Epayment
    Route::get('/con-epayment', [ConEpaymentController::class, 'index']);
    Route::post('/con-epayment/retrieve', [ConEpaymentController::class, 'Retrieve']);
    Route::get('/con-epayment/retrieve', [ConEpaymentController::class, 'Retrieveget']);

    //All Banks Consolidated Epayment
    Route::get('/con-all-epayment', [ConEpaymentController::class, 'indexAllBanks']);
    Route::post('/con-all-epayment/retrieve', [ConEpaymentController::class, 'RetrieveAllBanks']);
    Route::get('/con-all-epayment/retrieve', [ConEpaymentController::class, 'RetrieveAllBanksget']);

    //consolidated Epayment Justices
    Route::get('/con-epayment-justices', [ConEpaymentController::class, 'indexJustices']);
    Route::post('/con-epayment-justices/retrieve', [ConEpaymentController::class, 'RetrieveJustices']);
    Route::post('/cv-details/update', [ConEpaymentController::class, 'UpdateControlVariable']);


    //PECARD CONSOLIDATED
    Route::get('/con-pecard/view', [ConPecardController::class, 'create']);
    Route::post('/con-pecard/view', [ConPecardController::class, 'viewCard']);
    Route::get('/con-pecard/getCard/{id?}/{year?}', [ConPecardController::class, 'getPecard']);

    //Epayment
    Route::get('/epayment', [EpaymentController::class, 'index']);
    Route::post('/epayment/retrieve', [EpaymentController::class, 'Retrieve']);
    Route::get('/epayment/retrieve', [EpaymentController::class, 'Retrieveget']);
    Route::post('/epayment/fone', [EpaymentController::class, 'getPhone']);

    //sammarise by bank
    Route::get('/summary/bybanks', [SummaryController::class, 'summaryByBank']);
    Route::post('/summary/bybanks', [SummaryController::class, 'summaryPostBank']);

    //NHF REPORTS
    Route::get('/update-nhf/{id}/{nhf}', [NHFReportController::class, 'updateNHFnumber']);

    // Route::get('/nhf/report', [NHFReportController::class, 'index']);
    // Route::post('/nhf/report', [NHFReportController::class, 'retrieve']);
    Route::get('/nhf/report/new', [NHFReportController::class, 'nhfReportIndex']);
    Route::post('/nhf/report/new', [NHFReportController::class, 'nhfReportDetails']);
    Route::get('/justiceNhf/report/new', [NHFReportController::class, 'nhfJusticeReportIndex']);
    Route::post('justiceNhf/report/new',  [NHFReportController::class, 'justiceNhfReportDetails']);

    Route::get('/nhf/remittance-attachment', [NHFReportController::class, 'nhfRemittanceAttachment']);
    Route::post('/nhf/remittance-attachment', [NHFReportController::class, 'nhfRemittanceAttachmentUpload']);
    Route::get('/nhf/remittance-attachment/download/{id}', [NHFReportController::class, 'nhfRemittanceAttachmentDownload'])->name('nhf.remittance.download');
    Route::get('/nhf/remittance-attachment/view/{id}', [NHFReportController::class, 'nhfRemittanceAttachmentView']);
    Route::delete('/nhf/remittance-attachment/delete/{id}', [NHFReportController::class, 'nhfRemittanceAttachmentDelete']);
    Route::get('/nhf/remittance-receipts', [NHFReportController::class, 'nhfRemittanceReceipts']);


    //salary schedule bank by bank
    Route::get('/schedule/bank-by-bank', [BankSalaryScheduleController::class, 'index']);
    Route::post('/schedule/bank-by-bank/retrieve', [BankSalaryScheduleController::class, 'retrieve']);

    //NSITF
    Route::get('/nsitf/view', [NSITFController::class, 'index']);
    Route::post('/nsitf/view', [NSITFController::class, 'view']);



    Route::get('/monthly-control-variable', [MonthControlVariableController::class, 'monthControlVariable']);
    Route::post('/monthly-control-variable', [MonthControlVariableController::class, 'RetrieveMonthlyControlVariable']);


    Route::get('/council-members/mandate', [CouncilMembersController::class, 'index']);
    Route::post('/council-members/mandate', [CouncilMembersController::class, 'Retrieve']);

    Route::get('/council-members/create', [CouncilMembersController::class, 'createCouncilMember']);
    Route::post('/council-members/create', [CouncilMembersController::class, 'saveCouncilMember']);
    Route::get('/edit/council-member/{id?}', [CouncilMembersController::class, 'editCouncilMember']);
    Route::post('/council-members/update',  [CouncilMembersController::class, 'updateCouncilMember']);

    Route::get('/council-member/bank-schedule', [CouncilMembersController::class, 'councilBankSchedule']);
    Route::post('/council-member/bank-schedule', [CouncilMembersController::class, 'postCouncilBankSchedule']);

    Route::get('/council-members/analysis', [CouncilMembersController::class, 'analysis']);
    Route::post('/council-members/analysis', [CouncilMembersController::class, 'viewAnalysis']);

    Route::get('/council-members/payroll', [CouncilMembersController::class, 'councilPayrollIndex']);
    Route::post('/council-members/payroll', [CouncilMembersController::class, 'councilPayrollReport']);



    Route::get('/addition-allowance', [StaffControlVariableController::class, 'additionalAllowance']);
    Route::get('/allocation-recieved', [StaffControlVariableController::class, 'allocation']);
    Route::any('/invalid/staffcontrol-variable', [StaffControlVariableController::class, 'staff']);
    Route::post('/save-allocation',  [StaffControlVariableController::class, 'saveallocation']);
    Route::post('/edit-allocation', [StaffControlVariableController::class, 'updateallocation']);
    Route::post('/delete-allocation', [StaffControlVariableController::class, 'deleteallocation']);
    Route::post('/save-otherAllowance', [StaffControlVariableController::class, 'saveadditionalAllowance']);
    Route::post('/edit-otherAllowance',  [StaffControlVariableController::class, 'editadditionalAllowance']);
    Route::post('/delete-otherAllowance', [StaffControlVariableController::class, 'deleteadditionalAllowance']);


    Route::get('/salary', [SalaryController::class, 'salary']);
    Route::get('/division-salary/{id}',  [SalaryController::class, 'SalaryDetails']);
    Route::post('/salary',  [SalaryController::class, 'RetrieveSalary']);
    Route::post('/submit-salary', [SalaryController::class, 'submitSalary']);


    Route::get('/pension-deduction-report',  [NewPensionController::class, 'viewPensionDeduction'])->name('viewPensionDeduction');
    Route::post('/pension-deduction-report-result',  [NewPensionController::class, 'viewPensionDeductionReport'])->name('viewPensionDeductionReport');


    Route::get('/staff-due/retirement/', [DueForRetirementController::class, 'create']);
    Route::get('/staff-due-for-retirement/details/{id}',    [DueForRetirementController::class, 'viewRetirementDue'])->name('view-staff-due-retirement-data');
    Route::post('/staff-due/retirement/', [DueForRetirementController::class, 'create']);
    Route::get('/get-staff-details/{fileNo}', [DueForRetirementController::class, 'getStaffDetails'])
        ->name('getStaffDetails');
    Route::post('/approve-retirement/{ID}', [DueForRetirementController::class, 'approveRetirement'])
        ->name('approveRetirement');

    Route::any('/con-payrollReport/compare-earning', [ConPayrollReportController::class, 'CompareEarning']);

    // Epayment earning and deduction by bank
    Route::get('/earning-deduction-by-bank', [ConEpaymentController::class, 'earningAndDeductionByBank']);
    Route::post('/retrieve-earning-deduction-by-bank', [ConEpaymentController::class, 'retrieveEarningAndDeduction']);

    // Justice Epayment earning and deduction by bank
    Route::get('/justice-earning-deduction-by-bank', [ConEpaymentController::class, 'justiceEarningAndDeductionByBank']);
    Route::post('/retrieve-justice-earning-deduction-by-bank', [ConEpaymentController::class, 'retrieveJusticeEarningAndDeduction']);





    // ================================ END =========================================





    //-------------------------------routes for Pitoff------------------------------------
    Route::get('/con-payrollReport/create', [ConPayrollReportController::class, 'create']);
    Route::post('/con-payrollReport/create', [ConPayrollReportController::class, 'Retrieve']);
    Route::get('/con-payrollReport/create/{division?}/{year?}/{month?}', [ConPayrollReportController::class, 'Retrieve']);
    Route::get('/con-payrollReport2/create/{division?}/{year?}/{month?}', [ConPayrollReportController::class, 'Retrieve']);
    Route::get('con-payrollReport/paid-staff-search', [ConPayrollReportController::class, 'paidStaffSearch'])->name('paid-staff.search');
    Route::post('con-payrollReport/get-paid-staff', [ConPayrollReportController::class, 'getPaidStaff'])->name('get-paid-staff');


    //staff for half payment
    Route::get('/approve-salary-newly-employed-staff',    [HalfPayStaffController::class, 'view'])->name('approveNewStaffSalary');
    Route::post('/approve-salary-newly-employed-staff',   [HalfPayStaffController::class, 'approveNewStaffSalary']);

    //staff Due For Arrears
    Route::get('/staff-due/arrears/',    [DueForArrearsController::class, 'create']);
    Route::post('/staff-due/arrears/',   [DueForArrearsController::class, 'create']);
    // Route::get('/staff-due/arrears', [DueForArrearsController::class, 'showCreateForm'])->name('arrears.create');
    // Route::post('/staff-due/arrears/search', [DueForArrearsController::class, 'searchStaff'])->name('arrears.search'); // New search route
    // Route::post('/staff-due/arrears', [DueForArrearsController::class, 'storeDueForArrears'])->name('arrears.store');
    Route::get('/staff-due/backlogs/',    [DueForArrearsController::class, 'Backlog']);
    Route::post('/staff-due/backlogs/',   [DueForArrearsController::class, 'Backlog']);
    Route::get('/staff-overdue/arrears/',    [DueForArrearsController::class, 'createOverdue']);
    Route::post('/staff-overdue/arrears/',   [DueForArrearsController::class, 'createOverdue']);
    Route::post('/staff-due/store',      [DueForArrearsController::class, 'store']);
    Route::post('/division/session',     [DueForArrearsController::class, 'divSession']);
    Route::any('/staff-due/all',         [DueForArrearsController::class, 'index']);
    Route::get('/staff-due/delete/{id}', [DueForArrearsController::class, 'destroy']);
    Route::get('/staff-due/alert',      [DueForArrearsController::class, 'alert']);
    Route::get('/staff-due/due',        [DueForArrearsController::class, 'dueForIncrement']);
    Route::post('/staff-due/due',        [DueForArrearsController::class, 'dueForIncrement']);
    Route::post('/increment/accept',    [DueForArrearsController::class, 'acceptIncrement']);
    Route::get('/staff-due/edit/{id?}',     [DueForArrearsController::class, 'edit']);

    Route::get('/con-payrollReport/arrears/{court}/{fileNo}/{year}/{month}',   [ConPayrollReportController::class, 'arrearsOearn']);
    Route::get('/con-payrollReport/arrears-test/{court}/{fileNo}/{year}/{month}',   [ConPayrollReportController::class, 'arrearsOearnTest']);

    //Assign function or ability
    Route::get('/assign-user/function',            [UserFunctionController::class, 'create'])->name('AssignUserFunction');
    Route::post('/assign-ability',                    [UserFunctionController::class, 'createAbility']);
    Route::post('/update-assign-ability',   [UserFunctionController::class, 'updateAbility']);

    //assign salary staff for payroll
    Route::get('assign-salary-staff', [AssignSalaryStaffPayrollController::class, 'index']);
    Route::post('assign-salary-staff', [AssignSalaryStaffPayrollController::class, 'store']);
    Route::post('remove-assign-salary-staff/{id}', [AssignSalaryStaffPayrollController::class, 'destroy'])->name('removeAssigned');
    Route::post('/update-assigned-salary-staff/{id}', [AssignSalaryStaffPayrollController::class, 'update']);
    Route::get('bank-assigned-to-salary/{id}', [AssignSalaryStaffPayrollController::class, 'getBanksAssignedToStaff']);


    //signatory mandate route
    Route::get('/user/signatory-mandate',  [SignatoryMandateController::class, 'displayMandateForm']);
    Route::post('/user/assign-mandate',             [SignatoryMandateController::class, 'assignMandate']);


    // Page that displays the upload form
    Route::get('/upload-bank-page', function () {
        return view('upload-bank-details');
    });

    // Route that handles the upload
    Route::post('/upload-bank-details', [UploadBankAccountDetail::class, 'uploadStaffBankDetails']);

    // Page that displays the upload form
    Route::get('/grade-and-step-check', function () {
        return view('grade-and-step-check');
    });
    // Route that handles the upload
    Route::post('/grade-and-step-check', [UploadBankAccountDetail::class, 'checkStaffGradeStep']);

    // GET: Show page with empty table
    Route::get('/cr-excel-check', function () {
        return view('cr-excel-check', [
            'merged' => [],
            'missingInExcel' => [],
            'missingInDb' => [],
            'gradeMismatchDbVsExcel' => [],
            'total_excel' => 0,
            'total_db' => 0
        ]);
    });

    // POST: Handle upload & comparison
    Route::post('/cr-excel-check', [UploadBankAccountDetail::class, 'compareFileNoAndGrade']);


    Route::post('/cr-excel-check/pdf', [UploadBankAccountDetail::class, 'exportPdf'])->name('export.pdf');


    // Page (UI)
    Route::get('/staff/education', [UploadBankAccountDetail::class, 'create'])
        ->name('staff.education.create');

    // Save education + attachments
    Route::post('/staff/education', [UploadBankAccountDetail::class, 'store'])
        ->name('staff.education.store');

    // AJAX: search staff by file number
    Route::post('/staff/search-by-file-no', [UploadBankAccountDetail::class, 'searchByFileNo'])
        ->name('staff.search.fileNo');


    Route::post('/s3/presign', [S3UploadController::class, 'generate']);





    Route::prefix('staff')->group(function () {

        // 🔹 ENTRY PAGE (THIS WAS MISSING)
        Route::get('/', [StaffEducationController::class, 'staffList'])
            ->name('staff.index');

        // Staff profile page
        Route::get('{staff}/documents', [StaffEducationController::class, 'index'])
            ->name('staff.documents');

        // Education
        Route::post('education/store', [StaffEducationController::class, 'store'])
            ->name('education.store');

        Route::post('education/{education}/verify', [StaffEducationController::class, 'verify'])
            ->name('education.verify');

        // Attachments
        Route::post('attachment/store', [StaffEducationController::class, 'storeAttachment'])
            ->name('attachment.store');
    });

    //-------------------------------end of routes for Pitoff------------------------------------

});

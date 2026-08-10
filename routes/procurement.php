<?php

use App\Http\Controllers\BanksController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\procurement\ContractorRegistrationController;
use App\Http\Controllers\procurement\BudgetItemController;
use App\Http\Controllers\procurement\BudgetCategoryController;
use App\Http\Controllers\procurement\BudgetMarketSurveyController;
use App\Http\Controllers\procurement\ContractDetailsController;
use App\Http\Controllers\procurement\ContractCategoryController;
use App\Http\Controllers\procurement\ProcurementEvaluationController;
use App\Http\Controllers\procurement\ProcurementPlanBudgetController;
use App\Http\Controllers\procurement\unitController;
use App\Http\Controllers\procurement\DepartmentController;
use App\Http\Controllers\procurement\ContractBiddingController;
use App\Http\Controllers\procurement\BiddingRequiredDocController;
use App\Http\Controllers\procurement\StoreController;
use App\Http\Controllers\procurement\ReportController;
use App\Http\Controllers\procurement\ItemRequestController;
use App\Http\Controllers\procurement\ApproveItemRequestController;
use App\Http\Controllers\procurement\CrApproveController;
use App\Http\Controllers\procurement\UrgentRequestController;

use App\Http\Controllers\procurement\TechnicalEvaluationController;
use App\Http\Controllers\procurement\AwardLetterController;
use App\Http\Controllers\procurement\ProcurementPlanController;
use App\Http\Controllers\procurement\TendersBoardController;
use App\Http\Controllers\procurement\FedJudicialTendersBoardController;
use App\Http\Controllers\procurement\PerformaceEvaluationController;
use App\Http\Controllers\procurement\LiabilityTakingController;
use App\Http\Controllers\procurement\ConfirmCompletionController;
use App\Http\Controllers\procurement\ProjectConfirmationUnitController;
use App\Http\Controllers\procurement\SecretaryController;
use App\Http\Controllers\procurement\ThresholdController;
use App\Http\Controllers\procurement\LocalPurchaseOrderController;
use App\Http\Controllers\procurement\JobOrderController;
use App\Http\Controllers\procurement\ContractorCategoryController;
use App\Http\Controllers\procurement\BinCardController;






// Route::group(['middleware' => ['auth', 'force.password.change', 'permission']], function () {
Route::group(['middleware' => ['auth', 'force.password.change']], function () {

    /////////////////////////////////////////  John Routes ///////////////////////////////////////////////////////////////////////
    // Contractor
    Route::get('/contractor-registration/{id?}',                  [ContractorRegistrationController::class, 'createContractorRegistration'])->name('createContractorRegistration');
    Route::post('/contractor-registration',                 [ContractorRegistrationController::class, 'postContractorRegistration'])->name('postContractorRegistration');
    Route::get('/contractor-report',                        [ContractorRegistrationController::class, 'createContractorReport'])->name('contractorReport');
    Route::get('/edit-contractor/{id?}',                    [ContractorRegistrationController::class, 'getEditContractorRecord'])->name('editContractorRecord');
    Route::get('/remove-contractor/{id?}',                  [ContractorRegistrationController::class, 'removeContractorRecord'])->name('deleteContractorRecord');
    Route::get('/cancel-contractor-edit',                   [ContractorRegistrationController::class, 'cancelEditContractorRecord'])->name('cancelEditContractor');
    // Route::get('/contractor-report',                        [ContractorRegistrationController::class, 'createContractorReport'])->name('contractorReport');
    Route::get('/remove-contractor-bank/{idb?}/{idc}',      [ContractorRegistrationController::class, 'deleteContractorBank'])->name('removeContractorBank');
    Route::get('/remove-contractor-document/{idd?}/{idc}',  [ContractorRegistrationController::class, 'deleteContractorDocument'])->name('removeContractorDocument');


    Route::put('/contractor/update', [ContractorRegistrationController::class, 'updateContractorRegistration'])
        ->name('contractor.update');
    Route::delete('/contractor/document/{id}', [ContractorRegistrationController::class, 'deleteContractorDocumentNew'])
        ->name('deleteContractorDocumentNew'); // TEMMII

    Route::get('contractor/{id}/documents', [ContractorRegistrationController::class, 'getDocuments']);

    // Show remaining document types for a contractor (AJAX)
    Route::get('/contractor/{id}/remaining-doc-types', [ContractorRegistrationController::class, 'remainingDocumentTypes'])
        ->name('contractor.remainingDocTypes');

    // Store uploaded document
    Route::post('/contractor/add-document', [ContractorRegistrationController::class, 'addContractorDocument'])
        ->name('contractor.addDocument');




    //procurement plan budget
    Route::get('/add-needs',          [ProcurementPlanBudgetController::class, 'needsTitle'])->name('needsTitle');
    Route::post('/add-needs',         [ProcurementPlanBudgetController::class, 'saveNeedsTitle'])->name('saveNeedsTitle');
    Route::any('/generate-needs-pdf', [ProcurementPlanBudgetController::class, 'generatePDFNeed'])->name('generate-needs-pdf');
    Route::get('/delete-needs/{id}',  [ProcurementPlanBudgetController::class, 'deleteNeeds'])->name('deleteNeeds');
    Route::get('/delete-needs/{id}',  [ProcurementPlanBudgetController::class, 'deleteNeedsTitle'])->name('deleteNeedsTitle');
    Route::get('/open-needs/{id}',    [ProcurementPlanBudgetController::class, 'openNeedsTitle'])->name('openNeedsTitle');
    Route::get('/close-needs/{id}',   [ProcurementPlanBudgetController::class, 'closeNeedsTitle'])->name('closeNeedsTitle');
    Route::post('/update-needs',      [ProcurementPlanBudgetController::class, 'updateNeedsTitle'])->name('updateNeedsTitle');

    Route::get('/view-needs-assessment',    [ProcurementPlanBudgetController::class, 'viewNeedsAssessment'])->name('viewNeedsAssessment');
    Route::get('/categorised-needs-assessment/{id}',    [ProcurementPlanBudgetController::class, 'categorisedNeedsAssessment'])->name('categorisedNeedsAssessment');

    Route::get('/dept-needs-assessment-report',    [ProcurementPlanBudgetController::class, 'deptNeedsAssessment'])->name('deptNeedsAssessment');
    Route::post('/dept-needs-assessment-report',    [ProcurementPlanBudgetController::class, 'needAssessmentReport'])->name('needs.report');

    Route::get('/submit-needs',                  [ProcurementPlanBudgetController::class, 'submitNeeds'])->name('submitNeeds');
    Route::get('/get-notification',              [ProcurementPlanBudgetController::class, 'getNotification'])->name('getNotification');
    Route::post('/save-notification',        [ProcurementPlanBudgetController::class, 'saveNotification'])->name('saveNotification');
    Route::get('/delete-notification/{id}',  [ProcurementPlanBudgetController::class, 'deleteNotification'])->name('deleteNotification');
    Route::post('/mark-notification-as-read/{notificationID}', [ProcurementPlanBudgetController::class, 'markNotificationAsRead']);
    Route::get('/get-updated-notification-count', [ProcurementPlanBudgetController::class, 'getUpdatedNotificationCount']);

    Route::get('/submit-needs-assessment/{id}',    [ProcurementPlanBudgetController::class, 'submitNeedsAssessment'])->name('submitNeedsAssessment');
    Route::get('/get-all-needs',                    [ProcurementPlanBudgetController::class, 'getAllNeeds'])->name('get-all-needs');
    Route::get('/get-item-from-category',           [ProcurementPlanBudgetController::class, 'getItemFromCategory'])->name('getItemFromCategory');
    Route::post('/view-needs-assessment',           [ProcurementPlanBudgetController::class, 'saveNeedsAssessment'])->name('saveNeedsAssessment');
    Route::get('/view-needs/{id}',                  [ProcurementPlanBudgetController::class, 'viewDepartmentalNeeds'])->name('viewDepartmentalNeeds');
    Route::post('/check-item-exists-for-department', [ProcurementPlanBudgetController::class, 'checkItemExistsForDepartment'])->name('check.item.exists');

    //Route::get('/view-needs/{idbidding-required-document-setup}', 'ProcurementPlanBudgetController@viewDepartmentalNeeds')->name('viewDepartmentalNeeds');
    Route::post('/update-needs-assessment',         [ProcurementPlanBudgetController::class, 'updateNeedsAssessment'])->name('updateNeedsAssessment');
    Route::get('/delete-needs-assessment/{id}',     [ProcurementPlanBudgetController::class, 'deleteNeedsAssessment'])->name('deleteNeedsAssessment');
    //Send notification for need submission:
    Route::post('/send-notification-for-plan',      [ProcurementPlanBudgetController::class, 'sendNoticeForNeedSusmission'])->name('send-notification-for-plan');
    Route::get('/get-specifications-by-item/{itemID}', [ProcurementPlanBudgetController::class, 'getSpecificationsByItem']);

    //bidding required documents setup
    Route::get('/bidding-required-document-setup',         [BiddingRequiredDocController::class, 'bidRequiredDocSetup']);
    Route::post('/bidding-required-document-setup',         [BiddingRequiredDocController::class, 'saveBidRequiredDocSetup'])->name('saveBidRequiredDocSetup');
    Route::put('/bidding-required-document-setup/{id}',         [BiddingRequiredDocController::class, 'updateBidRequiredDocSetup'])->name('updateBidRequiredDocSetup');
    Route::delete('/remove-bidding-required-document-setup/{id}',         [BiddingRequiredDocController::class, 'removeBidRequiredDocSetup'])->name('removeBidRequiredDocSetup');


    //Units
    Route::get('/unit-creation', [unitController::class, 'create'])->name('unit');
    Route::post('/unit-saving', [unitController::class, 'store'])->name('store-unit');
    Route::get('/unit-update/', [unitController::class, 'update'])->name('update-unit');
    Route::post('/unit-update/', [unitController::class, 'update']);
    Route::get('/delete-unit/{id}', [unitController::class, 'delete'])->name('delete-unit');


    //DEPARTMENT
    Route::get('/department',                            [DepartmentController::class, 'show'])->name('department');
    Route::post('/department',                            [DepartmentController::class, 'update'])->name('updateDepartment');
    Route::post('/delete-department',                            [DepartmentController::class, 'delete'])->name('deleteDepartment');


    //Contract Bidding Routes
    Route::get('/add-bidding',                             [ContractBiddingController::class, 'create']);
    Route::post('/add-bidding',                         [ContractBiddingController::class, 'saveBidding']);
    Route::post('/add-bidding-document',                 [ContractBiddingController::class, 'saveBiddingDocument']);
    Route::any('/view-bidding',                         [ContractBiddingController::class, 'viewBidding']);
    Route::get('/view-submitted/{id}',                     [ContractBiddingController::class, 'viewBiddingDocuments']);
    Route::post('/fetch-bid',                             [ContractBiddingController::class, 'fetchBid']);
    Route::post('/update-bidding',                         [ContractBiddingController::class, 'updateBid']);
    Route::get('/delete-bidding/doc/{id?}',             [ContractBiddingController::class, 'deleteBiddingDoc']);
    Route::get('/edit/bid/{id?}',                         [ContractBiddingController::class, 'editBid']);
    Route::post('/bidding-update',                         [ContractBiddingController::class, 'bidUpdate']);

    // Route::get('/contractor-documents/{id}', [ContractBiddingController::class, 'getContractorDocuments'])
    Route::get('/contractor-documents/{contractorId}/{contractId}', [ContractBiddingController::class, 'getContractorDocuments'])
        ->name('contractor.getDocuments'); // TEMII

    Route::delete('/delete-contractor-doc/{id}', [ContractBiddingController::class, 'deleteDocument'])
        ->name('contractor.deleteDoc');  // TEMII

    //Procurement technical evaluation
    Route::get('/procurement-technical-evaluation', [TechnicalEvaluationController::class, 'index']);
    Route::get('/pro-procurement/tech-evaluate/{contract_id}', [TechnicalEvaluationController::class, 'viewContract']);
    Route::put('/pro-procurement/bidding/tech-evaluate/disqualify/{bidding_id?}', [TechnicalEvaluationController::class, 'disqualify']);
    Route::put('/pro-procurement/bidding/tech-evaluate/requalify/{bidding_id?}', [TechnicalEvaluationController::class, 'requalify']);
    Route::put('/pro-procurement/bidding/tech-evaluate/recommend/{contract_id?}', [TechnicalEvaluationController::class, 'recommend']);
    Route::put('/pro-procurement/bidding/tech-evaluate/recommend/reverse/{contract_id?}', [TechnicalEvaluationController::class, 'reverseRecommend']);
    Route::post('/pro-procurement/tech-evaluate/to-block/{contract_id?}', [TechnicalEvaluationController::class, 'blockTechnical']);
    //after cancel bid, you can renable from here
    Route::get('/canceled-contract-list', 'ProcurementEvaluationController@disabledContractList');
    Route::post('/renable-contract/{id}', 'ProcurementEvaluationController@renableContract');

    //Procurement Route
    Route::get('/pro-procurement', [ProcurementEvaluationController::class, 'contract']);
    Route::post('/pro-procurement', [ProcurementEvaluationController::class, 'search'])->name('pro-procurement.search');

    Route::get('/requalify-bids/{id}', [ProcurementEvaluationController::class, 'requalifyView']);
    Route::get('/pro-procurement/contract/{contract_id}', [ProcurementEvaluationController::class, 'viewContract']);
    Route::put('/pro-procurement/bidding/disqualify/{bidding_id?}', [ProcurementEvaluationController::class, 'disqualify']);
    Route::put('/pro-procurement/bidding/requalify/{bidding_id?}', [ProcurementEvaluationController::class, 'requalify']);
    Route::post('/pro-procurement/approve/{contract_id?}', [ProcurementEvaluationController::class, 'approve']);
    Route::post('/pro-procurement/to-tenders/{contract_id?}', [ProcurementEvaluationController::class, 'tenders']);
    Route::post('/pro-procurement/to-f-tenders/{contract_id?}', [ProcurementEvaluationController::class, 'ftenders']);
    Route::post('/pro-procurement/to-block/{contract_id?}', [ProcurementEvaluationController::class, 'block']);
    Route::get('/procurement/award/{approvalID}', [ProcurementEvaluationController::class, 'award']);
    Route::get('/procurement/award-letters', [ProcurementEvaluationController::class, 'letters']);
    Route::put('/pro-procurement/bidding/recommend/{contract_id?}', [ProcurementEvaluationController::class, 'recommend']);

    //Liability taking
    Route::get('/contract-comments/{id}',                             [ProcurementEvaluationController::class, 'comments']);



    Route::get('/bin-card', [BinCardController::class, 'index'])->name('bin.card');
    Route::get('/item-in-store-category', [BinCardController::class, 'indexItemInStoreCategory'])->name('bin.item-in-store');
    Route::get('/get-items', [BinCardController::class, 'getItems'])->name('get.items');

    //Award letter issuance
    Route::get('/view-contract-lists',          [AwardLetterController::class, 'viewContractlist']);
    Route::get('/award-letter/{id}',            [AwardLetterController::class, 'approveBidlist'])->name('approve_bidlist');
    Route::post('/recall-letter',               [AwardLetterController::class, 'recallLetter'])->name('recall-letter');
    Route::post('/push-to-secretary',           [AwardLetterController::class, 'pushtoSecretary'])->name('push-to-secretary');
    Route::post('/save-award-letter',           [AwardLetterController::class, 'saveAwardletter'])->name('save-award-letter');
    Route::post('/save-agreement-letter',       [AwardLetterController::class, 'saveAgreementletter'])->name('save-agreement-letter');
    Route::get('/view-letter/{id}',             [AwardLetterController::class, 'viewletter'])->name('view-letter');
    Route::get('/edit-letter/{id}',             [AwardLetterController::class, 'editletter'])->name('edit-letter');
    Route::post('/update-award-letter',         [AwardLetterController::class, 'updateAwardletter'])->name('update-award-letter');
    Route::get('/view-agreed-letter/{id}',      [AwardLetterController::class, 'viewAgreedletter']);
    Route::get('/confirm-agreement/{id}',       [AwardLetterController::class, 'confirmAgreement'])->name('confirm-agreement');

    // Main upload letters page
    Route::get('/upload-letters', [AwardLetterController::class, 'index'])->name('upload-letters.index');
    Route::get('/upload-letters/create/{id}', [AwardLetterController::class, 'create'])->name('upload-letters.create');
    Route::post('/upload-letters/upload', [AwardLetterController::class, 'upload'])->name('upload-letters.upload');
    Route::post('/upload-letters/upload-multiple', [AwardLetterController::class, 'uploadMultiple'])->name('upload-letters.upload-multiple');
    Route::get('/upload-letters/download/{id}', [AwardLetterController::class, 'download'])->name('upload-letters.download');
    Route::get('/upload-letters/view/{id}', [AwardLetterController::class, 'view'])->name('upload-letters.view');
    Route::delete('/upload-letters/delete/{id}', [AwardLetterController::class, 'delete'])->name('upload-letters.delete');

    // recommendation and award letter send email
    // Route::post('/upload-letters/send-email/{id}', [AwardLetterController::class, 'sendEmail'])->name('upload-letters.send-email');
    Route::match(['post', 'get'], '/upload-letters/send-email/{id}', [AwardLetterController::class, 'sendEmail'])->name('upload-letters.send-email');
    Route::post('/upload-letters/send-bulk-emails', [AwardLetterController::class, 'sendBulkEmails'])->name('upload-letters.send-bulk-emails');
    Route::get('/upload-letters/email-logs/{id?}', [AwardLetterController::class, 'emailLogs'])->name('upload-letters.email-logs');
    Route::get('/fix-filenames', [AwardLetterController::class, 'fixFilenames']);


    Route::get('/categories', [ContractorCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [ContractorCategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/check-duplicate', [ContractorCategoryController::class, 'checkDuplicate'])->name('categories.checkDuplicate');
    Route::get('/categories/{id}/edit', [ContractorCategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categories/{id}/update', [ContractorCategoryController::class, 'update'])->name('categories.update');
    Route::get('/categories/{id}/delete', [ContractorCategoryController::class, 'destroy'])->name('categories.destroy');

    // LPO Routes
    Route::get('/lpo', [LocalPurchaseOrderController::class, 'index'])->name('lpo.index');
    Route::get('/lpo/create', [LocalPurchaseOrderController::class, 'create'])->name('lpo.create');
    Route::post('/lpo', [LocalPurchaseOrderController::class, 'store'])->name('lpo.store');
    Route::get('/lpo/{id}', [LocalPurchaseOrderController::class, 'show'])->name('lpo.show');
    Route::get('/lpo/{id}/edit', [LocalPurchaseOrderController::class, 'edit'])->name('lpo.edit');
    Route::put('/lpo/{id}', [LocalPurchaseOrderController::class, 'update'])->name('lpo.update');
    Route::post('/lpo/{id}/issue', [LocalPurchaseOrderController::class, 'issue'])->name('lpo.issue');
    Route::post('/lpo/{id}/sign-hod', [LocalPurchaseOrderController::class, 'signHOD'])->name('lpo.sign-hod');
    Route::post('/lpo/{id}/receive-goods', [LocalPurchaseOrderController::class, 'receiveGoods'])->name('lpo.receive-goods');
    Route::get('/lpo/{id}/print', [LocalPurchaseOrderController::class, 'print'])->name('lpo.print');
    Route::delete('/lpo/{id}', [LocalPurchaseOrderController::class, 'destroy'])->name('lpo.destroy');

    // Job Order Routes
    Route::get('/job-order', [JobOrderController::class, 'index'])->name('job-order.index');
    Route::get('/job-order/create', [JobOrderController::class, 'create'])->name('job-order.create');
    Route::post('/job-order', [JobOrderController::class, 'store'])->name('job-order.store');
    Route::get('/job-order/{id}', [JobOrderController::class, 'show'])->name('job-order.show');
    Route::get('/job-order/{id}/edit', [JobOrderController::class, 'edit'])->name('job-order.edit');
    Route::put('/job-order/{id}', [JobOrderController::class, 'update'])->name('job-order.update');
    Route::post('/job-order/{id}/issue', [JobOrderController::class, 'issue'])->name('job-order.issue');
    Route::post('/job-order/{id}/complete', [JobOrderController::class, 'complete'])->name('job-order.complete');
    Route::get('/job-order/{id}/print', [JobOrderController::class, 'print'])->name('job-order.print');
    Route::delete('/job-order/{id}', [JobOrderController::class, 'destroy'])->name('job-order.destroy');

    //Liability taking
    Route::get('/contracts-coments/{id}',                             [ContractBiddingController::class, 'comments']);


    ///////////////////////////////// END ////////////////////////////////////////////////////////////////////////


    // ===================================== TUNDE ====================================

    //Budget Items, Category, Item Specification and market_ survey
    Route::get('/create-budget-item', [BudgetItemController::class, 'createItem'])->name('createBudgetItem');
    Route::post('/create-budget-item', [BudgetItemController::class, 'saveItem'])->name('saveBudgetItem');
    Route::post('/check-duplicate-item', [BudgetItemController::class, 'checkDuplicate'])->name('checkDuplicateItem');

    Route::get('/add-specification-item', [BudgetItemController::class, 'itemSpecification'])->name('itemSpecification');
    Route::post('/add-specification-item', [BudgetItemController::class, 'saveItemSpecification'])->name('saveItemSpecification');
    Route::post('/update-specification', [BudgetItemController::class, 'updateItemSpecification'])->name('updateItemSpecification');
    Route::get('/delete-specification/{id}', [BudgetItemController::class, 'deleteItemSpecification'])->name('deleteItemSpecification');

    Route::get('/budget-category-item/{iID?}', [BudgetItemController::class, 'deleteItem'])->name('deleteItem');


    Route::get('/budget-category/create',  [BudgetCategoryController::class, 'createCategory'])->name('createBudgetCategory');
    Route::post('/budget-category/create',  [BudgetCategoryController::class, 'saveCategory'])->name('createBudgetCategory');
    Route::get('/budget-category-delete/{cID?}',    [BudgetCategoryController::class, 'deleteCategory'])->name('deleteCategory');

    Route::get('/create-budget-market-survey', [BudgetMarketSurveyController::class, 'createSurvey'])->name('createBudgetSurvey');

    Route::post('/create-budget-market-survey', [BudgetMarketSurveyController::class, 'saveSurvey'])->name('saveBudgetSurvey');
    Route::get('/delete-all-item-surveys/{itemId}', [BudgetMarketSurveyController::class, 'deleteAllItemSurveys'])->name('deleteAllItemSurveys');

    Route::get('/export-market-survey', [BudgetMarketSurveyController::class, 'exportSurveyExcel'])->name('exportBudgetSurvey');

    Route::get('/getItemsByCategory', [BudgetCategoryController::class, 'createSurvey'])->name('getItemsByCategory');
    Route::get('/getSpecByItem', [BudgetMarketSurveyController::class, 'createSurvey'])->name('getSpecByItem');
    Route::get('/get-items-by-category/{itemID}', [BudgetMarketSurveyController::class, 'getItemsByCategory'])->name('getItemsByCategory');
    Route::get('/get-specification-by-item/{itemID}', [BudgetMarketSurveyController::class, 'getSpecByItem'])->name('getSpecByItem');

    Route::get('/get-specifications-by-item', [BudgetMarketSurveyController::class, 'getSpecificationsByItem'])->name('getSpecificationsByItem');

    Route::get('/budget-market-survey-delete/{msID?}', [BudgetMarketSurveyController::class, 'deleteMarketSurvey'])->name('deleteMSurvey');

    Route::any('/generate-pdf', [BudgetMarketSurveyController::class, 'generatePDF'])->name('generate-pdf'); // adams
    Route::get('/archive-generate-pdf', [BudgetMarketSurveyController::class, 'archiveGeneratePDF'])->name('archive-generate-pdf'); // adams
    Route::get('/budget-market-survey/archive', [BudgetMarketSurveyController::class, 'allBudgetSurveyArchive'])->name('budgetSurveyArchive');
    Route::get('/show-budget-market-survey/archive/{id}', [BudgetMarketSurveyController::class, 'getBudgetSurveyArchive'])->name('singleSurveyArchive');


    //Contract Details
    Route::get('/contract-details',   [ContractDetailsController::class, 'createContractDetails'])->name('contractDetails');
    Route::post('/contract-details',                    [ContractDetailsController::class, 'postContractDetails'])->name('postDetails');
    Route::get('/remove-contract-details/{id?}',        [ContractDetailsController::class, 'removeContractDetails'])->name('deleteContractDetails');
    Route::get('/edit-contract-details/{id?}',          [ContractDetailsController::class, 'getEditContractDetails'])->name('editContractDetails');
    Route::get('/cancel-contract-details-edit',         [ContractDetailsController::class, 'cancelEditContractDetails'])->name('cancelEditContractDetails');
    Route::get('/contract-report',                      [ContractDetailsController::class, 'createContractReport'])->name('contractReport');
    Route::get('/contract-list',                        [ContractDetailsController::class, 'createContractList'])->name('contractList');

    Route::get('/contracts/{id}/documents', [ContractDetailsController::class, 'getDocuments']);

    ///Search Product From DB JSON
    Route::get('/search-contract-from-db-JSON/{query?}', [ContractDetailsController::class, 'searchContractFromDB']);
    Route::get('report/search-contract-report',        [ContractDetailsController::class, 'searchContract'])->name('searchContractReport');
    Route::get('/contracts/{contractDetailsID}/memoir-documents', [ContractDetailsController::class, 'getMemoirDocuments'])->name('contracts.memoir-documents');
    Route::post('/contracts/{contractDetailsID}/upload-memoir-files', [ContractDetailsController::class, 'uploadMemoirFilesModal'])->name('contracts.upload-memoir-files');
    Route::delete('/contracts/memoir-file/{fileId}/delete', [ContractDetailsController::class, 'deleteMemoirFile'])->name('contracts.delete-memoir-file');

    Route::put('/pro-procurement/bidding/recommend/{contract_id}', [ProcurementEvaluationController::class, 'recommend'])->name('recommend.bid');

    //Contract Category
    Route::get('/contract-category',  [ContractCategoryController::class, 'createContractCategory'])->name('createContractCategory');
    Route::post('/contract-category', [ContractCategoryController::class, 'postContractCategory'])->name('postContractCategory');
    Route::get('/remove-contract-category/{id?}', [ContractCategoryController::class, 'removeContractCategory'])->name('deleteContractCategory');

    //after cancel bid, you can renable from here
    Route::get('/canceled-contract-list', [ProcurementEvaluationController::class, 'disabledContractList']);
    Route::post('/renable-contract/{id}', [ProcurementEvaluationController::class, 'renableContract']);


    // Threshold Routes - Only View and Update
    // Route::get('/thresholds', [ThresholdController::class, 'index'])->name('thresholds.index');
    // Route::get('/threshold/{id}/edit', [ThresholdController::class, 'edit'])->name('threshold.edit');
    // Route::put('/threshold/{id}', [ThresholdController::class, 'update'])->name('threshold.update');
    // Route::delete('/threshold/{id}', [ThresholdController::class, 'destroy'])->name('threshold.destroy');


    Route::get('/thresholds', [ThresholdController::class, 'index'])->name('thresholds.index');
    Route::post('/threshold', [ThresholdController::class, 'store'])->name('threshold.store');
    Route::get('/threshold/{id}/edit', [ThresholdController::class, 'edit'])->name('threshold.edit');
    Route::put('/threshold/{id}', [ThresholdController::class, 'update'])->name('threshold.update');
    Route::delete('/threshold/{id}', [ThresholdController::class, 'destroy'])->name('threshold.destroy');


    //Procurement Plan
    Route::get('/procurement-plan',       [ProcurementPlanController::class, 'procurementPlan']);
    Route::get('/procurement-new-plan', [ProcurementPlanController::class, 'newprocurementPlan']);


    Route::post('/procurement-plan', [ProcurementPlanController::class, 'procurementPlanAction']);

    Route::post('/procurement-new-plan', [ProcurementPlanController::class, 'procurementPlanAction']);

    Route::get('/procurement-records', [ProcurementPlanController::class, 'procurementRecords']);
    Route::post('/procurement-records', [ProcurementPlanController::class, 'searchProcurementRecords']);
    Route::get('/view/procurement-plan', [ProcurementPlanController::class, 'procurementPlanReport'])->name('procurement-plans');
    Route::get('/procurement-new-form', [ProcurementPlanController::class, 'newForm'])->name('newForm');
    Route::get('/view/procurement-plan/{id?}', [ProcurementPlanController::class, 'viewProcurementPlan']);
    Route::get('/edit/procurement-plan/{id?}', [ProcurementPlanController::class, 'editProcurementPlan']);
    Route::post('/edit/procurement-plan', [ProcurementPlanController::class, 'updateProcurementPlan']);
    Route::get('/export/procurement-plan/{id?}', [ProcurementPlanController::class, 'exportPlan'])->name('exportPlan');



    //STORE
    Route::get('/store',    [StoreController::class, 'index'])->name('store');
    Route::post('/approve-bidding', [StoreController::class, 'approveBidding'])->name('bidding.approve');
    Route::post('/assign-user', [StoreController::class, 'assignUser'])->name('bidding.assign.user');
    Route::get('/store/bidding/{id}', [StoreController::class, 'viewBidding'])->name('bidding.view');

    Route::get('/assign-items', [StoreController::class, 'assignedStoreItems'])->name('assign.items');
    Route::post('/assign-items/accept/{id}', [StoreController::class, 'acceptAssignItem'])->name('assign.items.accept');
    Route::get('/store/items/{id}', [StoreController::class, 'itemInputPage'])->name('store.itemInputPage');
    Route::get('/insert/items', [StoreController::class, 'storeItemPage'])->name('store.itemPage');
    Route::post('/store/save-item-qty/{id}', [StoreController::class, 'saveItemQty'])->name('store.saveItemQty');
    Route::post('/store/save-item', [StoreController::class, 'saveItem'])->name('store.saveItem');
    Route::get('/purchase/items', [StoreController::class, 'listReceivedContracts'])->name('contracts.received.list');
    Route::get('/view/{biddingStoreid}/items', [StoreController::class, 'viewItemsForContract'])->name('contracts.received-items.view');
    Route::post('/items/approve', [StoreController::class, 'approve'])->name('items.approve');
    Route::get('/get-approve-modal/{id}', [StoreController::class, 'approveModal'])->name('items.approve-modal');
    Route::get('/get-specs/{itemID}', [StoreController::class, 'getItemSpecifications']);

    // STORE REPORT
    Route::get('/reports/items-in-store', [ReportController::class, 'itemsInStoreReport'])->name('reports.items_in_store');
    Route::get('/reports/items-summary',  [ReportController::class, 'itemsSummaryReport'])->name('reports.items_summary');

    // STORE - Items Balance (Received/Issued/Balance upto date)
    Route::get('/reports/items-balance', [StoreController::class, 'itemsBalanceReport'])->name('reports.items_balance');

    // Add Item to Store
    Route::get('/store/add-item', [StoreController::class, 'addItemToStore'])->name('store.add-item');
    Route::post('/store/add-item', [StoreController::class, 'saveItemToStore'])->name('store.save-item-to-store');
    Route::get('/store/get-available-quantity/{itemId}', [StoreController::class, 'getAvailableQuantity'])->name('store.get-available-quantity');



    
    // item-Request-adams dept to store
    Route::get('item-request', [ItemRequestController::class, 'index'])->name('item-request');
    Route::get('/item-request/list', [ItemRequestController::class, 'listItemRequests'])->name('item-request-list');
    Route::get('/item-request/items/{id}', [ItemRequestController::class, 'fetchRequestItems'])->name('fetch-request-items');
    Route::post('/item-request/item/add', [ItemRequestController::class, 'addRequestItem'])->name('add-request-item');
    Route::post('/item-request/item/update-quantity', [ItemRequestController::class, 'updateRequestItemQuantity'])
        ->name('update-item-request-quantity');
    Route::post('/item-request/item/delete', [ItemRequestController::class, 'deleteRequestItem'])->name('delete-request-item');
    Route::post('/item-request/delete', [ItemRequestController::class, 'deleteRequest'])->name('delete-item-request');
    Route::post('/item-request/approve/{id}', [ItemRequestController::class, 'approveItemRequest'])->name('approve-item-request');

    // item-Request-adams - store to cr
    Route::get('/item-request-list-submited', [ItemRequestController::class, 'submittedItemRequestList'])->name('submitted-item-request-list');
    Route::get('/submitted-item-request/{id}', [ItemRequestController::class, 'viewSubmittedItemRequest'])
        ->name('submitted-item-request-view');
    Route::post('/submitted-item-request/recommend', [ItemRequestController::class, 'recommendSubmittedItemRequest'])
        ->name('submitted-item-request-recommend');
    Route::post('/submitted-item-request/reopen', [ItemRequestController::class, 'reopenSubmittedItemRequest'])
        ->name('submitted-item-request-reopen');


    // item-Request-adams - cr to store
    Route::get('/item-request-list-recommended-by-store', [ItemRequestController::class, 'recommendedItemRequestList'])->name('recommend-item-request-list');
    Route::get('/recommended-item-request/{id}', [ItemRequestController::class, 'viewRecommendItemRequest'])
        ->name('recommended-item-request-view');
    Route::post('/submitted-item-request-approved-by-cr', [ItemRequestController::class, 'approveRecommendSubmittedItemRequest'])
        ->name('submitted-item-request-approved-by-cr');
    Route::post('/recommended-item-request/reopen', [ItemRequestController::class, 'reopenApproveedRecommendSubmittedItemRequest'])
        ->name('recommended-item-request-reopen');


    // item-Request-adams - store to Dept
    Route::get('/approved-item-request-list-by-cr', [ItemRequestController::class, 'approvedItemRequestList'])
        ->name('approved-item-request-list');
    Route::get('/approved-item-request-view/{id}', [ItemRequestController::class, 'viewApprovedItemRequest'])
        ->name('approved-item-request-view');
    Route::post('/approved-item-request-issue', [ItemRequestController::class, 'issueApprovedItemRequest'])
        ->name('issue-approved-item-request');







    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::get('/item-request/view/{id}', [ItemRequestController::class, 'viewItemRequest'])->name('view-item-request');
    Route::post('item-request', [ItemRequestController::class, 'saveItemRequest'])->name('saveitem-request');
    Route::get('/specifications/{itemId}', [ItemRequestController::class, 'getSpecificationsByItem']);


    //Cr-Approve-itemRequest
    Route::get('cr-approve', [CrApproveController::class, 'index'])->name('cr-approve-itemrequest');

    //Department-request-items
    Route::get('department-request-items', [CrApproveController::class, 'requestItemsByDepertment'])->name('department-itemrequest');

    //////////////////////////////////STORE CATEGORY ////////////////////////////////////////
    Route::get('/store-category',    [StoreController::class, 'createStoreCategory'])->name('store-category');
    Route::post('/store-category/store', [StoreController::class, 'storeCat'])->name('store-category.store');
    Route::post('/store-category/update', [StoreController::class, 'storeCatUpdate'])->name('store-category.update');
    Route::post('/store-category/delete/{id}', [StoreController::class, 'storeCatDestroy'])->name('store-category.delete');


    //Approve-itemRequest
    Route::get('approve-request', [ApproveItemRequestController::class, 'index'])->name('approve-item-request');
    Route::post('/item-request/update-status', [ApproveItemRequestController::class, 'updateDelivered'])->name('item-request.updateStatus');

    //Urgent_Request
    Route::get('urgent-request', [UrgentRequestController::class, 'index'])->name('urgent-Request');
    Route::post('urgent-request', [UrgentRequestController::class, 'saveAndDeliverRequest'])->name('urgent-request.deliver');


    //tenders boarder
    Route::get('/view-bidded-contracts', [TendersBoardController::class, 'display']);
    Route::post('/view-bidded-contracts', [TendersBoardController::class, 'searchContract']);
    Route::get('/preview-all-bids/{contractID}', [TendersBoardController::class, 'previewBids']);
    Route::post('/get/docs', [TendersBoardController::class, 'getDocs']);
    Route::post('/contract/awarding', [TendersBoardController::class, 'award']);
    Route::post(' /contract-award/reverse',  [TendersBoardController::class, 'reverse']);
    Route::post(' /adjust/contract/amount',  [TendersBoardController::class, 'adjustAount']);
    Route::post(' /final-contract/award',  [TendersBoardController::class, 'finalApproval']);
    Route::post(' /contract-approval/reversal', [TendersBoardController::class, 'approvalReversal']);
    Route::post(' /contract-approval/rejection', [TendersBoardController::class, 'approvalRejection']);

    //Federal Judicial tenders boarder
    Route::get('/view-contracts/fed-tenders', [FedJudicialTendersBoardController::class, 'display']);
    Route::post('/view-contracts/fed-tenders', [FedJudicialTendersBoardController::class, 'searchContract']);
    Route::get('/preview-all-bids/fed-tenders/{contractID}', [FedJudicialTendersBoardController::class, 'previewBids']);
    Route::post('/get/docs',  [FedJudicialTendersBoardController::class, 'getDocs']);
    Route::post('/contract-awarding/fed-tenders', [FedJudicialTendersBoardController::class, 'award']);
    Route::post(' /contract-award/reversal',  [FedJudicialTendersBoardController::class, 'reverse']);
    Route::post(' /adjust-amount/fed-tenders', [FedJudicialTendersBoardController::class, 'adjustAount']);
    Route::post(' /final-contract-award/fed-tenders', [FedJudicialTendersBoardController::class, 'finalApproval']);
    Route::post(' /contract-approval/reversal-fed-tenders', [FedJudicialTendersBoardController::class, 'approvalReversal']);

    //Agreement letter issuance
    Route::get('/agreement-letter-view', [PerformaceEvaluationController::class, 'viewList']);
    Route::get('/agreement-letter/{id}',  [PerformaceEvaluationController::class, 'agreementlist'])->name('agreement_bidlist');
    Route::post('/agreement-letter', [PerformaceEvaluationController::class, 'generateAgreementletter'])->name('generate-agreement-letter');
    Route::get('/generate-letter/{id}', [PerformaceEvaluationController::class, 'generateletter'])->name('generate-letter');
    Route::get('/view-agreement-letter/{id}', [PerformaceEvaluationController::class, 'viewAgreementletter'])->name('view-agreement-letter');
    Route::get('/edit-agreement-letter/{id}', [PerformaceEvaluationController::class, 'editAgreementletter'])->name('view-agreement-letter');
    Route::post('/update-agreement-letter', [PerformaceEvaluationController::class, 'updateAgreementletter'])->name('update-agreement-letter');
    Route::get('/view-bidding-document/{id}', [PerformaceEvaluationController::class, 'viewBiddingdocument'])->name('view-bidding-document');
    Route::post('/return-letter',  [PerformaceEvaluationController::class, 'returnLetter'])->name('return-letter');
    Route::post('/reverse-letter', [PerformaceEvaluationController::class, 'reverseLetter'])->name('reverse-letter');
    Route::post('/update-agreement-document', [PerformaceEvaluationController::class, 'updateAgreementdocument'])->name('update-agreement-document');
    Route::post('/add-agreement-document', [PerformaceEvaluationController::class, 'addAgreementdocument'])->name('add-agreement-document');
    Route::post('/remove-agreement-document', [PerformaceEvaluationController::class, 'removeAgreementdocument'])->name('remove-agreement-document');
    Route::get('/push-agreement-letter/{id}', [PerformaceEvaluationController::class, 'pushAgreementletter']);

    //Liability taking
    Route::get('/contracts-for-libility', [LiabilityTakingController::class, 'approvedContracts']);
    Route::post('/contracts-for-libility', [LiabilityTakingController::class, 'moveToFinanceDirector']);



    //Payment request
    Route::get('/confirm-completion', [ConfirmCompletionController::class, 'ApproveContracts'])->name('confirm-completion');
    Route::get('/upload-payment-request/{id}', [ConfirmCompletionController::class, 'uploadPaymentRequest'])->name('uploadPaymentRequest');
    Route::post('/confirm-completion', [ConfirmCompletionController::class, 'push'])->name('push');
    Route::post('/confirm-file-upload', [ConfirmCompletionController::class, 'uploadRequest'])->name('upload-request');
    Route::get('/view-contract-list/{id}', [ConfirmCompletionController::class, 'viewContractList'])->name('viewcontractList');

    //Confirmation of Items/Projects
    Route::get('/confirm/list-contract', [ProjectConfirmationUnitController::class, 'contractList'])->name('contractList');
    Route::get('/confirm/create/{id?}',  [ProjectConfirmationUnitController::class, 'createConfirm'])->name('createConfirmation');
    Route::post('/confirm/create', [ProjectConfirmationUnitController::class, 'confirmProcess'])->name('postConfirmation');
    Route::get('/confirm/upload-file/{id?}', [ProjectConfirmationUnitController::class, 'projectCompletionUploadFile'])->name('attachMoreFile');
    Route::get('/file/remove/{id?}', [ProjectConfirmationUnitController::class, 'deleteProjectUploadFIle'])->name('deleteProjectFile');


    //Bank Route
    Route::resource('banks', 'BanksController');
    // Route::resource('status', 'StatusController');


    //Secretary approval section
    Route::get('/view-all-contract-list', [SecretaryController::class, 'viewContracts'])->name('incoming-file');
    Route::get('/secretary-approved-list', [SecretaryController::class, 'secretaryApprovedList'])->name('secretary-approved-list');
    Route::get('/view-list/{id}', [SecretaryController::class, 'viewBiddings']);
    Route::post('/view-list',    [SecretaryController::class, 'updateAmount'])->name('updateAmt');
    Route::post('/approve-contractor', [SecretaryController::class, 'approveContractor']);
    Route::post('/remove-contractor', [SecretaryController::class, 'removeContractor']);
    Route::post('/approve', [SecretaryController::class, 'approve'])->name('approve');
    Route::post('/reject',   [SecretaryController::class, 'reject'])->name('reject');
    Route::post('/approve-bidder',  [SecretaryController::class, 'approveBidder'])->name('approveBidder');


    //Contract Payment Report
    // Route::get('/contract-payment/search', [ContractDetailsController::class, 'searchContractPayment']);


    // Route::get('/search-contract-payment', [ContractDetailsController::class, 'searchContractPaymentTransaction'])->name('contract.payment.search');

    Route::get('/contract-payment/autocomplete', [ContractDetailsController::class, 'autocompleteFileNo'])
        ->name('contract.payment.autocomplete');

    Route::get(
        '/contract-payment/search',
        [ContractDetailsController::class, 'searchContractPaymentTransaction']
    )->name('contract.payment.search');





    // =============================================== END =====================================================================
});

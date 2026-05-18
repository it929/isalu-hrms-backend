<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use Carbon\Carbon;
use Entrust;
use Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TaxMatterController extends functionController
{

	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware('auth');
                return $next($request);
        });
    }
    
    public function getTaxDescription()
    {
        return DB::table('tax_matter_description')->where('active', 1)->get();
    }
    
    //Update record
    public function postRecordUpdate(Request $request)
    {
        $data['message']    = "Successfully updated !";
        $description        = $request['paymentDescription'];
        $transactionID      = $request['recordID'];
        $descriptionID      = $request['descriptionID']; 
        //
        $data['success'] = DB::table('tblpaymentTransaction')->where('ID', $transactionID)->update(['tax_report_description' => $descriptionID]);
        //
        return $data;
    }
    
    public function postRevertDescription(Request $request)
    {
        $data['message']    = "Successfully reverted !";
        $transactionID      = $request['recordID'];
        // 
        $data['getPreviousDescription'] = DB::table('tblpaymentTransaction')->where('ID', $transactionID)->value('paymentDescription');
        $data['success'] = DB::table('tblpaymentTransaction')->where('ID', $transactionID)->update(['tax_report_description' => '' ]);
        //
        return $data;
    }
    
    
    //Start view Tax/WHT Report
    public function postSearchReport(Request $request)
    {
        //Unset Session Variables
        Session::forget('startDate');
        Session::forget('endDate');
        Session::forget('recordType');
        Session::forget('taxElement');
        Session::forget('pageNumber');
        //
        Session::put('startDate', $request['startDate']);
        Session::put('endDate', $request['endDate']);
        Session::put('recordType', $request['recordType']);
        Session::put('taxElement', $request['taxWhtElement']);
        Session::put('pageNumber', $request['numberRecordPerPage']);
        //
        return redirect()->route('viewTaxMatterReport');
    }
    
    
    public function showTaxMatterReport()
    {
        //get Session Variables
        $startDate  = (empty(Session::get('startDate')) ? date('Y-m-d') : Session::get('startDate'));
        $endDate    = (empty(Session::get('endDate')) ? date('Y-'.('m'. - 1) .'-d') : Session::get('endDate'));
        $recordType = Session::get('recordType');
        $taxElement = Session::get('taxElement');
        $pageNumber = ((Session::get('pageNumber') > 0) ? Session::get('pageNumber') : 20);
        Session::forget('queryReport');
        
        $data       = array();
        $data['getReportDetails'] = array(); 
        $data['elementType'] = null;
        $data['recordType'] = $recordType;
        $data['taxElement'] = $taxElement;
        $data['MonthOfReport'] =  date_format(date_create($endDate), "F, Y");
        
        //query
        $queryData = $this->getQueries($startDate, $endDate, $taxElement, $recordType, $pageNumber);

	    $data['getReportDetails']   = $queryData['getReportDetails'];
        $data['elementType']        = $queryData['elementType'];
	    
	    //
        $data['allTaxdescription']  = $this->getTaxDescription();
        $data['pageNumber']         = $pageNumber;
        Session::put('queryReport', $data['getReportDetails']);
        Session::put('sDate', $startDate);
        Session::put('eDate', $endDate);
        Session::put('tElement', $taxElement);
        Session::put('rType', $recordType);
        Session::put('pNumber', $pageNumber);
        
	    //
	    return view('nicnModuleViews.taxMatter.viewTaxReport', $data);
    }
    
    
    //export_tax_matter_report
    public function exportRecordSoftcopy()
    {
        $data = array();
      
    	// Execute the query used to retrieve the data. 
	    Excel::create('Tax-Matter-Records-SoftCopy-'.(new \DateTime())->format('Y-m-d_H-i-s'), function($excel) 
	    {
	        $excel->sheet('Sheet 1', function($sheet) 
	        {   
                $totalTaxAmount = 0.0;
                $totalContractAmount = 0.0;
	            
                $query = $this->getQueries(Session::get('sDate'), Session::get('eDate'), Session::get('tElement'), Session::get('rType'), Session::get('pNumber'));
                $queryRecord = $query['getReportDetails'];
            
	            if($queryRecord)
	            { 
    	            foreach($queryRecord as $exportValue) 
    	            {
    	                //Add All records to an array object
        	           $data[] = array(
        	               $export = $exportValue->beneficiaryTin,
        	               $export = $exportValue->beneficiaryName,
        	               $export = $exportValue->beneficiaryAddress,
        	               $export = '',
        	               $export = $exportValue->committedDate,
        	               $export = ($exportValue->reportDescription != '' ? $exportValue->reportDescription : $exportValue->paymentDescription),
        	               $export = number_format($exportValue->contractAmount, 2),
        	               $export = ($query['elementType'] != null) ? $query['elementType'] : "VAT",
        	               $export = $exportValue->taxPercent,
        	               $export = number_format($exportValue->taxAmount, 2),
        	               $export = $exportValue->committedDate,
        	           );
                       $totalContractAmount += $exportValue->contractAmount;
                       $totalTaxAmount += $exportValue->taxAmount;
    	            }//foreach
    	            
    	            //Add Total Amount
    	            $data[] = array(
        	            $export = '',
            	        $export = '',
            	        $export = '',
            	        $export = '',
            	        $export = '',
            	        $export = 'TOTAL',
            	        $export = number_format($totalContractAmount, 2),
            	        $export = '',
            	        $export = '',
            	        $export = number_format($totalTaxAmount, 2),
            	        $export = '',
        	        );
	            }else{
	                $data[] = array(
        	           $export = '',
        	           $export = '',
        	           $export = '',
        	           $export = '',
        	           $export = '',
        	           $export = '',
        	           $export = '',
        	           $export = '',
        	           $export = '',
        	           $export = 0,
        	           $export = '',
        	       );    
	            }//endif

	            // Set the spreadsheet title, creator, and description
		        $sheet->setTitle('Tax-Matter SoftCopy');
		     
		        // Define the Excel spreadsheet headers
	            $sheet->fromArray($data, null, 'A1', false, false);
	            $headers = array('BENEFICIARY TIN', 'BENEFICIARY NAME', 'BENEFICIARY ADDRESS', 'INVOICE NO', 'CONTRACT DATE', 'CONTRACT DESCRIPTION', 'CONTRACT AMOUNT', 'CONTRACT TYPE', (($query['elementType'] != null) ? $query['elementType'] : "VAT"). ' RATE%', (($query['elementType'] != null) ? $query['elementType'] : "VAT"). ' AMOUNT', 'PERIOD CONVERED');
	            $sheet->prependRow(1, $headers);

		   });
		   Session::forget('queryReport');
	    })->export('xls');
        
    }
    
    
    
    
    
    //Queries - Tax-matter
    public function getQueries($startDate, $endDate, $taxElement, $recordType, $pageNumber)
    {
         //Start Database Query
	    if($recordType == 1) //Not Committed Records
	    {
	        if($taxElement == 1) //select VAT only
	        {
	           
	           $data['elementType'] = 'VAT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '<', 2)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.VAT', '>', 0)
	    		->select('tblpaymentTransaction.VAT as taxPercent', 'tblpaymentTransaction.VATValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);

	        }elseif($taxElement == 2) //select WHT only
	        {
	            
	           $data['elementType'] = 'WHT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '<', 1)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.WHT', '>', 0)
	    		->select('tblpaymentTransaction.WHT as taxPercent', 'tblpaymentTransaction.WHTValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }elseif($taxElement == 3) //select STAMP DUTY only
	        {
	            
	           $data['elementType'] = 'STD';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '<', 1)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.stampdutypercentage', '>', 0)
	    		->select('tblpaymentTransaction.stampdutypercentage as taxPercent', 'tblpaymentTransaction.stampduty as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }else{
	           
	           $data['elementType'] = 'VAT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '<', 2)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.VAT', '>', 0)
	    		->select('tblpaymentTransaction.VAT as taxPercent', 'tblpaymentTransaction.VATValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }
	    
	    }elseif($recordType == 2) //Comment Records
	    {
	        if($taxElement == 1) //select VAT only
	        {
	            
	           $data['elementType'] = 'VAT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '>', 1)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.VAT', '>', 0)
	    		->select('tblpaymentTransaction.VAT as taxPercent', 'tblpaymentTransaction.VATValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }elseif($taxElement == 2) //select WHT only
	        {
	            
	           $data['elementType'] = 'WHT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '>', 1)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.WHT', '>', 0)
	    		->select('tblpaymentTransaction.WHT as taxPercent', 'tblpaymentTransaction.WHTValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }elseif($taxElement == 3) //select STAMP DUTY only
	        {
	           
	           $data['elementType'] = 'STD';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '>', 1)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.stampdutypercentage', '>', 0)
	    		->select('tblpaymentTransaction.stampdutypercentage as taxPercent', 'tblpaymentTransaction.stampduty as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }else{
	            
	           $data['elementType'] = 'VAT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '<', 2)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.VAT', '>', 0)
	    		->select('tblpaymentTransaction.VAT as taxPercent', 'tblpaymentTransaction.VATValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }
	        
	    }elseif($recordType == 3) // Paid Records
	    {
	        if($taxElement == 1) //select VAT only
	        {
	            
	           $data['elementType'] = 'VAT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '>', 1)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.VAT', '>', 0)
	    		->select('tblpaymentTransaction.VAT as taxPercent', 'tblpaymentTransaction.VATValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }elseif($taxElement == 2) //select WHT only
	        {
	           
	           $data['elementType'] = 'WHT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '>', 1)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.WHT', '>', 0)
	    		->select('tblpaymentTransaction.WHT as taxPercent', 'tblpaymentTransaction.WHTValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }elseif($taxElement == 3) //select STAMP DUTY only
	        {
	            
	           $data['elementType'] = 'STD';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '>', 1)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.stampdutypercentage', '>', 0)
	    		->select('tblpaymentTransaction.stampdutypercentage as taxPercent', 'tblpaymentTransaction.stampduty as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }else{
	            
	           $data['elementType'] = 'VAT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.status', '<', 2)
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.VAT', '>', 0)
	    		->select('tblpaymentTransaction.VAT as taxPercent', 'tblpaymentTransaction.VATValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }
	    }else{
	        //All records
	        if($taxElement == 1) //select VAT only
	        {
	            
	           $data['elementType'] = 'VAT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.VAT', '>', 0)
	    		->select('tblpaymentTransaction.VAT as taxPercent', 'tblpaymentTransaction.VATValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }elseif($taxElement == 2) //select WHT only
	        {
	            
	           $data['elementType'] = 'WHT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.WHT', '>', 0)
	    		->select('tblpaymentTransaction.WHT as taxPercent', 'tblpaymentTransaction.WHTValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }elseif($taxElement == 3) //select STAMP DUTY only
	        {
	            
	           $data['elementType'] = 'STD';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.stampdutypercentage', '>', 0)
	    		->select('tblpaymentTransaction.stampdutypercentage as taxPercent', 'tblpaymentTransaction.stampduty as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }else{
	            
	           $data['elementType'] = 'VAT';
	           $data['getReportDetails'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('tax_matter_description', 'tblpaymentTransaction.tax_report_description', '=', 'tax_matter_description.descriptionID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$startDate ." 00:00:00", $endDate." 23:59:59"])
	    		->where('tblpaymentTransaction.companyID', '<>', 13)
	    		->where('tblpaymentTransaction.VAT', '>', 0)
	    		->select('tblpaymentTransaction.VAT as taxPercent', 'tblpaymentTransaction.VATValue as taxAmount', 'tblcontractDetails.beneficiary as beneficiaryDetailsName', 
	    		'tblcontractor.contractor as beneficiaryName', 'tblcontractor.TIN as beneficiaryTin', 'tblVATWHTPayee.address as beneficiaryAddress', 
	    		'tblpaymentTransaction.datePrepared as createdDate', 'tblpaymentTransaction.dateTakingLiability as committedDate', 'tblpaymentTransaction.dateTakingLiability as paidDate',
	    		'tblpaymentTransaction.paymentDescription as paymentDescription', 'tax_matter_description.tax_description as reportDescription', 'tblpaymentTransaction.tax_report_description as taxDescriptionID',
	    		'tblpaymentTransaction.totalPayment as contractAmount', 'tblpaymentTransaction.ID as transactionID')
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate($pageNumber);
	        }
	    }
	    
	    return $data;
    }//end function
    
	
}//End class

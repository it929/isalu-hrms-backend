<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers;
use carbon\carbon;
use Session;
use DB;

class CompanyPurchaseDayBook extends BasefunctionController
{

    public function createPurchaseDayBookPage()
    {
        //dd($this->getUserRole());
        
      $data['allContractors'] = DB::table('tblcontractor')
      ->where('type', 1)
      ->where('status', 1)
      ->orderBy('contractor', 'Asc')
      ->get();
      
        $data['dateFrom']   = Session::get('dateFrom');
        $data['dateTo']     = Session::get('dateTo');
        $data['paymentType'] = Session::get('paymentType');
        $data['companyID']  = Session::get('companyID');
        
        //$data['accountType']  = Session::get('accountType');
        if($this->getUserRole()->roleID == 16)
        {
            $data['accountType'] = 1;
        }else if($this->getUserRole()->roleID == 17)
        {
            $data['accountType'] = 4;
        }else{
            $data['accountType'] = 0;
        }
        
        
        if($data['companyID'] and $data['paymentType'] and $data['paymentType'] <> 2)
        {
            if( $data['accountType'] <> 0)
            {
                $data['getCompanyLedger'] = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
                ->leftJoin('tblcontractType','tblcontractType.ID','=', 'tblpaymentTransaction.contractTypeID')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->where('tblpaymentTransaction.contractTypeID',  $data['accountType'])
                ->where('tblpaymentTransaction.WHTValue', '>', 0)
                ->where('tblpaymentTransaction.VATValue', '>', 0)
                ->where('tblpaymentTransaction.cpo_payment', $data['paymentType'])
                ->where('tblcontractor.id', $data['companyID'])
                ->whereBetween('tblpaymentTransaction.datePrepared', [$data['dateFrom'], $data['dateTo']])
                ->select('*','tblpaymentTransaction.status as payStatus', 'tblpaymentTransaction.ID as transactionID', 'tbleconomicCode.economicCode')
                ->orderBy('datePrepared', 'desc')
                ->get();
            
            }else{
                $data['getCompanyLedger'] = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
                 ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->where('tblpaymentTransaction.WHTValue', '>', 0)
                ->where('tblpaymentTransaction.VATValue', '>', 0)
                ->where('tblpaymentTransaction.cpo_payment', $data['paymentType'])
                ->where('tblcontractor.id', $data['companyID'])
                ->whereBetween('tblpaymentTransaction.datePrepared', [$data['dateFrom'], $data['dateTo']])
                ->select('*','tblpaymentTransaction.status as payStatus', 'tblpaymentTransaction.ID as transactionID', 'tbleconomicCode.economicCode')
                ->orderBy('datePrepared', 'desc')
                ->get();
            }
            
        }elseif($data['companyID'] or $data['paymentType'] == 2 or $data['paymentType'] == null)
        {
            if( $data['accountType'] <> 0)
            {
                $data['getCompanyLedger'] = DB::table('tblpaymentTransaction')
                    ->leftJoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
                    ->leftJoin('tblcontractType','tblcontractType.ID','=', 'tblpaymentTransaction.contractTypeID')
                    ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                    ->where('tblpaymentTransaction.contractTypeID',  $data['accountType'])
                    ->where('tblpaymentTransaction.WHTValue', '>', 0)
                    ->where('tblpaymentTransaction.VATValue', '>', 0)
                    ->where('tblcontractor.id', $data['companyID'])
                    ->whereBetween('tblpaymentTransaction.datePrepared', [$data['dateFrom'], $data['dateTo']])
                    ->select('*','tblpaymentTransaction.status as payStatus', 'tblpaymentTransaction.ID as transactionID', 'tbleconomicCode.economicCode')
                    ->orderBy('datePrepared', 'desc')
                    ->get();
            }else{
                $data['getCompanyLedger'] = DB::table('tblpaymentTransaction')
                    ->leftJoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
                    ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                    ->where('tblpaymentTransaction.WHTValue', '>', 0)
                    ->where('tblpaymentTransaction.VATValue', '>', 0)
                    ->where('tblcontractor.id', $data['companyID'])
                    ->whereBetween('tblpaymentTransaction.datePrepared', [$data['dateFrom'], $data['dateTo']])
                    ->select('*','tblpaymentTransaction.status as payStatus', 'tblpaymentTransaction.ID as transactionID', 'tbleconomicCode.economicCode')
                    ->orderBy('datePrepared', 'desc')
                    ->get();
            }
        }else{
            $data['getCompanyLedger'] = array();
        }
         foreach ($data['getCompanyLedger'] as $key => $value) {
            $lis = (array) $value;
            $lis['balance'] = $this->VoucherFinancialInfo($value->transactionID);
            $value = (object) $lis;
            $data['getCompanyLedger'][$key]  = $value;
        }
        
       return view('companyPurchaseDayBook.purchaseDayBook', $data);
    }
    
    
    public function processCompanyDayBook(Request $request)
    {
        Session::forget('dateFrom');
        Session::forget('dateTo');
        Session::forget('paymentType');
        Session::forget('companyID');
        
        Session::put('dateFrom', date('Y-m-d', strtotime(trim($request['getFrom']))));
        Session::put('dateTo', date('Y-m-d', strtotime(trim($request['getTo']))));
        Session::put('companyID', (trim($request['companyName'])));
        Session::put('paymentType', (trim($request['paymentType'])));
        //Session::put('accountType', (trim($request['accountType'])));

        /*$data['dayBook'] = DB::table('tblpaymentTransaction')
          ->leftJoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
          //->where('tblpaymentTransaction.voucher_status', '=', 1)
          ->where('tblpaymentTransaction.WHTValue', '>', 0)
          ->where('tblpaymentTransaction.VATValue', '>', 0)
          ->whereBetween('datePrepared', [$datefrom, $dateTo])
          ->orderBy('datePrepared', 'desc')
          ->get();*/

       return redirect()->route('createPurchaseDayBookPage');
    }


   
    


}

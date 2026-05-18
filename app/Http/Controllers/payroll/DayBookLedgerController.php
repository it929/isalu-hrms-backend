<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers;
use carbon\carbon;
use DB;

class DayBookLedgerController extends BasefunctionController
{

    public function dayBook()
    {
        if($this->getUserRole()->roleID == 16)
        {
            $data['accountType'] = 1;
        }else if($this->getUserRole()->roleID == 17)
        {
            $data['accountType'] = 4;
        }else{
            $data['accountType'] = 1;
        }
        
      $data['dayBook'] = DB::table('tblpaymentTransaction')
          ->leftJoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
           ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
          ->where('tblpaymentTransaction.contractTypeID',  $data['accountType'])
          ->where('tblpaymentTransaction.WHTValue', '>', 0)
          ->where('tblpaymentTransaction.VATValue', '>', 0)
          ->select('*', 'tblpaymentTransaction.ID as voucherCode')
          ->orderBy('datePrepared', 'desc')
          ->paginate(50);
      
       return view('ledgerBook.daybook', $data);
    }
    public function postDayBook(Request $request)
    {
        $datefrom = date('Y-m-d', strtotime(trim($request['getFrom'])));
        $dateTo   = date('Y-m-d', strtotime(trim($request['getTo'])));

        $request->session()->flash('retain_from',$request['getFrom']);
        $request->session()->flash('retain_to',$request['getTo']);

       if($this->getUserRole()->roleID == 16)
        {
            $data['accountType'] = 1;
        }else if($this->getUserRole()->roleID == 17)
        {
            $data['accountType'] = 4;
        }else{
            $data['accountType'] = 1;
        }
    
      $data['dayBook'] = DB::table('tblpaymentTransaction')
          ->leftJoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
           ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
          ->where('tblpaymentTransaction.contractTypeID',  $data['accountType'])
          ->where('tblpaymentTransaction.WHTValue', '>', 0)
          ->where('tblpaymentTransaction.VATValue', '>', 0)
          ->whereBetween('datePrepared', [$datefrom, $dateTo])
           ->select('*', 'tblpaymentTransaction.ID as voucherCode')
          ->orderBy('datePrepared', 'desc')
          ->get();
      //dd($data['dayBook']);
       return view('ledgerBook.daybook', $data);
    }


    public function ledger()
    {
    $date = date('F');
    /*$data['ledger'] = DB::table('tblpaymentTransaction')
        ->Join('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
        //->where('tblpaymentTransaction.voucher_status', '=', 1)
        ->where('tblpaymentTransaction.WHTValue', '>', 0)
        ->where('tblpaymentTransaction.VATValue', '>', 0)
        ->select('*','tblpaymentTransaction.status as payStatus')
        ->paginate(50);*/
        if($this->getUserRole()->roleID == 16)
        {
            $data['accountType'] = 1;
        }else if($this->getUserRole()->roleID == 17)
        {
            $data['accountType'] = 4;
        }else{
            $data['accountType'] = 1;
        }

        $data['ledger'] = DB::table('tblpaymentTransaction')
            ->Join('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
            ->where('tblpaymentTransaction.WHTValue', '>', 0)
            ->where('tblpaymentTransaction.contractTypeID',  $data['accountType'])
            ->where('tblpaymentTransaction.VATValue', '>', 0)
            ->select('*','tblpaymentTransaction.status as payStatus')
            ->get();
        foreach ($data['ledger'] as $key => $value) {
            $lis = (array) $value;
            $lis['balance'] = $this->VoucherFinancialInfo($value->ID);
            $value = (object) $lis;
            $data['ledger'][$key]  = $value;
        }
        //dd($data['ledger']);

    return view('ledgerBook.ledger', $data);
    }

    public function postLedger(Request $request)
    {
        if($this->getUserRole()->roleID == 16)
        {
            $data['accountType'] = 1;
        }else if($this->getUserRole()->roleID == 17)
        {
            $data['accountType'] = 4;
        }else{
            $data['accountType'] = 1;
        }
        
        $status = trim($request['voucherStatus']);
        $datefrom = date('Y-m-d', strtotime(trim($request['getFrom'])));
        $dateTo   = date('Y-m-d', strtotime(trim($request['getTo'])));

        $request->session()->flash('date_from',$request['getFrom']);
        $request->session()->flash('date_to',$request['getTo']);
        $request->session()->flash('paystatus',$request['voucherStatus']);
      if($status == '') {
          $data['ledger'] = DB::table('tblpaymentTransaction')
              ->Join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
              ->where('tblpaymentTransaction.WHTValue', '>', 0)
              ->where('tblpaymentTransaction.VATValue', '>', 0)
              ->whereBetween('dateprepared', [$datefrom, $dateTo])
              ->where('tblpaymentTransaction.contractTypeID',  $data['accountType'])
              ->select('*', 'tblpaymentTransaction.status as payStatus')
              ->get();
          foreach ($data['ledger'] as $key => $value) {
              $lis = (array)$value;
              $lis['balance'] = $this->VoucherFinancialInfo($value->ID);
              $value = (object)$lis;
              $data['ledger'][$key] = $value;
          }
          return view('ledgerBook.ledger', $data);
      }
      elseif($status == 6){
          $data['ledger'] = DB::table('tblpaymentTransaction')
              ->Join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
              ->where('tblpaymentTransaction.WHTValue', '>', 0)
              ->where('tblpaymentTransaction.VATValue', '>', 0)
              ->whereBetween('dateprepared', [$datefrom, $dateTo])
              ->where('tblpaymentTransaction.status', '=', 6)
              ->where('tblpaymentTransaction.contractTypeID',  $data['accountType'])
              ->select('*', 'tblpaymentTransaction.status as payStatus')
              ->get();
          foreach ($data['ledger'] as $key => $value) {
              $lis = (array)$value;
              $lis['balance'] = $this->VoucherFinancialInfo($value->ID);
              $value = (object)$lis;
              $data['ledger'][$key] = $value;
          }
          return view('ledgerBook.ledger', $data);
      }

      elseif($status == 2){
          $data['ledger'] = DB::table('tblpaymentTransaction')
              ->Join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
              ->where('tblpaymentTransaction.WHTValue', '>', 0)
              ->where('tblpaymentTransaction.VATValue', '>', 0)
              ->whereBetween('dateprepared', [$datefrom, $dateTo])
              ->where('tblpaymentTransaction.status', '<', 6)
               ->where('tblpaymentTransaction.contractTypeID',  $data['accountType'])
              ->select('*', 'tblpaymentTransaction.status as payStatus')
              ->get();
          foreach ($data['ledger'] as $key => $value) {
              $lis = (array)$value;
              $lis['balance'] = $this->VoucherFinancialInfo($value->ID);
              $value = (object)$lis;
              $data['ledger'][$key] = $value;
          }
          return view('ledgerBook.ledger', $data);
      }
    }


}

<?php

namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Auth;

class MandateApprovalProcessController extends Controller
{
    function getUser($approvedfield,$mandateStatus)
    {
        $u = DB::table('tblpaymentTransaction')
            ->join('tblepayment','tblepayment.transactionID','=','tblpaymentTransaction.ID')
            ->join('tblmandate_approval','tblmandate_approval.batch','=','tblepayment.batch')
            ->join('tblmandate_comments','tblmandate_comments.batch','=','tblepayment.batch')
            ->join('users','users.username','=',"tblmandate_comments.$approvedfield")
            ->select('tblmandate_comments.comment','users.name','tblmandate_comments.to_who')
            ->where('tblpaymentTransaction.pay_confirmation','=',1)
            ->where('tblpaymentTransaction.cpo_payment','=',1)
            ->where('tblpaymentTransaction.mandate_status','=',$mandateStatus)
            ->where('tblmandate_approval.mandate_status','=',$mandateStatus)
            ->first();
        return $u;
    }
    public function CAMandate()
    {
        $data['cmt'] = "";
        $data['vouchers'] = DB::table('tblpaymentTransaction')
            ->join('tblepayment','tblepayment.transactionID','=','tblpaymentTransaction.ID')
            ->join('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
            ->select('*','tblpaymentTransaction.ID as TID')
            ->where('tblpaymentTransaction.pay_confirmation','=',1)
            ->where('tblpaymentTransaction.cpo_payment','=',1)
            ->where('tblepayment.mandate_status','!=',3)
            ->where('tblpaymentTransaction.mandate_status','=',0)
            ->get();
            //dd($data['vouchers']);
             foreach ($data['vouchers'] as $list)
        {


                $data['cmt'] = DB::table('tblmandate_comments')
                    ->join('users', 'users.username', '=', "tblmandate_comments.by_who")
                    ->where('tblmandate_comments.batch', '=', $list->batch)->get();

        }
        //dd( $data['vouchers']);
        $data['action'] = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
//dd( $data['vouchers']);
        return view('mandate.CAmandate', $data);
    }
    public function CAComment(Request $request)
    {
        $btn = $request['submit'];
        $batch = $request['batch'];
        $comment = $request['instruction'];
        $to = $request['attension'];
        $id = $request['id'];

        $count = DB::table('tblmandate_approval')->where('batch','=',$batch)->count();

        $action = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        if($to == 'FA')
        {
            $mandate = 3;
            $msg = 'Succesfully Approved';
        }
        elseif($to == 'DFA'){
            $mandate = 2;
            $msg = 'Successfully Transfered to Director Finance for Approval';
        }
        elseif($to == 'DDFA'){
            $mandate = 1;
            $msg = 'Successfully Transfered to Deputy Director Finance for Approval';
        }
        elseif($to == 'CA'){
            $mandate = 0;
            $msg = 'Succesfully Transfered to Director Finance for checking and clearing';
        }
        elseif($to == 'ES'){
            $mandate = 4;
            $msg = 'Succesfully Transfered to Executive Secretary for Approval';
        }
//dd($batch);
        $q = DB::table('tblaction_rank')->where('code', '=', $to)->first();
        if($count >0)
        {
            DB::table('tblmandate_approval')->where('batch','=',$batch)
                ->update(array(
                    'batch' => $batch,
                    'mandate_status' => $mandate,
                    'sent_to' => $to,
                    'updated_at' => date('Y-m-d'),
                    'cleared_by' => auth::user()->username,
                ));
            DB::table('tblmandate_comments')
                ->insert(array(
                    'batch' => $batch,
                    'to_who' => $to,
                    'updated_at' => date('Y-m-d'),
                    'by_who' => auth::user()->username,
                    'comment' => $comment,
                ));
            foreach ($id as $key => $value) {
                DB::table('tblpaymentTransaction')->where('ID', '=', $id[$key])
                    ->update(array(
                        'mandate_status' => $mandate,
                    ));
            }
            return redirect('/ca/mandate')->with('msg', "Successfully Transfered to $q->description for Verification");
        }
        else {
            DB::table('tblmandate_approval')
                ->insert(array(
                    'batch' => $batch,
                    'mandate_status' => $mandate,
                    'sent_to' => $to,
                    'updated_at' => date('Y-m-d'),
                    'cleared_by' => auth::user()->username,
                ));
            DB::table('tblmandate_comments')
                ->insert(array(
                    'batch' => $batch,
                    'to_who' => $to,
                    'updated_at' => date('Y-m-d'),
                    'by_who' => auth::user()->username,
                    'comment' => $comment,
                ));

            foreach ($id as $key => $value) {
                DB::table('tblpaymentTransaction')->where('ID', '=', $id[$key])
                    ->update(array(
                        'mandate_status' => $mandate,
                    ));
            }

            return redirect('/ca/mandate')->with('msg', "Successfully Transfered to $q->description for Verification");
        }

    }

    public function DDMandate()
    {
        $data['cmt'] = "";
        $data['vouchers'] = DB::table('tblpaymentTransaction')
            ->join('tblepayment','tblepayment.transactionID','=','tblpaymentTransaction.ID')
            ->join('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
            ->select('*','tblpaymentTransaction.ID as TID')
            ->where('tblpaymentTransaction.pay_confirmation','=',1)
            ->where('tblpaymentTransaction.cpo_payment','=',1)
            ->where('tblpaymentTransaction.mandate_status','=',1)
            ->orWhere('tblpaymentTransaction.mandate_status','=',2)
            ->orWhere('tblpaymentTransaction.mandate_status','=',4)
            ->get();
        foreach ($data['vouchers'] as $list)
        {


                $data['cmt'] = DB::table('tblmandate_comments')
                    ->join('users', 'users.username', '=', "tblmandate_comments.by_who")
                    ->where('tblmandate_comments.batch', '=', $list->batch)->get();

        }

        //dd($data['vouchers']);
        $data['comm'] = DB::table('tblpaymentTransaction')
            ->join('tblepayment','tblepayment.transactionID','=','tblpaymentTransaction.ID')
            ->join('tblmandate_comments','tblmandate_comments.batch','=','tblepayment.batch')
            ->join('tblmandate_approval','tblmandate_approval.batch','=','tblepayment.batch')
            ->join('users','users.username','=','tblmandate_comments.to_who')
            /*->join('users', function ($join) {
                $join->on('users.username', '=', 'tblbatch_comments.approved_by')->orOn('users.username', '=', 'tblbatch_comments.ddapprove')->orOn('users.username', '=', 'tblbatch_comments.dfapprove');
            })*/
            ->select('tblmandate_comments.comment','users.name','tblmandate_comments.to_who','tblmandate_approval.sent_to','tblmandate_comments.batch')
            ->where('tblpaymentTransaction.pay_confirmation','=',1)
            ->where('tblpaymentTransaction.cpo_payment','=',1)
            ->where('tblpaymentTransaction.mandate_status','=',1)
            ->where('tblmandate_approval.mandate_status','=',1)
            ->orWhere('tblmandate_approval.mandate_status','=',2)
            ->orWhere('tblmandate_approval.mandate_status','=',4)
            ->first();


           $data['ca_comm'] = $this->getUser('by_who',2);


        $data['ddcomm'] = $this->getUser('by_who',4);
        $data['action'] = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        $data['to'] = DB::table('tblmandate_approval')->where('sent_to','=',$data['action']->code)->first();
        //dd($data['to']);

     // dd( $data['ddcomm']->to_who);
        return view('mandate.ddmandate', $data);
    }


    public function DDComment(Request $request)
    {
        $btn = $request['submit'];
        $batch = $request['batch'];
        $comment = $request['instruction'];
        $to = $request['attension'];
        $id = $request['id'];
        if($to == 'FA')
        {
            $mandate = 3;
            $msg = 'Succesfully Approved';
        }
        elseif($to == 'DFA'){
            $mandate = 2;
            $msg = 'Successfully Transfered to Director Finance for Approval';
        }
        elseif($to == 'DDFA'){
            $mandate = 1;
            $msg = 'Successfully Transfered to Deputy Director Finance for Approval';
        }
        elseif($to == 'CA'){
            $mandate = 0;
            $msg = 'Succesfully Transfered to Director Finance for checking and clearing';
        }
        elseif($to == 'ES'){
            $mandate = 4;
            $msg = 'Succesfully Transfered to Executive Secretary for Approval';
        }
        $action = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        if($action->code == 'DFA')
        {
            $approve = 4;
        }
        else
        {
            $approve = '';
        }
//dd($batch);
        if($btn=='Approve') {
            DB::table('tblmandate_approval')->where('batch', '=', $batch)
                ->update(array(
                    'batch'             => $batch,
                    'mandate_status'    => $mandate,
                    'updated_at'        => date('Y-m-d'),
                    'sent_to'           => $to,
                    
                    'cleared_by'        => auth::user()->username,
                ));
            DB::table('tblmandate_comments')
                ->insert(array(
                    'batch'             => $batch,
                    'to_who'            => $to,
                    'updated_at'        => date('Y-m-d'),
                    'by_who'            => auth::user()->username,
                    'ddapprove'         => $approve,
                    'comment'           => $comment,
                ));

            foreach($id as $key=>$value)
            {
                DB::table('tblpaymentTransaction')->where('ID','=',$id[$key])
                    ->update(array(
                        'mandate_status' => $mandate,

                    ));
            }
           if($to == 'FA')
           {
               DB::table('tblepayment')->where('batch','=',$batch)
                ->update(array(
                 'mandate_status' => 3,

                ));
           }
           

             
            return redirect('/dd/mandate')->with('msg', $msg);
        }
        elseif($btn == 'Reject')
        {

        }
    }


    public function ESMandate()
    {

        $data['vouchers'] = DB::table('tblpaymentTransaction')
            ->join('tblepayment','tblepayment.transactionID','=','tblpaymentTransaction.ID')
            ->join('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
            ->select('*','tblpaymentTransaction.ID as TID')
            ->where('tblpaymentTransaction.pay_confirmation','=',1)
            ->where('tblpaymentTransaction.cpo_payment','=',1)
            ->where('tblpaymentTransaction.mandate_status','=',4)
            ->get();

        //dd($data['vouchers']);
        /*$data['comm'] = DB::table('tblpaymentTransaction')
            ->join('tblepayment','tblepayment.transactionID','=','tblpaymentTransaction.ID')
            ->join('tblbatch_comments','tblbatch_comments.batch','=','tblepayment.batch')
            ->join('users','users.username','=','tblbatch_comments.approved_by')
            ->select('tblbatch_comments.ddcomment','tblbatch_comments.ca_comment','users.name')
            ->where('tblpaymentTransaction.pay_confirmation','=',1)
            ->where('tblpaymentTransaction.cpo_payment','=',1)
            ->where('tblpaymentTransaction.mandate_status','=',2)
            ->where('tblbatch_comments.mandate','=',2)
            ->first();*/
            
            //dd($data['vouchers']);
        $data['ca_comm'] = $this->getUser('approved_by',2);

       //dd($data['ca_comm']);
        //$data['ddcomm'] = DB::table('tblmandate_comments')->where('ddapprove',4)->first();
        $data['ddcomm'] = $this->getUser('by_who',4);
        //dd( $data['ddcomm']);
        $data['action'] = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        //dd($data['action']);
        return view('mandate.esmandate', $data);
    }

    public function ESComment(Request $request)
    {
        $btn = $request['submit'];
        $batch = $request['batch'];
        $comment = $request['instruction'];
        $to = $request['attension'];
        $id = $request['id'];
//dd($batch);
        if($btn=='Approve') {
            DB::table('tblbatch_comments')->where('batch','=',$batch)
                ->update(array(

                    'dfcomment' => $comment,
                    'to_whom' => $to,
                    'mandate' => 3,
                    'approved_by'  => auth::user()->username,
                    'updated_at'  => date('Y-m-d'),
                ));

            foreach($id as $key=>$value)
            {
                DB::table('tblpaymentTransaction')->where('ID','=',$id[$key])
                    ->update(array(
                        'mandate_status' => 3,

                    ));
            }

            DB::table('tblepayment')->where('batch','=',$batch)
                ->update(array(
                    'mandate_status' => 3,

                ));
            return redirect('/dd/mandate')->with('msg', 'Successfully Approved for payment');
        }
        elseif($btn == 'Reject')
        {

        }
    }



    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}

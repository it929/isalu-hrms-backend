<?php

namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;
use DateTime;

class TourSlashLeaveController extends functions22Controller
{
    public function index(Request $request)
    {
        
        $data['success']                = "";
        $data['error']                  = "";
        $data['court']                  = $request['court'];
        $data['division']               = $request['division'];
        $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
        $data['courtList']              = $this->getCourts();
        $data['courtdivision']          = $this->getCourtDivision($request['court']);
        $data['getedj']                 = [];
        
        
        $data['courtstaff']             = [];
        $data['cvdesc']                 = $this->getDesc();
        $data['cvdesc1']                = $request['cvdesc'];
        $data['gezette']                = $request['gezette'];
        $data['startdate']             = $request['start-date'];
        $data['duedate']               = $request['due-date'];
        $data['returndate']            = $request['return-date'];
        $data['duration']               = $request['duration'];
        $data['type']                   = $request['type'];
        $data['fileNo']                 = $request['fileNo'];

        if($request['gezette'] != "" && $request['start-date'] != "" && $request['due-date'] != "" && $request['return-date'] != "" && $request['duration'] != "" && $request['type'] != "" && $request['court'] != "" && $request['division'] != ""){
            $idd = "";
            foreach($data as $key => $value){
                $$key = $value;
            }

            $datechk = $this->ajax($startdate, $duedate);
            
            $dat = explode('/', $datechk);
            //dd($dat);
            if(($startdate == $dat[0] && $startdate != "0000-00-00") && ($duedate == $dat[1] && $duedate != "0000-00-00") && ($returndate == $dat[2] && $returndate != "0000-00-00") && ($duration == $dat[3] && $duration != "0")){

                $check = DB::Table('tblTourSlashLeave')
                ->where('fileNo', $request['fileNo'])
                ->where('start_date', $startdate)
                ->where('due_date', $duedate)
                ->where('gazette', $gezette)
                ->get();               
                

                if($check){
                    DB::table('tblTourSlashLeave')
                    ->where('fileNo', $request['fileNo'])
                    ->where('start_date', $startdate)
                    ->where('due_date', $duedate)
                    ->where('gazette', $gezette)
                    ->update(
                        [
                            'courtID'       => $court, 
                            'divisionID'    => $division,
                            'fileNo'        => $fileNo,
                            'type'          => $type,
                            'start_date'    => $startdate,
                            'gazette'       => $gezette,
                            'duration'      => $duration,
                            'due_date'      => $duedate,
                            'return_date'   => $returndate
                        ]            
                    );
                    
                } else {
                    DB::table('tblTourSlashLeave')
                    ->insert(
                        [
                            'courtID'       => $court, 
                            'divisionID'    => $division,
                            'fileNo'        => $fileNo,
                            'type'          => $type,
                            'start_date'    => $startdate,
                            'gazette'       => $gezette,
                            'duration'      => $duration,
                            'due_date'      => $duedate,
                            'return_date'   => $returndate
                        ]
                    );
                }
            } else {
                $data['error'] = 'Please enter correct dates! note that due date cannot be less than beginning date';
            }
        }

        if($request['deleting']){
            DB::table('tblTourSlashLeave')
            ->where('id', $request['deleting'])
            ->delete();
        }
        
        $data['courtstaff']     = $this->getStaffinDivision( $request['court'], $request['division'] );
        
        $data['tablecontent'] = $this->getRealTableContent($request['fileNo'], $request['type']);
        
        $data['fileNo']         = $request['fileNo'];
        $data['staff']          = $this->getStaffInfo($request['fileNo']);
        $chk                    = [];
        return view('TourSlashLeave.tourleave', $data);
    }

    public function delete($id)
    {
        DB::table('tblTourSlashLeave')->where('id', '=', $id)->delete();
        return redirect('/tourslash/leave/'.$id);
    }

    public function ajax($start, $end)
    {
        
        if(strpos($start, ',') !== false){
            $start = date('Y').'-'.date('m').'-'.date('d');
        }

        if(strpos($end, ',') !== false){
            $end = date('Y').'-'.date('m').'-'.date('d');
            
        }
        
        $e      = explode('-', $end);
        $s      = explode('-', $start);
        $start  = mktime(0,0,0, $s[1], $s[2], $s[0]);//$start . ' ' . $end;
        $end    = mktime(0,0,0, $e[1], $e[2], $e[0]);
        // echo date("Y-m-d", $start) . '/' . date("Y-m-d", $end);
        // $data['dat'] = $start . '/' . $end;
        if($end < $start){
            return "0000-00-00/0000-00-00/0000-00-00/0";
        } else {
            $startdaychk = date("l", mktime(0,0,0, $s[1], $s[2], $s[0]));
            $enddaycheck =date("l", mktime(0,0,0, $e[1], $e[2], $e[0]));
            
            if($startdaychk == "Saturday" || $startdaychk == "Sunday"){
                return "0000-00-00/0000-00-00/0000-00-00/0";
            }elseif($enddaycheck == "Saturday" || $enddaycheck == "Sunday"){
                return "0000-00-00/0000-00-00/0000-00-00/0";
            } else {

               $days = $this->checkWeekends($start, $end);
                
                    //echo $end + 86400 . " ";
                    //$due = date("Y-m-d", $end + 86400); 
                $due = $end + 86400;
                $duechk = date("l", $due);
                // echo $duechk . ' ';
                if($duechk == "Saturday"){
                    $due = $due + 86400 + 86400;
                }     
                $due    = date("Y-m-d", $due); 
                $start  = date("Y-m-d", $start);
                $end    = date("Y-m-d", $end);
                return  $start . '/' . $end . '/' . $due . '/' .$days;
                //return fasview('TourSlashLeave.ajax', $data);
            }
        }
    }
}
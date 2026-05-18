<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use DB;
use Auth;
use Session;
class TimeVariablesController extends Controller
{
    	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }//
 
    public function index(Request $request){
        $variables = db::table('tbltime_variables')->get();
        return view('TimeVariables.variables')->with('variables',$variables);
        
    }
    
 public function create(Request $request){
        $this->validate($request,[
            
            'name'  =>'required|unique:tbltime_variables',
            'period' => 'required',
            'unit'   => 'required'
        ]);
        if($request->unit==1){
            $unit_name='Days';
        }
        else if($request->unit==2){
            $unit_name='Months';
        }
        else{
            $unit_name='Years';
        }
        db::table('tbltime_variables')->insert([
            'name'=> $request->name,
            'period' => $request->period,
            'unit' => $request->unit,
            'unit_name' => $unit_name
            ]);
        return redirect('/time-variables')->with('success','Variable created successfully');
        
    }
    
 public function edit(Request $request){
        $this->validate($request,[
            'id' => 'required',
            'name'  =>'required|unique:tbltime_variables,name,'.$request->id,
            'period' => 'required',
             'unit'   => 'required'
        ]);
         if($request->unit==1){
            $unit_name='Days';
        }
        else if($request->unit==2){
            $unit_name='Months';
        }
        else{
            $unit_name='Years';
        }
        db::table('tbltime_variables')->where('id',$request->id)->update([
            'name'=> $request->name,
            'period' => $request->period,
            'unit' => $request->unit,
            'unit_name' => $unit_name
            ]);
        return redirect('/time-variables')->with('success','Variable Edited successfully');
        
    }
     public function delete(Request $request){
        $this->validate($request,[
            'id' => 'required',
            
        ]);
        db::table('tbltime_variables')->where('id',$request->id)->delete();
        return redirect('/time-variables')->with('success','Variable Deleted successfully');
        
    }
    /*
    public function editBooking($id){
           $bookings = db::table('bookings')->where('bookingID',$id)->first(); 
           $getBookings = db::table('bookings')->join('room','bookings.roomID','=','room.roomID')
           ->join('room_type','bookings.room_type','=','room_type.typeID')
           ->leftjoin('customers','bookings.customerID','=','customers.customerID')
           ->select('bookings.*','room.*','customers.*','room_type.name as typeName')
           ->get();
        
        //$getPositions = GeneralController::getPositions();
        return view('bookings.bookingsView')->with('getBookings',$getBookings)->with('booking',$bookings);
    } */
   
}
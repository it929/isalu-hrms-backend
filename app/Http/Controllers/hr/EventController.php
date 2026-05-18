<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use App\Http\Requests;

use Auth;
use Session;
use App\Event;
use Illuminate\Support\Facades\DB;

class EventController extends ParentController
{
 
    public function create () 
    {
        $events = DB::table('tblevent_type')->orderBy('id', 'DESC')->get();
        //dd($events);
            return view('eventType.event', ['events'=>$events]); 
            
    }   

    public function storeEvent(Request $request)
    {
        $check = $request->get('event_type');
        
        $this->validate($request, [
            'event_type' => 'required',
            'status' => 'required',
        ]);
        
        if(DB::table('tblevent_type')->where('event_type',$check)->count()>0)
          {
               return back()->with('err', 'record already exist!');
              
          }
          else{
        
            try {
                DB::table('tblevent_type')->insert([
                    'event_type'=> $request->get('event_type'),
                    'eventStatus'=> $request->get('status')
                        ]);
                    
                    // $event->save();
                    return redirect()->back()->with('msg', 'Event Type saved!');
            } catch (\Throwable $th) {
                //throw $th;
            }
            
          }
    }
    
    public function updateEvent(Request $request)
    {
    
        $id = $request->input('eventId');
        
        $event = DB::table('tblevent_type')->find($id);
        //dd($request);
        $event_type = $request->input('new_event_type');
        $status = $request->input('new_event_status');

        DB::table('tblevent_type')->where('id', $id)->update([
            'event_type' => $event_type,
            'eventStatus' => $status
        ]);

        return redirect()->back()->with('msg', 'Event Type updated!'); 
    }
    
    public function Destroy($id)
    {
        
        $event = DB::table('tblevent_type')->where('id', $id)->first();
        if(!$event){
            return back()->with('msg', 'Event does not exist!');
        }
        DB::table('tblevent_type')->where('id', $id)->delete();
        return back()->with('msg', 'Event Type deleted!');
    }
}
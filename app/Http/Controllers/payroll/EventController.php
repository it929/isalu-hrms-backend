<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use App\Http\Requests;

use DB;
use Auth;
use Session;
use App\Event;

class EventController extends ParentController
{
 
    public function create () 
    {
        $events = DB::table('tblevent_type')->get();
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
        
        $event= new Event([
           'event_type'=> $request->get('event_type'),
           'eventStatus'=> $request->get('status')
            ]);
        
        $event->save();
        return redirect()->back()->with('msg', 'Event Type saved!');
          }
    }
    
    public function updateEvent(Request $request)
    {
    
        $id = $request->input('eventId');
        
        $event = Event::find($id);  
        //dd($request);
        $event_type = $request->input('event_type');
        $status = $request->input('status');
     
        $event->event_type = $event_type;
        $event->eventStatus = $status;

        $event->save();

        return redirect()->back()->with('msg', 'Event Type updated!'); 
    }
    
    public function Destroy($id)
    {
        
        $event = Event::find($id);
        $event->delete();

        return back()->with('msg', 'Event Type deleted!');
    }
}
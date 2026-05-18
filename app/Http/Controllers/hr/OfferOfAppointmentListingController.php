<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class OfferOfAppointmentListingController extends Controller
{
     public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    } 
    public function offerlisting()
    {
       
        $data['perList'] = DB::table('offerofappointment')->join('tblper','tblper.fileNo','=','offerofappointment.fileNo')->get();
        return view('OfferOfAppointmentList.listingoffers', $data);
    }
    public function letterlisting()
    {
         
        $data['perList'] = DB::table('letterofappointment')->join('tblper','tblper.fileNo','=','letterofappointment.fileNo')->get();
        return view('OfferOfAppointmentList.listingletters', $data);
    }
    public function acceptancelisting()
    {
        
         $data['perList'] = DB::table('acceptance')->join('tblper','tblper.fileNo','=','acceptance.fileNo')->get();
        return view('offerOfAppointmentList.listingacceptance',$data);
    }
    public function medicallisting()
    {
        
        $data['perList'] = DB::table('medicalexam')->join('tblper','tblper.fileNo','=','medicalexam.fileNo')->get();
   
        return view('offerOfAppointmentList.listingmedicals',$data);
    }

     public function autocomplete_STAFF(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblper')
                ->where('divisionID', $this->divisionID)
                ->where('surname', 'LIKE', '%'.$query.'%')
                ->orWhere('first_name', 'LIKE', '%'.$query.'%')
                ->orWhere('fileNo', 'LIKE','%'.$query.'%')
                ->take(6)
                ->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames.' - '.$s->fileNo, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }
public function filter_acceptance(Request $request)
    {
        $filterBy = trim($request['fileNo']); 
        if($filterBy == null){
            return redirect('/offerOfAppointmentList/listacceptance')->with('err', 'No record found !');
        }
        $data['perList'] = DB::table('tblper')
                ->join('acceptance','acceptance.fileNo','=','tblper.fileNo')
                ->where('tblper.surname', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.first_name', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.fileNo', 'LIKE','%'.$filterBy.'%')
                ->orderBy('tblper.surname', 'Asc')
                ->paginate(20);
        return view('offerOfAppointmentList.listingacceptance', $data);
        
    }
    public function filter_offerletters(Request $request)
    {
        $filterBy = trim($request['fileNo']); 
        if($filterBy == null){
            return redirect('/offerOfAppointmentList/listoffer')->with('err', 'No record found !');
        }
        $data['perList'] = DB::table('tblper')
                ->join('letterofappointment','letterofappointment.fileNo','=','tblper.fileNo')
                ->where('tblper.surname', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.first_name', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.fileNo', 'LIKE','%'.$filterBy.'%')
                ->orderBy('tblper.surname', 'Asc')
                ->paginate(20);
        return view('offerOfAppointmentList.listingacceptance', $data);
        
    }

    public function filter_appointmentletters(Request $request)
    {
        $filterBy = trim($request['fileNo']); 
        if($filterBy == null){
            return redirect('/offerOfAppointmentList/listletters')->with('err', 'No record found !');
        }
        $data['perList'] = DB::table('tblper')
                ->join('letterofappointment','letterofappointment.fileNo','=','tblper.fileNo')
                ->where('tblper.surname', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.first_name', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.fileNo', 'LIKE','%'.$filterBy.'%')
                ->orderBy('tblper.surname', 'Asc')
                ->paginate(20);
        return view('offerOfAppointmentList.listingacceptance', $data);
        
    }

    public function filter_medicals(Request $request)
    {
        $filterBy = trim($request['fileNo']); 
        if($filterBy == null){
            return redirect('/offerOfAppointmentList/listmedicals')->with('err', 'No record found !');
        }
        $data['perList'] = DB::table('tblper')
                ->join('medicalexam','medicalexam.fileNo','=','tblper.fileNo')
                ->where('tblper.surname', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.first_name', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.fileNo', 'LIKE','%'.$filterBy.'%')
                ->orderBy('tblper.surname', 'Asc')
                ->paginate(20);
        return view('offerOfAppointmentList.listingmedicals', $data);
        
    }


     public function autocomplete(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblper')->where('surname', 'LIKE', '%'.$query.'%')->
            orWhere('first_name', 'LIKE', '%'.$query.'%')->orWhere('fileNo', 'LIKE','%'.$query.'%')->take(15)->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames.' - '.$s->fileNo, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }

    public function showAll(Request $request)
    {
        $term=$request->input('nameID');
        DB::enableQueryLog();
        $data = DB::table('tblper')
            ->leftJoin('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblper.fileNo', '=', $term)
            ->select('fileNo','surname', 'first_name', 'othernames', 'fileNo', 'division','Designation','gender') 
            ->get();
        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

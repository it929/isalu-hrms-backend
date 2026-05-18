<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use Auth;
use DB;

class CommentReportController extends Controller
{
    protected $userID;

    //Contruct
    public function __construct()
    {
        $this->middleware('auth');
        $this->userID = (Auth::check() ? Auth::user()->id : null);
    }

    
    //create comment report
    public function index(Reqeust $request, $comment = null)
    {
        $data['comments'] = (empty($request['comment']) ? $comment : $request['comment']);
        return view('comment.allcomments', $data);
    }

}//end class

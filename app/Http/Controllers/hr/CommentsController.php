<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
use Auth;
use Illuminate\Support\Facades\Log;

class CommentsController extends Controller
{
   public function commentTemplate()
   {
   	return view('comment.allcomments');
   }
}
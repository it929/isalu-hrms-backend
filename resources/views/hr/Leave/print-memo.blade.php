@extends('layouts.layout')

@section('pageTitle')
  	LEAVE MEMO
@endsection

@section('content')
 <div class="box box-default" style="border: none;">
    <div class="box-body box-profile" style="margin:10px 20px;">

		<div class="row">
		    <div class="col-xs-12" style="margin:5px 30px;">
    		    <div class="col-xs-2">
        			<!-- <div align="right">
        				<img src="{{ asset('Images/njc-logo.jpg') }}" alt=" " class="img-responsive" width="90" />
        			</div> -->
    			</div>
    			<div align="right" class="col-xs-10">
        			<div align="right" class="text-success green-color">
        				<strong>ISALU HOSPITAL LIMITED<br>ABUJA</strong>

        			</div>
    			</div>
			</div>

			<div class="col-md-12">
            <div class="row">
		    <div class="col-md-12">
		        <div align="center" class="green-color">
		            <h4 style="text-decoration: underline; color:green; font-size:24px"><strong>MEMORANDUM  </strong></h4>
		        </div>
		        <br />
		    </div>
        </div>
			    <br />
    			<div class="col-xs-12">
    			    <div style="margin-left:-50px;font-size:18px" class="green-color">

    			        <strong>
                            <ol>
    			            <table>

                            <tr> <td><div class="row"> <div class="col-md-2">TO: </div> <div class="col-md-10 resize" style="padding-left:10px;">{{ $LeaveHistory->surname}} {{ $LeaveHistory->first_name}} {{ $LeaveHistory->othernames}}</div></div></td></tr>

                            <tr><td> <div class="resize"> FROM:  {{ $LeaveHistory->subjectFrom}}</div></td></tr>

    			            <tr> <td><div class="resize"> SUBJECT:  {{ $LeaveHistory->subject}}</div></td></tr>

    			            <tr> <td> <div class="resize"> DATE:{{ $LeaveHistory->mdate}}</div></td></tr>

                            </table>
                            </ol>
    			        </strong>
    			    </div>
    			</div>

			</div>
		</div>

        <div class="row" style="margin-left:-50px;font-size:18px">
		    <div class="col-md-12">
		        <div align="justify" >
		            <ol type="i">
			             {!! $LeaveHistory->content !!}

			        </ol>
		        </div>
		    </div>
        </div>

        <br />
    </div>
    </div>
@stop

@section('styles')
    <style>
        .green-color {
        color: green;
        }
        .resize {
            width: 80%;
            border-bottom-style:solid;
            border-width:1px;
        }

        @media only screen and (max-width: 600px) {
        .green-color {
            color: green !important;
        }

        .resize {
            width:300px;
            border-bottom-style:solid;
            border-width:3px;
        }
		table tr td
		{
			padding:15px !important;
		}

</style>
@endsection

@section('scripts')
    <script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
    <script src="{{ asset('assets/js/jquery.autocomplete.js') }}" ></script>

    <script type="text/javascript">

    </script>
@stop

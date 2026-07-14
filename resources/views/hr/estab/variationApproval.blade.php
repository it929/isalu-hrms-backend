@extends('layouts.layout')
@section('pageTitle')
	 MANPOWER
@endsection


@section('content')
<div class="box box-default" style="border-top: none;">
	<form action="{{url('/manpower/view/central')}}" method="post">
	{{ csrf_field() }}
        <div class="box-header with-border hidden-print">
          <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
          <span class="pull-right" style="margin-right: 30px;">
          	 <div style="float: left;">
          	 	<div class="wrap">
    				   <div class="search">
               <button type="submit" class="btn btn-default" style="padding: 6px; float: right; border-radius: 0px;">
                <i class="fa fa-search"></i>
              </button>
				       <input type="text" id="autocomplete_central" name="q" class="form-control" placeholder="Search By Name or File No." style="padding: 5px; width: 300px;"><!--searchTerm-->
				       <input type="hidden" id="fileNo"  name="fileNo">
                <input type="hidden" id="monthDay"  name="monthDay" value="">
				      </div>
				      </div>
          	 </div>
          </span>
        </form>
        <form method="post" action="{{url('/manpower/view/central')}}">
          {{ csrf_field() }}
            <!--<span class="hidden-print">
                 <span class="pull-right" style="margin-left: 5px;">
                  <div style="float: left; width: 100%; margin-top: -20px;">
                     <button type="submit" class=" btn btn-default" style="padding: 6px; border-radius: 0px;">Staff Due for Increment Today</button>
                  </div>
                  <input type="hidden" id="monthDay"  name="monthDay" value="{{date('Y-m-d')}}">
                  <input type="hidden" id="fileNo"  name="fileNo" value="">
                  <input type="hidden" id="filterDivision"  name="filterDivision" value="">
                </span>
                <a href="{{url('/map-power/view/central')}}" title="Refresh" class="pull-right">
                  <i class="fa fa-refresh"></i> Refresh
                </a>
            </span>-->
        </form>
    </div>

    <div style="margin: 10px 20px;">
    	<div align="center">
        <h3><b>{{strtoupper('ISALU HOSPITAL LIMITED')}}</b></h3>
        <big><b></b></big>
      </div>
    	<span class="pull-right" style="margin-right: 30px;">Printed On: {{date('D M, Y')}} &nbsp; | &nbsp; Time: {{date('h:i:s A')}}</span>
      <br />
    @if(session('err'))
  		<div class="col-sm-12 alert alert-warning alert-dismissible hidden-print" role="alert">
  		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
  		</button>
  		<strong>Error!</strong>
  		{{ session('err') }}
  		</div>
	 @endif
	  @if(session('msg'))
  		<div class="col-sm-12 alert alert-success alert-dismissible hidden-print" role="alert">
  		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
  		</button>
  		<strong>Error!</strong>
  		{{ session('msg') }}
  		</div>
	 @endif


	<div class="box-body">
		<div class="row">
			{{ csrf_field() }}

			<div class="col-md-12">
        <h3>Variation (Annual Increment)</h3>
				<table class="table table-striped table-condensed table-bordered input-sm">
					<thead>
                    <tr class="input-sm">
                    <th>S/N</th>
                    <th>STAFF FULL NAME</th>
                    <th>GRADE</th>
                    <th>STEP</th>
                    <th>FILE NO</th>
                    <th>VARIATION LETTER</th>
                    @if($stage != 3)
                    <th>VARIATION ADVICE</th>
                    @endif
                    <th>TAKE ACTION</th>
                    <th>REVERSE</th>
                    </tr>
					</thead>
					<tbody>
			@php $key = 1;

             @endphp
             @if(count($variationList) > 0)
            @foreach($variationList as $list)
           @php
               $comm = DB::table('tblvariation_comments')->where('ID','=',$list->varTempID)->where('sent_by','=',Auth::user()->id)->where('present_stage','=',$stage)->count();
           @endphp
            @php
                        $fileNo = str_replace('/','-',$list->fileNo);
                        @endphp
  						<tr>
                  <td>{{$key++}}</td>
                  <td><p>{{strtoupper($list->surname .' '. $list->first_name .' '. $list->othernames)}}</p> <div class="clearfix"></div>
                          <a href="{{url("/file/docs/$fileNo")}}">View Staff File</a></td>
                  <td>{{$list->grade}}</td>
                  <td>{{$list->step}}</td>
                  <td>{{$list->fileNo}}</td>
                  <td><a href="{{url("/print/doc/$list->staffid")}}" target="_blank">Variation Letter</a></td>

                  @if($stage != 3)
                  <td>@if($stage != 3)<a href="{{url("/variation-order/approve/$list->staffid")}}">Variation Advice</a> @endif</td>
                  @endif

                  <td>
                    @if($list->is_rejected == 0 && $stage !=7)
                    <a href="javascript:void()" class="btn btn-success reject btn-sm" id="{{$list->staffID}}" varID ="{{$list->varTempID}}">Reject</a>
                    @endif

                    @if($list->is_rejected == 1)
                    <div class="btn btn-success rejected btn-sm" id="{{$list->staffID}}" varID ="{{$list->varTempID}}">This Variation was rejected. Click to see reason</div>
                    @endif
                    @if($list->approval_status == 0 && $list->is_rejected == 0)
                    <a href="javascript:void()" class="btn btn-success push btn-sm" id="{{$list->staffID}}" varID ="{{$list->varTempID}}">Approve</a>
                    @elseif($list->is_rejected == 1 )

                    @else

                    <strong class="text-success"> Approved </strong>
                    @endif

                    @if($list->approval_status == 1 && $stage != 9 && $list->is_rejected == 0)
                    <div class="btn btn-success approve btn-sm" id="{{$list->staffID}}" varID ="{{$list->varTempID}}">Move to {{$next ?? ''}}</div>
                    @endif

                  </td>
                  <td>
                   @if(($list->processed_by == Auth::user()->id && $comm == 1))
                    <div class="btn btn-primary reverse btn-sm" id="{{$list->staffID}}" varID ="{{$list->varTempID}}">Reverse</div>
                   @endif

                  </td>

              </tr>

            @endforeach

            @else
            <tr>
              <td colspan="10"><h1 class="text-center">No record available</h1></td>
            </tr>
            @endif
					</tbody>
				</table>

				<div class="hidden-print"></div>
			</div>
		</div><!-- /.col -->
	</div><!-- /.row -->
</div>


<!-- Bootsrap Modal for Conversion and Advancemnet-->

<form method="post" action="{{url('/variation/approval/remark')}}">
{{ csrf_field() }}
<div id="advModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Candidate Due For /Increment/Promotion/Conversion/Advancement</h4>
                <h3>Staff Name: <span id="name"></span> </h3>
                <p id="message"></p>
            </div>
            <div class="modal-body">
              @php
                if($approver != '')
                {
                  $code = $approver->code;
                }
                else
                {
                  $code = '';
                }
              @endphp

          <input type="hidden" name="staffid" id="staffid">
          <input type="hidden" name="staffCode" id="staffCode" value="{{$code}}">
          <input type="hidden" name="stage" id="stage" value="{{$stage}}">
          <input type="hidden" name="varID" id="varTemp">
            <div class="form-group">
                <label>Remark</label>
                <textarea class="form-control" name="remark"></textarea>
            </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary adv" id="adv">Submit</button>
            </div>
        </div>
    </div>
</div>
</form>

<!-- //// Bootsrap Modal for Conversion and Advancemnet-->

<!-- Push Modal -->

<form method="post" action="{{url('/variation/approval/push')}}">
  {{ csrf_field() }}
  <div id="pushModal" class="modal fade">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title">Candidate Due For /Increment/Promotion/Conversion/Advancement</h4>
                  <h3>Staff Name: <span id="name"></span> </h3>
                  <p id="message">Appprove

                  </p>
              </div>
              <div class="modal-body">
                @php
       if($approver != '')
       {
        $code = $approver->code;
       }
       else
       {
        $code = '';
       }
                @endphp

            <input type="hidden" name="staffid" id="staffidx">
            <input type="hidden" name="staffCode" id="staffCode" value="{{$code}}">
            <input type="hidden" name="stage" id="stage" value="{{$stage}}">
            <input type="hidden" name="varID" id="varTempp">
              <div class="form-group">
                  <label>Remark</label>
                  <textarea class="form-control" name="remark"></textarea>
              </div>

              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary adv" id="adv">Submit</button>
              </div>
          </div>
      </div>
  </div>
  </form>

<!-- //end push button -->



<!-- Bootsrap Modal for Conversion and Advancemnet-->

<form method="post" action="{{url('/variation/approval/reverse')}}">
{{ csrf_field() }}
<div id="reverseModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Candidate Due For /Increment/Promotion/Conversion/Advancement</h4>
                <h3>Staff Name: <span id="name"></span> </h3>
                <p id="message"></p>
            </div>
            <div class="modal-body">
              @php
     if($approver != '')
     {
      $code = $approver->code;
     }
     else
     {
      $code = '';
     }
              @endphp

          <input type="hidden" name="staffid" id="staffidrv">
          <input type="hidden" name="staffCode" id="staffCode" value="{{$code}}">
          <input type="hidden" name="varID" id="varTempr">
            <div class="form-group">
                <label>Remark</label>
                <textarea class="form-control" name="remark"></textarea>
            </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary adv" id="adv">Submit</button>
            </div>
        </div>
    </div>
</div>
</form>

<!-- //// Bootsrap Modal for Conversion and Advancemnet-->




<!-- B    ootsrap Modal for Conversion and Advancemnet-->

<form method="post" action="{{url('/variation/approval/reject')}}">
{{ csrf_field() }}
<div id="rejectModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Candidate Due For /Increment/Promotion/Conversion/Advancement</h4>
                <h3>Staff Name: <span id="name"></span> </h3>
                <p id="message"></p>
            </div>
            <div class="modal-body">
              @php
              if($approver != '')
              {
              $code = $approver->code;
              }
              else
              {
              $code = '';
              }
              @endphp

          <input type="hidden" name="staffid" id="staffID">
          <input type="hidden" name="staffCode" id="staffCode" value="{{$code}}">
          <input type="hidden" name="varID" id="varTemprj">
            <div class="form-group">
                <label>Reason For Rejecting</label>
                <textarea class="form-control" name="remark"></textarea>
            </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary adv" id="adv">Reject</button>
            </div>
        </div>
    </div>
</div>
</form>

<!-- //// Bootsrap Modal for Conversion and Advancemnet-->


<!--  Rejected Modal -->

<div id="rejectedModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                <p id="message"></p>
            </div>
            <div class="modal-body">

            <div class="reasontext"></div>

            </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary adv" id="adv">Reject</button>
            </div>
        </div>
    </div>
</div>

<!-- // Rejected Modal -->



@endsection


@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<!-- autocomplete js-->
<script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
<script type="text/javascript">

   $(document).ready(function(){

    $("table tr td .approve").click(function(){
      var staffid = $(this).attr('id');
      var varID = $(this).attr('varID');
        $("#advModal").modal('show');
        $("#staffid").val(staffid);
        $("#varTemp").val(varID);
        $("#v").html(staffid);
       // alert(staffid);


$.ajax({
  url: murl +'/staff/details/get',
  type: "post",
  data: {'staffid': staffid, '_token': $('input[name=_token]').val()},
  success: function(data){
     console.log(data);
    $('#name').html(data.surname+', '+data.first_name +' '+ data.othernames);
    //$('#oldgrade').html(data[0].grade);
    //$('#oldstep').html(data[0].step);

  }
});  //end of first ajax call for profile


    }); //click events end
});

//push

$(document).ready(function(){

  $("table tr td .push").click(function(){
    var staffid = $(this).attr('id');
    var varID = $(this).attr('varID');
      $("#staffidx").val(staffid);
      $("#varTempp").val(varID);
      $("#vx").html(staffid);
      $("#pushModal").modal('show');
     // alert(staffid);


$.ajax({
url: murl +'/staff/details/get',
type: "post",
data: {'staffid': staffid, '_token': $('input[name=_token]').val()},
success: function(data){
   console.log(data);
  $('#name').html(data.surname+', '+data.first_name +' '+ data.othernames);
  //$('#oldgrade').html(data[0].grade);
  //$('#oldstep').html(data[0].step);

}
});  //end of first ajax call for profile


  }); //click events end
});

//push ends


  $(function() {
      $("#autocomplete_central").autocomplete({
        serviceUrl: murl + '/map-power/staff/search/json',
        minLength: 10,
        onSelect: function (suggestion) {
            $('#fileNo').val(suggestion.data);
            showAll();
        }
      });
  });

  $("#searchDate").datepicker({
    changeMonth: true,
    changeYear: true,
    yearRange: '1910:2090', // specifying a hard coded year range
    showOtherMonths: true,
    selectOtherMonths: true,
    dateFormat: "dd MM, yy",
    onSelect: function(dateText, inst){
      var theDate = new Date(Date.parse($(this).datepicker('getDate')));
      var dateFormatted = $.datepicker.formatDate('yy-mm-d', theDate);
       $('#fileNo').val($.datepicker.formatDate('yy-m-d', theDate));
    },
  });

</script>

<script type="text/javascript">
  $(document).ready(function(){

    $("table tr td .reverse").click(function(){
      var staffid = $(this).attr('id');
      var varID = $(this).attr('varID');
        $("#staffidrv").val(staffid);
        $("#varTempr").val(varID);
        $("#reverseModal").modal('show');
      // alert(staffid);


$.ajax({
  url: murl +'/variation/approval/reverse',
  type: "post",
  data: {'staffid': staffid, '_token': $('input[name=_token]').val()},
  success: function(data){
     console.log(data);
   //alert(data);

  }
})  //end of first ajax call for profile



    }); //click events end
});

</script>
<script type="text/javascript">
     $(document).ready(function(){

    $("table tr td .reject").click(function(){
      var staffid = $(this).attr('id');
      var varID = $(this).attr('varID');
      $("#staffID").val(staffid);
      $("#varTemprj").val(staffid);
        $("#v").html(staffid);
        $("#rejectModal").modal('show');


$.ajax({
  url: murl +'/variation/approval/rejects',
  type: "post",
  data: {'staffid': staffid, '_token': $('input[name=_token]').val()},
  success: function(data){
     console.log(data);

    //$('#oldgrade').html(data[0].grade);
    //$('#oldstep').html(data[0].step);

  }
})  //end of first ajax call for profile
   }); //click events end
});

</script>

<script type="text/javascript">
     $(document).ready(function(){

    $("table tr td .rejected").click(function(){
      var staffid = $(this).attr('id');
        $("#rejectedModal").modal('show');
        $("#staffID").val(staffid);
        //$("#v").html(staffid);

$.ajax({
  url: murl +'/variation/rejection/reason',
  type: "post",
  data: {'staffid': staffid, '_token': $('input[name=_token]').val()},
  success: function(data){
     console.log(data);

    $('.reasontext').html(data.comment);
    //$('#oldstep').html(data[0].step);

  }
})  //end of first ajax call for profile
   }); //click events end
});

</script>






@stop

@section('styles')
  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom-style.css')}}">

  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@stop








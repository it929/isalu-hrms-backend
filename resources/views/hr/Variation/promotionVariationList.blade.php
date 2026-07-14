@extends('layouts.layout')
@section('pageTitle')
	 STAFF DUE FOR INCREMENT
@endsection

@section('content')
<div class="box box-default" style="border-top: none; background: white;">

    <!-- Bootsrap Modal Push-->

<form method="post" action="{{url('/promotion-variation/moveto')}}">
{{ csrf_field() }}
<div id="pushModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Promotion Variation</h4>
                <h3>Staff Name: <span id="name"></span> </h3>
                <p id="message"></p>
            </div>
            <div class="modal-body">

          <input type="hidden" name="staffid" class="staffid">
           <input type="hidden" name="staffCode" id="staffCode" value="VO">
           <div class="row">



            <div class="col-md-6">
               <div class="form-group">
              <label>Move To</label>
            <select name="moveTo" class="form-control">
                <option value="">Select</option>

                <option value="3">Director Admin</option>
                <option value="14">Deputy Director Admin</option>
                <option value="4">Assistant Director Admin</option>
                <option value="7">Variation</option>
                <option value="8">Director Audit</option>
                <option value="9">Director Account</option>

            </select>
           </div>
           </div>



          <div class="col-md-12">
            <div class="form-group">
                <label>Remark</label>
                <textarea class="form-control" name="remark" required></textarea>
            </div>
         </div>
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

<!-- //// Bootsrap Modal Push-->


	{{-- <!--
	<form action="" method="post">
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
				       <input type="text" id="autocomplete_central" name="q" class="form-control" placeholder="Search By Name or File No." style="padding: 5px; width: 300px;">
				       <input type="hidden" id="fileNo"  name="fileNo">
                <input type="hidden" id="monthDay"  name="monthDay" value="">
				      </div>
				      </div>
          	 </div>
          </span>
        </form>
        --> --}}

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
        <h5><b>{{strtoupper('Staff Due For Increment')}}</b></h5>
        <big><b></b></big>
      </div>
    	<span class="pull-right" style="margin-right: 30px;">Printed On: {{date('d M, Y')}} &nbsp; | &nbsp; Time: {{date('h:i:s A')}}</span>

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
  		<strong></strong>
  		{{ session('msg') }}
  		</div>
	 @endif

	</div>

	<div class="box-body" style="background: white;">
		<div class="row">



			<div class="col-md-12">
			    <div class="table-responsive">
				<table class="table table-bordered ">
                                                    <thead>
                                                    <tr>
                                                        <th>SN</th>
                                                        <th>Name</th>
                                                        <th>DATE OF 1st APPT</th>
                                                        <th>Date of Present <br/> Appointment</th>
                                                        <th>POST SOUGHT</th>
                                                        <th>DEPARTMENT</th>
                                                       <th>Old SGL|Old Step</th>
                                                       <th>New SGL|New Step</th>
                                                       <th>Due Date</th>
                                                       <th class="hidden-print">Generate Advice</th>
                                                       <th class="hidden-print">Action</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                   @php
                                                   $count = 1;
                                                   @endphp

                                                    @foreach($due as $number)
                                                    @php
                                                    $staff = DB::table('tblper')->where('ID','=',$number->staffid)->first()
                                                    @endphp

                                                    <tr>
                                                        <th>{{ $count++ }}</th>
                                                        <td>{{$number->surname.' '.$number->othernames.' '.$number->first_name}}</td>
                                                        <td>{{$number->appointment_date}}</td>

                                                        <td>{{$number->date_present_appointment}}</td>

                                                        <td>{{$number->designation}}</td>
                                                        <td>{{$number->department}}</td>
                                                        <td>{{$number->old_grade}} | {{$number->old_step}}</td>
                                                        <td>{{$number->new_grade}} | {{$number->new_step}}</td>
                                                        <td>{{$number->due_date}}</td>
                                                        <td>
                                                            <a href="{{url('/variation-order/approve/'.$number->staffid)}}">Variation Advice</a>
                                                        </td>
                                                        <td>

                                                            @if($number->stage == 3)
                                                            <a href="javascript:void()" id="reverse" staffid="{{$number->staffid}}" class="btn btn-success">Approve</a>
                                                            <a href="javascript:void()" id="reverse" staffid="{{$number->staffid}}" class="btn btn-warning">Reject</a>
                                                            @endif
                                                            @if($number->pushed_by == Auth::user()->id && $stages->action_stageID !=$number->stage)
                                                            <a href="javascript:void()" id="reverse" staffid="{{$number->staffid}}" class="btn btn-warning">Reverse</a>
                                                            @else
                                                            <a href="javascript:void()" id="push" staffid="{{$number->staffid}}" class="btn btn-success">Push</a>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    @endforeach

                                                    </tbody>
</table>
</div>
                <div align="right">

                </div>

				<div class="hidden-print"></div>
			</div>
		</div><!-- /.col -->
	</div><!-- /.row -->
</div>


<!-- Bootsrap Modal for Conversion and Advancemnet-->

<form method="post" action="{{url('/promotion-variation/details')}}">
{{ csrf_field() }}
<div id="processModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Promotion Variation</h4>
                <h3>Staff Name: <span id="name"></span> </h3>
                <p id="message"></p>
            </div>
            <div class="modal-body">

          <input type="hidden" name="staffid" id="staffid">
           <input type="hidden" name="staffCode" id="staffCode" value="VO">
           <div class="row">


           <div class="col-md-6">
               <div class="form-group">
            <label>Previous Grade</label>
            <input type="text" name="previousGrade" class="form-control" id="previousGrade" readonly/>
           </div>
           </div>
            <div class="col-md-6">
               <div class="form-group">
            <label>Previous Step</label>
            <input type="text" name="previousStep" class="form-control" id="previousStep" readonly/>
           </div>
           </div>


           <div class="col-md-6">
               <div class="form-group">
            <label>New Grade</label>
            <select name="newGrade" class="form-control">
                <option value="">Select</option>
                @for($i=1; $i <=17; $i++)
                <option value="{{$i}}">{{$i}}</option>
                @endfor
            </select>
           </div>
           </div>
            <div class="col-md-6">
               <div class="form-group">
            <label>New Step</label>
            <select name="newStep" class="form-control">
                <option value="">Select</option>
                @for($i=1; $i <=17; $i++)
                <option value="{{$i}}">{{$i}}</option>
                @endfor
            </select>
           </div>
           </div>

           <div class="col-md-12">
                <div class="form-group">
            <label> Promotion Due Date</label>
            <input type="text" name="dueDate" class="form-control" id="dueDate" />
           </div>
           </div>

          <div class="col-md-12">
            <div class="form-group">
                <label>Remark</label>
                <textarea class="form-control" name="remark" required></textarea>
            </div>
         </div>
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




<!-- Bootsrap Modal View Rejected-->


<div id="reasonModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Candidate Due For /Increment/Promotion/Conversion/Advancement</h4>
                <h3>Staff Name: <span id="staffName"></span> </h3>
                <p id="message"></p>
            </div>
            <div class="modal-body">
              <label>Reason for Rejecting</label>
              <div class="reason"></div>
            </div>

            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>


<!-- //// Bootsrap Modal for view Rejected-->

<form method="post" action="{{url('/promotion-variation/reversal')}}">
{{ csrf_field() }}
<div id="reverseModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Promotion Variation</h4>
                <h3>Staff Name: <span id="name"></span> </h3>
                <p id="message"></p>
            </div>
            <div class="modal-body">

          <input type="hidden" name="staffid" class="staffid">
           <input type="hidden" name="staffCode" id="staffCode" value="VO">
           <div class="row">

          <div class="col-md-12">
            <div class="form-group">
                <label>Reason For Reversal</label>
                <textarea class="form-control" name="remark" required></textarea>
            </div>
         </div>
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

<!-- //// Bootsrap Modal Push-->



<form method="post" action="{{url('/promotion-variation/moveto')}}">
{{ csrf_field() }}
<div id="rejectModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Promotion Variation</h4>
                <h3>Staff Name: <span id="name"></span> </h3>
                <p id="message"></p>
            </div>
            <div class="modal-body">

          <input type="hidden" name="staffid" class="staffid">
           <input type="hidden" name="staffCode" id="staffCode" value="VO">


          <div class="col-md-12">
            <div class="form-group">
                <label>Reason for Rejecting</label>
                <textarea class="form-control" name="remark" required></textarea>
            </div>
         </div>
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

<!-- //// Bootsrap Modal Reject Variation-->




@endsection



@section('styles')

<style>
    .blink-text a
    {
        color:red !important;
    }
</style>

@endsection


@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<!-- autocomplete js-->
<script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
<script type="text/javascript">


   $(document).ready(function(){

    $("table tr td #process").click(function(){
      var staffid = $(this).attr('staffid');
      var due = $(this).attr('due');

        $("#staffid").val(staffid);

        $("#processModal").modal('show');



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
})  //end of first ajax call for profile



    }); //click events end
});


//Move to

   $(document).ready(function(){

    $("table tr td #push").click(function(){

       var staffid = $(this).attr('staffid');
      var due = $(this).attr('due');

        $(".staffid").val(staffid);
        $("#pushModal").modal('show');

    });
});

//End Move to

$(document).ready(function(){

    $("table tr td #reverse").click(function(){
      var staffid = $(this).attr('staffid');
      var due = $(this).attr('due');
      var grade= $(this).attr('grade');
      var step = $(this).attr('step');
      $("#previousStep").val(step);
      $("#previousGrade").val(grade);
        $("#dueDates").val(due);
        $(".staffid").val(staffid);

        $("#reverseModal").modal('show');

    }); //click events end
});




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

  $("#dueDate").datepicker({
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


$( function() {
      $("#effectiveDate").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '1910:2090', // specifying a hard coded year range
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: "dd MM, yy",
        //dateFormat: "D, MM d, yy",
        onSelect: function(dateText, inst){
          var theDate = new Date(Date.parse($(this).datepicker('getDate')));
        var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
        $("#effectiveDate").val(dateFormatted);
          },
    });

  } );
</script>

<script type="text/javascript">
  $(document).ready(function(){

$("table tr td .confirm").on('click',function(){
  //alert("ok");
 //var id=$(this).parent().parent().find("input:eq(0)").val();
  var id = $(this).attr('id');
  //alert(id);
   //var post =1;
   if($(this).prop("checked") == true){
                var publish = 1;

            }
            else if($(this).prop("checked") == false){
               var publish = 0;

            }

             $token = $("input[name='_token']").val();
 $.ajax({
  headers: {'X-CSRF-TOKEN': $token},
  url: "{{ url('/estab/promotion/confirmation') }}",

  type: "post",
  data: {'fileNo':id,'publish':publish},
  success: function(data){
    alert(data);
    $('#message').html(data);
  location.reload(true);
  }
});



});
 });



    $( function() {
        $("#getDateofBirth").datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: '1910:2990', // specifying a hard coded year range
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat: "dd MM, yy",
            //dateFormat: "D, MM d, yy",
            onSelect: function(dateText, inst){
                var theDate = new Date(Date.parse($(this).datepicker('getDate')));
                var dateFormatted = $.datepicker.formatDate('yy-mm-dd', theDate);
                var getDateofBirth = $.datepicker.formatDate('dd-mm-yy', theDate);
                var getDOB = $.datepicker.formatDate('yy-mm-dd', theDate);
                $("#getDateofBirth").val(getDateofBirth);
                $("#dateOfBirth").val(dateFormatted);
            },
        });
    });

</script>


@stop

@section('styles')
  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom-style.css')}}">

  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@stop








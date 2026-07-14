@extends('layouts.layout')
@section('pageTitle')
    STAFF DUE FOR INCREMENT
@endsection

@section('content')
    <div class="box box-default" style="border-top: none; background: white;">

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

        <form method="post" action="{{ url('/manpower/view/central') }}">
            {{ csrf_field() }}
            <!--<span class="hidden-print">
                     <span class="pull-right" style="margin-left: 5px;">
                      <div style="float: left; width: 100%; margin-top: -20px;">
                         <button type="submit" class=" btn btn-default" style="padding: 6px; border-radius: 0px;">Staff Due for Increment Today</button>
                      </div>
                      <input type="hidden" id="monthDay"  name="monthDay" value="{{ date('Y-m-d') }}">
                      <input type="hidden" id="fileNo"  name="fileNo" value="">
                      <input type="hidden" id="filterDivision"  name="filterDivision" value="">
                    </span>
                    <a href="{{ url('/map-power/view/central') }}" title="Refresh" class="pull-right">
                      <i class="fa fa-refresh"></i> Refresh
                    </a>
                </span>-->
        </form>
    </div>

    <div style="margin: 10px 20px;">
        <div align="center">
            <h3><b>{{ strtoupper('ISALU HOSPITAL LIMITED') }}</b></h3>
            <h5><b>{{ strtoupper('Staff Due For Increment') }}</b></h5>
            <big><b></b></big>
        </div>
        <span class="pull-right" style="margin-right: 30px;">Printed On: {{ date('d M, Y') }} &nbsp; | &nbsp; Time:
            {{ date('h:i:s A') }}</span>

        <br />
        @if (session('err'))
            <div class="col-sm-12 alert alert-warning alert-dismissible hidden-print" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span>
                </button>
                <strong>Error!</strong>
                {{ session('err') }}
            </div>
        @endif

    </div>

    <div class="box-body" style="background: white;">
        <div class="row">

            <form method="post" action="{{ Route::has('searchIncrement') ? Route('searchIncrement') : '#' }}">
                {{ csrf_field() }}
                <div class="col-md-12">
                    <div class="col-md-4">
                        <label> Date </label>
                        <!--getDateofBirth-->
                        <input type="text" readonly name="lastIncrementDate" id="incrementAlertDate"
                            class="form-control input-lg"
                            value="{{ isset($lastIncrementDate) ? date('d-m-Y', strtotime($lastIncrementDate)) : old('lastIncrementDate') }}"
                            placeholder="DD/MM/YY" />
                        <br />
                    </div>
                    <div class="col-md-4">
                        <br />
                        <button type="submit" name="search" class="btn btn-success btn-sm"
                            style="margin-top:10px">Search</button>
                    </div>

                </div>
            </form>

            <div class="col-md-12">
                <table class="table table-striped table-condensed table-bordered input-sm">
                    <thead>
                        <tr class="input-sm">
                            <th>S/N</th>
                            <th>FILE NO</th>
                            <th width="250" class="">FULL NAME</th>
                            <th>DATE OF BIRTH</th>
                            <th>DATE OF FIRST <BR /> APPOINTMENT</th>
                            <th>RANK</th>
                            <th>LAST INCREMENT <BR /> DATE</th>
                            <th class="hidden-print">Date Due <br /> for Increment</th>
                            <th>Approve Variation <br /> Order</th>
                            <th>Take Action</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($getCentralList as $key => $list)
                            @php
                                $staff = DB::table('tblvariation_temp')
                                    ->where('staffid', '=', $list->ID)
                                    ->count();
                                $reject = DB::table('tblvariation_comments')
                                    ->where('staffid', '=', $list->ID)
                                    ->where('rejected', '=', 1)
                                    ->where('sentby_code', '=', 'PA')
                                    ->count();
                                $user = $data = DB::table('tblvariation_approval_staff')
                                    ->where('user_id', '=', Auth::user()->id)
                                    ->first();

                                $confirmation = DB::table('tblvariation_comments')
                                    ->where('staffid', '=', $list->ID)
                                    ->where('sentby_code', '=', 'AS')
                                    ->where('sent_to', '=', 'DD')
                                    ->where('payment_status', '=', 0)
                                    ->first();
                                $variationProcess = DB::table('tblvariation_comments')
                                    ->where('staffid', '=', $list->ID)
                                    ->where('payment_status', '=', 0)
                                    ->where('sent_to', '=', 'PA')
                                    ->where('sentBy_code', '=', 'VO')
                                    ->where('worked_on', '=', 0)
                                    //->where('rejected','=', 1)
                                    ->count();
                                $variationDue = DB::table('tblvariation_temp')
                                    ->where('staffid', '=', $list->ID)
                                    ->count();

                            @endphp
                            <tr>
                                <td>{{ ($getCentralList->currentpage() - 1) * $getCentralList->perpage() + (1 + $key++) }}
                                </td>
                                <td>{{ $list->fileNo }}</td>
                                <td>{{ strtoupper($list->surname . ' ' . $list->first_name . ' ' . $list->othernames) }}</td>
                                <td width="90">{{ date('d-m-Y', strtotime($list->dob)) }}</td>
                                <td>{{ date('d-m-Y', strtotime($list->date_present_appointment)) }}</td>
                                <td>{{ 'GL' . $list->grade . '|' . 'S' . $list->step }}</td>
                                <td>{{ date('d-m-Y', strtotime($list->incremental_date)) }}</td>
                                <td>
                                    {{ isset($newDateDueForIncrement) ? $newDateDueForIncrement[$list->ID] : '-' }}
                                </td>
                                @php
                                    $user = DB::table('tblvariation_approval_staff')
                                        ->where('user_id', '=', Auth::user()->id)
                                        ->first();
                                @endphp

                                <td>
                                    @if ($staff == 1)
                                        @if ($confirmation != '' && $user && $confirmation->sent_to == $user->code)
                                            <a href="{{ url("/variation-order/approve/$list->ID") }}"> Confirm</a>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($variationDue == 1)
                                        @if ($variationProcess == 0)
                                            <div class="btn btn-success btn-sm push" id="{{ $list->ID }}">Process</div>
                                        @endif
                                    @endif
                                </td>

                                <td>
                                    @if ($reject == 1)
                                        <div class="btn btn-danger viewReason" id="{{ $list->ID }}">Variation Rejected
                                        </div>
                                    @endif
                                </td>
                                <td>{{ isset($incrementStatus) ? $incrementStatus[$list->ID] : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div align="right">
                    Showing {{ ($getCentralList->currentpage() - 1) * $getCentralList->perpage() + 1 }}
                    to {{ $getCentralList->currentpage() * $getCentralList->perpage() }}
                    of {{ $getCentralList->total() }} entries
                </div>

                <div class="hidden-print">{{ $getCentralList->links() }}</div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
    </div>


    <!-- Bootsrap Modal for Conversion and Advancemnet-->

    <form method="post" action="{{ url('/variation/approval/remark') }}">
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

                        <input type="hidden" name="staffid" id="staffid">
                        <input type="hidden" name="staffCode" id="staffCode" value="VO">

                        <div class="form-group">
                            <label>Remark</label>
                            <textarea class="form-control" name="remark">

                            </textarea>
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


    <div id="rejectModal" class="modal fade">
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



@endsection



@section('styles')

    <style>
        .blink-text a {
            color: red !important;
        }
    </style>

@endsection


@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <!-- autocomplete js-->
    <script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('input[id$=incrementAlertDate]').datepicker({
                dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
            });
        });



        $(document).ready(function() {

            $("table tr td .push").click(function() {
                var staffid = $(this).attr('id');
                $("#advModal").modal('show');
                $("#staffid").val(staffid);
                $("#v").html(staffid);


                $.ajax({
                    url: murl + '/staff/details/get',
                    type: "post",
                    data: {
                        'staffid': staffid,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        console.log(data);
                        $('#name').html(data.surname + ', ' + data.first_name + ' ' + data
                            .othernames);
                        //$('#oldgrade').html(data[0].grade);
                        //$('#oldstep').html(data[0].step);

                    }
                }) //end of first ajax call for profile



            }); //click events end
        });

        $(document).ready(function() {

            $("table tr td .viewReason").click(function() {
                var staffid = $(this).attr('id');
                $("#rejectModal").modal('show');
                $("#staffid").val(staffid);
                $("#v").html(staffid);


                $.ajax({
                    url: murl + '/variation/rejection/reason',
                    type: "post",
                    data: {
                        'staffid': staffid,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        console.log(data);
                        $('#staffName').html(data.surname + ', ' + data.first_name + ' ' + data
                            .othernames);
                        //$('#oldgrade').html(data[0].grade);
                        $('.reason').html(data.comment);

                    }
                }) //end of first ajax call for profile



            }); //click events end
        });


        $(function() {
            $("#autocomplete_central").autocomplete({
                serviceUrl: murl + '/map-power/staff/search/json',
                minLength: 10,
                onSelect: function(suggestion) {
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
            onSelect: function(dateText, inst) {
                var theDate = new Date(Date.parse($(this).datepicker('getDate')));
                var dateFormatted = $.datepicker.formatDate('yy-mm-d', theDate);
                $('#fileNo').val($.datepicker.formatDate('yy-m-d', theDate));
            },
        });
    </script>



    <script type="text/javascript">
        $(function() {

            $(".adv").on('click', function() {

                var fileNo = $('.file-number').val();
                var type = $('#type').val();
                var postcon = $('#postcon').val();
                var effectiveDate = $('#effectiveDate').val();
                var grade = $('#newGrade').val();
                var step = $('#newStep').val();
                //$('#msg').html(fileNo);
                // alert(fileNo);

                $('#advModal').removeData('bs.modal');
                if (grade == '') {
                    $('#message').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert"><strong>Please, Enter New Grade</strong> </div> '
                        );

                } else if (type == '') {
                    $('#message').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert"><strong>Please, Choose the whether it is Conversion or Advancement</strong> </div> '
                        );
                } else if (postcon == '') {
                    $('#message').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert"><strong>Please, Enter the Post Considered</strong> </div> '
                        );
                } else if (effectiveDate == '') {
                    $('#message').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert"><strong>Please, Select the Effective date</strong> </div>'
                        );
                } else if (step == '') {
                    $('#message').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert"><strong>Please, Select the New Step</strong> </div>'
                        );
                } else {
                    //$('#msg').html(fileNo);
                    $token = $("input[name='_token']").val();

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $token
                        },
                        url: "{{ url('/estab/promotion/save') }}",

                        type: "post",
                        data: {
                            'fileNo': fileNo,
                            'type': type,
                            'position': postcon,
                            'effdate': effectiveDate,
                            'grade': grade,
                            'step': step
                        },
                        success: function(data) {

                            $('#message').html(data);
                            // location.reload(true);
                        },
                        error: function(data) {
                            console.log(data)
                        }
                    });

                }

            });
        });


        $(function() {
            $("#effectiveDate").datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '1910:2090', // specifying a hard coded year range
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat: "dd MM, yy",
                //dateFormat: "D, MM d, yy",
                onSelect: function(dateText, inst) {
                    var theDate = new Date(Date.parse($(this).datepicker('getDate')));
                    var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
                    $("#effectiveDate").val(dateFormatted);
                },
            });

        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("table tr td .confirm").on('click', function() {
                //alert("ok");
                //var id=$(this).parent().parent().find("input:eq(0)").val();
                var id = $(this).attr('id');
                //alert(id);
                //var post =1;
                if ($(this).prop("checked") == true) {
                    var publish = 1;

                } else if ($(this).prop("checked") == false) {
                    var publish = 0;

                }

                $token = $("input[name='_token']").val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $token
                    },
                    url: "{{ url('/estab/promotion/confirmation') }}",

                    type: "post",
                    data: {
                        'fileNo': id,
                        'publish': publish
                    },
                    success: function(data) {
                        alert(data);
                        $('#message').html(data);
                        location.reload(true);
                    }
                });



            });
        });



        $(function() {
            $("#getDateofBirth").datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '1910:2990', // specifying a hard coded year range
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat: "dd MM, yy",
                //dateFormat: "D, MM d, yy",
                onSelect: function(dateText, inst) {
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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-style.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
@stop

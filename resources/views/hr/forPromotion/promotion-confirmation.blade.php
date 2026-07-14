@extends('layouts.layout')
@section('pageTitle')
    Promotions
@endsection

@section('content')
    <div class="box box-default" style="border-top: none;">


        <div class="box-header with-border hidden-print">
            <h3 class="box-title"><b>@yield('pageTitle')</b> <i class="fa fa-arrow-right"></i> <span
                    id='processing'><strong><em>Promotion Scores Confirmation.</em></strong></span></h3>
        </div>

        <div style="margin: 10px 20px;">
            <div align="center">
                <h3><b>{{ strtoupper('ISALU HOSPITAL LIMITED') }}</b></h3>
                <h5><b>{{ strtoupper('CANDIDATES WITH RECORDED SCORES') }}</b></h5>
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
        <div class="box box-success">
            <div class="box-body" style="background: white;">
                <div class="row">
                    <div class="col-md-12">
                        {{-- <h3>Promotion Scores Entry Sheet</h3> --}}
                        <table class="table table-striped table-condensed table-bordered input-sm">
                            <thead>
                                <tr class="input-sm">
                                    <th>S/N</th>
                                    <th>FILE NO.</th>
                                    <th width="250" class="">FULL NAME</th>
                                    <th>DATE OF BIRTH</th>
                                    <th>DATE OF FIRST <BR /> APPOINTMENT</th>
                                    <th>RANK</th>
                                    <th>DATE OF PRESENT <BR /> APPOINTMENT</th>
                                    <th class="hidden-">Status</th>
                                    <th class="hidden-print">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($shortlisted as $key => $list)
                                    @php
                                        $data = DB::table('tblstaffpromotion_shortlist')
                                            ->where('year', '=', date('Y'))
                                            ->where('staffid', '=', $list->ID)
                                            ->count();
                                    @endphp
                                    <tr>
                                        <td>{{ ($shortlisted->currentpage() - 1) * $shortlisted->perpage() + (1 + $key++) }}
                                        </td>
                                        <td>
                                            <span class="badge label-success" style="font-size: 13px;">
                                                {{ $list->fileNo }}
                                            </span>
                                        </td>
                                        <td>{{ strtoupper($list->surname . ' ' . $list->first_name . ' ' . $list->othernames) }}
                                        </td>
                                        <td width="90">{{ date('d-m-Y', strtotime($list->dob)) }}</td>

                                        <td> {{ date('d-m-Y', strtotime($list->appointment_date)) }} </td>
                                        {{-- <td>{{ 'GL' . $list->grade . '|' . 'S' . $list->step }}</td> --}}
                                        <td>
                                            <span class="label label-primary"
                                                style="font-size: 12px; padding: 6px 10px; border-radius: 6px;">
                                                GL{{ $list->grade }}
                                            </span>
                                            <span class="label label-success"
                                                style="font-size: 12px; padding: 6px 10px; border-radius: 6px; margin-left: 5px;">
                                                S{{ $list->step }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ date('d-m-Y', strtotime($list->date_present_appointment)) }}
                                        </td>
                                        <td>
                                            @if ($list->confirmed_promoted == 1)
                                                <span class="badge label-success" style="font-size: 13px;">
                                                    Promoted
                                                </span>
                                            @else
                                                <span class="badge label-danger" style="font-size: 13px;">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>


                                        <td class="hidden-print text-">
                                            <a href="{{ url('/viewpromotion/' . $list->id) }}" target="_blank"
                                                staffid="{{ $list->id }}" class="btn btn-primary btn-xs">
                                                <i class="fa fa-eye"></i> View Scores
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>

                        <div class="hidden-print">{{ $shortlisted->links() }}</div>
                    </div>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <!-- autocomplete js-->
    <script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('input[id$=promotionAlertDate]').datepicker({
                dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
            });
        });
        $(document).ready(function() {

            $("table tr td .promote").click(function() {
                var fileNo = $(this).attr('id');
                $("#advModal").modal('show');
                $(".file-number").val(fileNo);


                $.ajax({
                    url: murl + '/estab/profile/details',
                    type: "post",
                    data: {
                        'fileNo': fileNo,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {

                        $('#name').html(data[0].surname + ', ' + data[0].first_name + data[0]
                            .othernames);
                        $('#oldgrade').html(data[0].grade);
                        $('#oldstep').html(data[0].step);

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
                //alert(fileNo);

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
                            location.reload(true);
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

    <script type="text/javascript">
        $(document).ready(function() {
            $(".move").on('click', function() {
                var id = $(this).attr('staffid');
                $('.save').attr('staffDataID', id);
                $("#shortlistModal").modal('show');
            });

            $(".save").on('click', function() {
                var id = $(this).attr('staffDataID');
                $('#shortlist' + id).html('Processing....')
                $.ajax({

                    url: "{{ url('/promotion/shortlist') }}",

                    type: "post",
                    data: {
                        'staffid': id,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {

                        $('#message').html(data);
                        //location.reload(true);
                        $('#shortlist' + id).html(
                            '<span class="text-success">Shortlisted</span>')
                    }
                });


            });
        });
    </script>

    <script type="text/javascript">
        function confirmActionx() {
            var x = confirm('Do you realy want to shortlist this staff for promotion ?');
            if (x) {
                return true;
            } else {
                return false;
            }
        }
    </script>


    <script type="text/javascript">
        $(document).ready(function() {
            $(".reverse").on('click', function() {

                $("#reverseModal").modal('show');
            });


        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $(".approve").on('click', function() {
                var id = $(this).attr('staffid');
                $('.save').attr('staffDataID', id);
                $("#approvalModal").modal('show');
            });

            /*$(".save").on('click',function(){
              var id = $(this).attr('staffDataID');
              $('#shortlist' + id).html('Processing....')
             $.ajax({

              url: "{{ url('/promotion/shortlist') }}",

              type: "post",
              data: {'staffid':id, '_token': $('input[name=_token]').val()},
              success: function(data){

                $('#message').html(data);
              //location.reload(true);
              $('#shortlist' + id).html('<span class="text-success">Shortlisted</span>')
              }
            });


            });
            */

        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $(".reverseAppr").on('click', function() {
                var id = $(this).attr('staffid');
                $('.save').attr('staffDataID', id);
                $("#appReversalModal").modal('show');
            });



        });
    </script>



@stop

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-style.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
@stop

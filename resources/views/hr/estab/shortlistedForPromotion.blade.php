@extends('layouts.layout')
@section('pageTitle')
    Promotions
@endsection

@section('content')
    <div class="box box-default" style="border-top: none;">

        <div class="box-header with-border hidden-print">
            <h3 class="box-title"><b>@yield('pageTitle')</b> <i class="fa fa-arrow-right"></i> <span
                    id='processing'><strong><em>Shortlisted For Promotion.</em></strong></span></h3>
        </div>


        <div style="margin: 10px 20px;">
            <div align="center">
                <h3><b>{{ strtoupper('ISALU HOSPITAL LIMITED') }}</b></h3>
                <h5><b>{{ strtoupper('LIST OF SHORTLISTED STAFF FOR PROMOTION') }}</b></h5>
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
                        <h3>Shortlisted Staff For Promotion</h3>
                        <table class="table table-striped table-condensed table-bordered input-sm">
                            <thead>
                                <tr class="input-sm">
                                    <th>S/N</th>
                                    <th>FILE NO.</th>
                                    <th width="250" class="">FULL NAME</th>
                                    <th>DATE OF BIRTH</th>
                                    <!--<th>SEX</th>-->
                                    <th>DATE OF FIRST <BR /> APPOINTMENT</th>
                                    <th>RANK</th>
                                    <th>DATE OF PRESENT <BR /> APPOINTMENT</th>
                                    <th>Status</th>

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

                                        <td class="hidden-print">
                                            @if ($list->approval_status == 1)
                                                <span class="label label-primary">
                                                    Approved
                                                </span>
                                            @else
                                                <span class="label label-danger">
                                                    Await Approval
                                                </span>
                                            @endif
                                        </td>

                                        <td class="hidden-print">
                                            <a href="{{ url('/promotion/brief/' . $list->ID) }}" target="_blank"
                                                class="btn btn-xs btn-dark" staffid="{{ $list->ID }}" class="">
                                                <i class="fa fa-briefcase"></i> Promotion
                                                Brief
                                            </a>
                                            <button class="btn btn-primary btn-xs approve" data-year="{{ $list->year }}"
                                                data-id="{{ $list->id }}" data-progress="{{ $list->progress_stage }}"
                                                data-status="{{ $list->status }}"
                                                data-name="{{ strtoupper($list->surname . ' ' . $list->first_name . ' ' . $list->othernames) }}"
                                                data-approval="{{ $list->approval_status }}">
                                                <i class="fa fa-check"></i> Approve
                                            </button>

                                            <button class="btn btn-info btn-xs viewComments"
                                                data-staffid="{{ $list->id }}"
                                                data-name="{{ strtoupper($list->surname . ' ' . $list->first_name . ' ' . $list->othernames) }}">
                                                <i class="fa fa-comments"></i> View Comments
                                            </button>



                                            @if ($list->status == 1 && $list->progress_stage == 1 && $list->approval_status == 1)
                                                <button class="btn btn-warning btn-xs reverseAppr"
                                                    data-year="{{ $list->year }}" data-id="{{ $list->id }}"
                                                    data-progress="{{ $list->progress_stage }}"
                                                    data-status="{{ $list->status }}"
                                                    data-name="{{ strtoupper($list->surname . ' ' . $list->first_name . ' ' . $list->othernames) }}"
                                                    data-approval="{{ $list->approval_status }}">
                                                    <i class="fa fa-undo"></i> Reverse Approval
                                                </button>
                                            @endif

                                            {{-- <button class="btn btn-danger btn-xs reject" year="{{ $staff->year }}">
                                                <i class="fa fa-times"></i>  Reject
                                            </button> --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- @if ($staff)
                            <div class="hidden-print" style="margin-top: 15px;">

                                <div class="panel panel-default">
                                    <div class="panel-body text-center">
                                        @if ($shortlisted->isNotEmpty() && $staff->approval_status == 1)
                                            <button class="btn btn-success btn-sm move" year="{{ $staff->year }}">
                                                <i class="fa fa-arrow-right"></i> Move To
                                            </button>
                                        @endif

                                        @if ($stage->action_stageID == 6 && $staff->approval_status == 1)
                                            <button class="btn btn-warning btn-sm reverseAppr" year="{{ $staff->year }}">
                                                <i class="fa fa-undo"></i> Reverse Approval
                                            </button>
                                        @endif

                                        @if ($stage->action_stageID == 6 && $staff->approval_status == 0)
                                            <button class="btn btn-primary btn-sm approve" year="{{ $staff->year }}">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                        @endif

                                        @if ($stage->action_stageID == 3)
                                            <button class="btn btn-info btn-sm move" year="{{ $staff->year }}">
                                                <i class="fa fa-file-text-o"></i> Generate Memo
                                            </button>
                                        @endif

                                        @if ($stage->action_stageID == $staff->stage_from && $stage->action_stageID != $staff->progress_stage)
                                            <button class="btn btn-danger btn-sm reverse" year="{{ $staff->year }}">
                                                <i class="fa fa-undo"></i> Reverse
                                            </button>
                                        @endif

                                    </div>
                                </div>

                            </div>
                        @endif --}}


                        {{-- @if ($staff)

                            <span>
                                @if ($shortlisted->isNotEmpty() && $staff->approval_status == 1)
                                    <div class="float-left text-right hidden-print" style="width:6%; float: right;">
                                        <a href="javascript:void()" class="btn btn-success btn-sm move"
                                            year="{{ $staff->year }}">Move To</a>
                                    </div>
                                @endif
                            </span>

                            @if ($stage->action_stageID == 6 && $staff->approval_status == 1)
                                <div class="float-right text-right hidden-print" style="width:8%; float: right;">
                                    <a href="javascript:void()" class="btn btn-success btn-sm reverseAppr"
                                        year="{{ $staff->year }}">Reverse Approval</a>
                                </div>
                            @endif

                            <span>
                                @if ($stage->action_stageID == 6 && $staff->approval_status == 0)
                                    <div class="float-left text-right hidden-print" style="width:6%; float: right;">
                                        <a href="javascript:void()" class="btn btn-success btn-sm approve"
                                            year="{{ $staff->year }}">Approve</a>
                                    </div>
                                    @elseif($staff->approval_status == 1)
                                    <div class="float-left text-right hidden-print"
                                        style="margin-right: 30px; margin-left: 30px;">
                                        Approved</div>
                                @endif
                            </span>


                            <span>
                                @if ($stage->action_stageID == 3)
                                    <div class="float-right text-right hidden-print" style="width:6%; float: right;">
                                        <a href="javascript:void()" class="btn btn-success btn-sm move"
                                            year="{{ $staff->year }}">Generate Memo</a>
                                    </div>
                                @endif
                            </span>

                            <span>
                                @if ($stage->action_stageID == $staff->stage_from && $stage->action_stageID != $staff->progress_stage)
                                    <div class="float-right text-right hidden-print" style="width:6%; float: right;">
                                        <a href="javascript:void()" class="btn btn-success btn-sm reverse"
                                            year="{{ $staff->year }}">Reverse</a>
                                    </div>
                                @endif
                            </span>
                        @endif --}}

                        <div align="right">
                        </div>

                        <div class="hidden-print">{{ $shortlisted->links() }}</div>
                    </div>
                </div><!-- /.col -->
            </div>
        </div>
    </div>



    <!-- button -->

    <form method="post" action="{{ url('/promotion/shortlisted-staff') }}">
        {{ csrf_field() }}
        <div id="shortlistModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <p id="message"></p>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">

                            <label>Sent To</label>
                            <select name="referTo" class="form-control" required>
                                <option value="">Select</option>
                                @foreach ($stages as $list)
                                    <option value="{{ $list->stage }}">{{ $list->approval_name }}</option>
                                @endforeach
                            </select>

                        </div>

                        <input type="hidden" name="year" value="{{ $staff->year ?? '' }}">
                        <div class="form-group">

                            <label>Comment</label>
                            <textarea name="comment" class="form-control"></textarea>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary save" staffDataID="" id="save">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- //// Bootsrap Modal for Conversion and Advancemnet-->


    <!-- Reverse -->
    <form method="post" action="{{ url('/promotion/shortlist-reversal') }}">
        {{ csrf_field() }}
        <div id="reverseModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <p id="message"></p>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">

                            <label>Comment</label>
                            <textarea name="comment" class="form-control"></textarea>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary save" staffDataID=""
                            id="save">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!--// Reverse -->


    <form method="post" action="{{ url('/promotion/approval') }}">
        {{ csrf_field() }}

        <input type="hidden" name="promotion_id" id="staffID">
        <input type="hidden" name="progress_stage" id="progressStage">
        <input type="hidden" name="approval_status" id="approvalStatus">
        <input type="hidden" name="status" id="promotionStatus">

        <div id="approvalModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">
                            <i class="fa fa-check-circle"></i> Approve Staff Promotion
                        </h4>
                    </div>

                    <div class="modal-body">
                        <p id="message1" class="alert alert-info" style="padding: 10px;">
                            <!-- dynamic message will load here -->
                        </p>
                        <div class="form-group">
                            <label for="">Comment (optional)</label>
                            <textarea name="comment" class="form-control" placeholder="Add a note..."></textarea>
                        </div>

                        <p class="text-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            Once approved, this staff record will move to the <strong>Deputy Director</strong> desk.
                        </p>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> Approve Promotion
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>


    <!-- End Promotion approval -->


    <!-- Reverse Promotion Approval -->

    <form method="post" action="{{ url('/promotion/approval/reversal') }}">
        {{ csrf_field() }}
        <div id="appReversalModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <p id="message"></p>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Reason for Reversal</label>
                            <textarea name="comment" class="form-control"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary save" staffDataID=""
                            id="save">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- // Reverse Promotion Approval -->

    <div id="commentModal" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header bg-info">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-commenting"></i> Staff Comments Timeline
                    </h4>
                </div>

                <div class="modal-body">

                    <p id="commentStaffName" class="text-primary" style="font-weight: bold;"></p>

                    <div class="timeline" id="commentTimeline" style="max-height: 400px; overflow-y: auto;">

                        <!-- Comments will be injected here as timeline items -->

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>


@endsection






@section('style')
    <style>
        .timeline {
            position: relative;
            padding: 0;
            list-style: none;
        }

        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 40px;
            width: 2px;
            background: #ddd;
        }

        .timeline-item {
            position: relative;
            margin: 20px 0;
            padding-left: 80px;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            left: 33px;
            background-color: #3498db;
            border-radius: 50%;
            top: 0;
            border: 2px solid white;
        }

        .timeline-item .timeline-panel {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px 15px;
            position: relative;
        }

        .timeline-item .timeline-time {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }
    </style>

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <!-- autocomplete js-->
    <script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>

    <script>
        $(document).on("click", ".viewComments", function() {

            var staffID = $(this).data("staffid");
            var name = $(this).data("name");

            $("#commentStaffName").text("Comments for: " + name);
            $("#commentTimeline").html('<p>Loading comments...</p>');

            $.get('/promotion/comments/' + staffID, function(data) {

                if (data.length > 0) {
                    var html = '';
                    data.forEach(function(comment) {
                        html += `
                    <div class="timeline-item">
                        <div class="timeline-time">${comment.created_at} - <strong>${comment.commented_by}</strong></div>
                        <div class="timeline-panel">${comment.comment}</div>
                    </div>
                `;
                    });
                    $("#commentTimeline").html(html);
                } else {
                    $("#commentTimeline").html('<p>No comments found.</p>');
                }
            });

            $("#commentModal").modal("show");
        });
    </script>
    <script>
        $(document).ready(function() {
            $(document).on("click", ".approve", function() {
                var staffID = $(this).data("id"); // if using data-id
                var staffYear = $(this).data("year");
                var name = $(this).data("name");

                var approvalStatus = $(this).data("approval");
                var progressStage = $(this).data("progress");
                var promotionStatus = $(this).data("status");
                // Put staff ID inside hidden input
                $("#staffID").val(staffID);

                $("#approvalStatus").val(approvalStatus);
                $("#progressStage").val(progressStage);
                $("#promotionStatus").val(promotionStatus);
                // Optional: update modal text
                $("#message1").text(`Approve promotion for ${name} for the year ${staffYear}?`);
                // Show the modal
                $("#approvalModal").modal("show");
            });
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $(".reverseAppr").on('click', function() {
                var staffID = $(this).data("id"); // if using data-id
                var staffYear = $(this).data("year");

                var approvalStatus = $(this).data("approval");
                var progressStage = $(this).data("progress");
                var promotionStatus = $(this).data("status");
                // Put staff ID inside hidden input
                $("#staffID").val(staffID);

                $("#approvalStatus").val(approvalStatus);
                $("#progressStage").val(progressStage);
                $("#promotionStatus").val(promotionStatus);
                // Optional: update modal text
                $("#message").text("Approve staff for year " + staffYear + "?");
                $("#appReversalModal").modal('show');
            });
        });
    </script>

    {{-- <script type="text/javascript">
        $(document).ready(function() {
            $(".approve").on('click', function() {
                var id = $(this).attr('staffid');
                $('.save').attr('staffDataID', id);
                $("#approvalModal").modal('show');
            });
        });
    </script> --}}

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







@stop

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-style.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
@stop

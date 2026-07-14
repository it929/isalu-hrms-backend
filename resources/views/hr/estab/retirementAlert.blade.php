@extends('layouts.layout')
@section('pageTitle')

@endsection

@section('content')
    <div class="box box-default" style="border-top: none;">


    </div>

    <div style="margin: 10px 20px;">
        <div align="center">
            <h3><b>{{ strtoupper('ISALU HOSPITAL LIMITED') }}</b></h3>
            <h5><strong>Staff Due For Retirement</strong></h5>
            <big><b></b></big>
        </div>
        <span class="pull-right" style="margin-right: 30px;">Printed On: {{ date('jS M, Y') }} &nbsp; | &nbsp; Time:
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
    <div class="card">
        <div class="card-body">
            <h3 class="card-title mb-4" style="margin-left:30px">Search Parameters</h3>

            <form class="row gy-2 gx-3 align-items-center" style="padding:30px;"method="POST" action="/retirement/alert">
                {{ csrf_field() }}
                <div class="col-sm-3 mb-4">
                    <label class="" for="autoSizingSelect">Period</label>
                    @if (isset($variable))
                        <select class="form-control" id="autoSizingInput" name="unit" value="{{ $variable }}">

                            <option value="">-- Select Period --</option>
                            <option value="1">Days</option>
                            <option value="2">Months</option>
                            <option value="3">Years</option>
                        </select>
                    @else
                        <select class="form-control" id="autoSizingInput" name="unit" value="{{ old('unit') }}">
                            <option value="">-- Select Period --</option>
                            <option value="1">Days</option>
                            <option value="2">Months</option>
                            <option value="3">Years</option>
                        </select>
                    @endif


                </div>

                <div class="col-sm-3 mb-4">
                    <label class="" for="autoSizingSelect">Duration</label>
                    @if (isset($period))
                        <input type="number" class="form-control" id="autoSizingInput" placeholder="Enter duration"
                            name="period" value="{{ $period }}">
                    @else
                        <input type="number" class="form-control" id="autoSizingInput" placeholder="Enter duration"
                            name="period" value="{{ old('period') }}">
                    @endif




                </div>



                <div class="col-sm-4 mb-4" style="margin-top:25px">
                    <button type="submit" class="btn btn-primary w-md">Submit</button>
                </div>
            </form>
        </div>
        <!-- end card body -->
    </div>
    <div class="box-body">
        <div class="row">
            {{ csrf_field() }}

            <div class="col-md-12">
                <table class="table table-striped table-condensed table-bordered input-sm">
                    <thead>
                        <tr class="input-sm">
                            <th>S/N</th>
                            <th width="250" class="">FULL NAME</th>
                            <th>GRADE</th>
                            <th>STEP</th>
                            <th>DATE OF BIRTH</th>
                            <!--	<th>SEX</th>
                                                                                              <th>MARITAL STATUS</th>
                                                                                              <th>L.G.A OF ORIGIN</th>
                                                                                              <th>STATE OF ORIGIN</th> -->
                            <th>DATE OF FIRST <BR /> APPOINTMENT</th>
                            <th>RANK</th>
                            <th>DATE OF PRESENT <BR /> APPOINTMENT</th>
                            <!--<th>DIVISION</th> -->
                            <th>FILE NO.</th>
                            <th class="hidden-print">RETIREMENT STATUS</th>


                        </tr>
                    </thead>
                    <tbody>
                        @php $key = 1; @endphp
                        @foreach ($getCentralList as $list)
                            <tr>
                                {{-- <td>{{ ($getCentralList->currentpage() - 1) * $getCentralList->perpage() + $key++ }}</td> --}}
                                <td>{{ $key++ }}</td>
                                <td>{{ strtoupper($list->surname . ' ' . $list->first_name . ' ' . $list->othernames) }}
                                </td>
                                <td>{{ $list->grade }}</td>
                                <td>{{ $list->step }}</td>
                                <td width="90">{{ date_format(date_create($list->dob), 'd-m-Y') }}</td>
                                <!-- <td>
                                                                                                    @php
                                                                                                        if (
                                                                                                            strtoupper(
                                                                                                                $list->gender ==
                                                                                                                    'Male',
                                                                                                            )
                                                                                                        ) {
                                                                                                            $sex = 'M';
                                                                                                        } elseif (
                                                                                                            strtoupper(
                                                                                                                $list->gender ==
                                                                                                                    'Female',
                                                                                                            )
                                                                                                        ) {
                                                                                                            $sex = 'F';
                                                                                                        } else {
                                                                                                            $sex = '';
                                                                                                        }
                                                                                                    @endphp
                                                                                                    {{ $sex }}
                                                                                                  </td>
                                                                                                  <td>{{ $list->maritalstatus }}</td>
                                                                                                  <td>{{ $list->lga }}</td>
                                                                                                  <td>{{ $list->State }}</td> -->
                                <td>{{ date_format(date_create($list->appointment_date), 'd-m-Y') }}</td>
                                <td>{{ ' ' . 'GL' . $list->grade . '|' . 'S' . $list->step }}</td>
                                <td>{{ date_format(date_create($list->date_present_appointment), 'd-m-Y') }}</td>
                                <!-- <td></td> -->
                                <td>{{ $list->fileNo }}</td>

                                <td>


                                    {{-- @if ($list->check) --}}
                                    @if (!empty($list->check) && $list->check)
                                        <span
                                            style="
            display: inline-block;
            background-color: #dc3545; /* Bootstrap danger red */
            color: #fff;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        ">
                                            {{ $list->remaining }}
                                        </span>
                                    @else
                                        <span
                                            style="
            display: inline-block;
            background-color: #28a745; /* Bootstrap success green */
            color: #fff;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        ">
                                            {{ $list->remaining }}
                                        </span>
                                    @endif

                                </td>




                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- <div align="right">
                    Showing {{ ($getCentralList->currentpage() - 1) * $getCentralList->perpage() + 1 }}
                    to {{ $getCentralList->currentpage() * $getCentralList->perpage() }}
                    of {{ $getCentralList->total() }} entries
                </div> --}}

                {{-- <div class="hidden-print">{{ $getCentralList->links() }}</div> --}}
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
    </div>


    <!-- Bootsrap Modal for Conversion and Advancemnet-->

    <form method="post" action="">
        {{ csrf_field() }}
        <div id="advModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Candidate Due For Conversion/Advancement</h4>
                        <h3>Name: <span id="name"></span> Old Grade:<span id="oldgrade"></span> Old Step: <span
                                id="oldstep"></span></h3>
                        <p id="message"></p>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Post for Consideration</label>
                            <input type="text" name="postConsidered" id="postcon" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" id="type" class="form-control type ">

                                <option value="Promotion">Promotion</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>New Grade Level</label>
                            <select name="newGrade" id="newGrade" class="form-control grade">
                                <option value="">Select New Grade</option>
                                <option value="1" {{ old('grade') == '1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ old('grade') == '2' ? 'selected' : '' }}>2</option>
                                <option value="3" {{ old('grade') == '3' ? 'selected' : '' }}>3</option>
                                <option value="4" {{ old('grade') == '4' ? 'selected' : '' }}>4</option>
                                <option value="5" {{ old('grade') == '5' ? 'selected' : '' }}>5</option>
                                <option value="6" {{ old('grade') == '6' ? 'selected' : '' }}>6</option>
                                <option value="7" {{ old('grade') == '7' ? 'selected' : '' }}>7</option>
                                <option value="8" {{ old('grade') == '8' ? 'selected' : '' }}>8</option>
                                <option value="9" {{ old('grade') == '9' ? 'selected' : '' }}>9</option>
                                <option value="10" {{ old('grade') == '10' ? 'selected' : '' }}>10</option>
                                <option value="11" {{ old('grade') == '11' ? 'selected' : '' }}>11</option>
                                <option value="12" {{ old('grade') == '12' ? 'selected' : '' }}>12</option>
                                <option value="13" {{ old('grade') == '13' ? 'selected' : '' }}>13</option>
                                <option value="14" {{ old('grade') == '14' ? 'selected' : '' }}>14</option>
                                <option value="15" {{ old('grade') == '15' ? 'selected' : '' }}>15</option>
                                <option value="16" {{ old('grade') == '16' ? 'selected' : '' }}>16</option>
                                <option value="17" {{ old('grade') == '17' ? 'selected' : '' }}>17</option>
                            </select>
                            <input type="hidden" name="fileNo" id="fileNo" class="form-control file-number">
                        </div>

                        <div class="form-group">
                            <label>New Step</label>
                            <select name="newStep" id="newStep" class="form-control grade">
                                <option value="">Select New Step</option>
                                <option value="1" {{ old('grade') == '1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ old('grade') == '2' ? 'selected' : '' }}>2</option>
                                <option value="3" {{ old('grade') == '3' ? 'selected' : '' }}>3</option>
                                <option value="4" {{ old('grade') == '4' ? 'selected' : '' }}>4</option>
                                <option value="5" {{ old('grade') == '5' ? 'selected' : '' }}>5</option>
                                <option value="6" {{ old('grade') == '6' ? 'selected' : '' }}>6</option>
                                <option value="7" {{ old('grade') == '7' ? 'selected' : '' }}>7</option>
                                <option value="8" {{ old('grade') == '8' ? 'selected' : '' }}>8</option>
                                <option value="9" {{ old('grade') == '9' ? 'selected' : '' }}>9</option>
                                <option value="10" {{ old('grade') == '10' ? 'selected' : '' }}>10</option>
                                <option value="11" {{ old('grade') == '11' ? 'selected' : '' }}>11</option>
                                <option value="12" {{ old('grade') == '12' ? 'selected' : '' }}>12</option>
                                <option value="13" {{ old('grade') == '13' ? 'selected' : '' }}>13</option>
                                <option value="14" {{ old('grade') == '14' ? 'selected' : '' }}>14</option>
                                <option value="15" {{ old('grade') == '15' ? 'selected' : '' }}>15</option>
                                <option value="16" {{ old('grade') == '16' ? 'selected' : '' }}>16</option>
                                <option value="17" {{ old('grade') == '17' ? 'selected' : '' }}>17</option>
                            </select>
                            <input type="hidden" name="fileNo" id="fileNo" class="form-control file-number">
                        </div>

                        <div class="form-group">
                            <label>Effective Date</label>
                            <input type="text" name="effectiveDate" id="effectiveDate"
                                class="form-control effectiveDate">

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary adv" id="adv">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- //// Bootsrap Modal for Conversion and Advancemnet-->



@endsection






@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <!-- autocomplete js-->
    <script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            .blinking {
                animation: blinkingText 1.2 s infinite;
            }

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
    </script>


@stop

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-style.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
    <style type="text/css">
        .blinking {
            animation: blinkingText 1.2s infinite;

        }

        @keyframes blinkingText {
            0% {
                color: red;
            }

            49% {
                color: red;
            }

            60% {
                color: transparent;
            }

            99% {
                color: transparent;
            }

            100% {
                color: red;
            }
        }
    </style>
@stop

@extends('layouts.layout')
@section('pageTitle')
    VARIATION
@endsection


@section('content')
    <div class="box box-default" style="border-top: none;">
        <form action="{{ url('/manpower/view/central') }}" method="post">
            {{ csrf_field() }}
            <div class="box-header with-border hidden-print">
                <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
                <span class="pull-right" style="margin-right: 30px;">
                    <div style="float: left;">
                        <div class="wrap">
                            <div class="search">
                                <button type="submit" class="btn btn-default"
                                    style="padding: 6px; float: right; border-radius: 0px;">
                                    <i class="fa fa-search"></i>
                                </button>
                                <input type="text" id="autocomplete_central" name="q" class="form-control"
                                    placeholder="Search By Name or File No."
                                    style="padding: 5px; width: 300px;"><!--searchTerm-->
                                <input type="hidden" id="fileNo" name="fileNo">
                                <input type="hidden" id="monthDay" name="monthDay" value="">
                            </div>
                        </div>
                    </div>
                </span>
        </form>
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
            <big><b></b></big>
        </div>
        <span class="pull-right" style="margin-right: 30px;">Printed On: {{ date('D M, Y') }} &nbsp; | &nbsp; Time:
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
        @if (session('msg'))
            <div class="col-sm-12 alert alert-success alert-dismissible hidden-print" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span>
                </button>
                <strong>Success!</strong>
                {{ session('msg') }}
            </div>
        @endif


        <div class="box-body">
            <div class="row">
                {{ csrf_field() }}

                <div class="col-md-12">
                    <div class="text-center">
                        <h4>Variation (Annual Increment)</h4>
                        <p>Awaiting approval of the Director Admin</p>
                    </div>

                    <table class="table table-striped table-condensed table-bordered input-sm">
                        <thead>
                            <tr class="input-sm">
                                <th>S/N</th>
                                <th>FILE NO</th>
                                <th>STAFF FULL NAME</th>
                                <th>OLD GRADE|STEP</th>
                                <th>NEW GRADE|STEP</th>
                                <th>LAST INCREMENT</th>
                                <th>DUE DATE</th>
                                <th>RAISED ON</th>
                                <th>TAKE ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $key = 1;
                            @endphp
                            @if (count($variationList) > 0)
                                @foreach ($variationList as $list)
                                    {{-- @php
                                        $comm = DB::table('tblvariation_comments')
                                            ->where('ID', '=', $list->varTempID)
                                            ->where('sent_by', '=', Auth::user()->id)
                                            ->where('present_stage', '=', $stage)
                                            ->count();
                                    @endphp --}}
                                    @php
                                        $fileNo = str_replace('/', '-', $list->fileNo);
                                    @endphp
                                    <tr style="{{$list->is_rejected == 1 ? 'background: red; color:white;' : ''}}">
                                        <td>{{ $key }}</td>
                                        <td>{{ $list->fileNo }}</td>
                                        <td>
                                            <p>{{ strtoupper($list->surname . ' ' . $list->first_name . ' ' . $list->othernames) }}
                                            </p>

                                        </td>
                                        <td>GL-{{ $list->old_grade }}|S-{{ $list->old_step }}</td>
                                        <td>GL-{{ $list->new_grade }}|S-{{ $list->new_step }}</td>
                                        <td>{{ $list->incremental_date }}</td>
                                        <td>{{ $list->due_date }}</td>
                                        <td>{{ $list->createdAt }}</td>


                                        <td>
                                            <div class="btn btn-success push btn-sm" id="{{ $list->staffID }}" data-temp-vid="{{ $list->ID }}">Approve</div>

                                            <div class="btn btn-danger decline btn-sm" data-decline-staffid="{{ $list->staffID }}" data-decline-temp-vid="{{ $list->ID }}">Decline </div>

                                            <a href="{{url("/view-staff-variation-comment/$list->ID/$list->year_payment")}}" target="_blank" class="btn btn-info approve btn-sm">Comments </a>


                                        </td>


                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10">
                                        <h4 class="text-center">No record available</h4>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="hidden-print"></div>
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div>

    <form method="post" action="{{ url('/da-send-increment-to-audit') }}">
        {{ csrf_field() }}
        <div id="advModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4><i>Approve Variation for: <span id="name"></span> </i></h4>
                        <p id="messagee" class="text-danger"><i>Audit will be notified</i></p>
                    </div>
                    <div class="modal-bodyy">

                        <input type="hidden" name="staffid" id="staffid">
                        <input type="hidden" name="variationID" id="variationID">
                        <input type="hidden" name="incremental_stage" id="incremental_stage" value="3">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Remark</label>
                                <textarea class="form-control" required name="remark">

                                </textarea>
                            </div>
                        </div>

                        {{-- <div class="col-md-12">
                            <div class="form-group">
                                <label>Forward to</label>
                                <select id="" name="incremental_stage" class="form-control">
                                    <option value="2">Director Admin</option>
                                </select>
                            </div>
                        </div> --}}

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary adv" id="adv">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

        <form method="post" action="{{ url('/da-decline-increment') }}">
        {{ csrf_field() }}
        <div id="declineModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4><i>Decline Variation raised for: <span id="declinename"></span> </i></h4>
                        <p id="message"></p>
                    </div>
                    <div class="modal-bodyy">

                        <input type="hidden" name="declinestaffid" id="declinestaffid">
                        <input type="hidden" name="declinevariationID" id="declinevariationID">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Remark</label>
                                <textarea class="form-control" required name="declineremark">

                                </textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Decline to</label>
                                <select id="" name="incremental_stage" class="form-control">
                                    <option value="1">Deputy Director Admin</option>
                                    <option value="0">Variation Unit</option>
                                </select>
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


@endsection


@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <!-- autocomplete js-->
    <script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>


    <script type="text/javascript">
        $(document).ready(function() {

            $("table tr td .push").click(function() {
                var staffid = $(this).attr('id');
                var variationID = $(this).data('temp-vid');

                $("#advModal").modal('show');
                $("#staffid").val(staffid);
                $("#variationID").val(variationID);

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
                        $('#fileNo').html(data.fileNo)

                    }
                })

            }); //click events end

            $("table tr td .decline").click(function() {
                var staffid = $(this).data('decline-staffid');
                var variationID = $(this).data('decline-temp-vid');

                console.log("staffid", staffid)

                $("#declineModal").modal('show');
                $("#declinestaffid").val(staffid);
                $("#declinevariationID").val(variationID);

                $.ajax({
                    url: murl + '/staff/details/get',
                    type: "post",
                    data: {
                        'staffid': staffid,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        console.log(data);
                        $('#declinename').html(data.surname + ', ' + data.first_name + ' ' + data
                            .othernames);
                        $('#declinefileNo').html(data.fileNo)

                    }
                })

            });
        });
    </script>




@stop

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-style.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
@stop

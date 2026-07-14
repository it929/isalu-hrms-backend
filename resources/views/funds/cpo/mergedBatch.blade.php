@extends('layouts.layout')

@section('pageTitle')
    E-payment Schedule
@endsection

@section('content')
    <div class="box-body">

        <div class="box-body">
            <div class="row">
                <div class="col-sm-12">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">&times;</span>
                            </button>
                            <strong>Error!</strong> <br />
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (session('msg'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">&times;</span>
                            </button>
                            <strong>Success!</strong> <br />
                            {{ session('msg') }}
                        </div>
                    @endif

                    @if (session('err'))
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">&times;</span>
                            </button>
                            <strong>Operation Error !</strong> <br />
                            {{ session('err') }}
                        </div>
                    @endif
                </div>
            </div><!-- /row -->
        </div><!-- /div -->


        <!--search all vouchers-->



        <div class="row">

            <div class="col-md-12">

                <div class="panel panel-default" style="border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.15);">

                    <!-- Card Header -->
                    <div class="panel-heading" style="background:#fff; border-bottom:1px solid #eee;">
                        <h3 class="panel-title text-center" style="font-size:18px; font-weight:bold;">
                            Search Merged Batch
                        </h3>
                    </div>

                    <!-- Card Body -->
                    <div class="panel-body">

                        <form method="post" action="{{ url('/merged-batch/search') }}">
                            {{ csrf_field() }}

                            <div class="row">

                                <!-- Date From -->
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Date From</label>
                                        <input type="text" name="dateFrom" class="form-control" id="dateFrom"
                                            value="@if (session('from') != '') {{ session('from') }} @endif">
                                    </div>
                                </div>

                                <!-- Date To -->
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Date To</label>
                                        <input type="text" name="dateTo" class="form-control" id="dateTo"
                                            value="@if (session('to') == '') {{ date('Y-m-d') }} @else {{ session('to') }} @endif">
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="col-md-2">
                                    <div class="form-group" style="margin-top:25px;">

                                        <button type="submit" name="submit" class="btn btn-success btn-block">
                                            View
                                        </button>
                                    </div>
                                </div>

                            </div>

                        </form>

                    </div> <!-- panel-body -->

                </div> <!-- panel -->

            </div>

        </div>

    </div>
    <!--Search all vouchers-->



    <div class="row">
        <div class="col-sm-12">

            <!-- Card / Panel -->
            <div class="panel panel-default" style="border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.15);">

                <!-- Card Header -->
                <div class="panel-heading text-center" style="background:#fff; border-bottom:1px solid #eee;">
                    <h2 style="margin:0; font-weight:bold;">ISALU HOSPITAL LIMITED</h2>
                    <h3 style="margin-top:5px;">MERGED PAYMENT LIST</h3>
                </div>

                <!-- Card Body -->
                <div class="panel-body">

                    <form action="{{ url('/merged-batch/search') }}" method="post">
                        {{ csrf_field() }}

                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Batch</th>
                                        <th class="text-center">Amount (₦)</th>
                                        <th class="hidden-print">View Mandate</th>
                                        <th class="hidden-print">Update Batch No.</th>
                                        <th>Tick to Restore</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php $key = 1; @endphp
                                    @foreach ($audited as $list)
                                        <tr>
                                            <td>{{ $key++ }}</td>
                                            <td>{{ $list->adjusted_batch }}</td>

                                            <td class="text-center">
                                                {{ number_format($list->sum + $list->vsum + $list->wsum, 2) }}
                                            </td>

                                            <td class="hidden-print">
                                                <a href="{{ url("/view/merge-payments/$list->adjusted_batch") }}"
                                                    class="btn btn-success btn-sm">
                                                    View
                                                </a>
                                            </td>

                                            <td class="hidden-print">
                                                <a href="javascript:void(0)" id="{{ $list->batch }}"
                                                    class="btn btn-primary btn-sm edit">
                                                    Edit
                                                </a>
                                            </td>

                                            <td>
                                                <input type="checkbox" name="toRestore[]" value="{{ $list->batch }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" name="submit" onclick="return ConfirmAction();"
                            class="btn btn-success pull-right">
                            Restore
                        </button>

                    </form>

                </div> <!-- /panel-body -->

            </div> <!-- /panel -->

        </div>
    </div>


    <!-- Modal HTML -->
    <div id="myModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Confirmation</h4>
                </div>
                <div class="modal-body">
                    <div id="desc"></div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                </div>
            </div>
        </div>
    </div>
    <!--///// end modal -->

    <!-- Modal HTML -->
    <form method="post" action="{{ url('/cpo/update-batch-merged') }}">
        {{ csrf_field() }}
        <div id="editModal" class="modal fade">
            <div class="modal-dialog" style="background:#FFF;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Updating Batch Number</h4>
                    </div>
                    <div class="modal-body">

                        <div class="form-group" style="margin-bottom:50px;">
                            <label class="control-label col-md-12">Enter New Batch:</label>
                            <div class="col-md-12">
                                <input type="text" name="newBatch" id="batchNo" class="form-control" required>
                                <input type="hidden" name="batch" id="batch" class="batch">

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-9">
                                <input type="submit" name="submit" class="btn btn-success" value="Save">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                </div>
            </div>
        </div>

    </form>
    <!--///// end modal -->


    <!-- Modal HTML -->
    <form action="{{ url('/move/mandate') }}" method="post">
        {{ csrf_field() }}
        <div id="approveModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Confirmation</h4>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" name="id" id="tid" value="" />
                        <input type="hidden" name="batch" id="batchID" value="" />

                        <div class="form-group" style="margin-bottom:10px;">
                            <div class="col-sm-122">
                                <label class="control-label"><b>Enter Remarks</b></label>
                            </div>
                            <div class="col-sm-122">
                                <textarea name="instruction" id="instruction" class="form-control" placeholder="e.g Pay a sum amount of XXXXX"> </textarea>
                            </div>
                            <div class="col-sm-122">
                                <label class="control-label"><b>Refer to</b></label>
                            </div>
                            <div class="col-sm-122">
                                <select required name="nextAction" class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($codes as $list)
                                        <option value="{{ $list->code }}">{{ $list->description }}</option>
                                    @endforeach

                                </select>
                            </div>

                        </div>



                        <div class="clearfix"></div>

                        <input type="submit" name="submit" value="Proceed"
                            class="btn btn-success pull-right hidden-print" style="margin-left:10px;">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>

                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </form>
    <!--///// end modal -->



    <!--Rejection reason Modal HTML -->
    <div id="rejectModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Reason For Rejecting</h4>
                </div>
                <div class="modal-body">

                    <div id="reason">

                    </div>



                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                </div>
            </div>
        </div>
    </div>
    <!--///// end Rejection reason Modal -->


@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-style.css') }}">

    <style type="text/css">
        .status {
            font-size: 15px;
            padding: 0px;
            height: 100%;

        }

        .textbox {
            border: 1px;
            background-color: #66FFBA;
            outline: 0;
            height: 25px;
            width: 275px;
        }

        $('.autocomplete-suggestions').css({
            color: 'red'
        });

        .autocomplete-suggestions {
            color: #66FFBA;
            height: 125px;
        }

        .table,
        tr,
        td {
            border: #9f9f9f solid 1px !important;
            font-size: 12px !important;
        }

        .table thead tr th {
            font-weight: 700;
            font-size: 17px;
            border: #9f9f9f solid 1px
        }
    </style>
@endsection


@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/js/daterangepicker.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("table tr td .edit").click(function() {
                var batchNo = $(this).attr('id');
                $(".batch").val(batchNo);
                $("#batchNo").val(batchNo);

                $("#editModal").modal('show');

            });
        });
    </script>
    <script>
        function ConfirmAction() {
            var x = confirm("Are you sure you want to restore this merged payment?");
            if (x)
                return true;
            else
                return false;
        }
    </script>

    <script>
        $(document).ready(function() {

            $(".pro").click(function() {
                var batch = $(this).attr('id');
                //alert(batch);

                $('#batchID').val(batch);
                $("#approveModal").modal('show');

            });

        });
    </script>

    <script>
        $(document).ready(function() {

            $(".reason").click(function() {

                var id = $(this).attr('val');
                var batch = $(this).attr('id');

                $.ajax({
                    // headers: {'X-CSRF-TOKEN': $token},
                    url: "{{ url('/rejection/reason') }}",

                    type: "post",
                    data: {
                        'batch': batch,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        //location.reload(true);
                        console.log(data.comment);
                        $('#reason').html(data.comment);
                    }
                });


                $("#rejectModal").modal('show');

            });

        });
    </script>

    <script type="text/javascript">
        $(function() {
            $("#dateTo").datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '1910:2090', // specifying a hard coded year range
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat: "dd MM, yy",
                //dateFormat: "D, MM d, yy",
                onSelect: function(dateText, inst) {
                    var theDate = new Date(Date.parse($(this).datepicker('getDate')));
                    var dateFormatted = $.datepicker.formatDate('yy-mm-dd', theDate);
                    $("#dateTo").val(dateFormatted);
                },
            });

        });

        $(function() {
            $("#dateFrom").datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '1910:2090', // specifying a hard coded year range
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat: "dd MM, yy",
                //dateFormat: "D, MM d, yy",
                onSelect: function(dateText, inst) {
                    var theDate = new Date(Date.parse($(this).datepicker('getDate')));
                    var dateFormatted = $.datepicker.formatDate('yy-mm-dd', theDate);
                    $("#dateFrom").val(dateFormatted);
                },
            });

        });
    </script>
@endsection

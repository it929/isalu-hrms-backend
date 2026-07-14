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

    </div>
    <!--Search all vouchers-->



    <div class="row">
        <div class="col-md-12">

            <!-- Bootstrap 3 Card Equivalent -->
            <div class="panel panel-success">

                <!-- Panel Header -->
                <div class="panel-heading text-center">
                    <h3 class="panel-title" style="font-size: 18px;">
                        ISALU HOSPITAL LIMITED
                    </h3>
                </div>

                <!-- Panel Body -->
                <div class="panel-body">

                    <!-- Titles -->
                    <h3 class="text-center" style="margin-top: 10px;">BATCH LIST</h3>
                    <br>

                    <!-- Table Container -->
                    <form action="{{ url('/cpo/restore') }}" method="post">
                        {{ csrf_field() }}

                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered table-striped">
                                <thead style="background: #f5f5f5;">
                                    <tr>
                                        <th>S/N</th>
                                        <th>Batch</th>
                                        <th class="text-center">Amount (&#8358;)</th>
                                        <th class="hidden-print">View mandate</th>
                                        {{-- <th class="hidden-print"></th> --}}
                                        {{-- <th class="hidden-print">Update Batch No.</th>
                                        <th class="hidden-print">Next Action</th> --}}
                                        {{-- <th>Rejection Status</th> --}}
                                    </tr>
                                </thead>

                                <tbody>
                                    @php $key = 1; @endphp
                                    @foreach ($audited as $list)
                                        <tr>
                                            <td>{{ $key++ }}</td>
                                            <td>{{ $list->adjusted_batch }}</td>
                                            <td class="text-center">
                                                {{ number_format($list->sum + $list->vsum + $list->wsum + $list->stampsum, 2) }}</td>

                                            <!-- View Remarks -->
                                            {{-- <td class="hidden-print">
                                                <a href="{{ url('/display/comments/' . $list->batch) }}" target="_blank"
                                                    class="btn btn-success btn-xs">
                                                    View Remarks
                                                </a>
                                            </td> --}}

                                            <!-- View Batch -->
                                            <td class="hidden-print">
                                                <a href="{{ url('/view/batch/' . $list->batch) }}"
                                                    class="btn btn-success btn-xs">
                                                    View
                                                </a>
                                            </td>

                                            <!-- Edit Batch -->
                                            {{-- <td class="hidden-print">
                                                <a href="javascript:void(0)" class="btn btn-success btn-xs edit"
                                                    id="{{ $list->batch }}">
                                                    Edit
                                                </a>
                                            </td> --}}

                                            <!-- Process -->
                                            {{-- <td class="hidden-print">
                                                <a href="javascript:void(0)" class="btn btn-success btn-xs pro"
                                                    id="{{ $list->batch }}">
                                                    Process
                                                </a>
                                            </td> --}}

                                            {{-- <td class="hidden-print">
                                                @if($list->is_paid_from_bank == 0)
                                                <a href="javascript:void(0)" class="btn btn-primary btn-xs pro2"
                                                    id2="{{ $list->batch }}">
                                                    Mark As Paid
                                                </a>
                                                @else
                                                    <a href="#" class="btn btn-success btn-xs"> Payment Made </a>
                                                @endif
                                            </td> --}}

                                            <!-- Rejection Message -->
                                            {{-- <td>
                                                @if ($list->rejection_status == 1)
                                                    This batch was rejected by {{ $list->rejected_by }}
                                                    <a href="javascript:void(0)" class="btn btn-danger btn-xs reason"
                                                        id="{{ $list->batch }}">
                                                        View Reason
                                                    </a>
                                                @endif
                                            </td> --}}

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>

                <!-- Optional Footer -->
                <div class="panel-footer text-center">
                    <small class="text-muted">End of Batch List</small>
                </div>

            </div> <!-- End Panel -->

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
    <form method="post" action="{{ url('/cpo/update-batch') }}">
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

    <form action="{{ url('/mark-bank-payment') }}" method="post">
        {{ csrf_field() }}
        <div id="markAsPaidModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Payment Bank Confirmation</h4>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" name="id2" id="tid2" value="" />
                        <input type="hidden" name="batch2" id="batchID2" value="" />

                        <div class="form-group" style="margin-bottom:10px;">
                            <div class="col-sm-12">
                                <label class="control-label"><b>Enter Remarks</b></label>
                            </div>
                            <div class="col-sm-12">
                                <textarea name="instruction" id="instruction" class="form-control">
                    Payment amount of XXXXX has been  processed from bank
                    </textarea>
                            </div>


                        </div>

                        <div class="clearfix"></div>


                    </div>
                    <div class="modal-footer">
                        <input type="submit" name="submit" value="Proceed"
                            class="btn btn-success pull-right hidden-print" style="margin-left:10px;">

                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>

                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </form>

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
    <script src="{{ asset('assets/js/jquery.autocomplete.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            $("#autocomplete").autocomplete({
                serviceUrl: murl + '/searchUser',
                minLength: 2,
                onSelect: function(suggestion) {
                    //alert('hello');
                    $('#nameID').val(suggestion.data);
                    //   alert(suggestion.data);


                }
            });
        });
    </script>


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
        $(document).ready(function() {

            $(".pro").click(function() {
                var batch = $(this).attr('id');
                //alert(batch);

                $('#batchID').val(batch);
                $("#approveModal").modal('show');

            });

            $(".pro2").click(function() {
                var batch = $(this).attr('id2');
                //alert(batch);

                $('#batchID2').val(batch);
                $("#markAsPaidModal").modal('show');

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

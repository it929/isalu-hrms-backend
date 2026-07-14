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

                <!-- Bootstrap 3 Card Equivalent -->
                <div class="panel panel-success" style="padding: 6px">

                    <!-- Header -->
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-search"></i> Search Batch
                        </h3>
                    </div>

                    <!-- Body -->
                    <div class="panel-body">
                        <form method="post" action="{{ url('/batch/search') }}">
                            {{ csrf_field() }}

                            <div class="row">

                                <!-- Date From -->
                                <div class="col-md-3" style="padding: 2px;">
                                    <label><strong>Date From</strong></label>
                                    <input type="text" name="dateFrom" class="form-control" id="dateFrom"
                                        value="{{$selectedfromDate != '' ? $selectedfromDate : ''}}">
                                </div>

                                <!-- Date To -->
                                <div class="col-md-3" style="padding: 2px;">
                                    <label><strong>Date To</strong></label>
                                    <input type="text" name="dateTo" class="form-control" id="dateTo"
                                        value="{{$selectedtoDate != '' ? $selectedtoDate : ''}}">
                                </div>

                                <div class="col-md-3" style="padding: 2px;">
                                    <label><strong>Contract Type</strong></label>
                                    <select class="form-control" id="contracttype" name="contracttype">
                                            <option value="">Select...</option>
                                            @foreach ($contractlist as $list)
                                                <option value="{{ $list->ID }}"
                                                    {{ $selectedContractType == $list->ID ? 'selected' : '' }}>
                                                    {{ $list->contractType }}</option>
                                            @endforeach
                                        </select>
                                </div>

                                <!-- Voucher Number -->
                                <div class="col-md-3" style="padding: 2px;">
                                    <label><strong>Enter Voucher Number</strong></label>
                                    <input type="text" name="voucherNumber" class="form-control"
                                        value='{{ $selectedVoucherNumber ?? '' }}' 
                                        placeholder="Search By Voucher Number"
                                        >
                                </div>

                                <!-- Submit Button -->
                                <div class="col-md-2" style="padding: 2px; margin-top: 25px;">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

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

                    <div style="margin-bottom:15px;">
                        {{-- <button type="button" class="btn btn-primary" id="exportSelected">
                            Export Selected Separately
                        </button> --}}

                        <button type="button" class="btn btn-success" id="exportConsolidated">
                            Export Consolidated
                        </button>
                    </div>

                    <form id="consolidatedForm" method="POST" action="{{ url('/export/consolidated') }}">
                        @csrf
                        <input type="hidden" name="ids" id="selectedIds">
                    </form>

                    <!-- Table Container -->
                    <form action="{{ url('/cpo/restore') }}" method="post">
                        {{ csrf_field() }}

                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered table-striped">
                                <thead style="background: #f5f5f5;">
                                    <tr>
                                        <th>
                                            {{-- <input type="checkbox" id="checkAll"> --}}
                                        </th>
                                        <th>S/N</th>
                                        <th>Batch</th>
                                        <th class="text-center">Amount (&#8358;)</th>
                                        {{-- <th class="hidden-print">View Remarks</th> --}}
                                        <th class="hidden-print" colspan="2">Actions</th>
                                        {{-- <th class="hidden-print">Update Batch No.</th> --}}
                                        {{-- <th class="hidden-print">Next Action</th> --}}
                                        {{-- <th>Rejection Status</th> --}}
                                    </tr>
                                </thead>

                                <tbody>
                                    @php $key = 1; @endphp
                                    @forelse ($audited as $list)
                                        <tr>
                                            <td>
                                                <input type="checkbox"
                                                    class="rowCheck"
                                                    value="{{ $list->adjusted_batch }}"
                                                    data-contract="{{ $list->contract_typeID }}"
                                                    data-account="{{ $list->NJCAccount }}">
                                            </td>
                                            <td>{{ $key++ }}</td>
                                            <td> {{ $list->adjusted_batch }} </td>
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
                                                {{-- <a href="{{ url('/view/batchbytransactionid/' . $list->transactionID) }}"
                                                    class="btn btn-success btn-xs">
                                                    View Batches
                                                </a> --}}
                                                {{-- <a href="{{ url('/view/mandateByTransactionid/' . $list->transactionID) }}"
                                                    class="btn btn-success btn-xs">
                                                    View All Mandate
                                                </a> --}}
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

                                            <td class="hidden-print">
                                                @if($list->is_paid_from_bank == 0)
                                                <a href="javascript:void(0)" class="btn btn-primary btn-xs pro2"
                                                    id2="{{ $list->batch }}" markPaidAmt="{{ number_format($list->sum + $list->vsum + $list->wsum + $list->stampsum, 2) }}">
                                                    Mark as Sent to Bank
                                                </a>
                                                @else
                                                    <span class="label label-success"> Sent to bank </span>
                                                @endif
                                            </td>

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
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No Batch Found</td>
                                        </tr>
                                    @endforelse
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
                        <h4 class="modal-title">Payment Sent to Bank Confirmation</h4>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" name="id2" id="tid2" value="" />
                        <input type="hidden" name="batch2" id="batchID2" value="" />

                        <div class="form-group" style="margin-bottom:10px;">
                            <div class="col-sm-12">
                                <label class="control-label"><b>Enter Remarks</b></label>
                            </div>
                            <div class="col-sm-12">
                                {{-- <textarea name="instruction" id="instruction" class="form-control">Payment amount of <span id="markPaidAmt"></span> has been  processed from bank
                                </textarea> --}}
                                <textarea name="instruction" id="paidInstruction" class="form-control"></textarea>
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
                var markPaidAmt = $(this).attr('markPaidAmt');
                //alert(batch);

                $('#batchID2').val(batch);
                // $('#markPaidAmt').val(markPaidAmt);
                $('#paidInstruction').val(
                    'Payment amount of ' + markPaidAmt + ' for mandate number ' + batch + ' has been sent to bank'
                );
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

    <script type="text/javascript">
        // Select All
        // $("#checkAll").on('click', function () {
        //     $(".rowCheck").prop('checked', this.checked);
        // });

        // Select All
        $("#checkAll").on('click', function () {

            var checked = this.checked;

            $(".rowCheck").each(function () {
                if (!$(this).is(':disabled')) {
                    $(this).prop('checked', checked);
                }
            });
        });


        $(".rowCheck").on('change', function () {

            var selected = $(".rowCheck:checked");

            // If nothing selected → reset everything
            if (selected.length === 0) {
                $(".rowCheck").prop('disabled', false);
                $("tr").css('opacity', '1');
                return;
            }

            // Get first selected row's values
            var selectedContract = selected.first().data('contract');
            var selectedAccount  = selected.first().data('account');

                $(".rowCheck").each(function () {

                    var rowContract = $(this).data('contract');
                    var rowAccount  = $(this).data('account');

                    // Disable rows that do not match BOTH conditions
                    if (rowContract != selectedContract || rowAccount != selectedAccount) {

                        $(this).prop('checked', false);
                        $(this).prop('disabled', true);
                        $(this).closest('tr').css('opacity', '0.4');

                    } else {

                        $(this).prop('disabled', false);
                        $(this).closest('tr').css('opacity', '1');

                    }

                });

        });

        function getSelectedTransactions() {
            var selected = [];

            $(".rowCheck:checked").each(function () {
                selected.push($(this).val());
            });

            return selected;
        }

        $("#exportConsolidated").click(function () {

            var selected = getSelectedTransactions();

            if (selected.length === 0) {
                alert("Please select at least one batch.");
                return;
            }

            $("#selectedIds").val(JSON.stringify(selected));
            $("#consolidatedForm").submit();

        });
    </script>
@endsection

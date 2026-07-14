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
                    <h3 class="text-center" style="margin-top: 10px;">{{$bankComponents[0]->batch}}</h3>
                    <br>

                    <!-- Table Container -->
                    <form action="{{ url('/cpo/restore') }}" method="post">
                        {{ csrf_field() }}

                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered table-striped">
                                <thead style="background: #f5f5f5;">
                                    <tr>
                                        <th>
                                            
                                        </th>
                                        <th>S/N</th>
                                        <th>Beneficiary</th>
                                        <th class="text-center">Amount (&#8358;)</th>
                                        <th>Bank</th>
                                        <th>Account No.</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php $key = 1; @endphp
                                    @forelse ($bankComponents as $list)
                                        <tr>
                                            <td>
                                                <input type="checkbox"
                                                    class="rowCheck"
                                                    value="{{ $list->ID }}"
                                                    data-batch="{{ $list->batch }}"
                                                    data-contract="{{ $list->contract_typeID }}"
                                                    data-account="{{ $list->NJCAccount }}"
                                                    >
                                            </td>
                                            <td>{{ $key++ }}</td>
                                            <td> {{ $list->contractor }} </td>
                                            <td class="text-center">
                                                {{ number_format($list->amount, 2) }}</td>

                                            <td> {{ $list->bank }} </td>
                                            <td> {{ $list->accountNo }} </td>
                                            

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

                @if($bankComponents->where('payment_status', '!=', 0)->isEmpty())
                <div class="text-center" style="margin-top: 10px;">
                    <button type="button" id="submitBtn" class="btn btn-primary">Submit Selected</button>
                </div>
                @endif

                <!-- Optional Footer -->
                <div class="panel-footer text-center">
                    <small class="text-muted">End of Batch List</small>
                </div>

            </div> <!-- End Panel -->

        </div>
    </div>


    <!-- Submit Modal -->
    <div id="submitModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Payment Confirmation</h4>
                </div>
                <form action="{{ url('/cpo/submit-selected-bank-paid') }}" method="post">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <input type="hidden" name="selected" id="selectedIds">
                        <div class="form-group">
                            <label for="date_paid">Date Paid:</label>
                            <input type="date" name="date_paid" id="date_paid" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                selected.push({
                    id: $(this).val(),
                    batch: $(this).data('batch'),
                    contractType: $(this).data('contract'),
                    Njcaccount: $(this).data('account')
                });
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

    <script type="text/javascript">
        $("#submitBtn").click(function() {
            var selected = getSelectedTransactions();
            if (selected.length === 0) {
                alert("Please select at least one item.");
                return;
            }
            $("#selectedIds").val(JSON.stringify(selected));
            $("#submitModal").modal('show');
        });
    </script>
@endsection

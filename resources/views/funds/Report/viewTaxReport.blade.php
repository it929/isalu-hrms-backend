@extends('layouts.layout')
@section('pageTitle')
    <b>TAX MATTER</b> - <span class="text-italic">ALL AVAILABLE RECORDS</span>
@endsection



@section('content')



    <div class="panel panel-default">

        <!-- Panel Heading -->
        <div class="panel-heading hidden-print">
            <h3 class="panel-title">
                @yield('pageTitle')
                <span id="processing"></span>
            </h3>
        </div>

        <!-- Panel Body -->
        <div class="panel-body">

            <div class="row">
                <div class="col-md-12">

                    @include('funds.Share.message')

                    <!-- ============================= -->
                    <!--      SEARCH FILTER CARD       -->
                    <!-- ============================= -->
                    <div class="panel panel-success hidden-print">
                        <div class="panel-heading">
                            <h4 class="panel-title">Search Filter</h4>
                        </div>

                        <div class="panel-body">
                            <form method="post">
                                {{ csrf_field() }}

                                <div class="row">

                                    <div class="col-md-2">
                                        <label>From</label>
                                        <input type="text" class="form-control" value="{{ $from }}"
                                            name="from" id="from">
                                    </div>

                                    <div class="col-md-2">
                                        <label>To</label>
                                        <input type="text" class="form-control" value="{{ $to }}"
                                            name="to" id="to">
                                    </div>

                                    <div class="col-md-3">
                                        <label>Select TAX/WHT/STD Element</label>
                                        <select name="element" class="form-control">
                                            <option value="">-Select element-</option>
                                            <option value="1" {{ $element == 1 ? 'selected' : '' }}>VAT Element Only
                                            </option>
                                            <option value="2" {{ $element == 2 ? 'selected' : '' }}>WHT Element Only
                                            </option>
                                            <option value="3" {{ $element == 3 ? 'selected' : '' }}>Stamp Duty Only
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Select Record Type</label>
                                        <select name="rtype" class="form-control">
                                            <option value="" {{ $rtype == '' ? 'selected' : '' }}>All Records
                                            </option>
                                            <option value="1" {{ $rtype == 1 ? 'selected' : '' }}>Not Committed
                                            </option>
                                            <option value="2" {{ $rtype == 2 ? 'selected' : '' }}>Committed</option>
                                            <option value="3" {{ $rtype == 3 ? 'selected' : '' }}>Paid</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label>Capital/Recurrent</label>
                                        <select name="rc" class="form-control">
                                            <option value="All" {{ $rc == '' ? 'selected' : '' }}>-All-</option>
                                            @foreach ($Recurrent_Capital as $b)
                                                <option value="{{ $b->id }}" {{ $rc == $b->id ? 'selected' : '' }}>
                                                    {{ $b->text }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12 text-right" style="margin-top:10px;">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ============================= -->
                    <!--           REPORT CARD         -->
                    <!-- ============================= -->
                    <div class="panel panel-success">
                        <div class="panel-heading text-center">
                            <h4 class="panel-title">Tax Report</h4>
                        </div>
                        @php $elementType = ($getReportDetails)? $getReportDetails[0]->element_:""; @endphp
                        <div class="panel-body">


                            <div class="row">
                                <div class="col-xs-2">
                                    <img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive">
                                </div>

                                <div class="col-xs-8 text-center">
                                    <h3 class="text-success"><strong>ISALU HOSPITAL LIMITED</strong></h3>
                                    <h4 class="text-success"><strong>THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h4>
                                    <h4 class="text-success"><strong>Tax Report</strong></h4>
                                </div>

                                <div class="col-xs-2">
                                    <img src="{{ asset('Images/coat.jpg') }}" class="img-responsive">
                                </div>
                            </div>

                            <div class="text-center" style="margin-top:15px;">
                                <strong>
                                    SCHEDULE OF {{ $elementType }} RETURNS TO FIRS FOR THE MONTH OF
                                    {{ date_format(date_create($to), 'F, Y') }}
                                </strong>
                            </div>

                            <br>

                            <div class="table-responsive" id="tableData">
                                <table class="table table-bordered table-striped table-highlight text-center">
                                    <thead>
                                        <tr style="background:#f0f0f0; font-size:11px;">
                                            <th>SN</th>
                                            <th>BENEFICIARY TIN</th>
                                            <th>NAME</th>
                                            <th>ADDRESS</th>
                                            <th>INVOICE</th>
                                            <th>DATE</th>
                                            <th>DESCRIPTION</th>
                                            <th>TYPE</th>
                                            <th>AMOUNT</th>
                                            <th>RATE (%)</th>
                                            <th>DEDUCTED</th>
                                            <th>NET</th>
                                            <th>PERIOD</th>
                                            <th class="hidden-print">VIEW</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php
                                            $i = 1;
                                            $totalPayment = 0;
                                            $totalDeduct = 0;
                                            $totalnetPay = 0;
                                        @endphp

                                        @forelse($getReportDetails as $list)
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ $list->TIN }}</td>
                                                <td>{{ $list->contractor }}</td>
                                                <td>{{ $list->address }}</td>
                                                <td></td>
                                                <td>{{ $list->transdate }}</td>

                                                <td>
                                                    <a href="#" data-toggle="modal"
                                                        data-target="#editRecordModal{{ $list->ID }}">
                                                        {{ $list->tax_description ?: $list->paymentDescription }}
                                                    </a>

                                                    &nbsp;
                                                    <a href="#" class="hidden-print" data-toggle="modal"
                                                        data-target="#refreshModal{{ $list->ID }}">
                                                        <i class="fa fa-refresh"></i>
                                                    </a>
                                                </td>

                                                <td>{{ $list->element_ }}</td>

                                                <td>
                                                    @php $totalPayment += $list->totalPayment; @endphp
                                                    {{ number_format($list->totalPayment, 2) }}
                                                </td>

                                                <td>{{ $list->element_per }}%</td>

                                                <td>
                                                    @php $totalDeduct += $list->element_value; @endphp
                                                    {{ number_format($list->element_value, 2) }}
                                                </td>

                                                <td>
                                                    @php $totalnetPay += $list->amtPayable; @endphp
                                                    {{ number_format($list->amtPayable, 2) }}
                                                </td>

                                                <td>{{ date_format(date_create($list->transdate), 'F, Y') }}</td>

                                                <td class="hidden-print">
                                                    <a href="{{ url('/display/voucher/' . $list->ID) }}" target="_blank">
                                                        <i class="fa fa-eye fa-2x"></i>
                                                    </a>
                                                </td>
                                            </tr>

                                            <!-- Your modals stay untouched -->
                                            @include('funds.modals.editTaxRecord')
                                            @include('funds.modals.refreshTaxRecord')

                                        @empty
                                            <tr>
                                                <td colspan="20" class="text-center">
                                                    No Record Found!
                                                </td>
                                            </tr>
                                        @endforelse

                                        <tr>
                                            <td colspan="8"><strong>Total</strong></td>
                                            <td>{{ number_format($totalPayment, 2) }}</td>
                                            <td></td>
                                            <td>{{ number_format($totalDeduct, 2) }}</td>
                                            <td>{{ number_format($totalnetPay, 2) }}</td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div><!-- panel-body -->
                    </div><!-- panel-success -->

                    <button class="btn btn-primary hidden-print" id="btnExport" onclick="Export()">Export to
                        Excel</button>

                </div>
            </div>

        </div>
    </div>

@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">

@stop

@section('styles')
    <style type="text/css">
        .modal-dialog {
            width: 13cm
        }

        .modal-header {

            background-color: #006600;

            color: #FFF;

        }

        #partStatus {
            width: 2.5cm
        }
    </style>

@endsection

@section('scripts')

    <script src="/assets/js/table2excel.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript">
        //Date Picker Range
        $(function() {

            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                $('#startDate').val(start.format('YYYY-MM-D'));
                $('#endDate').val(end.format('YYYY-MM-D'));
                $('#searchDate').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }
            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
            }, cb);
            cb(start, end);
        });
        //Date Picker Range


        //Submit Retire Form
        $(document).ready(function() {

            //Update Description
            $(".updateRecordForTaxMatter").click(function() {
                // alert("kdjddj");
                var getRecordID = this.id;
                var description = $('#description' + getRecordID).val();
                var descriptionID = $('#descriptionID' + getRecordID).val();
                var newDescription = $(".getElement" + getRecordID + " option:selected").text();
                $('#pageDescription' + getRecordID).val(newDescription);
                //

                if (descriptionID == '') {
                    alert('Sorry description cannot be empty');
                    return false;
                }

                $.ajax({
                    url: murl + '/update_tax_matter_report',
                    type: "post",
                    data: {
                        'descriptionID': descriptionID,
                        'recordID': getRecordID,
                        'paymentDescription': description,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        $('#descriptionID' + getRecordID).val('');
                        location.reload();
                    }
                })
            });

            //Revert Description
            $(".revertDescriptionTaxMatter").click(function() {
                var getRecordID = this.id;
                $.ajax({
                    url: murl + '/revert_update_tax_matter_report',
                    type: "post",
                    data: {
                        'recordID': getRecordID,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        location.reload();
                    }
                })
            });


            //Get Description ID
            $(".getTextID").change(function() {
                var getRecordID = this.id;

                $('#descriptionID' + getRecordID).val('');
                //$('#description' + getRecordID).val('');

                $('#descriptionID' + getRecordID).val($(".getElement" + getRecordID).val());
                //$('#description' + getRecordID).val($(".getElement" + getRecordID + " option:selected").text());
                $('#pageDescription' + getRecordID).val($(".getElement" + getRecordID + " option:selected")
                    .text());

            });

        });

        //
    </script>



    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script>
        $('#res_tab').DataTable({
            "paging": false // false to disable pagination (or any other option)
        });

        $(function() {
            $("#todayDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
        });

        $(function() {
            $("#dateawd").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
        });

        $("#check-all").change(function() {
            $(".checkitem").prop("checked", $(this).prop("checked"))
        })
        $(".checkitem").change(function() {
            if ($(this).prop("checked") == false) {
                $("#check-all").prop("checked", false)
            }
            if ($(".checkitem:checked").length == $(".checkitem").length) {
                $("#check-all").prop("checked", true)
            }
        })

        $(function() {
            $("#from").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#to").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
        });


        function Export() {
            $("#tableData").table2excel({
                filename: "chart_of_account.xls"
            });
        }
    </script>
@stop

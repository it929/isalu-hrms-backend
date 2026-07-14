@extends('layouts.layout')
@section('pageTitle')
@endsection

@section('content')
    <div class="box box-default" style="border:none;">

        <div class="box-body">

            <div class="row">
                <div class="col-xs-2"><img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                        style="width:100%; height:auto;"></div>
                <div class="col-xs-8">
                    <div>
                        <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
                        <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h4>
                        <h4 class="text-center text-success"><strong>Vote Vouchers Transactions</strong></h4>
                    </div>
                </div>
                <div class="col-xs-2"><img src="{{ asset('Images/coat.jpg') }}" class="responsive"></div>
            </div <div class="box-header with-border hidden-print">
            <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
        </div>
        @if ($warning != '')
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $warning }}</strong>
            </div>
        @endif
        @if ($success != '')
            <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $success }}</strong>
            </div>
        @endif
        @if (count($errors) > 0)
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span>
                </button>
                <strong>Error!</strong>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <form method="post" id="thisform1" name="thisform1">
            {{ csrf_field() }}
            <div class="box-body">
                <div class="row hidden-print">
                    <div class="col-md-2">
                        <label>Period</label>
                        <select name="period" id="period" class="form-control" onchange="ReloadForm();">
                            <option value="" selected>-Select Year-</option>
                            @foreach ($YearPeriod as $b)
                                <option value="{{ $b->Period }}" {{ $period == $b->Period ? 'selected' : '' }}>
                                    {{ $b->Period }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!--<div class="col-md-2">
         <label>Allocation Source</label>
            <select name="allocationsource" id="allocationsource" class="form-control" onchange ="ReloadForm();">
          <option value="" selected>-All-</option>
          @foreach ($AllocationSource as $b)
    <option value="{{ $b->ID }}" {{ $allocationsource == $b->ID ? 'selected' : '' }}>{{ $b->allocation }}</option>
    @endforeach
          </select>
         </div>-->
                    <div class="col-md-2">
                        <label>Budget Type</label>
                        <select name="budgettype" id="budgettype" class="form-control" onchange="ReloadForm();">
                            <option value="" selected>-All-</option>
                            @foreach ($BudgetType as $b)
                                <option value="{{ $b->ID }}" {{ $budgettype == $b->ID ? 'selected' : '' }}>
                                    {{ $b->contractType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Economic Head</label>
                        <select name="economichead" id="economichead" class="form-control" onchange="ReloadForm();">
                            <option value="" selected>-All-</option>
                            @foreach ($EconomicHead as $b)
                                <option value="{{ $b->ID }}" {{ $economichead == $b->ID ? 'selected' : '' }}>
                                    {{ $b->economicHead }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Economic Codes</label>
                        <select name="economiccode" id="economiccode" class="form-control" onchange="ReloadForm();">
                            <option value="" selected>-All-</option>
                            @foreach ($EconomicCode as $b)
                                <option value="{{ $b->ID }}" {{ $economiccode == $b->ID ? 'selected' : '' }}>
                                    {{ $b->economicCode }}|{{ $b->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control" onchange="ReloadForm();">
                            @foreach ($Statuss as $b)
                                <option value="{{ $b->code }}" {{ $status == $b->code ? 'selected' : '' }}>
                                    {{ $b->fundtext }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="row">
                </div>
                <input id="delcode" type="hidden" name="delcode">

                <div class="table-responsive" style="font-size: 12px; padding:10px;">
                    <table class="table table-bordered table-striped table-highlight" id="mytable">
                        <thead>
                            <tr bgcolor="#c7c7c7">

                                <th>Date</th>
                                <th>Voucher No</th>
                                <th>Payment Description</th>
                                <th>Amount</th>
                                <th>Print</th>



                            </tr>
                        </thead>

                        @php $total = 0; @endphp
                        @foreach ($VoteTrans as $b)
                            <tr>

                                <td>{{ $b->datePrepared }} </td>
                                <td>{{ $b->vref_no }} </td>
                                <td>{{ $b->paymentDescription }} </td>
                                <td class="align">{{ number_format($b->totalPayment, 2, '.', ',') }} </td>

                                <td><a href="/display/voucher/{{ $b->ID }}" class="btn btn-success btn-sm">Print</a>
                                </td>
                            </tr>
                            @php $total += $b->totalPayment; @endphp
                        @endforeach
                        <tr>
                            <td>Total </td>

                            <td> </td>
                            <td> </td>
                            <td class="align">{{ number_format($total, 2, '.', ',') }} </td>
                            <td></td>

                        </tr>
                    </table>
                    <button type="submit" class="print">Print</button>
                </div>
            </div>

        </form>

    </div>
@endsection

@section('styles')
    <style>
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">@media print {
            .hidden-print {
                display: none !important
            }

            .dt-buttons,
            .dataTables_info,
            .dataTables_paginate,
            .dataTables_filter {
                display: none !important
            }
        }

        .align {
            text-align: right;
        }
    </style>
@endsection

@section('scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">

    <script type="text/javascript">
        $('.print').click(function() {
            window.print();
        });
    </script>

    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#fields').multiselect({
                nonSelectedText: 'Select fields to view',
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '400px',
                includeSelectAllOption: true,
            });
        });
    </script>
    <script type="text/javascript">
        function checkForm() {
            var fields = document.getElementById('fields').value;
            var form = document.getElementById('thisform1');
            if (fields == '') {
                alert('Please select fields to view');
                return false;
            } else {
                form.submit();
            }
            return false;
        }

        function ReloadForm() {
            //alert("ururu")	;
            document.getElementById('thisform1').submit();
            return;
        }

        function DeletePromo(id) {
            var cmt = confirm('You are about to delete a record. Click OK to continue?');
            if (cmt == true) {
                document.getElementById('delcode').value = id;
                document.getElementById('thisform1').submit();
                return;

            }

        }
        $(function() {
            $("#todate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#fromdate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#appointmentDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#incrementalDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#firstArrivalDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#mytable').DataTable({
                dom: 'mytable',
                buttons: [{
                    extend: 'print',
                    customize: function(win) {
                        $(win.document.body)
                            .css('font-size', '10pt')
                            .prepend(
                                ''
                            );

                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    }
                }]
            });
        });
    </script>
@endsection

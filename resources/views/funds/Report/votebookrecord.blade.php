@extends('layouts.layout')
@section('pageTitle')

@endsection
@section('content')


    <div class="box box-default">
        <div class="box-body box-profile">
            <div class="box-header with-border">
                <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-2"><img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                            style="width:100%; height:auto;"></div>
                    <div class="col-xs-8">
                        @php
                            $selected = collect($EconomicCode)->firstWhere('ID', old('economiccode', $economiccode));
                        @endphp

                        <div>
                            <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
                            <h4 class="text-center text-success"><strong>THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h4>
                            <h4 class="text-center text-success"><strong>Vote Book Report</strong></h4>
                            @if ($selected)
                                <h5 class="text-success text-center">
                                    <strong>Economic Code: {{ $selected->economicCode }}</strong>
                                </h5>

                                <h6 class="text-success text-center">
                                    <strong>Description: </strong>
                                    {{ $selected->description }}
                                </h6>
                            @endif
                        </div>
                    </div>
                    <div class="col-xs-2"><img src="{{ asset('Images/coat.jpg') }}" class="responsive"></div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12"><!--1st col-->
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



                        <form class="form-horizontal hidden-print" role="form" id="thisform1" name="thisform1"
                            method="post">
                            {{ csrf_field() }}
                            <div class="row hidden-print">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                        </div>
                                        <div class="col-md-2">

                                            <label>Period</label>
                                            <select name="period" id="period" class="form-control"
                                                onchange="ReloadForm();">
                                                <option value="" selected>-Select Year-</option>
                                                @foreach ($YearPeriod as $b)
                                                    <option value="{{ $b->Period }}"
                                                        {{ old('period', $period) == $b->Period ? 'selected' : '' }}>
                                                        {{ $b->Period }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="control-label">Date From</label>
                                            <input type="text" class="col-sm-9 form-control"
                                                value="{{ old('fromdate', $fromdate) }}" name="fromdate" id="fromdate">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="control-label">Date To</label>
                                            <input type="text" class="col-sm-9 form-control"
                                                value="{{ old('todate', $todate) }}" name="todate" id="todate">
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>


                                    <div class="form-group">
                                        <input type="hidden" name="allocationsource" value="5" />
                                        <div class="col-md-3">
                                            <label>Budget Type</label>
                                            <select name="budgettype" id="budgettype" class="form-control"
                                                onchange="ReloadForm();">
                                                <option value="" selected>-All-</option>
                                                @foreach ($BudgetType as $b)
                                                    <option value="{{ $b->ID }}"
                                                        {{ old('budgettype', $budgettype) == $b->ID ? 'selected' : '' }}>
                                                        {{ $b->contractType }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Economic Head</label>
                                            <select name="economichead" id="economichead" class="form-control"
                                                onchange="ReloadForm();">
                                                <option value="" selected>-All-</option>
                                                @foreach ($EconomicHead as $b)
                                                    <option value="{{ $b->ID }}"
                                                        {{ old('economichead', $economichead) == $b->ID ? 'selected' : '' }}>
                                                        {{ $b->economicHead }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label>Economic Codes</label>
                                            <select name="economiccode" id="economiccode" class="form-control"
                                                onchange="ReloadForm();">
                                                <option value="" selected>-All-</option>
                                                @foreach ($EconomicCode as $b)
                                                    <option value="{{ $b->ID }}"
                                                        {{ old('economiccode', $economiccode) == $b->ID ? 'selected' : '' }}>
                                                        {{ $b->economicCode }}|{{ $b->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">

                                        <br>
                                        <label class="control-label"></label>
                                        <button type="submit" class="btn btn-success" name="add">
                                            <i class="fab fa-btn fa-sistrix"></i> Search
                                        </button>

                                        <button type="button" class="btn btn-primary" onclick="window.print()">
                                            <i class="fa fa-print"></i> Print
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </form>





                        <div class="table-responsive" style="font-size: 12px; padding:10px;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>

                                        <th>Line No</th>
                                        <th>Date</th>
                                        <th>Voucher No</th>
                                        <th>Particular</th>
                                        <th>Payment</th>
                                        <th>Total</th>
                                        <th>Balance</th>
                                        <th>Liab Ref</th>
                                        <th>Incurred</th>
                                        <th>Cleared</th>
                                        <th>Total Outstanding</th>
                                        <th>Remarks</th>
                                        <th>Balance Available</th>
                                        {{-- <th>Action</th> --}}

                                    </tr>
                                </thead>
                                @php $serialNum = 1; @endphp
                                {{-- @foreach ($VoteBookRecord as $b) --}}
                                @foreach (collect($VoteBookRecord)->sortBy('id') as $b)
                                    <tr id="row-{{ $b->id }}"
                                        class="{{ $b->cancel_status == 1 ? 'cancelled-row' : '' }}">
                                        <td>{{ $serialNum++ }}</td>
                                        <td>{{ $b->trandate }}</td>
                                        <td>
                                            @if (is_numeric($b->refNo))
                                            @else
                                                {{ $b->refNo }}
                                            @endif
                                        </td>
                                        <td style="width: 200px;">
                                            @if ($b->balance == 0)
                                                {{ $b->particular }} {{ $b?->payment }}
                                            @else
                                                {{ $b->particular }} {{ $b?->payment }}
                                            @endif
                                        </td>
                                        <td class="align">
                                            @if ($b->payment == 0)
                                                {{ $b->payment }}
                                            @else
                                                {{ number_format((float) $b?->payment, 2) }}
                                            @endif
                                        </td>
                                        <td class="align">
                                            @if ($b->total == 0)
                                                {{ $b->total }}@else{{ number_format((float) $b?->total, 2) }}
                                            @endif
                                        </td>
                                        <td class="align">
                                            @if ($b->balance == 0)
                                                {{ $b->balance }}@else{{ number_format((float) $b?->balance, 2) }}
                                            @endif
                                        </td>
                                        <td>{{ $b->liaref }}</td>
                                        <td class="align">
                                            @if ($b->incurred == 0)
                                                {{ $b->incurred }}@else{{ number_format((float) $b?->incurred, 2) }}
                                            @endif
                                        </td>
                                        <td class="align">
                                            @if ($b->cleared == 0)
                                                {{ $b->cleared }}@else{{ number_format((float) $b?->cleared, 2) }}
                                            @endif
                                        </td>
                                        <td class="align">
                                            @if ($b->liatotaloutstanding == 0)
                                                {{ $b->liatotaloutstanding }}@else{{ number_format((float) $b?->liatotaloutstanding, 2) }}
                                            @endif
                                        </td>
                                        <td>{{ $b->remark }}</td>
                                        <td class="align">{{ number_format((float) $b?->availablebal, 2) }}</td>
                                        {{-- <td>
                                             <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                        </td> --}}

                                        {{-- <td>
                                            <form action="{{ route('votebook.cancel.toggle', $b->id) }}" method="POST">
                                                @csrf


                                                <input type="hidden" name="fromdate"
                                                    value="{{ old('fromdate', $fromdate) }}">
                                                <input type="hidden" name="todate"
                                                    value="{{ old('todate', $todate) }}">
                                                <input type="hidden" name="period"
                                                    value="{{ old('period', $period) }}">
                                                <input type="hidden" name="budgettype"
                                                    value="{{ old('budgettype', $budgettype) }}">
                                                <input type="hidden" name="economichead"
                                                    value="{{ old('economichead', $economichead) }}">
                                                <input type="hidden" name="economiccode"
                                                    value="{{ old('economiccode', $economiccode) }}">
                                                <input type="hidden" name="allocationsource"
                                                    value="{{ old('allocationsource', $allocationsource) }}">


                                                @if ($b->cancel_status == 1)
                                                    <button type="submit" class="btn btn-success">
                                                        Revert
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        Cancel
                                                    </button>
                                                @endif
                                            </form>
                                        </td> --}}

                                    </tr>
                                @endforeach
                            </table>
                        </div>

                        <hr />
                    </div>

                </div>
            </div>




        @endsection

        @section('styles')
            <style type="text/css">
                .cancelled-row td {
                    background-color: #f8d7da !important;
                    color: #721c24;
                }

                .modal-dialog {
                    width: 15cm
                }

                .modal-header {

                    background-color: #20b56d;

                    color: #FFF;

                }

                @media print {
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
            </style>
        @endsection

        @section('scripts')
            <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
            <link rel="stylesheet" type="text/css"
                href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
            <link rel="stylesheet" type="text/css"
                href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">

            <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

            <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>

            <script>
                function ReloadForm() {
                    document.getElementById('thisform1').submit();
                    return;
                }




                $(function() {
                    $("#fromdate").datepicker({
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: 'yy-mm-dd'
                    });
                    $("#todate").datepicker({
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: 'yy-mm-dd'
                    });
                });

                $(document).ready(function() {
                    $('#').DataTable();
                });

                $(document).ready(function() {
                    $('#mytable').DataTable({
                        dom: 'Bfrtip',
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


        @stop

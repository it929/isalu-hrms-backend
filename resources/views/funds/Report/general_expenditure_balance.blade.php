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
                        <h4 class="text-center text-success"><strong>SUPREME COURT, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong>
                        </h4>
                        <h4 class="text-center text-success"><strong>{{ $allotext == '' ? '' : $allotext }} Balance as at
                                {{ date('l, d F, Y', strtotime($to)) }}</strong></h4>
                    </div>
                </div>
                <div class="col-xs-2"><img src="{{ asset('Images/coat.jpg') }}" class="responsive"></div>
            </div>
            <div class="box-header with-border hidden-print">
                <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
            </div>

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
                        <div class="col-md-2 ">
                            <label>From</label>
                            <input type="text" class="col-sm-9 form-control" value="{{ $from }}" name="from"
                                id="from">
                        </div>
                        <div class="col-md-2">
                            <label>To</label>
                            <input type="text" class="col-sm-9 form-control" value="{{ $to }}" name="to"
                                id="to">
                        </div>
                        <input type="hidden" name="allocationsource" value="5" />
                        <div class="col-md-2">
                            <label>Account Type</label>
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
                    </div>

                    <button type="submit" class="btn btn-success hidden-print">
                        Reload
                    </button>
                    <div class="row">
                    </div>
                    <input id="delcode" type="hidden" name="delcode">

                    <div class="table-responsive" style="font-size: 12px; padding:10px;">
                        <table class="table table-bordered">

                            {{-- TABLE HEADER --}}
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Eco/CD</th>
                                    <th>Description</th>
                                    <th class="align">Balance at hand</th>
                                </tr>

                                <tr>
                                    {{-- <th colspan="3">
                                        Total Allocation (January to {{ date('F', strtotime($to)) }})
                                    </th> --}}
                                    {{-- <th class="align">
                                        {{ number_format($TotalAllocationTodate->total, 2) }}
                                    </th> --}}
                                </tr>
                            </thead>

                            {{-- INITIALIZE TOTALS --}}
                            @php
                                $grouphead = 0;
                                $totalbal = 0;
                            @endphp

                            {{-- TABLE BODY --}}
                            <tbody>
                                @foreach ($QueryVoultReport as $b)
                                    {{-- ECONOMIC HEAD GROUP --}}
                                    @if ($b->economicHeadID != $grouphead)
                                        <tr>
                                            @php $grouphead = $b->economicHeadID; @endphp
                                            <td><strong>{{ $b->economicheadcode }}</strong></td>
                                            <td></td>
                                            <td colspan="2">
                                                <strong>
                                                    {{ $b->economichead }}
                                                    ({{ $b->economicgroup }}-{{ $b->allocationsource }})
                                                </strong>
                                            </td>
                                        </tr>
                                    @endif

                                    {{-- DATA ROW --}}
                                    @php
                                        // Calculate balance at hand
                                        $balanceAtHand = $b->receivedallocation - $b->expendtodate - $b->outstandinglib;
                                        $totalbal += $balanceAtHand;
                                    @endphp
                                    <tr>
                                        <td></td>
                                        <td>{{ $b->economiccode }}</td>
                                        <td>{{ $b->economicdisc }}</td>
                                        <td class="align">
                                            {{ number_format($balanceAtHand, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            {{-- FOOTER TOTALS --}}
                            <tr>
                                <th colspan="3">Grand Total </th>
                                <th class="align">
                                    {{ number_format($totalbal, 2) }}
                                </th>
                            </tr>
                        </table>

                        {{-- <button class="print hidden-print" type="button">Print</button> --}}
                        <button class="print hidden-print" type="button" onclick="printPage()">
                            Print
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
@endsection

@section('scripts')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">

    <style>
        .table tr th {
            text-transform: uppercase;
            font-size: 14px;
        }

        .table tr td {
            font-size: 14px;
        }

        @media print {
            .hidden-print {
                display: none !important
            }

            /* .dt-buttons,
            .dataTables_info,
            .dataTables_paginate,
            .dataTables_filter {
                display: none !important
            } */

            .box,
            .box-body,
            .row,
            .table-responsive {
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
        }

        .align {
            text-align: right;
        }

        .table-bordered td,
        .table-bordered th {
            border: 1px solid #444 !important
        }
    </style>

    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script type="text/javascript">
        function ReloadForm() {
            document.getElementById('thisform1').submit();
            return;
        }

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
    </script>
    <script>
        function printPage() {
            window.print();
        }
    </script>
@endsection

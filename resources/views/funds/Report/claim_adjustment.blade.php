@extends('layouts.layout')
@section('pageTitle')

@endsection
@section('content')


    <div class="box box-default" style="padding-bottom:0px; margin-bottom:0px;padding-top:0px; margin-top:0px;">
        <div class="box-body box-profile">
            <div class="box-header with-border">
                <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
            </div>
            <div class="box-body" style="padding-bottom:0px; margin-bottom:0px;">
                <div class="row">
                    <div class="col-xs-2"><img src="{{ asset('Images/scn_logo.jpg') }}" class="img-responsive responsive"
                            style="width:100%; height:auto;"></div>
                    <div class="col-xs-8">
                        <div>
                            <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
                            <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong>
                            </h4>
                            <h4 class="text-center text-success"><strong>Approval/Action Comments</strong></h4>
                        </div>
                    </div>
                    <div class="col-xs-2"><img src="{{ asset('Images/coat.jpg') }}" class="responsive"></div>
                </div>
            </div>
            <div class="box-body" style="padding-top:0px; margin-top:0px;">
                <div class="row">
                    <div class="col-md-12"><!--1st col-->

                        <div class="panel panel-default"
                            style="padding-bottom:0px; margin-bottom:0px;padding-top:0px; margin-top:0px;">
                            <div class="panel-heading fieldset-preview"><b>Claim details</b></div>
                            <div class="panel-body">
                                <table class="table table-striped table-hover table-responsive table-condensed">
                                    <tbody class="btn-lg">
                                        <tr>
                                            <td><b>Description </b></td>
                                            <td><b>{{ $claiminfo->Title }}:{{ $claiminfo->details }} </b></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <table class="table table-striped table-hover table-responsive table-condensed">
                                    <tbody class="btn-lg">
                                        <tr>
                                            <td><b>Amount Request </b></td>
                                            <td><b>{{ number_format($claiminfo->amount, 2, '.', ',') }} </b></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <table class="table table-striped table-hover table-responsive table-condensed">
                                    <tbody class="btn-lg">
                                        <tr>
                                            <td><b>Request By </b></td>
                                            <td><b>{{ $claiminfo->name }} </b></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="clearfix"></div>
                            </div>
                            <form class="form-horizontal" role="form" method="post" action="">
                                {{ csrf_field() }}
                                @if ($claimclaim_beneficiaries)
                                    <table class="table table-striped table-hover table-responsive table-condensed">
                                        <tbody class="btn-lg">
                                            <tr>
                                                <td><b>Staff name(s)</b></td>
                                                <td style="text-align: right; "><b>Amount </b></td>
                                            </tr>
                                            @php $amt=0; @endphp
                                            @foreach ($claimclaim_beneficiaries as $b)
                                                <tr>
                                                    <td>{{ $b->full_name }} @if ($b->remarks != '')
                                                            ({{ $b->remarks }})
                                                        @endif
                                                    </td>
                                                    <td><input type="text" class="form-control"
                                                            id="amount{{ $b->selectedID }}"
                                                            name="amount{{ $b->selectedID }}"
                                                            value="{{ $b->staffamount }}" onkeyup='Subtotal()'
                                                            style="width:200px;text-align: right;" autocomplete="off"></td>
                                                </tr>
                                                @php $amt+=is_numeric($b->staffamount)?$b->staffamount:0; @endphp
                                            @endforeach
                                            <tr>
                                                <td>Total
                                                    <br><br><a class="btn btn-success" href="/review-es">Back</a>
                                                <td style="width:200px;text-align: right;"><input type="text"
                                                        class="form-control" id="amt"
                                                        value="{{ number_format($amt, 2, '.', ',') }}"
                                                        style="width:200px;text-align: right;" readonly>
                                                    <br><button class="btn btn-success" type="submit" name="update"
                                                        name="update">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            </form>
                            @foreach ($claimcomments as $b)
                                <div class="panel panel-default">
                                    <div class="panel-heading fieldset-preview"><b>Comment by: {{ $b->name }} on
                                            {{ date('F j, Y', strtotime($b->created_at)) }}
                                            {{ date('g:i a', strtotime($b->created_at)) }}</b></div>
                                    <div class="panel-body">
                                        {{ $b->comment }}
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <hr />
                        <div class="col-md-12">


                        </div>
                    </div>

                </div>
            </div>




        @endsection

        @section('styles')
            <style type="text/css">
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

            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
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

                function Subtotal() {
                    var total = 0;
                    @foreach ($claimclaim_beneficiaries as $list)
                        total += parseFloat(document.getElementById("amount{{ $list->selectedID }}").value);
                    @endforeach
                    document.getElementById("amt").value = total.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    return;
                }
            </script>


        @stop

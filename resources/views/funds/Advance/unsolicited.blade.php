@extends('layouts.layout')
@section('pageTitle')

@endsection
@section('content')

    <div id="clearModal" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Clearance Minute</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="form-horizontal" role="form" method="POST" action="">

                    {{ csrf_field() }}
                    <div class="modal-body">

                        <div class="form-group" style="margin: 0 10px;">
                            <h4 class="modal-title">You are about to retire and link the request with selected voucher. Do
                                you really want to continue?</h4>
                            <div class="col-sm-12">
                                <label class="control-label"><b>Enter remark</b></label>
                            </div>
                            <div class="col-sm-12">
                                <textarea name="remark" class="form-control" id= "remark">Being retirement for  </textarea>
                            </div>
                            <div class="col-sm-12">
                                <label class="control-label"><b>Amount Retired</b></label>
                            </div>
                            <div class="col-sm-12">
                                <input type="text" name="amount" id="amount" class="form-control" value="">
                            </div>
                            <input type="hidden" id="clearid" name="clearid">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="Submit" name="retire"class="btn btn-success">Save and continue</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <div class="box box-default" style="padding-bottom:0px; margin-bottom:0px;padding-top:0px; margin-top:0px;">
        <div class="box-body box-profile">
            <div class="box-header with-border">
                <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
            </div>
            <div class="box-body" style="padding-bottom:0px; margin-bottom:0px;">
                <div class="row">
                    <div class="col-xs-2"><img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                            style="width:100%; height:auto;"></div>
                    <div class="col-xs-8">
                        <div>
                            <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
                            <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h4>
                            <h4 class="text-center text-success"><strong>Voucher Retirement</strong></h4>
                        </div>
                    </div>
                    <div class="col-xs-2"><img style="width:100%; height:auto;" src="{{ asset('Images/coat.png') }}" class="responsive"></div>
                </div>
            </div>
            <div class="box-body" style="padding-top:0px; margin-top:0px;">
                <div class="col-md-12">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>Error!</strong>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if (session('err'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>Input Error!</strong> <br />
                            {{ session('err') }}
                        </div>
                    @endif
                    @if (session('msg'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>Success!</strong> <br />
                            {{ session('msg') }}
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>

    <div class="table-responsive col-md-12" style="font-size: 12px; padding:10px;">
        <table id="res_tab" class="table table-bordered table-striped table-highlight">
            <thead>
                <tr bgcolor="#c7c7c7">
                    <th>S/N</th>
                    <th>Action</th>
                    <th>PVNO</th>
                    <th>Beneficiary</th>
                    <th>Contract/claim Description</th>
                    <th>Payment Naration</th>
                    <th>Total Amount</th>
                    <th> Date Approved </th>
                </tr>
            </thead>
            @php $i = 0; @endphp
            <tbody>
                @if ($tablecontent)
                    @foreach ($tablecontent as $list)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-danger btn-xs dropdown-toggle" type="button" id="dropdownMenu1"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                        Action
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                        <li>
                                            {{-- <a onclick="accept('{{ $list->ID }}','{{ $list->ContractDescriptions }}','{{ $list->totalPayment }}')"> --}}
                                            <a onclick='accept(
                                                        {{ $list->ID }},
                                                        @json($list->ContractDescriptions),
                                                        @json($list->totalPayment)
                                                    )'>
                                            Retire  Now</a></li>
                                        <li><a href="/display/voucher/{{ $list->ID }}">Preview</a></li>
                                        <li><a href="/display/comment/{{ $list->conID }}" target="_blank">View
                                                Minutes</a></li>
                                    </ul>
                                </div>
                            </td>
                            <td> SCN/AD/{{ $list->vref_no }}/{{ $list->period }}</td>
                            @if ($list->voucherType == '1')
                                <td>{{ $list->contractor }}</td>
                            @else
                                <td>{{ $list->payment_beneficiary }}</td>
                            @endif
                            <td>{{ $list->ContractDescriptions }}</td>
                            <td>{{ $list->paymentDescription }}</td>
                            <td>{{ number_format($list->totalPayment, 2) }}</td>
                            <td>{{ $list->dateAward }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="100%">
                            <center>No Voucher to check</center>
                        </td>
                    </tr>
                @endif

            </tbody>
        </table>
        <br><br><br><br><br>
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

        function addattachment(x) {
            //document.getElementById('cid').value = x;
            $("#attachModal").modal('show');
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

        function accept(id, remark, amount) {
            document.getElementById('clearid').value = id
            document.getElementById('amount').value = amount
            document.getElementById('remark').value = remark
            $("#clearModal").modal('show')
        }
    </script>


@stop

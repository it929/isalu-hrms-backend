@extends('layouts.layout')
@section('pageTitle')

@endsection
@section('content')


    <div class="box box-default" style="border:none;">
        <div class="box-body box-profile">
            <div class="box-header with-border">
                <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-2"><img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                            style="width:100%; height:auto;"></div>
                    <div class="col-xs-8">
                        <div>
                            <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
                            <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h4>
                            <h4 class="text-center text-success"><strong>Payment Transactions</strong></h4>
                        </div>
                    </div>
                    <div class="col-xs-2"><img src="{{ asset('Images/coat.jpg') }}" class="responsive"></div>
                </div <div class="box-body">
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
                            method="post" action="{{ url('report/transactions') }}">
                            {{ csrf_field() }}

                            <div class="col-md-12"><!--2nd col-->
                                <!-- /.row -->
                                <div class="form-group">


                                    <div class="col-md-2">
                                        <label class="control-label">Contactor</label>
                                        <select class="form-control" id="contractor" name="contractor"
                                            onchange="ReloadForm()">
                                            <option value=""> All</option>
                                            @foreach ($contractorDetails as $c)
                                                <option value="{{ $c->id }}"
                                                    {{ $contractor == $c->id ? 'selected' : '' }}>{{ $c->contractor }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="control-label">Date From</label>
                                        <input type="text" class="col-sm-9 form-control" value="{{ $datefrom }}"
                                            name="dateFrom" id="dateFrom">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="control-label">Date To</label>
                                        <input type="text" class="col-sm-9 form-control" value="{{ $dateto }}"
                                            name="dateTo" id="dateTo">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="control-label">Amount Range (Min)</label>
                                        <input type="text" class="col-sm-9 form-control" value="{{ $min }}"
                                            name="min" id="">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="control-label">Amount Range (Max)</label>
                                        <input type="text" class="col-sm-9 form-control" value="{{ $max }}"
                                            name="max" id="">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="control-label">Allocation Type</label>
                                        <select name="allocationType" class="form-control" onchange="ReloadForm()">
                                            <option Value=" ">All</option>
                                            @foreach ($allocation as $a)
                                                <option value="{{ $a->ID }}"
                                                    {{ $allocationType == $a->ID ? 'selected' : '' }}>{{ $a->allocation }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="control-label">Contract Type</label>
                                        <select name="contract" class="form-control" onchange="ReloadForm()">
                                            <option Value=" ">All</option>
                                            @foreach ($contractType as $con)
                                                <option value="{{ $con->ID }}"
                                                    {{ $contract == $con->ID ? 'selected' : '' }}>{{ $con->contractType }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="control-label">Economic Code</label>
                                        <select class="form-control" id="economicCode" name="economicCode">
                                            <option value=" ">All</option>
                                            @foreach ($economic as $e)
                                                <option value="{{ $e->ID }}"
                                                    {{ $economicCode == $e->ID ? 'selected' : '' }}>{{ $e->description }}
                                                    -
                                                    {{ $e->economicCode }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="control-label">Status{{ $status }}</label>
                                        <select class="form-control" id="status" name="status"
                                            onchange="ReloadForm()">
                                            <option value=" ">All</option>
                                            @foreach ($Mainstatus as $M)
                                                <option value="{{ $M->code }}"
                                                    {{ ((string) $status) == (string) $M->code ? 'selected' : ' ' }}>
                                                    {{ $M->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>






                                    <div class="col-md-2 hidden-print">
                                        <br>
                                        <label class="control-label"></label>
                                        <button type="submit" class="btn btn-success" name="add">
                                            <i class="fab fa-btn fa-sistrix"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- /.col -->
                    </div>
                    <!-- /.row -->

                    </form>

                    <div class="table-responsive col-md-12" style="font-size: 12px; padding:10px;">
                        <table id="mytable" class="table table-bordered table-striped table-highlight">
                            <thead>
                                <tr bgcolor="#c7c7c7">


                                    <th>S/N</th>
                                    <th> Contractor</th>

                                    <th> Payment Description</th>
                                    <th> Status</th>
                                    <th> Voucher Date</th>
                                    <th> Amount (₦)</th>

                                    <th class="hidden-print"> Action</th>
                                </tr>
                            </thead>
                            @php
                                $i = 1;
                                $grossAmount = 0.0;
                            @endphp


                            @foreach ($transactions as $t)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $t->contractor }}</td>

                                    <td>{{ $t->paymentDescription }}</td>
                                    <td>

                                        @if ($t->status == 1)
                                            <span class="text-success"> Approved </span>
                                        @elseif($t->status == 2)
                                            <span class="text-primary"> Booked </span>
                                        @elseif($t->status == 3)
                                            <span class="text-danger"> Rejected </span>
                                        @elseif($t->status == 4)
                                            <span class="text-success"> Commenced </span>
                                        @elseif($t->status == 5)
                                            <span class="text-success"> Complete </span>
                                        @elseif($t->status == 6)
                                            <span class="text-success"> Paid </span>
                                        @else
                                            <span class="text-danger"> Pending </span>
                                        @endif

                                    </td>
                                    <td>{{ date('d-m-Y', strtotime($t->datePrepared)) }}</td>
                                    <td style="text-align:right;">{{ number_format($t->totalPayment, 2) }}</td>

                                    <td class="hidden-print">
                                        <a href="{{ url('/display/voucher') }}/{{ $t->ID }}"
                                            class="btn btn-primary fa fa-eye" class="" id=""> View</a>
                                    </td>



                                </tr>
                            @endforeach
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <th>Total</th>
                                    <td style="text-align:right; padding-right:10px;"> @php $grossAmount = 0.0; @endphp
                                        @foreach ($transactions as $t)
                                            @php
                                                $grossAmount += $t->totalPayment;
                                            @endphp
                                        @endforeach
                                        {{ number_format($grossAmount, 2) }}
                                    </td>

                                    <td class="hidden-print"></td>
                                </tr>
                            </tfoot>
                        </table>

                    </div>

                    <hr />
                </div>

            </div>
        </div>
    </div>


    <!-- /.box -->





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

        function ReloadForm2() {
            document.getElementById('editBModal').submit();
            return;
        }

        function editfunc(a, b, c, d, e, f, g) {
            $(document).ready(function() {
                $('#period').val(a);
                $('#allocationType').val(b);
                $('#economicGroup').val(c);
                $('#economicCode').val(d);
                $('#budget').val(e);
                $('#economicHead').val(f);
                $('#B_id').val(g);
                $("#editModal").modal('show');
            });
        }

        function delfunc(a, b) {
            $(document).ready(function() {
                $('#conID').val(a);
                $('#status').val(b);
                $("#delModal").modal('show');
            });
        }


        $(function() {
            $("#dateFrom").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#dateTo").datepicker({
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

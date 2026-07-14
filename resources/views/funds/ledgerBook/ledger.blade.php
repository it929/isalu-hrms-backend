@extends('layouts.layout')
@section('pageTitle')
@endsection

<style type="text/css">
    .table,
    .table tr td,
    .table tr th {
        border: 1px solid #333;
    }

    .table>thead>tr>th,
    .table>tbody>tr>th,
    .table>tfoot>tr>th,
    .table>thead>tr>td,
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
        border-top: 1px solid #333;
    }
</style>

@section('content')
    <div class="box box-default" style="border-top: none;">
        <div style="margin: 10px 20px;">
            @if (session('err'))
                <div class="alert alert-warning alert-dismissible hidden-print" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span> </button>
                    <strong>Error!</strong> {{ session('err') }}
                </div>
            @endif
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-2"><img src="{{ asset('Images/scn_logo.jpg') }}" class="img-responsive responsive"
                        style="width:140px; height:120px;"></div>
                <div class="col-xs-8">
                    <div>
                        <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
                        <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h4>
                        <h4 class="text-center text-success"><strong>Purchase Ledger</strong></h4>
                    </div>
                </div>
                <div class="col-xs-2"><img src="{{ asset('Images/coat.jpg') }}" class="responsive"></div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <div class="col-md-12">
                        <div class="form-group" class="hidden-print">
                        </div>
                    </div>
                </div>
            </div>

            <!-- date picker -->


            <div class="row" style="margin-top:5px; padding-top: 15px; padding-left: 30px; padding-right: 30px;">

                <form action="{{ url('/ledger/view') }}" method="post">
                    {{ csrf_field() }}
                    <div class="col-md-12 hidden-print" style="padding-top: 0px; margin-top: 5px; font-size: 15px;">
                        <!--<div class="row hidden-print">
                        <div class="col-md-12">
                            <div class="col-sm-8">
                                    ======customized Date Picker=========-->
                        <!--<div class="form-group">
                    <label >Select Date/Year/Period of Time</label>
                    <div id="reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 0px 0px 0 15px; border: 1px solid #ccc; width: 100%">
                       <i class="glyphicon glyphicon-calendar fa fa-calendar" style="padding-top: 7px;"></i>&nbsp;
                       <span></span>
                       <b class="caret"></b>
                       <input type="text" name="selectDate" id="selectDate" class="form-control" style="background: #fff; cursor: pointer; border: none; width: 90%; float: right; border-radius: 0; outline: none !important; border-style:none !important; border: none !important; border-color: transparent !important;" readonly>
                   </div>
                   <input type="hidden" name="getYear" id="getYear" >
                   <input type="hidden" name="getFrom" id="getFrom" >
                   <input type="hidden" name="getTo"   id="getTo" >
                 -->

                        <!--=======End date Picker=========-->
                        <!-- </div>

               </div>-->

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date">Select to view Paid/Unpaid</label>
                                    <select name="voucherStatus" class="form-control input-lg">
                                        <option value="">Select</option>
                                        <option value="6" @if (Session::get('paystatus') == 6) selected @endif>Paid
                                        </option>
                                        <option value="2" @if (Session::get('paystatus') == 2) selected @endif>UnPaid
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date">Start Date</label>
                                    <input type="text" name="getFrom" id="getFrom" class="form-control input-lg"
                                        required value="{{ Session::get('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date">End Date</label>
                                    <input type="text" name="getTo" id="getTo" class="form-control input-lg"
                                        required value="{{ Session::get('date_to') }}">
                                </div>
                            </div>


                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-success input-lg"
                                            style="border: #333; border-radius: 0; outline: none !important; margin-left: -25px; padding: 8px;"><i
                                                class="fa fa-search"></i> Display</button>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End Row -->
                    </div>
                </form>

                <!-- end date picker -->


                <div class="col-md-12" style="padding-top: 0px; margin-top: 5px; font-size: 15px;">
                    <table class="table " style="font-size: 16px;border-top: 1px solid #333;">

                        <tr style="border-top: 1px solid #333;">

                            <th>DATE</th>
                            <th>NARRATION</th>
                            <th>REF NO.</th>
                            <th>DR</th>
                            <th>CR</th>
                            <th>BALANCE</th>
                            <th>REMARK</th>
                        </tr>


                        @php $serial = 1; @endphp
                        <?php
                        $contractor = '';
                        $contractId = '';
                        ?>
                        @foreach ($ledger as $lists)
                            <?php
                            if ($lists->contractID != $contractId) {
                                echo '
                                      <tr>
                                     <td  colspan="8" style=" padding:8px; border:1px solid #333; background-color:; color:#333;"><b>' .
                                    $lists->contractor .
                                    ' :</b></td>
                                     </tr>

                                      ';
                                $contractor = $lists->contractor;
                                $contractId = $lists->contractID;
                            }

                            ?>


                            <tr>

                                <td>{{ $lists->datePrepared }}</td>
                                <td>{{ $lists->paymentDescription }}</td>
                                <td>{{ $lists->ID }} </td>
                                <td>{{ number_format($lists->amtPayable + $lists->WHTValue + +$lists->VATValue, 2, '.', ',') }}
                                </td>
                                <td> {{ number_format($lists->amtPayable + $lists->WHTValue + +$lists->VATValue, 2, '.', ',') }}
                                </td>

                                <td>
                                    {{ number_format($lists->balance->BBF - $lists->totalPayment, 2, '.', ',') }}
                                </td>
                                <td>
                                    @if ($lists->payStatus == 6)
                                        {{ 'Paid' }}
                                    @else
                                        {{ 'Not Yet Paid' }}
                                    @endif
                                </td>

                            </tr>
                        @endforeach


                    </table>
                    <div class="hidden-print"></div>
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

    </div>
@endsection
@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/daterangepicker.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <!-- autocomplete js-->
    <script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/moment.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/daterangepicker.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(function() {
            $('#searchName').attr("disabled", true);
            $("#autocomplete").autocomplete({
                serviceUrl: murl + '/data/searchUser',
                minLength: 2,
                onSelect: function(suggestion) {
                    $('#fileNo').val(suggestion.data);
                    $('#searchName').attr("disabled", false);
                    //showAll();
                }
            });
        });
    </script>



    <script type="text/javascript">
        $(function() {
            $("#getFrom").datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '1910:2090', // specifying a hard coded year range
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat: "dd MM, yy",
                //dateFormat: "D, MM d, yy",
                onSelect: function(dateText, inst) {
                    var theDate = new Date(Date.parse($(this).datepicker('getDate')));
                    var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
                    $("#getFrom").val(dateFormatted);
                },
            });

        });

        $(function() {
            $("#getTo").datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '1910:2090', // specifying a hard coded year range
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat: "dd MM, yy",
                //dateFormat: "D, MM d, yy",
                onSelect: function(dateText, inst) {
                    var theDate = new Date(Date.parse($(this).datepicker('getDate')));
                    var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
                    $("#getTo").val(dateFormatted);
                },
            });

        });
    </script>


@endsection

@section('stypes')
    <style type="text/css">
        @import url(https://fonts.googleapis.com/css?family=Open+Sans);

        .table,
        .table tr td,
        .table tr th {
            border: 1px solid #333;
        }

        .table>thead>tr>th,
        .table>tbody>tr>th,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>tbody>tr>td,
        .table>tfoot>tr>td,
        .table tr th {
            border-top: 1px solid #333;
        }

        body {
            background: #f2f2f2;
            font-family: 'Open Sans', sans-serif;
        }

        .search {
            width: 100%;
            position: relative;
        }

        .searchTerm {
            float: left;
            width: 100%;
            border: 3px solid #00B4CC;
            padding: 5px;
            height: 20px;
            border-radius: 5px;
            outline: none;
            color: #9DBFAF;
        }

        .searchTerm:focus {
            color: #00B4CC;
        }

        .searchButton {
            position: absolute;
            right: -50px;
            width: 40px;

            height: 36px;
            border: 1px solid #00B4CC;
            background: #00B4CC;
            text-align: center;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            font-size: 20px;
        }

        /*Resize the wrap to see the search bar change!*/
        .wrap {
            width: 30%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .table,
        .table tr td,
        .table tr th {
            border: 1px solid #333;
        }
    </style>
@stop

@section('styles')
    <style>
        .textbox {
            border: 1px;
            background-color: #33AD0A;
            outline: 0;
            height: 25px;
            width: 275px;
        }

        .autocomplete-suggestions {
            color: #fff;
            font-size: 13px;
        }
    </style>
@endsection

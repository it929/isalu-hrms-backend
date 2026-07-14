@extends('layouts.layout')
@section('pageTitle')
    {{ strtoupper('search contracts to raise voucher') }}
@endsection
@section('content')

    <div id="vim" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">All comments</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="form-horizontal" id="deletevariableModal" role="form" method="POST" action="">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group" style="margin: 0 10px;">
                            <div class="col-sm-12" id="z-space">

                            </div>
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <!--<button type="Submit" class="btn btn-success" id="putedit"></button>-->
                    <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                </div>


            </div>

        </div>
    </div>


    <div class="box box-default">
        <div class="box-body box-profile">
            <div class="box-header with-border hidden-print">
                <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
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
                        @if ($error != '')
                            <div class="alert alert-dismissible alert-danger">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>{{ $error }}</strong>
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

                        <!-- <div align="center">
          <h3><b><div>{{ strtoupper('ISALU HOSPITAL LIMITED') }}</div></b></h3>
          <div><h4><b>{{ strtoupper('ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA') }}</b></h4></div>
        </div> -->

                        <form class="form-horizontal" role="form" id="form1" method="post" action="">
                            {{ csrf_field() }}

                            <div class="col-md-12"><!--2nd col-->
                                <!-- /.row -->
                                <div class="form-group">

                                    <div class="col-md-3">
                                        <label class="control-label">-Select Economic Group-</label>
                                        <select class="form-control" name="contracttype" id="contracttype" placeholder="">
                                            <option value="">Select Contract</option>
                                            @foreach ($contracttypes as $list)
                                                <option value="{{ $list->ID }}"
                                                    {{ $list->ID == $contracttype || $list->ID == old('contracttype') ? 'selected' : '' }}>
                                                    {{ $list->contractType }}</option>
                                            @endforeach
                                        </select>
                                    </div>




                                    <div class="col-md-3">
                                        <label class="control-label">Contractor/Staff Voucher</label>
                                        <select name="contractor" id="contractor" class="form-control">
                                            <option value="">-Select All-</option>
                                            @foreach ($companyDetails as $list)
                                                <option value="{{ $list->id }}"
                                                    {{ $list->id == $contractor || $list->id == old('contractor') ? 'selected' : '' }}>
                                                    {{ $list->contractor }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="control-label">File No</label>
                                        <input list="fileNos" value="{{ $fileno ? $fileno : old('fileno') }}"
                                            name="fileno" id="fileno" autocomplete="off" class="form-control">
                                        <datalist id="fileNos">
                                            @foreach ($fileNos as $list)
                                                <option value="{{ $list->fileNo }}">{{ $list->fileNo }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>


                                    <div class="col-md-1">
                                        <label class="control-label">&nbsp</label>
                                        <button class="btn btn-success form-control">Search</button>
                                    </div>
                                </div>


                                <div class="table-responsive col-md-12" style="font-size: 12px; padding:10px;">
                                    <table class="table table-bordered table-striped table-highlight">
                                        <thead>
                                            <tr bgcolor="#c7c7c7">
                                                <th>S/N</th>
                                                <th>File No</th>
                                                <th>Description</th>
                                                <th>Total Amount</th>
                                                <th>Balance</th>
                                                <th>Beneficiary</th>
                                                <th>Status</th>
                                                <th>Approved By</th>
                                                <th>Approved Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        @php $i = 1; @endphp
                                        <tbody>
                                            @if ($tablecontent)
                                                @foreach ($tablecontent as $list)
                                                    @if ($list->contractBalance != 0)
                                                        <tr>
                                                            <td>{{ $i++ }}</td>
                                                            <td>{{ $list->fileNo }}</td>

                                                            <td>{{ $list->ContractDescriptions }}</td>
                                                            @php$list->contractValue = (int) $list->contractValue;
                                                                $list->contractBalance = (int) $list->contractBalance;
                                                            @endphp
                                                            <td> &#8358; {{ number_format($list->contractValue) }} </td>
                                                            <td> &#8358; {{ number_format($list->contractBalance) }} </td>
                                                            <td>{{ $list->contractor }}</td>
                                                            <!-- <td>{{ $list->createdby }}</td> -->
                                                            <td>
                                                                @if ($list->approvalStatus == 1)
                                                                    <b><span class="text-success">Approved</span></b>
                                                                @elseif($list->approvalStatus == 2)
                                                                    <b><span class="text-warning">Rejected</span></b>
                                                                @else
                                                                    <b><span class="text-danger">Pending</span></b>
                                                                @endif
                                                            </td>
                                                            <!-- <td>{{ $list->datecreated }}</td> -->
                                                            <td>{{ $list->approvedBy }}</td>
                                                            <td>{{ $list->approvalDate }} </td>

                                                            <td id="{{ $list->ID }}">
                                                                <a class="btn btn-xs btn-success"
                                                                    id="{{ $list->ID }}" style="cursor: pointer;"
                                                                    onclick="return setID('{{ encrypt($list->ID) }}')">Select</a>

                                                            </td>

                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="100%">No record</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    </th>
                                    </tr>





                                </div>

                                <hr />
                            </div>
                        </form>
                    </div>
                </div>

                <form id="form10" method="post" action="/voucher/continue" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="selectedid" id="selectedid">
                    <input type="hidden" name="contracttype2" id="contracttype2">
                </form>







            @endsection
            @section('styles')
                <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
            @stop
            @section('styles')
                <style type="text/css">
                    .modal-dialog {
                        width: 10cm
                    }

                    .modal-header {

                        background-color: #006600;

                        color: #FFF;

                    }
                </style>
            @endsection

            @section('scripts')
                <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
                <script>
                    function editfunc(a, b, c, d, e, f, g, h, i, j) {
                        $(document).ready(function() {
                            $('#contractor').val(a);
                            $('#phone').val(b);
                            $('#email').val(c);
                            $('#address').val(d);
                            $('#bank').val(e);
                            $('#account').val(f);
                            $('#sortcode').val(g);
                            $('#tin').val(h);
                            $('#C_id').val(i);
                            $('#status').val(j);
                            $("#editModal").modal('show');
                        });
                    }

                    function delfunc(a) {
                        $(document).ready(function() {
                            $('#conID').val(a);
                            $("#delModal").modal('show');
                        });
                    }

                    function getEconomics() {

                        var all = document.getElementById('allocationtype1').value;
                        var con = document.getElementById('contracttype1').value;
                        var frm = document.getElementById('form1');

                        if (all !== "" && con !== "") {
                            return frm.submit();
                        }
                    }



                    function getBalance() {
                        var eco = document.getElementById('economicCode1').value;
                        var frm = document.getElementById('form1');
                        if (eco !== "") {
                            return frm.submit();
                        }
                    }

                    function viewInstruct(list) {
                        //<label class="control-label"><i id="vi"></i></label>
                        if (list !== "") {
                            var a = JSON.parse(list);
                            //console.log(a);
                            space = document.getElementById('z-space');
                            space.innerHTML = '';
                            for (i = 0; i < a.length; i++) {
                                space.innerHTML += '<p><b id="vi">' + a[i].comment + '</b> - <small class="text-warning"> <i>' + a[i]
                                    .name + ', posted ' + a[i].date_added + ' at ' + a[i].time + '</i></small></p><br>';
                            }
                        }
                        $('#vim').modal('show');
                        return false;
                    }

                    function setID(id) {
                        document.getElementById('selectedid').value = id;

                        var con = document.getElementById('contracttype').value;
                        //document.getElementById('contracttype2').value = con;
                        return window.location.assign('/voucher/continue/' + id + '/' + con);

                    }

                    function submitVoucher() {
                        document.getElementById('finalsubmit').value = 'complete';
                        return document.getElementById('form1').submit();
                    }

                    function getEconomics2() {
                        var all = document.getElementById('secallocationtype').value;
                        var con = document.getElementById('contracttype1').value;
                        var frm = document.getElementById('form1');
                        if (all !== "" && con !== "") {
                            return frm.submit();
                        }
                    }

                    function getAddrvat() {
                        console.log(document.getElementById('vatPayeeID').value);
                        return false;
                    }


                    function getAddrwht() {
                        console.log(document.getElementById('whtPayeeID').value);
                        return false;
                    }
                </script>

                </script>
                <script>
                    ///////////////////////DATE/////////////////////////////////
                    $(function() {
                        $("#todayDate").datepicker({
                            changeMonth: true,
                            changeYear: true,
                            dateFormat: 'yy-mm-dd'
                        });
                    });
                    ///////////////////////DATE/////////////////

                    //$('#netAmount').focus();

                    $('#companyGetLookUp').change(function() {
                        $('#beneficiaryName').val($('#companyGetLookUp').val());
                    });



                    vt = document.getElementById("vatPayeeID");
                    wh = document.getElementById("whtPayeeID");

                    if ($("#vatselect").val() !== "0") {
                        vt.disabled = false;
                    }

                    if ($("#WithholdingTax").val() !== "0") {
                        wh.disabled = false;
                    }

                    if ($("#vatselect").val() == "0") {
                        vt.disabled = true;
                        vt.value = "";
                    }

                    if ($("#WithholdingTax").val() == "0") {
                        wh.disabled = true;
                        wh.value = "";
                    }



                    $("#vatselect ,#WithholdingTax").change(function() {

                        vt = document.getElementById("vatPayeeID");
                        wh = document.getElementById("whtPayeeID");

                        if ($("#vatselect").val() !== "0") {
                            vt.disabled = false;
                        }

                        if ($("#WithholdingTax").val() !== "0") {
                            wh.disabled = false;
                        }

                        if ($("#vatselect").val() == "0") {
                            vt.disabled = true;
                            vt.value = "";
                        }

                        if ($("#WithholdingTax").val() == "0") {
                            wh.disabled = true;
                            wh.value = "";
                        }


                        var amount = $("#netAmount").val();
                        if (amount == "") {

                            //alert error when amount is empty , vat and tax not applicable to zero amount
                            alert("amount cant be empty");
                            $("#netAmount").focus();

                        } else {

                            var vat_rate = $("#vatselect").val();
                            var tax_rate = $("#WithholdingTax").val();

                            console.log('vat rate', vat_rate);
                            console.log('tax rate', tax_rate);

                            //we have amount not set to empty , calculate the vat and the tax
                            //display it and set it to the respective elements
                            //calculate vat value
                            var vat = (vat_rate / 100) * amount;

                            //calculate tax value
                            var tax = (tax_rate / 100) * amount;


                            $("#vat").val(vat);

                            //display the tax to the user
                            $("#tax").val(tax);

                            //calculate net payable
                            var netpay = Number(amount) - (Number(vat) + Number(tax));
                            $("#grossAmount").html(netpay);
                            $("#amtpayable").val(netpay);

                        }

                    })

                    function calc() {
                        var amount = $("#netAmount").val();
                        if (amount == "") {

                            //alert error when amount is empty , vat and tax not applicable to zero amount
                            alert("amount cant be empty");
                            $("#netAmount").focus();

                        } else {

                            var vat_rate = $("#vatselect").val();
                            var tax_rate = $("#WithholdingTax").val();

                            console.log('vat rate', vat_rate);
                            console.log('tax rate', tax_rate);

                            //we have amount not set to empty , calculate the vat and the tax
                            //display it and set it to the respective elements
                            //calculate vat value
                            var vat = (vat_rate / 100) * amount;

                            //calculate tax value
                            var tax = (tax_rate / 100) * amount;


                            $("#vat").val(vat);

                            //display the tax to the user
                            $("#tax").val(tax);

                            //calculate net payable
                            var netpay = Number(amount) - (Number(vat) + Number(tax));
                            $("#grossAmount").html(netpay);
                            $("#amtpayable").val(netpay);


                        }

                        function showF() {
                            document.getElementById('second-form').style.display = 'block';
                            document.getElementById('show-btn').style.visibility = 'hidden';
                            document.getElementById('hide-btn').style.visibility = 'visible';
                        }

                    }

                    function hideF() {
                        document.getElementById('second-form').style.display = 'none';
                        document.getElementById('hide-btn').style.visibility = 'hidden';
                        document.getElementById('show-btn').style.visibility = 'visible';
                    }
                </script>


            @stop

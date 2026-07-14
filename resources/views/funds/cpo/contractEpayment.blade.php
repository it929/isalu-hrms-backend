<!DOCTYPE html>
<html>

<head>

    <title>ISALU HOSPITAL LIMITED</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://funds.njc.gov.ng/assets/css/datepicker.min.css">
    <style type="text/css">
        @if ($checkApproval == 0)
            @media print {

                .no-print,
                .hidden-print,
                .no-print * {
                    display: none !important;
                }

            }
        @endif
        .printWrap {

            margin-bottom: 10px;
        }

        .typeSelect {
            border: 0px;
            outline: 0px;
            text-align: right;
            float: right;
            -webkit-appearance: none;
            -moz-appearance: none;
            text-indent: 1px;
            text-overflow: '';
            padding-right: 0px;

        }

        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            border: none;
            /* needed for Firefox: */
            overflow: hidden;
            width: 60%;
        }

        .bg {
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;

            -webkit-animation: myfirst 5s;
            /* Chrome, Safari, Opera */
            animation: myfirst 5s;
        }

        @-webkit-keyframes myfirst {
            from {
                opacity: 0.2;
            }

            to {
                opacity: 1;
            }
        }

        /* Standard syntax */
        @keyframes myfirst {
            from {
                opacity: 0.2;
            }

            to {
                opacity: 1;
            }
        }

        .type {
            border: 0px;
            outline: 0px;

            -webkit-appearance: none;
            -moz-appearance: none;
            text-indent: 1px;
            text-overflow: '';


        }

        .sigtab tr td {
            padding: 10px;
        }

        .sigtab p {
            border: 1px solid #ccc;
            padding: 9px;
            width: 100%;
            margin: 0px;
        }
    </style>

    <script type="text/javascript" src="{{ asset('assets/js/numberToWords.js') }}"></script>
</head>

<body onload="lookup();">


    <div class="col-md-12">
        <div>
            <p>
            <div class="row input-sm">
                <div class="col-xs-1"><img src="{{ asset('Images/scn_logo.jpg') }}" class="img-responsive"
                        style=""></div>
                <div class="col-xs-10">
                    <div>
                        <h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h4>

                        <h6 class=" text-center text-success col-md-offset-4"><strong>ACCOUNT NO.: <select
                                    class="type">

                                    @foreach ($accountDetails as $list)
                                        <option>{{ $list->account_no }}</option>
                                    @endforeach

                                </select></strong></h6>
                        <h6 class=" text-center text-success">E-PAYMENT SCHEDULE</h6>
                        <div class="hidden-print col-md-offset-4">
                            <select class="typeSelect print-voucher">
                                <option value="">Select mandate to print</option>
                                <option value="first">First Mandate</option>
                                <option value="second">Second Mandate</option>


                            </select>
                        </div>


                    </div>
                </div>
                <div class="col-xs-1"><img src="{{ asset('Images/coat.jpg') }}" class="img-responsive"></div>
            </div>
            </p>
        </div>

        <div>&nbsp;

            <p>
            <div class="row">
                <div align="left" class="col-xs-6">
                    <div class="address">
                        {!! $accountAddress->address !!}
                    </div>
                </div>

                <div align="right" class="col-xs-6">
                    <table>
                        <tr>
                            <td>
                                <div align="left">Reference No: <input type="text" class="type" name="batch"
                                        bch="{{ $current_batch }}"
                                        value="{{ $status->capital_refno ?? 'NJC/3/2/01AVOL.1/03' }}"
                                        id="ref" /><br />
                                    Code No: <input type="text" class="type" name="batch"
                                        bch="{{ $current_batch }}" value="{{ $status->adjusted_batch ?? '' }}"
                                        id="batchRef" /> <br />
                                    Date Printed: <input type="text" class="type" name="datePrepared"
                                        bch="{{ $current_batch }}" thedate="{{ $date }}"
                                        value="{{ $date }}" id="dateprep" /> <br />



                                    <br />
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            </p>

            <br />
            <div align="left">

            </div>

        </div>
    </div>

    <div class="col-md-12">

        <div style="öpacity:0.2; width:100%; float:left;" id="first">
            <div style="width:65%; margin:auto">
                Please credit the account(s) of the below listed beneficiary(s) and debit our account below with:
                (&#8358;)<b>{{ number_format($sum + $whtsum + $vatsum, 2) }} </b><br>
                <b><span id="result"></span> ONLY </b>

                <script type="text/javascript">
                    var amount = "";
                    var amount = "<?php echo number_format($sum + $whtsum + $vatsum, 2, '.', ''); ?>";
                    var money = amount.split('.'); //



                    function lookup() {
                        //Main Voucher
                        var words;
                        var naira = money[0];
                        var kobo = money[1];
                        var word1 = toWords(naira) + "naira";
                        var word2 = ", " + toWords(kobo) + " kobo";
                        if (kobo != "00")
                            words = word1 + word2;
                        else
                            words = word1;
                        //
                        var getWord = words.toUpperCase();
                        var parternRule1 = /HUNDRED AND NAIRA/ig;
                        var parternRule2 = /HUNDRED AND THOUSAND NAIRA/ig;
                        var instance1 = parternRule1.test(getWord);
                        var instance2 = parternRule2.test(getWord);
                        if ((instance1)) {
                            document.getElementById('result').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
                            document.getElementById('result2').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
                        } else if ((instance2)) {
                            document.getElementById('result').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
                            document.getElementById('result2').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
                        } else {
                            document.getElementById('result').innerHTML = getWord;
                            document.getElementById('result2').innerHTML = getWord;
                        }
                        //



                    }
                </script>




            </div>

            <table class="table table-responsive table-bordered" id="tableData">
                <tr class="tblborder">
                    <td class="tblborder">
                        <div align="center"><strong>S/N</strong></div>
                    </td>
                    <td class="tblborder">
                        <div align="center"><strong>BENEFICIARY</strong></div>
                        <div align="center"></div>
                        <div align="center"></div>
                    </td>
                    <td class="tblborder"><strong>NO. OF ITEMS</strong></td>
                    <td class="tblborder">
                        <div align="center"><strong>AMOUNT</strong> (&#8358;)</div>
                    </td>
                    <td class="tblborder"><strong>PURPOSE OF PAYMENT</strong></td>
                    <td class="tblborder hidden-print"><strong>Edit Purpose</strong></td>
                </tr>
                <?php $key = 1; ?>
                @foreach ($mandate as $reports)
                    <tr class="tblborder">
                        <td class="tblborder">{{ $key++ }} </td>
                        <td class="tblborder"> {{ $reports->bankName }} </td>
                        <td class="tblborder"> {{ $reports->NumOfBanks }} </td>
                        <td class="tblborder" align="right">
                            {{ number_format($reports->totalAmount + $reports->vat + $reports->tax, 2, '.', ',') }}
                        </td>
                        <td class="tblborder"> {{ $reports->capital_bank_purpose ?? $reports->purpose }} </td>
                        <td class="hidden-print"><a href="javascript:void()"
                                class="editPurpose btn btn-success btn-xs hidden-print no-print"
                                pps="{{ $reports->purpose }}" id="{{ $reports->bankName }}">Edit</a></td>
                    </tr>
                @endforeach
                @if (count((array) $mandate) == 0)
                    <tr class="tblborder">

                        <td class="tblborder text-center" colspan="6">Data not available</td>

                    </tr>
                @endif
                @if (count((array) $mandate) > 0)
                    <tr class="tblborder">
                        <td class="tblborder" colspan="3"><strong>Total</strong></td>
                        <td class="tblborder" align="right"><strong>{{ number_format($sum + $whtsum + $vatsum, 2) }}
                            </strong></td>

                        <td class="tblborder"></td>
                    </tr>
                @endif
            </table>
            </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="no-print" align="center">

                    </div>
                    <?php
                    $finalsum = 0;
                    ?>

                </td>
            </tr>
            <tr>
                <td colspan="2">

                    <table class="table">

                        <tr>
                            <td style="width: 35%">
                                <div class="col-md-12  sigtab" style="padding:0px;">
                                    <div class="inner-wrap">
                                        <p><strong> Authorised Signature </strong></p>
                                        <p>Name: <select class="type selectname">
                                                <option value="">Select One</option>
                                                @if (count((array) $sigA) > 0)
                                                    @foreach ($sigA as $list)
                                                        <option value="{{ $list->id ?? '' }}"
                                                            @if ($sig1 != '') @if ($list->id == $sig1->signatoryId) selected @endif
                                                            @endif>{{ $list->Name ?? '' }}
                                                        </option>
                                                    @endforeach
                                                @endif

                                            </select></p>
                                        <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                                        <p>Date: </p>
                                        <p>Phone No. @if ($sig1 != '')
                                                {{ $sig1->phone ?? '' }} @endif
                                        </p>
                                    </div>

                                    <div class="inner-wrap">
                                        <p><strong> Authorised Signature </strong></p>
                                        <p>Name: <select class="type selectname2">
                                                <option value="">Select One</option>
                                                @if (count((array) $sigB) > 0)
                                                    @foreach ($sigB as $list)
                                                        <option value="{{ $list->id ?? '' }}"
                                                            @if ($sig2 != '') @if ($list->id == $sig2->signatoryId) selected @endif
                                                            @endif>{{ $list->Name ?? '' }}
                                                        </option>
                                                    @endforeach
                                                @endif

                                            </select></p>
                                        <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                                        <p>Date: </p>
                                        <p>Phone No. @if ($sig2 != '')
                                                {{ $sig2->phone ?? '' }} @endif
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <td style="width: 30%">

                            </td>


                            <td style="width: 35%">
                                <div class="col-md-12 col-xs-12 col-sm-12 sigtab" style="padding:0px;">
                                    <div class="inner-wrap">
                                        <p><strong> Submitted for confirmation by </strong></p>
                                        <p>Name: <br><br></p>
                                        <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                                        <p>Date: <br><br><br></p>


                                    </div>

                                    <div class="inner-wrap">
                                        <p><strong> Confirm By Me </strong></p>
                                        <p>Name: </p>
                                        <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                                        <p>Date: <br><br><br></p>

                                    </div>
                                </div>
                            </td>

                    </table>


                    <div class="pull-left"><a href="{{ url()->previous() }}"
                            class="hidden-print btn btn-success">Back</a></div>

                    <div class="pull-left" style="margin-left:20px;">
                        <input type="button" class="hidden-print btn btn-success" id="btnExport"
                            value="Export to Excel" onclick="Export()" />
                    </div>

        </div>

        <div class="" style="margin-top:30px; width:100%; float:left" id="second">
            <table class="table table-bordered" id="tblExport">
                <thead>
                    <tr>
                        <th>BANK</th>
                        <th>CODE</th>
                        <th>NAME OF COMPANY</th>
                        <th>ACCT NO.</th>
                        <th>AMOUNT</th>
                        <th></th>
                        <th>NARRATION</th>
                        <th>NJC PAYMENT</th>
                        <th>DATE</th>
                        <th class="hidden-print">UPDATE NARRATION</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalCBN = 0; @endphp
                    @foreach ($breakdown as $list)
                        @php $date = $list->date; @endphp
                        <tr>
                            <td>{{ $list->BankName }}</td>
                            <td>{{ $list->sort_code }}</td>
                            <td>{{ $list->contractor }}</td>
                            <td>{{ $list->accountNo }}</td>
                            <td><?php $totalCBN += $list->amount + $list->VATValue + $list->WHTValue; ?>{{ number_format($list->amount + $list->VATValue + $list->WHTValue, 2) }}
                            </td>
                            <td>CR</td>
                            <td>{{ $list->purpose }}</td>
                            <td>NJC Payment</td>
                            <td>{{ $list->date }}</td>
                            <td class="hidden-print"><a href="javascript:void()"
                                    class="edit btn btn-success btn-xs hidden-print no-print"
                                    pps="{{ $list->purpose }}" id="{{ $list->ID }}">Edit</a></td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>CBN</strong></td>
                        <td colspan="3"><strong>{{ $cbn->account_no }}</strong></td>
                        <td><strong>{{ number_format($totalCBN, 2) }}</strong></td>
                        <td><strong>DR</strong></td>
                        <td>Code {{ $status->adjusted_batch ?? '' }}</td>
                        <td></td>
                        <td>{{ $date }}</td>
                        <td class="hidden-print"></td>
                    </tr>
                </tbody>

            </table>

            <div class="pull-left"><a href="{{ url()->previous() }}" class="hidden-print btn btn-success">Back</a>
            </div>

            <div class="pull-left" style="margin-left:20px;">
                <input type="button" class="hidden-print btn btn-success" id="btnExport" value="Export to Excel"
                    onclick="ExportTbl()" />
            </div>

        </div>




    </div>

    <!-- Modal Dialog for UPDATE RECORD-->
    <form method="post" action="{{ url('/cpo/update-account') }}">
        {{ csrf_field() }}
        <div class="actModal modal fade" id="confirmUpdate" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Account No Update</h4>
                    </div>
                    <div class="modal-body">

                        <div class="form-group" style="margin-bottom:50px;">
                            <label class="control-label col-md-3">Bank</label>
                            <div class="col-md-9">
                                <select name="bank" class="form-control" required>
                                    <option value=""> Select Bank </option>
                                    @foreach ($banks as $list)
                                        <option value="{{ $list->bank }}">{{ $list->bank }}</option>
                                    @endforeach

                                </select>

                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group">
                            <label class="control-label col-md-3">Account No:</label>
                            <div class="col-md-9">
                                <input type="text" name="accountNo" id="accountNo" class="form-control" required>
                                <input type="hidden" name="batch" id="batch" class="batch">
                                <input type="hidden" name="epaymentID" id="epaymentID" class="epaymentID">
                            </div>

                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <input type="submit" name="button" class="btn btn-info" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- //Modal Dialog -->


    <!-- Modal Dialog for Payment Description-->
    <form method="post" action="{{ url('/cpo/update-narration') }}">
        {{ csrf_field() }}
        <div class="narrateModal modal fade" id="confirmUpdate" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Update Payment Narration</h4>
                    </div>
                    <div class="modal-body">

                        <div class="form-group" style="margin-bottom:50px;">
                            <label class="control-label col-md-3">Update Narration</label>
                            <div class="col-md-9">
                                <textarea name="narration" id="narration" class="form-control narration">

					    </textarea>
                                <input type="hidden" name="eid" id="eID" class="eID">
                            </div>
                        </div>

                        <div class="clearfix"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <input type="submit" name="button" class="btn btn-info" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- //Modal Dialog Payment Description-->


    <!-- Modal Dialog for Bank Group Purpose-->
    <form method="post" action="{{ url('/cpo/update-purpose') }}">
        {{ csrf_field() }}
        <div class="purposeModal modal fade" id="confirmUpdate" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Update Payment Narration</h4>
                    </div>
                    <div class="modal-body">

                        <div class="form-group" style="margin-bottom:50px;">
                            <label class="control-label col-md-3">Update Narration</label>
                            <div class="col-md-9">
                                <textarea name="purpose" id="purpose" class="form-control purpose">

					    </textarea>
                                <input type="hidden" name="bankname" id="bankname" class="bankname">
                            </div>
                        </div>

                        <div class="clearfix"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <input type="submit" name="button" class="btn btn-info" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- //Modal Dialog For Bank Group Purpose-->




    <!-- Modal Dialog for UPDATE VAT AND TAX RECORD-->
    <form method="post" action="{{ url('/update-payee-account') }}">
        {{ csrf_field() }}
        <div class="payeModal modal fade" id="confirmUpdate" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Account No Update</h4>
                    </div>
                    <div class="modal-body">

                        <div class="form-group" style="margin-bottom:50px;">
                            <label class="control-label col-md-3">Beneficiary</label>
                            <div class="col-md-9">
                                <input type="text" name="beneficiary" class="form-control benefi" />
                                <input type="hidden" name="paye" class="form-control" id="paye" />
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:50px;">
                            <label class="control-label col-md-3">Bank</label>
                            <div class="col-md-9">
                                <select name="bank" class="form-control bks" id="banks" required>
                                    <option value=""> Select Bank </option>
                                    @foreach ($banks as $list)
                                        <option value="{{ $list->bank }}">{{ $list->bank }}</option>
                                    @endforeach

                                </select>

                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group">
                            <label class="control-label col-md-3">Account No:</label>
                            <div class="col-md-9">
                                <input type="text" name="accountNo" id="accountNo" class="form-control accountNo"
                                    required>
                                <input type="hidden" name="batch" id="batch" class="batch">
                                <input type="hidden" name="epaymentID" id="epaymentID" class="epaymentID">
                            </div>

                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <input type="submit" name="button" class="btn btn-info" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- //Modal Dialog UPDATE VAT AND TAX RECORD-->



    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/table2excel.js') }}"></script>

    <script>
        var murl = "{{ url('/') }}";
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("table tr td .update").click(function() {
                var batchNo = $(this).attr('btc');
                var epayID = $(this).attr('id');
                $(".batch").val(batchNo);
                $(".epaymentID").val(epayID);
                $(".actModal").modal('show');

            });
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("table tr td .edit").click(function() {
                var narration = $(this).attr('pps');
                var epayID = $(this).attr('id');
                $(".narration").val(narration);
                $(".eID").val(epayID);
                $(".narrateModal").modal('show');

            });
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("table tr td .editPurpose").click(function() {
                var narration = $(this).attr('pps');
                var bk = $(this).attr('id');
                $(".purpose").val(narration);
                $(".bankname").val(bk);
                $(".purposeModal").modal('show');

            });
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("table tr td .tax").click(function() {
                var batchNo = $(this).attr('btc');
                var epayID = $(this).attr('id');
                var acct = $(this).attr('accts');
                var bene = $(this).attr('bene');
                var bank = $(this).attr('bk');
                var p = $(this).attr('tx');
                $(".batch").val(batchNo);
                $(".epaymentID").val(epayID);
                $("#paye").val(p);
                $(".benefi").val(bene);
                $("#banks").val(bank);
                $(".accountNo").val(acct);
                $(".payeModal").modal('show');

            });
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {

            $("table tr td .vat").click(function() {
                var batchNo = $(this).attr('btc');
                var epayID = $(this).attr('id');
                var acct = $(this).attr('accts');
                var bene = $(this).attr('bene');
                var bank = $(this).attr('bk');
                var p = $(this).attr('vt');
                console.log(bank);
                $("#paye").val(p);
                $(".batch").val(batchNo);
                $(".epaymentID").val(epayID);
                $(".benefi").val(bene);
                $("#banks").val(bank);
                $(".accountNo").val(acct);
                $(".payeModal").modal('show');

            });
        });
    </script>

    <script type="text/javascript">
        function Export() {
            $("#tableData").table2excel({
                filename: "{{ 'Batch' }}_{{ $current_batch }}_Mandate.xls"
            });
        }

        function ExportTbl() {
            $("#tblExport").table2excel({
                filename: "{{ 'capital' }}_{{ $current_batch }}_Mandate.xls"
            });
        }

        $(".type").on('change', function() {
            var acct = $(this).val();
            $.ajax({

                url: murl + '/get-account/address',
                type: "post",
                data: {
                    'accountNo': acct,
                    _token: '{{ csrf_token() }}'
                },

                success: function(datas) {
                    console.log(datas.address);
                    //alert(datas.phone);

                    $('.address').html(datas.address);

                }
            });
        });
    </script>



    <script type="text/javascript">
        $(function() {

            $(".selectname").on('change', function() {

                var id = $(this).val();
                var batch = "{{ $current_batch }}";
                //alert(id);
                $token = $("input[name='_token']").val();
                $.ajax({

                    url: murl + '/epay/signatory',
                    type: "post",
                    data: {
                        'signid': id,
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(datas) {
                        console.log(datas.phone);
                        //alert(datas.phone);
                        $('.sigp1').hide();
                        $('.sign1').html(datas.phone);

                    }
                });
            });


            $(".selectname2").on('change', function() {

                var id = $(this).val();
                var batch = "{{ $current_batch }}";
                //alert(batch);
                $token = $("input[name='_token']").val();
                $.ajax({

                    url: murl + '/epay/signatory',
                    type: "post",
                    data: {
                        'signid': id,
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(datas) {
                        console.log(datas.phone);
                        //alert(datas.phoneno);
                        $('.sign3').html(datas.phone);

                    }
                });
            });

            $(".selectname3").on('change', function() {

                var id = $(this).val();
                //alert(id);
                $token = $("input[name='_token']").val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $token
                    },
                    url: murl + '/epay/signatory',
                    type: "post",
                    data: {
                        'signid': id
                    },

                    success: function(datas) {
                        console.log(datas.phoneno);
                        //alert(datas.phoneno);
                        $('.sigp2').hide();
                        $('.sign3').html(datas.phoneno);

                    }
                });
            });


        });
    </script>

    <script>
        $(document).ready(function() {
            $("#batchRef").blur(function() {
                var batch = $(this).attr('bch');
                var newBatch = $(this).val();
                // alert(batch);
                $.ajax({
                    url: murl + '/update/batch',
                    type: "post",
                    data: {
                        'newBatch': newBatch,
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(datas) {
                        console.log(datas.phone);

                    }
                });

            });
        });


        $(document).ready(function() {
            $("#ref").blur(function() {
                var batch = $(this).attr('bch');
                var newBatch = $(this).val();
                // alert(batch);
                $.ajax({
                    url: murl + '/update/ref',
                    type: "post",
                    data: {
                        'newBatch': newBatch,
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(datas) {
                        console.log(datas.phone);

                    }
                });

            });
        });


        $(document).ready(function() {
            $("#dateprep").change(function() {
                var batch = $(this).attr('bch');
                //var thedate = $(this).val();
                //alert(thedate);
                $.ajax({
                    url: murl + '/update/date',
                    type: "post",
                    data: {
                        'preparedate': $(this).val(),
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(datas) {
                        console.log(datas);

                    }
                });

            });
        });

        $(function() {
            $("#dateprep").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.print-voucher').change(function() {
                if ($(this).val() == 'first') {
                    $("#second").hide();
                    $("#first").show();
                } else if ($(this).val() == 'second') {
                    $("#first").hide();
                    $("#second").show();
                } else if ($(this).val() == '') {
                    $("#first, #second").show();
                }

            });
        });
    </script>

</body>

</html>

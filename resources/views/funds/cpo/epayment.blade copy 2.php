<!DOCTYPE html>
<html>

<head>
    <title>ISALU HOSPITAL LIMITED</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://funds.njc.gov.ng/assets/css/datepicker.min.css">

    {{-- <script type="text/javascript" src="{{ asset('assets/js/number_to_word2.js') }}"></script> --}}
    <script type="text/javascript" src="{{ asset('assets/js/numberToWords.js') }}"></script>
        <style type="text/css">
        /* @if ($checkApproval == 0) */
            @media print {

                .no-print,
                .hidden-print,
                .no-print * {
                    display: none !important;
                }

                tr[style*="background: green"] {
                    background-color: green !important;
                    color: #fff !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                tr[style*="background: green"] td {
                    background-color: green !important;
                    color: #fff !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

            }
        /* @endif */
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
    </style>
</head>

<body onload="lookup();">

    <div class="col-md-12">
        <div class="col-md-12">
            <div>
                <p>
                <div class="row input-sm">
                    <div class="col-xs-1"><img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                            style="width:100%; height:auto;"></div>
                    <div class="col-xs-10">
                        <div>
                            <h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h4>

                            <h6 class=" text-center text-success col-md-offset-4"><strong>ACCOUNT NO.: <select
                                        class="type">

                                        @foreach ($accountDetails as $list)
                                            <option {{$mandate[0]->NJCAccount == $list->id ? 'selected' : ''}}>{{ $list->account_no }}</option>
                                        @endforeach

                                    </select></strong></h6>
                            <h6 class=" text-center text-success">E-PAYMENT SCHEDULE</h6>
                        </div>
                    </div>
                    <div class="col-xs-1"><img style="width:100%; height:auto;" src="{{ asset('Images/coat.png') }}"
                            class="img-responsive responsive"></div>
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
                                    <div align="left">Reference No: {{ $current_batch }} <br>
                                        Date Printed: {{ $date }}
                                        
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
        <div style="öpacity:0.2">
            <div style="width:65%; margin:auto">
                Please credit the account(s) of the below listed beneficiary(s) and debit our account with:
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
            <table border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2">
                        <table class="table table-responsive table-bordered" id="tableData">
                            <tr class="tblborder" style="background: green !important; color: #fff;">
                                <td class="tblborder">
                                    <div align="center"><strong>S/N</strong></div>
                                </td>
                                <td class="tblborder">
                                    <div align="center"><strong>BENEFICIARY</strong></div>
                                    <div align="center"></div>
                                    <div align="center"></div>
                                </td>
                                <td class="tblborder"><strong>BANK </strong></td>
                                <!--<td class="tblborder"><strong>BRANCH</strong></td>-->
                                <td class="tblborder">
                                    <div align="center"><strong>ACC NUMBER</strong></div>
                                </td>
                                <td class="tblborder">
                                    <div align="center"><strong>AMOUNT</strong> (&#8358;)</div>
                                </td>
                                <td class="tblborder"><strong>PURPOSE OF PAYMENT</strong></td>
                                <td class="tblborder hidden-print"><strong>View Voucher</strong></td>
                                <td class="tblborder hidden-print"><strong>Update Account No.</strong></td>
                                <td class="tblborder hidden-print"><strong>Update Narration.</strong></td>
                            </tr>
                            <?php $key = 1; ?>
                            @php
                                $groupedMandate = $mandate->groupBy('bank');
                            @endphp
                            @foreach ($groupedMandate as $bankName => $bankReports)

                            @foreach ($bankReports as $reports)

                                @php
                                    $url = url('/display/voucher/' . $reports->transactionID);
                                @endphp

                                <!-- MAIN ROW -->
                                <tr class="tblborder">
                                    <td class="tblborder">{{ $key++ }}</td>
                                    <td class="tblborder">{{ $reports->contractor }}</td>
                                    <td class="tblborder">{{ $reports->bank }}</td>
                                    <td class="tblborder"><span style="display: none;">'</span>{{ $reports->accountNo }}</td>
                                    <td class="tblborder" align="right">
                                        {{ number_format($reports->amount, 2, '.', ',') }}
                                    </td>
                                    <td class="tblborder">{{ $reports->purpose }}</td>

                                    <td class="hidden-print">
                                        <a href="{{ $url }}" class="btn btn-success btn-xs hidden-print no-print" target="_blank">
                                            View Voucher
                                        </a>
                                    </td>

                                    <td class="hidden-print">
                                        <a href="javascript:void()" 
                                        class="update btn btn-success btn-xs hidden-print no-print"
                                        btc="{{ $current_batch }}"
                                        id="{{ $reports->ID }}">
                                        Update
                                        </a>
                                    </td>

                                    <td class="hidden-print">
                                        <a href="javascript:void()" 
                                        class="edit btn btn-success btn-xs hidden-print no-print"
                                        pps="{{ $reports->purpose }}"
                                        id="{{ $reports->ID }}">
                                        Edit
                                        </a>
                                    </td>
                                </tr>

                                <!-- WHT -->
                                @if ($reports->WHTValue > 0)
                                    <tr class="tblborder">
                                        <td class="tblborder">{{ $key++ }}</td>
                                        <td class="tblborder">{{ $reports->wht_payee }}</td>
                                        <td class="tblborder">{{ $reports->wht_bank }}</td>
                                        <td class="tblborder"><span style="display: none;">'</span>{{ $reports->wht_accountNo }}</td>
                                        <td class="tblborder" align="right">
                                            {{ number_format($reports->WHTValue, 2, '.', ',') }}
                                        </td>
                                        <td></td>
                                        <td>FIRS Remittance (TAX)</td>

                                        <td class="hidden-print">
                                            <a href="{{ $url }}" class="btn btn-success btn-xs hidden-print no-print" target="_blank">
                                                View Voucher
                                            </a>
                                        </td>

                                        <td class="hidden-print">
                                            <a href="javascript:void()"
                                            tx="tax"
                                            accts="{{ $reports->wht_accountNo }}"
                                            bk="{{ $reports->wht_bank }}"
                                            bene="{{ $reports->wht_payee }}"
                                            class="tax btn btn-success btn-xs hidden-print no-print"
                                            btc="{{ $current_batch }}"
                                            id="{{ $reports->ID }}">
                                            Update
                                            </a>
                                        </td>
                                        <td></td>
                                    </tr>
                                @endif

                                <!-- VAT -->
                                @if ($reports->VATValue > 0)
                                    <tr class="tblborder">
                                        <td class="tblborder">{{ $key++ }}</td>
                                        <td class="tblborder">{{ $reports->vat_payee }}</td>
                                        <td class="tblborder">{{ $reports->vat_bank }}</td>
                                        <td class="tblborder"><span style="display: none;">'</span>{{ $reports->vat_accountNo }}</td>
                                        <td class="tblborder" align="right">
                                            {{ number_format($reports->VATValue, 2, '.', ',') }}
                                        </td>
                                        <td></td>
                                        <td>FIRS Remittance (VAT)</td>

                                        <td class="hidden-print">
                                            <a href="{{ $url }}" class="btn btn-success btn-xs hidden-print no-print" target="_blank">
                                                View Voucher
                                            </a>
                                        </td>

                                        <td class="hidden-print">
                                            <a href="javascript:void()"
                                            vt="vat"
                                            accts="{{ $reports->vat_accountNo }}"
                                            bk="{{ $reports->vat_bank }}"
                                            bene="{{ $reports->vat_payee }}"
                                            class="vat btn btn-success btn-xs hidden-print no-print"
                                            btc="{{ $current_batch }}"
                                            id="{{ $reports->ID }}">
                                            Update
                                            </a>
                                        </td>
                                        <td></td>
                                    </tr>
                                @endif

                            @endforeach

                            <!-- BANK SUBTOTAL -->

                        @endforeach


                            @if (count((array) $mandate) == 0)
                                <tr class="tblborder">

                                    <td class="tblborder text-center" colspan="10">Data not available</td>

                                </tr>
                            @endif
                            @if (count((array) $mandate) > 0)
                                <tr class="tblborder">
                                    <td class="tblborder" colspan="4"><strong>Total</strong></td>
                                    <td class="tblborder" align="right">
                                        <strong>{{ number_format($sum + $whtsum + $vatsum, 2) }} </strong></td>
                                    <td class="tblborder hidden-print" colspan="2"></td>
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
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <br>

                                <td class="no-border" colspan="5">
                                    <div align="center">

                                    </div><br />
                                </td>

                            </tr>
                            <tr>
                                <td class="no-border" align="left"><strong>ALL DUE PROCESS COMPLIED WITH</strong></td>
                            </tr>
                            <tr>
                                <td class="no-border" width="385">
                                    <div align="left"><strong>Authorized Signatory</strong><br />
                                    </div>
                                </td>
                                <td class="no-border" width="167">
                                    <div align="left"><br />
                                    </div>
                                </td>
                                <td class="no-border" width="1" rowspan="9">&nbsp;</td>
                                <td class="no-border" colspan="2">
                                    <div align="left"><strong>Submitted For Confirmation by</strong><br />
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="no-border" align="left">Name: <select class="type selectname">
                                        <option value="">Select One</option>
                                        @if (count((array) $sigA) > 0)
                                            @foreach ($sigA as $list)
                                                <option value="{{ $list->id ?? '' }}"
                                                    @if ($sig1 != '') @if ($list->id == $sig1->signatoryId) selected @endif
                                                    @endif>{{ $list->Name ?? '' }}</option>
                                            @endforeach
                                        @endif

                                    </select></td>
                                <td class="no-border" rowspan="2"><img
                                        src="{{ asset('Images/sch.jpg') ?? '' }}" /></td>
                                <td class="no-border" align="left">Name: </td>
                                <td class="no-border" width="181" rowspan="2" align="left">
                                    <div align="left"><img src="{{ asset('Images/sch.jpg') ?? '' }}" /></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="no-border" align="left" valign="top">Signature: <br />
                                    Date:</td>
                                <td class="no-border" width="448" align="left" valign="top">Signature:<br />
                                    Date:</td>
                            </tr>
                            <tr>
                                <td class="no-border" align="left">Tel No: <span class="sigp1">
                                        @if ($sig1 != '')
                                            {{ $sig1->phone ?? '' }} @endif
                                    </span> <span class="sign1"></span></td>
                                <td class="no-border">&nbsp;</td>
                                <td class="no-border" colspan="2" align="left">Tel No: <span
                                        class="sign2"></span></td>
                            </tr>
                            <tr>
                                <td class="no-border" align="left" width=385><strong>Authorized Signatory</strong>
                                </td>
                                <td class="no-border">&nbsp;</td>
                                <td class="no-border" colspan="2">
                                    <div align="left"><strong>Confirmed Before Me</strong><br />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="no-border" align="left">Name: <select class="type selectname2">
                                        <option value="">Select One</option>
                                        @if (count((array) $sigB) > 0)
                                            @foreach ($sigB as $list)
                                                <option value="{{ $list->id ?? '' }}"
                                                    @if ($sig2 != '') @if ($list->id == $sig2->signatoryId) selected @endif
                                                    @endif>{{ $list->Name ?? '' }}</option>
                                            @endforeach
                                        @endif

                                    </select>
                                </td>
                                <td class="no-border" rowspan="2"><img
                                        src="{{ asset('Images/sch.jpg') ?? '' }}" /></td>
                                <td class="no-border" align="left">Name:</td>
                                <td class="no-border" rowspan="2" align="left"> <img
                                        src="{{ asset('Images/sch.jpg') }}" /> </td>
                            </tr>
                            <tr>
                                <td class="no-border" valign="top" align="left">Signature:<br />
                                    Date:</td>
                                <td class="no-border" valign="top" align="left">Signature:<br />
                                    Date:</td>
                            </tr>
                            <tr>
                                <td class="no-border" align="left">Tel No: <span class="sigp2">
                                        @if ($sig2 != '')
                                            {{ $sig2->phone ?? '' }} @endif
                                    </span><span class="sign2"></span>
                                </td>
                                <td class="no-border">&nbsp;</td>
                                <td class="no-border" colspan="2" align="left">Tel No:</td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div><a href="{{ url()->previous() }}" class="hidden-print btn btn-success">Back</a></div>
                    </td>
                    <td>
                        <div class="pull-right">
                        <input type="button" class="hidden-print btn btn-success" id="btnPrint"
                                value="Print" onclick="window.print()" />
                        
                            <input type="button" class="hidden-print btn btn-success" id="btnExport"
                                value="Export to Excel" onclick="Export()" />
                        </div>
                    </td>
                </tr>

            </table>

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
                                        <input type="text" name="accountNo" id="accountNo" class="form-control"
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
                                        <input type="text" name="accountNo" id="accountNo"
                                            class="form-control accountNo" required>
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

</body>

</html>

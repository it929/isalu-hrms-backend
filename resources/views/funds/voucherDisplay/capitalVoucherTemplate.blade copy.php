<style type="text/css">
    @media print {

        /* A4 page setup */
        @page {
            size: A4 portrait;
            margin: 12mm 12mm 12mm 12mm;
        }

        html,
        body {
            width: 210mm;
            height: 297mm;
            font-family: "Palatino Linotype", serif;
            font-size: 11px;
            font-weight: bold;
            color: #000;
            background: #fff;
        }

        /* Remove screen-only UI */
        .hidden-print,
        button,
        input,
        select,
        textarea {
            display: none !important;
        }

        /* Force strong text everywhere */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        b,
        strong,
        th,
        td {
            font-weight: bold !important;
        }

        h4 {
            font-size: 14px;
            margin: 2px 0;
        }

        h6 {
            font-size: 11px;
            margin: 3px 0;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse !important;
            page-break-inside: avoid;
        }

        table th,
        table td {
            border: 1px solid #000 !important;
            padding: 4px 6px !important;
            vertical-align: middle;
        }

        .table-condensed th,
        .table-condensed td {
            padding: 3px 5px !important;
        }

        /* Remove bootstrap background */
        .table th {
            background: #fff !important;
        }

        /* Voucher specific tweaks */
        .box-body {
            padding: 0 !important;
        }

        .make-bold {
            font-weight: bold !important;
        }

        /* Dotted underline fields */
        strong[style*="dotted"] {
            border-bottom: 2px dotted #000 !important;
            padding: 0 25px;
            display: inline-block;
        }

        /* Totals emphasis */
        big,
        .close-account,
        td big strong {
            font-size: 13px !important;
            font-weight: bold !important;
        }

        /* Align totals to bottom */
        td[style*="vertical-align: bottom"] {
            vertical-align: bottom !important;
        }

        /* Avoid breaking key rows */
        tr,
        td,
        th {
            page-break-inside: avoid !important;
        }

        .withHoldTaxVoucher {
            page-break-before: always !important;
        }

        .vatVoucher {
            page-break-before: always !important;
        }

        .stampDutyVoucher {
            page-break-before: always !important;
        }

        .journalMainVoucher {
            page-break-before: always !important;
        }

        .journalWTHVoucher {
            page-break-before: always !important;
        }

        .journalVATVoucher {
            page-break-before: always !important;
        }

        .journalStampVoucher {
            page-break-before: always !important;
        }

        #vref {
            border: none;
        }

        /* Rows separation */
        .row {
            margin-bottom: 4px !important;
        }

        /* Signature lines */
        .signature-line {
            border-bottom: 1px solid #000;
            height: 18px;
            display: inline-block;
            width: 100%;
        }

        .fla1 {
            float: left;
            width: 70%;
        }

        .fra1 {
            float: right;
            width: 30%;
        }

        .jfla1 {
            float: left;
            width: 40%;
        }

        .jfla2 {
            float: left;
            width: 30%;
        }

        .jfla3 {
            float: left;
            width: 30%;
        }

        .checkPass {
            padding: 2px 0px;
        }

        .checkPass2 {
            margin: 10px 0px !important;
        }

        .checkPass3 {
            margin: 5px 0px !important;
        }

        .fl1 {
            float: left;
        }

        .fr1 {
            float: right;
        }

    }

    .print-voucher {
        float: right;

    }

    .printWrap {
        float: right;
        margin-bottom: 10px;
    }

    tr {
        border: 1px solid #000 !important;
    }

    td {
        border: 1px solid #000 !important;
    }

    .checkPass {
        padding: 2px 0px;
    }

    .checkPass2 {
        margin: 10px 0px !important;
    }

    .checkPass3 {
        margin: 5px 0px !important;
    }

    .dotted-line {
        display: inline-block;
        min-width: 30% !important;
        border-bottom: 1px dotted #000;
        padding-bottom: 2px;
        text-align: center;
    }
</style>

<div class="mainVoucher">
    <div class="row">
        <div class="col-xs-12">
            <div class="box-body">
                <div align="center">
                    <h4>
                        <div class="make-bold">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <h4 class="text-center">{{ strtoupper('FEDERAL GOVERNMENT OF NIGERIA') }}</h4>
                            <div>
                                <h4 class="text-center">{{ strtoupper('CAPITAL EXPENDITURE PAYMENT VOUCHER') }}
                                </h4>
                            </div>
                            <div>
                                <h4 class="text-center">{{ strtoupper('CAPITAL BUDGET ONLY') }}</h4>
                            </div>


                            <div class="clearfix"></div>
                            <span class="pull-right"><small>
                                    @if ($list->contractTypeID == 4)
                                        Treasury F27
                                    @else
                                        Treasury F1
                                    @endif

                                </small></span><br />
                            <span class="pull-right hidden-print"><small><span style="color:green;">STATUS: </span>
                                    @if ($list->status == 6)
                                        {{ PAID }}@else{{ $status->description }}
                                    @endif
                                </small></span>
                        </div>
                    </h4>

                </div>

            </div>
        </div>
    </div>

    <div class="row hidden-print" style="margin-bottom: 10px;">
        <div class="col-md-12">
            <a href="javascript:void(0)" class="btn btn-success print-window">Print</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 fla1">
            <div align="left" style="font-weight: 100">
                Departmental No. <b>SCN/OC/<input type="text" class="noborder"
                        datePrepaid="{{ date_format(date_create($list->datePrepared), 'Y') }}"
                        style="border:none; width:50px !important;" transid="{{ $list->transID }}" id="vref"
                        value="{{ $list->vref_no }}" />/{{ date('Y', strtotime(trim($list->datePrepared))) }}</b>.
            </div>
            <table class="table table-borderedd text-center table-condensed" style="font-size: 10px">
                <tbody>
                    <tr>
                        <td colspan="4">Date Type 3</td>
                        <td colspan="4">4 Source 6</td>
                        <td colspan="12">7 &nbsp;&nbsp; Voucher Number &nbsp; &nbsp; 14</td>
                    </tr>
                    <tr>
                        <td colspan="4">VO 1</td>
                        <td>0</td>
                        <td>9</td>
                        <td>1</td>
                        <td>1</td>
                        @if ($list->contractTypeID == 4)
                            <td>C</td>
                            <td>E</td>
                            <td>X</td>
                            <td>1</td>
                        @else
                            <td>R</td>
                            <td>E</td>
                            <td>X</td>
                            <td>1</td>
                        @endif
                        <td colspan="2"></td>
                        <td colspan="2"></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="20">15 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Classification Code
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 26</td>
                    </tr>
                    <tr>
                        <td colspan="8">27 &nbsp;&nbsp;&nbsp; Date &nbsp;&nbsp;&nbsp; 32 &nbsp;&nbsp;&nbsp;</td>
                        <td colspan="12">33 &nbsp;&nbsp;&nbsp; Amount &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;45</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="2"></td>
                        <td colspan="14"><b>&#8358;{{ number_format($list->amtPayable, 2, '.', ',') }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            6 Source 8 <br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </td>
                        <td colspan="16"> Classification Code </td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td>0</td>
                        <td>3</td>
                        <td>1</td>
                        <td>8</td>
                        <td>0</td>
                        <td>0</td>
                        <td>1</td>
                        <td>0</td>
                        <td>0</td>
                        <td>1</td>
                        <td>0</td>
                        <td>0</td>
                        <td colspan="8">{{ substr($list->Code, 0, 4) }} {{ $list->economicCode }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-4 fra1">
            <table class="table table-borderedd input-sm" style="font-size: 9px">
                <tr>
                    <td colspan="2">
                        <div>Station &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Abuja</div>
                    </td>
                </tr>
                <tr>
                    <td>Head</td>
                    <td>246</td>
                </tr>
                <tr>
                    <td>S/Head</td>
                    <td>{{ substr($list->Code, 0, 4) }} {{ $list->economicCode }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9 fla1">
            <div style="margin-bottom: 2px;">
                <div style="text-decoration: none; border-bottom: 2px dotted #000;">
                    <strong>Payee:</strong> &nbsp;&nbsp;&nbsp; <span class="input-lg">
                        @if ($list->companyID == 13)
                            {{ $list->payment_beneficiary }}
                        @else
                            {{ $list->contractor }}
                        @endif
                    </span>
                </div>

                <div style="text-decoration: none;border-bottom: 2px dotted #000;">
                    <strong>Address:</strong> &nbsp;&nbsp;&nbsp; <span class="input-lg"><small>
                            @if ($voucherPreparedByRole == 21)
                                {{ $list->payee_address }}
                            @else
                                {{ $list->address }}
                            @endif
                        </small></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 fra1">
            <table class="table table-borderedd input-sm" style="font-size: 9px">
                <tr>
                    <td colspan="2">
                        <div style="height: 50px;">Cheque Number &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-condensed table-borderedd text-center input-sm">
                <thead>
                    <tr class="input-lg">
                        <th>Date</th>
                        <th>Detailed Description of Service or Article</th>
                        <th>Rate</th>
                        <th>&#8358;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;k</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="150">
                            {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                        </td>
                        <td width="650">
                            <div align="left">
                                {{ $list->paymentDescription }}

                                <div style="padding: 10px 0px">
                                    <!--<b>SCN/  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; /  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; refers.</b>-->
                                </div>
                            </div>

                        </td>
                        <td></td>
                        <td height="20">
                            <b><big>{{ number_format($list->amtPayable, 2, '.', ',') }}</big></b>
                            <?php $amtpayable = $list->amtPayable; ?>
                            <div class="close-account"></div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <table class="input-sm" style="width:100%;">
                                <tbody>
                                    {{-- @if ($list->companyID != 13) --}}
                                    {{-- commented if part payment is being made --}}
                                    {{-- @if ($contractAmount != $list->totalPayment)
                                    <tr>
                                        <td width="200" style="border: none !important; text-align: left;">
                                            @if ($contractAmount == $bbf)
                                                Total Contract Amount
                                            @elseif($contractAmount > $bbf)
                                                Balance Brought Forward
                                            @endif
                                        </td>
                                        <td style="border: none !important; text-align: right;">
                                            &#8358;{{ number_format($bbf, 2, '.', ',') }}
                                        </td>
                                    </tr>
                                @endif --}}

                                    @if ($list->premiumcharge > 0)
                                        <tr>
                                            <td width="200"
                                                style="border: none !important; text-align: left; padding: 0px 4px;">
                                                Total
                                                Amount(Premium Charge Inclusive)</td>
                                            <td style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment, 2, '.', ',') }}</td>
                                        </tr>
                                    @endif

                                    @if ($list->premiumcharge > 0)
                                        <tr>
                                            <td
                                                style="border: none !important; width:200; text-align: left; padding: 0px 4px;">
                                                Less {{ $list->premiumpercentage }}% Premium Charge</td>
                                            <td
                                                style="border: none !important; text-align: right; border-bottom: 2px solid #000; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->premiumcharge, 2, '.', ',') }}</td>
                                        </tr>
                                    @endif

                                    @if ($list->premiumcharge > 0)
                                        <tr>
                                            <td width="200"
                                                style="border: none !important; text-align: left; height: 30px; padding: 0px 4px;">
                                                Amount(VAT Inclusive)</td>
                                            <td style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment - $list->premiumcharge, 2, '.', ',') }}
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td width="200"
                                                style="border: none !important; text-align: left; height: 30px; padding: 0px 4px;">
                                                Amount(VAT Inclusive)</td>
                                            <td style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment, 2, '.', ',') }}</td>
                                        </tr>
                                    @endif

                                    @if ($list->VAT > 0)
                                        <tr>
                                            <td
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Less {{ $list->VAT }}% VAT Payable</td>
                                            <td
                                                style="border: none !important; text-align: right; border-bottom: 2px solid #000; padding: 0px 4px;">
                                                &#8358; @if ($list->VATValue == 0)
                                                    {{ $list->VATValue }}
                                                    @else{{ number_format($list->VATValue, 2, '.', ',') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                    @if ($list->premiumcharge > 0)
                                        <tr>
                                            <td
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Gross Amount</td>
                                            <td style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment - $list->premiumcharge - $list->VATValue, 2, '.', ',') }}
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Gross Amount</td>
                                            <td style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment - $list->VATValue, 2, '.', ',') }}
                                            </td>
                                        </tr>
                                    @endif

                                    @if ($list->WHT > 0)
                                        <tr>
                                            <td
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Less {{ $list->WHT }}% W/H Tax</td>
                                            <td style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;
                                                @if ($list->WHTValue == 0)
                                                    {{ $list->WHTValue }}
                                                    @else{{ number_format($list->WHTValue, 2, '.', ',') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                    @if ($list->stampduty > 0)
                                        <tr>
                                            <td
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Less {{ $list->stampdutypercentage }}% Stamp Duty</td>
                                            <td style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;
                                                @if ($list->stampduty == 0)
                                                    {{ $list->stampduty }}
                                                    @else{{ number_format($list->stampduty, 2, '.', ',') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                    {{-- @endif --}}
                                    <tr>
                                        <td
                                            style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                           Net  Amount Payable</td>
                                        <td
                                            style="border: none !important; text-align: right; border-bottom: 2px solid #000; border-top: 2px solid #000;  padding: 0px 4px;">
                                            &#8358;{{ number_format($list->amtPayable, 2, '.', ',') }}</td>
                                    </tr>
                                    @php $balance1 = $bbf - $list->totalPayment;  @endphp

                                    {{-- @if ($list->companyID != 13) --}}
                                    @if ($balance1 > 0)
                                        <tr>
                                            <td style="border: none !important; width:200; text-align: left;">Balance
                                            </td>
                                            <td
                                                style="border: none !important; text-align: right; border-bottom: 2px solid #000; border-top: 2px solid #000;">
                                                &#8358;{{ number_format($balance1, 2, '.', ',') }}</td>
                                        </tr>
                                    @endif
                                    {{-- @endif --}}

                                </tbody>
                            </table>
                        </td>
                        <td width="100" style="vertical-align: bottom;">
                            Total &#8358;
                        </td>
                        <td style="vertical-align: bottom;">
                            <big><strong>{{ number_format($list->amtPayable, 2, '.', ',') }}</strong></big>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div>
                <div class="checkPass">Checked and Passed for</div>
                <div class="checkPass">Payable at:
                    <strong class="dotted-line">
                        Abuja
                    </strong>
                </div>

                <div class="checkPass">Checking Officer:
                    <strong class="dotted-line">
                        @if ($checkBy != '')
                            {{ $checkBy->name }}
                        @endif
                    </strong>
                </div>

                <div class="checkPass">Station:
                    <strong class="dotted-line">
                        Abuja
                    </strong>
                </div>

                <div class="checkPass2">Date:
                    <strong class="dotted-line">
                        {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}
                    </strong>
                </div>

            </div>
        </div>
    </div>

    <div class="row checkPass2" style="border-bottom: 1px solid black; border-top: 1px solid black;">
        <div class="col-md-6 fl1">
            <h6>Financial Authority:.....................................................................

            </h6>
        </div>
        <div class="col-md-6 fr1">
            <h6> <strong>Total: &#8358;{{ number_format($list->amtPayable, 2, '.', ',') }} </strong> </h6>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 checkPass2">
            I certify the above amount is correct, and was incurred under the Authority quoted,
            that the service have been dully performed; that the rate/price charge is according
            to regulations/contract is fair and reasonable and that the amount of <b><strong><span
                        id="result"></span></strong></b> may be paid under the
            Classification quoted.
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 fl1">
            <div>
                <h6>Place:
                    <strong style="border-bottom: 1px dotted #000; padding: 0 30px;">
                        Abuja
                    </strong>
                </h6>

                <h6>Date:
                    <strong style="border-bottom: 1px dotted #000; padding: 0 30px;">
                        {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}
                    </strong>
                </h6>

            </div>
        </div>
        <div class="col-md-6 fr1">
            .......................................................................................<br>
            <span class="text-center">Signature of Officer Controlling Expenditure</span>
            <h6>Rank or Officer ..................................................</h6>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h6>Received this:...............................day of..............................in payment of the above
                account, the sum of <b><strong><span id="result2"></span></strong></b>

            </h6>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 fl1">
            Witness to Mark......................................................................
        </div>
        <div class="col-md-6 fr1">
            .....................................................................................<br>
            <span class="text-center">Signature of Receiver</span>
        </div>
    </div>
</div>

@if ($list->WHTValue > 0)
    <div class="withHoldTaxVoucher">
        <div class="row">
            <div class="col-xs-12">
                <div class="box-body">
                    <div align="center">
                        <h4>
                            <div class="make-bold">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <h4 class="text-center">{{ strtoupper('FEDERAL GOVERNMENT OF NIGERIA') }}</h4>
                                <div>
                                    <h4 class="text-center">{{ strtoupper('CAPITAL EXPENDITURE PAYMENT VOUCHER') }}
                                    </h4>
                                </div>
                                <div>
                                    <h4 class="text-center">{{ strtoupper('CAPITAL BUDGET ONLY') }}</h4>
                                </div>


                                <div class="clearfix"></div>
                                <span class="pull-right"><small>
                                        @if ($list->contractTypeID == 4)
                                            Treasury F27
                                        @else
                                            Treasury F1
                                        @endif

                                    </small></span><br />
                                <span class="pull-right hidden-print"><small><span style="color:green;">STATUS:
                                        </span>
                                        @if ($list->status == 6)
                                            {{ PAID }}@else{{ $status->description }}
                                        @endif
                                    </small></span>
                            </div>
                        </h4>

                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 fla1">
                <div align="left" style="font-weight: 100">
                    Departmental No. <b>SCN/OC/CAP/<span
                                        class="vrefNo">{{ $list->vref_no }}</span>/{{ date('Y', strtotime(trim($list->datePrepared))) }}</b>.
                </div>
                <table class="table table-borderedd text-center table-condensed" style="font-size: 10px">
                    <tbody>
                        <tr>
                            <td colspan="4">Date Type 3</td>
                            <td colspan="4">4 Source 6</td>
                            <td colspan="12">7 &nbsp;&nbsp; Voucher Number &nbsp; &nbsp; 14</td>
                        </tr>
                        <tr>
                            <td colspan="4">VO 1</td>
                            <td>0</td>
                            <td>9</td>
                            <td>1</td>
                            <td>1</td>
                            @if ($list->contractTypeID == 4)
                                <td>C</td>
                                <td>E</td>
                                <td>X</td>
                                <td>1</td>
                            @else
                                <td>R</td>
                                <td>E</td>
                                <td>X</td>
                                <td>1</td>
                            @endif
                            <td colspan="2"></td>
                            <td colspan="2"></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="20">15 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Classification Code
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 26</td>
                        </tr>
                        <tr>
                            <td colspan="8">27 &nbsp;&nbsp;&nbsp; Date &nbsp;&nbsp;&nbsp; 32 &nbsp;&nbsp;&nbsp;</td>
                            <td colspan="12">33 &nbsp;&nbsp;&nbsp; Amount &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;45</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2"></td>
                            <td colspan="14"><b>&#8358;{{ number_format($list->WHTValue, 2, '.', ',') }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                6 Source 8 <br />
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </td>
                            <td colspan="16"> Classification Code </td>
                        </tr>
                        <tr style="font-weight: bold;">
                            <td>0</td>
                            <td>3</td>
                            <td>1</td>
                            <td>8</td>
                            <td>0</td>
                            <td>0</td>
                            <td>1</td>
                            <td>0</td>
                            <td>0</td>
                            <td>1</td>
                            <td>0</td>
                            <td>0</td>
                            <td colspan="8">{{ substr($list->Code, 0, 4) }} {{ $list->economicCode }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 fra1">
                <table class="table table-borderedd input-sm" style="font-size: 9px">
                    <tr>
                        <td colspan="2">
                            <div>Station &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Abuja</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Head</td>
                        <td>246</td>
                    </tr>
                    <tr>
                        <td>S/Head</td>
                        <td>{{ substr($list->Code, 0, 4) }} {{ $list->economicCode }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9 fla1">
                <div style="margin-bottom: 2px;">
                    <div style="text-decoration: none; border-bottom: 2px dotted #000;">
                        <strong>Payee:</strong> &nbsp;&nbsp;&nbsp; @if ($whtpayee != '')
                            {{ $whtpayee->payee }}
                        @endif
                        <span class="input-lg">
                        </span>
                    </div>

                    <div style="text-decoration: none;border-bottom: 2px dotted #000;">
                        <strong>Address:</strong> &nbsp;&nbsp;&nbsp; @if ($whtpayee != '')
                            {{ $whtpayee->address }}
                        @endif
                        <span class="input-lg">
                            <small></small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fra1">
                <table class="table table-borderedd input-sm" style="font-size: 9px">
                    <tr>
                        <td colspan="2">
                            <div style="height: 50px;">Cheque Number
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-condensed table-borderedd text-center input-sm">
                    <thead>
                        <tr class="input-lg">
                            <th>Date</th>
                            <th>Detailed Description of Service or Article</th>
                            <th>Rate</th>
                            <th>&#8358;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;k</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="150">
                                {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                            </td>
                            <td width="650">
                                <div align="left">
                                    Being {{ $list->WHT > 0 ? $list->WHT : '' }}% deducted from @if ($list->companyID == 13)
                                        {{ $list->payment_beneficiary }}
                                    @else
                                        {{ $list->contractor }}
                                    @endif Vide attached document in File .......... Gross
                                    Amount Paid
                                    &#8358;{{ number_format($list->totalPayment - $list->VATValue, 2, '.', ',') }}
                                    less {{ $list->WHT }}% TAX
                                    &#8358;{{ number_format($list->WHTValue, 2, '.', ',') }}

                                    <div style="padding: 30px 0px">
                                        <!--<b>SCN/  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; /  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; refers.</b>-->
                                    </div>
                                </div>

                            </td>
                            <td></td>
                            <td height="20">
                                <b><big>{{ number_format($list->WHTValue, 2, '.', ',') }}</big></b>
                                <?php $amtpayable = $list->amtPayable; ?>
                                <div class="close-account"></div>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <table class="input-sm" style="width:100%;">
                                    <tbody>
                                        {{-- @if ($list->companyID != 13) --}}
                                        {{-- commented if part payment is being made --}}
                                        {{-- @if ($contractAmount != $list->totalPayment)
                                        <tr>
                                            <td width="200" style="border: none !important; text-align: left;">
                                                @if ($contractAmount == $bbf)
                                                    Total Contract Amount
                                                @elseif($contractAmount > $bbf)
                                                    Balance Brought Forward
                                                @endif
                                            </td>
                                            <td style="border: none !important; text-align: right;">
                                                &#8358;{{ number_format($bbf, 2, '.', ',') }}
                                            </td>
                                        </tr>
                                    @endif --}}

                                        {{-- @if ($list->premiumcharge > 0)
                                            <tr>
                                                <td
                                                    style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                    Gross Amount</td>
                                                <td
                                                    style="border: none !important; text-align: right; padding: 0px 4px;">
                                                    &#8358;{{ number_format($list->totalPayment - $list->premiumcharge - $list->VATValue, 2, '.', ',') }}
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td
                                                    style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                    Gross Amount</td>
                                                <td
                                                    style="border: none !important; text-align: right; padding: 0px 4px;">
                                                    &#8358;{{ number_format($list->totalPayment - $list->VATValue, 2, '.', ',') }}
                                                </td>
                                            </tr>
                                        @endif --}}

                                        {{-- @if ($list->WHT > 0)
                                            <tr>
                                                <td
                                                    style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                    Less {{ $list->WHT }}% W/H Tax</td>
                                                <td
                                                    style="border: none !important; text-align: right; padding: 0px 4px;">
                                                    &#8358;
                                                    @if ($list->WHTValue == 0)
                                                        {{ $list->WHTValue }}
                                                        @else{{ number_format($list->WHTValue, 2, '.', ',') }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif --}}

                                        {{-- @endif --}}
                                        <tr>
                                            <td
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Amount Payable</td>
                                            <td
                                                style="border: none !important; text-align: right; border-bottom: 2px solid #000; border-top: 2px solid #000;  padding: 0px 4px;">
                                                &#8358;{{ number_format($list->WHTValue, 2, '.', ',') }}</td>
                                        </tr>



                                    </tbody>
                                </table>
                            </td>
                            <td width="100" style="vertical-align: bottom;">
                                Total &#8358;
                            </td>
                            <td style="vertical-align: bottom;">
                                <big><strong>{{ number_format($list->WHTValue, 2, '.', ',') }}</strong></big>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div>
                    <div class="checkPass">Checked and Passed for</div>
                    <div class="checkPass">Payable at:
                        <strong class="dotted-line">
                            Abuja
                        </strong>
                    </div>

                    <div class="checkPass">Checking Officer:
                        <strong class="dotted-line">
                            @if ($checkBy != '')
                                {{ $checkBy->name }}
                            @endif
                        </strong>
                    </div>

                    <div class="checkPass">Station:
                        <strong class="dotted-line">
                            Abuja
                        </strong>
                    </div>

                    <div class="checkPass2">Date:
                        <strong class="dotted-line">
                            {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}
                        </strong>
                    </div>

                </div>
            </div>
        </div>

        <div class="row checkPass2" style="border-bottom: 1px solid black; border-top: 1px solid black;">
            <div class="col-md-6 fl1">
                <h6>Financial Authority:.....................................................................

                </h6>
            </div>
            <div class="col-md-6 fr1">
                <h6> <strong>Total: &#8358;{{ number_format($list->WHTValue, 2, '.', ',') }} </strong> </h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 checkPass2">
                I certify the above amount is correct, and was incurred under the Authority quoted,
                that the service have been dully performed; that the rate/price charge is according
                to regulations/contract is fair and reasonable and that the amount of <b><strong><span
                            id="resultTAX"></span></strong></b> may be paid under the
                Classification quoted.
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 fl1">
                <div>
                    <h6>Place:
                        <strong style="border-bottom: 1px dotted #000; padding: 0 30px;">
                            Abuja
                        </strong>
                    </h6>

                    <h6>Date:
                        <strong style="border-bottom: 1px dotted #000; padding: 0 30px;">
                            {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}
                        </strong>
                    </h6>

                </div>
            </div>
            <div class="col-md-6 fr1">
                .......................................................................................<br>
                <span class="text-center">Signature of Officer Controlling Expenditure</span>
                <h6>Rank or Officer ..................................................</h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h6>Received this:...............................day of..............................in payment of the
                    above
                    account, the sum of <b><strong><span id="resultTAX2"></span></strong></b>

                </h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 fl1">
                Witness to Mark......................................................................
            </div>
            <div class="col-md-6 fr1">
                .....................................................................................<br>
                <span class="text-center">Signature of Receiver</span>
            </div>
        </div>
    </div>
@endif

@if ($list->VATValue > 0)
    <div class="vatVoucher">
        <div class="row">
            <div class="col-xs-12">
                <div class="box-body">
                    <div align="center">
                        <h4>
                            <div class="make-bold">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <h4 class="text-center">{{ strtoupper('FEDERAL GOVERNMENT OF NIGERIA') }}</h4>
                                <div>
                                    <h4 class="text-center">{{ strtoupper('CAPITAL EXPENDITURE PAYMENT VOUCHER') }}
                                    </h4>
                                </div>
                                <div>
                                    <h4 class="text-center">{{ strtoupper('CAPITAL BUDGET ONLY') }}</h4>
                                </div>


                                <div class="clearfix"></div>
                                <span class="pull-right"><small>
                                        @if ($list->contractTypeID == 4)
                                            Treasury F27
                                        @else
                                            Treasury F1
                                        @endif

                                    </small></span><br />
                                <span class="pull-right hidden-print"><small><span style="color:green;">STATUS:
                                        </span>
                                        @if ($list->status == 6)
                                            {{ PAID }}@else{{ $status->description }}
                                        @endif
                                    </small></span>
                            </div>
                        </h4>

                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 fla1">
                <div align="left" style="font-weight: 100">
                    Departmental No. <b>SCN/OC/CAP/<span
                                        class="vrefNo">{{ $list->vref_no }}</span>/{{ date('Y', strtotime(trim($list->datePrepared))) }}</b>.
                </div>
                <table class="table table-borderedd text-center table-condensed" style="font-size: 10px">
                    <tbody>
                        <tr>
                            <td colspan="4">Date Type 3</td>
                            <td colspan="4">4 Source 6</td>
                            <td colspan="12">7 &nbsp;&nbsp; Voucher Number &nbsp; &nbsp; 14</td>
                        </tr>
                        <tr>
                            <td colspan="4">VO 1</td>
                            <td>0</td>
                            <td>9</td>
                            <td>1</td>
                            <td>1</td>
                            @if ($list->contractTypeID == 4)
                                <td>C</td>
                                <td>E</td>
                                <td>X</td>
                                <td>1</td>
                            @else
                                <td>R</td>
                                <td>E</td>
                                <td>X</td>
                                <td>1</td>
                            @endif
                            <td colspan="2"></td>
                            <td colspan="2"></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="20">15 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Classification Code
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 26</td>
                        </tr>
                        <tr>
                            <td colspan="8">27 &nbsp;&nbsp;&nbsp; Date &nbsp;&nbsp;&nbsp; 32 &nbsp;&nbsp;&nbsp;</td>
                            <td colspan="12">33 &nbsp;&nbsp;&nbsp; Amount &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;45</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2"></td>
                            <td colspan="14"><b>&#8358;{{ number_format($list->VATValue, 2, '.', ',') }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                6 Source 8 <br />
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </td>
                            <td colspan="16"> Classification Code </td>
                        </tr>
                        <tr style="font-weight: bold;">
                            <td>0</td>
                            <td>3</td>
                            <td>1</td>
                            <td>8</td>
                            <td>0</td>
                            <td>0</td>
                            <td>1</td>
                            <td>0</td>
                            <td>0</td>
                            <td>1</td>
                            <td>0</td>
                            <td>0</td>
                            <td colspan="8">{{ substr($list->Code, 0, 4) }} {{ $list->economicCode }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 fra1">
                <table class="table table-borderedd input-sm" style="font-size: 9px">
                    <tr>
                        <td colspan="2">
                            <div>Station &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Abuja</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Head</td>
                        <td>246</td>
                    </tr>
                    <tr>
                        <td>S/Head</td>
                        <td>{{ substr($list->Code, 0, 4) }} {{ $list->economicCode }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9 fla1">
                <div style="margin-bottom: 2px;">
                    <div style="text-decoration: none; border-bottom: 2px dotted #000;">
                        <strong>Payee:</strong> &nbsp;&nbsp;&nbsp; @if ($vatpayee != '')
                            {{ $vatpayee->payee }}
                        @endif
                        <span class="input-lg">
                        </span>
                    </div>

                    <div style="text-decoration: none;border-bottom: 2px dotted #000;">
                        <strong>Address:</strong> &nbsp;&nbsp;&nbsp; @if ($vatpayee != '')
                            {{ $vatpayee->address }}
                        @endif
                        <span class="input-lg"><small></small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fra1">
                <table class="table table-borderedd input-sm" style="font-size: 9px">
                    <tr>
                        <td colspan="2">
                            <div style="height: 50px;">Cheque Number
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-condensed table-borderedd text-center input-sm">
                    <thead>
                        <tr class="input-lg">
                            <th>Date</th>
                            <th>Detailed Description of Service or Article</th>
                            <th>Rate</th>
                            <th>&#8358;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;k</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="150">
                                {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                            </td>
                            <td width="650">
                                <div align="left">
                                    Being {{ $list->VAT > 0 ? $list->VAT : '' }}% deducted from @if ($list->companyID == 13)
                                        {{ $list->payment_beneficiary }}
                                    @else
                                        {{ $list->contractor }}
                                    @endif Vide attached document in File .......... Gross Amount
                                    Paid
                                    &#8358;{{ number_format($list->totalPayment - $list->VATValue, 2, '.', ',') }} less
                                    {{ $list->VAT }}% VAT &#8358;{{ number_format($list->VATValue, 2, '.', ',') }}

                                    <div style="padding: 30px 0px">
                                        <!--<b>SCN/  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; /  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; refers.</b>-->
                                    </div>
                                </div>

                            </td>
                            <td></td>
                            <td height="20">
                                <b><big>{{ number_format($list->VATValue, 2, '.', ',') }}</big></b>
                                <?php $amtpayable = $list->amtPayable; ?>
                                <div class="close-account"></div>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <table class="input-sm" style="width:100%;">
                                    <tbody>


                                        {{-- <tr>
                                            <td width="200"
                                                style="border: none !important; text-align: left; height: 30px; padding: 0px 4px;">
                                                Amount(VAT Inclusive)</td>
                                            <td style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment, 2, '.', ',') }}</td>
                                        </tr> --}}

                                        {{-- @if ($list->VAT > 0)
                                            <tr>
                                                <td
                                                    style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                    Less {{ $list->VAT }}% VAT</td>
                                                <td
                                                    style="border: none !important; text-align: right; padding: 0px 4px;">
                                                    &#8358;
                                                    @if ($list->VATValue == 0)
                                                        {{ $list->VATValue }}
                                                        @else{{ number_format($list->VATValue, 2, '.', ',') }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif --}}


                                        <tr>
                                            <td
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Amount Payable</td>
                                            <td
                                                style="border: none !important; text-align: right; border-bottom: 2px solid #000; border-top: 2px solid #000;  padding: 0px 4px;">
                                                &#8358;{{ number_format($list->VATValue, 2, '.', ',') }}</td>
                                        </tr>



                                    </tbody>
                                </table>
                            </td>
                            <td width="100" style="vertical-align: bottom;">
                                Total &#8358;
                            </td>
                            <td style="vertical-align: bottom;">
                                <big><strong>{{ number_format($list->VATValue, 2, '.', ',') }}</strong></big>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div>
                    <div class="checkPass">Checked and Passed for</div>
                    <div class="checkPass">Payable at:
                        <strong class="dotted-line">
                            Abuja
                        </strong>
                    </div>

                    <div class="checkPass">Checking Officer:
                        <strong class="dotted-line">
                            @if ($checkBy != '')
                                {{ $checkBy->name }}
                            @endif
                        </strong>
                    </div>

                    <div class="checkPass">Station:
                        <strong class="dotted-line">
                            Abuja
                        </strong>
                    </div>

                    <div class="checkPass2">Date:
                        <strong class="dotted-line">
                            {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}
                        </strong>
                    </div>

                </div>
            </div>
        </div>

        <div class="row checkPass2" style="border-bottom: 1px solid black; border-top: 1px solid black;">
            <div class="col-md-6 fl1">
                <h6>Financial Authority:.....................................................................

                </h6>
            </div>
            <div class="col-md-6 fr1">
                <h6> <strong>Total: &#8358;{{ number_format($list->VATValue, 2, '.', ',') }} </strong> </h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 checkPass2">
                I certify the above amount is correct, and was incurred under the Authority quoted,
                that the service have been dully performed; that the rate/price charge is according
                to regulations/contract is fair and reasonable and that the amount of <b><strong><span
                            id="resultVAT"></span></strong></b> may be paid under the
                Classification quoted.
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 fl1">
                <div>
                    <h6>Place:
                        <strong style="border-bottom: 1px dotted #000; padding: 0 30px;">
                            Abuja
                        </strong>
                    </h6>

                    <h6>Date:
                        <strong style="border-bottom: 1px dotted #000; padding: 0 30px;">
                            {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}
                        </strong>
                    </h6>

                </div>
            </div>
            <div class="col-md-6 fr1">
                .......................................................................................<br>
                <span class="text-center">Signature of Officer Controlling Expenditure</span>
                <h6>Rank or Officer ..................................................</h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h6>Received this:...............................day of..............................in payment of the
                    above
                    account, the sum of <b><strong><span id="resultVAT2"></span></strong></b>

                </h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 fl1">
                Witness to Mark......................................................................
            </div>
            <div class="col-md-6 fr1">
                .....................................................................................<br>
                <span class="text-center">Signature of Receiver</span>
            </div>
        </div>
    </div>
@endif

@if ($list->stampduty > 0)
    <div class="stampDutyVoucher">
        <div class="row">
            <div class="col-xs-12">
                <div class="box-body">
                    <div align="center">
                        <h4>
                            <div class="make-bold">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <h4 class="text-center">{{ strtoupper('FEDERAL GOVERNMENT OF NIGERIA') }}</h4>
                                <div>
                                    <h4 class="text-center">{{ strtoupper('CAPITAL EXPENDITURE PAYMENT VOUCHER') }}
                                    </h4>
                                </div>
                                <div>
                                    <h4 class="text-center">{{ strtoupper('CAPITAL BUDGET ONLY') }}</h4>
                                </div>


                                <div class="clearfix"></div>
                                <span class="pull-right"><small>
                                        @if ($list->contractTypeID == 4)
                                            Treasury F27
                                        @else
                                            Treasury F1
                                        @endif

                                    </small></span><br />
                            </div>
                        </h4>

                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 fla1">
                <div align="left" style="font-weight: 100">
                    Departmental No. <b>SCN/OC/CAP/<span
                                        class="vrefNo">{{ $list->vref_no }}</span>/{{ date('Y', strtotime(trim($list->datePrepared))) }}</b>.
                </div>
                <table class="table table-borderedd text-center table-condensed" style="font-size: 10px">
                    <tbody>
                        <tr>
                            <td colspan="4">Date Type 3</td>
                            <td colspan="4">4 Source 6</td>
                            <td colspan="12">7 &nbsp;&nbsp; Voucher Number &nbsp; &nbsp; 14</td>
                        </tr>
                        <tr>
                            <td colspan="4">VO 1</td>
                            <td>0</td>
                            <td>9</td>
                            <td>1</td>
                            <td>1</td>
                            @if ($list->contractTypeID == 4)
                                <td>C</td>
                                <td>E</td>
                                <td>X</td>
                                <td>1</td>
                            @else
                                <td>R</td>
                                <td>E</td>
                                <td>X</td>
                                <td>1</td>
                            @endif
                            <td colspan="2"></td>
                            <td colspan="2"></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="20">15 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Classification Code
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 26</td>
                        </tr>
                        <tr>
                            <td colspan="8">27 &nbsp;&nbsp;&nbsp; Date &nbsp;&nbsp;&nbsp; 32 &nbsp;&nbsp;&nbsp;</td>
                            <td colspan="12">33 &nbsp;&nbsp;&nbsp; Amount &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;45</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2"></td>
                            <td colspan="14"><b>&#8358;{{ number_format($list->stampduty, 2, '.', ',') }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                6 Source 8 <br />
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </td>
                            <td colspan="16"> Classification Code </td>
                        </tr>
                        <tr style="font-weight: bold;">
                            <td>0</td>
                            <td>3</td>
                            <td>1</td>
                            <td>8</td>
                            <td>0</td>
                            <td>0</td>
                            <td>1</td>
                            <td>0</td>
                            <td>0</td>
                            <td>1</td>
                            <td>0</td>
                            <td>0</td>
                            <td colspan="8">{{ substr($list->Code, 0, 4) }} {{ $list->economicCode }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 fra1">
                <table class="table table-borderedd input-sm" style="font-size: 9px">
                    <tr>
                        <td colspan="2">
                            <div>Station &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Abuja</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Head</td>
                        <td>246</td>
                    </tr>
                    <tr>
                        <td>S/Head</td>
                        <td>{{ substr($list->Code, 0, 4) }} {{ $list->economicCode }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9 fla1">
                <div style="margin-bottom: 2px;">
                    <div style="text-decoration: none; border-bottom: 2px dotted #000;">
                        <strong>Payee:</strong> &nbsp;&nbsp;&nbsp; FIRS Stamp Duty<span class="input-lg"> </span>
                    </div>

                    <div style="text-decoration: none;border-bottom: 2px dotted #000;">
                        <strong>Address:</strong> &nbsp;&nbsp;&nbsp; FIRS Stamp Duty, Abuja<span
                            class="input-lg"><small></small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fra1">
                <table class="table table-borderedd input-sm" style="font-size: 9px">
                    <tr>
                        <td colspan="2">
                            <div style="height: 50px;">Cheque Number
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-condensed table-borderedd text-center input-sm">
                    <thead>
                        <tr class="input-lg">
                            <th>Date</th>
                            <th>Detailed Description of Service or Article</th>
                            <th>Rate</th>
                            <th>&#8358;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;k</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="150">
                                {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                            </td>
                            <td width="650">
                                <div align="left">
                                    Being {{ $list->stampdutypercentage > 0 ? $list->stampdutypercentage : '' }}%
                                    deducted from @if ($list->companyID == 13)
                                        {{ $list->payment_beneficiary }}
                                    @else
                                        {{ $list->contractor }}
                                    @endif Vide attached document in File .......... Gross
                                    Amount Paid
                                    &#8358;{{ number_format($list->totalPayment - $list->VATValue, 2, '.', ',') }}
                                    less {{ $list->stampdutypercentage }}% STAMP DUTY
                                    &#8358;{{ number_format($list->stampduty, 2, '.', ',') }}

                                    <div style="padding: 30px 0px">
                                        <!--<b>SCN/  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; /  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; refers.</b>-->
                                    </div>
                                </div>

                            </td>
                            <td></td>
                            <td height="20">
                                <b><big>{{ number_format($list->stampduty, 2, '.', ',') }}</big></b>
                                <?php $amtpayable = $list->amtPayable; ?>
                                <div class="close-account"></div>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <table class="input-sm" style="width:100%;">
                                    <tbody>

                                        <tr>
                                            <td
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Amount Payable</td>
                                            <td
                                                style="border: none !important; text-align: right; border-bottom: 2px solid #000; border-top: 2px solid #000;  padding: 0px 4px;">
                                                &#8358;{{ number_format($list->stampduty, 2, '.', ',') }}</td>
                                        </tr>



                                    </tbody>
                                </table>
                            </td>
                            <td width="100" style="vertical-align: bottom;">
                                Total &#8358;
                            </td>
                            <td style="vertical-align: bottom;">
                                <big><strong>{{ number_format($list->stampduty, 2, '.', ',') }}</strong></big>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div>
                    <div class="checkPass">Checked and Passed for</div>
                    <div class="checkPass">Payable at:
                        <strong class="dotted-line">
                            Abuja
                        </strong>
                    </div>

                    <div class="checkPass">Checking Officer:
                        <strong class="dotted-line">
                            @if ($checkBy != '')
                                {{ $checkBy->name }}
                            @endif
                        </strong>
                    </div>

                    <div class="checkPass">Station:
                        <strong class="dotted-line">
                            Abuja
                        </strong>
                    </div>

                    <div class="checkPass2">Date:
                        <strong class="dotted-line">
                            {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}
                        </strong>
                    </div>

                </div>
            </div>
        </div>

        <div class="row checkPass2" style="border-bottom: 1px solid black; border-top: 1px solid black;">
            <div class="col-md-6 fl1">
                <h6>Financial Authority:.....................................................................

                </h6>
            </div>
            <div class="col-md-6 fr1">
                <h6> <strong>Total: &#8358;{{ number_format($list->stampduty, 2, '.', ',') }} </strong> </h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 checkPass2">
                I certify the above amount is correct, and was incurred under the Authority quoted,
                that the service have been dully performed; that the rate/price charge is according
                to regulations/contract is fair and reasonable and that the amount of <b><strong><span
                            id="resultSTAMP"></span></strong></b> may be paid under the
                Classification quoted.
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 fl1">
                <div>
                    <h6>Place:
                        <strong style="border-bottom: 1px dotted #000; padding: 0 30px;">
                            Abuja
                        </strong>
                    </h6>

                    <h6>Date:
                        <strong style="border-bottom: 1px dotted #000; padding: 0 30px;">
                            {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}
                        </strong>
                    </h6>

                </div>
            </div>
            <div class="col-md-6 fr1">
                .......................................................................................<br>
                <span class="text-center">Signature of Officer Controlling Expenditure</span>
                <h6>Rank or Officer ..................................................</h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h6>Received this:...............................day of..............................in payment of the
                    above
                    account, the sum of <b><strong><span id="resultSTAMP2"></span></strong></b>

                </h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 fl1">
                Witness to Mark......................................................................
            </div>
            <div class="col-md-6 fr1">
                .....................................................................................<br>
                <span class="text-center">Signature of Receiver</span>
            </div>
        </div>
    </div>
@endif

@for ($i = 1; $i <= 2; $i++)
    <div class="journalMainVoucher" style="margin-top: 40px;">
        <div class="row">
            <div class="col-md-12">
                <div align="center">
                    <img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                        style="width:100px; height:100px;" />

                    <h3><strong>ISALU HOSPITAL LIMITED </strong></h3>
                    <h4>Three Arms Zone, Abuja</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-condensed table-borderedd text-center input-sm">
                    <thead>
                        <tr class="input-lgg">
                            <th>Date</th>
                            <th> Description / Details</th>
                            <th>NCOA CODE (FUND & ECO CODE)</th>
                            <th>Debit &#8358;</th>
                            <th>Credit &#8358;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="150">
                                {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                            </td>
                            <td>{{ $list->ecoCodeDesc }}</td>
                            <td>{{ $list->ecoHeadCode }}{{ $list->economicCode }}</td>
                            <td>{{ number_format($list->amtPayable, 2, '.', ',') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <big><strong>CASHBOOK</strong></big>
                            </td>
                            <td>
                                31020103
                            </td>
                            <td>

                            </td style="vertical-align: bottom;">
                            <td>{{ number_format($list->amtPayable, 2, '.', ',') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <p>NARRATION: <span> {{ $list->paymentDescription }}</span></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 jfla1">
                <div class="checkPass3">PREPARED BY: ..................................................</div><br>
                <div class="checkPass3">CHECKED BY: ..................................................</div><br>
                <div class="checkPass3">APPROVED BY: ..................................................</div><br>
            </div>
            <div class="col-md-3 jfla2">
                <div class="checkPass3">SIGNATURE: ..........................</div><br>
                <div class="checkPass3">SIGNATURE: ...........................</div><br>
                <div class="checkPass3">SIGNATURE: ............................</div><br>

            </div>
            <div class="col-md-3 jfla3">
                <div class="checkPass3">DATE: ..................................</div><br>
                <div class="checkPass3">DATE: ..................................</div><br>
                <div class="checkPass3">DATE: ..................................</div><br>

            </div>
        </div>
    </div>
@endfor

@if ($list->WHTValue > 0)
    @for ($i = 1; $i <= 2; $i++)
        <div class="journalWTHVoucher" style="margin-top: 40px;">
            <div class="row">
                <div class="col-md-12">
                    <div align="center">
                        <img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                            style="width:100px; height:100px;" />

                        <h3><strong>ISALU HOSPITAL LIMITED </strong></h3>
                        <h4>Three Arms Zone, Abuja</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <table class="table table-condensed table-borderedd text-center input-sm">
                        <thead>
                            <tr class="input-lgg">
                                <th>Date</th>
                                <th> Description / Details</th>
                                <th>NCOA CODE (FUND & ECO CODE)</th>
                                <th>Debit &#8358;</th>
                                <th>Credit &#8358;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td width="150">
                                    {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                                </td>
                                <td>WITHOLDING TAX</td>
                                <td>{{ $list->ecoHeadCode }}{{ $list->economicCode }}</td>
                                <td>{{ number_format($list->WHTValue, 2, '.', ',') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <big><strong>CASHBOOK</strong></big>
                                </td>
                                <td>
                                    41030102
                                </td>
                                <td>

                                </td style="vertical-align: bottom;">
                                <td>{{ number_format($list->WHTValue, 2, '.', ',') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <p>NARRATION: <span> Being {{ $list->WHT > 0 ? $list->WHT : '' }}% deducted from @if ($list->companyID == 13)
                                {{ $list->payment_beneficiary }}
                            @else
                                {{ $list->contractor }}
                            @endif Vide attached document in File .......... Gross Amount Paid
                            &#8358;{{ number_format($list->totalPayment - $list->VATValue, 2, '.', ',') }} less
                            {{ $list->WHT }}% &#8358;{{ number_format($list->WHTValue, 2, '.', ',') }} TAX
                        </span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 jfla1">
                    <div class="checkPass3">PREPARED BY: ..................................................</div><br>
                    <div class="checkPass3">CHECKED BY: ..................................................</div><br>
                    <div class="checkPass3">APPROVED BY: ..................................................</div><br>
                </div>
                <div class="col-md-3 jfla2">
                    <div class="checkPass3">SIGNATURE: ..........................</div><br>
                    <div class="checkPass3">SIGNATURE: ...........................</div><br>
                    <div class="checkPass3">SIGNATURE: ............................</div><br>

                </div>
                <div class="col-md-3 jfla3">
                    <div class="checkPass3">DATE: ..................................</div><br>
                    <div class="checkPass3">DATE: ..................................</div><br>
                    <div class="checkPass3">DATE: ..................................</div><br>

                </div>
            </div>
        </div>
    @endfor
@endif

@if ($list->VATValue > 0)
    @for ($i = 1; $i <= 2; $i++)
        <div class="journalVATVoucher" style="margin-top: 40px;">
            <div class="row">
                <div class="col-md-12">
                    <div align="center">
                        <img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                            style="width:100px; height:100px;" />

                        <h3><strong>ISALU HOSPITAL LIMITED </strong></h3>
                        <h4>Three Arms Zone, Abuja</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <table class="table table-condensed table-borderedd text-center input-sm">
                        <thead>
                            <tr class="input-lgg">
                                <th>Date</th>
                                <th> Description / Details</th>
                                <th>NCOA CODE (FUND & ECO CODE)</th>
                                <th>Debit &#8358;</th>
                                <th>Credit &#8358;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td width="150">
                                    {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                                </td>
                                <td>VALUE ADDED TAX</td>
                                <td>{{ $list->ecoHeadCode }}{{ $list->economicCode }}</td>
                                <td>{{ number_format($list->VATValue, 2, '.', ',') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <big><strong>CASHBOOK</strong></big>
                                </td>
                                <td>
                                    41030103
                                </td>
                                <td>

                                </td style="vertical-align: bottom;">
                                <td>{{ number_format($list->VATValue, 2, '.', ',') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <p>NARRATION: <span> Being {{ $list->VAT > 0 ? $list->VAT : '' }}% deducted from @if ($list->companyID == 13)
                                {{ $list->payment_beneficiary }}
                            @else
                                {{ $list->contractor }}
                            @endif Vide attached document in File .......... Gross Amount
                            Paid
                            &#8358;{{ number_format($list->totalPayment - $list->VATValue, 2, '.', ',') }} less
                            {{ $list->VAT }}% VAT &#8358;{{ number_format($list->VATValue, 2, '.', ',') }}
                        </span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 jfla1">
                    <div class="checkPass3">PREPARED BY: ..................................................</div><br>
                    <div class="checkPass3">CHECKED BY: ..................................................</div><br>
                    <div class="checkPass3">APPROVED BY: ..................................................</div><br>
                </div>
                <div class="col-md-3 jfla2">
                    <div class="checkPass3">SIGNATURE: ..........................</div><br>
                    <div class="checkPass3">SIGNATURE: ...........................</div><br>
                    <div class="checkPass3">SIGNATURE: ............................</div><br>

                </div>
                <div class="col-md-3 jfla3">
                    <div class="checkPass3">DATE: ..................................</div><br>
                    <div class="checkPass3">DATE: ..................................</div><br>
                    <div class="checkPass3">DATE: ..................................</div><br>

                </div>
            </div>
        </div>
    @endfor
@endif

@if ($list->stampduty > 0)
    @for ($i = 1; $i <= 2; $i++)
        <div class="journalStampVoucher" style="margin-top: 40px;">
            <div class="row">
                <div class="col-md-12">
                    <div align="center">
                        <img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                            style="width:100px; height:100px;" />

                        <h3><strong>ISALU HOSPITAL LIMITED </strong></h3>
                        <h4>Three Arms Zone, Abuja</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <table class="table table-condensed table-borderedd text-center input-sm">
                        <thead>
                            <tr class="input-lgg">
                                <th>Date</th>
                                <th> Description / Details</th>
                                <th>NCOA CODE (FUND & ECO CODE)</th>
                                <th>Debit &#8358;</th>
                                <th>Credit &#8358;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td width="150">
                                    {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                                </td>
                                <td>STAMP DUTY</td>
                                <td>{{ $list->ecoHeadCode }}{{ $list->economicCode }}</td>
                                <td>{{ number_format($list->stampduty, 2, '.', ',') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <big><strong>CASHBOOK</strong></big>
                                </td>
                                <td>
                                    31020103
                                </td>
                                <td>

                                </td style="vertical-align: bottom;">
                                <td>{{ number_format($list->stampduty, 2, '.', ',') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <p>NARRATION: <span> Being {{ $list->stampdutypercentage > 0 ? $list->stampdutypercentage : '' }}%
                            deducted from @if ($list->companyID == 13)
                                {{ $list->payment_beneficiary }}
                            @else
                                {{ $list->contractor }}
                            @endif Vide attached document in File .......... Gross
                            Amount Paid
                            &#8358;{{ number_format($list->totalPayment - $list->VATValue, 2, '.', ',') }}
                            less {{ $list->stampdutypercentage }}% STAMP DUTY
                            &#8358;{{ number_format($list->stampduty, 2, '.', ',') }}
                        </span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 jfla1">
                    <div class="checkPass3">PREPARED BY: ..................................................</div><br>
                    <div class="checkPass3">CHECKED BY: ..................................................</div><br>
                    <div class="checkPass3">APPROVED BY: ..................................................</div><br>
                </div>
                <div class="col-md-3 jfla2">
                    <div class="checkPass3">SIGNATURE: ..........................</div><br>
                    <div class="checkPass3">SIGNATURE: ...........................</div><br>
                    <div class="checkPass3">SIGNATURE: ............................</div><br>

                </div>
                <div class="col-md-3 jfla3">
                    <div class="checkPass3">DATE: ..................................</div><br>
                    <div class="checkPass3">DATE: ..................................</div><br>
                    <div class="checkPass3">DATE: ..................................</div><br>

                </div>
            </div>
        </div>
    @endfor
@endif

<script type="text/javascript">
    $('.print-window').click(function() {
        window.print();
    });
</script>

<script>
    $(document).ready(function() {
        $("#vref").blur(function() {
            var transactionID = $(this).attr('transid');
            var datePrepaid = $(this).attr('datePrepaid');
            var vref = $(this).val();
            //alert(datePrepaid);
            $.ajax({
                url: murl + '/update/vrefNo',
                type: "post",
                data: {
                    'transactionID': transactionID,
                    'vref': vref,
                    'datePrepaid': datePrepaid,
                    _token: '{{ csrf_token() }}'
                },

                success: function(datas) {
                    console.log(datas.previous);
                    console.log(datas);
                    if (datas.check > 0) {
                        $("#vref").css("border", "5px solid red");
                        $("#vref").val(datas.previous);
                    } else {
                        $(".vrefNo").html(datas.vref_no)
                        window.location.reload();
                    }


                }
            });

        });
    });
</script>

<script type="text/javascript">
    var amount = "";
    var amount = "<?php echo number_format($amtpayable, 2, '.', ''); ?>";
    var money = amount.split('.'); //

    //VAT
    var amountVAT = "";
    var amountVAT = "<?php echo number_format($list->VATValue, 2, '.', ''); ?>";
    var moneyVAT = amountVAT.split('.');

    //TAX
    var amountTAX = "";
    var amountTAX = "<?php echo number_format($list->WHTValue, 2, '.', ''); ?>";
    var moneyTAX = amountTAX.split('.');

    //STAMPD
    var amountSTAMP = "";
    var amountSTAMP = "<?php echo number_format($list->stampduty, 2, '.', ''); ?>";
    var moneySTAMP = amountSTAMP.split('.');

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

        //VAT
        var wordVATs;
        var naira = moneyVAT[0];
        var kobo = moneyVAT[1];
        var word1 = toWords(naira) + "naira";
        var word2 = ", " + toWords(kobo) + " kobo";
        if (kobo != "00")
            wordVATs = word1 + word2;
        else
            wordVATs = word1;
        //
        var getWord = wordVATs.toUpperCase();
        var parternRule1 = /HUNDRED AND NAIRA/ig;
        var parternRule2 = /HUNDRED AND THOUSAND NAIRA/ig;
        var instance1 = parternRule1.test(getWord);
        var instance2 = parternRule2.test(getWord);
        if ((instance1)) {
            document.getElementById('resultVAT').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
            document.getElementById('resultVAT2').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
        } else if ((instance2)) {
            document.getElementById('resultVAT').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
            document.getElementById('resultVAT2').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
        } else {
            document.getElementById('resultVAT').innerHTML = getWord;
            document.getElementById('resultVAT2').innerHTML = getWord;
        }
        //

        //TAX
        var wordTAXs;
        var naira = moneyTAX[0];
        var kobo = moneyTAX[1];
        var word1 = toWords(naira) + "naira";
        var word2 = ", " + toWords(kobo) + " kobo";
        if (kobo != "00")
            wordTAXs = word1 + word2;
        else
            wordTAXs = word1;
        //
        var getWord = wordTAXs.toUpperCase();
        var parternRule1 = /HUNDRED AND NAIRA/ig;
        var parternRule2 = /HUNDRED AND THOUSAND NAIRA/ig;
        var instance1 = parternRule1.test(getWord);
        var instance2 = parternRule2.test(getWord);
        if ((instance1)) {
            document.getElementById('resultTAX').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
            document.getElementById('resultTAX2').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
        } else if ((instance2)) {
            document.getElementById('resultTAX').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
            document.getElementById('resultTAX2').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
        } else {
            document.getElementById('resultTAX').innerHTML = getWord;
            document.getElementById('resultTAX2').innerHTML = getWord;
        }
        //
        //STAMP
        var wordStamp;
        var naira = moneySTAMP[0];
        var kobo = moneySTAMP[1];
        var word1 = toWords(naira) + "naira";
        var word2 = ", " + toWords(kobo) + " kobo";
        if (kobo != "00")
            wordStamp = word1 + word2;
        else
            wordStamp = word1;
        //
        var getWord = wordStamp.toUpperCase();
        var parternRule1 = /HUNDRED AND NAIRA/ig;
        var parternRule2 = /HUNDRED AND THOUSAND NAIRA/ig;
        var instance1 = parternRule1.test(getWord);
        var instance2 = parternRule2.test(getWord);
        if ((instance1)) {
            document.getElementById('resultSTAMP').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
            document.getElementById('resultSTAMP2').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
        } else if ((instance2)) {
            document.getElementById('resultSTAMP').innerHTML = getWord.replace(parternRule2,
                ' HUNDRED THOUSAND NAIRA ');
            document.getElementById('resultSTAMP2').innerHTML = getWord.replace(parternRule2,
                ' HUNDRED THOUSAND NAIRA ');
        } else {
            document.getElementById('resultSTAMP').innerHTML = getWord;
            document.getElementById('resultSTAMP2').innerHTML = getWord;
        }

    }
</script>

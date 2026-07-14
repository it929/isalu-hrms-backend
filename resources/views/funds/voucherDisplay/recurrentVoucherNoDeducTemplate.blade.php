<style type="text/css">
    @media print {

        /* A4 page setup */
        @page {
            size: Legal portrait;
            margin: 12mm 12mm 12mm 12mm;
        }

        html,
        body {
            width: 210mm;
            height: 297mm;
            font-family: "Palatino Linotype", serif;
            font-size: 16px;
            background: #fff;
        }

        /* Remove screen-only UI */
        .hidden-print,
        button,
        select,
        textarea {
            display: none !important;
        }

        /* Force strong text everywhere */
        h1,
        h2,
        h5,
        h6,
        b,
        strong,
        th {
            /* font-weight: bold !important; */
        }

        h3,
        h4 {
            font-weight: bold !important;
        }

        td {
            font-size: 16px;
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
            font-size: 16px !important;
            /* font-weight: bold !important; */
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

        .verificationVoucher {
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

        .beneficiaryList {
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

        .padNarrationPrepared {
            margin-bottom: 40px !important;
            text-justify: "justify";
        }

        .padNarrationPrepared2 {
            margin-bottom: 80px !important;
            text-justify: "justify";
        }

        /* Signature lines */
        .signature-line {
            border-bottom: 1px solid #000;
            height: 18px;
            display: inline-block;
            width: 100%;
        }

        .flla1 {
            float: left;
            width: 25%;
        }

        .flla2 {
            float: left;
            width: 50%;
        }

        .frra1 {
            float: right;
            width: 25%;
        }

        .fla1 {
            float: left;
            width: 70%;
        }

        .fra1 {
            float: right;
            width: 30%;
        }

        .flac1 {
            float: left;
            width: 40%;
        }

        .frac1 {
            float: right;
            width: 60%;
        }

        .fla11a {
            float: left;
            width: 45%;
        }

        .fra11b {
            float: right;
            width: 55%;
        }

        .vla11a {
            float: left;
            width: 60%;
        }

        .vra11b {
            float: right;
            width: 40%;
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

        .rotated-table {
            transform: rotate(-90deg);
            page-break-inside: avoid;
            border-collapse: collapse !important;
        }

        .rotated-table,
        .rotated-table tr,
        .rotated-table td,
        .rotated-table th {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            font-size: 12px;
        }

        .rotate-wrapper {
            /* height: 200px; */
            margin-top: 20px;
            overflow: visible !important;
        }

        .vDesc {
            margin-top: 20px !important;
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
        min-width: 10% !important;
        border-bottom: 1px dotted #000;
        text-align: left;
    }

    .rotate-wrapper {
        position: relative;
        width: 100%;
        height: 220px;
        /* adjust based on content */
        overflow: hidden;
    }

    .rotated-table {
        position: absolute;
        top: 100%;
        left: 0;
        transform: rotate(-90deg);
        transform-origin: top left;
    }


    .rotated-table {
        font-size: 10px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .rotated-table td {
        padding: 2px 4px;
        /* vertical | horizontal */
        vertical-align: middle;
        border: none !important;
    }

    .rotated-table td div {
        white-space: nowrap;
    }
</style>

@php
    function formatVoucherNo($no)
    {
        return str_pad($no, 4, '0', STR_PAD_LEFT);
    }

    $baseVoucherNo = (int) $list->vref_no;

    // Start after main voucher
    $currentOffset = 1;

    $voucherOffsets = [];

    if ($list->WHTValue > 0) {
        $voucherOffsets['wht'] = $currentOffset++;
    }

    if ($list->VATValue > 0) {
        $voucherOffsets['vat'] = $currentOffset++;
    }

    if ($list->stampduty > 0) {
        $voucherOffsets['stamp'] = $currentOffset++;
    }
@endphp
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
                                <h4 class="text-center">{{ strtoupper(' PAYMENT VOUCHER') }}
                                </h4>
                            </div>


                            <div class="clearfix"></div>
                            <span class="pull-right"><small>
                                    Treasury F1
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
        <div class="col-md-6 fla11a">
            <div align="left" style="font-weight: 100">
                Deptal No. <b>SCN/{{$list->contractTypeID == 6 ? 'PE' : 'OC'}}/<input type="text" class="noborder"
                        datePrepaid="{{ date_format(date_create($list->datePrepared), 'Y') }}"
                        style="border:none; width:50px !important;" transid="{{ $list->transID }}" id="vref"
                        value="{{ formatVoucherNo($baseVoucherNo) }}" />/{{ date('Y', strtotime(trim($list->datePrepared))) }}</b>.
            </div>
        </div>
        <div class="col-md-6 fra11b">
            <div align="left" style="font-weight: 100; margin-bottom: 10px;">
                Checked and passed for payment at: <strong class="dotted-line">
                    Abuja
                </strong>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 flla1">
            <div class="rotate-wrapper">
                <table class="rotated-table">
                    <tr>
                        <td>
                            <div>For Use in Payment of Advance</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>Certified the Advance of</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>&#8358; ........................................................</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>has been entered on TF 174 (A) (B) or (C)</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>Deptal No:......................................................</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>Signature:..................................................</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>Name in Block Letters ......................................</div>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
        <div class="col-md-6 flla2">

            <table class="table table-borderedd text-center table-condensed" style="font-size: 10px">
                <tbody>
                    <tr>
                        <td colspan="4">
                            <h6>Date Type 3</h6>
                        </td>
                        <td colspan="4">
                            <h6>4 Source 6</h6>
                        </td>
                        <td colspan="12">
                            <h6>7 &nbsp;&nbsp; Voucher Number &nbsp; &nbsp; 14</h6>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">VO 1</td>
                        {{-- <td>0</td> --}}
                        <td>3</td>
                        <td>1</td>
                        <td>8</td>

                        <td>R</td>
                        <td>E</td>
                        <td>X</td>
                        <td>I</td>
                        <td>-</td>

                        <td colspan="2"></td>
                        <td colspan="2"></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="20">
                            <h6>15 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Classification Code
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 26</h6>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>1</td>
                        <td>8</td>
                        <td>0</td>
                        <td>0</td>
                        <td>2</td>
                        <td>0</td>
                        <td>0</td>
                        <td>1</td>
                        <td>0</td>
                        <td>0</td>
                        @foreach (str_split($list->economicCode) as $char)
                            <td>{{ $char }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="8">
                            <h6>27 &nbsp;&nbsp;&nbsp; Date &nbsp;&nbsp;&nbsp; 32 &nbsp;&nbsp;&nbsp;</h6>
                        </td>
                        <td colspan="12">
                            <h6>33 &nbsp;&nbsp;&nbsp; Amount &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;45</h6>
                        </td>
                    </tr>
                    <tr>
                        @foreach (str_split(str_replace('-', '', date_format(date_create($list->datePrepared), 'd-m-y'))) as $char)
                            <td>{{ $char }}</td>
                        @endforeach
                        <td colspan="14"><b>&#8358;{{ number_format($list->amtPayable, 2, '.', ',') }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <h6>6 Source 8 <br />
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </h6>
                        </td>
                        <td colspan="16">
                            <h6> 49
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Classification Code
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;60</h6>
                        </td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-3 frra1">

            <table class="table table-borderedd input-sm" style="font-size: 9px">
                <tr>
                    <td colspan="2">
                        <div>Station &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Abuja</div>
                    </td>
                </tr>
                <tr>
                    <td>Head</td>
                    <td>318</td>
                </tr>
                <tr>
                    <td>S/Head</td>
                    @if ($list->isAdvances == 1)
                        <td>31060201</td>
                    @else
                        <td>{{ substr($list->Code, 0, 4) }} {{ $list->economicCode }}</td>
                    @endif
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
                            {{-- @if ($voucherPreparedByRole == 21)
                                {{ $list->payee_address }}
                            @else
                                {{ $list->address }}
                            @endif --}}
                            SCN
                        </small></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 fra1"></div>
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
                        <td width="150" rowspan="2">
                            {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                        </td>
                        <td width="650">
                            <div align="left">
                                {{ $list->paymentDescription }}

                                <div style="padding: 10px 0px">
                                    <div><strong style="font-size: 14px;">CERTIFICATE:</strong> I certify that the
                                        expenditure was incurred in the interest of Public Service.</div>
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
                        <td>
                            <table class="input-sm" style="width:100%;">
                                <tbody>

                                    @if ($list->premiumcharge > 0)
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center;">
                                            <div width="200"
                                                style="border: none !important; text-align: left; height: 30px; padding: 0px 4px;">
                                                Gross Amount</div>
                                            <div style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment - $list->premiumcharge, 2, '.', ',') }}
                                            </div>
                                        </div>
                                    @else
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center;">
                                            <div width="200"
                                                style="border: none !important; text-align: left; height: 30px; padding: 0px 4px;">
                                                Gross Amount</div>
                                            <div style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment, 2, '.', ',') }}</div>
                                        </div>
                                    @endif

                                    @if ($list->VAT > 0)
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center;">

                                            <div style="text-align: left; height: 30px; padding: 0px 4px;">
                                                Less {{ $list->VAT }}% VAT Payable
                                            </div>

                                            <div style="text-align: right; padding: 0 4px;">
                                                &#8358;
                                                @if ($list->VATValue == 0)
                                                    {{ $list->VATValue }}
                                                @else
                                                    {{ number_format($list->VATValue, 2, '.', ',') }}
                                                @endif
                                            </div>

                                        </div>
                                    @endif


                                    {{-- @if ($list->premiumcharge > 0)
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center;">
                                            <div
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Gross Amount</div>
                                            <div style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment - $list->premiumcharge - $list->VATValue, 2, '.', ',') }}
                                            </div>
                                        </div>
                                    @else
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center;">
                                            <div
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Gross Amount</div>
                                            <div style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;{{ number_format($list->totalPayment - $list->VATValue, 2, '.', ',') }}
                                            </div>
                                        </div>
                                    @endif --}}

                                    @if ($list->WHT > 0)
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center;">
                                            <div
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Less {{ $list->WHT }}% W/H Tax</div>
                                            <div style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;
                                                @if ($list->WHTValue == 0)
                                                    {{ $list->WHTValue }}
                                                    @else{{ number_format($list->WHTValue, 2, '.', ',') }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if ($list->stampduty > 0)
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center;">
                                            <div
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Less {{ $list->stampdutypercentage }}% Stamp Duty</div>
                                            <div style="border: none !important; text-align: right; padding: 0px 4px;">
                                                &#8358;
                                                @if ($list->stampduty == 0)
                                                    {{ $list->stampduty }}
                                                    @else{{ number_format($list->stampduty, 2, '.', ',') }}
                                                @endif
                                            </div>
                                        </div>

                                    @endif

                                    {{-- @endif --}}
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div
                                            style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                            Net Amount Payable</div>
                                        <div
                                            style="border: none !important; text-align: right; border-bottom: 2px solid #000; border-top: 2px solid #000;  padding: 0px 4px;">
                                            <strong>
                                                &#8358;{{ number_format($list->amtPayable, 2, '.', ',') }}</strong>
                                        </div>
                                    </div>

                                    <div>
                                        Checked and Passed for &nbsp;&nbsp;&nbsp;&nbsp; <h6><strong> <span
                                                    id="resultInDesc"></span></strong></h6>

                                    </div>

                                    @php $balance1 = $bbf - $list->totalPayment;  @endphp

                                    {{-- @if ($list->companyID != 13) --}}
                                    {{-- @if ($balance1 > 0)
                                        <tr>
                                            <td style="border: none !important; width:200; text-align: left;">Balance
                                            </td>
                                            <td
                                                style="border: none !important; text-align: right; border-bottom: 2px solid #000; border-top: 2px solid #000;">
                                                &#8358;{{ number_format($balance1, 2, '.', ',') }}</td>
                                        </tr>
                                    @endif --}}
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
        <div class="col-md-5 flac1">
            <div style="border: 1px solid black; padding: 2px;">
                <div class="checkPass">Payable at:
                    <strong class="dotted-line">
                        Abuja
                    </strong>
                </div>

                <div class="checkPass">Signature:
                    <strong class="dotted-line">

                    </strong>
                </div>
                <div class="checkPass">Name in <br>Block Letters:
                    <strong class="dotted-line">

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
                    </strong> &nbsp;&nbsp;Date:
                    <strong class="dotted-line">
                        {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}
                    </strong>
                </div>

                <div class="checkPass2">Paying Officers <br>Signature:
                    <strong class="dotted-line">

                    </strong>
                </div>
                <div class="checkPass">Name in <br>Block Letters:
                    <strong class="dotted-line">

                    </strong>
                </div>
                <div>
                    <strong>GW/SW &nbsp;&nbsp;&nbsp;{{ date('Y') }} </strong> <br>
                    Anthy AIE NO. etc
                </div>

            </div>
        </div>
        <div class="col-md-7 frac1">
            <span class="text-center"><strong>CERTIFICATE</strong></span>
            <h5>I certify the above amount is correct, and was incurred under the Authority quoted,
                that the service have been dully performed; that the rate/price charge is according
                to regulations/contract is fair and reasonable <br> that the amount of <b><strong><span
                            id="result"></span></strong></b> may be paid under the
                Classification quoted.</h5>

            <div style="text-align: center;" class="vDesc">
                <h6 class="checkPass3">
                    .......................................................................................<br>
                    <span class="text-center">Signature of Officer Contr. Expenditure</span>
                </h6>
            </div>

            <div style="text-align: center;" class="vDesc">
                <h6 class="checkPass3">
                    Place:......................................Date.................<br>
                </h6>
            </div>

            <div style="text-align: center;" class="vDesc">
                <h6 class="checkPass3">
                    Designation:.....................................<br>
                </h6>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h6>Received from the Federal Government of Nigeria the sum of: <b><strong><span
                            id="result2"></span></strong></b>
            </h6>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 fl1">
            <strong>
                <h2 style="border-bottom:2px solid black; ">N
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;K
                </h2>
            </strong>
        </div>
        <div class="col-md-9 fr1">
            <div style="text-align: center;">
                <h6 class="checkPass3">
                    .......................................................................................<br>
                    <span class="text-center">Signature</span>
                </h6>
            </div>

            <div style="text-align: center;" class="vDesc">
                <h6 class="checkPass3">
                    Date:...............................&nbsp;&nbsp;Place....................................<br>
                </h6>
            </div>
        </div>
    </div>
</div>

@if ($list->isClaimId != 0)

    <div class="verificationVoucher">
        <div class="row">
            <div class="col-xs-12">
                <div class="box-body">
                    <div align="center">
                        <h4>
                            <div class="make-bold">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <h4 class="text-center">{{ strtoupper('ISALU HOSPITAL LIMITED') }}</h4>
                                <div>
                                    <h4 class="text-center">{{ strtoupper(' PAYMENT VERIFICATION SLIP') }}
                                    </h4>
                                </div>
                            </div>
                        </h4>

                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 vla11a">
                <div align="left" style="font-weight: 100">
                    Deptal No.
                    <b>SCN/{{$list->contractTypeID == 6 ? 'PE' : 'OC'}}/{{ formatVoucherNo($baseVoucherNo) }}/{{ date('Y', strtotime(trim($list->datePrepared))) }}</b>.
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
                                SCN
                            </small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 vra11b">
                <div align="left" style="font-weight: 100; margin-bottom: 10px;">
                    <strong>This voucher had been processed for <br>
                        Payment on</strong>: ..................................
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-condensed table-borderedd text-center input-sm">
                    <thead>
                        <tr class="input-lg">
                            <th>Date</th>
                            <th>Detailed Description of Service or Works</th>
                            <th>Rate</th>
                            <th>&#8358;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;k</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="150" rowspan="2">
                                {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                            </td>
                            <td width="650">
                                <div align="left">
                                    {{ $list->paymentDescription }}

                                    <div style="padding: 10px 0px">
                                        <div><strong style="font-size: 14px;">CERTIFICATE:</strong> I certify that
                                            the
                                            expenditure was incurred in the interest of Public Service.</div>
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
                            <td>
                                <table class="input-sm" style="width:100%;">
                                    <tbody>
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center;">
                                            <div
                                                style="border: none !important; width:200; text-align: left; height: 30px; padding: 0px 4px;">
                                                Gross Amount</div>
                                            <div
                                                style="border: none !important; text-align: right; border-bottom: 2px solid #000; border-top: 2px solid #000;  padding: 0px 4px;">
                                                <strong>
                                                    &#8358;{{ number_format($list->amtPayable, 2, '.', ',') }}</strong>
                                            </div>
                                        </div>

                                        <div>
                                            Amount In Words &nbsp;&nbsp;&nbsp;&nbsp; <h6><strong> <span
                                                        id="result3"></span></strong></h6>

                                        </div>

                                        {{-- @php $balance1 = $bbf - $list->totalPayment;  @endphp --}}

                                        {{-- @if ($list->companyID != 13) --}}
                                        {{-- @if ($balance1 > 0)
                                        <tr>
                                            <td style="border: none !important; width:200; text-align: left;">Balance
                                            </td>
                                            <td
                                                style="border: none !important; text-align: right; border-bottom: 2px solid #000; border-top: 2px solid #000;">
                                                &#8358;{{ number_format($balance1, 2, '.', ',') }}</td>
                                        </tr>
                                    @endif --}}
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
    </div>

@endif

@for ($i = 1; $i <= 2; $i++)
    <div class="journalMainVoucher" style="margin-top: 40px;">
        <div class="row">
            <div class="col-md-12">
                <div align="center">
                    <img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                        style="width:100px; height:100px;" />

                    <h2><strong>ISALU HOSPITAL LIMITED </strong></h2>
                    <h3>Three Arms Zone, Abuja</h3>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 padNarrationPrepared">
                <table class="table table-condensed table-borderedd text-center input-sm">
                    <thead>
                        <tr class="input-lgg">
                            <th>DATE</th>
                            <th> DESCRIPTION / DETAILS</th>
                            <th>NCOA CODE (FUND & ECO CODE)</th>
                            <th>DEBIT &#8358;</th>
                            <th>CREDIT &#8358;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="150">
                                {{ date_format(date_create($list->datePrepared), 'd-m-Y') }}

                            </td>
                            <td>{{ $list->ecoCodeDesc }}</td>
                            <td>{{ $list->ecoHeadCode }}{{ $list->economicCode }}</td>
                            <td><strong>{{ number_format($list->amtPayable, 2, '.', ',') }}</strong></td>
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
                            <td><strong>{{ number_format($list->amtPayable, 2, '.', ',') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row padNarrationPrepared2">
            <div class="col-md-12">
                <p>NARRATION: <span> {{ $list->paymentDescription }}</span></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 jfla1">
                <div class="checkPass3">PREPARED BY: ............................</div><br>
                <div class="checkPass3">CHECKED BY: .............................</div><br>
                <div class="checkPass3">APPROVED BY: ............................</div><br>
            </div>
            <div class="col-md-3 jfla2">
                <div class="checkPass3">SIGNATURE: .......................</div><br>
                <div class="checkPass3">SIGNATURE: .......................</div><br>
                <div class="checkPass3">SIGNATURE: ........................</div><br>

            </div>
            <div class="col-md-3 jfla3">
                <div class="checkPass3">DATE: ..................................</div><br>
                <div class="checkPass3">DATE: ..................................</div><br>
                <div class="checkPass3">DATE: ..................................</div><br>

            </div>
        </div>
    </div>
@endfor

<?php
$count = DB::table('tblvoucherBeneficiary')->where('voucherID', '=', $list->transID)->count();

$staff = DB::table('tblvoucherBeneficiary')->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')->where('voucherID', '=', $list->transID)->get();
?>
{{-- beneficiary --}}
@if($count > 0)
<div class="beneficiaryList">
    <div class="box-body" style="display:; background: #fff;margin-top: 30px;" id="report3">
        <h3 class="text-center">BENEFICIARIES:{{ $discr }}</h3>
        <div class="col-md-12">
            @if ($count > 0)
                <table id="myTables" class="table table-bordered" cellpadding="10">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Beneficiary </th>
                            <th class="text-center">Amount ( &#8358;)</th>


                        </tr>
                    </thead>
                    <tbody>
                        @php $key = 1; @endphp
                        @foreach ($staff as $s)
                            <tr>

                                <td>{{ $key++ }}</td>
                                <td>{{ $s->beneficiaryDetails }}</td>
                                <td class="text-center">{{ number_format($s->amount, 2) }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
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
                document.getElementById('result3').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
                document.getElementById('resultInDesc').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
            } else if ((instance2)) {
                document.getElementById('result').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
                document.getElementById('result2').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
                document.getElementById('result3').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
                document.getElementById('resultInDesc').innerHTML = getWord.replace(parternRule2,
                    ' HUNDRED THOUSAND NAIRA ');
            } else {
                document.getElementById('result').innerHTML = getWord;
                document.getElementById('result2').innerHTML = getWord;
                document.getElementById('result3').innerHTML = getWord;
                document.getElementById('resultInDesc').innerHTML = getWord;
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
                document.getElementById('resultVAT3').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
            } else if ((instance2)) {
                document.getElementById('resultVAT').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
                document.getElementById('resultVAT2').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
                document.getElementById('resultVAT3').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
            } else {
                document.getElementById('resultVAT').innerHTML = getWord;
                document.getElementById('resultVAT2').innerHTML = getWord;
                document.getElementById('resultVAT3').innerHTML = getWord;
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
                document.getElementById('resultTAX3').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
            } else if ((instance2)) {
                document.getElementById('resultTAX').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
                document.getElementById('resultTAX2').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
                document.getElementById('resultTAX3').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
            } else {
                document.getElementById('resultTAX').innerHTML = getWord;
                document.getElementById('resultTAX2').innerHTML = getWord;
                document.getElementById('resultTAX3').innerHTML = getWord;
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
                document.getElementById('resultSTAMP3').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
            } else if ((instance2)) {
                document.getElementById('resultSTAMP').innerHTML = getWord.replace(parternRule2,
                    ' HUNDRED THOUSAND NAIRA ');
                document.getElementById('resultSTAMP2').innerHTML = getWord.replace(parternRule2,
                    ' HUNDRED THOUSAND NAIRA ');
                document.getElementById('resultSTAMP3').innerHTML = getWord.replace(parternRule2,
                    ' HUNDRED THOUSAND NAIRA ');
            } else {
                document.getElementById('resultSTAMP').innerHTML = getWord;
                document.getElementById('resultSTAMP2').innerHTML = getWord;
                document.getElementById('resultSTAMP3').innerHTML = getWord;
            }

        }
    </script>

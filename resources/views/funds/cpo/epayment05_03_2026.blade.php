<!DOCTYPE html>
<html>

<head>
    <title>ISALU HOSPITAL LIMITED</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://funds.njc.gov.ng/assets/css/datepicker.min.css">

    {{-- <script type="text/javascript" src="{{ asset('assets/js/number_to_word2.js') }}"></script> --}}
    {{-- <script type="text/javascript" src="{{ asset('assets/js/numberToWords.js') }}"></script> --}}
    <script type="text/javascript" src="{{ asset('/assets/js/number_to_word.js') }}"></script>
    <style type="text/css">
        /* @if ($checkApproval == 0)
        */ .watermark-container {
            position: relative;
        }

        .watermark-layer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;

            font-size: 30px;
            font-weight: bold;
            color: rgba(24, 215, 21, 0.05) !important;

            line-height: 15px;
            word-spacing: 10px;
            white-space: pre-wrap;

            pointer-events: none;
            z-index: 0;
        }

        .print-container {
            position: relative;
            z-index: 2;
        }


        @media print {

            .watermark-container::before {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print,
            .hidden-print,
            .no-print * {
                display: none !important;
            }

            body,
            table,
            th,
            td,
            div,
            span {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .RefD span {
                background-color: green !important;
                color: #fff !important;
            }

            .RefD {
                font-size: 18px !important;
            }

            .sig-header {
                background-color: green !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            table th,
            table td {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                /* opacity: 0.7 !important; */
                font-weight: 700 !important;
            }

            .tblborder {
                border: 1px solid #000 !important;
                padding: 2px !important;
                font-size: 18px !important;
                /* border-collapse: collapse !important; */
            }
            .no-printt{
                font-size: 18px !important;
            }
            th,
            .sig-header {
                /* border: 1px solid #000; */
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

            .signature-section {
                page-break-inside: avoid;
            }

        }

        /* @endif
        */ select {
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
        .tblborder {
            border: 1px solid #000 !important;
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

<body style="margin: 20px;">
    <div class="watermark-layer">
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
        NationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginalNationalJudicialInstituteepaymentmandateOriginal
    </div>

    <div class="print-container watermark-container">
        <div class="row">
            <p>
            <div class="input-sm">
                <div class="col-xs-1"><img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                        style="width:100%; height:auto;"></div>
                <div class="col-xs-10">
                    <div>
                        <h4 class="text-success text-center RefD"><strong>ISALU HOSPITAL LIMITED</strong>
                        </h4>

                        <h6 class=" text-center text-success col-md-offset-4 RefD"><strong>ACCOUNT NO.: <select
                                    class="type">

                                    @foreach ($accountDetails as $list)
                                        <option {{ $mandate[0]->NJCAccount == $list->id ? 'selected' : '' }}>
                                            {{ $list->account_no }}</option>
                                    @endforeach

                                </select></strong></h6>
                        <h6 class=" text-center text-success">E-PAYMENT MANDATE</h6>
                    </div>
                </div>
                <div class="col-xs-1"><img style="width:100%; height:auto;" src="{{ asset('Images/coat.png') }}"
                        class="img-responsive responsive"></div>
            </div>
            </p>
        </div>

        <div class="row" style="margin-bottom: 0px !important; padding-bottom: 0px !important;">
            <div align="left" class="col-xs-6">
                <div class="address">
                    {!! $accountAddress->address !!}
                </div>
            </div>

            <div align="right" class="col-xs-6">
                <table>
                    <tr>
                        <td>
                            <div align="left" class="RefD">
                                <span style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                    No:</span> {{ $current_batch }} <br>
                                <div style="margin-top: 10px;"><span
                                        style="background: green; color:#fff !important; padding: 5px 5px !important;">Date:</span>
                                    {{ date('d-M-Y', strtotime($date)) }}</div>
                                <br />
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row"
            style="margin-bottom: 10px !important; margin-top: 0px !important; padding-top: 0px !important;">
            <div class="col-md-12" style="margin:auto; font-size: 18px !important;">
                Please credit the account(s) of the below listed beneficiary(s) and debit our account
                <strong>{{ $accountAddress->account_no }}</strong>
                
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" style="text-align: centerr">
                <div class="table-responsivee">
                    <tr>
                        <td colspan="2">
                            <table class="table table-stripedd table-borderedd" id="tableData">
                                <tr class="tblborder"
                                    style="background-color:green !important; color:#fff !important;">
                                    <td class="tblborder"
                                        style="background-color:green !important; color:#fff !important;">
                                        <div align="center"><strong style="color: white !important;">S/N</strong></div>
                                    </td>
                                    <td class="tblborder"
                                        style="background-color:green !important; color:#fff !important;">
                                        <div align="center"><strong
                                                style="color: white !important;">BENEFICIARY</strong></div>
                                        <div align="center"></div>
                                        <div align="center"></div>
                                    </td>
                                    <td class="tblborder"
                                        style="background-color:green !important; color:#fff !important;">
                                        <div align="center"><strong style="color: white !important;">ACCOUNT
                                                NAME</strong></div>
                                        <div align="center"></div>
                                        <div align="center"></div>
                                    </td>
                                    <td class="tblborder"
                                        style="background-color:green !important; color:#fff !important;"><strong
                                            style="color: white !important;">BANK
                                        </strong></td>
                                    <td class="tblborder"
                                        style="background-color:green !important; color:#fff !important;">
                                        <strong style="color: white !important;">BRANCH</strong>
                                    </td>
                                    <td class="tblborder"
                                        style="background-color:green !important; color:#fff !important;">
                                        <div align="center"><strong style="color: white !important;">ACC NUMBER</strong>
                                        </div>
                                    </td>
                                    <td class="tblborder"
                                        style="background-color:green !important; color:#fff !important;">
                                        <div align="center"><strong style="color: white !important;">AMOUNT</strong>
                                            <span style="color: white !important;">(&#8358;)</span>
                                        </div>
                                    </td>
                                    <td class="tblborder"
                                        style="background-color:green !important; color:#fff !important;">
                                        <strong style="color: white !important;">FIRS TIN</strong>
                                    </td>
                                    <td class="tblborder"
                                        style="background-color:green !important; color:#fff !important;">
                                        <strong style="color: white !important;">PURPOSE OF PAYMENT</strong>
                                    </td>
                                    <td class="tblborder hidden-print"><strong>View Voucher</strong></td>
                                    <td class="tblborder hidden-print"><strong>Update Account No.</strong></td>
                                    <td class="tblborder hidden-print"><strong>Update Narration.</strong></td>
                                </tr>
                                <?php $key = 1; ?>
                                {{-- @php
                                    $groupedMandate = $mandate->groupBy('bank');
                                @endphp --}}
                                {{-- @foreach ($groupedMandate as $bankName => $bankReports) --}}
                                    @foreach ($mandate as $reports)
                                        @php
                                            $url = url('/display/voucher/' . $reports->transactionID);
                                            // Fetch all breakdown vouchers for the current batch
                                            $breakdowns = DB::table('tblepayment_breakdown as b')
                                                ->join('tblepayment as p', 'p.ID', '=', 'b.payment_id')
                                                ->where('p.batch', $current_batch)
                                                ->select('b.transactionID', 'b.payment_id')
                                                ->orderBy('b.ID', 'asc')
                                                ->get()
                                                ->groupBy('payment_id');
                                        @endphp

                                        <!-- MAIN ROW -->
                                        <tr class="tblborder">
                                            <td class="tblborder">{{ $key++ }}</td>
                                            <td class="tblborder" align="left">{{ $reports->contractor }}</td>
                                            <td class="tblborder" align="left">{{$reports->accountName}}</td>
                                            <td class="tblborder" align="left">{{ $reports->bank }}</td>
                                            <td class="tblborder" align="left">ABUJA</td>
                                            <td class="tblborder"><span
                                                    style="display: none;">'</span>{{ $reports->accountNo }}</td>
                                            <td class="tblborder" align="right">
                                                {{ number_format($reports->amount, 2, '.', ',') }}
                                            </td>
                                            <td class="tblborder" align="left"></td>
                                            <td class="tblborder" align="left">{{ $reports->purpose }}</td>

                                            {{-- <td class="tblborder hidden-print">
                                                <a href="{{ $url }}"
                                                    class="btn btn-success btn-xs hidden-print no-print"
                                                    target="_blank">
                                                    View Voucher
                                                </a>
                                            </td> --}}

                                            <td class="hidden-print">
                                                @if(isset($breakdowns[$reports->ID]))
                                                    @foreach($breakdowns[$reports->ID] as $bd)
                                                        <a href="{{ url('/display/voucher/' . $bd->transactionID) }}" 
                                                        class="btn btn-success btn-xs" target="_blank">
                                                            View Voucher
                                                        </a>
                                                    @endforeach
                                                @else
                                                    <a href="{{ url('/display/voucher/' . $reports->transactionID) }}" 
                                                    class="btn btn-success btn-xs" target="_blank">
                                                        View Voucher
                                                    </a>
                                                @endif
                                            </td>

                                            <td class="tblborder hidden-print">
                                                <a href="javascript:void(0)"
                                                    class="update btn btn-success btn-xs hidden-print no-print"
                                                    btc="{{ $current_batch }}" id="{{ $reports->ID }}">
                                                    Update
                                                </a>
                                            </td>

                                            <td class="tblborder hidden-print">
                                                <a href="javascript:void()"
                                                    class="edit btn btn-success btn-xs hidden-print no-print"
                                                    pps="{{ $reports->purpose }}" id="{{ $reports->ID }}">
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- WHT -->
                                        @if ($reports->WHTValue > 0)
                                            <tr class="tblborder">
                                                <td class="tblborder">{{ $key++ }}</td>
                                                <td class="tblborder">{{ $reports->wht_payee }}</td>
                                                <td class="tblborder">FIRS TAX PROMAX WHT</td>
                                                <td class="tblborder">{{ $reports->wht_bank }}</td>
                                                <td class="tblborder">ABUJA</td>
                                                <td class="tblborder"><span
                                                        style="display: none;">'</span>{{ $reports->wht_accountNo }}
                                                </td>
                                                <td class="tblborder" align="right">
                                                    {{ number_format($reports->WHTValue, 2, '.', ',') }}
                                                </td>
                                                <td class="tblborder">-</td>
                                                <td class="tblborder">WHT</td>

                                                <td class="tblborder hidden-print">
                                                    <a href="{{ $url }}"
                                                        class="btn btn-success btn-xs hidden-print no-print"
                                                        target="_blank">
                                                        View Voucher
                                                    </a>
                                                </td>

                                                <td class="tblborder hidden-print">
                                                    <a href="javascript:void()" tx="tax"
                                                        accts="{{ $reports->wht_accountNo }}"
                                                        bk="{{ $reports->wht_bank }}"
                                                        bene="{{ $reports->wht_payee }}"
                                                        class="tax btn btn-success btn-xs hidden-print no-print"
                                                        btc="{{ $current_batch }}" id="{{ $reports->ID }}">
                                                        Update
                                                    </a>
                                                </td>
                                                <td class="tblborder hidden-print"></td>
                                            </tr>
                                        @endif

                                        <!-- VAT -->
                                        @if ($reports->VATValue > 0)
                                            <tr class="tblborder">
                                                <td class="tblborder">{{ $key++ }}</td>
                                                <td class="tblborder">{{ $reports->vat_payee }}</td>
                                                <td class="tblborder">FIRS TAX PROMAX VAT</td>
                                                <td class="tblborder">{{ $reports->vat_bank }}</td>
                                                <td class="tblborder">ABUJA</td>
                                                <td class="tblborder"><span
                                                        style="display: none;">'</span>{{ $reports->vat_accountNo }}
                                                </td>
                                                <td class="tblborder" align="right">
                                                    {{ number_format($reports->VATValue, 2, '.', ',') }}
                                                </td>
                                                <td class="tblborder">-</td>
                                                <td class="tblborder">VAT</td>

                                                <td class="tblborder hidden-print">
                                                    <a href="{{ $url }}"
                                                        class="btn btn-success btn-xs hidden-print no-print"
                                                        target="_blank">
                                                        View Voucher
                                                    </a>
                                                </td>

                                                <td class="tblborder hidden-print">
                                                    <a href="javascript:void()" vt="vat"
                                                        accts="{{ $reports->vat_accountNo }}"
                                                        bk="{{ $reports->vat_bank }}"
                                                        bene="{{ $reports->vat_payee }}"
                                                        class="vat btn btn-success btn-xs hidden-print no-print"
                                                        btc="{{ $current_batch }}" id="{{ $reports->ID }}">
                                                        Update
                                                    </a>
                                                </td>
                                                <td class="tblborder hidden-print"></td>
                                            </tr>
                                        @endif

                                        <!-- STAMP DUTY -->
                                        @if ($reports->stampduty > 0)
                                            <tr class="tblborder">
                                                <td class="tblborder">{{ $key++ }}</td>
                                                <td class="tblborder">FIRS STAMP DUTY</td>
                                                <td class="tblborder">FIRS TAX PROMAX (STAMP DUTY)</td>
                                                <td class="tblborder">UBA PLC</td>
                                                <td class="tblborder">ABUJA</td>
                                                <td class="tblborder"><span
                                                        style="display: none;">'</span>1016286608
                                                </td>
                                                <td class="tblborder" align="right">
                                                    {{ number_format($reports->stampduty, 2, '.', ',') }}
                                                </td>
                                                <td class="tblborder">-</td>
                                                <td class="tblborder">FIRS (STAMP DUTY)</td>

                                                <td class="tblborder hidden-print">
                                                    <a href="{{ $url }}"
                                                        class="btn btn-success btn-xs hidden-print no-print"
                                                        target="_blank">
                                                        View Voucher
                                                    </a>
                                                </td>

                                                <td class="tblborder hidden-print"></td>
                                                <td class="tblborder hidden-print"></td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    <!-- BANK SUBTOTAL -->
                                {{-- @endforeach --}}


                                @if (count((array) $mandate) == 0)
                                    <tr class="tblborder">

                                        <td class="tblborder text-center" colspan="10">Data not available</td>

                                    </tr>
                                @endif
                                @if (count((array) $mandate) > 0)
                                    <tr class="tblborder">
                                        <td class="tblborder" colspan="6"
                                            style="background-color:green !important; color:#fff !important;"><strong
                                                style="color: #fff !important;">Batch Total</strong></td>
                                        <td class="tblborder" align="right"
                                            style="background-color:green !important; color:#fff !important;">
                                            <strong
                                                style="color: #fff !important;">{{ number_format($sum + $whtsum + $vatsum + $stampdutysum, 2) }}
                                            </strong>
                                        </td>
                                        <td class="tblborder hidden-print" colspan="2"></td>
                                        <td class="tblborder"></td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <div class="no-printt" align="center">
                                {{-- <strong><span id="result2"></span>ONLY</strong> --}}
                                <b>Batch Total: &#8358;<b>{{ number_format($sum + $whtsum + $vatsum + $stampdutysum, 2) }} &nbsp;&nbsp;(<span id="result"></span> ONLY )</b>
                            </div>
                            <?php
                            $finalsum = 0;
                            ?>          
                        </td>
                    </tr>
                    <tr>
                        <td colspan="10">

                            <style>
                                .signature-wrapper {
                                    margin-top: 10px;
                                    text-align: left !important;
                                    width: 100%;
                                }

                                /* .signature-row {
                                    display: flex;
                                    justify-content: space-between;
                                } */

                                .signature-col {
                                    width: 99%;
                                }

                                .sig-header {
                                    background: green;
                                    color: #fff;
                                    padding: 5px 10px;
                                    font-weight: bold;
                                    margin-bottom: 15px;
                                }

                                .sig-field {
                                    margin-bottom: 15px;
                                }

                                .sig-line {
                                    display: inline-block;
                                    border-bottom: 1px solid #000;
                                    width: 260px;
                                    height: 18px;
                                }

                                .thumb-box {
                                    border: 1px solid #000;
                                    width: 120px;
                                    height: 120px;
                                    display: inline-block;
                                    vertical-align: top;
                                    margin-left: 20px;
                                }

                                .thumb-label {
                                    margin-left: 20px;
                                    font-weight: 500;
                                }
                            </style>

                            <div class="signature-wrapper">

                                <div class="signature-row">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-6">
                                                <div class="sig-header">Authorised Signatory:</div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="sig-field">
                                                            Name: <span class="sig-line"></span>
                                                        </div>
                                                        <div class="sig-field">
                                                            Signature: <span class="sig-line"></span>
                                                        </div>
                                                        <div class="sig-field">
                                                            Date: <span class="sig-line"></span>
                                                        </div>

                                                        <div class="sig-field">
                                                            GSM No: <span class="sig-line"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="sig-field">
                                                            <span class="thumb-label">Thumb Print</span>
                                                            <div class="thumb-box"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-md-6">
                                                <div class="sig-header">Submitted for Confirmation by:</div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="sig-field">
                                                            Name: <span class="sig-line"></span>
                                                        </div>
                                                        <div class="sig-field">
                                                            Signature: <span class="sig-line"></span>
                                                        </div>
                                                        <div class="sig-field">
                                                            Date: <span class="sig-line"></span>
                                                        </div>

                                                        <div class="sig-field">
                                                            GSM No: <span class="sig-line"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="sig-field">
                                                            <span class="thumb-label">Thumb Print</span>
                                                            <div class="thumb-box"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" style="margin-top: 20px;">
                                        <div class="col-md-12">
                                            <div class="col-md-6">
                                                <div class="sig-header">Authorised Signatory:</div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="sig-field">
                                                            Name: <span class="sig-line"></span>
                                                        </div>
                                                        <div class="sig-field">
                                                            Signature: <span class="sig-line"></span>
                                                        </div>
                                                        <div class="sig-field">
                                                            Date: <span class="sig-line"></span>
                                                        </div>

                                                        <div class="sig-field">
                                                            GSM No: <span class="sig-line"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="sig-field">
                                                            <span class="thumb-label">Thumb Print</span>
                                                            <div class="thumb-box"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-md-6">
                                                <div class="sig-header">For {{ $accountAddress->bank }} Use Only:
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="sig-field">
                                                            Confirmed by me: <span class="sig-line"></span>
                                                        </div>
                                                        <div class="sig-field">
                                                            Signature: <span class="sig-line"></span>
                                                        </div>
                                                        <div class="sig-field">
                                                            Date: <span class="sig-line"></span>
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <div><a href="{{ url()->previous() }}" class="hidden-print btn btn-success">Back</a>
                            </div>
                        </td>
                        <td>
                            <div class="pull-right">
                                <input type="button" class="hidden-print btn btn-success" id="btnPrint"
                                    value="Print" onclick="window.print()" />

                                {{-- <input type="button" class="hidden-print btn btn-success" id="btnExport"
                                    value="Export to Excel" onclick="Export()" /> --}}
                                <button type="button" class="hidden-print btn btn-success" id="exportSingleBatchEpayment">
                                    Export To Excel
                                </button>
                            </div>
                        </td>
                    </tr>

                    <form id="singleBatchForm" method="POST" action="{{ url('/export/singlebatch') }}">
                        @csrf
                        <input type="hidden" name="batchNo" id="batchNo" value="{{ $current_batch }}">
                        <input type="hidden" name="mandateAccNo" id="mandateAccNo" value="{{ $accountAddress->account_no }}">
                        <input type="hidden" name="mandateBankAddr" id="mandateBank" value="{{ $accountAddress->bank }}">
                        <input type="hidden" name="mandateDate" id="mandateDate" value="{{ date('d-M-Y', strtotime($date)) }}">
                    </form>

                </div>
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
                            <label class="control-label col-md-3">Account Name:</label>
                            <div class="col-md-9">
                                <input type="text" name="accountName" id="accountName" class="form-control" required>
                            </div>

                        </div>
                        <div class="clearfix"></div>
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
        <div class="narrateModal modal fade" id="confirmUpdate2" role="dialog" aria-hidden="true">
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
        <div class="payeModal modal fade" id="confirmUpdate3" role="dialog" aria-hidden="true">
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


    <script type="text/javascript">
        $(document).ready(function() {
            lookup();
        });
        var amount = "";
        var amount = "<?php echo number_format($sum + $whtsum + $vatsum + $stampdutysum, 2, '.', ''); ?>";
        var money = amount.split('.'); //

        console.log("reached here")

        function lookup() {
            //Main Voucher
            console.log("invoked lookup");
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
                console.log("result");
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

    <script>
        var murl = "{{ url('/') }}";
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            console.log("loaded");
            $(document).on('click', '.update', function() {
                console.log("clicked hee")
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

             $(document).on('click', '.edit', function() {
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
        // function Export() {
        //     $("#tableData").table2excel({
        //         filename: "{{ 'Batch' }}_{{ $current_batch }}_Mandate.xls"
        //     });
        // }

        function Export() {

            var wb = XLSX.utils.book_new();
            var ws_data = [];

            // ===== HEADER SECTION =====
            ws_data.push(["ISALU HOSPITAL LIMITED"]);
            ws_data.push([]);
            ws_data.push(["The Branch Manager"]);
            ws_data.push(["{{ $accountAddress->bank }}"]);
            ws_data.push(["Abuja"]);
            ws_data.push([]);

            // Instruction row (left) + No & Date (right)
            ws_data.push([
                "Please credit the account(s) of the under-listed beneficiaries and debit our Account Number {{ $accountAddress->account_no }}",
                "", "", "", "",
                "No:",
                "{{ $current_batch }}"
            ]);

            ws_data.push([
                "", "", "", "", "",
                "Date:",
                "{{ date('d-M-Y', strtotime($date)) }}"
            ]);

            ws_data.push([]);

            // ===== TABLE HEADER =====
            ws_data.push([
                "S/N",
                "Beneficiary",
                "Account Name",
                "Bank",
                "Branch",
                "Account Number",
                "Amount (₦)",
                "Purpose of Payment"
            ]);

            var rows = document.querySelectorAll("#tableData tr");
            var serial = 1;

            rows.forEach(function(row) {

                var cells = row.querySelectorAll("td");

                if (cells.length >= 9) {
                    ws_data.push([
                        serial++,
                        cells[1].innerText.trim(),
                        cells[2].innerText.trim(),
                        cells[3].innerText.trim(),
                        cells[4].innerText.trim(),
                        cells[5].innerText.trim(),
                        cells[6].innerText.trim(),
                        cells[8].innerText.trim()
                    ]);
                }
            });

            // ===== BATCH TOTAL =====
            ws_data.push([]);
            ws_data.push([
                "", "", "", "", "",
                "Batch Total",
                "{{ number_format($sum + $whtsum + $vatsum + $stampdutysum, 2) }}"
            ]);

            var ws = XLSX.utils.aoa_to_sheet(ws_data);

            // ===== MERGE TITLE & INSTRUCTION =====
            ws['!merges'] = [
                { s: { r: 0, c: 0 }, e: { r: 0, c: 7 } }, // Title merge
                { s: { r: 6, c: 0 }, e: { r: 6, c: 4 } }  // Instruction sentence merge
            ];

            XLSX.utils.book_append_sheet(wb, ws, "Mandate");

            XLSX.writeFile(wb, "Batch_{{ $current_batch }}_Mandate.xlsx");
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

    <script type="text/javascript">
        $("#exportSingleBatchEpayment").click(function () {
            $("#singleBatchForm").submit();

        });
    </script>

<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

</body>

</html>

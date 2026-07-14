<!DOCTYPE html>
<html>

<head>
    <title>ISALU HOSPITAL LIMITED</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://funds.njc.gov.ng/assets/css/datepicker.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                /* opacity: 0.65 !important; */
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
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
        SupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeriaOriginalEpaymentmandateSupremeCourtofNigeria
    </div>

    <div class="print-container watermark-container">
        <!-- Header Section - CENTERED LAYOUT -->
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

        <!-- First Mandate (Capital Format) -->
        <div class="row">
            <div class="col-md-12" style="text-align: centerr">
                <div class="table-responsivee">
                    <table class="table" id="tableData">
                        <thead>
                            <tr>
                                <th class="tblborder"
                                    style="background-color:green !important; color:#fff !important;">S/N</th>
                                <th class="tblborder"
                                    style="background-color:green !important; color:#fff !important;">Beneficiary</th>
                                <th class="tblborder"
                                    style="background-color:green !important; color:#fff !important;">Bank</th>
                                <th class="tblborder"
                                    style="background-color:green !important; color:#fff !important;">Branch</th>
                                <th class="tblborder"
                                    style="background-color:green !important; color:#fff !important;">Account Number
                                </th>
                                <th class="tblborder"
                                    style="background-color:green !important; color:#fff !important;">Amount (₦)</th>
                                <th class="tblborder"
                                    style="background-color:green !important; color:#fff !important;">FIRS TIN</th>
                                <th class="tblborder"
                                    style="background-color:green !important; color:#fff !important;">Purpose of
                                    Payment</th>
                                {{-- @if ($checkApproval != 0) --}}
                                    <th width="100" class="tblborder hidden-print"
                                        style="background-color:green !important; color:#fff !important;">Voucher</th>
                                    <th width="140" class="tblborder hidden-print"
                                        style="background-color:green !important; color:#fff !important;">Update
                                        Account</th>
                                    <th width="140" class="tblborder hidden-print"
                                        style="background-color:green !important; color:#fff !important;">Update
                                        Narration</th>
                                    <th width="140" class="tblborder hidden-print"
                                        style="background-color:green !important; color:#fff !important;">Payment
                                        Status</th>
                                {{-- @endif --}}
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            @foreach ($mandate as $reports)
                                <?php
                            $url = url('/display/voucher/' . $reports->transactionID);
                            $transId = '';
                            if ($reports->transactionID != $transId):
                            ?>
                                <!-- Main Payment Row -->
                                <tr class="tblborder">
                                    <td class="tblborder">{{ $counter++ }}</td>
                                    <td class="tblborder text-left">{{ $reports->contractor }}</td>
                                    <td class="tblborder">{{ $reports->bank }}</td>
                                    <td class="tblborder">{{ $reports->branch ?? 'ABUJA' }}</td>
                                    <td class="tblborder">{{ $reports->accountNo }}</td>
                                    <td class="tblborder text-right">{{ number_format($reports->amount, 2) }}</td>
                                    <td class="tblborder">{{ $reports->tin ?? '' }}</td>
                                    <td class="tblborder text-left">{{ $reports->purpose }}</td>
                                    {{-- @if ($checkApproval != 0) --}}
                                        <td class="tblborder hidden-print">
                                            <a href="{{ $url }}"
                                                class="btn btn-success btn-xs hidden-print no-print"
                                                target="_blank">View</a>
                                        </td>
                                        <td class="tblborder hidden-print">
                                            @if($reports->is_paid_from_bank == 0)
                                            <a href="javascript:void()"
                                                class="update btn btn-success btn-xs hidden-print no-print"
                                                btc="{{ $current_batch }}" id="{{ $reports->ID }}">Update</a>
                                            @endif
                                        </td>
                                        <td class="tblborder hidden-print">
                                            @if($reports->is_paid_from_bank == 0)
                                            <a href="javascript:void()"
                                                class="edit btn btn-success btn-xs hidden-print no-print"
                                                pps="{{ $reports->purpose }}" id="{{ $reports->ID }}">Edit</a>
                                            @endif
                                        </td>
                                        <td class="tblborder hidden-print">
                                            @if($reports->is_paid_from_bank == 0)
                                                @if($reports->amount_is_paid == 1)
                                                    <a href="javascript:void()" 
                                                    class="toggleAmount btn btn-danger btn-xs"
                                                    data-id="{{ $reports->ID }}"
                                                    data-status="0">Undo</a>
                                                @else
                                                    <a href="javascript:void()" 
                                                    class="toggleAmount btn btn-success btn-xs"
                                                    data-id="{{ $reports->ID }}"
                                                    data-status="1">Mark As Paid</a>
                                                @endif
                                            @endif
                                        </td>
                                    {{-- @endif --}}
                                </tr>
                                <?php
                                    $transId = $reports->transactionID;
                                    endif;
                                ?>
                                <!-- WHT Row -->
                                <tr class="tblborder">
                                    <td class="tblborder">{{ $counter++ }}</td>
                                    <td class="tblborder text-left">FIRS TAX PROMAX WHT</td>
                                    <td class="tblborder">{{ $reports->wht_bank ?? 'UBA PLC' }}</td>
                                    <td class="tblborder">ABUJA</td>
                                    <td class="tblborder">{{ $reports->wht_accountNo ?? '1016286608' }}</td>
                                    <td class="tblborder text-right">{{ number_format($reports->WHTValue, 2) }}</td>
                                    <td class="tblborder">-</td>
                                    <td class="tblborder text-left">WHT</td>
                                    {{-- @if ($checkApproval != 0) --}}
                                        <td class="tblborder hidden-print">
                                            <a href="{{ $url }}"
                                                class="btn btn-success btn-xs hidden-print no-print"
                                                target="_blank">View</a>
                                        </td>
                                        <td class="tblborder hidden-print">
                                            @if($reports->is_paid_from_bank == 0)
                                            <a href="javascript:void()" tx="tax"
                                                accts="{{ $reports->wht_accountNo }}" bk="{{ $reports->wht_bank }}"
                                                bene="{{ $reports->wht_payee }}"
                                                class="tax btn btn-success btn-xs hidden-print no-print"
                                                btc="{{ $current_batch }}" id="{{ $reports->ID }}">Update</a>
                                            @endif
                                        </td>
                                        <td class="tblborder hidden-print"></td>
                                        <td class="tblborder hidden-print">
                                            @if($reports->is_paid_from_bank == 0)
                                                @if($reports->wht_is_paid == 1)
                                                    <a href="javascript:void()" 
                                                    class="toggleWHT btn btn-danger btn-xs"
                                                    data-id="{{ $reports->ID }}"
                                                    data-status="0">Undo</a>
                                                @else
                                                    <a href="javascript:void()" 
                                                    class="toggleWHT btn btn-success btn-xs"
                                                    data-id="{{ $reports->ID }}"
                                                    data-status="1">Mark As Paid</a>
                                                @endif
                                            @endif
                                        </td>
                                    {{-- @endif --}}
                                </tr>

                                <!-- VAT Row -->
                                <tr class="tblborder">
                                    <td class="tblborder">{{ $counter++ }}</td>
                                    <td class="tblborder text-left">FIRS TAX PROMAX VAT</td>
                                    <td class="tblborder">{{ $reports->vat_bank ?? 'UBA PLC' }}</td>
                                    <td class="tblborder">ABUJA</td>
                                    <td class="tblborder">{{ $reports->vat_accountNo ?? '1016286608' }}</td>
                                    <td class="tblborder text-right">{{ number_format($reports->VATValue, 2) }}</td>
                                    <td class="tblborder">-</td>
                                    <td class="tblborder text-left">VAT</td>
                                    {{-- @if ($checkApproval != 0) --}}
                                        <td class="tblborder hidden-print">
                                            <a href="{{ $url }}"
                                                class="btn btn-success btn-xs hidden-print no-print"
                                                target="_blank">View</a>
                                        </td>
                                        <td class="tblborder hidden-print">
                                            @if($reports->is_paid_from_bank == 0)
                                            <a href="javascript:void()" vt="vat"
                                                accts="{{ $reports->vat_accountNo }}" bk="{{ $reports->vat_bank }}"
                                                bene="{{ $reports->vat_payee }}"
                                                class="vat btn btn-success btn-xs hidden-print no-print"
                                                btc="{{ $current_batch }}" id="{{ $reports->ID }}">Update</a>
                                            @endif
                                        </td>
                                        <td class="hidden-print"></td>
                                        <td class="tblborder hidden-print">
                                            @if($reports->is_paid_from_bank == 0)
                                                @if($reports->vat_is_paid == 1)
                                                    <a href="javascript:void()" 
                                                    class="toggleVAT btn btn-danger btn-xs"
                                                    data-id="{{ $reports->ID }}"
                                                    data-status="0">Undo</a>
                                                @else
                                                    <a href="javascript:void()" 
                                                    class="toggleVAT btn btn-success btn-xs"
                                                    data-id="{{ $reports->ID }}"
                                                    data-status="1">Mark As Paid</a>
                                                @endif
                                            @endif
                                        </td>
                                    {{-- @endif --}}
                                </tr>

                                <!-- Stamp Duty Row -->
                                @if ($stampdutysum > 0)
                                    <tr class="tblborder">
                                        <td class="tblborder">{{ $counter++ }}</td>
                                        <td class="tblborder text-left">FIRS TAX PROMAX (STAMP DUTY)</td>
                                        <td class="tblborder">{{ 'UBA PLC' }}</td>
                                        <td class="tblborder">ABUJA</td>
                                        <td class="tblborder">{{ '1016286608' }}</td>
                                        <td class="tblborder text-right">{{ number_format($stampdutysum, 2) }}</td>
                                        <td class="tblborder">-</td>
                                        <td class="tblborder text-left">FIRS (STAMP DUTY)</td>
                                        {{-- @if ($checkApproval != 0) --}}
                                            <td class="tblborder hidden-print">
                                                <a href="{{ $url }}"
                                                    class="btn btn-success btn-xs hidden-print no-print"
                                                    target="_blank">View</a>
                                            </td>
                                            <td class="tblborder hidden-print"></td>
                                            <td class="tblborder hidden-print"></td>
                                        {{-- @endif --}}
                                    </tr>
                                @endif
                            @endforeach

                            @if (count($mandate) == 0)
                                <tr>
                                    <td colspan="{{ $checkApproval != 0 ? 11 : 8 }}" class="text-center">Data not
                                        available</td>
                                </tr>
                            @endif
                        </tbody>
                        @if (count($mandate) > 0)
                            <tr class="tblborder">
                                <td class="tblborder" colspan="5"
                                    style="background-color:green !important; color:#fff !important;"><strong
                                        style="color: #fff !important;">Batch Total</strong></td>
                                <td class="tblborder" align="right"
                                    style="background-color:green !important; color:#fff !important;">
                                    <strong
                                        style="color: #fff !important;">{{ number_format($sum + $whtsum + $vatsum + $stampdutysum, 2) }}
                                    </strong>
                                </td>
                                <td class="tblborder hidden-print"></td>
                                <td class="tblborder hidden-print"></td>
                            </tr>
                        @endif
                    </table>

                    <tr>
                        <td colspan="2" align="right">
                            <div class="no-printt" align="right">
                                {{-- <strong><span id="result2"></span></strong> --}}
                               <b> Batch Total: &#8358;{{ number_format($sum + $whtsum + $vatsum + $stampdutysum, 2) }} &nbsp;&nbsp; (<span id="result"></span> ONLY ) </b>
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
                                    <div class="col-md-12"><strong><u> ALL DUE PROCESS COMPLIED WITH </u></strong></div>
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
                                <button type="button" class="btn btn-success hidden-print" id="exportSingleBatchEpayment">
                                    Export To Excel
                                </button>

                                <button type="button" class="btn btn-success hidden-print" id="regenerateMandate">
                                    Regenerate Mandate
                                </button>
                            </div>
                        </td>
                    </tr>

                    <form id="singleBatchForm" method="POST" action="{{ url('/export/singlebatchCapital') }}">
                        @csrf
                        <input type="hidden" name="batchNo" id="batchNo" value="{{ $current_batch }}">
                        <input type="hidden" name="mandateAccNo" id="mandateAccNo"
                            value="{{ $accountAddress->account_no }}">
                        <input type="hidden" name="mandateBankAddr" id="mandateBank"
                            value="{{ $accountAddress->bank }}">
                        <input type="hidden" name="mandateDate" id="mandateDate"
                            value="{{ date('d-M-Y', strtotime($date)) }}">
                    </form>

                    <form id="newMandate" method="POST" action="{{ url('/cpo/re-generate/neft') }}">
                        @csrf
                        <input type="hidden" name="batchNo" id="batchNo" value="{{ $current_batch }}">
                        
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
        $(document).ready(function() {
            lookup();
        });
        var murl = "{{ url('/') }}";
        var amount = "<?php echo number_format($sum + $whtsum + $vatsum + $stampdutysum, 2, '.', ''); ?>";
        var money = amount.split('.');

        // Flag to prevent multiple print triggers
        var isPrinting = false;

        function lookup() {
            // Convert amount to words
            var words;
            var naira = money[0];
            var kobo = money[1];
            var word1 = toWords(naira) + "naira";
            var word2 = ", " + toWords(kobo) + " kobo";

            if (kobo != "00")
                words = word1 + word2;
            else
                words = word1;

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
        }

        // Update account modal
            $(document).on('click', '.update', function() {
                console.log('Update button clicked');
                var batchNo = $(this).attr('btc');
                var epayID = $(this).attr('id');
                $(".batch").val(batchNo);
                $(".epaymentID").val(epayID);
                $(".actModal").modal('show');
            });

            // Edit narration modal
            $(document).on('click', '.edit', function() {
                var narration = $(this).attr('pps');
                var epayID = $(this).attr('id');
                $(".narration").val(narration);
                $(".eID").val(epayID);
                $(".narrateModal").modal('show');
            });

            // Edit purpose modal
            $(document).on('click', '.editPurpose', function() {
                var narration = $(this).attr('pps');
                var bk = $(this).attr('id');
                $(".purpose").val(narration);
                $(".bankname").val(bk);
                $(".purposeModal").modal('show');
            });

            // Tax update modal
            $(document).on('click', '.tax', function() {
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

            // VAT update modal
            $(document).on('click', '.vat', function() {
                var batchNo = $(this).attr('btc');
                var epayID = $(this).attr('id');
                var acct = $(this).attr('accts');
                var bene = $(this).attr('bene');
                var bank = $(this).attr('bk');
                var p = $(this).attr('vt');
                $("#paye").val(p);
                $(".batch").val(batchNo);
                $(".epaymentID").val(epayID);
                $(".benefi").val(bene);
                $("#banks").val(bank);
                $(".accountNo").val(acct);
                $(".payeModal").modal('show');
            });

            $(document).on('click', '.toggleAmount', function () {

                let id = $(this).data('id');
                let status = $(this).data('status');

                $.post('/epayment/toggle-amount', {
                    id: id,
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, function () {
                    location.reload();
                });

            });


            $(document).on('click', '.toggleWHT', function () {

                let id = $(this).data('id');
                let status = $(this).data('status');

                $.post('/epayment/toggle-wht', {
                    id: id,
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, function () {
                    location.reload();
                });

            });


            $(document).on('click', '.toggleVAT', function () {

                let id = $(this).data('id');
                let status = $(this).data('status');

                $.post('/epayment/toggle-vat', {
                    id: id,
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, function () {
                    location.reload();
                });

            });

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

        // Custom print function with better handling
        function customPrint() {
            if (isPrinting) return; // Prevent multiple clicks

            isPrinting = true;
            var printButton = document.getElementById('printButton');

            if (printButton) {
                printButton.disabled = true;
                printButton.value = "Printing...";
            }

            // Add compact mode class
            document.body.classList.add('printing-compact-mode');

            // Force images to load before printing
            $('img').each(function() {
                var img = new Image();
                img.src = $(this).attr('src');
            });

            // Small delay to ensure images are loaded
            setTimeout(function() {
                try {
                    // Use a promise-based approach for better control
                    var printPromise = new Promise(function(resolve, reject) {
                        // Store original onbeforeprint and onafterprint
                        var originalBeforePrint = window.onbeforeprint;
                        var originalAfterPrint = window.onafterprint;

                        // Set up afterprint event to re-enable button
                        window.onafterprint = function() {
                            // Remove compact mode class
                            document.body.classList.remove('printing-compact-mode');

                            isPrinting = false;
                            if (printButton) {
                                printButton.disabled = false;
                                printButton.value = "Print";
                            }
                            // Restore original handler
                            if (originalAfterPrint) {
                                originalAfterPrint();
                            }
                            resolve();
                        };

                        // Trigger print
                        window.print();
                    });

                    // Fallback timeout to re-enable button
                    setTimeout(function() {
                        if (isPrinting) {
                            isPrinting = false;
                            document.body.classList.remove('printing-compact-mode');
                            if (printButton) {
                                printButton.disabled = false;
                                printButton.value = "Print";
                            }
                        }
                    }, 5000); // 5 second timeout

                } catch (error) {
                    console.error('Print error:', error);
                    isPrinting = false;
                    document.body.classList.remove('printing-compact-mode');
                    if (printButton) {
                        printButton.disabled = false;
                        printButton.value = "Print";
                    }
                }
            }, 300);
        }

        $(document).ready(function() {
            // Preload images for printing
            function preloadImages() {
                var images = [
                    '{{ asset('Images/scn_logo.png') }}',
                    '{{ asset('Images/coat.jpg') }}'
                ];

                images.forEach(function(src) {
                    var img = new Image();
                    img.src = src;
                });
            }

            // Call preload function
            preloadImages();

            // Print button click handler with column width adjustment
            $('#printButton').click(function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Adjust column widths for large amounts before printing
                adjustColumnWidthsForPrint();

                customPrint();
            });

            // Function to adjust column widths for printing
            function adjustColumnWidthsForPrint() {
                // Find the widest amount in the table
                var maxAmountWidth = 0;
                var maxAmountText = '';

                $('.payment-table td.text-right').each(function() {
                    var text = $(this).text().trim();
                    // Approximate width: each character ~7px, comma ~4px, decimal ~5px
                    var textWidth = text.length * 6.5;
                    if (textWidth > maxAmountWidth) {
                        maxAmountWidth = textWidth;
                        maxAmountText = text;
                    }
                });

                // Add extra padding (20px for Naira symbol and padding)
                var requiredWidth = Math.max(130, maxAmountWidth + 25);

                // Apply the width to amount columns
                $('.payment-table th:nth-child(6), .payment-table td:nth-child(6)').css({
                    'min-width': requiredWidth + 'px',
                    'width': requiredWidth + 'px'
                });

                console.log('Largest amount:', maxAmountText, 'Required width:', requiredWidth + 'px');
            }

            // Also handle the window.print event to prevent multiple calls
            $(window).on('beforeprint', function() {
                if (isPrinting) {
                    console.log('Print already in progress');
                }
            });

            // Print voucher selection
            $('.print-voucher-select').change(function() {
                var value = $(this).val();

                if (value == 'first') {
                    $("#second").hide();
                    $("#first").show();
                    $("#third").hide();
                } else if (value == 'second') {
                    $("#first").hide();
                    $("#second").show();
                    $("#third").hide();
                } else if (value == 'third') {
                    $("#first").hide();
                    $("#second").hide();
                    $("#third").show();
                } else {
                    $("#first").show();
                    $("#second").show();
                    $("#third").show();
                }
            });

            // Account change
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
                        $('.address').html(datas.address);
                    }
                });
            });

            // Signatory 1
            $(".selectname").on('change', function() {
                var id = $(this).val();
                var batch = "{{ $current_batch }}";
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
                    }
                });
            });

            // Signatory 2
            $(".selectname2").on('change', function() {
                var id = $(this).val();
                var batch = "{{ $current_batch }}";
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
                    }
                });
            });

            // Batch reference update
            $("#batchRef").blur(function() {
                var batch = $(this).attr('bch');
                var newBatch = $(this).val();
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

            // Reference update
            $("#ref").blur(function() {
                var batch = $(this).attr('bch');
                var newBatch = $(this).val();
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

            // Date prepared update
            $("#dateprep").change(function() {
                var batch = $(this).attr('bch');
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

            // Datepicker
            $(function() {
                $("#dateprep").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: 'yy-mm-dd'
                });
            });

            // Prevent back button from resubmitting form
            $('.btn-default[href="{{ url()->previous() }}"]').click(function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to go back?')) {
                    window.location.href = $(this).attr('href');
                }
            });

            // Add afterprint event listener as fallback
            if (window.matchMedia) {
                var mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function(mql) {
                    if (!mql.matches) {
                        // Print dialog was closed (either printed or cancelled)
                        isPrinting = false;
                        document.body.classList.remove('printing-compact-mode');
                        var printButton = document.getElementById('printButton');
                        if (printButton) {
                            printButton.disabled = false;
                            printButton.value = "Print";
                        }
                    }
                });
            }

            // Traditional afterprint event as additional fallback
            window.onafterprint = function() {
                isPrinting = false;
                document.body.classList.remove('printing-compact-mode');
                var printButton = document.getElementById('printButton');
                if (printButton) {
                    printButton.disabled = false;
                    printButton.value = "Print";
                }
            };
        });
    </script>
        <script type="text/javascript">
        $("#exportSingleBatchEpayment").click(function () {
            $("#singleBatchForm").submit();

        });
        $("#regenerateMandate").click(function () {
            $("#newMandate").submit();

        });
        
    </script>
</body>

</html>

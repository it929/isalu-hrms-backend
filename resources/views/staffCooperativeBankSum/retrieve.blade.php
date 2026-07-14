<!DOCTYPE html>
<html>

    <head>
        <title>ISALU HOSPITAL LIMITED :: Staff Cooperative E-Payment</title>

        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

        <style>
            body,
            td,
            th {
                font-family: Verdana, Geneva, sans-serif;
                font-size: 14px;
                color: #000;
            }

            .tblborder {
                border: 1px dotted #000 !important;
            }

            table tr td,
            table tr th {
                /* border: 1px solid #444 !important; */
                padding: 4px;
            }

            .no-print {
                margin: 20px 0;
            }

            @media print {

                .no-print,
                .no-print * {
                    display: none !important;
                }

                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        </style>

        <style>
            body,
            td,
            th {
                font-family: Verdana, Geneva, sans-serif;
                font-size: 15px;
                color: #000;
            }

            .tblborder {
                border: 1px dotted #000 !important;
            }

            .no-border {
                border: none !important;
            }

            .table td,
            .table th {
                padding: 3px;
                vertical-align: middle;
            }

            .table th {
                text-align: center;
            }

            /* ================= PRINT SETTINGS ================= */
            @media print {

                @page {
                    /* size: A4 portrait; */
                    margin: 12mm 10mm 15mm 10mm;
                }

                body {
                    margin: 0;
                    padding: 0;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                    background: none !important;
                }

                .no-print,
                .no-print * {
                    display: none !important;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    page-break-inside: auto;
                }

                thead {
                    display: table-header-group;
                }

                tr,
                td,
                th {
                    page-break-inside: avoid !important;
                }

                .PleaseCredit {
                    page-break-inside: avoid !important;
                    margin-bottom: 15px;
                }

                .authorizerSign {
                    /* page-break-before: always; */
                    margin-top: 10px;
                }
            }


            /* normal view (optional) */
            .print-head th {
                background: green !important;
                color: #fff !important;
                padding: 5px !important;
            }

            .print-head span {
                background: green !important;
                color: #fff !important;
                padding: 5px !important;
            }

            /* printing */
            @media print {

                /* tell browser to KEEP background colors */
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                /* force the header row background */
                .print-head th {
                    background-color: #008000 !important;
                    color: #ffffff !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                .print-head span {
                    background-color: #008000 !important;
                    color: #ffffff !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
            }
        </style>

        <script src="{{ asset('assets/js/number_to_word.js') }}"></script>

        <link rel="stylesheet" href="{{ asset('assets/css/watermark.css') }}">

        <style>
            .signatories {
                margin-top: 22px;
                display: flex;
                gap: 18px;
                justify-content: space-between;
            }

            .sign-card {
                flex: 1;
                border: 2px solid #006400;
                padding: 12px 14px;
                border-radius: 6px;
                background: transparent;
                /* so watermark shows */
            }

            .sign-title {
                font-weight: 700;
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: .4px;
                padding-bottom: 8px;
                margin-bottom: 10px;
                border-bottom: 1px dashed #006400;
            }

            .sign-row {
                display: flex;
                align-items: flex-end;
                gap: 10px;
                margin: 10px 0;
            }

            .sign-label {
                width: 85px;
                font-weight: 600;
                font-size: 12px;
            }

            .sign-value {
                flex: 1;
                font-size: 13px;
            }

            .sign-line {
                flex: 1;
                border-bottom: 1px solid #006400;
                height: 18px;
            }

            .sign-thumb-row {
                align-items: center;
            }

            .thumb-box {
                width: 110px;
                height: 70px;
                border: 1px solid #006400;
                display: inline-block;
            }

            /* Print safety */
            @media print {
                .sign-card {
                    background: transparent !important;
                }
            }


            .sign-title-green {
                background: green !important;
                color: #fff !important;

                /* alignment */
                display: flex;
                align-items: left;
                /* vertical center */
                justify-content: left;
                /* horizontal center (change to flex-start if you want left) */

                padding: 8px 12px;
                min-height: 34px;

                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .4px;
                margin: 0;
                /* ensure no gaps */
            }

            /* If you insist on keeping <p> inside */
            .sign-title-green p {
                margin: 0 !important;
                /* remove default p margin */
                width: 100%;
                text-align: left;
            }
        </style>
    </head>

    <body onload="lookup()">

        @php
            /* ================= SIGNATORIES ================= */
            $sig1 = DB::table('tblmandatesignatory')
                ->join(
                    'tblmandatesignatoryprofiles',
                    'tblmandatesignatoryprofiles.id',
                    '=',
                    'tblmandatesignatory.signatoryID',
                )
                ->where('tblmandatesignatory.id', 1)
                ->first();

            $sig2 = DB::table('tblmandatesignatory')
                ->join(
                    'tblmandatesignatoryprofiles',
                    'tblmandatesignatoryprofiles.id',
                    '=',
                    'tblmandatesignatory.signatoryID',
                )
                ->where('tblmandatesignatory.id', 2)
                ->first();
        @endphp

        <div class="container-fluid">
            <div class="watermark-container">
                <div class="print-container">
                    @include('payroll.con_epayment.partial.watermark-layer')
                    <!-- ================= HEADER ================= -->
                    <div class="row">
                        <div class="col-xs-2">
                            <img src="{{ asset('Images/scn_logo.png') }}" width="50">
                        </div>
                        <div class="col-xs-8 text-center">
                            <div class="text-center">
                                <h4 class="text-success"><strong>ISALU HOSPITAL LIMITED</strong></h4>
                                {{-- <h5 class="text-success"><strong>ISALU HOSPITAL LIMITED</strong></h5> --}}
                                <h6 class="text-success"><strong>THREE ARM ZONE</strong></h6>
                                <h6 class="text-success">
                                    <strong>ACCOUNT NUMBER: {{ $accountDetails->account_no ?? 'N/A' }}</strong>
                                </h6>
                                <h6 class="text-success"><strong> COOPERATIVE E-PAYMENT MANDATE</strong></h6>
                                <hr>
                            </div>
                        </div>

                        <div class="col-xs-2 text-right">
                            <img src="{{ asset('Images/coat.png') }}" width="50">
                        </div>
                    </div>


                    <!-- ================= INTRO ================= -->
                    <p>
                        Please credit the account(s) of the under-listed beneficiary(s) and debit our account
                        accordingly. <br>
                        {{-- <strong>AMOUNT IN WORDS:</strong> --}}
                        <span id="grandTotalWords"></span>
                    </p>

                    <!-- ================= TABLE ================= -->
                    <table class="table table-bordered table-condensed" id="tableData">
                        <thead>
                            <tr style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                <th style=" color:#fff !important;">#</th>
                                <th style=" color:#fff !important;">BENEFICIARY</th>
                                <th style=" color:#fff !important;">BANK</th>
                                <th style=" color:#fff !important;">BRANCH</th>
                                <th style=" color:#fff !important;">ACCOUNT NO</th>
                                <th style="text-align:right; color:#fff !important;">AMOUNT (₦)</th>
                                <th style=" color:#fff !important;">PURPOSE OF PAYMENT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($staffEarnDeductionReport as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    @if ($row->deduction_name == 'OVERPAYMENT')
                                        <td>The Chief Registrar</td>
                                    @else
                                        <td>{{ $row->deduction_name }}</td>
                                    @endif

                                    <td>{{ $row->bank_name ?? '—' }}</td>
                                    <td>ABUJA</td>
                                    <td><span style="display: none;">'</span>{{ $row->account_number ?? '—' }}</td>
                                    <td style="text-align:right;">
                                        {{ number_format($row->total_amount, 2) }}
                                    </td>
                                    <td>
                                        {{ $month }} {{ $year }} PAYROLL DEDUCTION FOR
                                        {{ strtoupper($row->deduction_name) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr style="font-weight:bold;">
                                <td colspan="5">GRAND TOTAL</td>
                                <td style="text-align:right;">
                                    {{ number_format($cooperativeGrandTotal, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- ================= TOTAL IN WORDS ================= -->
                    <p style="margin-top:15px;">

                    </p>

                    <!-- ================= BUTTONS ================= -->
                    <div class="text-center no-print">
                        @if ($reportData && $reportData->vstage >= 5)
                        <button onclick="window.print()" class="btn btn-primary btn-sm">
                            Print
                        </button>

                        <button class="btn btn-success btn-sm" id="btnExport" onclick="ExportToExcel('xlsx')">
                            Export to Excel
                        </button>
                        @endif
                        <a href="{{ url('/staff/cooperative-bank-sum') }}" class="btn btn-success btn-sm">
                            Back
                        </a>
                    </div>

                    <!-- ================= SIGNATORIES ================= -->
                    <div class="signatories">
                        <div class="sign-card">
                            <div class="sign-title sign-title-green">
                                Authorized Signatory
                            </div>

                            <div class="sign-row">
                                <span class="sign-label">Name</span>
                                <span class="sign-value">{{ $sig1->Name }}</span>
                            </div>

                            <div class="sign-row">
                                <span class="sign-label">Signature</span>
                                <span class="sign-line"></span>
                            </div>

                            <div class="sign-row sign-thumb-row">
                                <span class="sign-label">Thumb Print</span>
                                <span class="thumb-box"></span>
                            </div>
                        </div>

                        <div class="sign-card">
                            <div class="sign-title sign-title-green">Authorized Signatory</div>

                            <div class="sign-row">
                                <span class="sign-label">Name</span>
                                <span class="sign-value">{{ $sig2->Name }}</span>
                            </div>

                            <div class="sign-row">
                                <span class="sign-label">Signature</span>
                                <span class="sign-line"></span>
                            </div>

                            <div class="sign-row sign-thumb-row">
                                <span class="sign-label">Thumb Print</span>
                                <span class="thumb-box"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
        <script src="{{ asset('assets/js/table2excel.js') }}"></script>

        <script>
            var $btnDLtoExcel = $('#DLtoExcel-2');
            $btnDLtoExcel.on('click', function() {
                $("#tableData").excelexportjs({
                    containerid: "tableData",
                    datatype: 'table'
                });

            });
        </script>

        <script type="text/javascript">
            function ExportToExcel() {
                $("#tableData").table2excel({
                    filename: "{{ session('month') }}_{{ session('year') }}_Mandate.xls"
                });
                $("#tableData").excelexportjs({
                    containerid: "tableData",
                    datatype: 'table'
                });
            }
        </script>

        <!-- ================= JS: AMOUNT TO WORDS ================= -->
        <script>
            function lookup() {
                let amount = "{{ number_format($cooperativeGrandTotal, 2, '.', '') }}";
                let parts = amount.split('.');

                let words = toWords(parts[0]) + " NAIRA";

                if (parts[1] !== "00") {
                    words += " AND " + toWords(parts[1]) + " KOBO";
                } else {
                    words += " ONLY";
                }

                document.getElementById('grandTotalWords').innerHTML = words.toUpperCase();
            }
        </script>

    </body>

</html>

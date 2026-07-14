<!DOCTYPE html>
<html>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ISALU HOSPITAL LIMITED...::...Justices E-payment Schedule</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/watermark.css') }}">

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

    <script src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>

<body onload="lookup()">

    <div class="container-fluid">
        {{-- <div class="watermark">ISALU HOSPITAL LIMITED</div> --}}

        <div class="watermark-container">
            <div class="print-container">
                @include('payroll.con_epayment.partial.watermark-layer')

                <!-- ================= HEADER ================= -->
                <div class="PleaseCredit">
                    <div class="row">
                        <div class="col-xs-2">
                            <img src="{{ asset('Images/scn_logo.png') }}" width="50">
                        </div>

                        <div class="col-xs-8 text-center">
                            <h4 class="text-success"><strong>ISALU HOSPITAL LIMITED</strong></h4>
                            {{-- <h5 class="text-success"><strong>ISALU HOSPITAL LIMITED</strong></h5> --}}
                            <h6 class="text-success"><strong>THREE ARM ZONE</strong></h6>
                            <h6 class="text-success">
                                <strong>ACCOUNT NUMBER: {{ $accountDetails->account_no ?? 'N/A' }}</strong>
                            </h6>
                            <h6 class="text-success"><strong>JUSTICES E-PAYMENT SCHEDULE</strong></h6>
                        </div>

                        <div class="col-xs-2 text-right">
                            <img src="{{ asset('Images/coat.png') }}" width="50">
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col-xs-6">
                            <strong>
                                THE BRANCH MANAGER<br>
                                UBA PLC.
                            </strong>
                        </div>
                        <div class="col-xs-6 text-right print-head">
                            {{-- <strong>Ref No: SCN/SALPE/{{ date('m/Y') }}</strong> --}}
                            <strong>
                                <span style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                    Ref No.
                                </span>
                                SCN/SALPE/{{ $monthNumber }}/{{ date('Y') }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- ================= TOTAL IN WORDS ================= -->
                @php
                    $sum1 = $epayment_detail->sum('NetPay');
                @endphp

                <div class="PleaseCredit" style="width:80%; margin:15px auto;">
                    Please credit the account(s) of the listed beneficiary(s) and debit our account with:
                    (₦) <strong>{{ number_format($sum1, 2) }}</strong>
                    <br>
                    <span id="result"></span>
                </div>

                <!-- ================= TABLE ================= -->
                <table class="table table-bordered" id="tableData">
                    <thead>
                        <tr class="tblborder print-head"
                            style="background: green; color:#fff !important; padding: 5px 5px !important;">
                            <th style="color:#fff !important;">S/N</th>
                            <th style="color:#fff !important;">BENEFICIARY</th>
                            <th style="color:#fff !important;">BANK</th>
                            <th style="color:#fff !important;">BRANCH</th>
                            <th style="color:#fff !important;">ACC NUMBER</th>
                            <th style="color:#fff !important;">AMOUNT (₦)</th>
                            <th style="color:#fff !important;">PURPOSE OF PAYMENT</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $counter = session('serialNo');
                            $sum = 0;
                            $subTotal = 0;
                            $bkID = '';
                        @endphp

                        @foreach ($epayment_detail as $reports)
                            @if ($bkID != '' && $bkID != $reports->bankID)
                                <tr class="tblborder">
                                    <td colspan="5"><strong>Sub Total</strong></td>
                                    <td colspan="2"><strong>{{ number_format($subTotal, 2) }}</strong></td>
                                </tr>
                                @php $subTotal = 0; @endphp
                            @endif

                            @php
                                $bkID = $reports->bankID;
                                $subTotal += $reports->NetPay;
                                $sum += $reports->NetPay;
                            @endphp

                            <tr class="tblborder">
                                <td>{{ $counter }}</td>
                                <td>{{ $reports->name }}</td>
                                <td>{{ $reports->bank }}</td>
                                <td>{{ $reports->bank_branch }}</td>
                                <td><span style="display: none;">'</span>{{ $reports->AccNo }}</td>
                                <td align="right">{{ number_format($reports->NetPay, 2) }}</td>
                                <td>
                                    {{ $reports->remarks ?? session('month') . ' ' . session('year') . ' Justices Allowance' }}
                                </td>
                            </tr>

                            @php $counter++; @endphp
                        @endforeach

                        <tr class="tblborder">
                            <td colspan="5"><strong>Sub Total</strong></td>
                            <td colspan="2"><strong>{{ number_format($subTotal, 2) }}</strong></td>
                        </tr>

                        <tr class="tblborder">
                            <td colspan="5"><strong>Total</strong></td>
                            <td colspan="2" align="right"><strong>{{ number_format($sum, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                <!-- ================= BUTTONS ================= -->
                <div class="no-print text-center" style="margin:20px;">
                    @if (isset($epayment_detail[0]) && $epayment_detail[0]->vstage >= 5)
                    <button class="btn btn-primary" onclick="window.print()">Print</button>
                    <button class="btn btn-success btn-sm" id="btnExport" onclick="ExportToExcel('xlsx')">
                        Export to Excel
                    </button>
                    @endif
                    <a href="{{ url('/con-epayment-justices') }}" class="btn btn-success">Back</a>
                </div>

                <!-- ================= SIGNATORIES ================= -->
                @php
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

                <div class="signatories">
                    <div class="sign-card">
                        <div class="sign-title sign-title-green">
                            Authorized Signatory
                        </div>

                        <div class="sign-row">
                            <span class="sign-label">Name</span>
                            <span class="sign-value">{{ $sig1->Name ?? '' }}</span>
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
                            <span class="sign-value">{{ $sig2->Name ?? '' }}</span>
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

            <br><br><br><br>
        </div>
    </div>

    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <script>
        function ExportToExcel() {
            exportXLSX();
        }

        function exportXLSX() {
            var table = document.getElementById("tableData");

            // Convert table to workbook
            var wb = XLSX.utils.table_to_book(table, {
                sheet: "Sheet1"
            });
            var ws = wb.Sheets["Sheet1"];

            // -----------------------------------------
            // 1. AUTO COLUMN WIDTHS
            // -----------------------------------------
            let range = XLSX.utils.decode_range(ws["!ref"]);
            let colWidths = [];

            for (let c = range.s.c; c <= range.e.c; c++) {
                let maxWidth = 10;

                for (let r = range.s.r; r <= range.e.r; r++) {
                    let cell = ws[XLSX.utils.encode_cell({
                        r: r,
                        c: c
                    })];
                    if (!cell) continue;

                    let cellValue = cell.v ? cell.v.toString() : "";
                    maxWidth = Math.max(maxWidth, cellValue.length + 3);
                }
                colWidths.push({
                    wch: maxWidth
                });
            }

            ws["!cols"] = colWidths;

            // -----------------------------------------
            // 2. HEADER FORMATTING
            // -----------------------------------------
            for (let c = range.s.c; c <= range.e.c; c++) {
                let cellAddress = XLSX.utils.encode_cell({
                    r: 0,
                    c: c
                });
                let cell = ws[cellAddress];

                if (cell) {
                    cell.s = {
                        font: {
                            bold: true
                        },
                        fill: {
                            fgColor: {
                                rgb: "D9D9D9"
                            }
                        },
                        alignment: {
                            horizontal: "center",
                            vertical: "center"
                        }
                    };
                }
            }

            // -----------------------------------------
            // 3. TOTAL ROW FORMATTING (MAKE BOLD)
            // -----------------------------------------
            let lastRow = range.e.r; // last row index

            for (let c = range.s.c; c <= range.e.c; c++) {
                let cellAddress = XLSX.utils.encode_cell({
                    r: lastRow,
                    c: c
                });
                let cell = ws[cellAddress];

                if (cell) {
                    cell.s = {
                        font: {
                            bold: true
                        }, // bold text
                        alignment: {
                            horizontal: "right"
                        } // right-align numbers
                    };
                }
            }

            // -----------------------------------------
            // 4. EXPORT AS XLSX
            // -----------------------------------------
            XLSX.writeFile(
                wb,
                "{{ session('month') }}_{{ session('year') }}_Justices_Mandate.xlsx"
            );
        }
    </script>

    <script>
        function lookup() {
            let amount = "{{ number_format($sum1, 2, '.', '') }}";
            let money = amount.split('.');
            let words = toWords(money[0]) + " naira";
            if (money[1] !== "00") {
                words += ", " + toWords(money[1]) + " kobo";
            }
            document.getElementById('result').innerHTML = words.toUpperCase();
        }
    </script>

</body>

</html>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>ISALU HOSPITAL LIMITED...::...Bank Mandate Schedule</title>
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
            justify-content: left;

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

        .header-title {
            color: #008000;
            font-weight: bold;
        }

        .total-row {
            border-top: 2px solid #000;
            font-weight: bold;
        }
    </style>

    <script src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>

<body onload="lookup()">

    <div class="container-fluid">
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
                            <h5 class="text-success"><strong>ISALU HOSPITAL LIMITED</strong></h5>
                            <h6 class="text-success"><strong>THREE ARM ZONE</strong></h6>
                            <h6 class="text-success">
                                <strong>ACCOUNT NUMBER: {{ $accountDetails->account_no ?? 'N/A' }}</strong>
                            </h6>
                            <h6 class="text-success"><strong>BANK MANDATE SCHEDULE - {{ strtoupper(str_replace('_', ' ', $bank)) }}</strong></h6>
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
                                @if($bank == 'CBN')
                                    CENTRAL BANK OF NIGERIA
                                @elseif($bank == 'COMMERCIAL')
                                    COMMERCIAL BANK
                                @elseif($bank == 'Micro_Finance')
                                    MICRO FINANCE BANK
                                @elseif($bank == 'NASARAWA')
                                    FIRST BANK PLC
                                @elseif($bank == 'NIGER')
                                    FIRST BANK PLC
                                @elseif($bank == 'UNION_DUES')
                                    FIRST BANK PLC
                                @else
                                    UBA PLC.
                                @endif
                            </strong>
                        </div>
                        <div class="col-xs-6 text-right print-head">
                            <strong>
                                <span style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                    Ref No.
                                </span>
                                SCN/MANDATE/{{ date('m/Y') }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- ================= TOTAL IN WORDS ================= -->
                @php
                    if($bank == 'CBN') {
                        $totalAmount = ($reportData->total_nhf ?? 0) + ($reportData->total_nsitf ?? 0) + ($reportData->total_tax ?? 0);
                    } elseif($bank == 'COMMERCIAL') {
                        $totalAmount = $commercialTotal ?? 0;
                    } elseif($bank == 'Micro_Finance') {
                        $totalAmount = $microfinanceTotal ?? 0;
                    } elseif($bank == 'NASARAWA') {
                        $totalAmount = $reportData->total_tax ?? 0;
                    } elseif($bank == 'NIGER') {
                        $totalAmount = $reportData->total_tax ?? 0;
                    } elseif($bank == 'UNION_DUES') {
                        $totalAmount = $reportData->UD ?? 0;
                    } else {
                        $totalAmount = 0;
                    }
                @endphp

                <div class="PleaseCredit" style="width:80%; margin:15px auto;">
                    Please credit the account(s) of the listed beneficiary(s) and debit our account with:
                    (₦) <strong>{{ number_format($totalAmount, 2) }}</strong>
                    <br>
                    <span id="result"></span>
                </div>

                <!-- ================= TABLE ================= -->
                @if ($bank == 'CBN')
                    <table class="table table-bordered" id="tableData">
                        <thead>
                            <tr class="tblborder print-head" style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                <th style="color:#fff !important;">Deduction</th>
                                <th style="color:#fff !important;">Amount (₦)</th>
                                <th style="color:#fff !important;">Beneficiary</th>
                                <th style="color:#fff !important;">Bank</th>
                                <th style="color:#fff !important;">Account No.</th>
                            </tr>
                        </thead>

                        <tbody>
                            {{-- NHF --}}
                            <tr class="tblborder">
                                <td>NHF</td>
                                <td align="right">{{ number_format($reportData->total_nhf ?? 0, 2) }}</td>
                                <td>FMBN FCT NHF</td>
                                <td>Central Bank of Nigeria</td>
                                <td>3000049535</td>
                            </tr>

                            {{-- NSITF --}}
                            <tr class="tblborder">
                                <td>NSITF</td>
                                <td align="right">{{ number_format($reportData->total_nsitf ?? 0, 2) }}</td>
                                <td>NSITF PAYMENT</td>
                                <td>Central Bank of Nigeria</td>
                                <td>90047547748</td>
                            </tr>

                            {{-- TAX --}}
                            <tr class="tblborder">
                                <td>TAX</td>
                                <td align="right">{{ number_format($reportData->total_tax ?? 0, 2) }}</td>
                                <td>FCT Internal Revenue Service</td>
                                <td>Central Bank of Nigeria</td>
                                <td>3000056483</td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="tblborder" style="border-top: 2px solid #000; font-weight: bold;">
                                <td><strong>Grand Total</strong></td>
                                <td align="right"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if ($bank == 'COMMERCIAL')
                    <table class="table table-bordered" id="tableData">
                        <thead>
                            <tr class="tblborder print-head" style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                <th style="color:#fff !important;">#</th>
                                <th style="color:#fff !important;">Staff Name</th>
                                <th style="color:#fff !important;">Bank</th>
                                <th style="color:#fff !important;">Amount (₦)</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($commercialRecords as $index => $row)
                                <tr class="tblborder">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->surname }} {{ $row->first_name }} {{ $row->othernames }}</td>
                                    <td>{{ $row->bank }}</td>
                                    <td align="right">{{ number_format($row->NetPay, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr class="tblborder" style="border-top: 2px solid #000; font-weight: bold;">
                                <td colspan="3"><strong>Total</strong></td>
                                <td align="right"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if ($bank == 'Micro_Finance')
                    <table class="table table-bordered" id="tableData">
                        <thead>
                            <tr class="tblborder print-head" style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                <th style="color:#fff !important;">#</th>
                                <th style="color:#fff !important;">Staff Name</th>
                                <th style="color:#fff !important;">Bank</th>
                                <th style="color:#fff !important;">Amount (₦)</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($microfinanceRecords as $index => $row)
                                <tr class="tblborder">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->surname }} {{ $row->first_name }} {{ $row->othernames }}</td>
                                    <td>{{ $row->bank }}</td>
                                    <td align="right">{{ number_format($row->NetPay, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr class="tblborder" style="border-top: 2px solid #000; font-weight: bold;">
                                <td colspan="3"><strong>Total</strong></td>
                                <td align="right"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if ($bank == 'NASARAWA')
                    <table class="table table-bordered" id="tableData">
                        <thead>
                            <tr class="tblborder print-head" style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                <th style="color:#fff !important;">Deduction</th>
                                <th style="color:#fff !important;">Amount (₦)</th>
                                <th style="color:#fff !important;">Beneficiary</th>
                                <th style="color:#fff !important;">Bank</th>
                                <th style="color:#fff !important;">Account No.</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="tblborder">
                                <td>TAX</td>
                                <td align="right">{{ number_format($reportData->total_tax ?? 0, 2) }}</td>
                                <td>Revenue Collection Nasarawa State Government</td>
                                <td>First Bank Plc</td>
                                <td>2005675850</td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="tblborder" style="border-top: 2px solid #000; font-weight: bold;">
                                <td><strong>Grand Total</strong></td>
                                <td align="right"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if ($bank == 'NIGER')
                    <table class="table table-bordered" id="tableData">
                        <thead>
                            <tr class="tblborder print-head" style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                <th style="color:#fff !important;">Deduction</th>
                                <th style="color:#fff !important;">Amount (₦)</th>
                                <th style="color:#fff !important;">Beneficiary</th>
                                <th style="color:#fff !important;">Bank</th>
                                <th style="color:#fff !important;">Account No.</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="tblborder">
                                <td>TAX</td>
                                <td align="right">{{ number_format($reportData->total_tax ?? 0, 2) }}</td>
                                <td>Niger State Board of Internal Revenue</td>
                                <td>First Bank Plc</td>
                                <td>2013787716</td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="tblborder" style="border-top: 2px solid #000; font-weight: bold;">
                                <td><strong>Grand Total</strong></td>
                                <td align="right"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if ($bank == 'UNION_DUES')
                    <table class="table table-bordered" id="tableData">
                        <thead>
                            <tr class="tblborder print-head" style="background: green; color:#fff !important; padding: 5px 5px !important;">
                                <th style="color:#fff !important;">Deduction</th>
                                <th style="color:#fff !important;">Amount (₦)</th>
                                <th style="color:#fff !important;">Beneficiary</th>
                                <th style="color:#fff !important;">Bank</th>
                                <th style="color:#fff !important;">Account No.</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="tblborder">
                                <td>UNION DUES</td>
                                <td align="right">{{ number_format($reportData->UD ?? 0, 2) }}</td>
                                <td>JUDICIAL STAFF UNION OF NIGERIA</td>
                                <td>First Bank Plc</td>
                                <td>2009822140</td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="tblborder" style="border-top: 2px solid #000; font-weight: bold;">
                                <td><strong>Grand Total</strong></td>
                                <td align="right"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                <!-- ================= BUTTONS ================= -->
                <div class="no-print text-center" style="margin:20px;">
                    @if (isset($reportData->vstage) && $reportData->vstage >= 5)
                        <button class="btn btn-primary" onclick="window.print()">Print</button>
                        <button class="btn btn-success btn-sm" id="btnExport" onclick="ExportToExcel('xlsx')">
                            Export to Excel
                        </button>
                    @endif
                    <a href="{{ url('/con-epayment') }}" class="btn btn-success">Back</a>
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

    <script type="text/javascript">
        function ExportToExcel() {
            var table = document.getElementById("tableData");

            // Convert table to workbook
            var wb = XLSX.utils.table_to_book(table, {
                sheet: "Sheet1"
            });

            var ws = wb.Sheets["Sheet1"];

            // -----------------------------------------
            // 1. AUTO COLUMN WIDTHS
            // -----------------------------------------
            let range = XLSX.utils.decode_range(ws['!ref']);
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
                    maxWidth = Math.max(maxWidth, cellValue.length + 2);
                }
                colWidths.push({
                    wch: maxWidth
                });
            }

            ws['!cols'] = colWidths;

            // -----------------------------------------
            // 2. FORMAT HEADER ROW (Bold + Gray BG)
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
                            } // Light gray
                        },
                        alignment: {
                            horizontal: "center",
                            vertical: "center"
                        }
                    };
                }
            }

            // -----------------------------------------
            // 3. EXPORT THE FILE
            // -----------------------------------------
            XLSX.writeFile(wb, "{{ session('month') }}_{{ session('year') }}_{{ $bank }}_Mandate.xlsx");
        }
    </script>

    <script>
        function lookup() {
            let amount = "{{ number_format($totalAmount, 2, '.', '') }}";
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
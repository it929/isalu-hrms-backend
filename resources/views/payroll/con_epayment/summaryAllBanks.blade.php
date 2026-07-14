<!DOCTYPE html>
<html>

<head>
    <title>ISALU HOSPITAL LIMITED...::...E-payment Schedule</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

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
    </style>
    {{-- Add CSS --}}
    <style>
        /* Highlight deduction rows */
        .deduction-row {
            background-color: #fce4ec;
            /* light pink */
            font-weight: bold;
            color: #c2185b;
        }

        /* Optional: different styling for staff salaries */
        .staff-row {
            background-color: #e3f2fd;
            /* light blue */
        }

        .bank-subtotal {
            background-color: #fff9c4;
            /* light yellow */
            font-weight: bold;
        }

        .grand-total {
            background-color: #c8e6c9;
            /* light green */
            font-weight: bold;
        }
    </style>

    <script src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>

<body onload="lookup()">

    <div class="container-fluid">

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
                    <h6 class="text-success"><strong>E-PAYMENT SCHEDULE</strong></h6>
                </div>

                <div class="col-xs-2 text-right">
                    <img src="{{ asset('Images/coat.png') }}" width="50">
                </div>
            </div>

            <br>

            <div class="row">
                <div class="col-xs-6">
                    <strong>
                        THE MANAGER<br>
                        UBA PLC.
                    </strong>
                </div>
                <div class="col-xs-6 text-right">
                    {{-- <strong>Ref No: SCN/SALPE/{{ date('m/Y') }}</strong> --}}
                    <strong>Ref No. SCN/SALPE/{{ $monthNumber }}/{{ date('Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- ================= TOTAL IN WORDS ================= -->
        @php
            $sum1 = $epayment_detail->sum('NetPay');
        @endphp

        <div class="PleaseCredit" style="width:80%; margin:15px auto;">
            Please credit the account(s) of the listed beneficiary(s) and debit our account with:
            <br>
            (₦) <strong>{{ number_format($sum1, 2) }}</strong>
            <br>
            <span id="result"></span>
        </div>

        <!-- ================= TABLE ================= -->
        {{-- <table class="table table-bordered" id="tableData">
            <thead>
                <tr class="tblborder">
                    <th>S/N</th>
                    <th>BENEFICIARY</th>
                    <th>BANK</th>
                    <th>BRANCH</th>
                    <th>ACC NUMBER</th>
                    <th>AMOUNT (₦)</th>
                    <th>PURPOSE OF PAYMENT</th>
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
                    @if ($bkID != '' && $bkID != $reports->bank)
                        <tr class="tblborder">
                            <td colspan="5"><strong>Sub Total</strong></td>
                            <td colspan="2"><strong>{{ number_format($subTotal, 2) }}</strong></td>
                        </tr>
                        @php $subTotal = 0; @endphp
                    @endif

                    @php
                        $bkID = $reports->bank;
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
                        <td>{{ session('month') }} {{ session('year') }} Staff Salary</td>
                    </tr>

                    @php $counter++; @endphp
                @endforeach

                <tr class="tblborder">
                    <td colspan="5"><strong>Total</strong></td>
                    <td align="right"><strong>{{ number_format($sum, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table> --}}
        <table class="table table-bordered" id="tableData">
            <thead>
                <tr class="tblborder">
                    <th>S/N</th>
                    <th>BENEFICIARY</th>
                    <th>BANK</th>
                    <th>BRANCH</th>
                    <th>ACC NUMBER</th>
                    <th>AMOUNT (₦)</th>
                    <th>PURPOSE OF PAYMENT</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $counter = session('serialNo', 1);
                    $sum = 0;
                    $salaryByBank = $epayment_detail->groupBy('bank');
                    $deductionByBank = $staffDeductionElement->groupBy('bank_name');
                @endphp

                {{-- Loop through each bank --}}
                @foreach ($salaryByBank as $bank => $salaries)
                    @php $subTotal = 0; @endphp

                    {{-- ===== Staff Salaries ===== --}}
                    @foreach ($salaries as $report)
                        @php
                            $subTotal += $report->NetPay;
                            $sum += $report->NetPay;
                        @endphp
                        <tr class="tblborder staff-row">
                            <td>{{ $counter }}</td>
                            <td>{{ $report->name }}</td>
                            <td>{{ $report->bank }}</td>
                            <td>{{ $report->bank_branch }}</td>
                            <td><span style="display:none;"></span>{{ $report->AccNo }}</td>
                            <td align="right">{{ number_format($report->NetPay, 2) }}</td>
                            <td>{{ session('month') }} {{ session('year') }} Staff Salary</td>
                        </tr>
                        @php $counter++; @endphp
                    @endforeach

                    {{-- ===== Deductions for the same bank ===== --}}
                    @if (isset($deductionByBank[$bank]))
                        @foreach ($deductionByBank[$bank] as $deduction)
                            @php
                                $subTotal += $deduction->totalDeduction;
                                $sum += $deduction->totalDeduction;
                            @endphp
                            <tr class="tblborder deduction-row">
                                <td>{{ $counter }}</td>
                                <td>{{ $deduction->beneficiary_name }}</td>
                                <td>{{ $deduction->bank_name }}</td>
                                <td>-</td>
                                <td><span style="display:none;"></span>{{ $deduction->account_number }}</td>
                                <td align="right">{{ number_format($deduction->totalDeduction, 2) }}</td>
                                <td>{{ session('month') }} {{ session('year') }} Deduction</td>
                            </tr>
                            @php $counter++; @endphp
                        @endforeach
                    @endif

                    {{-- ===== Bank Subtotal ===== --}}
                    <tr class="tblborder bank-subtotal">
                        <td colspan="5"><strong>Sub Total ({{ $bank }})</strong></td>
                        <td colspan="2"><strong>{{ number_format($subTotal, 2) }}</strong></td>
                    </tr>
                @endforeach

                {{-- ===== Grand Total ===== --}}
                <tr class="tblborder grand-total">
                    <td colspan="5"><strong>Total</strong></td>
                    <td align="right"><strong>{{ number_format($sum, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- ================= BUTTONS ================= -->
        <div class="no-print text-center" style="margin:20px;">
            {{-- @if ($epayment_detail[0]->vstage == 5) --}}
            <button class="btn btn-primary" onclick="window.print()">Print</button>
            <button class="btn btn-success btn-sm" id="btnExport" onclick="ExportToExcel('xlsx')">
                Export to Excel
            </button>
            {{-- @endif --}}
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

        <div class="authorizerSign">
            <table width="100%">
                <tr>
                    <td width="40%">
                        <strong>Authorized Signatory:</strong> {{ $sig1->Name }}<br><br>
                        Signature: ................................<br><br>
                        Thumb Print:
                        <div style="border:1px solid #000; width:80px; height:60px;"></div>
                    </td>

                    <td width="20%"></td>

                    <td width="40%">
                        <strong>Authorized Signatory:</strong> {{ $sig2->Name }}<br><br>
                        Signature: ................................<br><br>
                        Thumb Print:
                        <div style="border:1px solid #000; width:80px; height:60px;"></div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/table2excel.js') }}"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    {{-- <script>
        var $btnDLtoExcel = $('#DLtoExcel-2');
        $btnDLtoExcel.on('click', function() {
            $("#tableData").excelexportjs({
                containerid: "tableData",
                datatype: 'table'
            });

        });
    </script> --}}

    {{-- <script type="text/javascript">
        function ExportToExcel() {
            $("#tableData").table2excel({
                filename: "{{ session('month') }}_{{ session('year') }}_Mandate.xls"
            });
            $("#tableData").excelexportjs({
                containerid: "tableData",
                datatype: 'table'
            });
        }
    </script> --}}

    <script>
        $('#DLtoExcel-2').on('click', function() {
            exportXLSX();
        });

        function ExportToExcel() {
            exportXLSX();
        }
    </script>

    {{-- <script>
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
                let maxWidth = 10; // minimum width

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
            // Bold + Gray Fill + Center Text
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
                            bold: true,
                            color: {
                                rgb: "000000"
                            }
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
            // 3. EXPORT AS XLSX
            // -----------------------------------------
            XLSX.writeFile(
                wb,
                "{{ session('month') }}_{{ session('year') }}_Mandate.xlsx"
            );
        }
    </script> --}}

    <script>
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
                "{{ session('month') }}_{{ session('year') }}_Mandate.xlsx"
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

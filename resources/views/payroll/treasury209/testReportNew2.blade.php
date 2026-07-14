<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>ISALU HOSPITAL LIMITED...::...Report</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style type="text/css">
        .style25 {
            font-family: Verdana, Arial, Helvetica, sans-serif;
            color: #FF0000;
        }

        a:link {
            text-decoration: none;
        }

        a:visited {
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        a:active {
            text-decoration: none;
        }

        body {
            /*background-image: url({{ asset('Images/watermark.jpg') }});*/
            font-family: Verdana, Geneva, sans-serif;
            font-size: 14px;
            background-color: #f6f6f6;
        }

        .tblborder {
            border: 1px solid #000;
            border-collapse: collapse;
        }

        .FED {
            color: #008000;
        }

        table tr th {
            line-height: 30px;
            font-size: 14px;
            background-color: #f2f2f2;
            font-weight: bold;
            border: 1px solid #000;
            padding: 6px;
        }

        table tr td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 14px;
            background-color: #f2f2f2 !important;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .bg-light {
            background-color: #f2f2f2;
        }

        .pull-right {
            float: right;
        }

        .text-center {
            text-align: center;
        }



        /* .table-d,
        .table-d th,
        .table-d td {
            border: none !important;
            box-shadow: none !important;

        } */

        .cell-border {
            border: 1px solid #000 !important;
            padding: 6px;
        }

        .cell-no-border {
            border: none !important;
            padding: 6px;
        }

        div table td.cell-no-border,
        div table th.cell-no-border {
            border: none !important;
            background-color: #f2f2f2 !important;
        }

        /*
        .boxed-shadow {
            border: 1px solid #000;
            padding: 4px 8px;
            box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            padding: 4px 8px;
            border-radius: 6px;
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.45);
            display: inline-block;
            background: #fff;
        } */

        .boxed-shadow {
            border: 1px solid #222;
            padding: 6px 12px;
            box-shadow: 5px 5px 12px rgba(0, 0, 0, 0.55);
            display: inline-block;
            font-weight: bold;
        }

        .underline-words {
            /* font-weight: bold; */
            font-size: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
            display: inline-block;
            letter-spacing: 1px;
        }

        /* .line-field {
            width: 220px;
            height: 1px;
            background: #000;
            margin-top: 5px;
            margin-bottom: 5px;
            display: inline-block;

        } */

        /* .line-field {
            width: 220px;
            border-bottom: 2px solid #000;
            height: 12px;

            display: block;
        } */

        .sig-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .line-field {
            flex-grow: 1;
            border-bottom: 2px solid #000;
            height: 12px;
        }

        .note-box {
            margin: 10px 0;
            text-align: center;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .no-print {
            display: block;
        }


        /* Column header specific alignment - ALL LEFT ALIGNED */
        /* REDUCED WIDTHS for SN, File Number, GRADE, and STEP */
        /* INCREASED WIDTH for Name */
        .th-sn {
            text-align: center;
            width: 8%;
            line-height: 14px;
            /* Reduced from 4% */
        }

        .th-name {
            text-align: center;
            width: 40%;
            /* Increased from 52% */
        }

        .th-fileno {
            text-align: center;
            width: 12%;
            /* Reduced from 10% */
        }

        .th-grade {
            text-align: center;
            width: 15%;
            line-height: 14px;
            /* Reduced from 8% */
        }

        .th-step {
            text-align: center;
            width: 4%;
            /* Reduced from 8% */
        }

        .th-amount {
            text-align: center;
            width: 18%;
            /* Kept the same */
        }

        /* Data cell alignment - ALL LEFT ALIGNED except amount */
        .td-sn {
            text-align: left;
        }

        .td-name {
            text-align: left;
        }

        .td-fileno {
            text-align: left;
        }

        .td-grade {
            text-align: left;
        }

        .td-step {
            text-align: left;
        }

        .td-amount {
            text-align: right;
        }

        .heading-one {
            font-size: 16px !important;
            text-align: center !important;
            padding-left: 150px !important;
        }

        .pd-left-36 {
            padding-left: 36px;
        }

        .td-child {
            padding: 0px !important;
        }



        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                font-size: 12pt;
                margin: 0;
                padding: 10px;
            }

            .report-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse;
                font-size: 12pt;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 4px !important;
                font-size: 12pt;
            }

            /* Ensure tables don't break across pages */
            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            /* Adjusted widths for print */
            .th-sn,
            .td-sn {
                width: 8% !important;
            }

            .th-name,
            .td-name {
                width: 35% !important;
            }

            .th-fileno,
            .td-fileno {
                width: 25% !important;
            }

            .th-grade,
            .td-grade {
                width: 30% !important;
            }

            .th-step,
            .td-step {
                width: 4% !important;
            }

            .th-amount,
            .td-amount {
                width: 18% !important;
            }

            .heading-one th {
                font-size: 16px !important;
                text-align: center !important;
                padding-left: 150px !important;
            }

            .pd-left-36 {
                padding-left: 32px !important;
            }

            table.compact-table td {
                white-space: nowrap;
                font-size: 10px;
            }




            /* .line-field {
                width: 220px !important;
                height: 1px !important;
                background: #000 !important;
                margin-top: 5px !important;
                margin-bottom: 5px !important;
                display: inline-block !important;

            } */

            .sig-row {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                font-size: 10px !important;
            }

            .line-field {
                flex-grow: 1 !important;
                border-bottom: 2px solid #000 !important;
                height: 12px !important;
            }

            .td-child {
                padding: 0px !important;
            }

            .amount-in-word {
                font-size: 10px !important;
            }

            .underline-words {
                font-size: 7px !important;
            }

            .note-box {
                font-size: 12px !important;
            }

            .note-box-sub {
                font-size: 12px !important;
                padding-top: 0px !important;
                margin-top: -2px !important;
            }
        }

        .action-buttons {
            margin: 20px 0;
            text-align: center;
        }

        .action-buttons button {
            margin: 0 10px;
            padding: 10px 25px;
            font-size: 16px;
        }

        .report-container {
            width: 95%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 10px;
        }

        .compact-table {
            width: 100%;
            table-layout: fixed;
        }

        .name-column {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>




    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <!-- SheetJS library for Excel export -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>



    <script src="{{ asset('assets/js/number_to_word.js') }}"></script>
    <script type="text/javascript">
        function Export() {
            try {
                /* Create workbook from scratch (not from HTML table) */
                var wb = XLSX.utils.book_new();

                // Create worksheet data array
                var ws_data = [];

                // Add headers with proper formatting
                ws_data.push(["ISALU HOSPITAL LIMITED"]);
                ws_data.push(["ADVICE OF DEDUCTION FROM SALARY"]);

                @if (isset($reportType) && $reportType == 'TAX')
                    ws_data.push([
                        "{{ $payeeCaption->address }} DEDUCTION FOR THE MONTH OF {{ strtoupper($selectedMonth) }}, {{ strtoupper($selectedYear) }}{{ isset($divisionName) ? ' - ' . strtoupper($divisionName) : '' }}"
                    ]);
                @else
                    ws_data.push([
                        "{{ strtoupper($reportType->addressName ?? '') }} DEDUCTION FOR THE MONTH OF {{ strtoupper($selectedMonth) }}, {{ strtoupper($selectedYear) }}{{ isset($divisionName) ? ' - ' . strtoupper($divisionName) : '' }}"
                    ]);
                @endif

                // Empty row for spacing
                ws_data.push([]);

                // Column headers - left aligned
                ws_data.push(["SN", "Name", "File Number", "GRADE", "STEP", "8%"]);

                // Add data rows
                @if (isset($payment) && count($payment) > 0)
                    @php
                        $k = 1;
                        $totalCont = 0;
                    @endphp

                    @foreach ($payment as $list)
                        @php
                            $totalCont += $list->Vpara;
                        @endphp
                        ws_data.push([
                            {{ $k++ }},
                            "{{ $list->surname }} {{ $list->first_name }} {{ $list->othernames }}",
                            "{{ $list->fileNo ?? 'N/A' }}",
                            "{{ $list->grade }}",
                            "{{ $list->step }}",
                            {{ number_format($list->Vpara, 2, '.', '') }}
                        ]);
                    @endforeach

                    // Add total row
                    ws_data.push([]);
                    ws_data.push(["TOTAL", "", "", "", "", {{ number_format($totalCont, 2, '.', '') }}]);
                @else
                    ws_data.push(["No data found for the selected criteria"]);
                @endif

                // Convert array to worksheet
                var ws = XLSX.utils.aoa_to_sheet(ws_data);

                // Set column widths (ADJUSTED FOR NEW LAYOUT)
                var wscols = [{
                        wch: 3
                    }, // SN (reduced)
                    {
                        wch: 50
                    }, // Name (increased)
                    {
                        wch: 8
                    }, // File Number (reduced)
                    {
                        wch: 5
                    }, // GRADE (reduced)
                    {
                        wch: 5
                    }, // STEP (reduced)
                    {
                        wch: 12
                    }, // 8%
                ];
                ws['!cols'] = wscols;

                // Merge cells for headers (rows 0-2, columns A-F)
                if (!ws['!merges']) ws['!merges'] = [];
                ws['!merges'].push({
                    s: {
                        r: 0,
                        c: 0
                    },
                    e: {
                        r: 0,
                        c: 5
                    }
                }); // Row 1 (SUPREME COURT...)
                ws['!merges'].push({
                    s: {
                        r: 1,
                        c: 0
                    },
                    e: {
                        r: 1,
                        c: 5
                    }
                }); // Row 2 (ADVICE OF DEDUCTION...)
                ws['!merges'].push({
                    s: {
                        r: 2,
                        c: 0
                    },
                    e: {
                        r: 2,
                        c: 5
                    }
                }); // Row 3 (DEDUCTION FOR THE MONTH...)

                // Apply styling to ALL cells
                var range = XLSX.utils.decode_range(ws['!ref']);
                for (var R = range.s.r; R <= range.e.r; ++R) {
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell_address = {
                            c: C,
                            r: R
                        };
                        var cell_ref = XLSX.utils.encode_cell(cell_address);

                        if (!ws[cell_ref]) continue;

                        // Initialize style object if not exists
                        if (!ws[cell_ref].s) ws[cell_ref].s = {};

                        // Apply styles based on row
                        if (R <= 2) {
                            // Header rows - CENTER ALIGNED, bold, larger font
                            ws[cell_ref].s.alignment = {
                                horizontal: "center",
                                vertical: "center",
                                wrapText: true
                            };
                            ws[cell_ref].s.font = {
                                bold: true,
                                sz: R === 0 ? 16 : (R === 1 ? 14 : 12)
                            };
                        } else if (R === 4) {
                            // Column headers - LEFT ALIGNED, bold
                            ws[cell_ref].s.alignment = {
                                horizontal: "left",
                                vertical: "center"
                            };
                            ws[cell_ref].s.font = {
                                bold: true
                            };
                            ws[cell_ref].s.fill = {
                                fgColor: {
                                    rgb: "F2F2F2"
                                }
                            };
                        } else if (C === 5 && R > 4) {
                            // Amount column - right aligned, number format
                            ws[cell_ref].s.alignment = {
                                horizontal: "right",
                                vertical: "center"
                            };
                            ws[cell_ref].s.numFmt = "#,##0.00";
                        } else if (R > 4) {
                            // Data rows - ALL LEFT ALIGNED except amount column
                            if (C < 5) {
                                // SN, Name, File Number, GRADE, STEP - LEFT ALIGNED
                                ws[cell_ref].s.alignment = {
                                    horizontal: "left",
                                    vertical: "center"
                                };
                            }
                        }
                    }
                }

                // Style the total row
                var totalRow = ws_data.length - 1;
                for (var C = 0; C <= 5; C++) {
                    var cell_ref = XLSX.utils.encode_cell({
                        r: totalRow,
                        c: C
                    });
                    if (ws[cell_ref]) {
                        if (!ws[cell_ref].s) ws[cell_ref].s = {};
                        ws[cell_ref].s.font = {
                            bold: true
                        };
                        ws[cell_ref].s.alignment = {
                            horizontal: "left",
                            vertical: "center"
                        };

                        if (C === 5) {
                            ws[cell_ref].s.numFmt = "#,##0.00";
                            ws[cell_ref].s.alignment = {
                                horizontal: "right",
                                vertical: "center"
                            };
                        }
                    }
                }

                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(wb, ws, "Treasury209 Report");

                // Generate filename
                var filename = "Treasury209_{{ $selectedMonth }}_{{ $selectedYear }}.xlsx";

                // Save the file
                XLSX.writeFile(wb, filename);

            } catch (error) {
                console.error("Error exporting to Excel:", error);
                alert("Error exporting to Excel. Please try again or contact support.");
            }
        }

        function PrintReport() {
            window.print();
        }
    </script>
</head>



<body onload="lookup();">
    <div class="report-container">
        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <button class="btn btn-primary" onclick="Export()">
                Export to Excel
            </button>
            <button class="btn btn-info" onclick="PrintReport()">
                Print Report
            </button>
            <a class="btn btn-success" href="{{ URL::previous() }}">
                Go Back
            </a>
        </div>

        <!-- Report Table -->
        <table class=" compact-table table-d" cellpadding="0" cellspacing="0">
            <tr>
                <th colspan="2" class="cell-no-border" style="font-size: 16px; padding: 8px;">Min/Dept.</th>
                <th colspan="3" class="cell-no-border" class="text-center" style="font-size: 16px; padding: 8px;">
                    ISALU HOSPITAL LIMITED
                </th>
                <th colspan="1" class="cell-no-border" style="font-size: 14px; padding: 5px;"> <span
                        class="pull-right">
                        <small>TF 209 (1962E)</small>
                    </span></th>
            </tr>
            <tr>
                <td colspan="6" class="cell-no-border" style="background-color: #f2f2f2; padding: 5px;">Dept. PV
                    {{ $selectedMonth }}
                    {{ $selectedYear }}</td>
            </tr>

            <tr>

                <th colspan="5" class=" text-center cell-no-border heading-one">
                    NIGERIA FEDERAL GOVERNMENT
                    <br>ADVICE OF DEDUCTION FROM
                    SALARY
                </th>
                {{-- <th colspan="5" class="text-center" style="font-size: 16px; padding: 8px;">ADVICE OF DEDUCTION FROM
                    SALARY</th> --}}
                <th class="text-center cell-border">P.V. NO</th>
            </tr>
            <tr>
                <td colspan="1" class="cell-no-border"></td>
                <td colspan="5" class="td-child">
                    <table style="width: 100%">
                        <tr>

                            <td class="pd-left-36" style="width: 33.3%">Ref: Only <br><br> P.V. NO</td>
                            <td class="pd-left-36" style="width: 33.3%">Station <br><br> ABUJA</td>
                            <td class="pd-left-36 text-center" style="width: 33.3%">Month/Year
                                <br><br>{{ strtoupper($selectedMonth) }}
                                {{ $selectedYear }}
                            </td>

                        </tr>
                    </table>
                </td>
                {{-- <td colspan="2" class="pd-left-36">Ref: Only <br><br> P.V. NO</td>
                <td colspan="2" class="pd-left-36">Station <br><br> ABUJA</td>
                <td colspan="1" class="pd-left-36">Month/Year <br><br>{{ strtoupper($selectedMonth) }}
                    {{ $selectedYear }}</td> --}}
            </tr>
            <tr>
                <td colspan="1" class="cell-no-border"></td>
                <td colspan="3" class="cell-no-border">Details of Recovery</td>
                <td colspan="1" class="text-center" style="padding: 24px">HEAD <br>27</td>
                <td colspan="1" class="text-center" style="padding: 24px">SUB-HEAD <br> 1</td>
            </tr>
            {{-- <tr>

                <td colspan="1" class="cell-no-border" style="padding: 8px">A. Expenditure Credits</td>
                <td colspan="1" class="cell-no-border">B. Revenue</td>
                <td colspan="2" class="cell-no-border">C. Below the line Accounts</td>

                <td colspan="2" style="padding: 24px">
                    AMOUNT <br> &#8358;{{ number_format($totalCont, 2, '.', ',') }}
                </td>

            </tr> --}}

            <tr>
                <td colspan="1" class="cell-no-border" style="padding: 8px; width: 20%; white-space: nowrap;">
                    A. Expenditure Credits
                </td>
                <td colspan="1" class="cell-no-border text-center" style="width: 20%; white-space: nowrap;">
                    B. Revenue
                </td>
                <td colspan="2" class="cell-no-border" style="width: 30%; white-space: nowrap;">
                    C. Below the line Accounts
                </td>
                <td colspan="2" class="text-center" style="padding: 24px; width: 30%; white-space: nowrap;">
                    AMOUNT <br> &#8358;{{ number_format($totalCont, 2, '.', ',') }}
                </td>
            </tr>





        </table>


        <div class="info-box bg-light mt-20">
            <h6>
                <span class="boxed-shadow">{{ strtoupper($Tr2019Head ?? '') }}</span>
                <span class="boxed-shadow pull-right">{{ $banklist->bank ?? 'N/A' }}</span>
            </h6>
            <h6 class="note-box">NOTE: DELETE AS APPLICABLE. ONLY ONE CLASSIFICATION PER ONE ADVANCE FORM</h6>
            <p class="text-center note-box-sub">The amount shown has been deducted from the salary of official as shown
                below</p>

            <!-- Payroll Table -->
            <table class="compact-table" cellpadding="0" cellspacing="0" id="tableData">
                <tr>
                    <th class="th-sn cell-no-border">Payroll No</th>
                    <th class="th-fileno cell-no-border">Month / Year</th>
                    <th class="th-name cell-no-border">Name</th>
                    <th class="th-grade cell-no-border">Ledger Folio <br> Brought Forward</th>
                    <th class="th-amount cell-no-border">Amount</th>
                </tr>
                @php
                    $k = 1;
                    $totalCont = 0;
                @endphp
                @if (isset($payment) && count($payment) > 0)
                    @foreach ($payment as $list)
                        <tr>
                            <td class="td-sn">{{ $k++ }}</td>
                            <td class="td-fileno">{{ strtoupper($selectedMonth) }} {{ $selectedYear }}</td>
                            <td class="td-name name-column">{{ $list->surname }} {{ $list->first_name }}
                                {{ $list->othernames }}</td>
                            <td class="td-grade"></td>
                            <td class="td-amount">
                                @php $totalCont += $list->Vpara; @endphp
                                {{ number_format($list->Vpara, 2, '.', ',') }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="cell-no-border"></td>
                        <td class="cell-no-border"></td>
                        <td class="cell-no-border"></td>
                        <td class="text-center bold cell-no-border">TOTAL</td>
                        <td class="td-amount bold cell-no-border">&#8358;{{ number_format($totalCont, 2, '.', ',') }}
                        </td>
                    </tr>
                    <tr>
                     <td colspan="5" class="text-center cell-no-border">ORIGINAL</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 15px;">No data found for the selected
                            criteria</td>
                    </tr>
                @endif
            </table>
        </div>




        <!-- Amount in Words & Signatures -->
        @if (isset($payment) && count($payment) > 0)
            <table width="100%" cellpadding="6" cellspacing="0" class="mt-20">
                <tr>
                    <td colspan="4" class="cell-no-border">
                        <strong class="amount-in-word">AMOUNT IN WORDS:</strong>
                        <script type="text/javascript">
                            var amount = "";
                            var amount = "{{ number_format($totalCont, 2, '.', ',') }}";
                            var money = amount.split('.');

                            function lookup() {
                                var words;
                                var naira = money[0];
                                var kobo = money[1];

                                var word1 = toWords(naira) + " naira";
                                var word2 = ", " + toWords(kobo) + " kobo";
                                if (kobo != "00")
                                    words = word1 + word2;
                                else
                                    words = word1;
                                document.getElementById('result').innerHTML = words.toUpperCase();
                            }
                        </script>
                        <span id="result" class="underline-words"></span>
                    </td>
                </tr>



                <tr>
                    <td colspan="4" class="cell-no-border">
                        <table width="100%" style="border: none; font-size: 14px;">

                            <!-- Row 1 -->
                            <tr>
                                <td class="cell-no-border" width="50%">
                                    <div class="sig-row">
                                        <strong>Signature of Paying-in Officer:</strong>
                                        <div class="line-field"></div>
                                    </div>
                                </td>

                                <td class="cell-no-border" width="50%">
                                    <div class="sig-row">
                                        <strong>Signature of Receiving Officer:</strong>
                                        <div class="line-field"></div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr>
                                <td class="cell-no-border" width="50%">
                                    <div class="sig-row">
                                        <strong>Name:</strong>
                                        <div class="line-field"></div>
                                    </div>
                                </td>

                                <td class="cell-no-border" width="50%">
                                    <div class="sig-row">
                                        <strong>Name:</strong>
                                        <div class="line-field"></div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Block Letters -->
                            <tr>
                                <td class="cell-no-border">(BLOCK LETTERS)</td>
                                <td class="cell-no-border" style="text-align: right;">(BLOCK LETTERS)</td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        @endif

        <!-- Footer Action Buttons -->
        <div class="action-buttons no-print" style="margin-top: 25px;">
            <button class="btn btn-primary" onclick="Export()">
                Export to Excel
            </button>
            <button class="btn btn-info" onclick="PrintReport()">
                Print Report
            </button>
            <a class="btn btn-success" href="{{ URL::previous() }}">
                Go Back
            </a>
        </div>

        <br /><br />
    </div>

    <script type="text/javascript">
        // Prevent right-click context menu
        $(function() {
            $(this).bind("contextmenu", function(e) {
                e.preventDefault();
            });
        });

        // Simple fallback export if the main one fails
        function SimpleExport() {
            try {
                var table = document.getElementById('tableData');
                var wb = XLSX.utils.book_new();
                var ws = XLSX.utils.table_to_sheet(table);
                XLSX.utils.book_append_sheet(wb, ws, "Report");
                XLSX.writeFile(wb, "Treasury209_Simple_{{ $selectedMonth }}_{{ $selectedYear }}.xlsx");
            } catch (e) {
                alert("Error creating Excel file: " + e.message);
            }
        }
    </script>
</body>


</html>

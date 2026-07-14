<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>ISALU HOSPITAL LIMITED...::...Report</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style type="text/css">
        <!--
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
            text-align: left;
            width: 3%; /* Reduced from 4% */
        }
        .th-name {
            text-align: left;
            width: 65%; /* Increased from 52% */
        }
        .th-fileno {
            text-align: left;
            width: 6%; /* Reduced from 10% */
        }
        .th-grade {
            text-align: left;
            width: 4%; /* Reduced from 8% */
        }
        .th-step {
            text-align: left;
            width: 4%; /* Reduced from 8% */
        }
        .th-amount {
            text-align: left;
            width: 18%; /* Kept the same */
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
            
            th, td {
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
            .th-sn, .td-sn {
                width: 3% !important;
            }
            .th-name, .td-name {
                width: 65% !important;
            }
            .th-fileno, .td-fileno {
                width: 6% !important;
            }
            .th-grade, .td-grade {
                width: 4% !important;
            }
            .th-step, .td-step {
                width: 4% !important;
            }
            .th-amount, .td-amount {
                width: 18% !important;
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
        -->
    </style>

    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <!-- SheetJS library for Excel export -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
    
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
                ws_data.push(["{{ $payeeCaption->address }} DEDUCTION FOR THE MONTH OF {{ strtoupper($selectedMonth) }}, {{ strtoupper($selectedYear) }}{{ isset($divisionName) ? ' - ' . strtoupper($divisionName) : '' }}"]);
            @else
                ws_data.push(["{{ strtoupper($reportType->addressName ?? '') }} DEDUCTION FOR THE MONTH OF {{ strtoupper($selectedMonth) }}, {{ strtoupper($selectedYear) }}{{ isset($divisionName) ? ' - ' . strtoupper($divisionName) : '' }}"]);
            @endif
            
            // Empty row for spacing
            ws_data.push([]);
            
            // Column headers - left aligned
            ws_data.push(["SN", "Name", "File Number", "GRADE", "STEP", "8%"]);
            
            // Add data rows
            @if(isset($payment) && count($payment) > 0)
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
            var wscols = [
                {wch: 3},   // SN (reduced)
                {wch: 50},  // Name (increased)
                {wch: 8},   // File Number (reduced)
                {wch: 5},   // GRADE (reduced)
                {wch: 5},   // STEP (reduced)
                {wch: 12},  // 8%
            ];
            ws['!cols'] = wscols;
            
            // Merge cells for headers (rows 0-2, columns A-F)
            if (!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: 5}}); // Row 1 (SUPREME COURT...)
            ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: 5}}); // Row 2 (ADVICE OF DEDUCTION...)
            ws['!merges'].push({s: {r: 2, c: 0}, e: {r: 2, c: 5}}); // Row 3 (DEDUCTION FOR THE MONTH...)
            
            // Apply styling to ALL cells
            var range = XLSX.utils.decode_range(ws['!ref']);
            for (var R = range.s.r; R <= range.e.r; ++R) {
                for (var C = range.s.c; C <= range.e.c; ++C) {
                    var cell_address = {c: C, r: R};
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
                        ws[cell_ref].s.font = { bold: true };
                        ws[cell_ref].s.fill = { 
                            fgColor: { rgb: "F2F2F2" } 
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
                var cell_ref = XLSX.utils.encode_cell({r: totalRow, c: C});
                if (ws[cell_ref]) {
                    if (!ws[cell_ref].s) ws[cell_ref].s = {};
                    ws[cell_ref].s.font = { bold: true };
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

    <script src="{{ asset('assets/js/number_to_word.js') }}"></script>
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
        <table class="tblborder compact-table" border="1" cellpadding="0" cellspacing="0" id="tableData">
            <tr>
                <th colspan="6" class="text-center" style="font-size: 16px; padding: 8px;">ISALU HOSPITAL LIMITED</th>
            </tr>
            <tr>
                <th colspan="6" class="text-center" style="font-size: 16px; padding: 8px;">ADVICE OF DEDUCTION FROM SALARY</th>
            </tr>
            
            @if (isset($reportType) && $reportType == 'TAX')
                <tr>
                    <th colspan="6" class="text-center" style="font-size: 14px; padding: 8px;">
                        {{ $payeeCaption->address }} DEDUCTION FOR THE MONTH OF
                        {{ strtoupper($selectedMonth) }}, {{ strtoupper($selectedYear) }}
                        {{ isset($divisionName) ? ' - ' . strtoupper($divisionName) : '' }}
                    </th>
                </tr>
            @else
                <tr>
                    <th colspan="6" class="text-center" style="font-size: 14px; padding: 8px;">
                        {{ strtoupper($reportType->addressName ?? '') }} DEDUCTION FOR
                        THE MONTH OF {{ strtoupper($selectedMonth) }}, {{ strtoupper($selectedYear) }}
                        {{ isset($divisionName) ? ' - ' . strtoupper($divisionName) : '' }}
                    </th>
                </tr>
            @endif
            
            <tr>
                <th class="th-sn">SN</th>
                <th class="th-name">Name</th>
                <th class="th-fileno">File Number</th>
                <th class="th-grade">GRADE</th>
                <th class="th-step">STEP</th>
                <th class="th-amount">8%</th>
            </tr>

            @php
                $k = 1;
                $totalCont = 0;
            @endphp
            
            @if(isset($payment) && count($payment) > 0)
                @foreach ($payment as $list)
                    <tr>
                        <td class="td-sn">{{ $k++ }}</td>
                        <td class="td-name name-column">{{ $list->surname }} {{ $list->first_name }} {{ $list->othernames }}</td>
                        <td class="td-fileno">{{ $list->fileNo ?? 'N/A' }}</td>
                        <td class="td-grade">{{ $list->grade }}</td>
                        <td class="td-step">{{ $list->step }}</td>
                        <td class="td-amount">
                            @php
                                $totalCont += $list->Vpara;
                            @endphp
                            {{ number_format($list->Vpara, 2, '.', ',') }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px;">No data found for the selected criteria</td>
                </tr>
            @endif
            
            @if(isset($payment) && count($payment) > 0)
                <tr>
                    <td class="td-sn bold" colspan="5"><strong>TOTAL</strong></td>
                    <td class="td-amount" style="font-weight: bold;">
                        {{ number_format($totalCont, 2, '.', ',') }}
                    </td>
                </tr>
            @endif
        </table>
        
        @if(isset($payment) && count($payment) > 0)
            <!-- Summary Section -->
            <div style="margin-top: 25px; width: 100%;">
                <table width="100%" border="0" cellpadding="6" cellspacing="0">
                    <tr>
                        <td colspan="4" valign="top">
                            <div align="left"><strong>AMOUNT IN WORDS:</strong>
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
                                <span id="result" style="font-weight: bold;"></span>
                                <br />
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="margin-top:25px; padding-top: 40px;" colspan="4" valign="top">
                            Name:...........................................................................................Signature..........................................................................................
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" valign="top">(BLOCK LETTERS)</td>
                        <td width="636" valign="top">(BLOCK LETTERS)</td>
                    </tr>
                </table>
            </div>
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
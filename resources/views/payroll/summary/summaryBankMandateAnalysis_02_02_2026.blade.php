<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>ISALU HOSPITAL LIMITED</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style type="text/css">
        body,
        td,
        th {
            font-size: 15px;
            font-family: Verdana, Geneva, sans-serif;
            margin: 15px;
        }

        .tables tr td,
        .tables {
            padding: 6px;
            border: 1px solid #333;
        }

        @media print {

            .no-print,
            .no-print * {
                display: none !important;
            }
        }

        body {
            background-image: {{ asset('Images/nicn_bg.jpg') }};
        }

        .style2 {
            color: #008000
        }
    </style>
    <style type="text/css">
        .head-color tr td,
        .table .th-row td {
            //color:#06c;

        }

        .table,
        .table tr td {
            //border: 1px solid #06C;
            //color:#06c;
        }
    </style>
    <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}"></script>

<body background="" onload="lookup(); gross();">
    <div align="center"><strong><span class="style2">
                <h3><br />
                    <br />
                    <br />
                    ISALU HOSPITAL LIMITED
                </h3>
                @if ($bank == 'CBN')
                    <h4>CENTRAL BANK OF NIGERIA DEDUCTION PAYMENT MANDATE</h4>
                @endif
                @if ($bank == 'COMMERCIAL')
                    <h4>COMMERCIAL BANK DEDUCTION PAYMENT MANDATE</h4>
                @endif
                @if ($bank == 'Micro_Finance')
                    <h4>MICRO FINANCE BANK DEDUCTION PAYMENT MANDATE</h4>
                @endif

            </span>
        </strong></div>
    <br />


    <div align="left"><strong>

        </strong></div>
    <div align="right" class="col-md-8 col-md-offset-2">

        @if ($bank == 'CBN')
            <table class="table table-condense table-responsive" width="1586" border="1" align="center"
                cellpadding="0" cellspacing="0" id="tableData">
                <thead>
                    <tr style="border-bottom: 2px solid #000;">
                        <th align="left">Deduction</th>
                        <th align="right">Amount (₦)</th>
                        <th align="left">Beneficiary</th>
                        <th align="left">Bank</th>
                        <th align="left">Account No.</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- NHF --}}
                    <tr>
                        <td>NHF</td>
                        <td align="right">{{ number_format($reportData->total_nhf ?? 0, 2) }}</td>
                        <td>FMBN FCT NHF</td>
                        <td>Central Bank of Nigeria</td>
                        <td>3000049535</td>
                    </tr>

                    {{-- NSITF --}}
                    <tr>
                        <td>NSITF</td>
                        <td align="right">{{ number_format($reportData->total_nsitf ?? 0, 2) }}</td>
                        <td>NSITF PAYMENT</td>
                        <td>Central Bank of Nigeria</td>
                        <td>90047547748</td>
                    </tr>

                    {{-- TAX --}}
                    <tr>
                        <td>TAX</td>
                        <td align="right">{{ number_format($reportData->total_tax ?? 0, 2) }}</td>
                        <td>FCT Internal Revenue Service</td>
                        <td>Central Bank of Nigeria</td>
                        <td>3000056483</td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td>Grand Total</td>
                        <td align="right">
                            {{ number_format(
                                ($reportData->total_nhf ?? 0) + ($reportData->total_nsitf ?? 0) + ($reportData->total_tax ?? 0),
                                2,
                            ) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>

                <tr border="0" class="no-print">
                    <td colspan="17">
                        <div class="no-print" align="center">
                            <input type="button" class="hidden-print" id="btnExport" value="Export to Excel"
                                onclick="ExportToExcel('xlsx')" />
                        </div>
                    </td>
                </tr>
            </table>
        @endif

        @if ($bank == 'COMMERCIAL')
            <table class="table table-condense table-responsive" border="1" width="100%" cellspacing="0"
                cellpadding="6">
                <thead>
                    <tr style="border-bottom: 2px solid #000;">
                        <th>#</th>
                        <th>Staff Name</th>
                        <th>Bank</th>
                        <th align="right">Amount (₦)</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($commercialRecords as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $row->surname }}
                                {{ $row->first_name }}
                                {{ $row->othernames }}
                            </td>
                            <td>{{ $row->bank }}</td>
                            <td align="right">{{ number_format($row->NetPay, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td colspan="3">Total</td>
                        <td align="right">{{ number_format($commercialTotal ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        @if ($bank == 'Micro_Finance')
            <table class="table table-condense table-responsive" border="1" width="100%" cellspacing="0"
                cellpadding="6">
                <thead>
                    <tr style="border-bottom: 2px solid #000;">
                        <th>#</th>
                        <th>Staff Name</th>
                        <th>Bank</th>
                        <th align="right">Amount (₦)</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($microfinanceRecords as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $row->surname }}
                                {{ $row->first_name }}
                                {{ $row->othernames }}
                            </td>
                            <td>{{ $row->bank }}</td>
                            <td align="right">{{ number_format($row->NetPay, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td colspan="3">Total</td>
                        <td align="right">{{ number_format($microfinanceTotal ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        @if ($bank == 'NASARAWA')
            <table class="table table-condense table-responsive" width="1586" border="1" align="center"
                cellpadding="0" cellspacing="0" id="tableData">
                <thead>
                    <tr style="border-bottom: 2px solid #000;">
                        <th align="left">Deduction</th>
                        <th align="right">Amount (₦)</th>
                        <th align="left">Beneficiary</th>
                        <th align="left">Bank</th>
                        <th align="left">Account No.</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>TAX</td>
                        <td align="right">{{ number_format($reportData->total_tax ?? 0, 2) }}</td>
                        <td>Revenue Collection Nasarawa State Government</td>
                        <td>First Bank Plc</td>
                        <td>2005675850</td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td>Grand Total</td>
                        <td align="right">
                            {{ number_format(
                                ($reportData->total_nhf ?? 0) + ($reportData->total_nsitf ?? 0) + ($reportData->total_tax ?? 0),
                                2,
                            ) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>

                <tr border="0" class="no-print">
                    <td colspan="17">
                        <div class="no-print" align="center">
                            <input type="button" class="hidden-print" id="btnExport" value="Export to Excel"
                                onclick="ExportToExcel('xlsx')" />
                        </div>
                    </td>
                </tr>
            </table>
        @endif
        @if ($bank == 'NIGER')
            <table class="table table-condense table-responsive" width="1586" border="1" align="center"
                cellpadding="0" cellspacing="0" id="tableData">
                <thead>
                    <tr style="border-bottom: 2px solid #000;">
                        <th align="left">Deduction</th>
                        <th align="right">Amount (₦)</th>
                        <th align="left">Beneficiary</th>
                        <th align="left">Bank</th>
                        <th align="left">Account No.</th>
                    </tr>
                </thead>

                <tbody>

                    {{-- TAX --}}
                    <tr>
                        <td>TAX</td>
                        <td align="right">{{ number_format($reportData->total_tax ?? 0, 2) }}</td>
                        <td>Niger State Board of Internal Revenue</td>
                        <td>First Bank Plc</td>
                        <td>2013787716</td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td>Grand Total</td>
                        <td align="right">
                            {{ number_format(
                                ($reportData->total_nhf ?? 0) + ($reportData->total_nsitf ?? 0) + ($reportData->total_tax ?? 0),
                                2,
                            ) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>

                <tr border="0" class="no-print">
                    <td colspan="17">
                        <div class="no-print" align="center">
                            <input type="button" class="hidden-print" id="btnExport" value="Export to Excel"
                                onclick="ExportToExcel('xlsx')" />
                        </div>
                    </td>
                </tr>
            </table>
        @endif

        <p>&nbsp;</p>
    </div>


    <div>
        <h2>
            <a class="no-print" type="submit" class="btn btn-success btn-sm pull-right"
                href="{{ url('scn-payroll/summary/bank-mandate') }}">Back</a>
        </h2>
    </div>

    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/table2excel.js') }}"></script>

    <script type="text/javascript">
        function ExportToExcel() {
            //$("#btnExport").hide();
            $("#tableData").table2excel({
                filename: "{{ session('month') }}_{{ session('year') }}CBN_Mandate.xls"
            });
            $("#tableData").excelexportjs({
                containerid: "tableData",
                datatype: 'table'
            });
            //$("#btnExport").show();
        }
    </script>

</body>

</html>

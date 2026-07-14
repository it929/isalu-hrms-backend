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

<body background="{{ asset('Images/nicn_bg.jpg') }}" onload="lookup(); gross();">
    <div align="center"><strong><span class="style2">
                <h3><br />
                    <br />
                    <br />
                    ISALU HOSPITAL LIMITED
                </h3>
                @if ($mydivision)
                    <h4>PAYROLL SUMMARY AND BANK ANALYSIS FOR STAFF</h4>
                    <h4>DIVISION:-{{ strtoupper($mydivision) }}, {{ $month }} {{ $year }}</h4>
                @else
                    <h4>PAYROLL SUMMARY AND BANK ANALYSIS FOR STAFF</h4>
                    <h4>DIVISION:-ALL, {{ $month }} {{ $year }}</h4>
                @endif

            </span>
        </strong></div>
    <br />


    @php
        $bstotal = 0.0;
        $grosstotal = 0.0;
        $jusutotal = 0;
        $taxtotal = 0;
        $pentotal = 0;
        $nhftotal = 0;
        $otherearntotal = 0;
        $earntotal = 0;
        $deducttotal = 0;
        $netpaytotal = 0;
        $uniontotal = 0;
        $totalNetEmolu = 0;
        $totalAllowance = 0;
        $cooptotal = 0;
        $hstotal = 0;
        $saladvtotal = 0;
        $stafftotal = 0;
        $Earntotal = 0;
        $totalAdv = 0;
        $totalRefunds = 0;
        $alihsantotal = 0;
    @endphp

    <div align="left"><strong>

        </strong></div>
    <div align="right">

        <table class="table table-condense table-responsive" width="1586" border="1" align="center"
            cellpadding="0" cellspacing="0" id="tableData">
            <tr>
                <td><strong>SN</strong></td>
                <td><strong>PV No</strong></td>
                <td><strong>PAYEE</strong></td>
                <td><strong>NO OF STAFF</strong></td>
                <td><strong>BANK</strong></td>
                <td><strong>GROSS SALARY</strong></td>
                <td><strong> DEDUCTION </strong></td>
                <td><strong> AMOUNT </strong></td>
            </tr>
            {{-- @php $sn = 1; @endphp
                @foreach ($group as $list)

                @php

                    $basic = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('BS');
                    $netpay = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('NetPay');
                    $totdeduct = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('TD');
                    $jusu = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('PEC');
                    $pension = DB::table('tblpayment_consolidated')->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('PEN');
                    $dues = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('UD');
                    $tax = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('TAX');
                    $nhf = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('NHF');
                    $totAllowance = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('OEarn');
                    $totArr = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('OEarn');
                    $totalEarn = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('TEarn');
                    $coop = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('OD');
                    $totalStaff = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->count();

                    $coop1 = DB::table('tblotherEarningDeduction')
                    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.month','=',$month)
                    ->where('tblotherEarningDeduction.year','=',$year)
                    ->where('tblpayment_consolidated.month','=',$month)
                    ->where('tblpayment_consolidated.year','=',$year)
                    ->where('tblotherEarningDeduction.CVID','=',15)
                    ->where('tblpayment_consolidated.bank', '=',$list->bankid)
                    ->sum('tblotherEarningDeduction.amount');

                    $coop2 = DB::table('tblotherEarningDeduction')
                    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.month','=',$month)
                    ->where('tblotherEarningDeduction.year','=',$year)
                    ->where('tblpayment_consolidated.month','=',$month)
                    ->where('tblpayment_consolidated.year','=',$year)
                    ->where('tblotherEarningDeduction.CVID','=',16)
                    ->where('tblpayment_consolidated.bank', '=',$list->bankid)
                    ->sum('tblotherEarningDeduction.amount');

                    $salAdv = DB::table('tblotherEarningDeduction')
                    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.month','=',$month)
                    ->where('tblotherEarningDeduction.year','=',$year)
                    ->where('tblpayment_consolidated.month','=',$month)
                    ->where('tblpayment_consolidated.year','=',$year)
                    ->where('tblotherEarningDeduction.CVID','=',18)
                    ->where('tblpayment_consolidated.bank', '=',$list->bankid)
                    ->sum('tblotherEarningDeduction.amount');

                    $refunds = DB::table('tblotherEarningDeduction')
                    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.month','=',$month)
                    ->where('tblotherEarningDeduction.year','=',$year)
                    ->where('tblpayment_consolidated.month','=',$month)
                    ->where('tblpayment_consolidated.year','=',$year)
                    ->where('tblotherEarningDeduction.CVID','=',2)
                    ->where('tblpayment_consolidated.bank', '=',$list->bankid)
                    ->sum('tblotherEarningDeduction.amount');

                    $volPen = DB::table('tblotherEarningDeduction')
                    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.month','=',$month)
                    ->where('tblotherEarningDeduction.year','=',$year)
                    ->where('tblpayment_consolidated.month','=',$month)
                    ->where('tblpayment_consolidated.year','=',$year)
                    ->where('tblotherEarningDeduction.CVID','=',27)
                    ->where('tblpayment_consolidated.bank', '=',$list->bankid)
                    ->sum('tblotherEarningDeduction.amount');

                    $alihsan1 = DB::table('tblotherEarningDeduction')
                    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.month','=',$month)
                    ->where('tblotherEarningDeduction.year','=',$year)
                    ->where('tblpayment_consolidated.month','=',$month)
                    ->where('tblpayment_consolidated.year','=',$year)
                    ->where('tblotherEarningDeduction.CVID','=',31)
                    ->where('tblpayment_consolidated.bank', '=',$list->bankid)
                    ->sum('tblotherEarningDeduction.amount');

                    $alihsan2 = DB::table('tblotherEarningDeduction')
                    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.month','=',$month)
                    ->where('tblotherEarningDeduction.year','=',$year)
                    ->where('tblpayment_consolidated.month','=',$month)
                    ->where('tblpayment_consolidated.year','=',$year)
                    ->where('tblotherEarningDeduction.CVID','=',33)
                    ->where('tblpayment_consolidated.bank', '=',$list->bankid)
                    ->sum('tblotherEarningDeduction.amount');

                @endphp --}}

            @if (isset($allBanks) && $allBanks)
                @foreach ($allBanks as $key => $eachBank)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td></td>

                        @if ($staffInBank[$eachBank->bank] == 1)
                            <td>{{ $staffName[$eachBank->bank]->name }}
                            </td>
                        @else
                            <td>{{ $staffName[$eachBank->bank]->name }} & {{ $staffInBank[$eachBank->bank] - 1 }}
                                OTHERS </td>
                        @endif

                        <td><?php $stafftotal += $staffInBank[$eachBank->bank]; ?>{{ $staffInBank[$eachBank->bank] }}</td>
                        <td>{{ $eachBank->bank_name }}</td>

                        <td>
                            <?php $Earntotal += $variableElement[$eachBank->bank]->totalNetPay + $variableElement[$eachBank->bank]->totalTD; ?>
                            {{ number_format($variableElement[$eachBank->bank]->totalNetPay + $variableElement[$eachBank->bank]->totalTD, 2) }}
                        </td>
                        <td><?php $deducttotal += $variableElement[$eachBank->bank]->totalTD; ?>{{ number_format($variableElement[$eachBank->bank]->totalTD, 2) }}</td>
                        <td><?php $netpaytotal += $variableElement[$eachBank->bank]->totalNetPay; ?>{{ number_format($variableElement[$eachBank->bank]->totalNetPay, 2) }}
                        </td>
                    </tr>

                    {{-- <td>?php //$totalAllowance += $totAllowance;?>{{number_format((($netpay+$totdeduct)-$basic),2)}}</td>
                    <td>?php //$Earntotal += $totalEarn;?>{{number_format(($netpay+$totdeduct),2)}}</td>
                    <td>?php //$taxtotal += $tax;?>{{number_format($tax,2)}}</td>
                    <td>?php //$nhftotal += $nhf;?>{{number_format($nhf,2)}}</td>
                    <td>?php //$uniontotal += $dues;?>{{number_format($dues,2)}}</td>
                    <td>?php //$pentotal += $pension + $volPen;?>{{number_format($pension + $volPen,2)}}</td>
                    <td>?php //$cooptotal += $coop2 + $coop1;?> {{number_format($coop2 + $coop1 + $volPen,2)}} </td>
                    <td>?php //$alihsantotal += $alihsan2 + $alihsan1;?> {{number_format($alihsan1 + $alihsan2,2)}} </td>
                    <td>?php //$totalAdv += $salAdv;?> {{number_format($salAdv,2)}}</td>
                    <td >?php //$totalRefunds += $refunds;?> {{number_format($refunds,2)}}</td>
                    <td>?php //$deducttotal += $totdeduct;?>{{number_format($totdeduct,2)}}</td>
                    <td>?php //$netpaytotal += $netpay;?>{{number_format($netpay,2)}}</td> --}}
                @endforeach
            @endif

            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>{{ $stafftotal }}</strong></td>
                <td><strong></strong></td>
                <td><strong>{{ number_format($netpaytotal + $deducttotal, 2) }}</strong></td>
                <td><strong>{{ number_format($deducttotal, 2) }}</strong></td>
                <td><strong>{{ number_format($netpaytotal, 2) }}</strong></td>
            </tr>

            {{-- <tr border="0" class="no-print">
                <td colspan="7"></td>
            </tr> --}}

            {{-- <tr>
                <td width="150" colspan="4"> <strong> Prepared By</strong></td>
                <td width="450" colspan="3">&nbsp;</td>
            </tr>
            <tr>
                <td width="150" colspan="4"> <strong> Checked By</strong></td>
                <td width="450" colspan="3">&nbsp;</td>
            </tr>
            <tr>
                <td width="150" colspan="4"> <strong> Audited By</strong></td>
                <td width="450" colspan="3">&nbsp;</td>
            </tr> --}}

            <tr border="0" class="no-print">
                <td colspan="17">
                    <div class="no-print" align="center">
                        <input type="button" class="hidden-print" id="btnExport" value="Export to Excel"
                            onclick="ExportToExcel('xlsx')" />
                    </div>
                </td>
            </tr>

        </table>

        <p>&nbsp;</p>
    </div>




    <div>
        <h2>
            <a class="no-print" type="submit" class="btn btn-success btn-sm pull-right"
                href="{{ url('/payroll/summary/analysis') }}">Back</a>
        </h2>
    </div>

    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/table2excel.js') }}"></script>

    <script type="text/javascript">
        function ExportToExcel() {
            //$("#btnExport").hide();
            $("#tableData").table2excel({
                filename: "{{ session('month') }}_{{ session('year') }}_Mandate.xls"
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

<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>ISALU HOSPITAL LIMITED PAYROLL
        ...::...Payroll Report</title>



    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style type="text/css">
        .head-color tr td,
        .table .th-row td {
            color: #06c;
        }

        .table,
        .table tr td {
            border: 1px solid #06C;
            color: #06c;
        }

        .pr {
            display: none;
        }
        @page { size: A2 landscape; margin: 6mm; }
        .print-only { display: none; }
        @media print {
            .table tr .bg {
                background: #0cf !important;
                opacity: 0.8 !important;
                color: #FFF !important;
            }
            html, body { width: 594mm; height: 420mm; -webkit-print-color-adjust: exact; }
            .print-header { page-break-inside: avoid; break-inside: avoid; page-break-after: avoid; }
            .no-print { display: none !important; }
            .print-only { display: table-cell !important; }
            .head-color, .head-color table { width: 100% !important; max-width: 100% !important; table-layout: fixed; }
            table { word-wrap: break-word; }

            .marginTableHeading{
                margin-top: 100px !important;
                padding-top: 100px !important;
            }
        }
    </style>
    <style media="print">
        .pr {
            display: block;
        }
    </style>
    <style>
        /* highlight and shake animation */
        .highlight-row {
            background-color: #ffb3b3 !important;
            animation: shake 0.4s ease-in-out 0s 2;
        }

        @keyframes shake {
            0% {
                transform: translateX(0px);
            }

            25% {
                transform: translateX(-5px);
            }

            50% {
                transform: translateX(5px);
            }

            75% {
                transform: translateX(-5px);
            }

            100% {
                transform: translateX(0px);
            }
        }
    </style>
    <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>

<body>

    <div class="row">
        <div class="col-md-12" style="font-size:20px;"><!--1st col-->

            {{-- @media print {
                .table tr .bg {
                    background: #0cf !important;
                    opacity: 0.8 !important;
                    color: #FFF !important;
                }
            } --}}
            </style>
            <style media="print">
                .pr {
                    display: block;
                }
            </style>
            <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}"></script>
            <style>
                /* A2 print sizing and safer defaults for large payroll sheet */
                @page {
                    size: A2 landscape;
                    margin: 10mm;
                }

                @media print {

                    html,
                    body {
                        width: 594mm;
                        /* A2 landscape width */
                        height: 420mm;
                    }

                    /* Ensure printing hides UI controls */
                    .hidden-print {
                        display: none !important;
                    }

                    /* Prevent rows from breaking across pages */
                    /* .table tr, .table tbody, .table thead {
                    page-break-inside: avoid !important;
                    page-break-before: avoid !important;
                } */

                    .print-header {
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }

                    /* Payroll table MUST start immediately after header */
                    .payroll-table {
                        page-break-before: avoid !important;
                    }

                    thead {
                        display: table-header-group;
                    }

                    tfoot {
                        display: table-footer-group;
                    }

                    tr {
                        page-break-inside: avoid;
                    }

                    /* Allow the payroll 'pr' spans to show in print */
                    .pr {
                        display: inline !important;
                    }

                    /* Improve table density for large format */
                    .table,
                    .table td,
                    .table th {
                        font-size: 16px;
                    }
                }

                /* Non-print UI: show print button and keep normal layout */
                .print-btn {
                    margin-left: 10px;
                }
            </style>
            </head>

            <body>

                <div class="row">
                    <div class="col-md-12" style="font-size:20px;"><!--1st col-->

                        @if (session('message'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">&times;</span>
                                </button>
                                <strong>Success!</strong>
                                {{ session('message') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">&times;</span>
                                </button>
                                <strong>Error!</strong>
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>
                </div>
        </div>


        <div style="width:90%; margin: auto;">
            <div align="center" class="marginTableHeading">
                <h2>
                    <div style="color:#06c;">ISALU HOSPITAL LIMITED PAYROLL</div>
                </h2>
            </div>
            <table class="head-color" width="100%" border="0" cellpadding="0" cellspacing="0"
                style="font-size:18px">
                <span id="proccessingRequest" style="display:none; font-size:20px;" class="text-success">
                    <strong>Processing Please
                        wait...</strong> </span>
                <tr>
                    <td>Payroll P.V. No:</td>
                    <td>
                        <div align="left">Sheet No:</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h3>
                            {{-- MINISTRY/DEPARTMENT: --}}
                            {{ isset($courtName) ? strtoupper($courtName) : '' }}
                            {{-- {{ isset($divisionName) ? ', ' . strtoupper($divisionName) . ' DIVISION, ' : '' }} --}}
                            {{-- NIGERIA --}}
                        </h3>
                    </td>
                </tr>
                <tr>
                    <td width="1294">
                        <strong>MONTH ENDING: @if (session('schmonth'))
                                {{ session('schmonth') }}
                            @endif
                        </strong>
                        <br />
                    </td>
                    <td width="508" align="rights">Date Printed: {{ date('l, F d, Y') }}</td>
                </tr>
                <tr>
                    <td><strong>

                            @if (session('bank'))
                                {{ session('bank') }}
                            @endif

                        </strong> </td>
                    <td>&nbsp;</td>
                </tr>
            </table>

            <table class="table-condense table-responsive table" border="1" cellpadding="4" cellspacing="0">
                <tr class="th-row">
                    <td width="44" rowspan="2" align="center" valign="middle"><strong>SN</strong></td>
                    <td width="44" rowspan="2" align="center" valign="middle"><strong>FILE NO</strong></td>
                    <td class="no-print" width="44" rowspan="2" align="center" style="writing-mode: vertical-rl;">
                        <strong>CHECKING</strong>
                    </td>
                    <td class="no-print" width="44" rowspan="2" align="center" style="writing-mode: vertical-rl;">
                        <strong>AUDIT</strong>
                    </td>
                    <td width="271" rowspan="2" align="center" valign="middle"><strong>NAME</strong></td>
                    <td width="23" rowspan="2" align="center" valign="middle"><strong>GL</strong></td>
                    <td width="23" rowspan="2" align="center" valign="middle"><strong>ST</strong></td>
                    <td class="no-print" width="271" rowspan="2" align="center" valign="middle"><strong>Employment Type</strong>
                    </td>
                    <td colspan="{{ isset($staffEarnElement) ? count($staffEarnElement) + 5 : 5 }}" align="center"
                        valign="top">
                        <strong>EARNINGS</strong>
                    </td>
                    <td colspan="{{ isset($staffDeductionElement) ? count($staffDeductionElement) + 4 : 4 }}"
                        align="center" valign="top"><strong>DEDUCTIONS</strong></td>
                    <td width="70" rowspan="2" align="center" valign="middle"><strong>TOTAL DEDUCTION.</strong>
                    </td>
                    {{-- <td width="70" rowspan="2" align="center" valign="middle"><strong>NET BASIC <br />
                            SALARY</strong></td> --}}
                    <td width="75" rowspan="2" align="center" valign ="middle"><strong> TOTAL NET <BR />
                            EMOLUMENT </strong>
                    </td>
                </tr>

                <tr>
                    <td width="90" align="center" valign="middle"><strong>BASIC <br /> (CONSOLIDATED)</strong>
                    </td>
                    <td width="38" align="center" valign="middle"><strong>TOTAL <BR />ARREARS <BR />
                            EARNING</strong></td>
                    <td width="75" align="center" valign ="middle"><strong> PECULIAR </strong></td>
                    <td width="75" align="center" valign ="middle"><strong> 40% PECULIAR FG
                        </strong></td>
                    @if (isset($staffEarnElement) && $staffEarnElement)
                        @foreach ($staffEarnElement as $elementEarn)
                            <th align="center"><strong>{{ strtoupper($elementEarn->description) }}</strong></th>
                            @php $totalEarnAmount[$elementEarn->CVID] = 0.0; @endphp
                        @endforeach
                    @endif
                    <td width="80" align="center" valign="middle"><strong>GROSS<br /> EMOLUMENT</strong></td>
                    <td width="74" align="center" valign="middle"><strong>TAX</strong></td>
                    <td width="50" align="center" valign="middle"><strong>PENSION</strong></td>
                    <td width="50" align="center" valign="middle"><strong>UNION DUES</strong></td>
                    <td width="85" align="center" valign="middle"><strong>NHF</strong></td>
                    @if (isset($staffDeductionElement) && $staffDeductionElement)
                        @foreach ($staffDeductionElement as $elementDeduct)
                            <th align="center"><strong>{{ strtoupper($elementDeduct->description) }}</strong></th>
                            @php $totalDeductAmount[$elementDeduct->CVID] = 0.0; @endphp
                        @endforeach
                    @endif
                </tr>

                @php
                    $bstotal = 0.0;
                    $hatotal = 0.0;
                    $trtotal = 0;
                    $furtotal = 0;
                    $taxtotal = 0;
                    $pectotal = 0;
                    $pectotalFG = 0;
                    $utitotal = 0;
                    $drtotal = 0;
                    $sertotal = 0;
                    $e_arrearstotal = 0;
                    $e_otherstotal = 0;
                    $pentotal = 0;
                    $nhftotal = 0;
                    $d_arrearstotal = 0;
                    $d_othertotal = 0;
                    $earntotal = 0;
                    $deducttotal = 0;
                    $netpaytotal = 0;
                    $uniontotal = 0;
                    $totalNetEmolu = 0;
                    $totalSot = 0;
                    $k = 1;
                    $medAllowanceTotal = 0;
                    $cooptotal = 0;
                    $salAdvancetotal = 0;
                    $coopLoantotal = 0;
                    $coopSavingtotal = 0;
                @endphp

                @foreach ($payroll_detail as $reports)
                    @php
                        $fileNo = str_replace('/', '-', $reports->fileNo);
                    @endphp
                    <tr @if ($reports->NetPay <= 0) style="background-color:#ffb3b3;" @endif>
                        <td align="right">{{ $k++ }}</td>
                        <td align="right">{{ $reports->fileNo }}</td>
                        <td class="no-print" align="right">
                            @if ($reports->vstage == 3 && $userRole->can_check == 1)
                                @if ($reports->checking_verified == 1)
                                    {{-- <span class="text-success"><strong> ok </strong></span> --}}
                                    <input type="checkbox" checked="checked" name="unCheckChecking"
                                        id="unCheckChecking" data-staffId={{ $reports->staffid }}
                                        data-month={{ $reports->month }} data-yr={{ $reports->year }} />
                                @else
                                    <input type="checkbox" name="checkingChecked" id="checkingChecked"
                                        data-staffId={{ $reports->staffid }} data-month={{ $reports->month }}
                                        data-yr={{ $reports->year }}>
                                @endif
                            @elseif($reports->checking_verified == 1)
                                <span class="text-danger"><strong> ok </strong></span>
                            @endif

                        </td>
                        <td class="no-print">
                            @if ($reports->vstage == 4 && $userRole->can_audit == 1)
                                @if ($reports->audit_verified == 1)
                                    {{-- <span class="text-warning"><strong> ok </strong></span> --}}
                                    <input type="checkbox" checked="checked" name="auditUnChecked"
                                        id="auditUnChecked" data-staffId={{ $reports->staffid }}
                                        data-month={{ $reports->month }} data-yr={{ $reports->year }} />
                                @else
                                    <input type="checkbox" name="auditChecked" id="auditChecked"
                                        data-staffId={{ $reports->staffid }} data-month={{ $reports->month }}
                                        data-yr={{ $reports->year }}>
                                @endif
                            @elseif ($reports->audit_verified == 1)
                                <span class="text-warning"><strong> ok </strong></span>
                            @endif
                        </td>
                        <td align="left" valign="middle" nowrap="nowrap"><a class="hidden-print" target ="_blank"
                                href="{{ url("/con-pecard/getCard/$reports->staffid/$reports->year") }}">{{ $reports->name }}</a>
                            <span class="pr">{{ $reports->name }}</span>
                        </td>
                        <td width="23" align="center" valign="middle">
                            @if ($reports->employment_type == 6)
                            @else
                                {{ $reports->grade }}
                            @endif
                        </td>
                        <td width="23" align="center" valign="middle">
                            @if ($reports->employment_type == 6)
                                @else{{ $reports->step }}
                            @endif
                        </td>
                        <td class="no-print" width="271" align="center" valign="middle">
                            @if ($reports->employment_type == 6)
                                CONSOLIDATED
                            @else
                                {{ $reports->employmentType }}
                            @endif
                        </td>

                        <td width="75" align="right"><?php $bstotal += $reports->Bs; ?>
                            {{ number_format($reports->Bs, 2, '.', ',') }}</td>
                        @if ($reports->AEarn == '')
                            <td width="66" align="right">
                                <?php $e_arrearstotal += $reports->AEarn; ?>{{ number_format($reports->AEarn, 2, '.', ',') }}</td>
                        @else
                            <td width="66" align="right">
                                <?php $e_arrearstotal += $reports->AEarn; ?><a class="hidden-print"
                                    href="{{ url("/con-payrollReport/arrears/$reports->courtID/$reports->staffid/$reports->year/$reports->month") }}"
                                    target="_blank">{{ number_format($reports->AEarn, 2, '.', ',') }}</a><span
                                    class="pr">{{ number_format($reports->AEarn, 2, '.', ',') }}</span></td>
                        @endif

                        <td width="82" align="right" valign="middle">
                            <?php $pectotal += $reports->PEC; ?>
                            {{ number_format($reports->PEC, 2, '.', ',') }}
                        </td>
                        <td width="82" align="right" valign="middle">
                            <?php $pectotalFG += $reports->PECFG; ?>
                            {{ number_format($reports->PECFG, 2, '.', ',') }}
                        </td>

                        @php
                            $sumEarnAmount = 0.0;
                            $sumDeductAmount = 0.0;
                            $sumEarnGross = 0;
                        @endphp
                        @foreach ($staffEarnElement as $element)
                            @php

                                $earningResult = $allEarnDeduction->filter(function ($item) use ($reports, $element) {
                                    return $item->staffid == $reports->staffid && $item->CVID == $element->CVID;
                                });
                                $getEarnAmount = $earningResult->sum('amount');
                                $sumEarnGross += $getEarnAmount;

                                $totalEarnAmount[$element->CVID] += $getEarnAmount;
                            @endphp
                            <td width="66" align="right">{{ number_format($getEarnAmount, 2, '.', ',') }}</td>
                        @endforeach

                        <td width="80" align="right" class="bg" style="background:#0cf; opacity:0.8">
                            <?php $earntotal += $reports->Bs + $reports->AEarn + $sumEarnGross + $reports->PEC + $reports->PECFG; ?>
                            <strong>
                                {{ number_format($reports->Bs + $reports->AEarn + $sumEarnGross + $reports->PEC + $reports->PECFG, 2, '.', ',') }}
                            </strong>
                        </td>
                        <td width="52" align="right"><?php $taxtotal += $reports->TAX; ?>
                            {{ number_format($reports->TAX, 2, '.', ',') }}</td>
                        <td width="74" align="right"><?php $pentotal += $reports->PEN; ?>
                            {{ number_format($reports->PEN, 2, '.', ',') }}</td>
                        <td width="74" align="right"><?php $uniontotal += $reports->UD; ?>
                            {{ number_format($reports->UD, 2, '.', ',') }}</td>
                        <td width="50" align="right"><?php $nhftotal += $reports->NHF; ?>
                            {{ number_format($reports->NHF, 2, '.', ',') }}</td>
                        @foreach ($staffDeductionElement as $elementDec)
                            @php
                                $deductionResult = $allEarnDeduction->filter(function ($item) use (
                                    $reports,
                                    $elementDec,
                                ) {
                                    return $item->staffid == $reports->staffid && $item->CVID == $elementDec->CVID;
                                });
                                $getDeductAmount = $deductionResult->sum('amount');

                                $totalDeductAmount[$elementDec->CVID] += $getDeductAmount;
                            @endphp
                            <td width="85" align="right">{{ number_format($getDeductAmount, 2, '.', ',') }}</td>
                        @endforeach
                        <td width="82" align="right" class="bg" style="background:#0cf;opacity:0.8;">
                            <?php $deducttotal += $reports->TD; ?>
                            <strong> {{ number_format($reports->TD, 2, '.', ',') }}</strong>
                        </td>
                        {{-- <td width="82" align="right">
                            <?php $netpaytotal += $reports->NetPay - ($reports->PEC + $reports->PECFG); ?>
                            {{ number_format($reports->NetPay - ($reports->PEC + $reports->PECFG), 2, '.', ',') }}
                        </td> --}}
                        <td class="netpay" width="82" align="right"><?php $totalNetEmolu += $reports->NetPay; ?>
                            {{ number_format($reports->NetPay, 2, '.', ',') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td class="no-print" colspan="8" align="right"><strong>TOTAL</strong></td>
                    <td class="print-only" colspan="5" align="right"><strong>TOTAL</strong></td>

                    <td align="right"><strong>{{ number_format($bstotal, 2, '.', ',') }}</strong></td>
                    <td align="right"><strong>{{ number_format($e_arrearstotal, 2, '.', ',') }} </strong></td>
                    <td align="right"><strong>{{ number_format($pectotal, 2, '.', ',') }}</strong></td>
                    <td align="right"><strong>{{ number_format($pectotalFG, 2, '.', ',') }}</strong></td>
                    @foreach ($staffEarnElement as $element)
                        <td width="66" align="right">
                            {{ number_format($totalEarnAmount[$element->CVID], 2, '.', ',') }}</td>
                    @endforeach
                    <td align="right"><strong>{{ number_format($earntotal, 2, '.', ',') }}</strong></td>
                    {{-- <td align="right"><strong>{{ number_format($e_otherstotal, 2, '.', ',') }}</strong></td> --}}

                    <td align="right"><strong>{{ number_format($taxtotal, 2, '.', ',') }}</strong></td>
                    <td align="right"><strong>{{ number_format($pentotal, 2, '.', ',') }} </strong></td>
                    <td align="right"><strong>{{ number_format($uniontotal, 2, '.', ',') }} </strong></td>

                    <td align="right"><strong>{{ number_format($nhftotal, 2, '.', ',') }}</strong></td>
                    @foreach ($staffDeductionElement as $elementDec)
                        <td width="85" align="right">
                            {{ number_format($totalDeductAmount[$elementDec->CVID], 2, '.', ',') }}</td>
                    @endforeach
                    <td align="right"><strong>{{ number_format($deducttotal, 2, '.', ',') }}</strong></td>
                    {{-- <td align="right"><strong>{{ number_format($netpaytotal, 2, '.', ',') }}</strong></td> --}}
                    <td align="right"><strong>{{ number_format($totalNetEmolu, 2, '.', ',') }}</strong></td>

                </tr>
            </table>

            <div class="pull-right">
                <p>------------------------------20-------------------------------------------</p>
                <p class="text-right" style="margin-right:50px;">SIGNATURE</p>
                <br />
                <p>---------------------------------------------------------------------------</p>
                <p class="text-center">PAYING OFFICER STAMP</p>
            </div>

            <br>
            <div style="margin-left:30px;">
                {{-- <h2 class="hidden-print">
                    <a class="hidden-print btn btn-success btn-sm" href="{{ URL::previous() }}">Back</a>
                    <button type="button" class="btn btn-primary btn-sm print-btn hidden-print"
                        onclick="window.print();">Print</button>
                </h2> --}}

                <h2 class="hidden-print">
                    <a class="hidden-print btn btn-success btn-sm" href="{{ URL::previous() }}">Back</a>

                    @if (in_array($loggedRole, [1, 25, 35]))
                        <button type="button" class="btn btn-primary btn-sm print-btn hidden-print"
                            onclick="window.print();">
                            Print
                        </button>
                    @endif
                </h2>



                @if (!empty($payroll_detail) && isset($payroll_detail[0]))
                    {{-- <a href="/payroll-comments/{{ $payroll_detail[0]->divisionID }}/{{ $payroll_detail[0]->year }}/{{ $payroll_detail[0]->month }}"
                    class="btn btn-primary" style="margin-bottom: 10px;" type="button">
                    View Comment's
                </a> --}}

                    {{-- <a href="{{ route('payroll.comments', [
                'division' => $payroll_detail[0]->divisionID,
                'year' => $payroll_detail[0]->year,
                'month' => $payroll_detail[0]->month,
            ]) }}"
                class="btn btn-primary" style="margin-bottom: 10px;" type="button">
                View Comment's
            </a> --}}

                    <form action="{{ route('payroll.comments') }}" method="GET">
                        <input type="hidden" name="division" value="{{ $payroll_detail[0]->divisionID }}">
                        <input type="hidden" name="year" value="{{ $payroll_detail[0]->year }}">
                        <input type="hidden" name="month" value="{{ $payroll_detail[0]->month }}">
                        <button class="btn btn-primary mb-3" type="submit">View Comments</button>
                    </form>
                @else
                    <p>No payroll details available.</p>
                @endif





                <div>
                    <input type="hidden" value="{{ $bankName }}" id="selectedBankName">

                    <div id="selBankName">
                        @if ($payroll_detail)
                            @if ($payroll_detail[0]->vstage == 1 && $userRole->can_submit_salary == 1)
                                <div class="row">
                                    <div class="pull-left">
                                        <form action="{{ url('/submit-salary') }}" method="post">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="division"
                                                value="{{ $payroll_detail[0]->divisionID }}">
                                            <input type="hidden" name="year"
                                                value="{{ $payroll_detail[0]->year }}">
                                            <input type="hidden" name="month"
                                                value="{{ $payroll_detail[0]->month }}">
                                            <button type="submit" class="btn btn-success"> Submit Salary </button>
                                        </form>
                                    </div>
                                </div>
                            @elseif ($payroll_detail[0]->vstage == 2 && $userRole->can_authorize_salary == 1)
                                @include('payroll.payrollReport_con.includeSalary')
                            @elseif($payroll_detail[0]->vstage == 3 && $userRole->can_check == 1)
                                @include('payroll.payrollReport_con.includeChecking')
                            @elseif($payroll_detail[0]->vstage == 4 && $userRole->can_audit == 1)
                                @include('payroll.payrollReport_con.includeAudit')
                            @elseif($payroll_detail[0]->vstage == 5 && $userRole->can_cpo == 1)
                                @include('payroll.payrollReport_con.includeCpo')
                            @endif
                        @else
                            {{ 'No record' }}
                        @endif
                    </div>
                </div>

            </div>

            <div id="commentsModal" class="modal fade">
                <div class="modal-dialog box box-default" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Comments on this salary</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div style="background-color: lightblue; height: 150px; overflow: scroll;">
                                @php $key = 1 @endphp
                                @if (count($allcomments) > 0)
                                    @foreach ($allcomments as $key => $listComment)
                                        <div align="left" class="col-xs-12">
                                            {{ $key + 1 }}. &nbsp;
                                            {{ $listComment->name . ' - ' . $listComment->comment }} <br> Created Date:
                                            <i class="text-info"> {{ $listComment->updated_at }} </i>
                                            <hr style="margin: 1px 0px; solid #000!important; " />
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-xs-12 text-danger" align="center"> No comment found! </div>
                                @endif

                            </div>

                        </div>

                    </div>
                </div>
            </div>

</body>

<script src="{{ asset('/assets/js/jQuery-2.2.0.min.js') }}"></script>
<script src="{{ asset('/assets/js/bootstrap.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var a = $('#selectedBankName').val()
        if (a !== "") {
            $("#selBankName").hide()
        }
        //set selected bank to zero because it prenvents jquery check box if you don't select bank
        var a = 0

        if (a = 0) {
            //for checking to check
            $('#checkingChecked').each(function() {
                $('input[name="checkingChecked"]').click(function() {
                    console.log($(this).attr('data-staffId'))
                    console.log($(this).attr('data-month'))
                    console.log($(this).attr('data-yr'))
                    console.log("clicked checkbox 1")
                    $('#proccessingRequest').show()
                    $.ajax({
                        type: "post",
                        url: "/checking/verified",
                        data: {
                            'checked': 1,
                            'staffId': $(this).attr('data-staffId'),
                            'month': $(this).attr('data-month'),
                            'yr': $(this).attr('data-yr'),
                            '_token': $('input[name=_token]').val()
                        },
                        dataType: "json",
                        success: function(response) {
                            console.log(response)
                            // location.reload(true)
                            $('#proccessingRequest').hide()
                        }
                    });
                })

            })

            //for checking to uncheck
            $('#unCheckChecking').each(function() {
                $('input[name="unCheckChecking"]').click(function() {
                    console.log($(this).attr('data-staffId'))
                    console.log($(this).attr('data-month'))
                    console.log($(this).attr('data-yr'))
                    console.log("clicked uncheck box")
                    $('#proccessingRequest').show()
                    $.ajax({
                        type: "post",
                        url: "/checking/verified",
                        data: {
                            'checked': 0,
                            'staffId': $(this).attr('data-staffId'),
                            'month': $(this).attr('data-month'),
                            'yr': $(this).attr('data-yr'),
                            '_token': $('input[name=_token]').val()
                        },
                        dataType: "json",
                        success: function(response) {
                            console.log(response)
                            // location.reload(true)
                            $('#proccessingRequest').hide()
                        }
                    });

                })
            })

            //for audit to check
            $('#auditChecked').each(function() {
                $('input[name="auditChecked"]').click(function() {
                    $('#proccessingRequest').show()
                    $.ajax({
                        type: "post",
                        url: "/audit/verified",
                        data: {
                            'checked': 1,
                            'staffId': $(this).attr('data-staffId'),
                            'month': $(this).attr('data-month'),
                            'yr': $(this).attr('data-yr'),
                            '_token': $('input[name=_token]').val()
                        },
                        dataType: "json",
                        success: function(response) {
                            // location.reload(true)
                            $('#proccessingRequest').hide()
                        }
                    });
                })
            })

            //for audit to uncheck
            $('#auditUnChecked').each(function() {
                $('input[name="auditUnChecked"]').click(function() {
                    $('#proccessingRequest').show()
                    $.ajax({
                        type: "post",
                        url: "/audit/verified",
                        data: {
                            'checked': 0,
                            'staffId': $(this).attr('data-staffId'),
                            'month': $(this).attr('data-month'),
                            'yr': $(this).attr('data-yr'),
                            '_token': $('input[name=_token]').val()
                        },
                        dataType: "json",
                        success: function(response) {
                            // location.reload(true)
                            $('#proccessingRequest').hide()
                        }
                    });
                })
            })


        } else {
            //for checking to check
            $('#checkingChecked').each(function() {
                $('input[name="checkingChecked"]').click(function() {
                    console.log($(this).attr('data-staffId'))
                    console.log($(this).attr('data-month'))
                    console.log($(this).attr('data-yr'))
                    console.log("clicked checkbox 1")
                    $('#proccessingRequest').show()
                    $.ajax({
                        type: "post",
                        url: "/checking/verified",
                        data: {
                            'checked': 1,
                            'staffId': $(this).attr('data-staffId'),
                            'month': $(this).attr('data-month'),
                            'yr': $(this).attr('data-yr'),
                            '_token': $('input[name=_token]').val()
                        },
                        dataType: "json",
                        success: function(response) {
                            console.log(response)
                            // location.reload(true)
                            $('#proccessingRequest').hide()
                        }
                    });
                })
            })

            $('#unCheckChecking').each(function() {
                //for checking to uncheck
                $('input[name="unCheckChecking"]').click(function() {
                    console.log($(this).attr('data-staffId'))
                    console.log($(this).attr('data-month'))
                    console.log($(this).attr('data-yr'))
                    console.log("clicked uncheck box")
                    $('#proccessingRequest').show()
                    $.ajax({
                        type: "post",
                        url: "/checking/verified",
                        data: {
                            'checked': 0,
                            'staffId': $(this).attr('data-staffId'),
                            'month': $(this).attr('data-month'),
                            'yr': $(this).attr('data-yr'),
                            '_token': $('input[name=_token]').val()
                        },
                        dataType: "json",
                        success: function(response) {
                            console.log(response)
                            // location.reload(true)
                            $('#proccessingRequest').hide()
                        }
                    });

                })
            })

            //for audit to check
            $('#auditChecked').each(function() {
                $('input[name="auditChecked"]').click(function() {
                    $('#proccessingRequest').show()
                    $.ajax({
                        type: "post",
                        url: "/audit/verified",
                        data: {
                            'checked': 1,
                            'staffId': $(this).attr('data-staffId'),
                            'month': $(this).attr('data-month'),
                            'yr': $(this).attr('data-yr'),
                            '_token': $('input[name=_token]').val()
                        },
                        dataType: "json",
                        success: function(response) {
                            // location.reload(true)
                            $('#proccessingRequest').hide()
                        }
                    });
                })
            })

            //for audit to uncheck
            $('#auditUnChecked').each(function() {
                $('input[name="auditUnChecked"]').click(function() {
                    $('#proccessingRequest').show()
                    $.ajax({
                        type: "post",
                        url: "/audit/verified",
                        data: {
                            'checked': 0,
                            'staffId': $(this).attr('data-staffId'),
                            'month': $(this).attr('data-month'),
                            'yr': $(this).attr('data-yr'),
                            '_token': $('input[name=_token]').val()
                        },
                        dataType: "json",
                        success: function(response) {
                            // location.reload(true)
                            $('#proccessingRequest').hide()
                        }
                    });
                })
            })
        }

        $(document).ready(function() {
            $("#comments").click(function(e) {
                e.preventDefault();
                jQuery('#commentsModal').modal('show')
            })
        })

    })
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // const submitForm = document.querySelector('form[action="{{ url('/submit-salary') }}"]');
        const submitForm = document.querySelector('form[action*="submit-salary"]');

        if (submitForm) {
            submitForm.addEventListener("submit", function(e) {
                let firstNegativeRow = null;

                document.querySelectorAll(".netpay").forEach(function(td) {
                    let value = parseFloat(td.innerText.replace(/,/g, ""));
                    if (value <= 0 && !firstNegativeRow) {
                        firstNegativeRow = td.closest("tr");
                    }
                });

                if (firstNegativeRow) {
                    e.preventDefault(); // stop form submission

                    // highlight the row
                    firstNegativeRow.classList.add("highlight-row");

                    // smooth scroll to the row
                    firstNegativeRow.scrollIntoView({
                        behavior: "smooth",
                        block: "center"
                    });

                    Swal.fire({
                        icon: "error",
                        title: "Submission Blocked",
                        text: "A staff has total net emolument less than or equal to zero. Please correct it before submitting salary."
                    });

                    return false;
                }
            });
        }
    });
</script>

{{-- <script>
    document.addEventListener("DOMContentLoaded", function() {

        const submitForm = document.querySelector('form[action*="submit-salary"]');

        if (!submitForm) return;

        submitForm.addEventListener("submit", function(e) {
            let firstNegativeRow = null;

            // FAST: using a normal for loop (much faster than foreach)
            const tds = document.querySelectorAll(".netpay");

            for (let i = 0; i < tds.length; i++) {
                let value = parseFloat(tds[i].textContent.replace(/,/g, ""));
                if (value <= 0) {
                    firstNegativeRow = tds[i].closest("tr");
                    break; // STOP immediately
                }
            }

            if (firstNegativeRow) {
                e.preventDefault();

                Swal.fire({
                    icon: "error",
                    title: "Submission Blocked",
                    text: "A staff has NetPay less than or equal to zero. Please correct it before submitting salary.",
                }).then(() => {
                    // highlight and scroll AFTER alert closes
                    firstNegativeRow.classList.add("highlight-row");
                    firstNegativeRow.scrollIntoView({
                        behavior: "smooth",
                        block: "center"
                    });
                });

                return false;
            }
        });
    });
</script> --}}








</html>

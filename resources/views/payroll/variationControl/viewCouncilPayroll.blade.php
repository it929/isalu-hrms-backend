<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>SUPREME COURT
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

        @page { size: A2 landscape; margin: 6mm; margin-top: 120px; }
        .print-only { display: none; }
        @media print {
            .table tr .bg {
                /* background: #0cf !important;
                opacity: 0.8 !important;
                color: #FFF !important; */
            }

            .no-print { display: none !important; }
            .print-only { display: table-cell !important;} 
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
    <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>


<body>
    <div align="center" class="marginTableHeading">
        <h2>
            <div style="color:#06c;">NIGERIA FEDERAL GOVERNMENT PAYROLL
                <br />
                JUSTICES MEMBERS PAYROLL
            </div><br />
        </h2>
    </div>

    <div style="width:90%; margin: auto;">
        <table class="head-color" width="1802" border="0" cellpadding="0" cellspacing="0" style="font-size:18px">
            <span id="proccessingRequest" style="display:none; font-size:20px;" class="text-success"> <strong>Processing
                    Please wait...</strong> </span>
            <tr>
                <td>T.F.PRB (1973)</td>
                <td>
                    <div align="left">Sheet No:</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h3>MINISTRY/DEPARTMENT:

                        ISALU HOSPITAL LIMITED, ABUJA </h3>
                </td>
            </tr>
            <tr>
                <td width="1294">
                    <strong>MONTH ENDING: @if (session('schmonth'))
                            {{ session('schmonth') }}
                        @endif
                    </strong><br />
                </td>
                <td width="508" align="rights">Date Printed: {{ date('l, F d, Y') }}</td>
            </tr>

            <tr>
                <td width="1294">
                    <strong>
                      BANK:
                      {{ $selectedBank }}
                    </strong>
                </td>
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


        <table class="table table-condense table-responsive" border="1" cellpadding="4" cellspacing="0">
            <tr class="th-row">
                <td width="44" rowspan="2" align="center" valign="middle"><strong>SN</strong></td>
                <td width="44" rowspan="2" align="center" valign="middle"><strong>FileNo</strong></td>
                <td width="44" class="no-print" rowspan="2" align="center" style="writing-mode: vertical-rl;">
                    <strong>CHECKING</strong>
                </td>
                <td width="44" class="no-print" rowspan="2" align="center" style="writing-mode: vertical-rl;">
                    <strong>AUDIT</strong>
                </td>
                <td width="271" rowspan="2" align="center" valign="middle"><strong>NAME</strong></td>

                <td colspan="{{ isset($staffEarnElement) ? count($staffEarnElement) + 2 : 2 }}" align="center"
                    valign="top"><strong>EARNINGS</strong></td>
                <td colspan="{{ isset($staffDeductionElement) ? count($staffDeductionElement) + 2 : 2 }}" align="center"
                    valign="top"><strong>DEDUCTIONS</strong></td>

                <td width="70" rowspan="2" align="center" valign="middle"><strong>TOTAL DEDUCTION.</strong></td>
                {{-- <td align="center" rowspan="2" valign="middle"><strong>ALLOWANCE</strong></td> --}}
                <td width="85" rowspan="2" align="center" valign="middle"><strong>NET PAY</td>

            </tr>
            <tr>
                <td width="90" align="center" valign="middle"><strong>BASIC <br /> (CONSOLIDATED)</strong></td>
                @if (isset($staffEarnElement) && $staffEarnElement)
                    @foreach ($staffEarnElement as $elementEarn)
                        <th align="center"><strong>{{ strtoupper($elementEarn->description) }}</strong></th>
                        @php $totalEarnAmount[$elementEarn->CVID] = 0.0; @endphp
                    @endforeach
                @endif
                <td width="80" align="center" valign="middle"><strong>GROSS<br /> EMOLUMENT</strong></td>

                <td width="74" align="center" valign="middle"><strong>TAX</strong></td>
                <td width="74" align="center" valign="middle"><strong>NHF</strong></td>

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
                $totalGross = 0;
                $totalAllow = 0;
            @endphp


            @foreach ($payroll_detail as $reports)
                @php

                    $severance = DB::table('tblotherEarningDeduction')
                        ->where('CVID', '=', 25)
                        ->where('staffid', '=', $reports->staffid)
                        ->where('year', '=', $reports->year)
                        ->where('month', '=', $reports->month)
                        ->first();
                    $furAll = DB::table('tblotherEarningDeduction')
                        ->where('CVID', '=', 23)
                        ->where('staffid', '=', $reports->staffid)
                        ->where('year', '=', $reports->year)
                        ->where('month', '=', $reports->month)
                        ->first();
                    if ($severance == '') {
                        $ser = 0;
                    } else {
                        $ser = $severance->amount;
                    }
                    if ($furAll == '') {
                        $funiture = 0;
                    } else {
                        $funiture = $furAll->amount;
                    }

                @endphp

                <tr>
                    <td align="right">{{ $k++ }}</td>
                    <td align="right">{{ $reports->fileNo }}</td>
                    <td align="right" class="no-print">
                        @if ($reports->vstage == 3 && $userRole->can_check == 1)
                            @if ($reports->checking_verified == 1)
                                {{-- <span class="text-success"><strong> ok </strong></span> --}}
                                <input type="checkbox" checked="checked" name="unCheckChecking" id="unCheckChecking"
                                    data-staffId={{ $reports->staffid }} data-month={{ $reports->month }}
                                    data-yr={{ $reports->year }} />
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
                                <input type="checkbox" checked="checked" name="auditUnChecked" id="auditUnChecked"
                                    data-staffId={{ $reports->staffid }} data-month={{ $reports->month }}
                                    data-yr={{ $reports->year }} />
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
                            href="{{ url("/con-pecard/getCard/$reports->staffid/$reports->year") }}">{{ $reports->council_title }}
                            {{ $reports->name }}</a> <span class="pr">{{ $reports->council_title }}
                            {{ $reports->name }}</span></td>
                    <td width="75" align="right"><?php $bstotal += $reports->Bs; ?>
                        {{ number_format($reports->Bs, 2, '.', ',') }}</td>

                    @php
                        $sumEarnAmount = 0.0;
                        $sumDeductAmount = 0.0;
                        $sumEarnGross = 0;
                    @endphp
                    @foreach ($staffEarnElement as $element)
                        @php
                            $getEarnAmount = $getStaffMonthEarnAmount[$reports->staffid][$element->CVID];
                            $sumEarnAmount = $getEarnAmount ? $getEarnAmount->staffEarnings : 0.0;
                            $sumEarnGross += $sumEarnAmount;
                            $totalEarnAmount[$element->CVID] += $sumEarnAmount;
                        @endphp
                        <td width="75" align="right"> {{ number_format($sumEarnAmount, 2, '.', ',') }}</td>
                    @endforeach

                    <td width="66" align="right" class="bg"
                        style="background:#0cf;opacity:0.8; color:#FFF;">
                        <?php $totalGross += $reports->Bs + $reports->AEarn + $sumEarnGross; ?>{{ number_format($reports->Bs + $reports->AEarn + $sumEarnGross, 2, '.', ',') }}
                    </td>



                    <td width="52" align="right"><?php $taxtotal += $reports->TAX; ?>
                        {{ number_format($reports->TAX, 2, '.', ',') }}</td>
                    <td width="52" align="right"><?php $nhftotal += $reports->NHF; ?>
                        {{ number_format($reports->NHF, 2, '.', ',') }}</td>
                    @foreach ($staffDeductionElement as $elementDec)
                        @php
                            $getDeductAmount = $getStaffMonthDeductionAmount[$reports->staffid][$elementDec->CVID];
                            $sumDeductAmount = $getDeductAmount ? $getDeductAmount->staffDeductions : 0.0;
                            $totalDeductAmount[$elementDec->CVID] += $sumDeductAmount;
                        @endphp
                        <td width="85" align="right">{{ number_format($sumDeductAmount, 2, '.', ',') }}</td>
                    @endforeach
                    <td width="82" align="right" class="bg"
                        style="background:#0cf;opacity:0.8; color:#FFF;"><?php $deducttotal += $reports->TD; ?> <strong>
                            {{ number_format($reports->TD, 2, '.', ',') }}</strong></td>
                    {{-- <td width="75" align="right"><?php $totalAllow += $reports->PEC; ?>
                        {{ number_format($reports->PEC, 2, '.', ',') }}</td> --}}
                    <td width="82" align="right"><?php $totalNetEmolu += $reports->NetPay; ?>
                        {{ number_format($reports->NetPay, 2, '.', ',') }}</td>

                </tr>
            @endforeach


            <tr>
                <td class="no-print" colspan="5" align="right"><strong>TOTAL</strong></td>
                <td class="print-only" colspan="3" align="right"><strong>TOTAL</strong></td>

                <td align="right"><strong>{{ number_format($bstotal, 2, '.', ',') }}</strong></td>
                @foreach ($staffEarnElement as $element)
                    <td width="66" align="right">
                        {{ number_format($totalEarnAmount[$element->CVID], 2, '.', ',') }}</td>
                @endforeach
                <td align="right"><strong>{{ number_format($totalGross, 2, '.', ',') }} </strong></td>


                <td align="right"><strong>{{ number_format($taxtotal, 2, '.', ',') }}</strong></td>
                <td align="right"><strong>{{ number_format($nhftotal, 2, '.', ',') }}</strong></td>
                @foreach ($staffDeductionElement as $elementDec)
                    <td width="85" align="right">
                        {{ number_format($totalDeductAmount[$elementDec->CVID], 2, '.', ',') }}</td>
                @endforeach
                <td align="right"><strong>{{ number_format($deducttotal, 2, '.', ',') }}</strong></td>
                {{-- <td align="right"><strong>{{ number_format($totalAllow, 2, '.', ',') }}</strong></td> --}}
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

        <h2 class="hidden-print" style="margin-top:10px; color:green; margin-left:20px;">KEY</h2>
        <!--Table Key -->

        <!--<table class="table-condense table-responsive hidden-print" border="1" cellpadding="4" cellspacing="0" style="margin-left:20px;">
  <tr>
    <th scope="col">ABBREVIATION</th>
    <th scope="col">MEANING</th>
  </tr>
  <tr>
    <td>BS</td>
    <td>Basic Salary</td>
  </tr>

  <tr>
    <td>PEN</td>
    <td>Pension</td>
  </tr>
  <tr>
    <td>NHF</td>
    <td>National Housing Fund</td>
  </tr>

  </table>-->
    </div>
    <!-- Table Key -->


    <br>
    <div style="margin-left:30px;">
        {{-- <h2 class="hidden-print">  <a  class="hidden-print" type="submit" class="btn btn-success btn-sm pull-right" href = "{{ url('/payrollReport/create') }}">Back</a> --}}
        <h2 class="hidden-print"> <a class="hidden-print" type="submit" class="btn btn-success btn-sm pull-right"
                href = "{{ url('/council-members/payroll-vc') }}">Back</a>
        </h2>

        <a href="/payroll-council-comments/{{ $payroll_detail[0]->year }}/{{ $payroll_detail[0]->month }}"
            class="btn btn-primary" style="margin-bottom: 10px;" type="button">
            View Comment's
        </a>

        <div>
            <input type="hidden" value="{{ $bankName ?? '' }}" id="selectedBankName">

            <div id="selBankName">
                @if ($payroll_detail)
                    @if ($payroll_detail[0]->vstage == 0 && $userRole->can_authorize_salary == 1)
                        @include('payroll.variationControl.includeSalaryCouncil')
                    @elseif($payroll_detail[0]->vstage == 3 && $userRole->can_check == 1)
                        @include('payroll.variationControl.includeCheckingCouncil')
                    @elseif($payroll_detail[0]->vstage == 4 && $userRole->can_audit == 1)
                        @include('payroll.variationControl.includeAuditCouncil')
                    @elseif(($payroll_detail[0]->vstage == 5 || $payroll_detail[0]->vstage == 6) && $userRole->can_cpo == 1)
                        @include('payroll.variationControl.includeCpoCouncil')
                    @endif
                @else
                    {{ 'No record' }}
                @endif
            </div>
        </div>

    </div>

</body>

<script src="{{ asset('/assets/js/jQuery-2.2.0.min.js') }}"></script>
<script src="{{ asset('/assets/js/bootstrap.min.js') }}"></script>
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
                        url: "/checking-council/verified",
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
                        url: "/checking-council/verified",
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
                        url: "/audit-council/verified",
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
                        url: "/audit-council/verified",
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
                        url: "/checking-council/verified",
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
                        url: "/checking-council/verified",
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
                        url: "/audit-council/verified",
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
                        url: "/audit-council/verified",
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

</html>

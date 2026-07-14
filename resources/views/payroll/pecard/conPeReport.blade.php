<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>ISALU HOSPITAL LIMITED</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

        <!-- AdminLTE Skins. Choose a skin from the css/skins
  folder instead of downloading all of them to reduce the load. -->

        @yield('styles')

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesnt work if you view the page via file:// -->
        <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
        <script>
            var murl = "{{ url('/') }}";
        </script>
    </head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>ISALU HOSPITAL LIMITED...::...PeCard Report</title>
    <style type="text/css">
        @media print {

            .no-print,
            .no-print * {
                display: none !important;
            }

            .wrapper {
                width: 100%;
                margin-top: 20px;
                float: left;
            }
        }

        .wrapper {
            width: 2100px;
            margin-top: 20px;
            float: left;
        }

        .sec1,
        .sec2,
        .sec3,
        .sec4,
        .sec5 {
            width: 19%;
            float: left;

            margin-left: 1%;
        }

        table tr td span {
            padding: 5px;
        }

        table tr td strong,
        table tr td b {
            font-size: 12px;
            padding: 5px;

        }

        body {}

        p {
            padding: 0px;
        }

        .peTable tr th {
            font-size: 12px;
            text-align: center;

        }

        .peTable tr td {
            font-size: 12px;
            padding: 6px;

        }
    </style>

    <body style="background:white;">
        <div class="row">
            <div class="col-md-12">

                <div align="center">
                    <h4 class="text-center ">FEDERAL GOVERNMENT OF NIGERIA &nbsp; &nbsp;&nbsp; &nbsp; <strong>PERSONAL
                            EMOLUMENTS RECORD </strong>
                    </h4>
                </div>
                <div class="wrapper" style="background:white"> <!-- wrapper-->
                    <div class="sec1"> <!-- sec1 start-->
                        <table width="100%" border="1" cellpadding="4" cellspacing="0">
                            <tr>
                                <td colspan="4" align="center"><strong>SALARY PER ANUM</strong></td>
                                <td align="center"><strong>MONTHLY TOTAL</strong></td>
                            </tr>
                            <tr>
                                <td align="center"><strong>BASIC</strong></td>
                                <td align="center"><strong>IND.ADD</strong></td>
                                <td align="center"><strong>CONTRACT</strong></td>
                                <td align="center"><strong>TOTAL</strong></td>
                                <td align="center"></td>
                            </tr>
                            <tr>
                                <td align="center"> &nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center"> &nbsp; &nbsp;</td>


                            </tr>
                            <tr>
                                <td align="center"> &nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center"> &nbsp; &nbsp;</td>


                            </tr>
                            <tr>
                                <td colspan="5"><b> M. B. A. INC N K PER MONTH DATE FIRST PAYABLE</b></td>

                            </tr>
                            <tr>
                                <td colspan="5"><b> M. B. A. STAND N K PER MONTH DATE FIRST PAYABLE</b></td>

                            </tr>

                        </table>
                    </div> <!-- sec1 ends-->
                    <div class="sec2"> <!-- sec2 start-->
                        <table width="100%" border="1" cellpadding="4" cellspacing="0">
                            <tr>
                                <td colspan ="3" align="center"><strong>INCREMENT</strong></td>
                            </tr>
                            <tr>
                                <td align="center"><strong>DATE</strong></td>
                                <td align="center"><strong>AMOUNT PER ANNUM</strong></td>
                                <td align="center"><strong>AUTHY</strong></td>
                            </tr>
                            <tr>
                                <td>&nbsp; &nbsp;</td>
                                <td>&nbsp; &nbsp;</td>
                                <td>&nbsp; &nbsp;</td>
                            </tr>
                        </table>
                    </div> <!-- sec2 ends-->
                    <div class="sec3">
                        <table width="100%" border="1" cellpadding="4" cellspacing="0" style="background:#white">

                            <tr>
                                <td align="center"><strong>CONTRACT</strong></td>

                            </tr>
                            <tr>
                                <td><strong>DATE OF COMMT</strong></td>

                            </tr>
                            <tr>
                                <td><strong>DATE EXPIRY</strong></td>

                            </tr>
                            <tr>
                                <td><strong>DATE OF COMMT</strong></td>

                            </tr>
                            <tr>
                                <td><strong>DATE EXPIRY</strong></td>

                            </tr>

                        </table>
                    </div>
                    <div class="sec4">

                        <table width="100%" border="1" cellpadding="4" cellspacing="0" style="background:white">
                            <tr>
                                <td colspan ="5" align="center"><strong>DEDUCTION "B" MISCELLANEOUS</strong></td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td colspan="2" style="border-bottom: 1px solid #666; padding-top:0px;">
                                                <span>SALARY</span> <br /><span>ADVANCE</span></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="border-bottom: 1px solid #555;">
                                                <span>INSTALLMENT</span></td>
                                        </tr>
                                        <tr>
                                            <td border="0"><span>First</span></td>
                                            <td border="0"><span>Final</span></td>
                                        </tr>
                                    </table>
                                </td>
                                <td align="center">
                                    A

                                </td>
                                <td align="center">
                                    <table width="100%" cellpadding="4" cellspacing="0">
                                        <tr>
                                            <td style="border-bottom: 1px solid #333;"><strong>O.GOVTS <br /> W.O.P.S
                                                    <br /> PER MTH</strong></td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp; &nbsp;</td>
                                        </tr>

                                    </table>

                                </td>
                                <td align="center">
                                    B <br /><br />
                                    C

                                </td>
                                <td>
                                    <table width="100%" cellpadding="4" cellspacing="0">
                                        <tr>
                                            <td colspan="2" style="border-bottom: 1px solid #555;">
                                                <span>RECOVERS</span> <br /><span> PER MONTH</span></td>
                                        </tr>
                                        <tr>
                                            <td border="0"><span>First</span></td>
                                            <td border="0"><span>Final</span></td>
                                        </tr>
                                    </table>
                                </td>

                            </tr>

                        </table>

                    </div>
                    <div class="sec5">
                        <p class="col-xs-6"><strong>Min/Dept</strong> </p>
                        <p class="col-xs-6">January: {{ $year }}</p>
                        <p class="col-xs-6"><strong>Control No:</strong>----{{ $details->control_no }}----</p>
                        <p class="col-xs-6">December: {{ $year }}</p>
                        <p class="col-xs-12"><strong>Bank:</strong> {{ $details->bank }}----------------------</p>
                        <p class="col-xs-12"><strong>Branch:</strong>
                            {{ $getLevel->bank_branch }}----------------------</p>
                        <p class="col-xs-12"><strong>Name:</strong> {{ $getLevel->name }}----------------------</p>
                        <p class="col-xs-12"><strong>Rank:</strong> ----------------------</p>
                        <p class="col-xs-12"><strong>Min/Dept:</strong> ----------------------</p>
                        <p class="col-xs-12">&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
                            &nbsp;<strong>Commenced:</strong> ---------------</p>
                    </div>

                </div><!-- /// wrapper -->

                <br />

                <div class="wrapper" style="background:white"> <!-- second wrapper-->
                    <div class="sec1"> <!-- sec1 start-->
                        <table width="100%" border="1" cellpadding="4" cellspacing="0">
                            <tr>
                                <td colspan="6" align="center"><strong>ACTING ALLOWANCE</strong></td>

                            </tr>
                            <tr>
                                <td align="center"><strong>RATE PER<br />ANNUM</strong></td>
                                <td align="center"><strong>RATE PER<br />MONTH</strong></td>
                                <td align="center"><strong>DATE COMM</strong></td>
                                <td align="center"><strong>DATE CEASED</strong></td>
                                <td align="center"><strong>GAZ<br />AUTHY</strong></td>
                                <td align="center"><strong>VARIATION<br />COUNT. NO</strong></td>

                            </tr>
                            <tr>
                                <td align="center"> &nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center"> &nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>

                            </tr>
                            <tr>
                                <td align="center"> &nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center"> &nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>

                            </tr>


                        </table>
                        <table width="100%" border="1" cellpadding="4" cellspacing="0"
                            style="margin-top:10px;">
                            <tr>
                                <td colspan="4" align="center"><strong>EARNINGS</strong></td>

                            </tr>
                            <tr>
                                <td align="center"><strong>DATE</strong></td>
                                <td align="center"><strong>SALARY</strong></td>
                                <td align="center"><strong>ACTING ALLCE</strong></td>
                                <td align="center"><strong>MONTHLY VARIATION<br />CONTROL. NO</strong></td>
                            </tr>
                            <tr>
                                <td align="center"> &nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>

                            </tr>
                            <tr>
                                <td align="center"> &nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>
                                <td align="center">&nbsp; &nbsp;</td>


                            </tr>


                        </table>
                    </div> <!-- sec1 ends-->
                    <div class="sec2"> <!-- sec2 start-->
                        <table width="100%" border="1" cellpadding="4" cellspacing="0">
                            <tr>
                                <td align="center"><strong>INCOME TAX CODE NO</strong></td>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                            </tr>

                        </table>
                        <table width="100%" border="1" cellpadding="4" cellspacing="0">
                            <tr>
                                <td colspan="2"><strong>NAME</strong></td>

                            </tr>
                            <tr>
                                <td colspan="2"><strong>REF NO</strong></td>
                            </tr>
                            <tr>

                                <td colspan="2"><strong>FREE PAY TABLE MONTHLY</strong></td>

                            </tr>
                            <tr>
                                <td><strong>1&nbsp; &nbsp;</strong></td>
                                <td><strong>7&nbsp; &nbsp;</strong></td>
                            </tr>
                            <tr>
                                <td><strong>2&nbsp; &nbsp;</strong></td>
                                <td><strong>8&nbsp; &nbsp;</strong></td>
                            </tr>
                            <tr>
                                <td><strong>3&nbsp; &nbsp;</strong></td>
                                <td><strong>9&nbsp; &nbsp;</strong></td>
                            </tr>
                            <tr>
                                <td><strong>4&nbsp; &nbsp;</strong></td>
                                <td><strong>10&nbsp; &nbsp;</strong></td>
                            </tr>
                            <tr>
                                <td><strong>5&nbsp; &nbsp;</strong></td>
                                <td><strong>11&nbsp; &nbsp;</strong></td>
                            </tr>
                            <tr>
                                <td><strong>6&nbsp; &nbsp;</strong></td>
                                <td><strong>12&nbsp; &nbsp;</strong></td>
                            </tr>

                        </table>
                    </div> <!-- sec2 ends-->
                    <div class="sec3">
                        <table width="100%" border="1" cellpadding="4" cellspacing="0">

                            <tr>
                                <td align="center"><strong>U. D. C GUARANTEE</strong></td>

                            </tr>
                            <tr>
                                <td><strong>DATE OF COMMT</strong></td>

                            </tr>
                            <tr>
                                <td><strong>DATE EXPIRY</strong></td>

                            </tr>


                        </table>
                    </div>
                    <div class="sec4">

                        <table width="100%" border="1" cellpadding="4" cellspacing="0">
                            <tr>
                                <td align="center"><strong>RENT/WATER</strong></td>
                                <td align="center"><strong>M/VADV INCL. INT</strong></td>
                                <td align="center"><strong>W.O.P.S.</strong></td>
                                <td align="center"><strong></strong></td>
                                <td align="center"><strong>A.S.H.S<BR>INCL. INTEREST</strong></td>
                            </tr>
                            <tr>
                                <td><strong>PER ANNUM </strong></td>
                                <td><strong>PRINCIPAL</strong></td>
                                <td><strong>PER ANNUM</strong></td>
                                <td><strong>&nbsp; &nbsp;</strong></td>
                                <td><strong>PRINCIPAL</strong></td>
                            </tr>
                            <tr>
                                <td><strong>PER MONTH </strong></td>
                                <td><strong>INTEREST</strong></td>
                                <td><strong>PER MONTH</strong></td>
                                <td><strong></strong></td>
                                <td><strong>INTEREST</strong></td>
                            </tr>
                            <tr>
                                <td><strong>PER ANNUM </strong></td>
                                <td><strong>TOTAL ADV.</strong></td>
                                <td><strong>PRINCIPAL</strong></td>
                                <td><strong>&nbsp; &nbsp;</strong></td>
                                <td><strong>TOTAL ADV.</strong></td>
                            </tr>
                            <tr>
                                <td><strong>PER MONTH</strong></td>
                                <td><strong>MONTHLY AMT</strong></td>
                                <td><strong>INTEREST</strong></td>
                                <td><strong>&nbsp; &nbsp;</strong></td>
                                <td><strong>MONTHLY AMT</strong></td>
                            </tr>
                            <tr>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                            </tr>
                            <tr>
                                <td colspan ="2" align="center"><strong>PER ANNUM </strong></td>

                                <td align="center"><strong>PER ANNUM</strong></td>
                                <td align="center"><strong>&nbsp; &nbsp;</strong></td>
                                <td align="center"><strong>PRINCIPAL</strong></td>
                            </tr>



                        </table>

                    </div>
                    <div class="sec5"><!-- sec 5 starts -->
                        <p class="col-xs-12"><strong>EMPLOYMENT</strong> </p>
                        <p class="col-xs-12">&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
                            &nbsp;<strong>Terminated:</strong> ---------------</p>
                        <p class="col-xs-6"><strong>Date of Birth:</strong> {{ $details->dob }}</p>
                        <p class="col-xs-6"><strong>January:</strong> -----</p>
                        <p class="col-xs-6"><strong>Children:</strong> -----M</p>
                        <p class="col-xs-6"> ----F</p>
                        <p class="col-xs-12"><strong>Residential Address:</strong>
                            {{ $details->home_address }}----------------------</p>
                        <p class="col-xs-12"><strong>Leave Address:</strong> ----------------------</p>
                        <p class="col-xs-12"><strong>M.B.A Increased Last Month Payable:</strong> -------------- ------
                        </p>
                        <p class="col-xs-12"><strong>M.B.A. Standard:</strong> ----------------- --------</p>
                        <p class="col-xs-12"><strong>REG D CHECK</strong> &nbsp; &nbsp;&nbsp; &nbsp; <strong>OFF
                                NO:</strong> ------------</p>
                    </div><!-- /// sec 5 -->

                </div><!-- /// wrapper -->


                <div class="col-md-12">
                    <div class="wrapper" style="background:white"><!-- start wrapper -->
                        <table class="peTable" width="100%" border="1" cellpadding="4" cellspacing="0">

                            <tr>
                                <th align="" colspan="2"><strong></strong></th>
                                <th align=""
                                    colspan="{{ isset($staffEarnElement) ? count($staffEarnElement) + 4 : 4 }}">
                                    <strong>ALL EARNINGS</strong></th>
                                <th align="center"
                                    colspan="{{ isset($staffDeductionElement) ? count($staffDeductionElement) + 5 : 5 }}">
                                    <strong>ALL DEDUCTIONS</strong></th>
                                <th align=""><strong></strong></th>
                            </tr>

                            <tr>
                                <th align=""><strong>MTN NO</strong></th>
                                <th align="" style="writing-mode: vertical-rl;"><strong>Checking</strong></th>
                                <th align="" style="writing-mode: vertical-rl;"><strong>Audit</strong></th>
                                <th align="center"><strong>MONTH</strong></th>
                                <th align="center"><strong>BASIC<br>SALARY</strong></th>
                                <th align="center"><strong>ARREARS <BR /> EARNING</strong></th>
                                @if (isset($staffEarnElement) && $staffEarnElement)
                                    @foreach ($staffEarnElement as $elementEarn)
                                        <th align="center">
                                            <strong>{{ strtoupper($elementEarn->description) }}</strong></th>
                                    @endforeach
                                @endif
                                <th align="center"><strong>GROSS<br>EMOLUMENT</strong></th>
                                <th align="center"><strong>TAX</strong></th>
                                <th align="center"><strong>NHF</strong></th>
                                <th align="center"><strong>UNION DUES</strong></th>
                                <th align="center"><strong>PENSION<br />CONTRIBUTION</strong></th>
                                @if (isset($staffDeductionElement) && $staffDeductionElement)
                                    @foreach ($staffDeductionElement as $elementDeduct)
                                        <th align="center">
                                            <strong>{{ strtoupper($elementDeduct->description) }}</strong></th>
                                    @endforeach
                                @endif
                                <th align="center"><strong>TOTAL<br />DEDUCTION</strong></th>
                                <th align="center"><strong>NET PAY</strong></th>
                                <th align="center"><strong>PECULIAR/<br />ALLOWANCE</strong></th>
                                <th align="center"><strong>40% <br /> PECULIAR <br /> FG</strong></th>
                                <th align="center"><strong>TOTAL NET<br />EMOLUMENT</strong></th>
                            </tr>

                            @if (isset($monthList) && $monthList)
                                @foreach ($monthList as $itemKey => $month)
                                    <tr>
                                        <th> {{ $itemKey }}&nbsp; &nbsp;</th>
                                        <td width="10">
                                            @if (DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('staffid', '=', $getLevel->staffid)->value('checking_verified') == 1)
                                                {{ 'ok' }}
                                            @endif

                                        </td>
                                        <td width="10">
                                            @if (DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('staffid', '=', $getLevel->staffid)->value('audit_verified') == 1)
                                                {{ 'ok' }}
                                            @endif
                                        </td>
                                        <td align="">{{ strtoupper($month) }}</td>
                                        <td align="right">{{ number_format($result[$month]['Bs'], 2, '.', ',') }}
                                        </td>
                                        <td align="right">{{ number_format($result[$month]['AEarn'], 2, '.', ',') }}
                                        </td>
                                        @php
                                            $sumEarnAmount = 0.0;
                                            $sumDeductAmount = 0.0;
                                        @endphp
                                        @foreach ($staffEarnElement as $element)
                                            @php
                                                $getEarnAmount = $getStaffMonthEarnAmount[$month][$element->CVID];
                                                $amountEarn = $getEarnAmount ? $getEarnAmount->staffEarning : 0.0;
                                                $sumEarnAmount += $amountEarn;
                                            @endphp
                                            <td align="right">{{ number_format($amountEarn, 2, '.', ',') }}</td>
                                        @endforeach
                                        <td align="right">
                                            {{ number_format($result[$month]['Bs'] + $result[$month]['AEarn'] + $sumEarnAmount, 2, '.', ',') }}
                                        </td>
                                        <td align="right"> {{ number_format($result[$month]['TAX'], 2, '.', ',') }}
                                        </td>
                                        <td align="right">{{ number_format($result[$month]['NHF'], 2, '.', ',') }}
                                        </td>
                                        <td align="right">{{ number_format($result[$month]['UD'], 2, '.', ',') }}
                                        </td>
                                        <td align="right">{{ number_format($result[$month]['PEN'], 2, '.', ',') }}
                                        </td>
                                        @foreach ($staffDeductionElement as $elementDec)
                                            @php
                                                $getDeductAmount =
                                                    $getStaffMonthDeductionAmount[$month][$elementDec->CVID];
                                                $sumDeductAmount = $getDeductAmount
                                                    ? $getDeductAmount->staffDeduction
                                                    : 0.0;
                                            @endphp
                                            <td align="right">
                                                {{ number_format($getDeductAmount->staffDeduction, 2, '.', ',') }}</td>
                                        @endforeach
                                        <td align="right"> {{ number_format($result[$month]['TD'], 2, '.', ',') }}
                                        </td>
                                        <td align="right">
                                            {{ number_format($result[$month]['Bs'] + $result[$month]['AEarn'] + $sumEarnAmount - $result[$month]['TD'], 2) }}
                                        </td>
                                        <td align="right"> {{ number_format($result[$month]['PEC'], 2, '.', ',') }}
                                        </td>
                                        <td align="right"> {{ number_format($result[$month]['PECFG'], 2, '.', ',') }}
                                        </td>
                                        <td align="right">
                                            {{ number_format($result[$month]['NetPay'], 2, '.', ',') }}</td>
                                    </tr>
                                @endforeach
                            @endif


                        </table>
                        <div class="col-xs-5">
                            NOTE: &nbsp;&nbsp;X NOT TO BE USED EXCEPT BY AUTHORITY OF<BR />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DIRECTOR
                            GENERAL MINISTRY OF FINANCE <br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ARREARS
                            SALARY MARK "AR" AR IN COLUMN FOR C.S.D.A
                        </div>
                        <div class="col-xs-5 col-xs-offset-2">
                            <div class ="col-xs-3">
                                MISCELLANEOUS <BR />
                                DEDUCTION CODE
                            </div>
                            <div class ="col-xs-6">
                                A SALARY ADVANCE <br />
                                B O. GOVTS SB.O.P.S <br />
                                C OTHER CHARGES
                            </div>
                        </div>
                    </div> <!-- end wrapper -->
                </div>

                <div class="row hidden-print">
                    <div class="col-md-11 col-md-offset-1">
                        <a href="{{ URL::previous() }}">Go back</a>
                    </div>
                </div>

            </div>
        </div>
    </body>

</html>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="images/favicon.ico">
<title>ISALU HOSPITAL LIMITED</title>
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<style type="text/css">

body,td,th {
  font-size: 15px;
  font-family: Verdana, Geneva, sans-serif;
  margin:15px;
}
@media print
{
    .no-print, .no-print *
    {
        display: none !important;
    }
}

body {
  background-image: {{asset('Images/nicn_bg.jpg')}};
}
.style2 {color: #008000}

</style>
<style type="text/css">
.head-color tr td, .table .th-row td
{
//color:#06c;

}
.table, .table tr td
{
//border: 1px solid #06C;
//color:#06c;
}
</style>
 <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}">
</script>
<body background="{{asset('Images/nicn_bg.jpg')}}"  onload="lookup(); gross();">
<div align="center"><strong><span class="style2"><h3><br />
  <br />
  <br />
ISALU HOSPITAL LIMITED</h3>
<h4>PAYMENT VOUCHER EMOLUMENTS - SENIOR OFFICERS</h4>
</span>
        </strong></div>
<br />
<?php
         $bstotal = 0.00;
    $hatotal = 0.00;
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
       ?>

  @foreach ($summary_detail as $reports)
<?php



        $bstotal += $reports->Bs ;
        $hatotal += $reports->AEarn;
    $trtotal += 0;
    $furtotal += 0;
    $taxtotal += $reports->TAX;
    $pectotal += $reports->PEC;
    $utitotal += 0;
    $drtotal += 0;
    $sertotal += 0;
    $e_arrearstotal += $reports->AEarn;
    $e_otherstotal += $reports->OEarn;
    $pentotal += $reports->PEN;
    $nhftotal += $reports->NHF;
    $d_arrearstotal += $reports->AD;
    $d_othertotal += $reports->OD;
    $earntotal += $reports->TEarn;
    $deducttotal += $reports->TD;
    $netpaytotal += ($reports->NetPay);
    $uniontotal += $reports->UD;
    $totalNetEmolu += $reports->NetPay;

       ?>

 @endforeach

<div align="left"><strong>
@if(session('bank'))
{{ session('bank') }}

@endif
</strong></div><div align="right">
<p>SHEET NO ___________________________________</p>
  <p>MONTH ENDING:  @if(session('schmonth'))
{{ strtoupper(session('schmonth')) }}

@endif REGULAR ALLOWANCE</p>
  <table class="table table-condense table-responsive" width="1586" border="1" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <tr class="th-row">
    <!--<td width="44" rowspan="2" align="center" valign="middle"><strong>FILE NO</strong></td>
    <td width="271" rowspan="2" align="center" valign="middle"><strong>NAME</strong></td>
    <td width="23" rowspan="2" align="center" valign="middle"><strong>GL</strong></td>
    <td width="23" rowspan="2" align="center" valign="middle"><strong>ST</strong></td>-->
    <td colspan="4" align="center" valign="top"><strong>EARNINGS</strong></td>
    <td colspan="5" align="center" valign="top"><strong>DEDUCTIONS</strong></td>

    <td width="70" rowspan="2" align="center" valign="middle"><strong>TOTAL DEDUCTION.</strong></td>
    <td width="70" rowspan="2" align="center" valign="middle"><strong>NET BASIC <br/> SALARY</strong></td>
    <td width="75" rowspan="2" align="center" valign ="middle"><strong> JUSU </strong></td>
    <td width="75" rowspan="2" align="center" valign ="middle"><strong> TOTAL NET <BR/> EMOLUMENT </strong></td>
  </tr>
  <tr>
    <td width="90" align="center" valign="middle"><strong>BASIC <br/> (CONSOLIDATED)</strong></td>



    <td width="38" align="center" valign="middle"><strong>TOTAL <BR/>ARREARS <BR/> EARNING</strong></td>
    <td width="52" align="center" valign="middle"><strong>OVERTIME/<BR/>ACT ALL.</strong></td>
    <td width="80" align="center" valign="middle"><strong>GROSS<br/> EMOLUMENT</strong></td>

    <td width="74" align="center" valign="middle"><strong>TAX</strong></td>
    <td width="50" align="center" valign="middle"><strong>PENSION</strong></td>
     <td width="50" align="center" valign="middle"><strong>UNION DUES</strong></td>
    <td width="85" align="center" valign="middle"><strong>NHF</strong></td>

    <td width="80" align="center" valign="middle"><strong>COOP/ADV.</strong></td>

  </tr>

  <tr>

    <td align="right"><strong>{{ number_format($bstotal, 2, '.', ',') }}</strong></td>

    <td align="right"><strong>{{ number_format($e_arrearstotal, 2, '.', ',') }} </strong></td>
    <td align="right"><strong>{{ number_format($e_otherstotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($netpaytotal + $deducttotal, 2, '.', ',') }}</strong></td>

    <td align="right"><strong>{{ number_format($taxtotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($pentotal, 2, '.', ',') }} </strong></td>
    <td align="right"><strong>{{ number_format($uniontotal, 2, '.', ',') }} </strong></td>

    <td align="right"><strong>{{ number_format($nhftotal, 2, '.', ',') }}</strong></td>

    <td align="right"><strong>{{ number_format($d_othertotal, 2, '.', ',') }}</strong></td>


    <td align="right"><strong>{{ number_format($deducttotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($netpaytotal + $pectotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($pectotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($totalNetEmolu, 2, '.', ',') }}</strong></td>

    </tr>

   <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
   <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
   <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
     <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
      <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
   <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
      <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
    <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
   <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
  <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
   <tr>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td></td>
   <td style="background:#999;opacity:0.9;"></td>
   <td></td>

   <td></td>
   </tr>
      <tr>

    <td align="right"><strong>{{ number_format($bstotal, 2, '.', ',') }}</strong></td>

        <td align="right"><strong>{{ number_format($e_arrearstotal, 2, '.', ',') }} </strong></td>
    <td align="right"><strong>{{ number_format($e_otherstotal, 2, '.', ',') }}</strong></td>
    <td style="background:#999;opacity:0.9;" align="right"><strong>{{ number_format($netpaytotal + $deducttotal, 2, '.', ',') }}</strong></td>

    <td align="right"><strong>{{ number_format($taxtotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($pentotal, 2, '.', ',') }} </strong></td>
    <td align="right"><strong>{{ number_format($uniontotal, 2, '.', ',') }} </strong></td>

    <td align="right"><strong>{{ number_format($nhftotal, 2, '.', ',') }}</strong></td>

    <td align="right"><strong>{{ number_format($d_othertotal, 2, '.', ',') }}</strong></td>


    <td  align="right"><strong>{{ number_format($deducttotal, 2, '.', ',') }}</strong></td>
    <td style="background:#999;opacity:0.9;" align="right"><strong>{{ number_format($netpaytotal + $pectotal, 2, '.', ',') }}</strong></td>
     <td align="right"><strong>{{ number_format($pectotal, 2, '.', ',') }}</strong></td>
     <td align="right"><strong>{{ number_format($totalNetEmolu, 2, '.', ',') }}</strong></td>

    </tr>
  </table>

  <p>&nbsp;</p>
</div>
<br />
<br>

            <div class="col-md-12 text-center">PAYROLL SUMMARY VOUCHER - SENIOR OFFICER</div>

            <div class="row">
            <p><strong> MINISTRY/DEPARTMENT P.V. No. ________________________ </strong> <strong>_____________ 20 ____________</strong></p>

            <div class="col-xs-3">
            <table class="table" border="1">

            <tr>
            <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
            <th colspan="3">VOUCHER CERTIFICATE</th>
            </tr>
            <tr>
            <td>DETAIL</td>
            <td>SIGNATURE</td>
            <td>NAME <br/> IN BLOCK LETTERS</td>
            <td>DATE</td>
            </tr>
            <tr>
            <td>Prepaid by</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>
            <tr>
            <td>Checked by</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>
            <tr>
            <td>Entered in <br/> Vote Book</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>
            <tr>
            <td>Passed by</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>
            <tr>
            <td>Paid by</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>

            </table>

            <table class="table" border="1">

            <tr>
            <th colspan ="2" class="text-center">INTERNAL AUDIT</th>
            </tr>
            <tr>
            <td>STAMP &nbsp; &nbsp; <br/><br/><br/><br/><br/></td>
            <td>DATE  &nbsp; <br/><br/><br/><br/><br/></td>
            </tr>

            </table>

              </div>

            <div class="col-xs-3">
            <table class="table" border="1" style="padding:6px 2px;">
             <tr>
            <td style="border:none;"></td>
            <th>AMOUNT</th>
            </tr>
            <tr>
            <th>Gross Emolument for the month</th>
            <td>{{ number_format($earntotal, 2, '.', ',') }}</td>
            </tr>
            <tr>
            <td>Month Basic Allowance for the month to</td>
            <td>{{ number_format($pectotal, 2, '.', ',') }}</td>
            </tr>

            <tr>
            <td>Gross Payment Per Voucher<br/>Recoveries from officers</td>
            <td>{{ number_format($earntotal-$pectotal, 2, '.', ',') }}</td>
            </tr>
            <tr>
            <td>Income Tax</td>
            <td>{{ number_format($taxtotal, 2, '.', ',') }}</td>
            </tr>
            <tr>
            <td>Income Rate</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>Rent/Water</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>M.V Advance including interest</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>Furniture Loan</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>Union Dues</td>
            <td>{{ number_format($uniontotal, 2, '.', ',') }}</td>
            </tr>
            <tr>
            <td>M.V Refurb Loan</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>Staff Transport Deduction</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>Salary Advance Recovery</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>Total Deductions</td>
            <td>{{ number_format($deducttotal, 2, '.', ',') }}</td>
            </tr>
            <tr>
            <td>A.S.H.S Adv. including Interest</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>Pension Scheme</td>
            <td>{{ number_format($pentotal, 2, '.', ',') }}</td>
            </tr>
            <tr>
            <td>Miscellaneous: C.</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
             <tr>
            <td>Miscellaneous: D.</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
             <tr>
            <td>Total Recoveries on vouchers</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>

            </table>
            </div>

            <div class="col-xs-3">
            <p><strong>HEAD ________</strong> <strong>SUBHEAD__________</strong></p>
            <p><strong>HEAD _______</strong> <strong>SUBHEAD__________</strong></p>

            <div>
            <table class="table" border="1">
            <tr>
            <th colspan="2"> REVENUE/ACCOUNT <BR/> CREDITED</th>
            <th> PAYING OFFICE <br/> USE ONLY </th>
            </tr>

            <tr>
            <td>HEAD___ SUBHD__</td>
            <td>Salary <br/> Deduction <br/> Adv. No</td>
            <td>On payt <br/> Dept./Min. <br/> P.V. NO</td>
            </tr>

            <tr>
            <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
           <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
           <!-- <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD___ SUBHD___</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>-->
            <tr>
            <td>HEAD</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
            <td>HEAD</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            </tr>

            </table>
            </div>

            </div>
            <div class="col-xs-3">
            <p>
            <table class="table" border="1">
            <tr>
            <th>P.V.No</th>
            <th>MONTH/YR</th>
            <th>PAYABLE AT</th>
            </tr>
            <td>&nbsp;&nbsp;&nbsp;<br/><br/></td>
            <td>&nbsp;&nbsp;&nbsp;<br/><br/></td>
            <td>&nbsp;&nbsp;&nbsp;<br/><br/></td>
            </tr>
            </table>
            </p>
            <p>
            <h5>PAYMENT AUTHORISATION</h5>
            I HEREBYCERTIFY THAT the above is a orrect statement of the amount payable to the persons named on Min./Dept
            </p>
            <p>Payroll Sheet Nos. ____________________ for month ending ________________20_______</p>
            <p>That their employment at the rate of salary shown was duly authorised by _______________________ <br/> the amount of __________________________Naira ___________Kobo may be paid under the Heads and items shown.</p>
            <p>______________________________ Signature</p>
            <p>________________________________________Time</p>
            <p>_____________________________ <strong>200</strong> _______</p>
            <p>TO BE SIGNED BY DULY AUTHORISED OFFICERS</p>
            </div>

            </div>


  <div >
                        <h2>  <a  class= "no-print" type="submit" class="btn btn-success btn-sm pull-right" href = "{{ url('/summary/create') }}">Back</a>
                        </h2></div>
</body>
</html>

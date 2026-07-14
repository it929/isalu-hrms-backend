<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="images/favicon.ico">
<title>ISALU HOSPITAL LIMITED...::...Report</title>
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<style type="text/css">
<!--
.style25 {	font-family: Verdana, Arial, Helvetica, sans-serif;
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
	/*background-image: url({{asset('Images/watermark.jpg')}});*/
}
.tblborder {
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-top-style: dotted;
	border-right-style: dotted;
	border-bottom-style: dotted;
	border-left-style: dotted;
}
.FED {	color: #008000;
}
body,td,th {
	font-size: 15px;
	font-family: Verdana, Geneva, sans-serif;
}
-->
table tr th
{
line-height:35px;
font-size:14px;
}
</style>

<style type="text/css" media="print">
   /* body
    {
        display: none;
        visibility: hidden;
    }*/
</style>

<script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>

<script type="text/javascript" language="javascript">
       /* $(function() {
            $(this).bind("contextmenu", function(e) {
                e.preventDefault();
            });
        });*/
</script>

<script type="text/javascript">
    /*$(document).ready(function(){

        function copyToClipboard() {
  // Create a "hidden" input
  var aux = document.createElement("input");
  // Assign it the value of the specified element
  aux.setAttribute("value", "Você não pode mais dar printscreen. Isto faz parte da nova medida de segurança do sistema.");
  // Append it to the body
  document.body.appendChild(aux);
  // Highlight its content
  aux.select();
  // Copy the highlighted text
  document.execCommand("copy");
  // Remove it from the body
  document.body.removeChild(aux);
  //ert("Print screen desabilitado.");
}

$(window).keyup(function(e){
  if(e.keyCode == 44){
    copyToClipboard();
  }
});




    });*/
</script>

 <script src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>
<body onload="lookup();">
<div align="center" style="width:80%; margin:auto;">
<table class="" border="0" align="left" cellpadding="0" cellspacing="0" style="margin-bottom:30px; margin-top:20px;">
  <tr>
  <th>NIGERIA FEDERAL GOVERNMENT</th>
  </tr>
  <tr><th>ADVICE OF DEDUCTION FROM SALARY</th></tr>
  <tr><th>{{strtoupper($reportTitle->desc)}} FOR THE MONTH OF {{strtoupper($selectedMonth)}}</th></tr>
  @if($banklist != '')
  <tr><th>ADDRESS: {{$banklist->bank}}, ABUJA</th></tr>
  @endif
  <tr><th>THE ATTACHED UP P.V NO. SCN/PE/207/19</th></tr>
  </table>
  <table class="table tblborder"  border="1" align="left" cellpadding="0" cellspacing="0">
  <tr>
  <th>SN</th>
  <th>NAME</th>
  @if($rtype == 'PEN')
  <th>GROSS</th>
  <th>10%</th>
  <th>8%</th>
  @else
  <th>AMOUNT</th>
  @endif
  @if($rtype == 'PEN')
  <th>TOTAL</th>
  @endif
  </tr>

  @php $k = 1; @endphp
  @foreach($payment as $list)
  <?php
  $tenTotal= 0;
  //$sal = DB::table('basicsalaryconsolidated')->where('grade','=',$list->grade)->where('step','=',$list->step)->where('employee_type','=',$list->employee_type)->first();
  $ten = (10/100) * ($list->Bs + $list->AEarn);
  $tenpercent = number_format($ten,2);
  $penTotal= $tenpercent + $list->amt;
  $tenTotal += $ten;
  ?>
  <?php
  $coopSaving = DB::table('tblotherEarningDeduction')->where('staffid','=',$list->staffid)->where('year','=',$year)->where('month','=',$month)->where('CVID','=',15)->first();
  $cooploan = DB::table('tblotherEarningDeduction')->where('staffid','=',$list->staffid)->where('year','=',$year)->where('month','=',$month)->where('CVID','=',16)->first();
  if(count($coopSaving) == '')
  {
  $saving = 0;
  }
  else
  {
  $saving = $coopSaving->amount;
  }
  if(count($cooploan) == 0)
  {
  $loan = 0;
  }
  else
  {
  $loan = $coopSaloan->amount;
  }
  ?>
  <tr>

  <td>{{$k++}}</td>
  <td>{{$list->fullname}}</td>
  @if($rtype == 'PEN')
  <td>{{number_format(($list->Bs + $list->AEarn),2)}}</td>

  <td>{{$tenpercent}}</td>
  @endif
  @if($rtype == 'gross')
  <td align="right">{{number_format($list->amt - $list->PEC, 2, '.', ',')}}</td>
  @else

   <td align="right">{{number_format($list->amt, 2, '.', ',')}}</td>
   @endif
   @if($rtype == 'PEN')
  <td>{{number_format($list->amt + $ten,2)}}</td>
  @endif
   @if($rtype == 'coop')
  <td>{{number_format($saving + $loan,2)}}</td>
  @endif

  </tr>
  @endforeach
  <tr style="border-top:2px solid #000;">
          <td>&nbsp;</td>
          <td><strong>Grand Total:</strong</td>
          @if($rtype == 'PEN')
          <td>&#8358;{{number_format($allPenGross,2)}}</td>
          <td>&#8358;{{number_format($tenpersum,2)}}</td>
          @endif
          @if($rtype == 'gross')
          <td align="right"><div align="right"><strong></strong>&#8358;{{number_format($totalSum - $jusuSum, 2, '.', ',')}}</div></td>
          @else
          <td align="right"><div align="right"><strong></strong>&#8358;{{number_format($totalSum, 2, '.', ',')}}</div></td>
          @endif
          @if($rtype == 'PEN')
          <td>&#8358;{{number_format($totalPen,2)}}</td>
          @endif
          </tr>
        <tr>
  </table>
      @php
      if($rtype == 'PEN')
       {
         $allSum = $totalPen;
        }
     else
     {
       $allSum = $totalSum;
     }

      @endphp
      <table width="1242" border="0" cellpadding="6" cellspacing="0">

          <td colspan="4" valign="top"><div align="left"><strong>AMOUNT IN WORDS:</strong>

 <script type="text/javascript">
	var amount = "";
	var amount = "{{number_format($allSum, 2, '.', ',')}}";
	var money = amount.split('.');
	function lookup()
	{
	var words;
	var naira = money[0];
	var kobo = money[1];


	var word1 = toWords(naira)+" naira";
	var word2 = ", "+toWords(kobo)+" kobo";
	if(kobo != "00")
	words = word1+word2;
	else
	words = word1;
	document.getElementById('result').innerHTML = words.toUpperCase();
	}

</script>
<span id="result"></span>
			 			 <br />
            </div></td
          ></tr>

        <tr>
          <td  style="margin-top:15px;" colspan="4" valign="top">Name:...........................................................................................Signature..........................................................................................</td>
        </tr>
        <tr>
          <td colspan="3" valign="top">(BLOCK LETTERS)</td>
          <td width="636" valign="top">(BLOCK LETTERS)</td>
        </tr>
      </table></td>
    </tr>
  </table>
</div>
</body>
</html>

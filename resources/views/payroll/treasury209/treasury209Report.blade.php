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
  @if(isset($Tr2019Head) && $Tr2019Head)
  <tr><th>{{strtoupper($Tr2019Head)}} FOR THE MONTH OF {{strtoupper($selectedMonth)}}</th></tr>
  @elseif(isset($reportTitle) && $reportTitle)
  <tr><th>{{strtoupper($reportTitle->desc)}} FOR THE MONTH OF {{strtoupper($selectedMonth)}}</th></tr>
  @endif
  @if(isset($banklist) && $banklist != '')
  <tr><th>ADDRESS: {{$banklist->bank}}, ABUJA</th></tr>
  @endif
  <tr><th>THE ATTACHED UP P.V NO. NJC/PE/207/19</th></tr>
  </table>
  
  @php
  // Calculate totals
  $totalSum = 0;
  $tenpersum = 0;
  $allPenGross = 0;
  $totalPen = 0;
  $jusuSum = 0;
  
  foreach($payment as $list) {
      if(isset($list->Vpara)) {
          $totalSum += $list->Vpara;
          
          // For pension calculation
          if(isset($rtype) && $rtype == 'PEN') {
              $ten = (10/100) * ($list->Bs + $list->AEarn);
              $tenpersum += $ten;
              $allPenGross += ($list->Bs + $list->AEarn);
              $totalPen += ($ten + $list->Vpara);
          }
          
          // For gross calculation
          if(isset($list->PEC)) {
              $jusuSum += $list->PEC;
          }
      }
  }
  @endphp
  
  <table class="table tblborder" border="1" align="left" cellpadding="0" cellspacing="0">
  <tr>
  <th>SN</th>
  <th>NAME</th>
  <th>FILE NO</th>
  <th>DESIGNATION</th>
  @if(isset($rtype) && $rtype == 'PEN')
  <th>GROSS</th>
  <th>10%</th>
  <th>8%</th>
  @else
  <th>AMOUNT</th>
  @endif
  @if(isset($rtype) && $rtype == 'PEN')
  <th>TOTAL</th>
  @endif
  </tr>	
  
  @php $k = 1; @endphp
  @foreach($payment as $list)
  @php
      // Skip if no Vpara value (no deduction for this type)
      if(!isset($list->Vpara) || $list->Vpara <= 0) {
          continue;
      }
      
      $tenTotal = 0;
      if(isset($rtype) && $rtype == 'PEN') {
          $ten = (10/100) * ($list->Bs + $list->AEarn); 
          $tenpercent = number_format($ten, 2);
          $penTotal = $tenpercent + $list->Vpara;
          $tenTotal += $ten;
      }
      
      // Get cooperative saving and loan if needed
      if(isset($rtype) && $rtype == 'coop') {
          $coopSaving = DB::table('tblotherEarningDeduction')->where('staffid','=',$list->staffid)->where('year','=',$year)->where('month','=',$month)->where('CVID','=',15)->first();
          $cooploan = DB::table('tblotherEarningDeduction')->where('staffid','=',$list->staffid)->where('year','=',$year)->where('month','=',$month)->where('CVID','=',16)->first();
          
          $saving = $coopSaving ? $coopSaving->amount : 0;
          $loan = $cooploan ? $cooploan->amount : 0;
      }
  @endphp
  
  <tr>
  <td>{{$k++}}</td>
  <td>{{$list->surname}} {{$list->first_name}} {{$list->othernames}}</td>
  <td>{{$list->fileNo ?? 'N/A'}}</td>
  <td>{{$list->designation ?? 'N/A'}}</td>
  
  @if(isset($rtype) && $rtype == 'PEN')
  <td>{{number_format(($list->Bs + $list->AEarn), 2)}}</td>
  <td>{{$tenpercent}}</td>
  @endif
  
  @if(isset($rtype) && $rtype == 'gross')
  <td align="right">{{number_format($list->Vpara - ($list->PEC ?? 0), 2, '.', ',')}}</td>
  @elseif(isset($rtype) && $rtype == 'coop')
  <td align="right">{{number_format($saving + $loan, 2, '.', ',')}}</td>
  @else
  <td align="right">{{number_format($list->Vpara, 2, '.', ',')}}</td>
  @endif
  
  @if(isset($rtype) && $rtype == 'PEN')
  <td>{{number_format(($list->Vpara + $ten), 2)}}</td>
  @endif
  </tr>  
  @endforeach 
  
  @if(count($payment) > 0)
  <tr style="border-top:2px solid #000;">
    <td>&nbsp;</td>
    <td colspan="3"><strong>Grand Total:</strong></td>
    @if(isset($rtype) && $rtype == 'PEN')
    <td>&#8358;{{number_format($allPenGross, 2)}}</td>
    <td>&#8358;{{number_format($tenpersum, 2)}}</td>
    @endif
    
    @if(isset($rtype) && $rtype == 'gross')
    <td align="right"><div align="right"><strong></strong>&#8358;{{number_format($totalSum - $jusuSum, 2, '.', ',')}}</div></td>
    @elseif(isset($rtype) && $rtype == 'coop')
    <td align="right"><div align="right"><strong></strong>&#8358;{{number_format($totalSum, 2, '.', ',')}}</div></td>
    @elseif(isset($rtype) && $rtype == 'PEN')
    <td>&#8358;{{number_format($totalSum, 2)}}</td>
    <td>&#8358;{{number_format($totalPen, 2)}}</td>
    @else
    <td align="right"><div align="right"><strong></strong>&#8358;{{number_format($totalSum, 2, '.', ',')}}</div></td>
    @endif
  </tr>
  @endif
  
  </table>
  
  @php
  if(isset($rtype) && $rtype == 'PEN') {
      $allSum = $totalPen;
  } else {
      $allSum = $totalSum;
  }
  @endphp
  
  @if($allSum > 0)
  <table width="1242" border="0" cellpadding="6" cellspacing="0">
    <tr>
      <td colspan="4" valign="top"><div align="left"><strong>AMOUNT IN WORDS:</strong> 
        <script type="text/javascript">
          var amount = "";
          var amount = "{{number_format($allSum, 2, '.', ',')}}";
          var money = amount.split('.');
          function lookup() {
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
      </div></td>
    </tr>
    
    <tr>
      <td style="margin-top:15px;" colspan="4" valign="top">Name:...........................................................................................Signature..........................................................................................</td>
    </tr>
    <tr>
      <td colspan="3" valign="top">(BLOCK LETTERS)</td>
      <td width="636" valign="top">(BLOCK LETTERS)</td>
    </tr>
  </table>
  @else
  <div style="text-align: center; margin-top: 20px; padding: 20px; border: 1px solid #ddd;">
    <h4>No records found for the selected criteria</h4>
    <p>Please try different search parameters.</p>
  </div>
  @endif
</div>
</body>
</html>
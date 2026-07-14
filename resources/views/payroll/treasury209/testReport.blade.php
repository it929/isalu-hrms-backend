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
<script src="{{asset('assets/js/table2excel.js')}}"></script>
<script type="text/javascript" language="javascript">
       /* $(function() {
            $(this).bind("contextmenu", function(e) {
                e.preventDefault();
            });
        });*/ 
</script>

<script type="text/javascript">
        function Export() {
            $("#tableData").table2excel({
                filename: "{{$selectedMonth}}_{{$year}}_NHF.xls"
            });
        }
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
<table class="" border="0" align="left" cellpadding="0" cellspacing="0" style="margin-bottom:30px; margin-top:20px;" >
  
  </table>
  <table class="table tblborder"  border="1" align="left" cellpadding="0" cellspacing="0" id="tableData" style="margin-top: 20px;">
    <tr>
      <th colspan="13" class="text-center">ISALU HOSPITAL LIMITED</th>
      </tr>
      <tr><th colspan="13" class="text-center">ADVICE OF DEDUCTION FROM SALARY</th></tr>
      @if($reportType == 'TAX')
      <tr><th colspan="13" class="text-center">{{$payeeCaption->address}} DEDUCTION FOR THE MONTH OF {{strtoupper($selectedMonth)}}, {{strtoupper($selectedYear)}} {{isset($divisionName) ? ' - ' . strtoupper($divisionName) : ''}} </th></tr>
      @else
      <tr><th colspan="13" class="text-center"> {{strtoupper($reportType->addressName)}} DEDUCTION FOR THE MONTH OF {{strtoupper($selectedMonth)}}, {{strtoupper($selectedYear)}} {{isset($divisionName) ? ' - ' . strtoupper($divisionName) : ''}}</th></tr>
      @endif
      <tr>
  <tr>
  <th>SN</th>
   @if($reportType == 'NHF')
  <th>EMPLOYER</th>
  <th>EMPLOYER NUMBER</th>
  <th>LAST NAME</th>
  <th>FIRST NAME</th>
  <th>MIDDLE NAME</th>
  <th>NHF NUMBER</th>
  @endif
  @if($reportType != 'NHF')
  <th>NAME</th>
  @if($Tr2019Head == 'PENSION')
    <th>RANK</th>
  @endif
  <th>GRADE</th>
  <th>STEP</th>
  @if($Tr2019Head == 'PENSION')
  <th>DATE OF BIRTH</th>
  <th>DATE OF FIRST APPT</th>
  <th>DATE OF PRESENT APPT</th>
  {{-- <th>DATE OF RETIREMENT</th>
  <th>P.F.A</th> --}}
  {{-- <th>PIN NO. PEN</th> --}}
  @endif
  @endif
 
  @if($Tr2019Head == 'PENSION')
  <th>GROSS</th>
  <th>10%</th>
  @endif
  @if($Tr2019Head == 'PENSION')
  <th>8%</th>
  <th> TOTAL </th>
  <th>OTHER REMARKS</th>
  @else
  <th>AMOUNT @if($Tr2019Head == 'UD') (4% of Basic) @endif</th>
  @endif
  
  </tr>	
  
  @php $k = 1; $loanTotal =0; $total = 0;
  $totalPen =0;
  $totalTenPer =0;
  $totalCont  =0;
  $totalBs = 0;
  $totalVol = 0;
   @endphp
  @foreach($payment as $list)
  <?php 
  $tenPercent = ($list->PEN*10)/8; //(10/100) * $list->Bs;
  
   ?>
  <tr>
 
  <td>{{$k++}}</td>
  @if($reportType == 'NHF')
  <td>ISALU HOSPITAL LIMITED</td>
  <td>0-200011466</td>
  <td>{{$list->surname}}</td>
  <td>{{$list->first_name}}</td>
  <td>{{$list->othernames}}</td>
  <td>{{$list->nhfNo}}</td>
  @endif
  @if($reportType != 'NHF')
  <td>{{$list->name}}</td>

  @if($Tr2019Head == 'PENSION')
    <td>{{$list->designation}}</td>
  @endif

  <td>{{$list->grade}}</td>
  <td>{{$list->step}}</td>
  @if($Tr2019Head == 'PENSION')
    <td>{{$list->dob}}</td>
    <td>{{$list->appointment_date}}</td>
    <td>{{$list->date_present_appointment}}</td>
    {{-- <td>retirement date</td>
    <td>p.f.a</td> --}}
    {{-- <td>pension No.</td> --}}
  @endif
  @endif
 
  @if($Tr2019Head == 'PENSION')
  <td class ="text-right"  <?php $totalBs += $list->gross - $list->PEC; ?> >{{number_format($list->gross - $list->PEC, 2, '.', ',')}}</td>
  <td class ="text-right"  <?php $totalTenPer += $tenPercent; ?> >@if($list->PEN > 0){{number_format($tenPercent, 2, '.', ',')}} @else 0.00 @endif</td>
  <td class ="text-right"  <?php $totalCont += $list->Vpara; ?> >{{number_format($list->Vpara, 2, '.', ',')}}</td>
  <td class ="text-right"  <?php $totalPen += $list->Vpara + $tenPercent; ?> > @if($list->PEN > 0){{number_format($list->Vpara + $tenPercent, 2, '.', ',')}} @else 0.00 @endif</td>
  <td></td>
  @else
   <td align="right" <?php $total += $list->Vpara; ?>>{{number_format($list->Vpara, 2, '.', ',')}}</td>
  @endif
 
  </tr>
   
  @endforeach 
   <tr>
 
  <td></td>
  @if($reportType == 'NHF')
  <td></td>
  <td></td>
  <td></td>
  @endif
  <td></td>
  <td></td>
  <td></td>
    @if($Tr2019Head == 'PENSION')
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td class ="text-right" align="right"><strong>{{number_format($totalBs, 2, '.', ',')}}</strong></td>
    <td class ="text-right" align="right"><strong>{{number_format($totalTenPer, 2, '.', ',')}}</strong></td>
    <td class ="text-right"  align="right"><strong>{{number_format($totalCont, 2, '.', ',')}}</strong></td>
    {{-- <td class ="text-right"  align="right"><strong>{{number_format($totalVol, 2, '.', ',')}}</strong></td> --}}
    <td class ="text-right"  align="right"><strong>{{number_format($totalPen + $totalVol, 2, '.', ',')}}</strong></td>
    @else
  <td align="right"><strong>{{number_format($total, 2, '.', ',')}}</strong></td>
  @endif
  </tr>   
  </table>
  <table class="table tblborder hidden-print"  border="1" align="left" cellpadding="0" cellspacing="0">
  <tr><td colspan="3">
          <div class="no-print hidden-print" align="center">
                   <input type="button" class="hidden-print" id="btnExport" value="Export to Excel" onclick="Export()" />    
                    </div>
                    
        </td></tr>
  </table>
      
      <table width="1242" border="0" cellpadding="6" cellspacing="0">
        
        
          <td colspan="4" valign="top"><div align="left"><strong>AMOUNT IN WORDS:</strong> 
            
 <script type="text/javascript">
	var amount = "";
	var amount = "{{number_format($total, 2, '.', ',')}}";
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
  <br /><br />
    <div align="center"><a class="btn btn-success" href="{{ URL::previous() }}">Go Back</a></div>
    <br /><br />
</div>
</body>
</html>
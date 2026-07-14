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
                filename: "{{$selectedMonth}}_{{$selectedYear}}_NHF.xls"
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
  <tr>
  <th>ISALU HOSPITAL LIMITED</th>
  </tr>
  <tr><th>ADVICE OF DEDUCTION FROM SALARY</th></tr>
  <tr><th> {{strtoupper('NHIS')}} DEDUCTION FOR THE MONTH OF {{strtoupper($selectedMonth)}}, {{strtoupper($selectedYear)}}</th></tr>
  <tr><th></th></tr>

  <!--<tr><th>THE ATTACHED UP P.V NO. NJC/PE/207/19</th></tr>-->
  </table>
  <table class="table tblborder"  border="1" align="left" cellpadding="0" cellspacing="0" id="tableData">
  <tr>
  <th>SN</th>
  <th>NAME</th>
  <th>GRADE</th>
  <th>STEP</th>
  <th>BASIC (Consolidated)</th>
  <th>AMOUNT (5% of Basic)</th>


  </tr>

  @php $k = 1; $loanTotal =0; $total = 0;
  $totalPen =0;
  $totalFivePer =0;
  $totalCont  =0;
  $totalBs = 0;
   @endphp
  @foreach($nhis as $list)
  <?php
  $fivePercent = (5/100) * ($list->TEarn - $list->PEC - $list->SOT);
   ?>
  <tr>

  <td>{{$k++}}</td>
  <td>{{$list->name}}</td>
  <td>@if($list->staffid == 1) {{'Consolidated'}} @else {{$list->grade}} @endif</td>
  <td>@if($list->staffid == 1) {{'Consolidated'}} @else {{$list->step}} @endif</td>

  <td class ="text-right"  <?php $totalBs += $list->Bs; ?> >{{number_format($list->Bs, 2, '.', ',')}}</td>
  <td class ="text-right"  <?php $totalFivePer += $fivePercent; ?> >{{number_format($fivePercent, 2, '.', ',')}}</td>


  </tr>

  @endforeach
   <tr>

  <td></td>
  <td></td>
  <td></td>
  <td></td>
   <td align="right"><strong>{{number_format($totalBs, 2, '.', ',')}}</strong></td>
  <td align="right"><strong>{{number_format($nhisNew, 2, '.', ',')}}</strong></td>

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
	var amount = "{{number_format($nhisNew, 2, '.', ',')}}";
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

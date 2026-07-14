<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="images/favicon.ico">
<title>ISALU HOSPITAL LIMITED...::...Report T209</title>

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
	background-image: url({{asset('Images/watermark.jpg')}});
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
</style>
 <script src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>
<body onload="lookup();">
<div align="center">
  <table width="1100" border="0" align="left" cellpadding="0" cellspacing="0">
    <tr>
      <td colspan="3"><div align="center">
        <h2 class="FED">FEDERAL GOVERNMENT OF NIGERIA</h2>
          ADVICE OF DEDUCTION FROM SALARY
      </div></td>
    </tr>
    <tr>
      <td width="808"><div align="left">
        <p><strong>Subhead</strong>: {{$scode->subhead}}<br />
          <strong>Classification Code</strong>: {{$scode->classcode}}<br />
          <strong>{{$month}}, {{$year}}</strong> <br/>
          <strong>{{$payeAddress}}</strong><br /><br />
           <strong>Bank</strong>: {{$bank}}<br />
          </p>
       
      </div></td>
      <td width="320" colspan="2" valign="top"><div align="left">
        <strong>TF. 209(1962 R)</strong><br />
        Date Printed: {{ date('l F d, Y') }}<br />
        <strong>
        </strong> <br />
      </div></td>
    </tr>
    <tr>
      <td colspan="3"><div align="right"><a href="userArea.php">Home</a></div></td>
    </tr>
    <tr>
      <td colspan="3"><div align="center"><strong>{{$scode->addressName}}</strong></div></td>
    </tr>
    <tr>
      <!--<td colspan="3"><strong>{{$division}} Division</strong></td>-->
    </tr>
    <tr>
      <td colspan="3"><table width="858" border="1" align="center" cellpadding="6" cellspacing="0"  class="tblborder">
        <tr>
          <td width="33"><div align="center"><strong>S/N</strong></div></td>
          <td width="415"><div align="left"><strong>FULL NAME</strong></div>
<div align="center"></div>            <div align="center"></div></td>
          <td width="342"><div align="center"><strong>AMOUNT</strong>(&#8358;)</div></td>
          </tr>
            @php $serial = 1; @endphp
            @foreach($details as $list)
           @if(count($details) > 0)
            <tr>
          <td align="center">{{$serial++}}</td>
          <td align="left">{{$list->name}}</td>
          <td align="right">{{ number_format($list->$getvalue, 2, '.', ',')}}</td>
          </tr>
         @endif
            @endforeach 

		          
      </table>
      
      <table width="1242" border="0" cellpadding="6" cellspacing="0">
        <tr>
          <td width="150">&nbsp;</td>
          <td width="419" align="right"></td>
         
          <td colspan="2" align="center"><div align="left"><strong>Grand Total:</strong>&#8358;{{number_format($total, 2, '.', ',')}}</div></td>
          
          </tr>
        <tr>
          <td>&nbsp;</td>
          <td colspan="3">.....................................................................................................................................................................................................<br />
            <br /></td>
          </tr>
        <tr>
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
          <td colspan="4" valign="top">Signature of Paying in Officer:................................................................Signature of Receiving  Officer: .........................................................................</td>
        </tr>
        <tr>
          <td colspan="4" valign="top">Name:..................................................................................................Name..............................................................................................................</td>
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
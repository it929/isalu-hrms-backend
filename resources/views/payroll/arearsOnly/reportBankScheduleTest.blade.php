<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="images/favicon.ico">
<title>ISALU HOSPITAL LIMITED...::...Bank Schedule</title>

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
body,td,th {
	font-size: 15px;
	font-family: Verdana, Geneva, sans-serif;
}
-->
</style>
<script src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>

<body onLoad="lookup();">
<div align="center">
  <table width="1300" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td colspan="3"><div align="center"><strong>
        NATIONAL INDUSTRIAL COURT
        <br />
      </strong></div></td>
    </tr>
    <tr>
      <td colspan="3"><div align="center">C.P.O<br />
        10, PORT HARCOURT CRESENT, AREA 11, GARKI, ABUJA<br />
      BANK SCHEDULE</div></td>
    </tr>
    <tr>
      <td width="730"><div align="left">The Branch Manager,<br />
        First Bank of Nigeria,<br />
        Jos Street, Area 3,<br />
Garki, Abuja, Nigeria. <br />
      </div></td>
      <td width="405" colspan="2"><div align="left">
        <p>To be attached to P.V. No.:<br />
        Month:@if(session('schmonth'))
                 {{ session('schmonth') }}
              @endif<br />
          Date Printed: @if(session('date'))
                      {{ session('date') }}
                      @endif<br />
          Division:  @if(session('division'))
                      <strong>
                      {{ session('division') }}
                    </strong>
                    @endif
          <br />
        
        Bank: <strong> @if(session('bank'))
                    {{ session('bank') }}
                    @endif
              </strong></p>
      </div></td>
    </tr>
    <tr>
      <td colspan="3">Schedule of accounts to be credited on the morning ___________________. In respect of the enclose remittance of #______________<br /></td>
    </tr>
    
    
    <tr>
      <td colspan="3"><table width="988" border="1" align="center" cellpadding="0" cellspacing="0" background="images/bg_1.png" class="tblborder">
        <tr>
          <td width="50"><strong>S/N</strong></td>
          <td width="371"><div align="center"><strong>BENEFICIARY {{session('serialNos')}}</strong></div> <div align="center"></div> <div align="center"></div></td>
          <td width="241"><strong>BRANCH</strong></td>
          <td width="175"><div align="center"><strong>ACC NUMBER</strong></div></td>
          <td width="159"><div align="center"><strong>AMOUNT</strong>(&#8358;)</div></td>
          </tr>
            <?php  $sn = 1;?>
            <?php $total =0; $counter=session('serialNos');?>
            @foreach ($schedule_detail as $reports)
          <tr>
          <td align="left">{{$counter }}</td>
          <td align="left">{{ $reports->name }}</td>
          <td align="left">{{ $reports->bank_branch }}</td>
          <td align="center">{{ $reports->AccNo }}</td>
          <td align="center">{{ number_format( $reports->netpay , 2, '.', ',') }}</td>
          </tr>
            @php
             
            $counter=$counter+1;
            $total = $total + $reports->netpay ;
            @endphp    
            @endforeach		          
      </table>
      
      <div class="hidden-print">
         {{$schedule_detail->links()}}
      </div>
      
      <table width="1202" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="1181"><div align="center"></div></td>
        </tr>
        <tr>
          <td><table width="1200" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td width="391"></td>
              <td width="110">&nbsp;</td>
              <td width="330">                <br />
                Total: <b>&#8358;{{number_format($total, 2, '.', ',')}}</b><br>			<script type="text/javascript">
			//var amount = "";
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

             </td>
            </tr>
            <tr>
              <td colspan="3"><p>&nbsp;</p>
                <p><strong>AMOUNT IN WORDS:</strong> <span id="result"></span></p></td>
              </tr>
            <tr>
              <td colspan="3"><br />
              <strong>CERTIFICATE </strong>It is hereby certified that the above accounts will be credited on the date indicated to the respective accounts of the persons named above</td>
              </tr>
            <tr>
              <td>&nbsp;</td>
              <td colspan="2">&nbsp;</td>
              </tr>
            <tr>
              <td>&nbsp;</td>
              <td colspan="2">______________________Manager</td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td>..............................................................................................................................................................................................................................................................</td>
        </tr>
        
      </table></td>
    </tr>
  </table>
 <form>
    <div align="center"></div>
  </form>
</div>
</body>
</html>
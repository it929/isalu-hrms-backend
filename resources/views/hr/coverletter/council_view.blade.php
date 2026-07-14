<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.second-spacing { line-height:2.5px;
}
-->
</style>

<script>

</script>

</head>

<body onload="lookup();">
<p>&nbsp;</p>
<p>&nbsp;</p>
<input type="hidden" class="form-control" id="number" value="{{ $total }}">
<input type="hidden" class="form-control" id="total_six" value="{{ $total_six }}">
<input type="hidden" class="form-control" id="frac_number" value="{{ $fractions }}">

<input type="hidden" class="form-control" id="tax_number" value="{{ $tax }}">
<input type="hidden" class="form-control" id="nhis_six" value="{{ $nhis_six }}">
<input type="hidden" class="form-control" id="taxfrac_number" value="{{ $taxfractions }}">

<table width="650" height="649" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size:19px">
  <!--<tr>
    <td height="23">&nbsp;</td>
    <td colspan="2" align="center" style="font-weight:bold">ISALU HOSPITAL LIMITED, ABUJA</td>
    <td>&nbsp;</td>
  </tr>

  <tr>
    <td height="23">&nbsp;</td>
    <td colspan="2" align="center" style="font-weight:bold">MEMORANDUM</td>
    <td>&nbsp;</td>
  </tr>
  -->

  <br style="margin-top:-10px;">
  <tr>
    <td width="25" height="23">&nbsp;</td>
    <td colspan="2" style="border-bottom:none #ccc;border-width:thin;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold">The Secretary, Through: Director (F &amp; A)</span></td>
    <td width="43">&nbsp;</td>
  </tr>
  <tr>
    <td height="23">&nbsp;</td>
    <td colspan="2" style="border-bottom:none #ccc;border-width:thin;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold;">Principal Accountant(Salary)</span></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="27">&nbsp;</td>
    <td colspan="2" style="border-bottom:none #ccc;border-width:thin;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold">Council Members Salary for {{ ucwords($M) }}, {{ $year }}</span></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="23">&nbsp;</td>
    <td colspan="2" style="border-bottom:none #ccc;border-width:thin;padding-top:6px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold;"><?php echo date('jS') ?> {{ ucwords($M) }}, {{ $year }}</span></td>
    <td>&nbsp;</td>
  </tr>

  <tr>
    <td height="200">&nbsp;</td>
    <td colspan="2" style="text-align:justify"><br><p>The above subject refers:</p>
     <p>
            1.&nbsp; Kindly give approval for the sum of &#x20A6;{{ number_format($total,2) }} (<span id="result" style="font-weight:bold;font-style:italic"></span>) only,
            being salary of Ten (10) Council Members for the Month of {{ ucwords($M) }}, {{ $year }}
        </p>

        <p>
            2.&nbsp; Tax for the month amounts to &#x20A6;{{ number_format($tax+$council_tax,2) }} (<span id="resultTAX" style="font-weight:bold;font-style:italic"></span>) only
        </p>

        <p>
            3.&nbsp;Humbly, submited for your consideration and approval, please:
        </p>
    </td>
    <td>&nbsp;</td>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td colspan="2" valign="top"><p align="center"><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;<strong>BILIKISU MOHAMMED A. (Mrs.)</strong></p>
    <p align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Head Salary </p></td>
    <td valign="top">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
<!--Convert Number to word -->
<script type="text/javascript">

   // Convert numbers to words
// copyright 25th July 2006, by Stephen Chapman http://javascript.about.com
// permission to use this Javascript on your web page is granted
// provided that all of the code (including this copyright notice) is
// used exactly as shown (you can change the numbering system if you wish)
// American Numbering System
var th = ['', 'thousand', 'million', 'billion', 'trillion'];
// uncomment this line for English Number System
// var th = ['','thousand','million', 'milliard','billion'];

var dg = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
var tn = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
var tw = ['twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

function toWords(s) {

    s = s.toString();
    s = s.replace(/[\, ]/g, '');
    if (s != parseFloat(s)) return 'not a number';
    var x = s.indexOf('.');
    if (x == -1) x = s.length;
    if (x > 15) return 'too big';
    var n = s.split('');
    var str = '';
    var sk = 0;
    for (var i = 0; i < x; i++) {
        if ((x - i) % 3 == 2) {
            if (n[i] == '1') {
                str += tn[Number(n[i + 1])] + ' ';
                i++;
                sk = 1;
            } else if (n[i] != 0) {
                str += tw[n[i] - 2] + ' ';
                sk = 1;
            }
        } else if (n[i] != 0) {
            str += dg[n[i]] + ' ';
            if ((x - i) % 3 == 0) str += 'hundred and ';
            sk = 1;
        }
        if ((x - i) % 3 == 1) {
            if (sk) str += th[(x - i - 1) / 3] + ' ';
            sk = 0;
        }
    }
    if (x != s.length) {
        var y = s.length;
        str += 'point ';
        for (var i = x + 1; i < y; i++) str += dg[n[i]] + ' ';
    }
    return str.replace(/\s+/g, ' ');
}

    function lookup()
    {
        var amount = "";
        var amount = "<?php echo number_format($netpay, 2, '.', '') ; ?>";
        var money = amount.split('.');//

        ////Remove all these .... VAT
       // var amountVAT = "";
        //var moneyVAT = amountVAT.split('.');

        //TAX
        var amountTAX = "";
        var amountTAX = "<?php echo number_format($tax+$council_tax, 2, '.', '') ; ?>";
        var moneyTAX = amountTAX.split('.');

        //////////////////
    	var words;
        var naira = money[0];
        var kobo = money[1];
        var word1 = toWords(naira)+"naira";
        var word2 = ", "+toWords(kobo)+" kobo";
        if(kobo != "00")
            words = word1 + word2;
        else
            words = word1;
        //
        	 var getWord = words.toUpperCase();
			 var parternRule1 = /HUNDRED AND NAIRA/ig;
			 var parternRule2 = /HUNDRED AND THOUSAND NAIRA/ig;
			 var instance1 = parternRule1.test(getWord);
			 var instance2 = parternRule2.test(getWord);
			 if((instance1))
			 {
			 	document.getElementById('result').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
			 	////Remove this .... document.getElementById('result2').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
			 }else if((instance2))
			 {
			 	document.getElementById('result').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
			 	////Remove this .... document.getElementById('result2').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
			 }else
			 {
			  	document.getElementById('result').innerHTML = getWord;
			  	//Remove this .... document.getElementById('result2').innerHTML = getWord;
			 }


        //TAX
        //alert(moneyTAX);
        var wordTAXs;
        var naira = moneyTAX[0];
        var kobo = moneyTAX[1];
        var word1 = toWords(naira)+"naira";
        var word2 = ", "+toWords(kobo)+" kobo";
        if(kobo != "00")
            wordTAXs = word1 + word2;
        else
            wordTAXs = word1;
        //
        	 var getWord = wordTAXs.toUpperCase();
			 var parternRule1 = /HUNDRED AND NAIRA/ig;
			 var parternRule2 = /HUNDRED AND THOUSAND NAIRA/ig;
			 var instance1 = parternRule1.test(getWord);
			 var instance2 = parternRule2.test(getWord);
			 if((instance1))
			 {
			 	document.getElementById('resultTAX').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
			 //Remove this .... 	document.getElementById('resultTAX2').innerHTML = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
			 }else if((instance2))
			 {
			 	document.getElementById('resultTAX').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
			 	//Remove this .... document.getElementById('resultTAX2').innerHTML = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
			 }else
			 {
			  	document.getElementById('resultTAX').innerHTML = getWord;
			  	//Remove this .... document.getElementById('resultTAX2').innerHTML = getWord;
			 }
		//

    }
</script>


</body>
</html>

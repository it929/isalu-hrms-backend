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
.tables tr td, .tables
{
padding:6px;
border:1px solid #333;
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
/* border: 1px solid #06C;
color:#06c; */
}
</style>
 <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}">
</script>
<body background="{{asset('Images/nicn_bg.jpg')}}"  onload="lookup(); gross();">
  <div align="center">
    <strong>
      <br />
      <br />
      <br />
      ISALU HOSPITAL LIMITED{{ isset($divisionName) ?  ', '. strtoupper($divisionName) : '' }}
      <h4>SALARY PROJECTION FOR THE MONTH OF {{isset($month) ? $month . ', ' : ''}} {{isset($year) ? $year .'.' : ''}}</h4>
    </strong>
  </div>
  <br />


  @php
    $totalEmolument = 0.0;
    $totalPension = 0.0;
    $totalStaff = 0.0;
  @endphp


  <div align="center">
      <table class="table table-condense table-responsive" border="1" align="center" cellpadding="0" cellspacing="0" id="tableData">
      <tr class="text-center">
        <td><strong>SN</strong></td>
        <td><strong>GRADE</strong></td>
        <td><strong>NO. OF STAFF</strong></td>
        <td><strong>TOTAL MONTHLY EMOLUMENT</strong></td>
      </tr>
    
      
      @if(isset($salary_detail) && $salary_detail)
        @foreach ($salary_detail as $key=>$projection)
          <tr class="text-center">
            <td>{{ ($key + 1) }}</td>
            <td>{{ $projection->grade }}</td>
            <td><?php $totalStaff += $projection->totalStaffNo; ?> {{ $projection->totalStaffNo }}</td>
            <td>
              <?php 
                $totalPension += $projection->totalMonthlyPEN; 
                $totalEmolument += $projection->totalMonthlyEmo; 
              ?> 
              {{ number_format($projection->totalMonthlyEmo, 2) }}</td>
          </tr>
        
          @endforeach
        @endif

      <tr class="text-center">
          <td colspan="2"><strong>Total</strong></td>
          <td><strong>{{ $totalStaff }}</strong></td>
          <td><strong>{{ number_format($totalEmolument, 2) }}</strong></td>
      </tr>
        <tr border="0" class="no-print">
            <td colspan="4">
              <br />
            </td>
        </tr>

        <tr border="0" class="no-print">
          <td colspan="4">
            <div align="right">
              <div class="row">
                <div class="col-md-9">
                  <div style="font-weight: bolder; padding: 2px;">LESS R. S. A. = &#8358;{{ number_format($totalPension, 2)}}</div>
                </div>
                <div class="col-md-9">
                  <div style="font-weight: bolder;  padding: 2px;"> SUB-TOTAL = &#8358;{{ number_format($totalEmolument - $totalPension, 2)}}</div>
                </div>
                <div class="col-md-9">
                  <div style="font-weight: bolder;  padding: 2px;"> NET TOTAL = &#8358;{{ number_format($totalEmolument - $totalPension, 2)}}</div>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </table>
      
      <br />
      <div class="no-print" align="center">
        <input type="button" class="hidden-print" id="btnExport" value="Export to Excel" onclick="ExportToExcel('xlsx')" />    
      </div>
  </div>


  <div>
      <h2>  
        <a  class= "no-print" type="submit" class="btn btn-success btn-sm pull-right" href = "{{ url('/report/salary-projection') }}">Back</a>
      </h2>
  </div>

    <script src="{{asset('assets/js/jQuery-2.2.0.min.js')}}"></script>
    <script src="{{asset('assets/js/table2excel.js')}}"></script>

      <script type="text/javascript">
        function ExportToExcel() {
          //$("#btnExport").hide();
            $("#tableData").table2excel({
                filename: "{{isset($month) ? $month: ''}}_{{isset($year) ? $year : ''}}_{{isset($divisionName) ? $divisionName : ''}}_salary_projection.xls"
            });
            $("#tableData").excelexportjs({
              containerid: "tableData", datatype: 'table'
            });
            //$("#btnExport").show();
        }
      </script>

</body>
</html>

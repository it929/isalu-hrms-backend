@extends('layouts.layout')
@section('pageTitle')
NHF Reports
@endsection
@section('content')
<form method="post" action="{{ url('/nhf-report/list') }}">
  <div class="box-body" style="background:#FFF;">
    <div class="row">
      {{ csrf_field() }}
      
      <!-- Action Buttons -->
      <div class="col-md-12 text-right" style="margin-bottom: 20px;">
        <button type="button" class="btn btn-success" onclick="exportToExcel()">
          <i class="fa fa-file-excel-o"></i> Export to Excel
        </button>
        <button type="button" class="btn btn-primary" onclick="printReport()">
          <i class="fa fa-print"></i> Print Report
        </button>
         @foreach($attachments as $attachment)
            <a href="{{ asset($attachment->attachment) }}" class="btn btn-info" target="_blank">
                <i class="fa fa-eye"></i> View Remittance Receipts
            </a>
        @endforeach
      </div>

      <!-- Main Content -->
      <div id="mainContent">
        <h2 class="text-center">ISALU HOSPITAL LIMITED</h2>
        <h3 class="text-center">MONTHLY NHF CONTRIBUTION SCHEDULE</h3>
        
        <div class="col-md-12">
          <div class="employer-info">
            <div class="init">
              <strong>EMPLOYER NAME:</strong> <span> ISALU HOSPITAL LIMITED</span>
            </div>
            <div class="init">
              <strong>EMPLOYER NHF NUMBER:</strong> <span> </span>
            </div>
            <div class="init">
              <strong>MONTH/YEAR:</strong> <span> {{$month}}/{{$year}}  </span>
            </div>
            <div class="init">
              <strong>AMOUNT PAID:</strong> <span>{{number_format($totalSum,2)}} </span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-condensed table-bordered" id="nhfTable">
              <thead class="text-gray-b">
                <tr>
                  <th>S/N</th>
                  <th>NAME</th>
                  <th>FILE NO.</th>
                  <th>ORGANIZATION</th>
                  <th>NHF NO.</th>
                  <th>BANK</th>
                  <th>ACCOUNT NO.</th>
                  <th>MOBILE NUMBER</th>
                  <th>EMAIL ADDRESS</th>
                  <th>BASIC SALARY</th>
                  <th>AMOUNT</th>
                  <th>REMARK</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $i=1;
                @endphp

                @foreach($nhf as $list)
                  <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{$list->surname}} {{$list->first_name}} {{$list->othernames}}</td>
                    <td>{{$list->fileNo}}</td>
                    <td>ISALU HOSPITAL LIMITED</td>
                    <td>{{$list->nhfNo}}</td>
                    <td>{{$list->bank_name}}</td> 
                    <td>{{$list->AccNo}}</td> 
                    <td>{{$list->phone}}</td>
                    <td>{{$list->email}}</td>
                    <td>{{number_format($list->Bs,2)}}</td>
                    <td class="text-right">{{number_format($list->NHF,2)}}</td>
                    <td>{{$list->month}} {{$list->year}} NHF Contribution</td>
                  </tr>
                @endforeach
                  <tr>
                  <td colspan="10"><strong>TOTAL:</strong></td>
                  <td class="text-right"><strong>{{number_format($totalSum,2)}}</strong></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Hidden form for Excel export -->
<form id="excelExportForm" method="post" action="{{ url('/nhf-report/export-excel') }}" style="display: none;">
  @csrf
  <input type="hidden" name="month" value="{{ $month }}">
  <input type="hidden" name="year" value="{{ $year }}">
</form>
@endsection

@section('styles')
<style>
  .init {
    line-height:30px;
  }
  .table-responsive{
    max-height: 800px;
    overflow: auto;
  }
</style>
@endsection

@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $("#court").on('change',function(e){
      e.preventDefault();
      var id = $(this).val();
      $token = $("input[name='_token']").val();
      $.ajax({
        headers: {'X-CSRF-TOKEN': $token},
        url: murl +'/session/court',
        type: "post",
        data: {'courtID':id},
        success: function(data){
          location.reload(true);
        }
      });
    });
  });

  function printReport() {
  // Store original content and styles
  const originalContent = document.body.innerHTML;
  const originalTitle = document.title;
  
  // Get the main content to print
  const printContent = document.getElementById('mainContent').innerHTML;
  
  // Create print-friendly HTML
  const printDocument = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>NHF Report - {{$month}}/{{$year}}</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          margin: 20px;
          color: #000;
          background: #fff;
        }
        .text-center {
          text-align: center;
        }
        h2 {
          font-size: 20px;
          margin: 10px 0 5px 0;
        }
        h3 {
          font-size: 16px;
          margin: 5px 0 15px 0;
        }
        .employer-info {
          margin-bottom: 20px;
          line-height: 25px;
        }
        .init {
          line-height: 25px;
        }
        .table-responsive {
          width: 100%;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          font-size: 10px;
          margin-top: 10px;
        }
        th, td {
          border: 1px solid #000;
          padding: 5px;
          text-align: left;
        }
        th {
          background-color: #f0f0f0;
          font-weight: bold;
        }
        .text-right {
          text-align: right;
        }
        @media print {
          body {
            margin: 0.5cm;
          }
          @page {
            size: landscape;
            margin: 0.5cm;
          }
          table {
            font-size: 9px;
          }
          th, td {
            padding: 3px;
          }
        }
      </style>
    </head>
    <body>
      ${printContent}
    </body>
    </html>
  `;
  
  // Replace current page content with print version
  document.body.innerHTML = printDocument;
  
  // Change page title for printing
  document.title = 'NHF Report - {{$month}}/{{$year}}';
  
  // Print the document
  window.print();
  
  // Restore original content after printing
  setTimeout(function() {
    document.body.innerHTML = originalContent;
    document.title = originalTitle;
    
    // Re-attach any event listeners if needed
    // You might need to reinitialize some scripts here
    if (typeof $ !== 'undefined') {
      $(document).ready(function(){
        // Re-initialize any jQuery functions if needed
      });
    }
  }, 500);
}

  // Export to Excel Function
  function exportToExcel() {
    try {
      const table = document.getElementById('nhfTable');
      const workbook = XLSX.utils.table_to_book(table, {sheet: "NHF Report"});
      XLSX.writeFile(workbook, `NHF_Report_{{ $month }}_{{ $year }}.xlsx`);
    } catch (error) {
      console.error('Client-side export failed:', error);
      document.getElementById('excelExportForm').submit();
    }
  }
</script>
@endsection
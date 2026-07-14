@extends('layouts_procurement.app')
@section('pageTitle')
  
@endsection
@section('content')


        
<div class="box box-default" style="padding-bottom:0px; margin-bottom:0px;padding-top:0px; margin-top:0px;">
  <div class="box-body box-profile">
    <div class="box-header with-border">
      <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
    </div>
     <div class="box-body" style="padding-bottom:0px; margin-bottom:0px;">
    <div class="row" >
      <div class="col-md-2"><img src="{{asset('assets/image-logo/njc-logo.jpg')}}" class="img-responsive responsive" style="width:100%; height:auto;"></div>
      <div class="col-md-8">
        <div>
          <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
          <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED</strong></h4>
         <h4 class="text-center text-success"><strong>Approval/Action Comments</strong></h4>
        </div>
      </div>
      <div class="col-md-2"><img src="{{asset('assets/image-logo/coat.jpg')}}" class="img-responsive responsive" style="width:100%; height:120px;"></div>
    </div>
	</div>
    <div class="box-body" style="padding-top:0px; margin-top:0px;">
      <div class="row">
        <div class="col-md-12"><!--1st col--> 
                    
        <div class="panel panel-default" style="padding-bottom:0px; margin-bottom:0px;padding-top:0px; margin-top:20px;">
		<div class="panel-heading fieldset-preview"><b>Contract Details</b></div>
			<div class="panel-body">
            @if(isset($contract) && !empty($contract))
			<table class="table table-striped table-hover table-responsive table-condensed">
					<tbody class="btn-lg">
						<tr>
						    <td><b>Title</b></td>
							<td><b>Description </b></td>
							<td><b>Lot Number</b></td>
							<td><b>Amount Approved </b></td>
						</tr>
						<tr>
							<td>{{$contract->contract_name}}</td>
							<td>{{$contract->contract_description}}</td>
							<td>{{$contract->lot_number}}</td>
							<td>{{number_format($contract->proposed_budget, 2, '.', ',')}}</td>
						</tr>
						
					</tbody>
				</table>
            @else
                <div class="alert alert-warning text-center">
                    <h4>No Contract Details Found</h4>
                    <p>The contract information is not available.</p>
                </div>
            @endif
			<div class="clearfix"></div>
		</div>
	
	</div>        
       
       <div class="clearfix"></div>
   
@if(isset($comments) && count($comments) > 0)
    @foreach ($comments as $b)
         <div class="panel panel-default">
		<div class="panel-heading fieldset-preview"><b>Comment by: {{$b->name}} on {{ date("F j, Y",strtotime($b->created_at))}} </b></div>
			<div class="panel-body">
			{{$b->comment_description}}
			<div class="clearfix"></div>
		</div> 
	</div>   	
    @endforeach	
@else
    <div class="panel panel-default">
        <div class="panel-heading fieldset-preview"><b>Comments</b></div>
        <div class="panel-body text-center">
            <p>No comments available for this contract.</p>
        </div>
    </div>
@endif

          <hr />
          
        </div>
       
  </div>
</div>




@endsection

@section('styles')
<style type="text/css">
    .modal-dialog {
width:15cm
}

.modal-header {

background-color: #20b56d;

color:#FFF;

}
@media print{
.hidden-print{display:none!important}
 .dt-buttons, .dataTables_info, .dataTables_paginate, .dataTables_filter
{
display:none!important
}
}

.panel {
	margin-bottom:20px;
	background-color:#fff;
	border:1px solid transparent;
	border-radius:4px;
	-webkit-box-shadow:0 1px 1px rgba(0, 0, 0, .05);
	box-shadow:0 1px 1px rgba(0, 0, 0, .05)
}
.panel-body {
	padding:15px
}
.panel-heading {
	padding:10px 15px;
	border-bottom:1px solid transparent;
	border-top-left-radius:3px;
	border-top-right-radius:3px
}
.panel-heading>.dropdown .dropdown-toggle {
	color:inherit
}
.panel-title {
	margin-top:0;
	margin-bottom:0;
	font-size:16px;
	color:inherit
}
.panel-title>.small, .panel-title>.small>a, .panel-title>a, .panel-title>small, .panel-title>small>a {
	color:inherit
}
.panel-footer {
	padding:10px 15px;
	background-color:#f5f5f5;
	border-top:1px solid #ddd;
	border-bottom-right-radius:3px;
	border-bottom-left-radius:3px
}
.panel>.list-group, .panel>.panel-collapse>.list-group {
	margin-bottom:0
}
.panel>.list-group .list-group-item, .panel>.panel-collapse>.list-group .list-group-item {
	border-width:1px 0;
	border-radius:0
}
.panel>.list-group:first-child .list-group-item:first-child, .panel>.panel-collapse>.list-group:first-child .list-group-item:first-child {
	border-top:0;
	border-top-left-radius:3px;
	border-top-right-radius:3px
}
.panel>.list-group:last-child .list-group-item:last-child, .panel>.panel-collapse>.list-group:last-child .list-group-item:last-child {
	border-bottom:0;
	border-bottom-right-radius:3px;
	border-bottom-left-radius:3px
}
.panel>.panel-heading+.panel-collapse>.list-group .list-group-item:first-child {
	border-top-left-radius:0;
	border-top-right-radius:0
}
.panel-heading+.list-group .list-group-item:first-child {
	border-top-width:0
}
.list-group+.panel-footer {
	border-top-width:0
}
.panel>.panel-collapse>.table, .panel>.table, .panel>.table-responsive>.table {
	margin-bottom:0
}
.panel>.panel-collapse>.table caption, .panel>.table caption, .panel>.table-responsive>.table caption {
	padding-right:15px;
	padding-left:15px
}
.panel>.table-responsive:first-child>.table:first-child, .panel>.table:first-child {
	border-top-left-radius:3px;
	border-top-right-radius:3px
}
.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child, .panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child, .panel>.table:first-child>tbody:first-child>tr:first-child, .panel>.table:first-child>thead:first-child>tr:first-child {
	border-top-left-radius:3px;
	border-top-right-radius:3px
}
.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child td:first-child, .panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child th:first-child, .panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child td:first-child, .panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child th:first-child, .panel>.table:first-child>tbody:first-child>tr:first-child td:first-child, .panel>.table:first-child>tbody:first-child>tr:first-child th:first-child, .panel>.table:first-child>thead:first-child>tr:first-child td:first-child, .panel>.table:first-child>thead:first-child>tr:first-child th:first-child {
	border-top-left-radius:3px
}
.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child td:last-child, .panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child th:last-child, .panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child td:last-child, .panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child th:last-child, .panel>.table:first-child>tbody:first-child>tr:first-child td:last-child, .panel>.table:first-child>tbody:first-child>tr:first-child th:last-child, .panel>.table:first-child>thead:first-child>tr:first-child td:last-child, .panel>.table:first-child>thead:first-child>tr:first-child th:last-child {
	border-top-right-radius:3px
}
.panel>.table-responsive:last-child>.table:last-child, .panel>.table:last-child {
	border-bottom-right-radius:3px;
	border-bottom-left-radius:3px
}
.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child, .panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child, .panel>.table:last-child>tbody:last-child>tr:last-child, .panel>.table:last-child>tfoot:last-child>tr:last-child {
	border-bottom-right-radius:3px;
	border-bottom-left-radius:3px
}
.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child td:first-child, .panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child th:first-child, .panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child td:first-child, .panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child th:first-child, .panel>.table:last-child>tbody:last-child>tr:last-child td:first-child, .panel>.table:last-child>tbody:last-child>tr:last-child th:first-child, .panel>.table:last-child>tfoot:last-child>tr:last-child td:first-child, .panel>.table:last-child>tfoot:last-child>tr:last-child th:first-child {
	border-bottom-left-radius:3px
}
.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child td:last-child, .panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child th:last-child, .panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child td:last-child, .panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child th:last-child, .panel>.table:last-child>tbody:last-child>tr:last-child td:last-child, .panel>.table:last-child>tbody:last-child>tr:last-child th:last-child, .panel>.table:last-child>tfoot:last-child>tr:last-child td:last-child, .panel>.table:last-child>tfoot:last-child>tr:last-child th:last-child {
	border-bottom-right-radius:3px
}
.panel>.panel-body+.table, .panel>.panel-body+.table-responsive, .panel>.table+.panel-body, .panel>.table-responsive+.panel-body {
	border-top:1px solid #ddd
}
.panel>.table>tbody:first-child>tr:first-child td, .panel>.table>tbody:first-child>tr:first-child th {
	border-top:0
}
.panel>.table-bordered, .panel>.table-responsive>.table-bordered {
	border:0
}
.panel>.table-bordered>tbody>tr>td:first-child, .panel>.table-bordered>tbody>tr>th:first-child, .panel>.table-bordered>tfoot>tr>td:first-child, .panel>.table-bordered>tfoot>tr>th:first-child, .panel>.table-bordered>thead>tr>td:first-child, .panel>.table-bordered>thead>tr>th:first-child, .panel>.table-responsive>.table-bordered>tbody>tr>td:first-child, .panel>.table-responsive>.table-bordered>tbody>tr>th:first-child, .panel>.table-responsive>.table-bordered>tfoot>tr>td:first-child, .panel>.table-responsive>.table-bordered>tfoot>tr>th:first-child, .panel>.table-responsive>.table-bordered>thead>tr>td:first-child, .panel>.table-responsive>.table-bordered>thead>tr>th:first-child {
	border-left:0
}
.panel>.table-bordered>tbody>tr>td:last-child, .panel>.table-bordered>tbody>tr>th:last-child, .panel>.table-bordered>tfoot>tr>td:last-child, .panel>.table-bordered>tfoot>tr>th:last-child, .panel>.table-bordered>thead>tr>td:last-child, .panel>.table-bordered>thead>tr>th:last-child, .panel>.table-responsive>.table-bordered>tbody>tr>td:last-child, .panel>.table-responsive>.table-bordered>tbody>tr>th:last-child, .panel>.table-responsive>.table-bordered>tfoot>tr>td:last-child, .panel>.table-responsive>.table-bordered>tfoot>tr>th:last-child, .panel>.table-responsive>.table-bordered>thead>tr>td:last-child, .panel>.table-responsive>.table-bordered>thead>tr>th:last-child {
	border-right:0
}
.panel>.table-bordered>tbody>tr:first-child>td, .panel>.table-bordered>tbody>tr:first-child>th, .panel>.table-bordered>thead>tr:first-child>td, .panel>.table-bordered>thead>tr:first-child>th, .panel>.table-responsive>.table-bordered>tbody>tr:first-child>td, .panel>.table-responsive>.table-bordered>tbody>tr:first-child>th, .panel>.table-responsive>.table-bordered>thead>tr:first-child>td, .panel>.table-responsive>.table-bordered>thead>tr:first-child>th {
	border-bottom:0
}
.panel>.table-bordered>tbody>tr:last-child>td, .panel>.table-bordered>tbody>tr:last-child>th, .panel>.table-bordered>tfoot>tr:last-child>td, .panel>.table-bordered>tfoot>tr:last-child>th, .panel>.table-responsive>.table-bordered>tbody>tr:last-child>td, .panel>.table-responsive>.table-bordered>tbody>tr:last-child>th, .panel>.table-responsive>.table-bordered>tfoot>tr:last-child>td, .panel>.table-responsive>.table-bordered>tfoot>tr:last-child>th {
	border-bottom:0
}
.panel>.table-responsive {
	margin-bottom:0;
	border:0
}
.panel-group {
	margin-bottom:20px
}
.panel-group .panel {
	margin-bottom:0;
	border-radius:4px
}
.panel-group .panel+.panel {
	margin-top:5px
}
.panel-group .panel-heading {
	border-bottom:0
}
.panel-group .panel-heading+.panel-collapse>.list-group, .panel-group .panel-heading+.panel-collapse>.panel-body {
	border-top:1px solid #ddd
}
.panel-group .panel-footer {
	border-top:0
}
.panel-group .panel-footer+.panel-collapse .panel-body {
	border-bottom:1px solid #ddd
}
.panel-default {
	border-color:#ddd
}
.panel-default>.panel-heading {
	color:#333;
	background-color:#f5f5f5;
	border-color:#ddd
}
.panel-default>.panel-heading+.panel-collapse>.panel-body {
	border-top-color:#ddd
}
.panel-default>.panel-heading .badge {
	color:#f5f5f5;
	background-color:#333
}
.panel-default>.panel-footer+.panel-collapse>.panel-body {
	border-bottom-color:#ddd
}
.panel-primary {
	border-color:#337ab7
}
.panel-primary>.panel-heading {
	color:#fff;
	background-color:#337ab7;
	border-color:#337ab7
}
.panel-primary>.panel-heading+.panel-collapse>.panel-body {
	border-top-color:#337ab7
}
.panel-primary>.panel-heading .badge {
	color:#337ab7;
	background-color:#fff
}
.panel-primary>.panel-footer+.panel-collapse>.panel-body {
	border-bottom-color:#337ab7
}
.panel-success {
	border-color:#d6e9c6
}
.panel-success>.panel-heading {
	color:#3c763d;
	background-color:#dff0d8;
	border-color:#d6e9c6
}
.panel-success>.panel-heading+.panel-collapse>.panel-body {
	border-top-color:#d6e9c6
}
.panel-success>.panel-heading .badge {
	color:#dff0d8;
	background-color:#3c763d
}
.panel-success>.panel-footer+.panel-collapse>.panel-body {
	border-bottom-color:#d6e9c6
}
.panel-info {
	border-color:#bce8f1
}
.panel-info>.panel-heading {
	color:#31708f;
	background-color:#d9edf7;
	border-color:#bce8f1
}
.panel-info>.panel-heading+.panel-collapse>.panel-body {
	border-top-color:#bce8f1
}
.panel-info>.panel-heading .badge {
	color:#d9edf7;
	background-color:#31708f
}
.panel-info>.panel-footer+.panel-collapse>.panel-body {
	border-bottom-color:#bce8f1
}
.panel-warning {
	border-color:#faebcc
}
.panel-warning>.panel-heading {
	color:#8a6d3b;
	background-color:#fcf8e3;
	border-color:#faebcc
}
.panel-warning>.panel-heading+.panel-collapse>.panel-body {
	border-top-color:#faebcc
}


</style>
@endsection

@section('scripts')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">

<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>

<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>

<script>


  function  ReloadForm()
  { 
  document.getElementById('thisform1').submit();
  return;
  }

  function addattachment(x){
        //document.getElementById('cid').value = x;
        $("#attachModal").modal('show');
    }


 $( function(){
   $("#fromdate").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
   $("#todate").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    });

  $(document).ready(function() {
    $('#').DataTable();
  } );

  $(document).ready(function() {
      $('#mytable').DataTable( {
          dom: 'Bfrtip',
          buttons: [
              {
                  extend: 'print',
                  customize: function ( win ) {
                      $(win.document.body)
                          .css( 'font-size', '10pt' )
                          .prepend(
                              ''
                          );
   
                      $(win.document.body).find( 'table' )
                          .addClass( 'compact' )
                          .css( 'font-size', 'inherit' );
                  }
              }
          ]
      } );
  } );


</script>


@stop
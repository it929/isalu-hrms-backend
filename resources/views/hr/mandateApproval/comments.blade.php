@extends('layouts.layout')
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
      <div class="col-xs-2"><img src="{{asset('Images/njc-logo.jpg')}}" class="img-responsive responsive" style="width:100%; height:auto;"></div>
      <div class="col-xs-8">
        <div>
          <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
          <h4 class="text-center text-success"><strong>3 ARMS ZONE ISALU HOSPITAL LIMITED COMPLEX, ABUJA</strong></h4>
         <h4 class="text-center text-success"><strong>Approval/Action Minutes</strong></h4>
        </div>
      </div>
      <div class="col-xs-2"><img src="{{asset('Images/coat.jpg')}}" class="responsive"></div>
    </div>
	</div>
    <div class="box-body" style="padding-top:0px; margin-top:0px;">
      <div class="row">
        <div class="col-md-12"><!--1st col-->
    @if(count($cmt) == 0)
    <div class="panel panel-default">
		<div class="panel-heading fieldset-preview"><b>Awaiting</b></div>
			<div class="panel-body">
			No Comment Available Yet
			<div class="clearfix"></div>
		</div>
	</div>
	@else
@foreach ($cmt as $b)
         <div class="panel panel-default">
		<div class="panel-heading fieldset-preview"><b>Comment by: {{$b->name}} on {{ date("F j, Y",strtotime($b->lastUpdated))}} {{ date("g:i a",strtotime($b->lastUpdated))}}</b></div>
			<div class="panel-body">
			{{$b->comment}}
			<div class="clearfix"></div>
		</div>
	</div>

@endforeach


	@endif

          <hr />
          <div class="col-md-12">



            </div>
        </div>

  </div>
  <div><a href="{{ url()->previous() }}" class="hidden-print btn btn-success">Back</a></div>
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

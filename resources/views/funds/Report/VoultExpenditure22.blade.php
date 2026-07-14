@extends('layouts.layout')
@section('pageTitle')

@endsection
@section('content')


<div class="box box-default">
  <div class="box-body box-profile">
    <div class="box-header with-border">
      <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
    </div>
     <div class="box-body">
    <div class="row" >
      <div class="col-xs-2"><img src="{{asset('Images/scn_logo.jpg')}}" class="img-responsive responsive" style="width:100%; height:auto;"></div>
      <div class="col-xs-8">
        <div>
          <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
          <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h4>
         <h4 class="text-center text-success"><strong>Vote Expenditure Report</strong></h4>
        </div>
      </div>
      <div class="col-xs-2"><img src="{{asset('Images/coat.jpg')}}" class="responsive"></div>
    </div>
	</div>
    <div class="box-body">
      <div class="row">
        <div class="col-md-12"><!--1st col-->
                    @if ($warning<>'')
                      <div class="alert alert-dismissible alert-danger">
                      <button type="button" class="close" data-dismiss="alert">&times;</button>
                      <strong>{{$warning}}</strong>
                      </div>
                      @endif
                      @if ($success<>'')
                      <div class="alert alert-dismissible alert-success">
                      <button type="button" class="close" data-dismiss="alert">&times;</button>
                      <strong>{{$success}}</strong>
                      </div>
                      @endif
                      @if (count($errors) > 0)
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Error!</strong>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif



        <form class="form-horizontal hidden-print" role="form" id="thisform1" name="thisform1" method="post">
        {{ csrf_field() }}
<div class="row hidden-print">
	<div class="col-md-12">
		<div class="form-group">
			<div class="col-md-2">
			<label>Period</label>
			<select name="period" id="period" class="form-control" onchange ="ReloadForm();" >
			<option value="" selected>-Select Year-</option>
			@foreach ($YearPeriod as $b)
			<option value="{{$b->Period}}" {{ ($period) == $b->Period? "selected":"" }}>{{$b->Period}}</option>
			@endforeach
			</select>
			</div>
			<div class="col-md-2">
			<label class="control-label">Month</label>
			<select class="form-control" id="month" name="month" onchange="ReloadForm()" required="">
				<option value=""  > Choose One</option>
				<option value="january" {{ ($month) == 'january' ? "selected":"" }}  > January</option>
				<option value="february" {{ ($month) == 'february' ? "selected":"" }} > February</option>
				<option value="march" {{ ($month) == 'march' ? "selected":"" }} > March</option>
				<option value="april" {{ ($month) == 'april' ? "selected":"" }} > April</option>
				<option value="may" {{ ($month) == 'may' ? "selected":"" }} > May</option>
				<option value="june" {{ ($month) == 'june' ? "selected":"" }} > June</option>
				<option value="july" {{ ($month) == 'july' ? "selected":"" }} > July</option>
				<option value="august" {{ ($month) == 'august' ? "selected":"" }} > August</option>
				<option value="september" {{ ($month) == 'september' ? "selected":"" }} > September</option>
				<option value="october" {{ ($month) == 'october' ? "selected":"" }} > October</option>
				<option value="november" {{ ($month) == 'november' ? "selected":"" }} > November</option>
				<option value="december" {{ ($month) == 'december' ? "selected":"" }} > December</option>
			</select>
			</div>
			<div class="col-md-2">
			<label>Allocation Source</label>
			<select name="allocationsource" id="allocationsource" class="form-control" onchange ="ReloadForm();">
			<option value="" selected>-All-</option>
			@foreach ($AllocationSource as $b)
			<option value="{{$b->ID}}" {{ ($allocationsource) == $b->ID? "selected":"" }}>{{$b->allocation}}</option>
			@endforeach
			</select>
			</div>
			<div class="col-md-2">
			<label>Budget Type</label>
			<select name="budgettype" id="budgettype" class="form-control" onchange ="ReloadForm();">
			<option value="" selected>-All-</option>
			@foreach ($BudgetType as $b)
			<option value="{{$b->ID}}" {{ ($budgettype) == $b->ID? "selected":"" }}>{{$b->contractType}}</option>
			@endforeach
			</select>
			</div>
			<div class="col-md-2">
			<label>Economic Head</label>
			<select name="economichead" id="economichead" class="form-control" onchange ="ReloadForm();">
			<option value="" selected>-All-</option>
			@foreach ($EconomicHead as $b)
			<option value="{{$b->ID}}" {{ ($economichead) == $b->ID? "selected":"" }}>{{$b->economicHead}}</option>
			@endforeach
			</select>
			</div>
			<div class="col-md-2">
			<label>Economic Codes</label>
			<select name="economiccode" id="economiccode" class="form-control" onchange ="ReloadForm();">
			<option value="" selected>-All-</option>
			@foreach ($EconomicCode as $b)
			<option value="{{$b->ID}}" {{($economiccode) == $b->ID? "selected":"" }}>{{$b->economicCode}}|{{$b->description}}</option>
			@endforeach
			</select>
			</div>
</div>

      </div>
			<div class="col-md-12">
			<div class="form-group">

                <br>
                <label class="control-label"></label>
                <button type="submit" class="btn btn-success" name="add">
                    <i class="fab fa-btn fa-sistrix"></i> Search
                </button>

        </div>
        </div>
        </div>


        </form>


		<div class="table-responsive" style="font-size: 12px; padding:10px;">
			<table class="table table-bordered">
			<thead>
				<tr >

			               	<th >Line No</th>
			                <th >Date</th>
			                <th >Voucher No</th>
			                <th >Particular</th>
			                <th >Payment</th>
			                <th >Total</th>
			                <th >Balance</th>
			 	</tr>
			</thead>
						@php $serialNum = 1; @endphp
						@php $grouphead = ""; @endphp
						@foreach ($QueryVoultReport as $b)
							@if($b->economicHeadID!=$grouphead)
							<tr>
								@php $grouphead = $b->economicHeadID; @endphp
								<!--<td></td>-->
								<td>{{$b->economicheadcode}}</td><td></td>
								<td colspan=7><b>{{$b->economichead}}  ({{$b->economicgroup }}-{{$b->allocationsource}})</b></td>
							</tr>
							@endif

							<tr>
								<!--<td>{{ $serialNum ++}} </td>-->
								<td></td>
								<td>{{$b->economiccode}}</td>

								<td>{{$b->economicdisc}}</td>
								<td class="align">{{number_format($b->allocationValue,2)}}</td>
								<td class="align">{{number_format($b->January,2)}}</td>
								<td class="align">{{number_format($b->bookonhold+$b->expend,2)}}</td>
								<td class="align">{{number_format($b->January-($b->bookonhold + $b->expend), 2)}}</td>
								<td class="align">{{number_format($b->bookonholdtodate+$b->expendtodate,2)}}</td>
								<td class="align">
								@if(($b->January-($b->bookonhold + $b->expend))<0)
								Excess expenditure drawn from accumulated BF
								@endif
								</td>

							</tr>



						@endforeach
			 </table>
			 <button class="print hidden-print" type="submit" >Print</button>
		</div>

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

  function  ReloadForm2()
  {
  document.getElementById('editBModal').submit();
  return;
  }

    function editfunc(a,b,c,d,e,f,g)
    {
    $(document).ready(function(){
        $('#period').val(a);
        $('#allocationType').val(b);
        $('#economicGroup').val(c);
        $('#economicCode').val(d);
        $('#budget').val(e);
        $('#economicHead').val(f);
        $('#B_id').val(g);
        $("#editModal").modal('show');
     });
    }

    function delfunc(a,b)
  {
  $(document).ready(function(){
  $('#conID').val(a);
  $('#status').val(b);
  $("#delModal").modal('show');
  });
  }


 $( function(){
   $("#dateFrom").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
   $("#dateTo").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
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

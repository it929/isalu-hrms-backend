@extends('layouts.layout')
@section('pageTitle')
	 <!--RECORD &amp; VARIATION-->
@endsection

@section('content')
<div class="box box-default col-sm-12" style="border-top: none;">
	<form action="{{url('/record-variation/view/cadre')}}" method="post">
	{{ csrf_field() }}
          <div class="box-header with-border hidden-print">
          <h3 class="box-title"><small>@yield('pageTitle')</small><span id='processing'></span></h3>
          <span class="pull-right" style="margin-right: 10px;">
             <div style="float: left;">
              <div class="wrap">
               <div class="search"> 
               <button type="submit" class="btn btn-default" style="padding: 6px; float: right; border-radius: 0px;">
                <i class="fa fa-search"></i>
              </button>
               <input type="text" id="autocomplete_central" name="q" class="form-control" placeholder="Search By Name or File No." style="padding: 5px; width: 200px;"><!--searchTerm-->
               <input type="hidden" id="fileNo"  name="fileNo">
               <input type="hidden" id="monthDay"  name="monthDay" value="">
              </div>
              </div>
             </div>
          </span>
				       <span class="pull-right" style="margin-right: 0px;">
			          	 <div style="float: left; width: 100%;">
			          	 	<select name="filterCadre" id="filterCadre" class="form-control hidden-print">
			          	 		<option value="" selected="selected">Select Cadre</option>
			          	 		<option value=""></option>
    					    		<option>ADMIN</option>
    					    		<option>ACCOUNT</option>
    					    		<option>BELIF</option>
    					    		<option>JUDGES</option>
    					    		<option>PRESIDING JUDGE</option>
    					    		<option>LITIGATION</option>
    					    		<option>RECORDS AND VERIATION</option>
    					    		<option>MAINTENANCE</option>
    					    		<option>TRANSPORT</option>
    					    		<option>STORE</option>
    					    		<option>VISITING JUDGES</option>
    					    		<option>LIBRARY</option>
    					    		<option>PERSONNEL</option>
    					    		<option>FUNDS</option>
    					    		<option>INTERNAL AUDIT</option>
    					    		<option>PROTOCOL</option>
    					    		<option>CR OFFICE</option>
    					    		<option>SECURITY</option>
    					    		<option>PRESIDENT CHAMBER</option>
    					    		<option>CENTRAL PAY OFF</option>
    					    		<option>REGISTRY</option>
    					    		<option>PORTER</option>
    					    		<option>RECONCILIATION</option>
    					    		<option>CHECKING</option>
    					    		<option>NHF</option>
    					    		<option>OPEN REGISTRY</option>
    					    		<option>C.P.O</option>
    					    		<option>CLINIC</option>
    					    		<option>ACR'S OFFICE</option>
    					    		<option>TAX MATTERS</option>
    					    		<option>WELFARE</option>
    					    		<option>CLERICAL OFFICE</option>
    					    		<option>MANPOWER</option>
    					    		<option>ADR CENTRE</option>
    					    		<option>DRIVER</option>
    					    		<option>TYPING POOL</option>
    					    		<option>DATA ROOM</option>
    					    		<option>TRANING</option>
    					    		<option>PENSION</option>
    					    		<option>PLANNING RESEARCH</option>
    					    		<option>OTHER CHARGES</option>
					    	    </select>
			          	 </div>
			          </span>
			           <span class="pull-right" style="margin-right: 0px;">
			          	 <div style="float: left; width: 100%;">
			          	 	<select name="filterDivision" id="filterDivision" class="form-control hidden-print">
			          	 		<option value="" selected="selected">Select Division</option>
			          	 		<option value=""></option>
			          	 		@foreach($getDivision as $getDiv)
					    			    <option  value="{{$getDiv->divisionID}}">{{$getDiv->division}}</option>
					    		    @endforeach
					    	</select>
					    </div>
					   </span>
          </form>
           <form method="post" action="{{url('/record-variation/view/cadre')}}">
          {{ csrf_field() }}
            <span class="hidden-print">
                 <span class="pull-right">
                  <div style="float: left; width: 100%; margin-top: -20px;">
                     <button type="submit" class="btn btn-default" style="padding: 6px; border-radius: 0px;">
                          <i class="fa fa-calendar blink-text"></i> Staff Due For Increment Today
                     </button>
                  </div>
                  <input type="hidden" id="monthDay"  name="monthDay" value="{{date('Y-m-d')}}">
                  <input type="hidden" id="filterCadre"  name="filterCadre" value="">
                  <input type="hidden" id="filterDivision"  name="filterDivision" value="">
                </span>
                
                <a href="{{url('/record-variation/view/increment')}}">
                  <button type="button" class="btn btn-default pull-right" style="padding: 6px; border-radius: 0px; margin-top: -20px;">
                      <i class="fa fa-calendar blink-text"></i> All Increments 
                  </button>
                </a>
                <a href="{{url('/record-variation/refresh/cadre-list')}}">
                    <div class="pull-right" style="padding: 6px; border-radius: 0px; margin: -20px 80px 0 0">
                      <i class="fa fa-refresh"></i>
                    </div>
                </a>
            </span>
        </form> 
    </div>

    <div style="margin: 10px 20px;">
    	<div align="center">
        <h3><b>{{strtoupper('SUPREME COURT OF NIGERIA')}}</b></h3>
        <big><b>{{strtoupper($headFile . ' AS AT ' . date('l jS \of F Y'))}}</b></big>
      </div>
    	<span class="pull-right" style="margin-right: 30px;">Printed On: {{date('D M, Y')}} &nbsp; | &nbsp; Time: {{date('h:i:s A')}}</span>
    
      <br />
    @if(session('err'))
  		<div class="col-sm-12 alert alert-warning alert-dismissible hidden-print" role="alert">
  		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
  		</button>
  		<strong>Error!</strong> 
  		{{ session('err') }} 
  		</div>                        
	 @endif

	</div>

	<div class="box-body">
		<div class="row">
			{{ csrf_field() }}

			<div class="col-md-12">
				<table class="table table-striped table-condensed table-bordered input-sm">
					<thead>
          <tr class="input-sm">
  						<th>S/N</th>
  						<th width="250" class="">FULL NAME</th>
  						<th width="80" class="">DATE OF BIRTH</th>
  						<th>SEX</th>
              <th>MARITAL STATUS</th>
              <th>L.G.A OF ORIGIN</th>
              <th>STATE OF ORIGIN</th>
              <th>DATE OF FIRST <BR /> APPOINTMENT</th>
              <th>RANK</th> 
              <th>DATE OF PRESENT <BR /> APPOINTMENT</th>
              <th>DIVISION</th>
              <th>FILE NO</th>
              <th class="hidden-print"></th>
              <th class="hidden-print"></th>
              </tr>
					</thead>
					<tbody class="input-sm">
						@php $key = 1; @endphp
            @foreach($getCentralList as $list)
  						<tr>
                  <td>{{($getCentralList->currentpage()-1) * $getCentralList->perpage() + $key ++}}</td> 
                  <td>{{strtoupper($list->surname .' '. $list->first_name .' '. $list->othernames)}}</td> 
                  <td>{{$list->dob}}</td>
                  <td>
                    @php 
                        if(strtoupper(($list->gender == "MALE")))
                        {
                          $sex = 'M';
                        }else if(strtoupper(($list->gender == "FEMALE")))
                        {
                          $sex = 'F';
                        }else
                        {
                          $sex = '';
                        }
                    @endphp
                    {{$sex}}
                  </td> 
                  <td>{{$list->maritalstatus}}</td>
                  <td></td> 
                  <td></td>
                  <td>{{$list->appointment_date}}</td>  
                  <td>{{$list->section .' '. 'GL'.$list->grade .'|'.'S'.$list->step}}</td> 
                  <td>{{$list->firstarrival_date}}</td> 
                  <td>{{strtoupper($list->division)}}</td> 
                  <td>{{'JIPPIS/P/'.$list->fileNo}}</td> 
                  @include('Layouts._alertIncrement')
                  <!--$this-><td class="hidden-print"></td> was include _alertIncrement-->  
                  <td class="hidden-print">
                    @if((($list->step) != ($list->stepalert)) and ($list->stepalert != ""))
                     <a href="{{url('/computer/variation/create')}}">
                        <i class="fa fa-file fa-2x"></i>
                      </a>
                    @endif
                  </td> 
              </tr>
            @endforeach
					</tbody>
				</table>
        <div align="right">
          Showing {{($getCentralList->currentpage()-1)*$getCentralList->perpage()+1}}
                  to {{$getCentralList->currentpage()*$getCentralList->perpage()}}
                  of  {{$getCentralList->total()}} entries
        </div>
				<div class="hidden-print">{{ $getCentralList->links() }}</div>
			</div>
		</div><!-- /.col -->
	</div><!-- /.row -->


  <!--Alert Modal for Increment/promotion -->
    @php
      $passParameter = $getAllAlert;
    @endphp
    @if(count($passParameter > 0))
      @include('layouts._showAlert')
    @endif
  <!--//ends here Increment Model-->
  

</div>

@endsection

@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<!-- autocomplete js-->
<script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
<script type="text/javascript">
  $(function() {
      $("#autocomplete_central").autocomplete({
        serviceUrl: murl + '/staff/search/json',
        minLength: 10,
        onSelect: function (suggestion) {
            $('#fileNo').val(suggestion.data);
            showAll();
        }
      });
  });

  $("#searchDate").datepicker({
    changeMonth: true,
    changeYear: true,
    yearRange: '1910:2090', // specifying a hard coded year range
    showOtherMonths: true,
    selectOtherMonths: true,
    dateFormat: "dd MM, yy",
    onSelect: function(dateText, inst){
      var theDate = new Date(Date.parse($(this).datepicker('getDate')));
      var dateFormatted = $.datepicker.formatDate('yy-mm-d', theDate);
       //$('#fileNo').val($.datepicker.formatDate('yy-m-d', theDate));
    },
  });

</script>
@stop

@section('styles')
  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom-style.css')}}">

  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@stop



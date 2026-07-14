@extends('layouts.layout')

@section('pageTitle')
  	LEAVE ROASTER REPORT
@endsection

@section('content')
 <div class="box box-default" style="border: none;">
    <div class="box-body box-profile" style="margin:10px 20px;">

		<div class="row">
		    <div class="col-xs-12" style="margin:5px 30px;">
    		    <div class="col-xs-2">
        			<div align="right">
        				<img src="{{ asset('Images/njc-logo.jpg') }}" alt=" " class="img-responsive" width="90" />
        			</div>
    			</div>
    			<div align="left" class="col-xs-10">
        			<div align="center" class="text-success text-center">
        				<h3><strong>ISALU HOSPITAL LIMITED</strong></h3>
        				<h4>SECRETARIAT DEPARTMENT</h4>
						<h5>{{ isset($year) ? $year : date('Y') }} PROPOSED ANNUAL LEAVE DATE </h5>
        			</div>
    			</div>
				<hr />
			</div>

			<div class="row">
				@includeIf('Share.message')

    			<div class="col-md-12 d-print-none hidden-print">
					<form method="post" action="{{route('leaveRoasterReport')}}">
					@csrf
						<div class="col-md-4">
							<div class="form-group">
								<label for="staffName">Filter</label>
								<select class="form-control" name="year">
									<option value="">Select</option>
									@for($i = date('Y'); $i > 2005; $i--)
										<option>{{$i}}</option>
									@endfor
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="staffName"></label><br />
								<button type="submit" class="btn btn-primary">Search</button>
							</div>
						</div>
					</form>

				</div><hr />
			</div>
		</div>

		<table class="table table-responsive table-hover">
			<thead>
				<tr>
					<th>S/NO</th>
					<th>NAMES</th>
					<th>DATE</th>
				<tr>
			</thead>
			<tbody>
				@if(isset($getRecord) && $getRecord)
					@foreach($getRecord as $key => $value)
						<tr>
							<td>{{ $key + 1 }}</td>
							<td>{{ $value->staff_name }}</td>
							<td>{{ date('jS F, Y', strtotime($value->startDate)) }}</td>
						</tr>
					@endforeach
				@endif
			</tbody>
		</table>
		@if(isset($getRecord) && $getRecord)
			<div align="right" class="col-md-12"><hr />
				Showing {{($getRecord->currentpage()-1)*$getRecord->perpage()+1}}
				to {{$getRecord->currentpage()*$getRecord->perpage()}}
				of  {{$getRecord->total()}} entries
			</div>
			<div class="d-print-none">{{ $getRecord->links() }}</div>
		@endif
    </div>
    </div>
@stop

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
    <style type="text/css">

    </style>
@endsection

@section('scripts')
    <script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
    <script src="{{ asset('assets/js/jquery.autocomplete.js') }}" ></script>

    <script type="text/javascript">

    </script>
@stop

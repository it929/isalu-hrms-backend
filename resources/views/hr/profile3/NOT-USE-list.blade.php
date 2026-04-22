@extends('layouts.layout')
@section('pageTitle')
Staff List
@endsection

@section('content')
<div class="box box-default" style="border-top: none;">
	<form action="{{url('/profile/list')}}" method="post">
	{{ csrf_field() }}
        <div class="box-header with-border hidden-print">
          <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
          <span class="pull-right" style="margin-right: 30px;">
          	 <div style="float: left;">
          	 	<select name="filterBy" class="form-control">
          	 		<option value="1" selected="selected">Current Division</option>
          	 		<option value="2">All Divisions</option>
          	 		<option value="3">Chief Registrar</option>
          	 		<option value="4">Health</option>
          	 		<option value="5">Medical</option>
          	 		<option value="6">Judicial</option>
          	 	</select>
          	 </div>
          	 <button style="float: left; height: 34px;"><li class="fa fa-search"></li></button>
          </span>
        </div>
    </form>

    <div style="margin: 10px 20px;">
    	<big><b>{{strtoupper('All Staff Records ')}}</b> - {{$getDivision}}</big>
    	<span class="pull-right" style="margin-right: 30px;">Printed On: {{date('D M, Y')}}.</span>
    </div>

	<form method="post" action="{{ url('staff/store') }}">
	<div class="box-body">
		<div class="row">
			{{ csrf_field() }}

			<div class="col-md-12">
				<table class="table table-striped table-condensed table-bordered">
					<thead>
						<th>S/N</th>
						<th>File Number</th>
						<th>Surname</th>
						<th>First Name</th>
						<th>Other Names</th>
						<th>Division</th>
					</thead>
					<tbody>
						@php $key = 1; @endphp
						@foreach ($users as $user)
					        <tr>
					        	<td>{{$key ++}}</td>
					       		<td>{{ $user->fileNo }}</td>
					       		<td>{{ $user->surname }}</td>
					       		<td>{{ $user->first_name }}</td>
					       		<td>{{ $user->othernames }}</td>
					       		<td>{{ $user->division }}</td>
					        </tr>
					    @endforeach
					</tbody>
					<tfooter>
						<th>S/N</th>
						<th>File Number</th>
						<th>Surname</th>
						<th>First Name</th>
						<th>Other Names</th>
						<th>Division</th>
					</tfooter>
				</table>
				<div class="hidden-print">{{ $users->links() }}</div>
			</div>
		</div><!-- /.col -->
	</div><!-- /.row -->
</form>
</div>
@endsection

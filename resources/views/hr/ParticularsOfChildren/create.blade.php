@extends('layouts.layout')

@section('pageTitle')
 Add Particular of Children
@endsection

@section('content')
 <div class="panel panel-primary">
    <div class="panel-body">
    	<div class="panel-heading hidden-print">
        	<h3 class="panel-title"><b>@yield('pageTitle')</b>
        		<big><b class="text-green"> - {{strtoupper($getStaff->surname." ".$getStaff->first_name." ".$getStaff->othernames)}}</b></big><span id='processing'></span>
        	</h3>
    	</div>


        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Children Information
                </h3>
            </div>

            <div class="panel-body">
                <form method="post" action="{{ url('/children/create') }}">
                    {{ csrf_field() }}

                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Full Name</label>

                                @php if($details != ""){ @endphp
                                    <input type="text" name="fullName" class="form-control" value="{{ $details->fullname }}">
                                    <input type="hidden" name="id" value="{{ $details->id }}">
                                @php }else{ @endphp
                                    <input type="text" name="fullName" class="form-control" value="{{ old('fullName') }}">
                                    <input type="hidden" name="id" value="">
                                @php } @endphp

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Gender</label>

                                <select name="gender" class="form-control">
                                    @php if($details != ""){ @endphp
                                        <option value="{{ $details->gender }}">{{ $details->gender }}</option>
                                    @php }else{ @endphp
                                        <option value=""></option>
                                    @php } @endphp

                                    <option value="Male" {{ old('gender') == "Male" ? "selected" : "" }}>Male</option>
                                    <option value="Female" {{ old('gender') == "Female" ? "selected" : "" }}>Female</option>
                                </select>

                            </div>
                        </div>

                         <div class="col-md-4">
                            <div class="form-group">
                                <label>Date of Birth</label>

                                @php if($details != ""){ @endphp
                                    <input type="text" id="dateOfBirth2" name="dateOfBirth2"
                                        class="form-control"
                                        value="{{ date('d M, Y', strtotime($details->dateofbirth)) }}">
                                    <input type="hidden" id="dateOfBirth" name="dateOfBirth" value="{{ $details->dateofbirth }}">
                                @php }else{ @endphp
                                    <input type="text" id="dateOfBirth2" name="dateOfBirth2"
                                        class="form-control"
                                        value="{{ old('dateOfBirth2') }}">
                                    <input type="hidden" id="dateOfBirth" name="dateOfBirth" value="{{ old('dateOfBirth') }}">
                                @php } @endphp

                            </div>
                        </div>

                    </div>


                    {{-- <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Birth</label>

                                @php if($details != ""){ @endphp
                                    <input type="text" id="dateOfBirth2" name="dateOfBirth2"
                                        class="form-control"
                                        value="{{ date('d M, Y', strtotime($details->dateofbirth)) }}">
                                    <input type="hidden" id="dateOfBirth" name="dateOfBirth" value="{{ $details->dateofbirth }}">
                                @php }else{ @endphp
                                    <input type="text" id="dateOfBirth2" name="dateOfBirth2"
                                        class="form-control"
                                        value="{{ old('dateOfBirth2') }}">
                                    <input type="hidden" id="dateOfBirth" name="dateOfBirth" value="{{ old('dateOfBirth') }}">
                                @php } @endphp

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Checked By</label>

                                @php if($details != ""){ @endphp
                                    <input type="text" class="form-control" name="checkedChildrenParticulars"
                                        value="{{ $details->checked_children_particulars }}">
                                @php }else{ @endphp
                                    <input type="text" class="form-control" name="checkedChildrenParticulars"
                                        value="{{ old('checkedChildrenParticulars') }}">
                                @php } @endphp

                            </div>
                        </div>

                    </div> --}}

                    <hr>

                    <div class="row">

                        <div class="col-md-3">
                            <a href="javascript: loadProfileDetail('{{ $staffid }}')" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Back
                            </a>
                        </div>

                        <div class="col-md-9 text-right">
                            <button class="btn btn-success" type="submit">
                                Update/Add New <i class="fa fa-save"></i>
                            </button>
                        </div>

                    </div>

                </form>
            </div>
        </div>



        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Children List</h3>
            </div>

            <div class="panel-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            {{-- <th>Checked By</th> --}}
                            <th>Edit</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @php if($childrenList != ''){ @endphp
                            @php $key = 1 @endphp
                            @foreach($childrenList as $list)
                                <tr>
                                    <td>{{ $key++ }}</td>
                                    <td>{{ $list->fullname }}</td>
                                    <td>{{ $list->gender }}</td>
                                    <td>{{ date('d-M-Y', strtotime($list->dateofbirth)) }}</td>
                                    

                                    <td>
                                        <a href="{{ url('/children/edit/'.$list->id) }}"
                                        class="btn btn-success btn-sm">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>

                                    <td>
                                        <!-- Delete button (if needed later)
                                        <a href="{{ url('/children/remove/'.$list->id) }}"
                                        class="btn btn-warning btn-sm">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        -->
                                    </td>
                                </tr>
                            @endforeach
                        @php } else { @endphp
                            <tr>
                                <td colspan="11" class="text-center text-danger">
                                    No details provided yet!
                                </td>
                            </tr>
                        @php } @endphp
                    </tbody>
                </table>
            </div>
        </div>

	</div>
</div>
<form method="post" id="displayform" name="displayform"  action="{{url('/profile/details')}}">

                {{ csrf_field() }}

                <input type="hidden" id="fileNos" name="fileNo" >



</form>
@endsection
@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@endsection
@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ✅ Toast for success after redirect
@if(session('msg'))
Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'success',
    title: '{{ session('msg') }}',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});
@endif
</script>
<script>
function  loadProfileDetail(staffid)
{
document.getElementById('fileNos').value = staffid;
document.forms["displayform"].submit();
return;

}
</script>
  <script type="text/javascript">

	$( function() {
	    $("#dateOfBirth2").datepicker({
	    	changeMonth: true,
	    	changeYear: true,
	    	yearRange: '1910:2090', // specifying a hard coded year range
		    showOtherMonths: true,
		    selectOtherMonths: true,
		    dateFormat: "dd MM, yy",
		    //dateFormat: "D, MM d, yy",
		    onSelect: function(dateText, inst){
		    	var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('yy-mm-d', theDate);
				$("#dateOfBirth").val(dateFormatted);
        	},
		});

  } );
</script>
@endsection

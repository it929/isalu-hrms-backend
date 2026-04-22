@extends('layouts.layout')

@section('pageTitle')
 Add Particular of Date of Birth and Wife's Details
@endsection

@section('content')
 <div class="panel panel-default">
    <div class="panel-body">
    	<div class="panel-heading hidden-print">
        	<h3 class="panel-title"><b>@yield('pageTitle')</b>
        		<big><b class="text-green"> - {{strtoupper($getStaff->surname." ".$getStaff->first_name." ".$getStaff->othernames)}}</b></big><span id='processing'></span>
        	</h3>
    	</div>


        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-user"></i> Particular Information
                </h3>
            </div>

            <div class="panel-body">

                <form method="post" action="{{ url('/process/particular') }}">
                    {{ csrf_field() }}

                    @php
                        if($details != ''){
                            echo '<input type="hidden" name="hiddenName" value="'.$details->particularID.'" />';
                        }else{
                            echo '<input type="hidden" name="hiddenName" value="" />';
                        }
                    @endphp

                    <!-- Wife Info -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Wife's Name</label>
                                <input type="text" name="wifeName" class="form-control"
                                    value="{{ $details ? $details->wifename : '' }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Marriage</label>
                                <input type="text" name="dateOfMarriage2" id="dateOfMarriage2" class="form-control"
                                    value="{{ $details ? date('d-m-Y', strtotime($details->dateofmarriage)) : '' }}">
                                <input type="hidden" name="dateOfMarriage" id="dateOfMarriage"
                                    value="{{ $details ? $details->dateofmarriage : '' }}">
                            </div>
                        </div>


                    </div>

                    <!-- Address & DOB -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Home Place Address</label>
                                <input type="text" name="homePlace" class="form-control"
                                    value="{{ $details ? $details->homeplace : '' }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Wife's Date of Birth</label>
                                <input type="text" name="wifeDateOfBirth2" id="wifeDateOfBirth2" class="form-control"
                                    value="{{ $details ? date('d-m-Y', strtotime($details->wifedateofbirth)) : '' }}">
                                <input type="hidden" name="wifeDateOfBirth" id="wifeDateOfBirth"
                                    value="{{ $details ? $details->wifedateofbirth : '' }}">
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <!-- Checked By -->
                    {{-- <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Checked By</label>
                                <input type="text" name="checkedBy" class="form-control"
                                    value="{{ $details ? $details->checkedby1 : '' }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Checked By</label>
                                <input type="text" name="checkedBy2" class="form-control"
                                    value="{{ $details ? $details->checkedby2 : '' }}">
                            </div>
                        </div>
                    </div> --}}



                    <!-- Buttons -->
                    <div class="row">
                        <div class="col-md-6">
                            <a href="javascript: loadProfileDetail('{{$staffid}}')"
                            class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Back
                            </a>
                        </div>

                        <div class="col-md-6 text-right">
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
                <h3 class="panel-title">
                    <i class="fa fa-list"></i> Wife / Particular Details
                </h3>
            </div>

            <div class="panel-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Wife Name</th>
                                <th>Home Place</th>
                                <th>Date of Marriage</th>
                                <th>Wife Date of Birth</th>
                                {{-- <th>Checked By</th>
                                <th>Checked By</th> --}}
                                <th>Edit</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                        @php if($KinList != ''){ @endphp
                            @php $key = 1 @endphp
                            @foreach($KinList as $list)
                            <tr>
                                <td>{{ $key++ }}</td>
                                <td>{{ $list->wifename }}</td>
                                <td>{{ $list->homeplace }}</td>
                                <td>{{ date('d-m-Y', strtotime($list->dateofmarriage)) }}</td>
                                <td>{{ date('d-m-Y', strtotime($list->wifedateofbirth)) }}</td>
                               
                                <td>
                                    <a href="{{ url('/particular/edit/'.$list->particularID) }}"
                                    class="btn btn-success btn-sm fa fa-edit" title="Edit">
                                    </a>
                                </td>
                                <td>
                                    <!-- remove button can go here -->
                                </td>
                            </tr>
                            @endforeach
                        @php }else{ @endphp
                            <tr>
                                <td colspan="11" class="text-center">No record found!</td>
                            </tr>
                        @php } @endphp
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



	</div>
</div>
<form method="post" id="displayform" name="displayform"  action="{{url('/profile/details')}}" >

                {{ csrf_field() }}

                <input type="hidden" id="fileNos" name="fileNo" >



</form>
@endsection
@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@endsection
{{-- @section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
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
		    dateFormat: "dd-mm-yy",
		    //dateFormat: "D, MM d, yy",
		    onSelect: function(dateText, inst){
		    	var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('dd-mm-yy', theDate);
				$("#dateOfBirth").val(dateFormatted);
        	},
		});
		$("#dateOfMarriage2").datepicker({
			changeMonth: true,
	    	changeYear: true,
	    	yearRange: '1910:2090', // specifying a hard coded year range
		    showOtherMonths: true,
		    selectOtherMonths: true,
		    dateFormat: "dd-mm-yy",
		    onSelect: function(dateText, inst){
		    	var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('dd-mm-yy', theDate);
				$("#dateOfMarriage").val(dateFormatted);
        	},
		});

		$("#wifeDateOfBirth2").datepicker({
			changeMonth: true,
	    	changeYear: true,
	    	yearRange: '1910:2090', // specifying a hard coded year range
		    showOtherMonths: true,
		    selectOtherMonths: true,
		    dateFormat: "dd-mm-yy",
		    onSelect: function(dateText, inst){
		    	var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('dd-mm-yy', theDate);
				$("#wifeDateOfBirth").val(dateFormatted);
        	},
		});

  } );
</script>
@endsection --}}

@section('scripts')
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function loadProfileDetail(staffid) {
    document.getElementById('fileNos').value = staffid;
    document.forms["displayform"].submit();
    return;
}

$(function() {
    // Helper: convert dd-mm-yy to yyyy-mm-dd
    function formatToYMD(dateText) {
        const parts = dateText.split('-'); // ["03", "11", "2025"]
        return `${parts[2]}-${parts[1]}-${parts[0]}`; // "2025-11-03"
    }

    $("#dateOfMarriage2").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '1910:2090',
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: "dd-mm-yy",
        onSelect: function(dateText, inst){
            const formatted = formatToYMD(dateText);
            $("#dateOfMarriage").val(formatted);
        },
    });

    $("#wifeDateOfBirth2").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '1910:2090',
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: "dd-mm-yy",
        onSelect: function(dateText, inst){
            const formatted = formatToYMD(dateText);
            $("#wifeDateOfBirth").val(formatted);
        },
    });

    $("#dateOfBirth2").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '1910:2090',
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: "dd-mm-yy",
        onSelect: function(dateText, inst){
            const formatted = formatToYMD(dateText);
            $("#dateOfBirth").val(formatted);
        },
    });
});
</script>


@if (session('msg'))
<script>
Swal.fire({
    toast: true,
    position: 'top-end', // top-end, top-start, bottom-end, etc.
    icon: 'success',
    title: '{{ session("msg") }}',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});
</script>
@endif
@endsection


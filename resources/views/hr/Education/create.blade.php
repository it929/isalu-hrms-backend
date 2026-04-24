@extends('layouts.layout')

@section('pageTitle')
 Add Education
@endsection

@section('content')
 <div class="panel panel-primary">
    <div class="panel-body">
    	<div class="panel-heading  hidden-print">
        	<h3 class="panel-title"><b>@yield('pageTitle')</b>
        		<big><b class="text-green"> - {{strtoupper($getStaff->surname." ".$getStaff->first_name." ".$getStaff->othernames)}}</b></big><span id='processing'></span>
        	</h3>
    	</div>


        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Education Information</h3>
            </div>

            <div class="panel-body">

                <form method="post" action="{{ url('/education/create') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-12">

                            <!-- Degrees & Qualification -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> Qualifications</label>

                                        @php if(($details != "")){ @endphp
                                            <select name="degreeQualification" class="form-control">
                                                <option>{{$details->degreequalification}}</option>
                                                @foreach($qualificationList as $qList)
                                                    <option>{{$qList->qualification}}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="id" value="{{$details->id}}">
                                        @php }else{ @endphp
                                            <select name="degreeQualification" class="form-control">
                                                <option></option>
                                                @foreach($qualificationList as $qList)
                                                    <option>{{$qList->qualification}}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="id" value="">
                                        @php } @endphp
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>School Attended</label>
                                        <input type="text" name="schoolAttended" class="form-control"
                                            value="{{ $details ? $details->schoolattended : old('schoolAttended') }}">
                                    </div>
                                </div>

                                 <div class="col-md-3">
                                    <div class="form-group">
                                        <label>School Attended From</label>
                                        <input type="text" name="schoolFrom2" id="schoolFrom2" class="form-control"
                                            value="{{ $details ? date('d M, Y', strtotime($details->schoolfrom)) : old('schoolFrom2') }}">
                                        <input type="hidden" name="schoolFrom" id="schoolFrom"
                                            value="{{ $details ? $details->schoolfrom : old('schoolFrom') }}">
                                    </div>
                                </div>

                                 <div class="col-md-3">
                                    <div class="form-group">
                                        <label>School Attended To</label>
                                        <input type="text" name="schoolTo2" id="schoolTo2" class="form-control"
                                            value="{{ $details ? date('d M, Y', strtotime($details->schoolto)) : old('schoolTo2') }}">
                                        <input type="hidden" name="schoolTo" id="schoolTo"
                                            value="{{ $details ? $details->schoolto : old('schoolTo') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- School From/To -->
                            {{-- <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>School Attended From</label>
                                        <input type="text" name="schoolFrom2" id="schoolFrom2" class="form-control"
                                            value="{{ $details ? date('d M, Y', strtotime($details->schoolfrom)) : old('schoolFrom2') }}">
                                        <input type="hidden" name="schoolFrom" id="schoolFrom"
                                            value="{{ $details ? $details->schoolfrom : old('schoolFrom') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>School Attended To</label>
                                        <input type="text" name="schoolTo2" id="schoolTo2" class="form-control"
                                            value="{{ $details ? date('d M, Y', strtotime($details->schoolto)) : old('schoolTo2') }}">
                                        <input type="hidden" name="schoolTo" id="schoolTo"
                                            value="{{ $details ? $details->schoolto : old('schoolTo') }}">
                                    </div>
                                </div>
                            </div> --}}

                            <!-- Certificates and Checked By -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>School Certificates Held</label>
                                        <input type="text" name="certificateHeld" class="form-control"
                                            value="{{ $details ? $details->certificateheld : old('certificateHeld') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Checked By</label>
                                        <input type="text" name="checkedEducation" class="form-control"
                                            value="{{ $details ? $details->checkededucation : old('checkedEducation') }}">
                                    </div>
                                </div>

                                 <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Attach Document (Optional)</label>
                                        <input type="file" name="document" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-3 ">

                                    {{-- <button class="btn btn-success" type="submit">
                                        Update/Add New <i class="fa fa-save"></i>
                                    </button> --}}
                                    <div class="form-group">
                                        <!-- Empty label to align button with inputs -->
                                        <label>&nbsp;</label>
                                        <button class="btn btn-success btn-block" type="submit">
                                            Update/Add New <i class="fa fa-save"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Upload -->
                            {{-- <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Attach a Scanned Document (Optional)</label>
                                        <input type="file" name="document" class="form-control">
                                    </div>
                                </div>
                            </div> --}}

                            <hr>

                            <!-- Buttons -->
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="javascript: loadProfileDetail('{{$staffid}}')" class="btn btn-warning">
                                        <i class="fa fa-arrow-circle-left"></i> Back
                                    </a>
                                </div>

                                {{-- <div class="col-md-9 text-right">
                                    <button class="btn btn-success" type="submit">
                                        Update/Add New <i class="fa fa-save"></i>
                                    </button>
                                </div> --}}
                            </div>

                        </div>
                    </div>

                </form>
            </div>
        </div>



        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Education Records</h3>
            </div>

            <div class="panel-body" style="padding:0;">
                <table class="table table-striped table-hover table-bordered" style="margin:0;">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Surname</th>
                            <th>FileNo</th>
                            <th>Qualifications</th>
                            <th>School Attended</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Certificates Held</th>
                            <th>Checked By</th>
                            <th>Doc</th>
                            <th>Edit</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @php if($educationList != ""){ @endphp
                            @php $key = 1 @endphp
                            @foreach($educationList as $list)
                            <tr>
                                <td>{{ $key++ }}</td>
                                <td>{{ $list->surname }}</td>
                                <td>{{ $list->fileNo }}</td>
                                <td>{{ $list->degreequalification }}</td>
                                <td>{{ $list->schoolattended }}</td>
                                <td>{{ date('d-M-Y', strtotime($list->schoolfrom)) }}</td>
                                <td>{{ date('d-M-Y', strtotime($list->schoolto)) }}</td>
                                <td>{{ $list->certificateheld }}</td>
                                <td>{{ $list->checkededucation }}</td>
                                <td>
                                    @php if($list->document != ""){ @endphp
                                        <a href="{{ asset('document/'.$list->document) }}" target="_blank">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    @php }else{ @endphp
                                        No File
                                    @php } @endphp
                                </td>
                                <td>
                                    <a href="{{ url('/education/edit/'.$list->id) }}"
                                    class="btn btn-sm btn-success fa fa-edit"
                                    title="Edit"></a>
                                </td>
                                <td></td>
                            </tr>
                            @endforeach
                        @php }else{ @endphp
                            <tr>
                                <td colspan="12" class="text-center">No details provided yet!</td>
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
function  loadProfileDetail(staffid)
{
document.getElementById('fileNos').value = staffid;
document.forms["displayform"].submit();
return;

}
</script>
  {{-- <script type="text/javascript">

	$( function() {
	    $("#schoolFrom2").datepicker({
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
				$("#schoolFrom").val(dateFormatted);
        	},
		});
  	});

  	$( function() {
	    $("#schoolTo2").datepicker({
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
				$("#schoolTo").val(dateFormatted);
        	},
		});
  	});

</script> --}}

<script>
$(function () {
    $("#schoolFrom2").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '1910:2090',
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: "dd-mm-yy", // visible field format
        onSelect: function (dateText, inst) {
            // Convert to YYYY-MM-DD for database
            const parts = dateText.split('-');
            const formatted = parts[2] + '-' + parts[1] + '-' + parts[0];
            $("#schoolFrom").val(formatted);
        }
    });

    $("#schoolTo2").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '1910:2090',
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: "dd-mm-yy", // visible field format
        onSelect: function (dateText, inst) {
            const parts = dateText.split('-');
            const formatted = parts[2] + '-' + parts[1] + '-' + parts[0];
            $("#schoolTo").val(formatted);
        }
    });
});
</script>

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

<script>

@endsection

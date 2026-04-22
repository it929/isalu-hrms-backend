@extends('layouts.layout')

@section('pageTitle')
  Update Next of Kin Details
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
                <h3 class="panel-title"><i class="fa fa-user"></i> Next of Kin Information</h3>
            </div>

            <div class="panel-body">

                <form method="post" action="{{ url('/update/next-of-kin/') }}">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name</label>
                                @php
                                    if($nextOfKin != ''){
                                        echo '
                                            <input type="text" name="fullName" class="form-control" value="'.$nextOfKin->fullname.'" />
                                            <input type="hidden" name="kinID" value="'.$nextOfKin->kinID.'" />
                                            <input type="hidden" name="hiddenName" value="'.$nextOfKin->fullname.'" />
                                        ';
                                    }else{
                                        echo '
                                            <input type="text" name="fullName" class="form-control" />
                                            <input type="hidden" name="hiddenName" value="" />
                                        ';
                                    }
                                @endphp
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Relationship</label>
                                @php
                                    if($nextOfKin != ''){
                                        echo '<input type="text" name="relationship" class="form-control" value="'.$nextOfKin->relationship.'" />';
                                    }else{
                                        echo '<input type="text" name="relationship" class="form-control" />';
                                    }
                                @endphp
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Address</label>
                                @php
                                    if($nextOfKin != ''){
                                        echo '<textarea name="address" class="form-control">'.$nextOfKin->address.'</textarea>';
                                    } else {
                                        echo '<textarea name="address" class="form-control"></textarea>';
                                    }
                                @endphp
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number</label>
                                @php
                                    if($nextOfKin != ''){
                                        echo '<input type="text" name="phoneNumber" class="form-control" value="'.$nextOfKin->phoneno.'" placeholder="Optional" />';
                                    } else {
                                        echo '<input type="text" name="phoneNumber" class="form-control" placeholder="Optional" />';
                                    }
                                @endphp
                            </div>
                        </div>
                    </div>

                    <hr />

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <a href="javascript: loadProfileDetail('{{$staffid}}')"
                                class="btn btn-warning">
                                    <i class="fa fa-arrow-circle-left"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="col-md-9" align="right">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button class="btn btn-success" type="submit">
                                    Update / Add New Next of Kin <i class="fa fa-save"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </form>

            </div>
        </div>


        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Next of Kin Details</h3>
            </div>

            <div class="panel-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Full Name</th>
                            <th>Relationship</th>
                            <th>Address</th>
                            <th>Phone Number</th>
                            <th>Edit</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php if($KinList != ''){ @endphp
                            @php $key = 1 @endphp

                            @foreach($KinList as $list)
                                <tr>
                                    <td>{{ $key++ }}</td>
                                    <td>{{ $list->fullname }}</td>
                                    <td>{{ $list->relationship }}</td>
                                    <td>{{ $list->address }}</td>
                                    <td>{{ $list->phoneno }}</td>

                                    <td>
                                        <a href="{{ url('/update/view/'.$list->kinID) }}"
                                        class="btn btn-success btn-sm"
                                        title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        @php } else { @endphp
                            <tr>
                                <td colspan="7" class="text-center text-danger">
                                    No details provided yet!
                                </td>
                            </tr>
                        @php } @endphp
                    </tbody>
                </table>
            </div>
        </div>



		  <form action="{{url('/process/next-of-kin/')}}" method="post">
		  {{ csrf_field() }}
		  		<!-- Modal -->
				<div class="bs-example">
			    <!-- Modal HTML -->
			    <div id="myModal" class="modal fade">
			        <div class="modal-dialog">
			            <div class="modal-content" style="padding: 10px; border-radius: 6px;">

			                <div class="box box-default">
    							<div class="box-body box-profile">
					                <div class="modal-header">
					                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					                    <h4 class="modal-title"><b>Add New Next of Kin</b></h4>
					                </div>
					                <div class="modal-body">
					                    <div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="month">Full Name</label>
												<input type="text" name="fullName" class="form-control" />
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label for="month">Relationship</label>
												<input type="text" name="relationship" class="form-control"/>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="month">Full Address</label>
												<textarea name="address" class="form-control"></textarea>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label for="month">Phone Number</label>
												<input type="text" name="phoneNumber" class="form-control" placeholder="Optional" />
											</div>
										</div>
									</div>
					                </div>
					              </div>
					            </div>

			                <div class="modal-footer-not-use" align="right">
			                    <button type="button" class="btn btn-warning" data-dismiss="modal">
			                    	<i class="fa fa-arrow-circle-left"></i> Close
			                    </button>
			                    <button type="submit" class="btn btn-primary">
			                    	<i class="fa fa-save"></i> Save
			                    </button>
			                </div>

			            </div>
			        </div>
			    </div>
			</div>
		  </form>
	</div>
</div>

<form method="post" id="displayform" name="displayform"  action="{{url('/profile/details')}}">

                {{ csrf_field() }}

                <input type="hidden" id="fileNos" name="fileNo" >



</form>
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
  <script type="text/javascript">
	//Modal popup
	$(document).ready(function(){
		$('.open-modal').click(function(){
			$('#myModal').modal('show');
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

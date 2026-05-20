@extends('layouts.layout')
@section('pageTitle')
Assign Head of Department
@endsection

@section('content')

   <div class="box box-default">
            <div class="box-header with-border hidden-print">
            <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
            </div>
            
                        
        <div class="box-body">
            <div class="form-group">
            	@include('Share.message')
                <form class="form-horizontal"  action="{{url('/department/departmentHeaD')}}" method="POST">
                    {{ csrf_field() }}
                    <!--hidden field for updating record-->
                    <div class="form-group">
                        <div class="col-md-6">
                        	
                                
                                  <label class="control-label">Departments</label>    
                                    <select class="form-control" name="departmentName">
                                       <option> select department </option>
                                       @foreach ($getDept as $list)
                                         <option  value="{{$list->id}}">{{$list->department}}</option> 
                                       @endforeach                           
                                </select>                               
                                
                        </div>
                        <div class="col-md-6">
                        	<label class="control-label">Head of department </label>                                
                           	 <select type="text" class="form-control" name="headOfDepartment"  id="input-tags2" onchange="input()" placeholder="--Search for Staff to assign--">
                           	   <option value="">--select staff as head--</option>
                           	     @foreach ($getTheStaff as $getStaff)
                              		<option  value="{{ $getStaff->UserID}}">{{ $getStaff->fileNo.' - '. $getStaff->surname .' '. $getStaff->first_name .' '. $getStaff->othernames}}</option>
                              		@endforeach
  			      </select>                   
                        </div>
                       
                      
                        <div class="col-md-8" > 
                         <span>&nbsp;</span>
                        	<center><button type="submit" class="btn btn-success" name="btnSave">Assign as head</button></center>
                        </div>
                    </div>
              </form>
           </div>
            
			           <div class="col-md-12">     
			             <div class="table-responsive" style="font-size: 12px; padding:40px;">
			                    <table id="mytable" class="table table-bordered table-striped table-highlight" >
			                        <thead>
			                            <tr bgcolor="#c7c7c7">
			                                        
			                                
			                                <th> S/N</th>
			                                <th >Department</th>
			                                <th >Head Of Department</th>
			                                <th ></th>
			                                                                                                                                            
			                            </tr>
			                        </thead>
			                    
			                    
			                    @forelse($mergeUserID as $i=>$list)
                                        <tr>
                                            <td>{{ 1 + $i++}}</td>
                                            <td>{{$list->department}}</td>
                                            <td>{{$list->surname.' '. $list->first_name}}</td>

                                           
                                            <td>
                                                     
                                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Edit</button> 
 
                                                    <!-- Modal -->
                                                    	
						        <form class="form-horizontal" action="{{route('departmentHead.update')}}" method="post" role="form">
						        	 {{ csrf_field() }}
								<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
								  <div class="modal-dialog" role="document">
								    <div class="modal-content">
								      <div class="modal-header" style="background-color:#00a65a;">							      	
								          <h5 class="modal-title" id="exampleModalLabel" style="font-color:white;">Re-assign new Head of Department</h5>
									        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
									          <span aria-hidden="true">&times;</span>
									        </button>
								        
								      </div>
								      <div class="modal-body">
								           <label class="control-label">Departments</label>
								            <select class="form-control" name="departmentName">
								        	<option value=""> select department </option>
						                                       @foreach ($getDept as $list)
						                                         <option  value="{{$list->id}}">{{$list->department}}</option> 
						                                       @endforeach  
						                             </select>
						                             		<br>
						                                       <span>&nbsp;</span> 
						                                       
						                                   <label class="control-label">Head of department </label>                                
							                           	 <select type="text" class="form-control" name="headOfDepartment"  id="input-tags2" onchange="input()" placeholder="--Search for Staff to assign--">
							                           	   <option value="">--select staff as head--</option>
							                           	     @foreach ($getTheStaff as $getStaff)
							                              	   <option  value="{{ $getStaff->UserID}}">{{ $getStaff->fileNo.' - '. $getStaff->surname .' '. $getStaff->first_name .' '.
							                              		 	$getStaff->othernames}}</option>
							                              	     @endforeach
							  			          </select>
						                                                               
								      </div>
								     
								      <div class="modal-footer">
								        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
								        <button type="submit" class="btn btn-primary">Save changes</button>
								      </div>
								    </div>
								  </div>
								</div>
							</form>
                                            </td>                 
                                        </tr>
                                    @empty
                                    	<tr>
                                    	    <td colspan="4" align="center">No Record Found!</td>
                                    	</tr>
                                    @endforelse
                                    </table>
                </div>
          </div>      
        </div>    
    </div>                                 
    
                                 
@endsection

@section('scripts')
<script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/app.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
 	<script type="text/javascript">
 		
 		$('#input-tags2').selectize({
    plugins: ['restore_on_backspace'],
    delimiter: ',',
    persist: false,
    create: function(input) {
        return {
            value: input,
            text: input
        }
    }
});
		
		    
		
 	 function myFunction(val){
 	 
 	     alert(val);
 	 
 	 }
 	}
 	
 	</script>
	
	
@endsection 
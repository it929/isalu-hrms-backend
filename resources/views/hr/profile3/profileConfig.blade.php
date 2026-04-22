@extends('layouts.layout')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header with-border hidden-print">
        <h3 class="box-title">PROFILE CONFIGURATION <span id='processing'></span></h3>
      </div>
      <!--
      <form method="post" action="{{ url('/profile/details') }}">
        <div class="box-body row">
             <div class="form-group col-md-10">
                {{ csrf_field() }}
               <input id="autocomplete" name="q" class="form-control input-lg" placeholder="Search By First Name, Surname or File Number">
               <input type="hidden" id="fileNo"  name="fileNo">
            </div>
            <div class="col-md-2">
              <button type="submit" name="searchName" id="searchName" class="btn btn-default btn-lg"><i class="fa fa-search"></i> Search</button>
             
            </div>
        </div>

      </form>
      -->
      <!--
      <div style="margin-left:10px;margin-bottom:10px;">
          <a href="{{ url('/profile/details') }}"><button type="submit" name="searchName" id="searchName" class="btn btn-default btn-lg"><i class="fa fa-search"></i> Search another</button></a>
      </div>
      -->
      <br>
    </div>
  </div><!-- /.col -->
</div><!-- /.row -->

<div class="box-body">
    <div class="row">
        <div class="col-md-12">
            @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                  <strong>Error!</strong> 
                  @foreach ($errors->all() as $error)
                      <p>{{ $error }}</p>
                  @endforeach
                  </div>
                  @endif                       
                        
                  @if(session('msg'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Success!</strong> {{ session('msg') }}</div>                        
                  @endif
                 
        </div>
    </div>
</div>


          <!--NEXT OF KIN-->
          <div class="box box-success">
            <div class="box-body box-profile">
               <div class="table-responsive">
              <h3 class="profile-username text-center">{{strtoupper('Settings')}}</h3>
              <table class="table table-condensed">
                  <thead class="text-gray-b">
                        <tr>
                            <td><b></b></td>
                            <td><b>Biodata</b></td>
                            <td><b>Education</b></td>
                            <td><b>Birth Part.</b></td>
                            <td><b>Language</b></td>
                            
                            <td><b>Children Part.</b></td>
                            <td><b>Salary</b></td>
                            <td><b>Next of Kin</b></td>
                            <td><b>Wife Part.</b></td>
                            
                            <td><b>Part. of Prev.<br> Public Serv.</b></td>
                            <td><b>Censors & Recomm.</b></td>
                            <td><b>Gratuity Payment</b></td>
                            <td><b>Termination Service</b></td>
                            
                            <td><b>Tour & Leave</b></td>
                            <td><b>Record of Service</b></td>
                            <td><b>Record of Emolument</b></td>
                            
                            <td><b></b></td>
                        </tr>
                  </thead>
                  <tbody>
                   
                        <tr>
                          <td></td>
                          <td>
                            <select class="form-control" id="select1">
                                @if($getIDs->biodata==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->biodata==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv">@if($getIDs->biodata==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->biodata==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok"></div>
                            <div id="oks"></div>
                           
                                
                          </td>
                          <td>
                            <select class="form-control" id="select2">
                            @if($getIDs->education==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->education==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv2">@if($getIDs->education==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->education==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok2"></div>
                            <div id="oks2"></div>
                          </td>
                          <td>
                            <select class="form-control" id="select3">
                            @if($getIDs->birthparticulars==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->birthparticulars==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv3">@if($getIDs->birthparticulars==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->birthparticulars==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok3"></div>
                            <div id="oks3"></div>
                            
                          </td>
                          <td>
                            <select class="form-control" id="select4">
                            @if($getIDs->language==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->language==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv4">@if($getIDs->language==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->language==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok4"></div>
                            <div id="oks4"></div>
                            
                          </td>
                          <td>
                            <select class="form-control" id="select5">
                             @if($getIDs->childrenparticulars==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->childrenparticulars==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv5">@if($getIDs->childrenparticulars==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->childrenparticulars==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok5"></div>
                            <div id="oks5"></div>
                          
                          </td>
                          <td>
                            <select class="form-control" id="select6">
                            @if($getIDs->salary==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->salary==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv6">@if($getIDs->salary==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->salary==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok6"></div>
                            <div id="oks6"></div>
                          </td>
                          <td>
                            <select class="form-control" id="select7">
                             @if($getIDs->nok==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->nok==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv7">@if($getIDs->nok==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->nok==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok7"></div>
                            <div id="oks7"></div>
                            
                          </td>
                          <td>
                            <select class="form-control" id="select8">
                            @if($getIDs->wifeparticulars==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->wifeparticulars==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv8">@if($getIDs->wifeparticulars==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->wifeparticulars==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok8"></div>
                            <div id="oks8"></div>
                            
                          </td>
                          <td>
                            <select class="form-control" id="select9">
                             @if($getIDs->publicservice==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->publicservice==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv9">@if($getIDs->publicservice==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->publicservice==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok9"></div>
                            <div id="oks9"></div>
                            
                          </td>
                          <td>
                            <select class="form-control" id="select10">
                            @if($getIDs->censors_recomm==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->censors_recomm==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv10">@if($getIDs->censors_recomm==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->censors_recomm==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok10"></div>
                            <div id="oks10"></div>
                            
                          </td>
                          <td>
                            <select class="form-control" id="select11">
                            @if($getIDs->gratuitypayment==1)
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->gratuitypayment==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv11">@if($getIDs->gratuitypayment==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->gratuitypayment==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok11"></div>
                            <div id="oks11"></div>
                            
                          </td>
                          <td>
                            <select class="form-control" id="select12">
                            @if($getIDs->terminaofservice==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->terminaofservice==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv12">@if($getIDs->terminaofservice==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->terminaofservice==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok12"></div>
                            <div id="oks12"></div>
                            
                          </td>
                          <td>
                            <select class="form-control" id="select13">
                             @if($getIDs->tour_leave==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->tour_leave==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv13">@if($getIDs->tour_leave==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->tour_leave==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok13"></div>
                            <div id="oks13"></div>
                           
                          </td>
                          <td>
                            <select class="form-control" id="select14">
                             @if($getIDs->record_service==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->record_service==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv14">@if($getIDs->record_service==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->record_service==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok14"></div>
                            <div id="oks14"></div>
                           
                          </td>
                          <td>
                            <select class="form-control" id="select15">
                             @if($getIDs->record_emolument==1)
                             <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                           
                                @elseif($getIDs->record_emolument==0)
                            <option value="{{'1'}}" {{ ( 1 == 1) ? "selected" : ""}}>Yes</option>
                            <option value="{{'0'}}" {{ ( 0 == 0) ? "selected" : ""}}>No</option>
                            
                                @endif
                            </select>
                            <div id="hidediv15">@if($getIDs->record_emolument==1)<i class="glyphicon glyphicon-ok" style="color:green" ></i>@elseif($getIDs->record_emolument==0)<i class="glyphicon glyphicon-remove"></i>@endif</div>
                            <div id="ok15"></div>
                            <div id="oks15"></div>
                           
                          </td>
                          
                          <td></td>
                        </tr>
                   
                  </tbody>
              </table>
             </div>
             
            </div>
          </div>
          <!--//NEXT OF KIN-->
</div><!--//main roll2-->


@endsection
@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom-style.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">
@endsection
@section('styles')
<style> 

  #editSALARYINFO{
    
    
    display: table;
    height: 100%;
    width: 100%;
    position:absolute;
    background-color:#FF0000;   
    margin-top:250px;
}

  .textbox { 
    border: 1px;
    background-color: #33AD0A; 
    outline:0; 
    height:25px; 
    width: 275px; 
  } 
  $('.autocomplete-suggestions').css({
    color: '#0f3'
  });

  .autocomplete-suggestions{
    color:#fff;
    font-size: 15px;
  }
</style> 
@endsection
@section('scripts')
<!--loading vuejs -->
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.js"></script>

<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<!-- autocomplete js-->
<script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
<!--<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>-->

<script>
 
 $(document).ready(function() {
    $('#').DataTable();
  } );

  $(document).ready(function() {
      $('#mytable').DataTable( {
          dom: 'Bfrtip',
          //"pageLength": 1,
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
<script type="text/javascript">
  $(function() {
      $('#searchName').attr("disabled", false);
      $("#autocomplete").autocomplete({
        serviceUrl: murl + '/profile/searchUser',
        minLength: 2,
        onSelect: function (suggestion) {
            $('#fileNo').val(suggestion.data);
            $('#searchName').attr("disabled", false);
            showAll();
        }
      });
  });
</script>

<script>

 function profilePicEdit(x){
     
      document.getElementById('PfileID').value = x;
     
       $("#editProfilePic").modal('show')
     
 }
 
</script>

<script>

 function profileEdit(x,y,z,a,b,c,d,e,f,g,h,i,j){
     
      document.getElementById('fileID').value = x;
      document.getElementById('fileNo').value = y;
      document.getElementById('divs').value = z;
      document.getElementById('titles').value = a;
      document.getElementById('surname').value = b;
      document.getElementById('firstname').value = c;
      document.getElementById('othernames').value = d;
      
      document.getElementById('address').value = e;
      document.getElementById('gender').value = f;
      document.getElementById('currentstate').value = g;
      document.getElementById('phone').value = h;
      document.getElementById('nationality').value = i;
      document.getElementById('status').value = j;

       $("#editBIO").modal('show')
     
 }
 
</script>

@include('profile.jsscripts')
@endsection
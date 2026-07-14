@extends('layouts.layout')
@section('pageTitle')
PE-CARD
@endsection
@section('content')
<!-- /.box-header -->
<div class="box-body">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
      <b><p class="profile-username text-center text-success">ISALU HOSPITAL LIMITED
      </p></b>
      <b><p class="profile-username text-center text-success">PAYROLL</p></b>
      <b><p class="text-muted text-left ">Payroll P.V. No:</p></b>
      <p class="text-muted text-left ">MINISTRY/DEPARTMENT: ISALU HOSPITAL LIMITED, {{session('division')}} DIVISION </p>
      <b><p class="text-muted text-left "> MONTH ENDING: {{$bankName}} {{$bankgroup}}</p></b>
      <table class="table table-hover">
        <tr>
            <th>
               <div>
                  File No
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  &nbsp;&nbsp;&nbsp;
                  Name
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  &nbsp;&nbsp;&nbsp;
                  
              </div>
            </th>    
        </tr> 
        @foreach ($users as $user)
        <tr>
           <td> 
              <div >
                <a  href = "{{url('pecard/pereport/'. $user->fileNo.'/'.$year )}}">  {{ $user->fileNo }} 
                    &nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                    {{ $user->surname  }}  {{ $user->first_name  }} 
                    &nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    @php
                       
                    @endphp
                    
                    
                </a>
              </div>
           </td>
            </tr>
          @endforeach
         
        </table>
      </div>
    </div>
    <!-- /.box-body -->
  </div>
  <!-- /.box -->
    @endsection

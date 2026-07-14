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
                  <div class="text-center" style="margin-top: -20px; margin-left: 110px;"> 
                     Current Bank Group
                  </div>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  &nbsp;&nbsp;&nbsp;
                  <div class="pull-right" style="margin-top: -20px;"> 
                     
                  </div>
              </div>
            </th>    
        </tr> 
        @foreach ($users as $user)
        <tr>
           <td> 
              <div>
                <a  href = "{{url('pecard/pereport/'. $user->fileNo.'/'.$user->year )}}">  {{ $user->fileNo }} 
                    &nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                    {{ $user->name  }} 
                    &nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    @php
                        $newBankGroup = DB::table('tblper')
                          ->select('bankGroup')
                          ->where('fileNo', $user->fileNo)
                          ->first();
                    @endphp
                    <div class="text-center" style="margin-left: 110px; margin-top: -25px;"> 
                      {{$newBankGroup->bankGroup}}
                    </div>
                    &nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <div class="pull-right" style="width: 35%"> 
                        <small>
                        @if($newBankGroup->bankGroup != $user->bankGroup)
                          This person shows under bank group {{$user->bankGroup}}  because he/she has been paid using this bank group {{$user->bankGroup}} but the Current/New bank group is: {{$newBankGroup->bankGroup}}. 
                          @endif
                        </small>
                    </div>
                    
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

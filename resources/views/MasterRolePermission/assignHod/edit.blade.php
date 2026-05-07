@extends('layouts.layout')
@section('pageTitle')
EDIT USER USER
@endsection

@section('content')
  <div id="page-wrapper" class="box box-default">
            <div class="container-fluid">

            <div class="col-md-12 text-success"><!--2nd col-->
                <big><b>@yield('pageTitle')</b></big>
            </div>
            <br />
            <hr >
                <div class="row">
                    <div class="col-md-9">
                       <br>
                        @if (count($errors) > 0)
                        <div class="alert alert-danger alert-dismissible" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Error!</strong> 
                             @foreach ($errors->all() as $error)
                                  <p>{{ $error }}</p>
                             @endforeach
                        </div>
                       @endif                       
                     
                        @if(session('message'))
                        <div class="alert alert-success alert-dismissible" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Success!</strong> {{ session('message') }}</div>                        
                        @endif
                          @if(session('error_message'))
                        <div class="alert alert-error alert-dismissible" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Error!</strong> {{ session('error_message') }}</div>                        
                        @endif
                       <form method="post" action="{{ url('/user-assign/assign') }}" class="form-horizontal">
                         {{ csrf_field() }}

                        <div class="form-group">
                        <label for="section" class="col-md-3 control-label">Select User</label>
                        <div class="col-md-9">
                          <select name="user" class="form-control" id="role">
                          <option value="">Select One</option>
                          @foreach($users as $list)
                            <option value="{{$list->id}}">{{$list->name}}</option>
                          @endforeach
                          </select>
                        </div>
                      </div>

                       <div class="form-group">
                        <label for="section" class="col-md-3 control-label">Select Role</label>
                        <div class="col-md-9">
                          <select name="role" class="form-control" id="role">
                          <option value="">Select One</option>
                          @foreach($roles as $list)
                            <option value="{{$list->roleID}}">{{$list->rolename}}</option>
                          @endforeach
                          </select>
                        </div>
                      </div>

                        
                      

                     <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                          <button type="submit" class="btn btn-success btn-sm pull-right">Edit USER ROLE</button>
                        </div>
                      </div>                      
                    
                       </form>
                    </div>
                    
                </div>
            </div>
        </div>


<div id="page-wrapper" class="box box-default">
    <div class="box-body">
    <h2 class="text-center">ASSIGNED USERS</h2>
    <div class="row">
      {{ csrf_field() }}

      <div class="col-md-12">
        <table class="table table-striped table-condensed table-bordered input-sm">
          <thead>
          <tr class="input-sm">
              <th>S/N</th>
              <th> NAME</th>
              <th> Role</th>
              <th>Date Created</th>
              <th></th>
              </tr>
          </thead>
          <tbody>
            @php $key = 1; @endphp
            @foreach($userroles as $list)
              <tr>
                  <td>{{$key++}}</td> 
                  <td>{{strtoupper($list->name)}}</td>
                  <td>{{strtoupper($list->rolename)}}</td>  
                  <td>{{$list->created_at}}</td>
                  <td><a href="{{url('/user-assign/edit/'.$list->assignuserID)}}" title="Edit" class="btn btn-success fa fa-edit"></a></td>          
              </tr>
            @endforeach
          </tbody>
        </table>

      </div>
    </div><!-- /.col -->

    </div>



@endsection
        
@extends('layouts.layout')
@section('pageTitle')
    User Modifications
@endsection
@section('content')


    <div id="editModal" class="modal fade">
        <div class="modal-dialog panel panel-success" role="document">
            <div class="modal-content">
                <div class="modal-header panel-heading">
                    <h4 class="modal-title panel-title">User Modification </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                {{-- <form class="form-horizontal" id="editLgaModal" name="editLgaModal" role="form" method="POST">
                    {{ csrf_field() }}
                    <div class="modal-body panel-body">
                        <div class="form-group" style="margin: 0 10px;">
                            <label class="col-sm-2 control-label">User Name</label>
                            <input type="text" class="col-sm-9 form-control" id="username" readonly>
                        </div>
                        <div class="form-group" style="margin: 0 10px;">
                            <label class="col-sm-2 control-label">Names</label>
                            <input type="text" class="col-sm-9 form-control" id="names" name="name" required>
                        </div>
                        <div class="form-group" style="margin: 0 10px;">
                            <label class="col-sm-2 control-label">Email</label>
                            <input type="text" class="col-sm-9 form-control" id="email" name="email">
                        </div>
                        <div class="form-group" style="margin: 0 10px;">
                            <label class="col-sm-2 control-label">Roles</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value=''>-Select Role-</option>
                                @foreach ($Rolelist as $b)
                                    <option value="{{ $b->roleID }}">{{ $b->rolename }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0 10px;">
                            <label class="col-sm-2 control-label">Password</label>
                            <input type="text" class="col-sm-9 form-control" id="password" name="password">
                        </div>
                        <div class="form-group" style="margin: 0 10px;">
                            <label class="col-sm-2 control-label">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value=''>-Select Status-</option>
                                @foreach ($Statuslist as $b)
                                    <option value="{{ $b->id }}">{{ $b->status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="id" name="id">
                            <button type="Submit" name="edit" class="btn btn-success">Save changes</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>


                    </div>
                </form> --}}

                <form class="form-horizontal" id="editLgaModal" name="editLgaModal" role="form" method="POST">
                    {{ csrf_field() }}

                    <div class="modal-body panel-body">

                        <!-- Row 1 -->
                        <div class="row" style="margin:0 10px;">
                            <div class="col-md-4">
                                <label>User Name</label>
                                <input type="text" class="form-control" id="username" readonly>
                            </div>

                            <div class="col-md-4">
                                <label>Names</label>
                                <input type="text" class="form-control" id="names" name="name" required>
                            </div>

                            <div class="col-md-4">
                                <label>Email</label>
                                <input type="text" class="form-control" id="email" name="email">
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="row" style="margin:0 10px; margin-top:10px;">
                            <div class="col-md-4">
                                <label>Roles</label>
                                <select name="role" id="role" class="form-control" required>
                                    <option value="">-Select Role-</option>
                                    @foreach ($Rolelist as $b)
                                        <option value="{{ $b->roleID }}">{{ $b->rolename }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Password</label>
                                <input type="text" class="form-control" id="password" name="password">
                            </div>

                            <div class="col-md-4">
                                <label>Status</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="">-Select Status-</option>
                                    @foreach ($Statuslist as $b)
                                        <option value="{{ $b->id }}">{{ $b->status }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer">
                            <input type="hidden" id="id" name="id">
                            <button type="submit" name="edit" class="btn btn-success">Save changes</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="panel panel-success">
        {{-- <div class="box-body box-profile"> --}}
        <div class="panel-heading  hidden-print">
            <h3 class="panel-title">@yield('pageTitle') <span id='processing'></span></h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12"><!--1st col-->
                    @if (count($errors) > 0)
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">&times;</span>
                            </button>
                            <strong>Error!</strong>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (session('msg'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">&times;</span>
                            </button>
                            <strong>Success!</strong>
                            {{ session('msg') }}
                        </div>
                    @endif

                    @if (session('err'))
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">&times;</span>
                            </button>
                            <strong>Not Allowed ! </strong>
                            {{ session('err') }}
                        </div>
                    @endif

                    <table class="table table-bordered table-striped table-highlight">
                        <thead>
                            <tr bgcolor="#c7c7c7">

                                <th> S/N</th>
                                <th> User Name</th>
                                <th> Names</th>
                                <th> Email</th>
                                <th> Role</th>
                                <th> Status</th>
                                <th> Action</th>
                            </tr>
                        </thead>
                        @php $i=1;@endphp
                        @foreach ($UserList as $con)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $con->username }}</td>
                                <td>{{ $con->name }}</td>
                                <td>{{ $con->email }}</td>
                                <td>{{ $con->roletext }}</td>
                                <td>{{ $con->statustext }}</td>
                                <td>
                                    <button type="button" class="btn btn-success fa fa-edit"
                                        onclick="editfunc('{{ $con->id }}', '{{ $con->username }}', '{{ $con->name }}','{{ $con->email }}','{{ $con->role }}','{{ $con->status }}' )"
                                        class="" id=""> Edit</button>
                                </td>
                        @endforeach
                        </tr>
                    </table>
                    <hr />
                </div>

            </div>
        </div>
        {{-- </div> --}}
    </div>



@endsection

@section('styles')
    <style type="text/css">
        .modal-dialog {
            width: 10cm
        }

        .modal-header {

            background-color: #006600;

            color: #FFF;

        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script>
        function editfunc(id, usernane, names, email, role, status) {
            $(document).ready(function() {
                $('#username').val(usernane);
                $('#names').val(names);
                $('#email').val(email);
                $('#role').val(role);
                $('#status').val(status);
                $('#id').val(id);
                $("#editModal").modal('show');
            });
        }

        function delfunc(a) {
            $(document).ready(function() {
                $('#conID').val(a);
                $("#delModal").modal('show');
            });
        }
    </script>

    </script>

@stop

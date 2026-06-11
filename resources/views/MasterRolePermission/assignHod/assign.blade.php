@extends('layouts.layout')
@section('pageTitle')
    Assign Head of Department
@endsection


@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"> @yield('pageTitle') </h3>
        </div>

        <div class="panel-body">

            {{-- @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif --}}

            @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span> </button>
                    <strong>Error!</strong>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if (session('message'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span> </button>
                    <strong>Success!</strong> {{ session('message') }}
                </div>
            @endif
            @if (session('error_message'))
                <div class="alert alert-error alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span> </button>
                    <strong>Error!</strong> {{ session('error_message') }}
                </div>
            @endif



            <div class="panel panel-success">
                <div class="panel-heading">
                    <h4 class="panel-title" style="font-weight: bold;">Assign HOD</h4>
                </div>
                <div class="panel-body">

                    <form method="POST" action="{{ url('/assign-hod') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Department</label>
                                    <select name="department_id" id="department" class="form-control" required>
                                        <option value="">-- Select Department --</option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->department }}</option>
                                        @endforeach
                                    </select>
                                </div>


                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Staff to be HOD</label>
                                    <select name="user_id" id="staff" class="form-control" required>
                                        <option value="">-- Select Staff --</option>
                                    </select>
                                </div>

                            </div>
                            <div class="col-md-4">
                                <div class="form-group" style="margin-top: 25px;">
                                    <button type="submit" class="btn btn-success">Assign </button>
                                </div>


                            </div>
                        </div>





                    </form>

                </div>

            </div>


            <div class="panel panel-success">
                <div class="panel-heading">
                    <h4 class="panel-title" style="font-weight: bold;">Current Heads of Department</h4>
                </div>

                <div class="panel-body">
                    <table class="table table-bordered table-striped">
                        <thead style="background: #f5f5f5;">
                            <tr>
                                <th>Department</th>
                                <th>Head of Department</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($hods as $hod)
                                <tr>
                                    <td>{{ $hod->department_name }}</td>
                                    <td>
                                        @if ($hod->surname)
                                            {{ $hod->surname }} {{ $hod->first_name }} {{ $hod->othernames }}
                                        @else
                                            <span class="text-danger">No HOD Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-danger">
                                        No HODs Assigned Yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>



@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
    <script>
        // Load staff based on department selection
        // $('#department').change(function() {
        //     let dept = $(this).val();

        //     $.ajax({
        //         url: "{{ url('/api/staff-by-department') }}/" + dept,
        //         type: "GET",
        //         success: function(data) {
        //             $('#staff').html('<option value="">-- Select Staff --</option>');
        //             data.forEach(function(staff) {
        //                 $('#staff').append(
        //                     `<option value="${staff.ID}">${staff.surname} ${staff.first_name} ${staff.othernames} </option>`
        //                 );
        //             });
        //         }
        //     });
        // });

        $('#department').change(function() {
            let dept = $(this).val();

            $.ajax({
                url: "{{ url('/staff-by-department') }}/" + dept,
                type: "GET",
                success: function(data) {
                    $('#staff').html('<option value="">-- Select Staff --</option>');
                    data.forEach(function(staff) {
                        let staffId = staff.ID || staff.id;
                        let surname = staff.surname || '';
                        let firstName = staff.first_name || '';
                        let othernames = staff.othernames || '';
                        $('#staff').append(
                            `<option value="${staffId}">${surname} ${firstName} ${othernames}</option>`
                        );
                    });
                },
                error: function(xhr) {
                    alert("Error loading staff. Check console.");
                    console.log(xhr.responseText);
                }
            });
        });
    </script>
@endsection

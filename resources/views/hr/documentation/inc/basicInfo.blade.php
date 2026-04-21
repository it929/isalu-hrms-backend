<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


<div class="panel panel-primary">

    <!-- CARD HEADER -->
    <div class="panel-heading">
        <h3 class="panel-title text-center text-success">
            <i class="glyphicon glyphicon-user"></i>
            <b>Basic Information</b>
        </h3>
        {{-- <div class="text-right text-danger" style="margin-top:-25px;">
            Field with <big>*</big> is required
        </div> --}}
    </div>

    <div class="panel-body">

        <form action="{{ url('/documentation-basic-infox') }}" method="POST">
            {{ csrf_field() }}

            <!-- ROW 1 (4 columns) -->
            <div class="row">

                <div class="col-md-4">
                    <label>File Number</label>
                    <input type="text" class="form-control" name="fileNox"
                        value="{{ isset($mainStaffFileNo) ? $mainStaffFileNo : '' }}">
                </div>

                <div class="col-md-4">
                    <label>Surname</label>
                    <input readonly class="form-control" name="surname"
                        value="{{ $staffInfo->surname ?? old('surname') }}">
                </div>

                <div class="col-md-4">
                    <label>First Name</label>
                    <input readonly class="form-control" name="firstName"
                        value="{{ $staffInfo->first_name ?? old('firstName') }}">
                </div>



            </div>

            <br>

            <!-- ROW 2 -->
            <div class="row">


                <div class="col-md-4">
                    <label>Other Names</label>
                    <input readonly class="form-control" name="otherNames"
                        value="{{ $staffInfo->othernames ?? old('otherNames') }}">
                </div>

                <div class="col-md-4">
                    <label>Title *</label>
                    <select name="title" class="form-control" required>
                        <option value="">Select</option>
                        <option>MR.</option>
                        <option>MRS.</option>
                        <option>MISS</option>
                        <option>HON. JUSTICE</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Gender *</label>
                    <select name="gender" class="form-control" required>
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </div>



            </div>

            <br>

            <!-- ROW 3 -->
            <div class="row">

                <div class="col-md-4">
                    <label>Date of Birth</label>
                    <input type="date" class="form-control" name="dateofBirth">
                </div>

                <div class="col-md-4">
                    <label>Place of Birth *</label>
                    <select name="placeofBirth" class="form-control" required>
                        <option value="">Select State</option>
                        @foreach ($StateList as $b)
                            <option value="{{ $b->StateID }}">{{ $b->State }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Employment Type *</label>
                    <select name="hremploymentType" class="form-control" required>
                        <option value="">Select</option>
                        @foreach ($hrEmploymentType as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- <div class="col-md-3">
                    <label>Grade Level *</label>
                    <select name="grade" class="form-control" required>
                        <option value="">Select</option>
                        @for ($i = 1; $i <= 17; $i++)
                            @if ($i != 11)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endif
                        @endfor
                    </select>
                </div> --}}

                {{-- <div class="col-md-3">
                    <label>Step *</label>
                    <select name="step" class="form-control" required>
                        <option value="">Select</option>
                        @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div> --}}



            </div>

            <br>

            <!-- ROW 4 -->
            <div class="row">


                <div class="col-md-4">
                    <label>Department *</label>
                    <select name="department" class="form-control" required>
                        <option value="">Select</option>
                        @foreach ($departments as $b)
                            <option value="{{ $b->id }}">{{ $b->department }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Designation *</label>
                    <select name="designation" class="form-control" required>
                        @foreach ($designation as $list)
                            <option value="{{ $list->id }}">{{ $list->designation }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Date of Appointment *</label>
                    <input type="date" class="form-control" name="presentAppointment2">
                </div>

                {{-- <div class="col-md-3">
                    <label>First Appointment *</label>
                    <input type="date" class="form-control" name="firstAppointment2">
                </div> --}}

                {{-- <div class="col-md-4">
                    <label>Effective Resumption *</label>
                    <input type="date" class="form-control" name="dateofResumption2">
                </div> --}}

            </div>

            <br>

            <!-- SUBMIT -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary">
                    Save and Continue
                </button>
            </div>

        </form>

    </div>
</div>
{{-- <div class="panel panel-default">

    <!-- CARD HEADER -->
    <div class="panel-heading">
        <h3 class="panel-title text-center text-success">
            <i class="glyphicon glyphicon-user"></i>
            <b>Basic Information</b>
        </h3>
        <div class="text-right text-danger" style="margin-top:-25px;">
            Field with <big>*</big> is required
        </div>
    </div>

    <div class="panel-body">

        <form action="{{ url('/documentation-basic-infox') }}" method="POST">
            {{ csrf_field() }}

            <!-- ROW 1 (4 columns) -->
            <div class="row">

                <div class="col-md-3">
                    <label>File Number</label>
                    <input type="text" class="form-control" name="fileNox"
                        value="{{ isset($mainStaffFileNo) ? $mainStaffFileNo : '' }}">
                </div>

                <div class="col-md-3">
                    <label>Surname</label>
                    <input readonly class="form-control" name="surname"
                        value="{{ $staffInfo->surname ?? old('surname') }}">
                </div>

                <div class="col-md-3">
                    <label>First Name</label>
                    <input readonly class="form-control" name="firstName"
                        value="{{ $staffInfo->first_name ?? old('firstName') }}">
                </div>

                <div class="col-md-3">
                    <label>Other Names</label>
                    <input readonly class="form-control" name="otherNames"
                        value="{{ $staffInfo->othernames ?? old('otherNames') }}">
                </div>

            </div>

            <br>

            <!-- ROW 2 -->
            <div class="row">

                <div class="col-md-3">
                    <label>Title *</label>
                    <select name="title" class="form-control" required>
                        <option value="">Select</option>
                        <option>MR.</option>
                        <option>MRS.</option>
                        <option>MISS</option>
                        <option>HON. JUSTICE</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Gender *</label>
                    <select name="gender" class="form-control" required>
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Date of Birth</label>
                    <input type="date" class="form-control" name="dateofBirth">
                </div>

                <div class="col-md-3">
                    <label>Place of Birth *</label>
                    <select name="placeofBirth" class="form-control" required>
                        <option value="">Select State</option>
                        @foreach ($StateList as $b)
                            <option value="{{ $b->StateID }}">{{ $b->State }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <br>

            <!-- ROW 3 -->
            <div class="row">

                <div class="col-md-3">
                    <label>Employment Type *</label>
                    <select name="hremploymentType" class="form-control" required>
                        <option value="">Select</option>
                        @foreach ($hrEmploymentType as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Grade Level *</label>
                    <select name="grade" class="form-control" required>
                        <option value="">Select</option>
                        @for ($i = 1; $i <= 17; $i++)
                            @if ($i != 11)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endif
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Step *</label>
                    <select name="step" class="form-control" required>
                        <option value="">Select</option>
                        @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Department *</label>
                    <select name="department" class="form-control" required>
                        <option value="">Select</option>
                        @foreach ($departments as $b)
                            <option value="{{ $b->id }}">{{ $b->department }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <br>

            <!-- ROW 4 -->
            <div class="row">

                <div class="col-md-3">
                    <label>Designation *</label>
                    <select name="designation" class="form-control" required>
                        @foreach ($designation as $list)
                            <option value="{{ $list->id }}">{{ $list->designation }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Date of Appointment *</label>
                    <input type="date" class="form-control" name="presentAppointment2">
                </div>

                <div class="col-md-3">
                    <label>First Appointment *</label>
                    <input type="date" class="form-control" name="firstAppointment2">
                </div>

                <div class="col-md-3">
                    <label>Effective Resumption *</label>
                    <input type="date" class="form-control" name="dateofResumption2">
                </div>

            </div>

            <br>

            <!-- SUBMIT -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary">
                    Save and Continue
                </button>
            </div>

        </form>

    </div>
</div> --}}
<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/base/jquery-ui.css" rel="stylesheet" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/jquery-ui.min.js"></script>

<script>
    $(document).ready(function() {

        $("#department").change(function(e) {

            //console.log(e);
            var dept_id = e.target.value;
            // var state_id = $(this).val();

            //alert(dept_id);
            //$token = $("input[name='_token']").val();
            //ajax
            $.get('get-designation?dept_id=' + dept_id, function(data) {
                $('#designation').empty();
                //console.log(data);
                //$('#lga').append( '<option value="">Select</option>' );
                $.each(data, function(index, obj) {
                    $('#designation').append('<option value="' + obj.id + '">' + obj
                        .designation + '</option>');
                });


            })
        });


    });
</script>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(
            "input[type='date'], #dob, #dob2, #presentAppointment1, #presentAppointment2, #firstAppointment1, #firstAppointment2, #dateofResumption1, #dateofResumption2", {
                dateFormat: "Y-m-d",
                allowInput: true,
                altInput: true,
                altFormat: "F j, Y",
                maxDate: "today",
                yearSelectorType: "scroll",
            });
    });
</script>

{{-- <script>
    $(document).ready(function() {
        $('input[id$=presentAppointment1]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });


    $(document).ready(function() {
        $('input[id$=presentAppointment2]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=firstAppointment1]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=firstAppointment2]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=dob]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=dob2]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });
</script> --}}

{{-- <style>
    .card-panel {
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        background: #fff;
    }

    .card-header {
        padding: 12px;
        border-bottom: 1px solid #eee;
        background: #f9f9f9;
    }

    .card-title {
        text-align: center;
        font-weight: bold;
        color: #3c763d;
        margin: 0;
    }

    .card-body {
        padding: 20px;
    }
</style> --}}

<style>
    .card-panel {
        border: 1px solid #337ab7;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        background: #fff;
    }

    .card-header {
        padding: 12px;
        border-bottom: 1px solid #337ab7;
        background: #337ab7;
    }

    .card-title {
        text-align: center;
        font-weight: bold;
        color: #fff;
        margin: 0;
    }

    .card-body {
        padding: 15px;
    }

    .table thead tr {
        background: #f5f5f5;
    }

    .table tbody tr:hover {
        background: #f9f9f9;
    }
</style>

<div class="card-panel">

    <!-- HEADER -->
    <div class="card-header">
        <h3 class="card-title">
            <i class="glyphicon glyphicon-education"></i>
            Education Qualification
        </h3>


    </div>

    <div class="card-body">

        <form action="{{ url('save-document') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}

            <input type="hidden" name="fileNo" value="{{ $fileNo }}">

            <!-- ROW 1 (4 COLUMNS) -->
            <div class="row">

                <div class="col-md-3">
                    <label>Education </label>
                    <select name="category" class="form-control input-sm" required>
                        <option value="">Select</option>
                        @foreach ($list as $b)
                            <option value="{{ $b->edu_categoryID }}">
                                {{ $b->category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>School Attended </label>
                    <input type="text" name="school" class="form-control input-sm" required>
                </div>

                <div class="col-md-3">
                    <label>From </label>
                    <input type="date" name="from" class="form-control input-sm" max="{{ date('Y-m-d') }}"  required>
                </div>

                <div class="col-md-3">
                    <label>To </label>
                    <input type="date" name="to" class="form-control input-sm" required>
                </div>

            </div>

            <br>

            <!-- ROW 2 (4 COLUMNS) -->
            <div class="row">

                <div class="col-md-3">
                    <label>Qualification Description </label>
                    <input type="text" name="description" class="form-control input-sm" required>
                </div>

                <div class="col-md-3">
                    <label>Class of Qualification (eg. BSc)</label>
                    <input type="text" name="class_of_qualification" class="form-control input-sm" required>
                </div>

                <div class="col-md-3">
                    <label>Attach Certificate</label>
                    <input type="file" name="certificate" class="form-control input-sm">
                </div>

                <div class="col-md-3" style="padding-top: 25px;">
                    <button type="submit" class="btn  btn-block" style="background-color: #31b0d5; color: #fff">
                        <i class="glyphicon glyphicon-upload"></i> Upload
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>


<br>

<!-- TABLE CARD -->
<div class="card-panel">

    <div class="card-header">
        <h3 class="card-title">
            <i class="glyphicon glyphicon-list"></i>
            Uploaded Education Records
        </h3>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table id="mytable" class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Education</th>
                        <th>School</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Class</th>
                        <th>Description</th>
                        <th>Certificate</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @php $i = 1; @endphp

                    @foreach ($staffDETAILS as $p)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $p->category }}</td>
                            <td>{{ $p->schoolattended }}</td>
                            <td>{{ $p->schoolfrom }}</td>
                            <td>{{ $p->schoolto }}</td>
                            <td>{{ $p->degreequalification }}</td>
                            <td>{{ $p->certificateheld }}</td>

                            <td>
                                <a href="{{ $p->document }}" target="_blank" class="btn btn-info btn-xs">
                                    <i class="fa fa-file"></i> View
                                </a>
                            </td>

                            <td>
                                <a onclick="deleteFunction('{{ $p->id }}')" class="btn btn-danger btn-xs">
                                    <i class="fa fa-trash"></i> Remove
                                </a>
                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>
        </div>

    </div>
</div>

<form action="{{ url('/documentation-education') }}" method="POST">
    {{ csrf_field() }}
    </p>
    <hr />
    <div align="center" style="padding-bottom: 40px;">
        <ul class="list-inline">
            <li><a href="{{ url('/documentation-previous-employment') }}" class="btn btn-default">Previous</a></li>
            <li><button type="submit" class="btn btn-primary">Save and continue</button></li>
        </ul>
    </div>
    </div>

    <input type="hidden" id="appt_count" name="appt_count" value=1>
</form>

<styles>
    <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/base/jquery-ui.css" rel="stylesheet" />

</styles>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/jquery-ui.min.js"></script>

<script>
    function deleteFunction(id) {

        var y = confirm("Do you want to delete this file?");
        if (y == true) {
            document.location = 'delete-document/' + id;
        }
    }

    $(document).ready(function() {
        $('input[id$=fromPrevEmpy]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=fromPrevEmp2y]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=toPrevEmpy]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=toPrevEmp2y]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=fromPrevEmpx]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=fromPrevEmp2x]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=toPrevEmpx]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=toPrevEmp2x]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=fromPrevEmpa]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=fromPrevEmp2a]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=toPrevEmpa]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=toPrevEmp2a]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=fromPrevEmpb]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=fromPrevEmp2b]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=toPrevEmpb]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });

    $(document).ready(function() {
        $('input[id$=toPrevEmp2b]').datepicker({
            dateFormat: 'dd-mm-yy' // Date Format "dd-mm-yy"
        });
    });
</script>

<!-- Flatpickr JS -->
{{-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("input[type='date'], #eduFrom, ", {
            dateFormat: "Y-m-d",
            allowInput: true,
            altInput: true,
            altFormat: "F j, Y",
            maxDate: "today",
            yearSelectorType: "scroll",
        });
    });
</script> --}}

<script>
    function addRows() {
        var count = document.getElementById('appt_count').value;
        var app = [];
        for (i = 0; i < count; i++) {
            try {
                var json_arr = {};
                json_arr["countid"] = i;
                json_arr["name"] = document.getElementById('appname' + i).value;
                json_arr["address"] = document.getElementById('appaddress' + i).value;
                json_arr["phoneno"] = document.getElementById('appphoneno' + i).value;
                json_arr["email"] = document.getElementById('appemail' + i).value;
                var json_string = JSON.stringify(json_arr);
                app.push(json_string);
            } catch (e) {
                // Code to run if an exception occurs
            } finally {
                // Code that is always executed regardless of
                // an exception occurring
            }

        }
        //alert(app);
        document.getElementById('appt_count').value = parseInt(count) + 1;
        var table = document.getElementById('myTable1');
        var row = table.getElementsByTagName('tr');
        var row = row[row.length - 1].outerHTML;
        table.innerHTML = table.innerHTML + row;
        var row = table.getElementsByTagName('tr');
        var row = row[row.length - 1].getElementsByTagName('td');
        row[0].innerHTML = '<input type="text" class="form-control input-lg " placeholder="" name="appname' + count +
            '" id="appname' + count +
            '"    style="width:250px; padding:0; margin:0;font-size:16px;" autocomplete="off" >'
        row[1].innerHTML = '<input type="text" class="form-control input-lg"  placeholder=""  name="appaddress' +
            count + '" id="appaddress' + count +
            '"   style="width:300px; padding:0; margin:0;font-size:16px;" autocomplete="off" >'
        row[2].innerHTML = '<input type="text" class="form-control input-lg" placeholder=""  name="appphoneno' + count +
            '" id="appphoneno' + count +
            '"    style="width:150px; padding:0; margin:0;font-size:16px;" autocomplete="off" >'
        row[3].innerHTML =
            '<div class="input-group mb-3"><input type="text" class="form-control input-lg" name="appemail' + count +
            '" id="appemail' + count +
            '"  placeholder="email" aria-label="Username" aria-describedby="basic-addon1"><div class="input-group-prepend"><button class="btn btn-outline-secondary" type="button" onclick="addRows()">+</button><button class="btn btn-outline-secondary" type="button" id="button' +
            count + '" onclick="RowDel(this)">-</button></div></div>';
        for (i = 0; i < app.length; i++) {
            var obj = JSON.parse(app[i]);
            document.getElementById('appname' + obj.countid).value = obj.name;
            document.getElementById('appaddress' + obj.countid).value = obj.address;
            document.getElementById('appphoneno' + obj.countid).value = obj.phoneno;
            document.getElementById('appemail' + obj.countid).value = obj.email;
        }

    }

    function RowDel(element) {
        var rowJavascript = element.parentNode.parentNode;
        var rowjQuery = $(element).closest("tr");
        var x = rowjQuery[0].rowIndex;
        var table = document.getElementById('myTable1');
        var row = table.getElementsByTagName('tr');
        row[x].outerHTML = '';
    }
</script>

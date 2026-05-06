<!DOCTYPE html>
<html>

    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>HRM System</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <!-- CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
        <link rel="stylesheet" href="{{ asset('assets/css/_all-skins.min.css') }}">

        <style>
            @media print {
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                form,
                .no-print,
                button,
                input {
                    display: none !important;
                }

                table,
                tr,
                td,
                th {
                    border: 1px solid #000 !important;
                }
            }
        </style>
    </head>

    <body class="hold-transition skin-green sidebar-mini">
        <div>
            <header class="main-header">
                <nav class="navbar navbar-static-top" style="background: #0B610B">
                    <div align="center" style="color: #fff; font-size: 26px; padding: 10px;">
                        <b>Human Resource Management System</b>
                        <div style="font-size: 18px;">
                            <h2>HRMS</h2>
                        </div>
                    </div>
                </nav>
            </header>

            <div>
                <section>
                    <div class="box box-default">
                        <div class="container">
                            <h3 class="page-header">Staff Education & Attachments</h3>

                            <!-- SEARCH STAFF -->
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Search Staff</strong></div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>File Number</label>
                                            <div class="input-group">
                                                <input type="text" id="fileNo" class="form-control"
                                                    placeholder="Enter File Number">
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-primary"
                                                        id="searchStaff">Search</button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Display Errors / Success -->
                            @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- FORM -->
                            <form method="POST" action="{{ route('staff.education.store') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <!-- STAFF INFO -->
                                <div class="panel panel-info" id="staffInfo" style="display:none;">
                                    <div class="panel-heading"><strong>Staff Information</strong></div>
                                    <div class="panel-body">
                                        <p><strong>Name:</strong> <span id="staffName"></span></p>
                                        <p><strong>File No:</strong> <span id="staffFile"></span></p>
                                        <input type="hidden" name="staff_id" id="staff_id">
                                        <input type="hidden" name="file_no" id="file_no">
                                    </div>
                                </div>



                                <!-- EDUCATION -->
                                <div class="panel panel-success">
                                    <div class="panel-heading clearfix">
                                        <strong>Education Records</strong>
                                        <button type="button" class="btn btn-xs btn-success pull-right"
                                            id="addEducation">
                                            <i class="glyphicon glyphicon-plus"></i> Add More
                                        </button>
                                    </div>
                                    <div class="panel-body" id="educationWrapper">

                                        <div class="educationRow well well-sm">
                                            <div class="text-right">
                                                <button type="button" class="btn btn-xs btn-danger remove-education">
                                                    <i class="glyphicon glyphicon-trash"></i> Remove
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label>Category</label>
                                                    <select name="educations[0][categoryID]" class="form-control">
                                                        <option value="">-- Select --</option>
                                                        @foreach ($categories ?? [] as $cat)
                                                            <option value="{{ $cat->edu_categoryID }}">
                                                                {{ $cat->category }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Degree / Qualification</label>
                                                    <input type="text" name="educations[0][degreequalification]"
                                                        class="form-control">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>School Attended</label>
                                                    <input type="text" name="educations[0][schoolattended]"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <div class="row" style="margin-top:10px;">
                                                <div class="col-md-3">
                                                    <label>From</label>
                                                    <input type="date" name="educations[0][schoolfrom]"
                                                        class="form-control">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>To</label>
                                                    <input type="date" name="educations[0][schoolto]"
                                                        class="form-control">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Certificate Held</label>
                                                    <input type="text" name="educations[0][certificateheld]"
                                                        class="form-control">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Document</label>
                                                    <input type="file" name="educations[0][document]"
                                                        class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ATTACHMENTS -->
                                <div class="panel panel-warning">
                                    <div class="panel-heading clearfix">
                                        <strong>Other Attachments</strong>
                                        <button type="button" class="btn btn-xs btn-warning pull-right"
                                            id="addAttachment">
                                            <i class="glyphicon glyphicon-plus"></i> Add More
                                        </button>
                                    </div>
                                    <div class="panel-body" id="attachmentWrapper">
                                        <div class="attachmentRow well well-sm">
                                            <div class="text-right">
                                                <button type="button"
                                                    class="btn btn-xs btn-danger remove-attachment">
                                                    <i class="glyphicon glyphicon-trash"></i> Remove
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>File Description</label>
                                                    @php
                                                        $filedescs = [
                                                            'Application Letter',
                                                            'Letter of Appointment',
                                                            'Birth Certificate',
                                                            'Certificate of Indine',
                                                            'GEN 75',
                                                            'NIMC Slip',
                                                        ];
                                                    @endphp
                                                    <select name="attachments[0][filedesc]" class="form-control">
                                                        <option value="">-- Select --</option>
                                                        @foreach ($filedescs ?? [] as $filedesc)
                                                            <option value="{{ $filedesc }}">
                                                                {{ $filedesc }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>File</label>
                                                    <input type="file" name="attachments[0][filepath]"
                                                        class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary btn-lg">Submit Records</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
                <br><br><br>
                <br><br><br>
                <br><br><br>
            </div>
        </div>

        <!-- JS -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
        <script>
            var eduIndex = 1;
            var attIndex = 1;

            // ADD EDUCATION
            $('#addEducation').on('click', function() {
                var row = $('.educationRow:first').clone();
                row.find('input, select').val('');
                row.html(row.html().replace(/\[0\]/g, '[' + eduIndex + ']'));
                $('#educationWrapper').append(row);
                eduIndex++;
            });

            // REMOVE EDUCATION
            $(document).on('click', '.remove-education', function() {
                if ($('.educationRow').length === 1) {
                    alert('At least one education record is required.');
                    return;
                }
                $(this).closest('.educationRow').remove();
            });

            // ADD ATTACHMENT
            $('#addAttachment').on('click', function() {
                var row = $('.attachmentRow:first').clone();
                row.find('input').val('');
                row.html(row.html().replace(/\[0\]/g, '[' + attIndex + ']'));
                $('#attachmentWrapper').append(row);
                attIndex++;
            });

            // REMOVE ATTACHMENT
            $(document).on('click', '.remove-attachment', function() {
                if ($('.attachmentRow').length === 1) {
                    alert('At least one attachment is required.');
                    return;
                }
                $(this).closest('.attachmentRow').remove();
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#searchStaff').on('click', function(e) {
                    e.preventDefault();

                    let fileNo = $('#fileNo').val().trim();
                    if (!fileNo) {
                        alert('Enter file number');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('staff.search.fileNo') }}",
                        type: "POST",
                        dataType: "json",
                        data: {
                            _token: "{{ csrf_token() }}",
                            fileNo: fileNo
                        },
                        success: function(res) {
                            if (res.status) {
                                $('#staffInfo').show();
                                $('#staffName').text(
                                    `${res.data.title} ${res.data.surname} ${res.data.first_name} ${res.data.othernames}`
                                );
                                $('#staffFile').text(res.data.fileNo);
                                $('#staff_id').val(res.data.ID);
                                $('#file_no').val(res.data.fileNo);
                            }
                        },
                        error: function(xhr) {
                            $('#staffInfo').hide();
                            if (xhr.status === 404) {
                                alert('Staff not found');
                            } else {
                                console.error(xhr.responseText);
                                alert('An error occurred, check console for details');
                            }
                        }
                    });
                });
            });
        </script>


        <script>
            document.querySelector("form").addEventListener("submit", async function(e) {
                e.preventDefault();

                const form = e.target;
                const fileInputs = form.querySelectorAll("input[type='file']");
                const files = [];

                fileInputs.forEach(input => {
                    if (input.files[0]) {
                        const fieldType = input.name.includes('educations') ? 'education' : 'attachment';
                        files.push({
                            file: input.files[0],
                            name: input.files[0].name,
                            type: input.files[0].type,
                            fieldType,
                            input
                        });
                    }
                });

                if (!files.length) {
                    form.submit();
                    return;
                }

                // 1️⃣ Request presigned URLs
                const res = await fetch('/s3/presign', {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        files: files.map(f => ({
                            name: f.name,
                            type: f.type,
                            fieldType: f.fieldType
                        }))
                    })
                });

                const urls = await res.json();

                // 2️⃣ Upload to S3
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const urlObj = urls[i];

                    console.log("Uploading", file.name, "to", urlObj.uploadUrl);

                    const uploadRes = await fetch(urlObj.uploadUrl, {
                        method: "PUT",
                        body: file.file // ⚠️ NO headers
                    });

                    if (!uploadRes.ok) {
                        console.error(await uploadRes.text());
                        alert("S3 upload failed for " + file.name);
                        return;
                    }

                    // 3️⃣ Replace file input with hidden input
                    const hidden = document.createElement("input");
                    hidden.type = "hidden";
                    hidden.name = file.input.name;
                    hidden.value = urlObj.fileUrl;

                    file.input.parentNode.appendChild(hidden);
                    file.input.remove();
                }

                // 4️⃣ Submit form with hidden inputs
                form.submit();
            });
        </script>


    </body>

</html>

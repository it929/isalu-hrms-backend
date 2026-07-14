@extends('layouts.layout')
@section('pageTitle')
    NHF Reports
@endsection
@section('content')

    <div class="box-body" style="background:#FFF;">
        <div class="row">
            {{ csrf_field() }}
            
            <!-- Success/Error Messages -->
            <div class="col-md-12" id="messageContainer">
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
                @if (session('message'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                        </button>
                        <strong>Success!</strong>
                        {{ session('message') }}
                    </div>
                @endif
            </div>
            
            <!-- Header Section with Upload Button and File List -->
            <div class="col-md-12">
                <div class="pull-right" style="margin-bottom: 15px;">
                    <!-- Action Buttons -->
                    <button type="button" class="btn btn-success" onclick="exportToExcel()">
                        <i class="fa fa-file-excel-o"></i> Export to Excel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="printReport()">
                        <i class="fa fa-print"></i> Print Report
                    </button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadModal">
                        <i class="fa fa-upload"></i> Attach Remittance Receipt
                    </button>

                    @foreach($attachments as $attachment)
                        <a href="{{ asset($attachment->attachment) }}" class="btn btn-info" target="_blank">
                            <i class="fa fa-eye"></i> View Remittance Receipt
                        </a>
                    @endforeach
                </div>
                
                <!-- Display Uploaded Files -->
                <div class="clearfix"></div>
                
            </div>

            <!-- Main Content for Printing -->
            <div id="mainContent">
                <h2 class="text-center">ISALU HOSPITAL LIMITED</h2>
                <h3 class="text-center">ADDRESS:................................................</h3>
                <h3 class="text-center">NHF PAYMENT SCHEDULE FOR THE MONTH OF
                    {{ $month ? $month : '' }}, {{ $year ? $year : '' }}.
                </h3>
                @if (Auth::user()->is_global == 1)
                    @if ($division != '')
                        <h3 class="text-center">DIVISION: @foreach ($division as $division)
                                {{ $division->division }}</h3>
                    @endforeach
                @endif
                @if ($division == '')
                    <h3 class="text-center">ALL DIVISIONS</h3>
                @endif
            @else
                <h3 class="text-center">DIVISION: @foreach ($division as $division)
                        {{ $division->division }}</h3>
                @endforeach
                @endif
                <div class="col-md-12">
                    <div class="" style="margin-bottom:20px;">

                    </div>

                    <div class="table-responsive">

                        <table class="table table-striped table-condensed table-bordered" id="nhfTable">
                            <thead class="text-gray-b">
                                <tr>
                                    <th colspan="11" align="center">
                                        <p class="text-center" style="padding-top:15px; font-size:20px;">NHF SCHEDULE TEMPLATE
                                        </p>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="text-align: center;">S/N</th>
                                    <th style="text-align: left;">NAME</th>
                                    <th style="text-align: left;">ORGANIZATION</th>
                                    @if ($division == '')
                                        <th style="text-align: left;">Division</th>
                                    @endif
                                    <th style="text-align: center;">EMPLOYER NUMBER</th>
                                    <th style="text-align: center;">NHF NUMBER</th>
                                    <th style="text-align: right;">AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                    $totalNHF = 0;
                                    $count = 0;
                                    $subTotal = 0;
                                    $divID = '';
                                @endphp

                                @foreach ($nhf as $list)
                                    @php
                                        $i = 1;
                                        $count++;
                                        $basicSalary = DB::table('basicsalaryconsolidated')
                                            ->where('employee_type', '=', $list->employee_type)
                                            ->where('grade', '=', $list->grade)
                                            ->where('step', '=', $list->step)
                                            ->value('amount');
                                    @endphp
                                    @if ($divID != $list->divisionID && $divID != '')
                                        <tr>
                                            <td colspan="{{ $division == '' ? '6' : '5' }}" class="tblborder" style="text-align: right;"><strong> Sub Total: </strong> </td>
                                            <td class="tblborder" style="text-align: right;"><strong>{{ number_format($subTotal, 2) }}</strong></td>
                                        </tr>
                                        <?php
                                        $subTotal = 0;
                                        ?>
                                    @endif

                                    @php
                                        $divID = $list->divisionID;
                                        $subTotal += $list->NHF;
                                    @endphp

                                    <tr>
                                        <td style="text-align: center;">{{ $count }}</td>
                                        <td style="text-align: left;" class="text-uppercase">{{ $list->surname }} {{ $list->first_name }} {{ $list->othernames }}</td>
                                        <td style="text-align: left;">Supreme Court Of Nigeria</td>
                                        @if ($division == '')
                                            <td style="text-align: left;" class="text-uppercase">{{ $list->division }}</td>
                                        @endif
                                        <td style="text-align: center;">020001146-6</td>
                                        <td style="text-align: center;" id="{{ $list->ID }}"
                                            onclick="getAccountDetailsFunction('{{ $list->ID }}', '{{ $list->nhfNo }}')"
                                            data-toggle="modal" data-target="#updateAccountModal"> <a> {{ $list->nhfNo }}</a>
                                        </td>
                                        <td style="text-align: right;"><?php $totalNHF += $list->NHF; ?>{{ number_format($list->NHF, 2) }}</td>
                                    </tr>
                                @endforeach
                                @if ($divID != '')
                                    <tr class="tblborder">
                                        <td colspan="{{ $division == '' ? '6' : '5' }}" class="tblborder" style="text-align: left;"><strong> Sub Total:</strong> </td>
                                        <td class="tblborder" style="text-align: right;"><strong>{{ number_format($subTotal, 2) }}</strong></td>
                                    </tr>
                                    <?php $subTotal = 0; ?>
                                @endif

                                <tr>
                                    <td colspan="{{ $division == '' ? '6' : '5' }}" style="text-align: left;"><strong>TOTAL:</strong></td>
                                    <td style="text-align: right;" class=""><strong>{{ number_format($totalNHF, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /.col -->
    </div><!-- /.row -->

    <!-- Update NHF Number Modal -->
    <div id="updateAccountModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update NHF Number</h4>
                </div>
                <div class="modal-body">
                    <br />
                    <div class="row">
                        <form>
                            <div class="col-md-6">
                                <label>NHF number</label>
                                <input type="hidden" name="nhfID" id="nhfID"
                                    class="form-control" />
                                <input type="text" name="nhfnumber" id="nhf"
                                    class="form-control" />
                            </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="close"
                        data-dismiss="modal">Close</button>
                    <button type="button" name="submit" id="submitButton"
                        class="btn btn-success">Update</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Upload Remittance Receipt for {{ $month }}, {{ $year }}</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ url('/nhf/remittance-attachment') }}" enctype="multipart/form-data" id="uploadForm">
                        @csrf
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
                            </div>
                            
                            <!-- Hidden fields for month and year -->
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Upload Remittance Receipt File</label>
                                    <input type="file" name="attachment" class="form-control" required>
                                    <small class="text-muted">Allowed files: jpg, jpeg, png, pdf, doc, docx (Max: 100KB)</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="submitUpload">Upload</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for Excel export -->
    <form id="excelExportForm" method="post" action="{{ url('/nhf-report/export-excel') }}" style="display: none;">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
    </form>

@endsection

@section('styles')
    <style>
        .init {
            line-height: 30px;
        }
        
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        
        .btn-info:hover {
            background-color: #138496;
            border-color: #138496;
        }
        
        .pull-right {
            float: right;
        }
        
        .modal-lg {
            max-width: 800px;
        }
        
        .uploaded-files-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .uploaded-files-section h4 {
            margin-bottom: 15px;
            color: #495057;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
        }
        
        .table-responsive {
            margin-top: 10px;
        }
        
        /* Print Styles */
        @media print {
            .no-print, .pull-right, .modal, .btn {
                display: none !important;
            }
            
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #000;
                background: #fff;
            }
            
            .text-center {
                text-align: center;
            }
            
            h2 {
                font-size: 20px;
                margin: 10px 0 5px 0;
            }
            
            h3 {
                font-size: 16px;
                margin: 5px 0 15px 0;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10px;
                margin-top: 10px;
            }
            
            th, td {
                border: 1px solid #000;
                padding: 5px;
                text-align: left;
            }
            
            th {
                background-color: #f0f0f0;
                font-weight: bold;
            }
            
            .text-right {
                text-align: right;
            }
            
            @page {
                size: landscape;
                margin: 0.5cm;
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/table2excel.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("#court").on('change', function(e) {
                e.preventDefault();
                var id = $(this).val();
                $token = $("input[name='_token']").val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $token
                    },
                    url: murl + '/session/court',
                    type: "post",
                    data: {
                        'courtID': id
                    },
                    success: function(data) {
                        location.reload(true);
                    }
                });
            });

            // Handle upload form submission
            $('#submitUpload').click(function() {
                var form = $('#uploadForm');
                var formData = new FormData(form[0]);
                
                // Show loading state
                $(this).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
                $(this).prop('disabled', true);
                
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ url("/nhf/remittance-attachment") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Reset button state
                        $('#submitUpload').html('Upload');
                        $('#submitUpload').prop('disabled', false);
                        
                        // Close modal and reset form
                        $('#uploadModal').modal('hide');
                        $('#uploadForm')[0].reset();
                        
                        // Show success message in Bootstrap alert
                        showMessage(response.success, 'success');
                        
                        // Reload page to show updated attachments
                        setTimeout(function() {
                            location.reload(true);
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        // Reset button state
                        $('#submitUpload').html('Upload');
                        $('#submitUpload').prop('disabled', false);
                        
                        // Show error message in Bootstrap alert
                        var errorMessage = 'Error uploading file. ';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage += Object.values(xhr.responseJSON.errors).join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else {
                            errorMessage += error;
                        }
                        showMessage(errorMessage, 'error');
                    }
                });
            });

            // Clear form when modal is closed
            $('#uploadModal').on('hidden.bs.modal', function () {
                $('#uploadForm')[0].reset();
            });

            // Handle form submission on enter key
            $('#uploadForm').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#submitUpload').click();
                    return false;
                }
            });

            
           
        });

        // Function to show messages in Bootstrap alert
        function showMessage(message, type) {
            var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            var strongText = type === 'success' ? 'Success!' : 'Error!';
            
            var alertHtml = `
                <div class="alert ${alertClass} alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>${strongText}</strong> ${message}
                </div>
            `;
            
            $('#messageContainer').html(alertHtml);
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(function() {
                    $('.alert-success').alert('close');
                }, 5000);
            }
        }

        function getAccountDetailsFunction(a, b) {
            $('#nhfID').val(a);
            $('#nhf').val(b);
        };

        $(document).ready(function() {
            $("#submitButton").click(function() {
                var ID = $("#nhfID").val();
                var nhf = $("#nhf").val();
                $('#submitButton').html('Please wait...')
                $('#submitButton').prop('disable', true)

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '/update-nhf/' + ID + '/' + nhf,
                    method: "GET",
                    success: function(data) {
                        location.reload(true);
                        $('#submitButton').html('Update')
                        $('#submitButton').prop('disable', false)
                        $('#close').click()
                        showMessage("Record Updated successfully.", 'success');
                    },
                    error: function(xhr, status, error) {
                        showMessage("Error sending record: " + error, 'error');
                        $('#submitButton').html('Update')
                        $('#submitButton').prop('disable', false)
                    }
                });
            });
        });

        // Print Report Function
        function printReport() {
            // Store original content and styles
            const originalContent = document.body.innerHTML;
            const originalTitle = document.title;
            
            // Get the main content to print
            const printContent = document.getElementById('mainContent').innerHTML;
            
            // Create print-friendly HTML
            const printDocument = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>NHF Report - {{$month}}/{{$year}}</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 20px;
                            color: #000;
                            background: #fff;
                        }
                        .text-center {
                            text-align: center;
                        }
                        h2 {
                            font-size: 20px;
                            margin: 10px 0 5px 0;
                        }
                        h3 {
                            font-size: 16px;
                            margin: 5px 0 15px 0;
                        }
                        .table-responsive {
                            width: 100%;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            font-size: 10px;
                            margin-top: 10px;
                        }
                        th, td {
                            border: 1px solid #000;
                            padding: 5px;
                            text-align: left;
                        }
                        th {
                            background-color: #f0f0f0;
                            font-weight: bold;
                        }
                        .text-right {
                            text-align: right;
                        }
                        @media print {
                            body {
                                margin: 0.5cm;
                            }
                            @page {
                                size: landscape;
                                margin: 0.5cm;
                            }
                            table {
                                font-size: 9px;
                            }
                            th, td {
                                padding: 3px;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
                </html>
            `;
            
            // Replace current page content with print version
            document.body.innerHTML = printDocument;
            
            // Change page title for printing
            document.title = 'NHF Report - {{$month}}/{{$year}}';
            
            // Print the document
            window.print();
            
            // Restore original content after printing
            setTimeout(function() {
                document.body.innerHTML = originalContent;
                document.title = originalTitle;
                
                // Re-attach any event listeners if needed
                if (typeof $ !== 'undefined') {
                    $(document).ready(function(){
                        // Re-initialize any jQuery functions if needed
                    });
                }
            }, 500);
        }

        // Export to Excel Function
        function exportToExcel() {
            try {
                const table = document.getElementById('nhfTable');
                const workbook = XLSX.utils.table_to_book(table, {sheet: "NHF Report"});
                XLSX.writeFile(workbook, `NHF_Report_{{ $month }}_{{ $year }}.xlsx`);
            } catch (error) {
                console.error('Client-side export failed:', error);
                // Fallback to server-side export
                document.getElementById('excelExportForm').submit();
            }
        }

        // Original Export function (keeping for backward compatibility)
        function Export() {
            exportToExcel();
        }
    </script>
@endsection
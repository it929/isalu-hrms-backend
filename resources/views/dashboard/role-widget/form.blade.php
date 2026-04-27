 @extends('layouts.layout')
 @section('pageTitle')
     <strong>Role Widget Assignment</strong>
 @endsection

 @section('content')
     <style>
         .table>thead>tr>th {
             vertical-align: middle;
             text-align: center;
             font-weight: 600;
             background-color: #f0f0f0;
             border-bottom: 2px solid #ddd;
         }

         .table>tbody>tr:hover {
             background-color: #f9f9f9;
         }

         .btn-xs {
             padding: 3px 8px;
             font-size: 12px;
         }

         .text-right {
             margin-top: 5px;
         }

         .panel {
             border-radius: 6px;
             box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
         }

         .panel-heading {
             background-color: #2c3e50;
             color: #fff;
         }

         .form-group label {
             font-weight: 600;
         }

         .swal2-title-custom {
             font-size: 18px !important;
             font-weight: 600;
         }

         .widget-checkbox {
             margin-right: 10px;
         }

         .widget-label {
             margin-left: 5px;
         }

         .checkbox-container {
             max-height: 400px;
             overflow-y: auto;
             border: 1px solid #ddd;
             padding: 15px;
             border-radius: 5px;
             margin-top: 10px;
             background-color: #f9f9f9;
         }

         .checkbox-item {
             padding: 8px 0;
             border-bottom: 1px solid #f0f0f0;
         }

         .checkbox-item:last-child {
             border-bottom: none;
         }

         .opacity-50 {
             opacity: 0.5;
         }

         .delete-btn {
             padding: 3px 8px;
             font-size: 12px;
         }
     </style>

     <div class="box box-default">
         <div class="box-header with-border hidden-print">
             <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
         </div>

         {{-- Success Messages --}}
         @if (session('success'))
             <div class="alert alert-success alert-dismissible">
                 <button type="button" class="close" data-dismiss="alert">&times;</button>
                 <strong>{{ session('success') }}</strong>
             </div>
         @endif

         {{-- Error Messages --}}
         @if (session('error'))
             <div class="alert alert-danger alert-dismissible">
                 <button type="button" class="close" data-dismiss="alert">&times;</button>
                 <strong>{{ session('error') }}</strong>
             </div>
         @endif

         {{-- Validation Errors --}}
         @if ($errors->any())
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

         {{-- MAIN FORM FOR SAVING ASSIGNMENTS --}}
         <form method="post" id="roleWidgetForm" action="{{ route('role-widget.store') }}">
             @csrf

             <div class="box-body">
                 <div class="panel panel-default">
                     <div class="panel-heading">
                         <h3 class="panel-title"><strong>Assign Widgets to Role</strong></h3>
                     </div>

                     <div class="panel-body">
                         <div class="row">
                             <div class="col-md-6">
                                 <div class="form-group">
                                     <label class="control-label">Select Role</label>
                                     <select required class="form-control" id="role_id" name="role_id">
                                         <option value="">- Select Role -</option>
                                         @foreach ($roles as $role)
                                             <option value="{{ $role->roleID }}"
                                                 {{ old('role_id') == $role->roleID ? 'selected' : '' }}>
                                                 {{ $role->rolename }}
                                             </option>
                                         @endforeach
                                     </select>
                                     @error('role_id')
                                         <span class="text-danger">{{ $message }}</span>
                                     @enderror
                                 </div>
                             </div>
                         </div>

                         <div class="row">
                             <div class="col-md-12">
                                 <div class="form-group">
                                     <label class="control-label">Available Widgets</label>

                                     @if ($widgets->isEmpty())
                                         <div class="alert alert-warning">
                                             No widgets found in the database.
                                         </div>
                                     @else
                                         <div id="widgets-section" class="checkbox-container">
                                             <div class="row">
                                                 @foreach ($widgets as $widget)
                                                     <div class="col-md-4">
                                                         <div class="checkbox-item">
                                                             <input type="checkbox" name="widgets[]"
                                                                 value="{{ $widget->id }}" id="widget_{{ $widget->id }}"
                                                                 class="widget-checkbox"
                                                                 {{ is_array(old('widgets')) && in_array($widget->id, old('widgets')) ? 'checked' : '' }}>
                                                             <label for="widget_{{ $widget->id }}" class="widget-label">
                                                                 {{ $widget->name ?? 'Widget ' . $widget->id }}
                                                             </label>
                                                         </div>
                                                     </div>
                                                 @endforeach
                                             </div>
                                         </div>
                                         @error('widgets')
                                             <span class="text-danger">{{ $message }}</span>
                                         @enderror
                                     @endif
                                 </div>
                             </div>
                         </div>

                         <div class="row">
                             <div class="col-md-12">
                                 <div class="form-group" style="margin-top: 20px;">
                                     <button type="submit" class="btn btn-success" name="save">
                                         <i class="fa fa-floppy-o"></i> Save Assignments
                                     </button>
                                     <button type="button" class="btn btn-default" onclick="resetForm()">
                                         <i class="fa fa-refresh"></i> Reset
                                     </button>
                                     {{-- <button type="button" class="btn btn-primary" style="float: right">
                                        <i class="fa fa-plus"></i> Add new widget
                                    </button> --}}

                                     <button type="button" class="btn btn-primary" style="float: right" data-toggle="modal"
                                         data-target="#addWidgetModal">
                                         <i class="fa fa-plus"></i> Add new widget
                                     </button>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>

                 @if (isset($assignments) && count($assignments) > 0)
                     <div class="panel panel-default">
                         <div class="panel-heading">
                             <h3 class="panel-title"><strong>Current Assignments</strong></h3>
                         </div>

                         <div class="panel-body">
                             <div class="table-responsive" style="font-size:13px;">
                                 <table class="table table-bordered table-striped table-hover">
                                     <thead>
                                         <tr style="background-color:#f5f5f5; color:#333;">
                                             <th width="5%">S/N</th>
                                             <th>Role</th>
                                             <th>Widget</th>
                                             <th>Assigned Date</th>
                                             <th width="15%" class="text-center">Action</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @php
                                             $serialNum = 1;
                                         @endphp
                                         @foreach ($assignments as $assignment)
                                             <tr>
                                                 <td>{{ $serialNum++ }}</td>
                                                 <td>{{ $assignment->rolename }}</td>
                                                 <td>{{ $assignment->widget_name ?? 'Widget ' . $assignment->widget_id }}
                                                 </td>
                                                 <td>{{ $assignment->created_at }}</td>
                                                 <td class="text-center">
                                                     {{-- JavaScript approach for delete --}}
                                                     <button type="button" class="btn btn-xs btn-danger delete-assignment"
                                                         data-id="{{ $assignment->id }}">
                                                         <i class="fa fa-trash"></i> Remove
                                                     </button>
                                                 </td>
                                             </tr>
                                         @endforeach
                                     </tbody>
                                 </table>
                             </div>
                         </div>
                     </div>
                 @else
                     <div class="alert alert-info">
                         <i class="fa fa-info-circle"></i> No widget assignments found. Select a role and assign widgets
                         above.
                     </div>
                 @endif
             </div>
         </form>
     </div>

     <!-- ADD NEW WIDGET MODAL -->
     <div class="modal fade" id="addWidgetModal">
         <div class="modal-dialog modal-sm">
             <div class="modal-content">

                 <form id="addWidgetForm">
                     @csrf

                     <div class="modal-header bg-primary" style="color:#fff;">
                         <button type="button" class="close" data-dismiss="modal">&times;</button>
                         <h4 class="modal-title">
                             <i class="fa fa-plus"></i> Add New Widget
                         </h4>
                     </div>

                     <div class="modal-body">
                         <div class="form-group">
                             <label>Widget Name</label>
                             <input type="text" name="name" class="form-control" required
                                 placeholder="Enter widget name">
                         </div>
                     </div>

                     <div class="modal-footer">
                         <button type="submit" class="btn btn-success">
                             <i class="fa fa-save"></i> Save Widget
                         </button>
                         <button type="button" class="btn btn-default" data-dismiss="modal">
                             Close
                         </button>
                     </div>

                 </form>

             </div>
         </div>
     </div>
 @endsection

 @section('styles')
     <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
 @endsection

 @section('scripts')
     <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

     <script type="text/javascript">
         $(document).ready(function() {
             // Load widgets when role is selected
             $('#role_id').change(function() {
                 var roleId = $(this).val();

                 if (roleId) {
                     // Show loading state
                     $('#widgets-section').addClass('opacity-50');

                     $.ajax({
                         url: '{{ url('/get-widgets') }}/' + roleId,
                         type: 'GET',
                         success: function(data) {
                             // Uncheck all checkboxes
                             $('.widget-checkbox').prop('checked', false);

                             // Check boxes based on assigned widgets
                             $.each(data, function(index, widgetId) {
                                 $('#widget_' + widgetId).prop('checked', true);
                             });
                         },
                         error: function() {
                             Swal.fire({
                                 icon: 'error',
                                 title: 'Error',
                                 text: 'Error loading widgets for this role.',
                                 toast: true,
                                 position: 'top-end',
                                 showConfirmButton: false,
                                 timer: 3000
                             });
                         },
                         complete: function() {
                             $('#widgets-section').removeClass('opacity-50');
                         }
                     });
                 } else {
                     // If no role selected, uncheck all
                     $('.widget-checkbox').prop('checked', false);
                 }
             });

             // Handle delete assignment with JavaScript
             $(document).on('click', '.delete-assignment', function(e) {
                 e.preventDefault();
                 var assignmentId = $(this).data('id');
                 var button = $(this);
                 var row = $(this).closest('tr');

                 Swal.fire({
                     title: 'Are you sure?',
                     text: "This will remove the widget assignment!",
                     icon: 'warning',
                     showCancelButton: true,
                     confirmButtonColor: '#d33',
                     cancelButtonColor: '#3085d6',
                     confirmButtonText: 'Yes, remove it!',
                     cancelButtonText: 'Cancel'
                 }).then((result) => {
                     if (result.isConfirmed) {
                         // Show loading on button
                         button.html('<i class="fa fa-spinner fa-spin"></i> Removing...');
                         button.prop('disabled', true);

                         // Send DELETE request via AJAX
                         $.ajax({
                             url: '{{ url('/role-widgets') }}/' + assignmentId,
                             type: 'DELETE',
                             headers: {
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json'
                             },
                             success: function(response) {
                                 if (response.success) {
                                     // Remove the row from table
                                     row.fadeOut(300, function() {
                                         $(this).remove();
                                         // Update serial numbers
                                         updateSerialNumbers();

                                         // Show success message
                                         Swal.fire({
                                             icon: 'success',
                                             title: 'Success',
                                             text: response.message,
                                             toast: true,
                                             position: 'top-end',
                                             showConfirmButton: false,
                                             timer: 2000
                                         });
                                     });
                                 } else {
                                     // Reset button
                                     button.html('<i class="fa fa-trash"></i> Remove');
                                     button.prop('disabled', false);

                                     Swal.fire({
                                         icon: 'error',
                                         title: 'Error',
                                         text: response.message ||
                                             'Failed to remove assignment.',
                                         toast: true,
                                         position: 'top-end',
                                         showConfirmButton: false,
                                         timer: 3000
                                     });
                                 }
                             },
                             error: function(xhr) {
                                 // Reset button
                                 button.html('<i class="fa fa-trash"></i> Remove');
                                 button.prop('disabled', false);

                                 var errorMessage = 'Failed to remove assignment.';
                                 if (xhr.responseJSON && xhr.responseJSON.message) {
                                     errorMessage = xhr.responseJSON.message;
                                 } else if (xhr.status === 404) {
                                     errorMessage =
                                         'Assignment not found. It may have already been deleted.';
                                 } else if (xhr.status === 405) {
                                     errorMessage =
                                         'Method not allowed. Please check your routes.';
                                 } else if (xhr.status === 419) {
                                     errorMessage =
                                         'Session expired. Please refresh the page.';
                                 }

                                 Swal.fire({
                                     icon: 'error',
                                     title: 'Error',
                                     text: errorMessage,
                                     toast: true,
                                     position: 'top-end',
                                     showConfirmButton: false,
                                     timer: 3000
                                 });

                                 // If it's a 419 error (CSRF token mismatch), reload the page
                                 if (xhr.status === 419) {
                                     setTimeout(function() {
                                         location.reload();
                                     }, 3000);
                                 }
                             }
                         });
                     }
                 });
             });

             // Handle main form submission
             $('#roleWidgetForm').submit(function(e) {
                 var roleId = $('#role_id').val();

                 if (!roleId) {
                     e.preventDefault();
                     Swal.fire({
                         icon: 'warning',
                         title: 'Role Required',
                         text: 'Please select a role first.',
                         toast: true,
                         position: 'top-end',
                         showConfirmButton: false,
                         timer: 3000
                     });
                     $('#role_id').focus();
                     return false;
                 }

                 // Optional: Show confirmation for empty selection
                 var checkedWidgets = $('.widget-checkbox:checked').length;
                 if (checkedWidgets === 0) {
                     e.preventDefault();
                     Swal.fire({
                         title: 'No Widgets Selected',
                         text: "You haven't selected any widgets. This will remove all widgets from this role. Continue?",
                         icon: 'question',
                         showCancelButton: true,
                         confirmButtonColor: '#3085d6',
                         cancelButtonColor: '#d33',
                         confirmButtonText: 'Yes, continue',
                         cancelButtonText: 'Cancel'
                     }).then((result) => {
                         if (result.isConfirmed) {
                             // Remove the submit handler and submit
                             $('#roleWidgetForm').off('submit').submit();
                         }
                     });
                     return false;
                 }
             });
         });

         function resetForm() {
             $('#role_id').val('');
             $('.widget-checkbox').prop('checked', false);
         }

         function updateSerialNumbers() {
             $('tbody tr').each(function(index) {
                 $(this).find('td:first').text(index + 1);
             });
         }

         // SweetAlert Toast for success messages (when page loads)
         @if (session('success'))
             $(document).ready(function() {
                 Swal.fire({
                     toast: true,
                     position: 'top-end',
                     icon: 'success',
                     title: '{{ session('success') }}',
                     showConfirmButton: false,
                     timer: 2500,
                     timerProgressBar: true
                 });
             });
         @endif

         @if (session('error'))
             $(document).ready(function() {
                 Swal.fire({
                     toast: true,
                     position: 'top-end',
                     icon: 'error',
                     title: '{{ session('error') }}',
                     showConfirmButton: false,
                     timer: 3000,
                     timerProgressBar: true
                 });
             });
         @endif
     </script>
     <script>
         $(document).on('submit', '#addWidgetForm', function(e) {
             e.preventDefault();

             let form = $(this);

             $.ajax({
                 url: "{{ route('widget.add') }}",
                 type: "POST",
                 data: form.serialize(),

                 success: function(response) {

                     // ✅ Success alert
                     Swal.fire({
                         icon: 'success',
                         title: 'Success!',
                         text: 'Widget added successfully',
                         timer: 1500,
                         showConfirmButton: false
                     });

                     // ✅ Close modal
                     $('#addWidgetModal').modal('hide');

                     // ✅ Reset form
                     form[0].reset();

                     // ✅ Append new widget to checkbox list
                     let widget = response.widget;

                     let html = `
                <div class="col-md-4">
                    <div class="checkbox-item">
                        <input type="checkbox"
                               name="widgets[]"
                               value="${widget.id}"
                               id="widget_${widget.id}"
                               class="widget-checkbox"
                               checked>

                        <label for="widget_${widget.id}" class="widget-label">
                            ${widget.name}
                        </label>
                    </div>
                </div>
            `;

                     $('#widgets-section .row').append(html);
                 },

                 error: function(xhr) {

                     let msg = 'Something went wrong';

                     if (xhr.responseJSON && xhr.responseJSON.errors) {
                         msg = Object.values(xhr.responseJSON.errors)
                             .map(e => e[0])
                             .join('\n');
                     }

                     // ❌ Error alert
                     Swal.fire({
                         icon: 'error',
                         title: 'Error',
                         text: msg
                     });
                 }
             });
         });
     </script>
 @endsection

@extends('layouts_procurement.app')
@section('pageTitle', 'Job Order Details')
@section('pageMenu', 'active')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <div class="pull-left">
                    <h3 class="panel-title"><b>Job Order: {{ $jobOrder->job_order_number }}</b></h3>
                </div>
                <div class="pull-right">
                    <a href="{{ route('job-order.index') }}" class="btn btn-sm btn-default">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                    <a href="{{ route('job-order.print', $jobOrder->job_order_id) }}" target="_blank" class="btn btn-sm btn-info">
                        <i class="fa fa-print"></i> Print
                    </a>
                    @if($jobOrder->status == 'draft')
                        <a href="{{ route('job-order.edit', $jobOrder->job_order_id) }}" class="btn btn-sm btn-warning">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                    @endif
                </div>
            </div>

            <div class="panel-body">
                @include('ShareView.operationCallBackAlert')

                <!-- Job Order Header -->
                <div class="well well-sm text-center" style="background-color: #00a65a; color: white;">
                    <h2>ISALU HOSPITAL LIMITED</h2>
                    <h4>ABUJA</h4>
                    <h3>JOB ORDER - {{ $jobOrder->job_order_no ?? '______' }}</h3>
                    <h4>No: {{ $jobOrder->job_order_no ?? '______' }} Original</h4>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th width="20%">Department</th>
                                <td width="30%">{{ $jobOrder->department ?? '________________' }}</td>
                                <th width="20%">Station</th>
                                <td width="30%">{{ $jobOrder->station ?? '________________' }}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td colspan="3">{{ date('d M, Y', strtotime($jobOrder->order_date)) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Item Description -->
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Item(s) to Repair/Supply/Implement</h3>
                    </div>
                    <div class="panel-body">
                        <p style="font-size: 14px; line-height: 1.8;">{{ nl2br($jobOrder->item_description) }}</p>
                    </div>
                </div>

                <!-- Cost Estimates -->
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <h3 class="panel-title">Cost Estimates</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Estimated Cost</th>
                                <td width="70%">₦{{ number_format($jobOrder->estimated_cost, 2) }}</td>
                            </tr>
                            @if($jobOrder->amount_in_words)
                            <tr>
                                <th>Amount in Words</th>
                                <td>{{ $jobOrder->amount_in_words }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Certification Section (if completed) -->
                @if($jobOrder->status == 'completed')
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <h3 class="panel-title">Certification</h3>
                    </div>
                    <div class="panel-body">
                        <p><strong>I certify that above item(s) has/have been satisfactorily repaired/supplied/implemented and...</strong></p>
                        
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Certified Amount</th>
                                <td width="70%">₦{{ number_format($jobOrder->certified_amount, 2) }}</td>
                            </tr>
                            @if($jobOrder->certified_amount_words)
                            <tr>
                                <th>Amount in Words</th>
                                <td>{{ $jobOrder->certified_amount_words }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Payment Head</th>
                                <td>{{ $jobOrder->payment_head ?? '________________' }}</td>
                            </tr>
                            <tr>
                                <th>Subhead</th>
                                <td>{{ $jobOrder->payment_subhead ?? '________________' }}</td>
                            </tr>
                        </table>

                        <div class="row" style="margin-top: 20px;">
                            <div class="col-md-6">
                                <p><strong>Certifying Officer:</strong> {{ $jobOrder->certifying_officer }}</p>
                                <p><strong>Rank:</strong> {{ $jobOrder->officer_rank }}</p>
                                <p><strong>Date:</strong> {{ date('d M, Y', strtotime($jobOrder->certifying_date)) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons for Issuing/Completing -->
                @if($jobOrder->status == 'draft')
                    <div class="text-center" style="margin-top: 20px;">
                        <form action="{{ route('job-order.issue', $jobOrder->job_order_id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Are you sure you want to issue this Job Order?')">
                                <i class="fa fa-check"></i> Issue Job Order
                            </button>
                        </form>
                    </div>
                @elseif($jobOrder->status == 'issued')
                    <div class="text-center" style="margin-top: 20px;">
                        <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#completeModal">
                            <i class="fa fa-check-circle"></i> Complete & Certify
                        </button>
                    </div>
                @endif

                <!-- Status Badge -->
                <div class="text-center" style="margin-top: 20px;">
                    <span class="label label-{{ 
                        $jobOrder->status == 'draft' ? 'warning' : 
                        ($jobOrder->status == 'issued' ? 'info' : 
                        ($jobOrder->status == 'completed' ? 'success' : 'danger')) 
                    }} label-lg" style="font-size: 16px; padding: 10px;">
                        Status: {{ strtoupper($jobOrder->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('job-order.complete', $jobOrder->job_order_id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Complete & Certify Job Order</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p><strong>Certification Statement:</strong> I certify that above item(s) has/have been satisfactorily repaired/supplied/implemented and...</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="certified_amount">Certified Amount (₦) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="certified_amount" 
                                       name="certified_amount" value="{{ $jobOrder->estimated_cost }}" 
                                       min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="certified_amount_words">Amount in Words</label>
                                <input type="text" class="form-control" id="certified_amount_words" 
                                       name="certified_amount_words" placeholder="e.g., One Million Naira Only">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_head">Payment Head</label>
                                <input type="text" class="form-control" id="payment_head" 
                                       name="payment_head">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_subhead">Subhead</label>
                                <input type="text" class="form-control" id="payment_subhead" 
                                       name="payment_subhead">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="certifying_officer">Certifying Officer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="certifying_officer" 
                                       name="certifying_officer" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="officer_rank">Rank <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="officer_rank" 
                                       name="officer_rank" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="certifying_date">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="certifying_date" 
                                       name="certifying_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="certification_text">Additional Certification Text (Optional)</label>
                        <textarea class="form-control" id="certification_text" 
                                  name="certification_text" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete & Certify</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
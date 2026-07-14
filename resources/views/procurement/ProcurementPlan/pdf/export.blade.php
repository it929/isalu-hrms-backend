<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exported Procurement Plan Data</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />

    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            font-size: 14px;
            font-family: Helvetica;
        }
    </style>
</head>

<body>

    <div class="row">
        <div class="col-md-8">
            <div style="text-align: center;">
                <h3><strong>ISALU HOSPITAL LIMITED</strong></h3>
                <h4><strong>ISALU HOSPITAL LIMITED</strong></h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">

                <h4 class="text-center"><strong>PROCUREMENT PLAN SHEET - {{ $report->project_title }}</strong></h4>

                <table class="table table-bordered">

                    <tr>
                        <th colspan="3" style="text-align: center"><strong>BASIC DATA</strong></th>
                    </tr>

                    <tr>
                        <th>Budget Year</th>
                        <td colspan="2">{{ $report->budget_year }}</td>
                    </tr>

                    <tr>
                        <th>Budget Code</th>
                        <td colspan="2">{{ $report->budget_code }}</td>
                    </tr>

                    <tr>
                        <th>Package Number</th>
                        <td colspan="2">{{ $report->package_number }}</td>
                    </tr>

                    <tr>
                        <th>Lot Number</th>
                        <td colspan="2">{{ $report->lot_number }}</td>
                    </tr>

                    <tr>
                        <th>Procurement Plan Date</th>
                        <td colspan="2">{{ date('jS M, Y', strtotime($report->plan_date)) }}</td>
                    </tr>

                    <tr>
                        <th>Project Title</th>
                        <td colspan="2">{{ $report->project_title }}</td>
                    </tr>

                    <tr>
                        <th>Procurement Category</th>
                        <td colspan="2">{{ $category }}</td>
                    </tr>

                    <tr>
                        <th>Contract Type</th>
                        <td colspan="2">{{ $contractType }}</td>
                    </tr>

                    <tr>
                        <th>Budget Amount</th>
                        <td colspan="2">{{ number_format($report->budget_amount, 2) }}</td>
                    </tr>

                    <tr>
                        <th>Project Estimate</th>
                        <td colspan="2">{{ number_format($report->estimate, 2) }}</td>
                    </tr>

                    <tr>
                        <th>Procurement Method</th>
                        <td colspan="2">{{ $report->procurement_method }}</td>
                    </tr>

                    <tr>
                        <th>Qualification</th>
                        <td colspan="2">{{ $report->qualification }}</td>
                    </tr>

                    <tr>
                        <th>Review</th>
                        <td colspan="2">{{ $report->review }}</td>
                    </tr>

                    <tr>
                        <th colspan="3" style="text-align: center"><strong>PLANNED ACTIVITY TIMELINE</strong></th>
                    </tr>

                    <tr>
                        <th>ACTIVITY</th>
                        <td><strong>FROM</strong></td>
                        <td><strong>TO</strong></td>
                    </tr>

                    {{-- Helper for date formatting --}}
                    @php
                        function showDate($d)
                        {
                            return empty($d) ? '' : date('jS M, Y', strtotime($d));
                        }
                    @endphp

                    <tr>
                        <td><strong>Preparation of Bidding Document & Advert</strong></td>
                        <td>{{ showDate($report->bidDocFrom) }}</td>
                        <td>{{ showDate($report->bidDocTo) }}</td>
                    </tr>

                    <tr>
                        <th>Approval for Bidding Document & Advert</th>
                        <td>{{ showDate($report->mdaApproveFrom) }}</td>
                        <td>{{ showDate($report->mdaApproveTo) }}</td>
                    </tr>

                    <tr>
                        <th>Advertisement for Prequalification/EOI</th>
                        <td>{{ showDate($report->preQualiAdvertFrom) }}</td>
                        <td>{{ showDate($report->preQualiAdvertTo) }}</td>
                    </tr>

                    <tr>
                        <th>Pre-qualification/EOI Closing/Opening</th>
                        <td>{{ showDate($report->preQualiClosingFrom) }}</td>
                        <td>{{ showDate($report->preQualiClosingTo) }}</td>
                    </tr>

                    <tr>
                        <th>Pre-qualification/EOI Evaluation</th>
                        <td>{{ showDate($report->preQualiEvaluationFrom) }}</td>
                        <td>{{ showDate($report->preQualiEvaluationTo) }}</td>
                    </tr>

                    <tr>
                        <th>Submission of Evaluation Report</th>
                        <td>{{ showDate($report->preQualiEvaluateReportFrom) }}</td>
                        <td>{{ showDate($report->preQualiEvaluateReportTo) }}</td>
                    </tr>

                    <tr>
                        <th>Approval for Pre-qualification/EOI</th>
                        <td>{{ showDate($report->mdaApprovalPreQualiFrom) }}</td>
                        <td>{{ showDate($report->mdaApprovalPreQualiTo) }}</td>
                    </tr>

                    <tr>
                        <th>Invitation to Tender/RFP</th>
                        <td>{{ showDate($report->invitationTenderFrom) }}</td>
                        <td>{{ showDate($report->invitationTenderTo) }}</td>
                    </tr>

                    <tr>
                        <th>Technical Bid Opening</th>
                        <td>{{ showDate($report->technicalBidOpeningFrom) }}</td>
                        <td>{{ showDate($report->technicalBidOpeningTo) }}</td>
                    </tr>

                    <tr>
                        <th>Technical Bid Evaluation</th>
                        <td>{{ showDate($report->technicalBidEvaluationFrom) }}</td>
                        <td>{{ showDate($report->technicalBidEvaluationTo) }}</td>
                    </tr>

                    <tr>
                        <th>Financial Bid Opening</th>
                        <td>{{ showDate($report->financialBidOpeningFrom) }}</td>
                        <td>{{ showDate($report->financialBidOpeningTo) }}</td>
                    </tr>

                    <tr>
                        <th>Financial Evaluation</th>
                        <td>{{ showDate($report->financialEvaluationFrom) }}</td>
                        <td>{{ showDate($report->financialEvaluationTo) }}</td>
                    </tr>

                    <tr>
                        <th>Submission of Evaluation Report</th>
                        <td>{{ showDate($report->submissionEvaluationFrom) }}</td>
                        <td>{{ showDate($report->submissionEvaluationTo) }}</td>
                    </tr>

                    <tr>
                        <th>Approval of No Objection</th>
                        <td>{{ showDate($report->mdaObjectionFrom) }}</td>
                        <td>{{ showDate($report->mdaObjectionTo) }}</td>
                    </tr>

                    <tr>
                        <th>Certifiable Amount</th>
                        <td>{{ showDate($report->certifiableAmountFrom) }}</td>
                        <td>{{ showDate($report->certifiableAmountTo) }}</td>
                    </tr>

                    <tr>
                        <th>FEC Approval</th>
                        <td>{{ showDate($report->fecApprovalFrom) }}</td>
                        <td>{{ showDate($report->fecApprovalTo) }}</td>
                    </tr>

                    <tr>
                        <th>Contract Offer</th>
                        <td>{{ showDate($report->dateContractOfferFrom) }}</td>
                        <td>{{ showDate($report->dateContractOfferTo) }}</td>
                    </tr>

                    <tr>
                        <th>Contract Signature</th>
                        <td>{{ showDate($report->contractSignatureDateFrom) }}</td>
                        <td>{{ showDate($report->contractSignatureDateTo) }}</td>
                    </tr>

                    <tr>
                        <th>Advance Payment</th>
                        <td>{{ showDate($report->advancePaymentFrom) }}</td>
                        <td>{{ showDate($report->advancePaymentTo) }}</td>
                    </tr>

                    <tr>
                        <th>Draft Final Report</th>
                        <td>{{ showDate($report->draftFinalReportFrom) }}</td>
                        <td>{{ showDate($report->draftFinalReportTo) }}</td>
                    </tr>

                    <tr>
                        <th>Final Acceptance</th>
                        <td>{{ showDate($report->finalAcceptanceFrom) }}</td>
                        <td>{{ showDate($report->finalAcceptanceTo) }}</td>
                    </tr>

                    <tr>
                        <th colspan="3" style="text-align: center"><strong>ACTION PARTIES</strong></th>
                    </tr>

                    <tr>
                        <th>Action Party Name</th>
                        <td colspan="2">Designation</td>
                    </tr>

                    <tr>
                        <th>Champion Name</th>
                        <td colspan="2">Designation</td>
                    </tr>

                </table>

            </div>
        </div>
    </div>

</body>

</html>

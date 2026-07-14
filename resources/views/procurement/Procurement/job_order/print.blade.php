<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Order #{{ $jobOrder->job_order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            margin: 30px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 20px;
        }
        .header h3 {
            margin: 10px 0 5px;
            font-size: 18px;
        }
        .job-order-no {
            font-size: 16px;
            margin: 10px 0;
        }
        .section {
            margin: 25px 0;
            border: 1px solid #000;
            padding: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        td, th {
            padding: 8px;
            border: 1px solid #000;
        }
        .label {
            font-weight: bold;
            width: 30%;
        }
        .value {
            width: 70%;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            min-width: 200px;
            display: inline-block;
            margin: 0 10px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 30%;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        @media print {
            body {
                margin: 0.5in;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ISALU HOSPITAL LIMITED</h1>
        <h2>ABUJA</h2>
        <h3>JOB ORDER - {{ $jobOrder->job_order_no ?? '______' }}</h3>
        <div class="job-order-no">No: {{ $jobOrder->job_order_no ?? '______' }} Original</div>
    </div>

    <table>
        <tr>
            <td class="label">Department</td>
            <td class="value">{{ $jobOrder->department ?? '________________' }}</td>
            <td class="label">Station</td>
            <td class="value">{{ $jobOrder->station ?? '________________' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="value" colspan="3">{{ date('d M, Y', strtotime($jobOrder->order_date)) }}</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Please undertake to repair/supply/implement the under mentioned item/items:</div>
        <div style="white-space: pre-line; min-height: 100px; border: 1px solid #000; padding: 15px;">
            {{ $jobOrder->item_description }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Estimated Cost</div>
        <table>
            <tr>
                <td class="label">Estimated Cost N</td>
                <td class="value">₦{{ number_format($jobOrder->estimated_cost, 2) }}</td>
            </tr>
            @if($jobOrder->amount_in_words)
            <tr>
                <td class="label">Amount in Words</td>
                <td class="value">{{ $jobOrder->amount_in_words }} (......Naira ......kobo)</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section">
        <div class="section-title">Certification</div>
        <p><strong>I certify that above item(s) has/have been satisfactorily repaired/supplied/implemented and...</strong></p>
        
        <table>
            @if($jobOrder->certified_amount)
            <tr>
                <td class="label">Certified Amount</td>
                <td class="value">₦{{ number_format($jobOrder->certified_amount, 2) }} (......Naira ......Kobo)</td>
            </tr>
            @else
            <tr>
                <td class="label">Certified Amount</td>
                <td class="value">________________ Naira ________________ Kobo</td>
            </tr>
            @endif
            
            <tr>
                <td class="label">Payment Head</td>
                <td class="value">{{ $jobOrder->payment_head ?? '________________' }}</td>
            </tr>
            <tr>
                <td class="label">Subhead</td>
                <td class="value">{{ $jobOrder->payment_subhead ?? '________________' }}</td>
            </tr>
        </table>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Certifying Officer/In-Charge of Works</strong></p>
            <p>Name: <span class="signature-line">{{ $jobOrder->certifying_officer ?? '________________' }}</span></p>
            <p>Rank: <span class="signature-line">{{ $jobOrder->officer_rank ?? '________________' }}</span></p>
            <p>Date: <span class="signature-line">{{ $jobOrder->certifying_date ? date('d M, Y', strtotime($jobOrder->certifying_date)) : '________________' }}</span></p>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer generated document.</p>
        <p>Generated on: {{ date('d M, Y h:i A') }}</p>
    </div>

    <!-- Print Button -->
    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 30px; background: #00a65a; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            <i class="fa fa-print"></i> Print / Save as PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-left: 10px;">
            Close
        </button>
    </div>
</body>
</html>
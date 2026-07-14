@extends('layouts.layout')

@section('pageTitle')
    Salary Mandate Letter
@endsection

@section('content')
    @php
        // Reusable function for number to words
        function numberToWords($num)
        {
            $ones = [
                0 => '',
                1 => 'one',
                2 => 'two',
                3 => 'three',
                4 => 'four',
                5 => 'five',
                6 => 'six',
                7 => 'seven',
                8 => 'eight',
                9 => 'nine',
                10 => 'ten',
                11 => 'eleven',
                12 => 'twelve',
                13 => 'thirteen',
                14 => 'fourteen',
                15 => 'fifteen',
                16 => 'sixteen',
                17 => 'seventeen',
                18 => 'eighteen',
                19 => 'nineteen',
            ];

            $tens = [
                2 => 'twenty',
                3 => 'thirty',
                4 => 'forty',
                5 => 'fifty',
                6 => 'sixty',
                7 => 'seventy',
                8 => 'eighty',
                9 => 'ninety',
            ];

            $units = ['', 'thousand', 'million', 'billion', 'trillion'];

            $num = number_format($num, 2, '.', ',');
            $parts = explode('.', $num);
            $whole = $parts[0];
            $decimal = (int) ($parts[1] ?? 0);
            $groups = array_reverse(explode(',', $whole));
            krsort($groups);

            $text = '';
            foreach ($groups as $key => $value) {
                $value = (int) $value;
                if ($value == 0) {
                    continue;
                }

                if ($value < 20) {
                    $text .= $ones[$value];
                } elseif ($value < 100) {
                    $text .= $tens[floor($value / 10)];
                    if ($value % 10) {
                        $text .= ' ' . $ones[$value % 10];
                    }
                } else {
                    $text .= $ones[floor($value / 100)] . ' hundred';
                    $rem = $value % 100;
                    if ($rem) {
                        $text .= ' and ';
                        if ($rem < 20) {
                            $text .= $ones[$rem];
                        } else {
                            $text .= $tens[floor($rem / 10)];
                            if ($rem % 10) {
                                $text .= ' ' . $ones[$rem % 10];
                            }
                        }
                    }
                }

                if ($key > 0) {
                    $text .= ' ' . $units[$key] . ' ';
                }
            }

            if ($text == '') {
                $text = 'zero';
            }
            $text .= ' naira';

            if ($decimal > 0) {
                $text .= ' , ';
                if ($decimal < 20) {
                    $text .= $ones[$decimal];
                } else {
                    $text .= $tens[floor($decimal / 10)];
                    if ($decimal % 10) {
                        $text .= ' ' . $ones[$decimal % 10];
                    }
                }
                $text .= ' kobo';
            }

            return ucfirst(trim($text)) . ' ';
        }
    @endphp

    {{-- ================= PAGE 1 ================= --}}
    <div class="page page-one">

          @if ($epayment_detail[0]->vstage >= 5)
                 <div style="text-align: right; margin-bottom: 10px;">
                    <button onclick="window.print()" class="print-btn"
                        style="padding: 6px 12px; font-size: 14px; background-color:#008000; color:#fff; border:none; border-radius:4px; cursor:pointer;">
                        🖨️ Print
                    </button>
                </div>
          @endif


        <div class="fold-line"></div>
        <div class="fold-line second"></div>

        <div class="letterhead">
            <!-- Letterhead content -->
        </div>

        <div class="top-row">
            <div class="to">
                The Manager,<br>
                UBA Plc<br>
                Maitama<br>
                Abuja.
            </div>
            <strong>{{ now()->format('jS F, Y') }}</strong>
        </div>

        <div class="subject">
            <h4>
                SALARY MANDATE FOR THE MONTH OF {{ strtoupper($epayment_detail->first()->month ?? '') }},
                {{ $epayment_detail->first()->year ?? '' }}
            </h4>
        </div>

        {{-- <p>
            Please credit the account(s) of the under-listed beneficiaries and debit our Account Number above with the sum
            of <strong>₦ {{ number_format($totalAmount, 2) }}</strong> (Amount in Words:
            <strong>{{ numberToWords($totalAmount) }}</strong>)
        </p> --}}
        <p class="p"> Please find attached the salary mandate for the Honourable Justices and Staff of the Supreme Court
            of Nigeria for the month of {{ $epayment_detail->first()->month ?? '' }},
            {{ $epayment_detail->first()->year ?? '' }}. </p>

        <p class="p">
            You are by this letter directed to debit our account
            <b>1002402636</b> with the Sum of

            <b id="amountInWords" class="amountInWords"></b>
            (<b><strong>₦{{ number_format($totalAmount, 2) }}</strong></b>) <b class="amountInWords">Only</b>
        </p>

        <p>Below is the analysis of payments to various banks:</p>
        <table class="analysis">
            <tbody>
                <tr>
                    <td>Commercial Banks:</td>
                    <td class="amount">{{ number_format($commercialBanks, 2) }}</td>
                </tr>
                <tr>
                    <td>Micro Finance/Other Banks:</td>
                    <td class="amount">{{ number_format($microBanks, 2) }}</td>
                </tr>
                <tr>
                    <td>CBN:</td>
                    <td class="amount">{{ number_format($cbnDeductions->grand_total ?? 0, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total">
                    <td class="total-label">TOTAL</td>
                    <td class="total-amount">{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <br><br>

        @php
            $sig1 = DB::table('tblmandatesignatory')
                ->join(
                    'tblmandatesignatoryprofiles',
                    'tblmandatesignatoryprofiles.id',
                    '=',
                    'tblmandatesignatory.signatoryID',
                )
                ->where('tblmandatesignatory.id', 1)
                ->first();

            $sig2 = DB::table('tblmandatesignatory')
                ->join(
                    'tblmandatesignatoryprofiles',
                    'tblmandatesignatoryprofiles.id',
                    '=',
                    'tblmandatesignatory.signatoryID',
                )
                ->where('tblmandatesignatory.id', 2)
                ->first();
        @endphp

        <div class="authorizerSign">
            <table width="100%">
                <tr>
                    <td width="40%">
                        <strong>{{ $sig1->Name }}</strong><br><br>
                        <strong>AUTHORIZED SIGNATORY:</strong><br><br>
                        Signature: ................................<br><br>
                    </td>

                    <td width="20%"></td>

                    <td width="40%">
                        <strong>{{ $sig2->Name }}</strong><br><br>
                        <strong>AUTHORIZED SIGNATORY:</strong><br><br>
                        Signature: ................................<br><br>
                    </td>
                </tr>
            </table>
        </div>

    </div>



    {{-- ================= PAGE 2 ================= --}}
    <div class="page">

        <div class="top-area">

            <div class="memo-title">MEMORANDUM</div>
        </div>

        <div class="line-row">
            {{-- <div class="label">TO</div> --}}
            <label>TO:</label>
            <div class="value-line">
                THE CHIEF REGISTRAR, THROUGH DIRECTOR (F&A), THROUGH DD (F&A)
            </div>

        </div>

        <div class="line-row">
            {{-- <div class="label">FROM</div> --}}
            <label>FROM:</label>
            <div class="value-line">CHIEF ACCOUNTANT</div>
        </div>

        <div class="line-row subject-row">
            {{-- <div class="label">SUBJECT</div> --}}
            <label>SUBJECT:</label>
            <div class="value-line">SALARY FOR THE MONTH OF {{ strtoupper($epayment_detail->first()->month ?? '') }},
                {{ $epayment_detail->first()->year ?? '' }}</div>
        </div>

        <div class="line-row date-row">
            {{-- <div class="label">DATE</div> --}}
            <label>DATE</label>
            <div class="value-line">{{ now()->format('jS F, Y') }}</div>
        </div>

        <div class="content">
            <p>Above subject refers:</p>
            <p>
                Kindly give approval for the payment of salary of the Honourable Justices and
                Staff of the ISALU HOSPITAL LIMITED for the month of
                {{ strtoupper($epayment_detail->first()->month ?? '') }}, {{ $epayment_detail->first()->year ?? '' }}
            </p>

            <strong>
                <p>
                    The Sum of
                    <span class="bold-inline">{{ numberToWords($totalAmount) }}
                        (N{{ number_format($totalAmount, 2) }})</span>
                    Only is hereby submitted for your kind consideration.
                </p>
            </strong>

            <p>Below is the analysis of payments to various banks.</p>
            <table class="bank-table">
                <tr>
                    <td>Commercial Banks:</td>
                    <td><span class="naira"></span>{{ number_format($commercialBanks, 2) }}</td>
                </tr>
                <tr>
                    <td>Micro Finance/Other Banks:</td>
                    <td>{{ number_format($microBanks, 2) }}</td>
                </tr>
                <tr>
                    <td>CBN:</td>
                    <td class="strike">{{ number_format($cbnDeductions->grand_total ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td><span class="circled-total">{{ number_format($totalAmount, 2) }}</span></td>
                </tr>
            </table>

            <div class="footer-text">
                Submitted for your kind consideration and approval.
            </div>

            <div class="signature">
                <div class="signature-line"></div>
                <div class="name">Sunday Kehinde Taiwo</div>
                <div class="designation">Chief Accountant</div>
            </div>
        </div>

    </div>
@endsection

@section('style')
    <style>
        /* GENERAL STYLES */
        body {
            margin: 0;
            font-family: "Times New Roman", serif;
            color: #444;
            background: #f5f5f5;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            background: #fff;
            padding: 22mm 18mm 18mm;
            box-sizing: border-box;
            position: relative;
        }

        .letterhead {
            text-align: center;
            margin-bottom: 10mm;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 6mm;
        }

        .to {
            font-size: 14px;
            color: #555;
            white-space: pre-line;
        }

        .subject h4 {
            text-align: center;
            text-decoration: underline;
            font-weight: 700;
        }

        .analysis,
        .bank-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6mm;
        }

        .analysis td,
        .bank-table td {
            padding: 6px 0;
        }

        .amount {
            text-align: right;
            font-weight: 700;
        }

        .total-amount {
            text-align: right;
            font-weight: 700;
            border-top: 2px solid #000;
            /* line above total */
            border-bottom: 2px double #000;
            /* double underline */
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .circled-total {
            /* border: 2px solid #b3b3b3; */
            border-top: 2px solid #000;
            /* line above total */
            border-bottom: 2px double #000;
            padding: 4px 18px;
            margin-top: 3px;
        }

        .print-btn {
            padding: 6px 12px;
            font-size: 14px;
            background-color: #008000;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* PAGE 2 SPECIFIC */
        .top-area {
            position: relative;
            height: 90px;
        }

        .logo-left,
        .logo-right {
            position: absolute;
            top: 0;
            width: 80px;
            height: 60px;
            border-radius: 10px;
            background: rgba(110, 168, 123, 0.25);
        }

        .logo-right {
            right: 120px;
        }

        .circled-number {
            position: absolute;
            top: 0;
            left: 260px;
            width: 42px;
            height: 42px;
            border: 2px solid #999;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transform: rotate(-5deg);
        }

        .memo-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            margin-top: 35px;
        }

        .line-row {
            display: flex;
            align-items: center;
            margin-top: 10px;
            min-height: 38px;
            position: relative;
        }

        .label {
            width: 62px;
            font-size: 14px;
            color: #c6c6c6;
            text-transform: uppercase;
        }

        .value-line {
            flex: 1;
            border-bottom: 1px solid #d9d9d9;
            padding: 0 8px 4px;
            font-size: 17px;
            font-weight: 700;
            color: #666;
            display: flex;
            align-items: end;
        }

        .handwritten {
            position: absolute;
            left: 72px;
            top: 19px;
            font-size: 15px;
            font-style: italic;
            color: #666;
            transform: rotate(2deg);
        }

        .content {
            margin-top: 25px;
            padding: 0 18px;
            font-size: 16px;
            line-height: 1.4;
        }

        .signature {
            margin-top: 45px;
            width: 290px;
        }

        .signature-line {
            border-top: 1px solid #555;
            width: 240px;
            margin-top: 32px;
        }

        .analysis td {
            padding: 3mm 0;
            vertical-align: bottom;
        }

        .analysis td:first-child {
            width: 70%;
            padding-right: 10mm;
        }

        .analysis td:last-child {
            width: 30%;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }


        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 18mm;
            color: var(--muted);
        }

        .name,
        .designation {
            font-weight: 700;
            font-size: 18px;
            color: #555;
            margin-top: 6px;
        }

        /* PRINT STYLES */
        @page {
            size: A4;
            margin: 0;
        }

        @media print {
            body {
                background: #fff;
                margin: 0;
            }

            .print-btn {
                display: none;
            }

            .page {
                width: 210mm;
                /* remove height */
                min-height: auto;
                padding: 15mm;
                box-sizing: border-box;
                /* avoid forcing break after each page */
                page-break-after: auto;
            }

            /* Only break before next page if next page exists */
            .page+.page {
                page-break-before: always;
            }

            /* Prevent blank page after last page */
            .page:last-child {
                page-break-after: avoid;
            }

            /* Keep letterhead spacing for first page */
            .page-one {
                padding-top: 60mm;
                /* adjust based on letterhead size */
            }
        }
    </style>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}"></script>

<script>



    let totalAmount = {{ $totalAmount }};

    let words = toWords(totalAmount);

    words = words.replace(/\b\w/g, function(l){ return l.toUpperCase() });

    document.getElementById("amountInWords").innerHTML =
        words + " Naira";

</script>

@if ($epayment_detail[0]->vstage < 5)
<script>
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        alert('Printing is disabled for this page.');
    }
});
</script>
@endif

@endsection

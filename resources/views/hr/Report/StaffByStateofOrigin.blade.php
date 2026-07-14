@extends('layouts.layout')
@section('pageTitle')
    Staff Distribution
@endsection

@section('content')
    <style>
        .table-responsive {
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .table thead th {
            vertical-align: middle !important;
            font-size: 1rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .table tbody td {
            font-size: 0.95rem;
        }

        .table-hover tbody tr:hover {
            background-color: #f9fafb !important;
        }

        .table th,
        .table td {
            white-space: nowrap;
            padding: 0.5rem 0.75rem;
        }

        .table-dark th {
            /* background-color: #343a40 !important; */
            background: linear-gradient(90deg, #449d44, #337a33) !important;
            color: #fff !important;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }

        .table td:first-child {
            font-weight: 500;
        }

        .table td:last-child {
            background-color: #eef4ff;
            font-weight: 600;
        }

        /* Optional Card Styling for Better Look */
        .card {
            border: none;
            border-radius: 0.85rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 10px;
        }

        .card-header {
            /* background: linear-gradient(90deg, #343a40, #495057); */
            color: #2f353a;
            font-weight: 800;
            /* letter-spacing: 0.4px; */
            font-size: 1.3rem;
            padding: 1rem 1.25rem;
            border-bottom: none;
        }

        .card-header.d-flex {
            gap: 1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .sub-header {
            margin-bottom: 17px;
            font-weight: 680;
        }

        .print-container {
            display: flex;
            justify-content: flex-end;
            /* Move to right */
            margin-bottom: 1rem;
            /* Add spacing below */
        }

        /* hide controls when printing */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
            }
        }
    </style>





    <div class="card" id="printList">
        <!-- header now contains title on left and Print button on right -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title m-0 no-print">STAFF LIST BY STATE OF ORIGIN</div>

            <!-- print button right-aligned; has .no-print so it's hidden in print -->
            <div class="no-print print-container">
                <button type="button" class="btn btn-primary btn-sm" onclick="printList()" title="Print staff list">
                    <i class="fa fa-print me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="text-center fw-bold mb-4 sub-header">
                ISALU HOSPITAL LIMITED STAFF LIST BY STATES OF ORIGIN AS AT
                {{ date('d-M-Y') }}
            </div>

            <div class="table-responsive shadow-sm rounded">
                <!-- ... your table (leave unchanged) ... -->
                <table class="table table-striped table-hover align-middle text-center">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th rowspan="2" class="align-middle">S/N</th>
                            <th rowspan="2" class="align-middle">STATE OF ORIGIN</th>
                            <th colspan="18" class="text-center">SALARY GRADE LEVEL</th>
                            <th rowspan="2" class="align-middle">TOTAL NO. OF STAFF</th>
                        </tr>
                        <tr>
                            @for ($i = 1; $i <= 17; $i++)
                                <th>{{ $i }}</th>
                            @endfor
                            <th>CONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($states as $key => $state)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td class="text-start fw-semibold">{{ $state->State }}</td>

                                <td>{{ $state->grade1 }}</td>
                                <td>{{ $state->grade2 }}</td>
                                <td>{{ $state->grade3 }}</td>
                                <td>{{ $state->grade4 }}</td>
                                <td>{{ $state->grade5 }}</td>
                                <td>{{ $state->grade6 }}</td>
                                <td>{{ $state->grade7 }}</td>
                                <td>{{ $state->grade8 }}</td>
                                <td>{{ $state->grade9 }}</td>
                                <td>{{ $state->grade10 }}</td>
                                <td>{{ $state->grade11 }}</td>
                                <td>{{ $state->grade12 }}</td>
                                <td>{{ $state->grade13 }}</td>
                                <td>{{ $state->grade14 }}</td>
                                <td>{{ $state->grade15 }}</td>
                                <td>{{ $state->grade16 }}</td>
                                <td>{{ $state->grade17 }}</td>
                                <td>{{ $state->consolidated }}</td>
                                <td class="fw-bold text-primary">{{ $state->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Optional: fallback button below card (also right aligned) -->
    <div class="d-flex justify-content-end mt-2 no-print print-container">
        {{-- <button class="btn btn-outline-secondary btn-sm" onclick="printList()">
            Quick Print
        </button> --}}
        <button type="button" class="btn btn-primary btn-sm " onclick="printList()" title="Print staff list">
            <i class="fa fa-print me-1"></i> Print
        </button>
    </div>
@endsection

{{-- <script>
    function printList() {

        var divToPrint = document.querySelector('#printList');
        var htmlToPrint = '' +
            '<style type="text/css">' +
            'table th, table td {' +
            'border:1px solid #000;' +
            'padding:0.5em;' +
            '}' +
            '</style>';
        htmlToPrint += divToPrint.outerHTML;
        newWin = window.open("");
        newWin.document.write(htmlToPrint);
        newWin.print();
        newWin.close();
    }
</script> --}}

<!-- Print script -->
{{-- <script>
    function printList() {
        // Grab the card element
        const card = document.getElementById('printList');
        if (!card) {
            window.print(); // fallback
            return;
        }

        // Create a new window for printing to preserve styles
        const printWindow = window.open('', '_blank', 'height=800,width=1000,scrollbars=yes');
        if (!printWindow) {
            // popup blocked, fallback to printing current page
            window.print();
            return;
        }

        // Collect page-level stylesheets so table/card styles are preserved
        let styles = '';
        for (const sheet of document.styleSheets) {
            try {
                if (sheet.href) {
                    styles += `<link rel="stylesheet" href="${sheet.href}">`;
                } else if (sheet.ownerNode && sheet.ownerNode.tagName === 'STYLE') {
                    styles += `<style>${sheet.ownerNode.innerHTML}</style>`;
                }
            } catch (e) {
                // ignore CORS-protected sheets
            }
        }

        // Build printable HTML
        const html = `
      <html>
        <head>
          <meta charset="utf-8"/>
          <title>Print - Staff List</title>
          ${styles}
          <style>
            /* hide interactive elements in print window just in case */
            .no-print { display: none !important; }
            /* make sure the table prints nicely */
            table { border-collapse: collapse; width: 100%; }
            th, td { page-break-inside: avoid; }
          </style>
        </head>
        <body>
          ${card.outerHTML}
        </body>
      </html>
    `;

        // Write, focus and print
        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.focus();

        // let the new window load resources and then print
        printWindow.onload = function() {
            printWindow.print();
            // optionally keep the window open or close it:
            // printWindow.close();
        };
    }
</script> --}}

<script>
    function printList() {
        const card = document.getElementById('printList');
        if (!card) {
            window.print();
            return;
        }

        // Collect styles from current page
        let styles = '';
        for (const sheet of document.styleSheets) {
            try {
                if (sheet.href) {
                    styles += `<link rel="stylesheet" href="${sheet.href}">`;
                } else if (sheet.ownerNode && sheet.ownerNode.tagName === 'STYLE') {
                    styles += `<style>${sheet.ownerNode.innerHTML}</style>`;
                }
            } catch (e) {
                // Ignore any CORS-protected stylesheets
            }
        }

        // Create or reuse hidden iframe
        let printFrame = document.getElementById('printFrame');
        if (!printFrame) {
            printFrame = document.createElement('iframe');
            printFrame.id = 'printFrame';
            printFrame.style.position = 'absolute';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = 'none';
            document.body.appendChild(printFrame);
        }

        const frameDoc = printFrame.contentWindow || printFrame.contentDocument;
        const html = `
        <html>
            <head>
                <meta charset="utf-8"/>
                <title>Print - Staff List</title>
                ${styles}
                <style>
                    .no-print { display: none !important; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { page-break-inside: avoid; }
                </style>
            </head>
            <body>
                ${card.outerHTML}
            </body>
        </html>
    `;

        frameDoc.document.open();
        frameDoc.document.write(html);
        frameDoc.document.close();

        frameDoc.focus();
        frameDoc.onload = function() {
            frameDoc.print();
        };
    }
</script>

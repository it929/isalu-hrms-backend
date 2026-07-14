<!DOCTYPE html>
<html>
<head>
    <title>ISALU HOSPITAL LIMITED</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://funds.njc.gov.ng/assets/css/datepicker.min.css">
    <style type="text/css">
        @if ($checkApproval == 0)
            @media print {
                .no-print, .hidden-print, .no-print * {
                    display: none !important;
                }
            }
        @endif
        
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            padding: 25px;
            background-color: #fff;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Watermark Styles - HORIZONTAL - 20px SIZE, NO ANIMATION */
        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            opacity: 0.15;
        }
        
        .watermark-text {
            position: absolute;
            font-size: 28px;
            font-weight: 800;
            color: #008000;
            white-space: nowrap;
            opacity: 0.25;
            text-align: center;
            width: 100%;
        }
        
        /* Create a grid of horizontal watermarks covering the entire page */
        .watermark-text:nth-child(1) {
            top: 8%;
            left: -15%;
            font-size: 30px;
            opacity: 0.22;
        }
        
        .watermark-text:nth-child(2) {
            top: 20%;
            left: -5%;
            font-size: 28px;
            opacity: 0.28;
        }
        
        .watermark-text:nth-child(3) {
            top: 32%;
            left: -20%;
            font-size: 32px;
            opacity: 0.20;
        }
        
        .watermark-text:nth-child(4) {
            top: 44%;
            left: -8%;
            font-size: 26px;
            opacity: 0.30;
        }
        
        .watermark-text:nth-child(5) {
            top: 56%;
            left: -25%;
            font-size: 34px;
            opacity: 0.18;
        }
        
        .watermark-text:nth-child(6) {
            top: 68%;
            left: 0;
            font-size: 30px;
            opacity: 0.26;
        }
        
        .watermark-text:nth-child(7) {
            top: 80%;
            left: -15%;
            font-size: 28px;
            opacity: 0.32;
        }
        
        .watermark-text:nth-child(8) {
            top: 12%;
            left: -30%;
            font-size: 36px;
            opacity: 0.15;
        }
        
        .watermark-text:nth-child(9) {
            top: 24%;
            left: -35%;
            font-size: 38px;
            opacity: 0.12;
        }
        
        .watermark-text:nth-child(10) {
            top: 36%;
            left: -40%;
            font-size: 40px;
            opacity: 0.10;
        }
        
        .watermark-text:nth-child(11) {
            top: 48%;
            left: -45%;
            font-size: 42px;
            opacity: 0.08;
        }
        
        .watermark-text:nth-child(12) {
            top: 60%;
            left: -50%;
            font-size: 44px;
            opacity: 0.06;
        }
        
        .watermark-text:nth-child(13) {
            top: 72%;
            left: -55%;
            font-size: 46px;
            opacity: 0.05;
        }
        
        .watermark-text:nth-child(14) {
            top: 84%;
            left: -60%;
            font-size: 48px;
            opacity: 0.04;
        }
        
        .watermark-text:nth-child(15) {
            top: 4%;
            left: -10%;
            font-size: 26px;
            opacity: 0.35;
        }
        
        /* Additional horizontal lines for even more coverage - NO ANIMATION */
        .watermark-row {
            position: absolute;
            width: 300%;
            left: -100%;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .watermark-row:nth-child(16) {
            top: 16%;
        }
        
        .watermark-row:nth-child(17) {
            top: 38%;
            opacity: 0.20;
        }
        
        .watermark-row:nth-child(18) {
            top: 60%;
            opacity: 0.25;
        }
        
        .watermark-row:nth-child(19) {
            top: 82%;
            opacity: 0.18;
        }
        
        .watermark-row:nth-child(20) {
            top: 92%;
            opacity: 0.22;
        }
        
        .scrolling-text {
            display: inline-block;
            font-size: 30px;
            font-weight: 800;
            color: #008000;
            padding-right: 80px;
            opacity: 0.25;
        }
        
        /* Print mode - OPTIMIZED FOR LARGE AMOUNTS */
        @media print {
            /* INCREASED BASE FONT SIZE FOR BETTER READABILITY */
            body {
                font-size: 12px !important;
                padding: 5px !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
                background: white !important;
                line-height: 1.3 !important;
            }
            
            /* FIX FOR HEADER IMAGES NOT SHOWING */
            img {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                max-height: 60px !important;
                width: auto !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            /* Specifically target the logo images */
            .header-logo img {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                max-height: 60px !important;
                width: auto !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Ensure the entire header logo container is visible */
            .header-logo {
                display: inline-block !important;
                visibility: visible !important;
                background-image: none !important;
                width: 60px !important;
                margin: 0 10px !important;
            }
            
            /* COMPACT HEADER FOR PRINT */
            .compact-header {
                display: block !important;
                visibility: visible !important;
                page-break-inside: avoid !important;
                text-align: center !important;
                width: 100% !important;
                margin-bottom: 10px !important;
                padding-bottom: 8px !important;
            }
            
            .header-center {
                display: inline-block !important;
                vertical-align: middle !important;
                text-align: center !important;
                margin: 0 10px !important;
                padding: 0 !important;
                max-width: 50% !important;
                font-size: 12px !important;
            }
            
            /* INCREASED HEADER TITLE SIZES */
            .court-title {
                font-size: 15px !important;
                margin: 0 0 4px 0 !important;
                line-height: 1.3 !important;
            }
            
            .zone-title {
                font-size: 13px !important;
                margin: 0 0 4px 0 !important;
                line-height: 1.3 !important;
            }
            
            .account-number {
                font-size: 13px !important;
                margin: 4px 0 !important;
                line-height: 1.3 !important;
            }
            
            .document-title {
                font-size: 14px !important;
                margin: 4px 0 !important;
                text-decoration: underline;
                line-height: 1.3 !important;
            }
            
            /* Ensure all content is visible */
            .container-fluid {
                display: block !important;
                visibility: visible !important;
            }
            
            /* Force background images and colors to print */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            .watermark-text {
                opacity: 0.10 !important;
                font-size: 24px !important;
            }
            
            .scrolling-text {
                font-size: 26px !important;
            }
            
            .thumb-print-box {
                width: 35px !important;
                height: 35px !important;
                border: 1px solid #333 !important;
            }
            
            .signature-line {
                line-height: 25px !important;
                font-size: 11px !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Hide non-print elements */
            .no-print, .hidden-print, .no-print * {
                display: none !important;
            }
            
            /* Hide the print button on print */
            input[value="Print"], .btn-primary[value="Print"] {
                display: none !important;
            }
            
            /* Hide the select dropdown in header on print */
            .print-voucher-select, .hidden-print.print-voucher {
                display: none !important;
            }
            
            /* ADDRESS AND REFERENCE SECTION WITH INCREASED FONT SIZES */
            .address-reference-section {
                border: 2px solid #ff0000 !important;
                padding: 8px !important;
                margin-bottom: 6px !important;
                font-size: 11.5px !important;
                line-height: 1.4 !important;
            }
            
            .address-section, .reference-info {
                font-size: 11.5px !important;
                line-height: 1.4 !important;
            }
            
            /* SPECIFICALLY INCREASE THE AMOUNT FONT SIZE IN ADDRESS SECTION */
            .address-reference-section strong:not([style*="background-color"]) {
                font-size: 11.5px !important;
            }
            
            /* SPECIFICALLY INCREASE REFERENCE NO, CODE NO, DATE PRINTED FONT SIZES */
            .reference-info strong[style*="background-color: #008000"] {
                font-size: 11px !important;
                padding: 2px 3px !important;
                line-height: 1.2 !important;
            }
            
            /* TABLE STYLES WITH OPTIMIZED COLUMN WIDTHS FOR LARGE AMOUNTS */
            .payment-table {
                border: 1px solid #333 !important;
                font-size: 11px !important;
                margin: 6px 0 !important;
                table-layout: auto !important; /* Changed to auto for flexibility */
                width: 100% !important;
            }
            
            .payment-table th {
                border: 1px solid #333 !important;
                padding: 5px 4px !important;
                font-size: 11px !important;
                line-height: 1.2 !important;
                white-space: nowrap !important;
            }
            
            .payment-table td {
                border: 1px solid #333 !important;
                padding: 5px 4px !important;
                font-size: 11px !important;
                line-height: 1.2 !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
            
            /* OPTIMIZED COLUMN WIDTHS FOR BETTER FIT */
            /* S/N column */
            .payment-table th:first-child,
            .payment-table td:first-child {
                width: 25px !important;
                min-width: 25px !important;
                max-width: 25px !important;
            }
            
            /* Beneficiary column */
            .payment-table th:nth-child(2),
            .payment-table td:nth-child(2) {
                width: 140px !important;
                min-width: 140px !important;
                max-width: 160px !important;
            }
            
            /* Bank column */
            .payment-table th:nth-child(3),
            .payment-table td:nth-child(3) {
                width: 70px !important;
                min-width: 70px !important;
                max-width: 80px !important;
            }
            
            /* Branch column */
            .payment-table th:nth-child(4),
            .payment-table td:nth-child(4) {
                width: 50px !important;
                min-width: 50px !important;
                max-width: 60px !important;
            }
            
            /* Account Number column */
            .payment-table th:nth-child(5),
            .payment-table td:nth-child(5) {
                width: 90px !important;
                min-width: 90px !important;
                max-width: 100px !important;
            }
            
            /* AMOUNT COLUMN - WIDER FOR LARGE NUMBERS */
            .payment-table th:nth-child(6),
            .payment-table td:nth-child(6) {
                width: 130px !important;
                min-width: 130px !important;
                max-width: 150px !important;
                text-align: right !important;
                padding-right: 8px !important;
                white-space: nowrap !important;
                overflow: visible !important;
            }
            
            /* FIRS TIN column */
            .payment-table th:nth-child(7),
            .payment-table td:nth-child(7) {
                width: 70px !important;
                min-width: 70px !important;
                max-width: 80px !important;
            }
            
            /* Purpose column */
            .payment-table th:nth-child(8),
            .payment-table td:nth-child(8) {
                width: 160px !important;
                min-width: 160px !important;
                max-width: 200px !important;
                white-space: normal !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                hyphens: auto !important;
            }
            
            /* SPECIFICALLY FOR AMOUNT VALUES - MONOSPACE FOR BETTER ALIGNMENT */
            .payment-table td.text-right {
                font-family: 'Courier New', monospace !important;
                font-size: 10.5px !important;
                letter-spacing: -0.05em !important;
                padding-right: 6px !important;
            }
            
            /* DUE PROCESS SECTION - INCREASED FONT SIZE */
            .due-process {
                font-size: 11px !important;
                margin-bottom: 4px !important;
                line-height: 1.2 !important;
                font-weight: bold !important;
            }
            
            /* SIGNATURE SECTION - INCREASED FONT SIZES */
            .signature-section {
                font-size: 11px !important;
                margin-top: 10px !important;
            }
            
            .inner-wrap {
                margin-bottom: 6px !important;
                padding: 0 !important;
                border: none !important;
                min-height: auto !important;
            }
            
            .inner-wrap p {
                font-size: 11px !important;
                padding: 2px 0 !important;
                min-height: 14px !important;
                line-height: 1.2 !important;
                margin: 0 !important;
                border: none !important;
            }
            
            /* SPECIFICALLY INCREASE AUTHORISED SIGNATORY FONT SIZES */
            .sigtab .inner-wrap p strong[style*="background-color: #008000"] {
                font-size: 10.5px !important;
                padding: 2px 3px !important;
                line-height: 1.2 !important;
            }
            
            .sigtab tr td {
                padding: 4px !important;
            }
            
            /* INCREASE SELECT DROPDOWN SIZES IN SIGNATURE SECTIONS */
            .sigtab .type, .sigtab .selectname, .sigtab .selectname2 {
                font-size: 10.5px !important;
                height: 16px !important;
                line-height: 1.2 !important;
            }
            
            /* REMOVE BR TAGS IN PRINT FOR SIGNATURE SECTIONS */
            .sigtab .inner-wrap br,
            .sigtab br {
                display: none !important;
                height: 0 !important;
                line-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* EXCEPTION: Keep br tags in Confirm By Me section for proper spacing */
            .sigtab .inner-wrap.confirm-by-me-section br,
            .confirm-by-me-section br {
                display: block !important;
                height: auto !important;
                line-height: normal !important;
                margin: 4px 0 !important;
            }
            
            /* MAKE SURE "CONFIRM BY ME" SECTION HAS SAME MARGINS/PADDING AS "SUBMITTED FOR CONFIRMATION" */
            .sigtab .inner-wrap:last-child,
            .sigtab .inner-wrap.confirm-by-me-section {
                margin-bottom: 6px !important;
                padding: 0 !important;
                min-height: auto !important;
            }
            
            .sigtab .inner-wrap:last-child p,
            .sigtab .inner-wrap.confirm-by-me-section p {
                padding: 2px 0 !important;
                margin: 0 !important;
                min-height: 14px !important;
                line-height: 1.2 !important;
            }
            
            /* INCREASE MARGINS BETWEEN SECTIONS */
            #second, #third {
                margin-top: 8px !important;
            }
            
            .table-container {
                margin: 0 !important;
            }
            
            .payment-instruction {
                font-size: 11.5px !important;
                margin: 8px 0 0 0 !important;
                padding: 6px !important;
                border-left: 2px solid #333 !important;
                line-height: 1.4 !important;
            }
            
            .amount-in-words {
                font-size: 11.5px !important;
                margin: 8px 0 !important;
                padding: 6px !important;
                border: 1px dashed #ccc !important;
                line-height: 1.4 !important;
            }
            
            .total-amount {
                font-size: 11.5px !important;
                font-weight: bold;
                white-space: nowrap !important;
                overflow: visible !important;
            }
            
            /* PAGE BREAK AVOIDANCE */
            .compact-header, .address-reference-section, .table-container, 
            .signature-section, #first, #second, #third {
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
                page-break-after: avoid !important;
            }
            
            /* FORCE SINGLE PAGE PRINTING WITH OPTIMIZED MARGINS */
            @page {
                size: A4 portrait;
                margin: 0.4cm;
            }
            
            body {
                margin: 0 !important;
                padding: 5px !important;
                width: 100% !important;
                height: auto !important;
                overflow: hidden !important;
            }
            
            /* Increase select dropdowns size in print */
            .type, .typeSelect, select {
                font-size: 11.5px !important;
                height: 20px !important;
                line-height: 1.3 !important;
            }
            
            /* Increase all text inside tables */
            .payment-table * {
                font-size: 11px !important;
                line-height: 1.2 !important;
            }
            
            /* Increase strong/bold text sizes */
            strong {
                font-size: 11.5px !important;
                padding: 0 3px !important;
            }
            
            /* Force everything to fit */
            .row, .col-xs-8, .col-xs-4, .col-md-12 {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Hide table columns that aren't essential for print */
            .payment-table th.hidden-print,
            .payment-table td.hidden-print {
                display: none !important;
                width: 0 !important;
            }
            
            /* Specifically hide the view/update columns in the first table */
            .payment-table th:nth-child(9),
            .payment-table td:nth-child(9),
            .payment-table th:nth-child(10),
            .payment-table td:nth-child(10),
            .payment-table th:nth-child(11),
            .payment-table td:nth-child(11) {
                display: none !important;
            }
            
            /* Hide the watermark during printing for more space */
            .watermark {
                display: none !important;
            }
        }
        
        /* Additional CSS to ensure images print */
        .header-logo img {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }
        
        img {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }
        
        .typeSelect {
            border: 0px;
            outline: 0px;
            text-align: right;
            float: right;
            -webkit-appearance: none;
            -moz-appearance: none;
            text-indent: 1px;
            text-overflow: '';
            padding-right: 0px;
        }
        
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            border: none;
            overflow: hidden;
        }
        
        .type {
            border: 0px;
            outline: 0px;
            -webkit-appearance: none;
            -moz-appearance: none;
            text-indent: 1px;
            text-overflow: '';
        }
        
        /* HEADER WITH CENTERED LAYOUT */
        .compact-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            width: 100%;
            font-size: 0;
            white-space: nowrap;
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: nowrap;
        }
        
        .header-logo {
            display: inline-block;
            vertical-align: middle;
            width: 90px;
            margin: 0 20px;
            padding: 0;
            flex-shrink: 0;
        }
        
        .header-logo img {
            max-height: 90px;
            width: auto;
            display: block;
            margin: 0 auto;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }
        
        .header-center {
            display: inline-block;
            vertical-align: middle;
            text-align: center;
            margin: 0 20px;
            padding: 0;
            font-size: 16px;
            white-space: normal;
            max-width: 50%;
            flex-shrink: 1;
        }
        
        .court-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 6px 0;
            line-height: 1.3;
        }
        
        .zone-title {
            font-size: 16px;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }
        
        .account-number {
            font-size: 16px;
            font-weight: bold;
            margin: 8px 0;
            line-height: 1.3;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin: 8px 0;
            text-decoration: underline;
            line-height: 1.3;
        }
        
        /* ADDRESS SECTION WITH RED BORDER - UPDATED TO REMOVE SPACING */
        .address-reference-section {
            border: 3px solid #ff0000 !important;
            padding: 15px;
            margin-bottom: 0 !important;
            background-color: #fff;
            position: relative;
            z-index: 1;
            font-size: 15px;
        }
        
        .address-section {
            font-size: 15px;
            line-height: 1.6;
        }
        
        .reference-info {
            font-size: 15px;
            text-align: right;
        }
        
        .payment-instruction {
            font-size: 15px;
            margin: 15px 0 0 0 !important;
            padding: 12px;
            background-color: #f5f5f5;
            border-left: 4px solid #333;
            position: relative;
            z-index: 1;
        }
        
        /* TABLE CONTAINER - UPDATED TO REMOVE SPACING */
        .table-container {
            width: 100%;
            margin: 0 !important;
            position: relative;
            z-index: 1;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #333;
            position: relative;
            z-index: 1;
            background-color: #fff;
            margin-top: 0 !important;
        }
        
        .payment-table th {
            background-color: green;
            border: 2px solid #333;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            color: #fff;
        }
        
        .payment-table td {
            border: 2px solid #333;
            padding: 10px 8px;
            font-size: 15px;
            background-color: #fff;
        }
        
        /* SPECIFICALLY ALLOW TEXT WRAPPING FOR PURPOSE COLUMN IN NORMAL VIEW */
        .payment-table td.text-left:last-child,
        .payment-table td:nth-child(8) {
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            min-width: 200px;
            max-width: 300px;
        }
        
        /* MONOSPACE FONT FOR AMOUNT COLUMNS IN NORMAL VIEW TOO */
        .payment-table td.text-right {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        .signature-section {
            position: relative;
            z-index: 1;
            font-size: 15px;
        }
        
        .signature-box {
            margin-top: 40px;
            padding-top: 25px;
        }
        
        .signature-line {
            white-space: nowrap;
            margin: 0;
            padding: 0;
            line-height: 55px;
            font-size: 15px;
        }
        
        .signature-label {
            font-weight: bold;
            font-size: 15px;
        }
        
        .total-amount {
            font-weight: bold;
            text-align: right;
            font-size: 16px;
            font-family: 'Courier New', monospace;
        }
        
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .amount-in-words {
            font-style: italic;
            margin: 15px 0 20px 0;
            padding: 12px;
            background-color: #f9f9f9;
            border: 2px dashed #ccc;
            font-size: 15px;
            position: relative;
            z-index: 1;
        }
        
        .due-process {
            font-weight: bold;
            font-size: 15px;
            position: relative;
            z-index: 1;
            margin-bottom: 10px !important;
        }
        
        .cbn-section {
            margin-top: 50px;
            padding-top: 25px;
            width: 50%;
            margin-left: auto;
        }
        
        /* ACTION BUTTONS - REDUCED MARGIN */
        .action-buttons {
            margin-top: 25px !important;
            padding-top: 18px !important;
            border-top: 2px solid #ddd;
            position: relative;
            z-index: 1;
        }
        
        .print-voucher {
            margin-bottom: 18px;
        }
        
        /* Thumb Print Box Styles - REDUCED BORDER BOLDNESS */
        .thumb-print-box {
            display: inline-block;
            width: 80px;
            height: 80px;
            border: 1px solid #333;
            margin-left: 10px;
            vertical-align: middle;
            position: relative;
            top: -2px;
        }

        /* REMOVE THUMB PRINT BOX FROM "CONFIRM BY ME" SECTION */
        .confirm-by-me-section .thumb-print-box {
            display: none !important;
        }
        
        /* Ensure consistent spacing for all signature sections */
        .sigtab .inner-wrap {
            margin-bottom: 18px !important;
            padding: 0 !important;
        }
        
        .sigtab .inner-wrap p {
            margin: 5px 0 !important;
            padding: 4px 0 !important;
            min-height: 25px;
            font-size: 15px;
            line-height: 1.4;
        }
        
        /* Adjust signature line height for consistency */
        .sigtab .signature-line {
            line-height: 55px;
            margin: 0;
            padding: 0;
        }

        /* Reduce margins and padding for signature sections */
        .inner-wrap {
            border: none !important;
            padding: 0 !important;
            margin-bottom: 12px !important;
        }
        
        .inner-wrap p {
            border: none !important;
            padding: 4px 0 !important;
            margin: 0 !important;
            min-height: 25px;
            font-size: 15px;
        }
        
        /* Ensure everything stays on one line */
        .signature-line-container {
            white-space: nowrap;
            display: block;
            margin: 0;
            padding: 0;
        }
        
        /* Reduce spacing in signature table cells */
        .sigtab tr td {
            padding: 12px;
        }
        
        /* Remove any remaining borders from inner-wrap */
        .inner-wrap {
            border: none !important;
        }
        
        /* Keep existing animations */
        .bg {
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            -webkit-animation: myfirst 5s;
            animation: myfirst 5s;
        }
        
        @-webkit-keyframes myfirst {
            from { opacity: 0.2; }
            to { opacity: 1; }
        }
        
        @keyframes myfirst {
            from { opacity: 0.2; }
            to { opacity: 1; }
        }
        
        /* Hide sections by default */
        #second, #third {
            display: none;
        }
        
        /* Show all when no selection */
        .print-voucher[value=""] ~ div #first,
        .print-voucher[value=""] ~ div #second,
        .print-voucher[value=""] ~ div #third {
            display: block;
        }
        
        /* Ensure content stays above watermark */
        .container-fluid {
            position: relative;
            z-index: 1;
        }
        
        /* FIRST SECTION - REMOVE TOP SPACING */
        #first {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        
        /* THIRD SECTION - REDUCED TOP MARGIN */
        #third {
            margin-top: 25px !important;
        }
        
        /* Additional font size increases for consistency */
        .compact-header select {
            font-size: 16px !important;
        }
        
        .address-reference-section strong {
            font-size: 15px !important;
        }
        
        .payment-table .text-right {
            font-size: 15px !important;
        }
        
        .action-buttons .btn {
            font-size: 16px !important;
            padding: 10px 20px !important;
        }
        
        /* Increase button sizes in tables */
        .btn-xs {
            padding: 6px 10px !important;
            font-size: 13px !important;
        }
        
        /* Fix for print button in action buttons */
        .action-buttons .btn[onclick*="print"] {
            display: inline-block;
        }
        
        @media print {
            .action-buttons .btn[onclick*="print"] {
                display: none !important;
            }
            
            /* Keep print size the same as before but with reduced border */
            .thumb-print-box {
                width: 35px !important;
                height: 35px !important;
                border: 1px solid #333 !important;
            }
        }
        
        /* Make tables more compact for printing */
        .printing-compact-mode .payment-table {
            table-layout: auto !important;
        }
        
        .printing-compact-mode .payment-table td,
        .printing-compact-mode .payment-table th {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        
        /* Make signature sections more compact */
        .printing-compact-mode .sigtab .inner-wrap {
            min-height: auto !important;
        }
        
        .printing-compact-mode .sigtab p {
            margin: 2px 0 !important;
        }
        
        /* Add specific class for confirm by me section */
        .confirm-by-me-section {
            margin-bottom: 18px !important;
            padding: 0 !important;
        }
        
        .confirm-by-me-section p {
            margin: 5px 0 !important;
            padding: 4px 0 !important;
            min-height: 25px !important;
        }
    </style>
    
    <script type="text/javascript" src="{{ asset('assets/js/numberToWords.js') }}"></script>
</head>

<body onload="lookup();">
    <!-- Watermark Container - HORIZONTAL - 20px SIZE, NO ANIMATION -->
    <div class="watermark">
        <!-- Static horizontal watermarks at different positions -->
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        <div class="watermark-text">ISALU HOSPITAL LIMITED</div>
        
        <!-- Static horizontal watermarks for even more coverage - NO ANIMATION -->
        <div class="watermark-row">
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
        </div>
        
        <div class="watermark-row">
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
        </div>
        
        <div class="watermark-row">
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
        </div>
        
        <div class="watermark-row">
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
        </div>
        
        <div class="watermark-row">
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
            <span class="scrolling-text">ISALU HOSPITAL LIMITED</span>
        </div>
    </div>
    
    <div class="container-fluid">
        <!-- Header Section - CENTERED LAYOUT -->
        <div class="compact-header">
            <!-- Left Logo - Original -->
            <div class="header-logo">
                <img src="{{ asset('Images/coat.jpg') }}" 
                     style="-webkit-print-color-adjust: exact; print-color-adjust: exact; color-adjust: exact; max-height: 90px; width: auto;">
            </div>
            
            <!-- Center Content -->
            <div class="header-center">
                <h4 class="court-title">ISALU HOSPITAL LIMITED</h4>
                <h6 class="zone-title">Three Arms Zone</h6>
                <div class="account-number">
                    <strong>ACCOUNT NO.: 
                        <select class="type">
                            @foreach ($accountDetails as $list)
                                <option>{{ $list->account_no }}</option>
                            @endforeach
                        </select>
                    </strong>
                </div>
                <div class="document-title">E-PAYMENT SCHEDULE</div>
                <div class="hidden-print print-voucher">
                    <select class="typeSelect print-voucher-select">
                        <option value="">Select mandate to print</option>
                        <option value="first">First Mandate</option>
                        <option value="second">Analysis 1</option>
                        <option value="third">Analysis 2</option>
                    </select>
                </div>
            </div>
            
            <!-- Right Logo - Original -->
            <div class="header-logo">
                <img src="{{ asset('Images/scnlogo.jpg') }}" 
                     style="-webkit-print-color-adjust: exact; print-color-adjust: exact; color-adjust: exact; max-height: 90px; width: auto;">
            </div>
        </div>
        
        <!-- Address and Reference Section WITH RED BORDER -->
        <div class="address-reference-section">
            <div class="row">
                <div class="col-xs-8 address-section">
                    <div class="address">
                        {!! $accountAddress->address !!}
                    </div>
                </div>
                <div class="col-xs-4 reference-info">
                    <div><strong style="background-color: #008000; color: #fff;">Reference No:</strong> 
                        <input type="text" class="type" name="batch" bch="{{ $current_batch }}" 
                               value="{{ $status->capital_refno ?? 'SCN/3/2/01AVOL.1/03' }}" id="ref" />
                    </div>
                    <div><strong style="background-color: #008000; color: #fff;">Code No:</strong> 
                        <input type="text" class="type" name="batch" bch="{{ $current_batch }}" 
                               value="{{ $status->adjusted_batch ?? '' }}" id="batchRef" />
                    </div>
                    <div><strong style="background-color: #008000; color: #fff;">Date Printed:</strong> 
                        <input type="text" class="type" name="datePrepared" bch="{{ $current_batch }}" 
                               thedate="{{ $date }}" value="{{ $date }}" id="dateprep" />
                    </div>
                </div>
            </div>
            <div class="">
                Please credit the account(s) of the under-listed beneficiaries and debit our Account Number above with the sum of 
                <strong>₦ {{ number_format($sum + $whtsum + $vatsum, 2) }}</strong> 
                (Amount in Words: <strong><span id="result">
                    <?php
                    // PHP fallback for amount in words
                    $totalAmount = $sum + $whtsum + $vatsum;
                    
                    // Function to convert number to words (simplified version)
                    function numberToWords($num) {
                        // Use lowercase for all words
                        $ones = array(
                            0 => "", 1 => "one", 2 => "two", 3 => "three", 4 => "four", 
                            5 => "five", 6 => "six", 7 => "seven", 8 => "eight", 9 => "nine",
                            10 => "ten", 11 => "eleven", 12 => "twelve", 13 => "thirteen", 
                            14 => "fourteen", 15 => "fifteen", 16 => "sixteen", 17 => "seventeen", 
                            18 => "eighteen", 19 => "nineteen"
                        );
                        
                        $tens = array(
                            2 => "twenty", 3 => "thirty", 4 => "forty", 5 => "fifty",
                            6 => "sixty", 7 => "seventy", 8 => "eighty", 9 => "ninety"
                        );
                        
                        $hundreds = array(
                            "hundred", "thousand", "million", "billion", "trillion", "quadrillion"
                        );
                        
                        // Format number with 2 decimal places
                        $formatted_num = number_format($num, 2, ".", ",");
                        $num_arr = explode(".", $formatted_num);
                        
                        $wholenum = $num_arr[0];
                        $decnum = isset($num_arr[1]) ? $num_arr[1] : '00';
                        
                        // Convert decimal part to integer (remove leading zeros)
                        $decnum_int = (int)$decnum;
                        
                        $whole_arr = array_reverse(explode(",", $wholenum));
                        krsort($whole_arr);
                        
                        $rettxt = "";
                        
                        foreach($whole_arr as $key => $i){
                            // Convert to integer to remove leading zeros
                            $i_int = (int)$i;
                            
                            if($i_int === 0 && count($whole_arr) > 1) {
                                continue; // Skip zero groups except for the last one
                            }
                            
                            if($i_int < 20){
                                $rettxt .= $ones[$i_int];
                            } elseif($i_int < 100){
                                $tens_digit = floor($i_int / 10);
                                $ones_digit = $i_int % 10;
                                $rettxt .= $tens[$tens_digit];
                                if($ones_digit > 0){
                                    $rettxt .= " ".$ones[$ones_digit];
                                }
                            } else{
                                $hundreds_digit = floor($i_int / 100);
                                $remainder = $i_int % 100;
                                $rettxt .= $ones[$hundreds_digit]." ".$hundreds[0];
                                
                                if($remainder > 0){
                                    $rettxt .= " and ";
                                    if($remainder < 20){
                                        $rettxt .= $ones[$remainder];
                                    } else{
                                        $tens_digit = floor($remainder / 10);
                                        $ones_digit = $remainder % 10;
                                        $rettxt .= $tens[$tens_digit];
                                        if($ones_digit > 0){
                                            $rettxt .= " ".$ones[$ones_digit];
                                        }
                                    }
                                }
                            }
                            
                            if($key > 0 && $i_int > 0){
                                $rettxt .= " ".$hundreds[$key]." ";
                            }
                        }
                        
                        // Handle zero amount
                        if($rettxt === "") {
                            $rettxt = "zero";
                        }
                        
                        // Add currency
                        $rettxt .= " naira";
                        
                        // Add kobo if exists
                        if($decnum_int > 0){
                            $rettxt .= " and ";
                            if($decnum_int < 20){
                                $rettxt .= $ones[$decnum_int]." kobo";
                            } else{
                                $tens_digit = floor($decnum_int / 10);
                                $ones_digit = $decnum_int % 10;
                                $rettxt .= $tens[$tens_digit];
                                if($ones_digit > 0){
                                    $rettxt .= "-".$ones[$ones_digit];
                                }
                                $rettxt .= " kobo";
                            }
                        } else {
                            $rettxt .= " only";
                        }
                        
                        // Capitalize first letter of the entire string
                        return ucfirst($rettxt);
                    }
                    
                    // Display the amount in words
                    echo numberToWords($totalAmount);
                    ?>
                </span> ONLY</strong>)
            </div>
        </div>
        
        <!-- First Mandate (Capital Format) -->
        <div id="first">       
            <!-- Beneficiaries Table (Capital Format) -->
            <div class="table-container">
                <table class="payment-table" id="tableData">
                    <thead>
                        <tr>
                            <th width="40">S/N</th>
                            <th class="text-left">Beneficiary</th>
                            <th width="140">Bank</th>
                            <th width="100">Branch</th>
                            <th width="140">Account Number</th>
                            <th width="120" class="text-right">Amount (₦)</th>
                            <th width="120">FIRS TIN</th>
                            <th class="text-left">Purpose of Payment</th>
                            @if($checkApproval != 0)
                            <th width="100" class="hidden-print">View Voucher</th>
                            <th width="140" class="hidden-print">Update Account</th>
                            <th width="140" class="hidden-print">Update Narration</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        @foreach ($mandate as $reports)
                            <?php
                            $url = url('/display/voucher/' . $reports->transactionID);
                            $transId = '';
                            if ($reports->transactionID != $transId):
                            ?>
                            <!-- Main Payment Row -->
                            <tr>
                                <td>{{ $counter++ }}</td>
                                <td class="text-left">{{ $reports->contractor }}</td>
                                <td>{{ $reports->bank }}</td>
                                <td>{{ $reports->branch ?? 'Abuja' }}</td>
                                <td>{{ $reports->accountNo }}</td>
                                <td class="text-right">{{ number_format($reports->amount, 2) }}</td>
                                <td>{{ $reports->tin ?? '104901084001' }}</td>
                                <td class="text-left">{{ $reports->purpose }}</td>
                                @if($checkApproval != 0)
                                <td class="hidden-print">
                                    <a href="{{ $url }}" class="btn btn-success btn-xs hidden-print no-print" target="_blank">View</a>
                                </td>
                                <td class="hidden-print">
                                    <a href="javascript:void()" class="update btn btn-success btn-xs hidden-print no-print" 
                                       btc="{{ $current_batch }}" id="{{ $reports->ID }}">Update</a>
                                </td>
                                <td class="hidden-print">
                                    <a href="javascript:void()" class="edit btn btn-success btn-xs hidden-print no-print" 
                                       pps="{{ $reports->purpose }}" id="{{ $reports->ID }}">Edit</a>
                                </td>
                                @endif
                            </tr>
                            <?php
                            $transId = $reports->transactionID;
                            endif;
                            
                            // Tax Rows
                            if ($reports->WHTValue > 0 || $reports->VATValue > 0):
                                $stampDuty = $reports->VATValue * 0.005; // 0.5% stamp duty
                            ?>
                            <!-- WHT Row -->
                            <tr>
                                <td>{{ $counter++ }}</td>
                                <td class="text-left">FIRS TAX PROMAX WHT</td>
                                <td>{{ $reports->wht_bank ?? 'Zenith Bank' }}</td>
                                <td>Abuja</td>
                                <td>{{ $reports->wht_accountNo ?? '1130089499' }}</td>
                                <td class="text-right">{{ number_format($reports->WHTValue, 2) }}</td>
                                <td>-</td>
                                <td class="text-left">WHT</td>
                                @if($checkApproval != 0)
                                <td class="hidden-print">
                                    <a href="{{ $url }}" class="btn btn-success btn-xs hidden-print no-print" target="_blank">View</a>
                                </td>
                                <td class="hidden-print">
                                    <a href="javascript:void()" tx="tax" accts="{{ $reports->wht_accountNo }}" 
                                       bk="{{ $reports->wht_bank }}" bene="{{ $reports->wht_payee }}" 
                                       class="tax btn btn-success btn-xs hidden-print no-print" 
                                       btc="{{ $current_batch }}" id="{{ $reports->ID }}">Update</a>
                                </td>
                                <td class="hidden-print"></td>
                                @endif
                            </tr>
                            
                            <!-- VAT Row -->
                            <tr>
                                <td>{{ $counter++ }}</td>
                                <td class="text-left">FIRS TAX PROMAX VAT</td>
                                <td>{{ $reports->vat_bank ?? 'Zenith Bank' }}</td>
                                <td>Abuja</td>
                                <td>{{ $reports->vat_accountNo ?? '1130089499' }}</td>
                                <td class="text-right">{{ number_format($reports->VATValue, 2) }}</td>
                                <td>-</td>
                                <td class="text-left">VAT</td>
                                @if($checkApproval != 0)
                                <td class="hidden-print">
                                    <a href="{{ $url }}" class="btn btn-success btn-xs hidden-print no-print" target="_blank">View</a>
                                </td>
                                <td class="hidden-print">
                                    <a href="javascript:void()" vt="vat" accts="{{ $reports->vat_accountNo }}" 
                                       bk="{{ $reports->vat_bank }}" bene="{{ $reports->vat_payee }}" 
                                       class="vat btn btn-success btn-xs hidden-print no-print" 
                                       btc="{{ $current_batch }}" id="{{ $reports->ID }}">Update</a>
                                </td>
                                <td class="hidden-print"></td>
                                @endif
                            </tr>
                            
                            <!-- Stamp Duty Row -->
                            @if($reports->VATValue > 0)
                            <tr>
                                <td>{{ $counter++ }}</td>
                                <td class="text-left">FIRS TAX PROMAX SD</td>
                                <td>{{ $reports->vat_bank ?? 'Zenith Bank' }}</td>
                                <td>Abuja</td>
                                <td>{{ $reports->vat_accountNo ?? '1130089499' }}</td>
                                <td class="text-right">{{ number_format($stampDuty, 2) }}</td>
                                <td>-</td>
                                <td class="text-left">FIRS (STAMP DUTY)</td>
                                @if($checkApproval != 0)
                                <td class="hidden-print">
                                    <a href="{{ $url }}" class="btn btn-success btn-xs hidden-print no-print" target="_blank">View</a>
                                </td>
                                <td class="hidden-print"></td>
                                <td class="hidden-print"></td>
                                @endif
                            </tr>
                            @endif
                            <?php endif; ?>
                        @endforeach
                        
                        @if (count($mandate) == 0)
                            <tr>
                                <td colspan="{{ $checkApproval != 0 ? 11 : 8 }}" class="text-center">Data not available</td>
                            </tr>
                        @endif
                    </tbody>
                    @if (count($mandate) > 0)
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong style="background-color: #008000; color: #fff;">TOTAL:</strong></td>
                            <td></td>
                            <td class="text-right total-amount">₦ {{ number_format($sum + $whtsum + $vatsum, 2) }}</td>
                            <td colspan="{{ $checkApproval != 0 ? 5 : 2 }}"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            
            <!-- First Mandate Signature Section -->
            <!-- Due Process -->
            <div class="due-process">ALL DUE PROCESS COMPLIED WITH</div> 
            <div class="signature-section">
                <table class="table" style="width: 100%;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <div class="col-md-12 sigtab" style="padding:0px;">
                                <div class="inner-wrap">
                                    <p><strong style="background-color: #008000; color: #fff;"> Authorised Signatory:</strong></p>
                                    <p>Name: 
                                        <select class="type selectname">
                                            <option value="">Select One</option>
                                            @if (count($sigA) > 0)
                                                @foreach ($sigA as $list)
                                                    <option value="{{ $list->id ?? '' }}"
                                                        @if ($sig1 != '') @if ($list->id == $sig1->signatoryId) selected @endif
                                                        @endif>{{ $list->Name ?? '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </p>
                                    <p class="signature-line">
                                        Signature:_______________________________ Thumb Print
                                        <span class="thumb-print-box"></span>
                                    </p>
                                    <p>Date:___________________________________ </p>
                                    <p>Phone No. 
                                        @if ($sig1 != '') {{ $sig1->phone ?? '' }} @endif
                                    </p>
                                </div>
                                
                                <div class="inner-wrap">
                                    <p><strong style="background-color: #008000; color: #fff;"> Authorised Signatory </strong></p>
                                    <p>Name: 
                                        <select class="type selectname2">
                                            <option value="">Select One</option>
                                            @if (count($sigB) > 0)
                                                @foreach ($sigB as $list)
                                                    <option value="{{ $list->id ?? '' }}"
                                                        @if ($sig2 != '') @if ($list->id == $sig2->signatoryId) selected @endif
                                                        @endif>{{ $list->Name ?? '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </p>
                                    <p class="signature-line">
                                        Signature:_______________________________ Thumb Print
                                        <span class="thumb-print-box"></span>
                                    </p>
                                    <p>Date:___________________________________ </p>
                                    <p>Phone No.
                                        @if ($sig2 != '') {{ $sig2->phone ?? '' }} @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        
                        <td style="width: 50%; vertical-align: top;">
                            <div class="col-md-12 col-xs-12 col-sm-12 sigtab" style="padding:0px;">
                                <div class="inner-wrap">
                                    <p><strong style="background-color: #008000; color: #fff;"> Submitted for Confirmation by </strong></p>
                                    <p>Name:____________________________________ </p>
                                    <p class="signature-line">
                                        Signature:_________________________________ Thumb Print
                                        <span class="thumb-print-box"></span>
                                    </p>
                                    <p>Date:_____________________________________ </p>
                                </div>
                                
                                <!-- "Confirm By Me" section - WITH BR TAGS FOR PROPER SPACING -->
                                <div class="inner-wrap confirm-by-me-section"> <br>
                                    <p><strong style="background-color: #008000; color: #fff;"> Confirm By Me </strong></p> <br>
                                    <p>Name:____________________________________ </p> <br>
                                    <p class="signature-line">
                                        Signature:_________________________________ 
                                    </p> <br>
                                    <p>Date:_____________________________________ </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Second Mandate Analysis -->
        <div id="second" style="margin-top:20px;">
            <div class="table-container">
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th width="40">S/N</th>
                            <th class="text-left">BENEFICIARY</th>
                            <th width="120">NO. OF ITEMS</th>
                            <th width="120" class="text-right">AMOUNT (₦)</th>
                            <th class="text-left">PURPOSE OF PAYMENT</th>
                            <th width="100" class="hidden-print">Edit Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $key = 1; ?>
                        @foreach ($mandate as $reports)
                            <tr>
                                <td>{{ $key++ }}</td>
                                <td class="text-left">{{ $reports->bankName }}</td>
                                <td>{{ $reports->NumOfBanks }}</td>
                                <td class="text-right">
                                    {{ number_format($reports->totalAmount + $reports->vat + $reports->tax, 2, '.', ',') }}
                                </td>
                                <td class="text-left">{{ $reports->capital_bank_purpose ?? $reports->purpose }}</td>
                                <td class="hidden-print">
                                    <a href="javascript:void()" class="editPurpose btn btn-success btn-xs hidden-print no-print"
                                       pps="{{ $reports->purpose }}" id="{{ $reports->bankName }}">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                        
                        @if (count($mandate) == 0)
                            <tr>
                                <td colspan="6" class="text-center">Data not available</td>
                            </tr>
                        @endif
                    </tbody>
                    @if (count($mandate) > 0)
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong style="background-color: #008000; color: #fff;">TOTAL:</strong></td>
                            <td class="text-right total-amount">₦ {{ number_format($sum + $whtsum + $vatsum, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            
            <!-- Second Mandate Signature Section -->
            <div class="signature-section">
                <table class="table" style="width: 100%;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <div class="col-md-12 sigtab" style="padding:0px;">
                                <div class="inner-wrap">
                                    <p><strong style="background-color: #008000; color: #fff;"> Authorised Signature </strong></p>
                                    <p>Name: 
                                        <select class="type selectname">
                                            <option value="">Select One</option>
                                            @if (count($sigA) > 0)
                                                @foreach ($sigA as $list)
                                                    <option value="{{ $list->id ?? '' }}"
                                                        @if ($sig1 != '') @if ($list->id == $sig1->signatoryId) selected @endif
                                                        @endif>{{ $list->Name ?? '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </p>
                                    <p class="signature-line">
                                        Signature & Thumb Print
                                        <span class="thumb-print-box"></span>
                                    </p>
                                    <p>Date: </p>
                                    <p>Phone No. 
                                        @if ($sig1 != '') {{ $sig1->phone ?? '' }} @endif
                                    </p>
                                </div>
                                
                                <div class="inner-wrap">
                                    <p><strong style="background-color: #008000; color: #fff;"> Authorised Signature </strong></p>
                                    <p>Name: 
                                        <select class="type selectname2">
                                            <option value="">Select One</option>
                                            @if (count($sigB) > 0)
                                                @foreach ($sigB as $list)
                                                    <option value="{{ $list->id ?? '' }}"
                                                        @if ($sig2 != '') @if ($list->id == $sig2->signatoryId) selected @endif
                                                        @endif>{{ $list->Name ?? '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </p>
                                    <p class="signature-line">
                                        Signature & Thumb Print
                                        <span class="thumb-print-box"></span>
                                    </p>
                                    <p>Date: </p>
                                    <p>Phone No. 
                                        @if ($sig2 != '') {{ $sig2->phone ?? '' }} @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        
                        <td style="width: 50%; vertical-align: top;">
                            <div class="col-md-12 col-xs-12 col-sm-12 sigtab" style="padding:0px;">
                                <div class="inner-wrap">
                                    <p><strong style="background-color: #008000; color: #fff;"> Submitted for Confirmation by </strong></p>
                                    <p>Name: </p>
                                    <p class="signature-line">
                                        Signature & Thumb Print
                                        <span class="thumb-print-box"></span>
                                    </p>
                                    <p>Date: </p>
                                </div>
                                
                                <!-- "Confirm By Me" section - WITH BR TAGS FOR PROPER SPACING -->
                                <div class="inner-wrap confirm-by-me-section">
                                    <p><strong style="background-color: #008000; color: #fff;"> Confirm By Me </strong></p>
                                    <p>Name: </p>
                                    <p class="signature-line">
                                        Signature
                                    </p>
                                    <p>Date: </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Third Mandate Analysis -->
        <div id="third">
            <div class="table-container">
                <table class="payment-table" id="tblExport">
                    <thead>
                        <tr>
                            <th>BANK</th>
                            <th>CODE</th>
                            <th>NAME OF COMPANY</th>
                            <th>ACCT NO.</th>
                            <th>AMOUNT</th>
                            <th></th>
                            <th>NARRATION</th>
                            <th>SCN PAYMENT</th>
                            <th>DATE</th>
                            <th class="hidden-print">UPDATE NARRATION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalCBN = 0; @endphp
                        @foreach ($breakdown as $list)
                            @php $date = $list->date; @endphp
                            <tr>
                                <td>{{ $list->BankName }}</td>
                                <td>{{ $list->sortcode }}</td>
                                <td>{{ $list->contractor }}</td>
                                <td>{{ $list->accountNo }}</td>
                                <td>
                                    <?php $totalCBN += $list->amount + $list->VATValue + $list->WHTValue; ?>
                                    {{ number_format($list->amount + $list->VATValue + $list->WHTValue, 2) }}
                                </td>
                                <td>CR</td>
                                <td>{{ $list->purpose }}</td>
                                <td>SCN Payment</td>
                                <td>{{ $list->date }}</td>
                                <td class="hidden-print">
                                    <a href="javascript:void()" class="edit btn btn-success btn-xs hidden-print no-print"
                                       pps="{{ $list->purpose }}" id="{{ $list->ID }}">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><strong>CBN</strong></td>
                            <td colspan="3"><strong>{{ $cbn->account_no }}</strong></td>
                            <td><strong>{{ number_format($totalCBN, 2) }}</strong></td>
                            <td><strong>DR</strong></td>
                            <td>Code {{ $status->adjusted_batch ?? '' }}</td>
                            <td></td>
                            <td>{{ $date }}</td>
                            <td class="hidden-print"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <a href="{{ url()->previous() }}" class="btn btn-default" onclick="return confirm('Are you sure you want to go back?')">Back</a>
            <input type="button" class="btn btn-primary" value="Print" id="printButton" />
            <a href="{{ route('batch.export', $current_batch) }}" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
        </div>
    </div>
    
    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/table2excel.js') }}"></script>
    
    <script>
        var murl = "{{ url('/') }}";
        var amount = "<?php echo number_format($sum + $whtsum + $vatsum, 2, '.', ''); ?>";
        var money = amount.split('.');
        
        // Flag to prevent multiple print triggers
        var isPrinting = false;
        
        function lookup() {
            // Convert amount to words
            var words;
            var naira = money[0];
            var kobo = money[1];
            var word1 = toWords(naira) + "naira";
            var word2 = ", " + toWords(kobo) + " kobo";
            
            if (kobo != "00")
                words = word1 + word2;
            else
                words = word1;
            
            var getWord = words.toUpperCase();
            var parternRule1 = /HUNDRED AND NAIRA/ig;
            var parternRule2 = /HUNDRED AND THOUSAND NAIRA/ig;
            var instance1 = parternRule1.test(getWord);
            var instance2 = parternRule2.test(getWord);
            
            if ((instance1)) {
                getWord = getWord.replace(parternRule1, ' HUNDRED NAIRA ');
            } else if ((instance2)) {
                getWord = getWord.replace(parternRule2, ' HUNDRED THOUSAND NAIRA ');
            }
            
            // Display amount in words in the payment instruction
            document.getElementById('result').innerHTML = getWord;
        }
        
        function Export() {
            $("#tableData").table2excel({
                filename: "{{ 'Batch' }}_{{ $current_batch }}_Mandate.xls"
            });
        }
        
        function ExportTbl() {
            $("#tblExport").table2excel({
                filename: "{{ 'capital' }}_{{ $current_batch }}_Mandate.xls"
            });
        }
        
        // Custom print function with better handling
        function customPrint() {
            if (isPrinting) return; // Prevent multiple clicks
            
            isPrinting = true;
            var printButton = document.getElementById('printButton');
            
            if (printButton) {
                printButton.disabled = true;
                printButton.value = "Printing...";
            }
            
            // Add compact mode class
            document.body.classList.add('printing-compact-mode');
            
            // Force images to load before printing
            $('img').each(function() {
                var img = new Image();
                img.src = $(this).attr('src');
            });
            
            // Small delay to ensure images are loaded
            setTimeout(function() {
                try {
                    // Use a promise-based approach for better control
                    var printPromise = new Promise(function(resolve, reject) {
                        // Store original onbeforeprint and onafterprint
                        var originalBeforePrint = window.onbeforeprint;
                        var originalAfterPrint = window.onafterprint;
                        
                        // Set up afterprint event to re-enable button
                        window.onafterprint = function() {
                            // Remove compact mode class
                            document.body.classList.remove('printing-compact-mode');
                            
                            isPrinting = false;
                            if (printButton) {
                                printButton.disabled = false;
                                printButton.value = "Print";
                            }
                            // Restore original handler
                            if (originalAfterPrint) {
                                originalAfterPrint();
                            }
                            resolve();
                        };
                        
                        // Trigger print
                        window.print();
                    });
                    
                    // Fallback timeout to re-enable button
                    setTimeout(function() {
                        if (isPrinting) {
                            isPrinting = false;
                            document.body.classList.remove('printing-compact-mode');
                            if (printButton) {
                                printButton.disabled = false;
                                printButton.value = "Print";
                            }
                        }
                    }, 5000); // 5 second timeout
                    
                } catch (error) {
                    console.error('Print error:', error);
                    isPrinting = false;
                    document.body.classList.remove('printing-compact-mode');
                    if (printButton) {
                        printButton.disabled = false;
                        printButton.value = "Print";
                    }
                }
            }, 300);
        }
        
        $(document).ready(function() {
            // Preload images for printing
            function preloadImages() {
                var images = [
                    '{{ asset("Images/scn_logo.png") }}',
                    '{{ asset("Images/coat.jpg") }}'
                ];
                
                images.forEach(function(src) {
                    var img = new Image();
                    img.src = src;
                });
            }
            
            // Call preload function
            preloadImages();
            
            // Print button click handler with column width adjustment
            $('#printButton').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Adjust column widths for large amounts before printing
                adjustColumnWidthsForPrint();
                
                customPrint();
            });
            
            // Function to adjust column widths for printing
            function adjustColumnWidthsForPrint() {
                // Find the widest amount in the table
                var maxAmountWidth = 0;
                var maxAmountText = '';
                
                $('.payment-table td.text-right').each(function() {
                    var text = $(this).text().trim();
                    // Approximate width: each character ~7px, comma ~4px, decimal ~5px
                    var textWidth = text.length * 6.5;
                    if (textWidth > maxAmountWidth) {
                        maxAmountWidth = textWidth;
                        maxAmountText = text;
                    }
                });
                
                // Add extra padding (20px for Naira symbol and padding)
                var requiredWidth = Math.max(130, maxAmountWidth + 25);
                
                // Apply the width to amount columns
                $('.payment-table th:nth-child(6), .payment-table td:nth-child(6)').css({
                    'min-width': requiredWidth + 'px',
                    'width': requiredWidth + 'px'
                });
                
                console.log('Largest amount:', maxAmountText, 'Required width:', requiredWidth + 'px');
            }
            
            // Also handle the window.print event to prevent multiple calls
            $(window).on('beforeprint', function() {
                if (isPrinting) {
                    console.log('Print already in progress');
                }
            });
            
            // Print voucher selection
            $('.print-voucher-select').change(function() {
                var value = $(this).val();
                
                if (value == 'first') {
                    $("#second").hide();
                    $("#first").show();
                    $("#third").hide();
                } else if (value == 'second') {
                    $("#first").hide();
                    $("#second").show();
                    $("#third").hide();
                } else if (value == 'third') {
                    $("#first").hide();
                    $("#second").hide();
                    $("#third").show();
                } else {
                    $("#first").show();
                    $("#second").show();
                    $("#third").show();
                }
            });
            
            // Update account modal
            $("table tr td .update").click(function() {
                var batchNo = $(this).attr('btc');
                var epayID = $(this).attr('id');
                $(".batch").val(batchNo);
                $(".epaymentID").val(epayID);
                $(".actModal").modal('show');
            });
            
            // Edit narration modal
            $("table tr td .edit").click(function() {
                var narration = $(this).attr('pps');
                var epayID = $(this).attr('id');
                $(".narration").val(narration);
                $(".eID").val(epayID);
                $(".narrateModal").modal('show');
            });
            
            // Edit purpose modal
            $("table tr td .editPurpose").click(function() {
                var narration = $(this).attr('pps');
                var bk = $(this).attr('id');
                $(".purpose").val(narration);
                $(".bankname").val(bk);
                $(".purposeModal").modal('show');
            });
            
            // Tax update modal
            $("table tr td .tax").click(function() {
                var batchNo = $(this).attr('btc');
                var epayID = $(this).attr('id');
                var acct = $(this).attr('accts');
                var bene = $(this).attr('bene');
                var bank = $(this).attr('bk');
                var p = $(this).attr('tx');
                $(".batch").val(batchNo);
                $(".epaymentID").val(epayID);
                $("#paye").val(p);
                $(".benefi").val(bene);
                $("#banks").val(bank);
                $(".accountNo").val(acct);
                $(".payeModal").modal('show');
            });
            
            // VAT update modal
            $("table tr td .vat").click(function() {
                var batchNo = $(this).attr('btc');
                var epayID = $(this).attr('id');
                var acct = $(this).attr('accts');
                var bene = $(this).attr('bene');
                var bank = $(this).attr('bk');
                var p = $(this).attr('vt');
                $("#paye").val(p);
                $(".batch").val(batchNo);
                $(".epaymentID").val(epayID);
                $(".benefi").val(bene);
                $("#banks").val(bank);
                $(".accountNo").val(acct);
                $(".payeModal").modal('show');
            });
            
            // Account change
            $(".type").on('change', function() {
                var acct = $(this).val();
                $.ajax({
                    url: murl + '/get-account/address',
                    type: "post",
                    data: {
                        'accountNo': acct,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(datas) {
                        console.log(datas.address);
                        $('.address').html(datas.address);
                    }
                });
            });
            
            // Signatory 1
            $(".selectname").on('change', function() {
                var id = $(this).val();
                var batch = "{{ $current_batch }}";
                $.ajax({
                    url: murl + '/epay/signatory',
                    type: "post",
                    data: {
                        'signid': id,
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(datas) {
                        console.log(datas.phone);
                    }
                });
            });
            
            // Signatory 2
            $(".selectname2").on('change', function() {
                var id = $(this).val();
                var batch = "{{ $current_batch }}";
                $.ajax({
                    url: murl + '/epay/signatory',
                    type: "post",
                    data: {
                        'signid': id,
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(datas) {
                        console.log(datas.phone);
                    }
                });
            });
            
            // Batch reference update
            $("#batchRef").blur(function() {
                var batch = $(this).attr('bch');
                var newBatch = $(this).val();
                $.ajax({
                    url: murl + '/update/batch',
                    type: "post",
                    data: {
                        'newBatch': newBatch,
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(datas) {
                        console.log(datas.phone);
                    }
                });
            });
            
            // Reference update
            $("#ref").blur(function() {
                var batch = $(this).attr('bch');
                var newBatch = $(this).val();
                $.ajax({
                    url: murl + '/update/ref',
                    type: "post",
                    data: {
                        'newBatch': newBatch,
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(datas) {
                        console.log(datas.phone);
                    }
                });
            });
            
            // Date prepared update
            $("#dateprep").change(function() {
                var batch = $(this).attr('bch');
                $.ajax({
                    url: murl + '/update/date',
                    type: "post",
                    data: {
                        'preparedate': $(this).val(),
                        'batch': batch,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(datas) {
                        console.log(datas);
                    }
                });
            });
            
            // Datepicker
            $(function() {
                $("#dateprep").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: 'yy-mm-dd'
                });
            });
            
            // Prevent back button from resubmitting form
            $('.btn-default[href="{{ url()->previous() }}"]').click(function(e) {
                e.preventDefault();
                if(confirm('Are you sure you want to go back?')) {
                    window.location.href = $(this).attr('href');
                }
            });
            
            // Add afterprint event listener as fallback
            if (window.matchMedia) {
                var mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function(mql) {
                    if (!mql.matches) {
                        // Print dialog was closed (either printed or cancelled)
                        isPrinting = false;
                        document.body.classList.remove('printing-compact-mode');
                        var printButton = document.getElementById('printButton');
                        if (printButton) {
                            printButton.disabled = false;
                            printButton.value = "Print";
                        }
                    }
                });
            }
            
            // Traditional afterprint event as additional fallback
            window.onafterprint = function() {
                isPrinting = false;
                document.body.classList.remove('printing-compact-mode');
                var printButton = document.getElementById('printButton');
                if (printButton) {
                    printButton.disabled = false;
                    printButton.value = "Print";
                }
            };
        });
    </script>
</body>
</html>
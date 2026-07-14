<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class CbnEpaymentExport implements FromCollection, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return new Collection([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Enable gridlines
                $sheet->setShowGridLines(true);

                // Column widths - removed column A since we're removing S/N column
                $sheet->getColumnDimension('A')->setWidth(40); // Changed from S/N to Beneficiary
                $sheet->getColumnDimension('B')->setWidth(20); // Bank
                $sheet->getColumnDimension('C')->setWidth(15); // Branch
                $sheet->getColumnDimension('D')->setWidth(20); // Account Number
                $sheet->getColumnDimension('E')->setWidth(20); // Amount (₦)
                $sheet->getColumnDimension('F')->setWidth(25); // FIRS TIN - INCREASED FROM 15 TO 25
                $sheet->getColumnDimension('G')->setWidth(60); // Purpose of Payment (moved from H to G)

                // Header row heights — reduced
                $sheet->getRowDimension(1)->setRowHeight(70);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(18);

                // ─── Logos ───────────────────────────────────────────
                if (file_exists(public_path('images/coat.jpg'))) {
                    $drawingLeft = new Drawing();
                    $drawingLeft->setName('Coat Logo');
                    $drawingLeft->setPath(public_path('images/coat.jpg'));
                    $drawingLeft->setHeight(70);
                    $drawingLeft->setCoordinates('B1'); // Changed from C1 to B1
                    $drawingLeft->setOffsetX(50);
                    $drawingLeft->setOffsetY(20);
                    $drawingLeft->setWorksheet($sheet);
                }

                if (file_exists(public_path('images/scnlogo.jpg'))) {
                    $drawingRight = new Drawing();
                    $drawingRight->setName('SCN Logo');
                    $drawingRight->setPath(public_path('images/scnlogo.jpg'));
                    $drawingRight->setHeight(70);
                    $drawingRight->setCoordinates('G1'); // Changed from H1 to G1
                    $drawingRight->setOffsetX(50);
                    $drawingRight->setOffsetY(20);
                    $drawingRight->setWorksheet($sheet);
                }

                // ─── HEADER TEXT ───────────────────────────────────────
                $sheet->setCellValue('A1', 'ISALU HOSPITAL LIMITED');
                $sheet->mergeCells('A1:G1'); // Changed from H1 to G1

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FF008000'],
                        'size'  => 18,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->setCellValue('A2', 'Three Arms Zone, Abuja');
                $sheet->mergeCells('A2:G2'); // Changed from H2 to G2
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $accountNo = $this->data['accountDetails']->first()->account_no ?? '002-02096-42-01-9';
                $sheet->setCellValue('A3', "ACCOUNT NO.: {$accountNo}");
                $sheet->mergeCells('A3:G3'); // Changed from H3 to G3
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A4', 'E-PAYMENT SCHEDULE');
                $sheet->mergeCells('A4:G4'); // Changed from H4 to G4
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ─── Address ─────────────────────────────────────────────────
                $rawAddress = $this->data['accountAddress']->address ?? "The Branch Controller\nCentral Bank of Nigeria\nGarki Abuja,\nAbuja";
                $addressText = str_replace(['<p>', '</p>'], ['', "\n"], $rawAddress);
                $addressText = trim(preg_replace('/\n+/', "\n", $addressText));
                $sheet->setCellValue('A6', $addressText);
                
                // Address takes 2 rows (6-7)
                $sheet->mergeCells('A6:G7'); // 2 rows for address
                
                // ─── INCREASED HEIGHT FOR ROWS 6 AND 7 ───────────────────────
                // Set increased height for address rows (increased from 20 to 25)
                $sheet->getRowDimension(6)->setRowHeight(25); // INCREASED FROM 20 TO 25
                $sheet->getRowDimension(7)->setRowHeight(25); // INCREASED FROM 20 TO 25
                
                // Apply italic style to address
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 11,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_TOP,
                        'wrapText'   => true,
                    ],
                ]);

                // ─── Remove row 9 only ─────────────────────────────────────
                $sheet->getRowDimension(9)->setRowHeight(5); // Very small height to "remove" it
                $sheet->setCellValue('A9', ''); // Clear any content

                // Payment instruction - on row 8 - REMOVED ₦ FROM PAYMENT INSTRUCTION
                $total = number_format(($this->data['sum'] ?? 0) + ($this->data['whtsum'] ?? 0) + ($this->data['vatsum'] ?? 0), 2);
                $sheet->setCellValue('A8', "Please credit the account(s) of the under-listed beneficiaries and debit our Account Number above");
                // REMOVED: "with the sum of ₦{$total}" - changed to "with the sum of {$total}"
                
                // Merge payment instruction
                $sheet->mergeCells('A8:G8'); // Single row for payment instruction
                
                // Set height for payment instruction row
                $sheet->getRowDimension(8)->setRowHeight(20);
                
                $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A8')->getAlignment()->setWrapText(true);
                $sheet->getStyle('A8')->getFont()->setSize(11);

                // ─── REMOVED TABLE HEADER SECTION ────────────────────────────
                // Starting row for data (without header row) - data starts at row 10
                $dataStartRow = 10;

                // ─── Insert table data ───────────────────────────────────────
                $counter = 1; // We'll keep counter but won't use it in output
                $dataRows = [];
                $reports = $this->data['breakdown'] ?? $this->data['mandate'];

                foreach ($reports as $report) {
                    // REMOVED S/N column - start with Beneficiary
                    $tin = $report->tin ?? '104901084001';
                    
                    $dataRows[] = [
                        $report->contractor ?? '',           // Column A: Beneficiary
                        $report->bank ?? '',                 // Column B: Bank
                        $report->branch ?? 'Abuja',          // Column C: Branch
                        $report->accountNo ?? '',            // Column D: Account Number
                        number_format($report->amount ?? 0, 2), // Column E: Amount (no ₦ sign)
                        (string)$tin,                        // Column F: FIRS TIN - Cast to string
                        $report->purpose ?? '',              // Column G: Purpose of Payment
                    ];

                    $stampDuty = ($report->VATValue ?? 0) * 0.005;

                    if (($report->WHTValue ?? 0) > 0) {
                        $dataRows[] = [
                            'FIRS TAX PROMAX WHT',           // Column A: Beneficiary
                            $report->wht_bank ?? 'Zenith Bank', // Column B: Bank
                            'Abuja',                         // Column C: Branch
                            $report->wht_accountNo ?? '1130089499', // Column D: Account Number
                            number_format($report->WHTValue ?? 0, 2), // Column E: Amount (no ₦ sign)
                            '-',                             // Column F: FIRS TIN
                            'WHT'                           // Column G: Purpose of Payment
                        ];
                    }

                    if (($report->VATValue ?? 0) > 0) {
                        $dataRows[] = [
                            'FIRS TAX PROMAX VAT',           // Column A: Beneficiary
                            $report->vat_bank ?? 'Zenith Bank', // Column B: Bank
                            'Abuja',                         // Column C: Branch
                            $report->vat_accountNo ?? '1130089499', // Column D: Account Number
                            number_format($report->VATValue ?? 0, 2), // Column E: Amount (no ₦ sign)
                            '-',                             // Column F: FIRS TIN
                            'VAT'                           // Column G: Purpose of Payment
                        ];
                    }

                    if ($stampDuty > 0) {
                        $dataRows[] = [
                            'FIRS TAX PROMAX SD',            // Column A: Beneficiary
                            $report->vat_bank ?? 'Zenith Bank', // Column B: Bank
                            'Abuja',                         // Column C: Branch
                            $report->vat_accountNo ?? '1130089499', // Column D: Account Number
                            number_format($stampDuty, 2),    // Column E: Amount (no ₦ sign)
                            '-',                             // Column F: FIRS TIN
                            'FIRS (STAMP DUTY)'             // Column G: Purpose of Payment
                        ];
                    }
                }

                if (!empty($dataRows)) {
                    $sheet->fromArray($dataRows, null, "A{$dataStartRow}");
                } else {
                    $sheet->setCellValue("A{$dataStartRow}", 'Data not available');
                    $sheet->mergeCells("A{$dataStartRow}:G{$dataStartRow}");
                }

                $lastDataRow = $dataStartRow + count($dataRows) - 1;
                if ($lastDataRow < 23) {
                    $lastDataRow = 23;
                }

                // Purpose wrap + top align (now column G instead of H)
                $sheet->getStyle("G{$dataStartRow}:G{$lastDataRow}")
                      ->getAlignment()
                      ->setWrapText(true)
                      ->setVertical(Alignment::VERTICAL_TOP);

                // Borders for data rows (A:G instead of A:H)
                $sheet->getStyle("A{$dataStartRow}:G{$lastDataRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // ─── RIGHT ALIGN COLUMN D (ACCOUNT NUMBER) AND COLUMN E (AMOUNT) ──
                // Apply right alignment to ALL account number cells in column D
                $sheet->getStyle("D{$dataStartRow}:D{$lastDataRow}")
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                      ->setVertical(Alignment::VERTICAL_CENTER);

                // Apply right alignment to ALL amount cells in column E
                $sheet->getStyle("E{$dataStartRow}:E{$lastDataRow}")
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                      ->setVertical(Alignment::VERTICAL_CENTER);

                // Amount formatting (now column E instead of F) - REMOVED ₦ SIGN
                $sheet->getStyle("E{$dataStartRow}:E{$lastDataRow}")
                      ->getNumberFormat()
                      ->setFormatCode('#,##0.00'); // Removed "₦" from format

                // ─── FORMAT FIRS TIN COLUMN AS TEXT TO PREVENT EXPONENTIAL NOTATION ──
                // Format column F (FIRS TIN) as text
                $sheet->getStyle("F{$dataStartRow}:F{$lastDataRow}")
                      ->getNumberFormat()
                      ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                
                // Set each cell individually to ensure text format
                for ($row = $dataStartRow; $row <= $lastDataRow; $row++) {
                    $cell = $sheet->getCell("F{$row}");
                    // Ensure the value is treated as string
                    if ($cell->getValue() !== null && $cell->getValue() !== '') {
                        $sheet->setCellValueExplicit("F{$row}", (string)$cell->getValue(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }

                // ─── FIXED TOTAL ROW AT ROW 14 ──────────────────────────────
                $totalRow = 14; // Row 14

                // Set "TOTAL:" at column D, row 14
                $sheet->setCellValue("D{$totalRow}", 'TOTAL:');
                
                // Set total amount at column E, row 14 - WITHOUT ₦ SIGN
                $sheet->setCellValue("E{$totalRow}", $total);

                // Style "TOTAL:" (D14) - green bg + white text, RIGHT ALIGNED
                $sheet->getStyle("D{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size'  => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF008000'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Amount (E14) - RIGHT ALIGNED, NO ₦ SIGN
                $sheet->getStyle("E{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'size'  => 11,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Apply number format to the total amount cell - WITHOUT ₦ SIGN
                $sheet->getStyle("E{$totalRow}")
                      ->getNumberFormat()
                      ->setFormatCode('#,##0.00'); // Removed "₦" from format

                // Borders ONLY on D14:E14
                $sheet->getStyle("D{$totalRow}:E{$totalRow}")->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'FF000000'],
                        ],
                        'inside' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // IMPORTANT: Remove all borders and fill from F14:G14 on total row
                $sheet->getStyle("F{$totalRow}:G{$totalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_NONE,
                    ],
                ]);

                // REDUCED TOTAL ROW HEIGHT
                $sheet->getRowDimension($totalRow)->setRowHeight(20);

                // Auto-size A-F (G fixed for purpose)
                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // No freeze panes + fit to page
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $usedRange = $sheet->calculateWorksheetDimension();
                $sheet->getPageSetup()->setPrintArea($usedRange);
            },
        ];
    }
}
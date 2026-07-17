<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AllBanksExport implements FromView, WithStyles, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('payroll.con_epayment.exportAllBanksExcel', $this->data);
    }

    /** ===========================
     *  HEADER STYLING
     * =========================== */
    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF008000'] // Green header
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ]
        ];
    }

    /** ===========================
     *  AFTER SHEET FORMATTING
     * =========================== */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                /* =====================================
                   COLUMN SIZING
                ===================================== */
                $sheet->getColumnDimension('A')->setWidth(8);  // S/N
                $sheet->getColumnDimension('B')->setWidth(32); // Beneficiary
                $sheet->getColumnDimension('C')->setWidth(23); // Bank
                $sheet->getColumnDimension('D')->setWidth(10); // Branch
                $sheet->getColumnDimension('E')->setWidth(22); // Account No
                $sheet->getColumnDimension('F')->setWidth(18); // Amount
                $sheet->getColumnDimension('G')->setWidth(38); // Purpose

                /* =====================================
                   MAKE AMOUNT COLUMN BOLD
                ===================================== */
                $sheet->getStyle("F2:F{$highestRow}")->getFont()->setBold(true);

                // The styling loop was removed to prevent Maximum Execution Time Exceeded errors.
                // The bold font styling for Sub Total and Grand Total is automatically handled
                // by the inline `style="font-weight:bold;"` in the exportAllBanksExcel.blade.php view.

                /* =====================================
                   ADD BORDER TO WHOLE TABLE
                ===================================== */
                $sheet->getStyle("A1:G{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000']
                        ]
                    ]
                ]);
            }
        ];
    }
}

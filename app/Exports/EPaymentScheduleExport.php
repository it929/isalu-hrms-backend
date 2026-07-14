<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EPaymentScheduleExport implements FromArray, WithDrawings, WithEvents, WithStyles
{
    public function __construct(private array $rows, private array $meta = []) {}

    public function array(): array
    {
        // We'll place header info in rows 1-6, then table starts at row 8
        // So we return empty header spacing + actual table
        $sheet = [];

        $sheet[] = ['']; // row 1 (logos will sit here)
        $sheet[] = ['ISALU HOSPITAL LIMITED']; // row 2
        $sheet[] = ['THREE ARM ZONE']; // row 3
        $sheet[] = ['ACCOUNT NUMBER: ' . ($this->meta['account_no'] ?? 'N/A')]; // row 4
        $sheet[] = [$this->meta['title'] ?? 'E-PAYMENT SCHEDULE']; // row 5
        $sheet[] = ['Ref No. ' . ($this->meta['ref_no'] ?? '')]; // row 6
        $sheet[] = ['']; // row 7 spacer

        // table rows begin row 8
        foreach ($this->rows as $r) {
            $sheet[] = $r;
        }

        return $sheet;
    }

    public function drawings()
    {
        $logo1 = new Drawing();
        $logo1->setName('SCN Logo');
        $logo1->setPath(public_path('Images/scn_logo.png'));
        $logo1->setHeight(45);
        $logo1->setCoordinates('A1');
        $logo1->setOffsetX(5);
        $logo1->setOffsetY(5);

        $logo2 = new Drawing();
        $logo2->setName('Coat');
        $logo2->setPath(public_path('Images/coat.png'));
        $logo2->setHeight(45);
        $logo2->setCoordinates('G1');
        $logo2->setOffsetX(5);
        $logo2->setOffsetY(5);

        return [$logo1, $logo2];
    }

    public function styles(Worksheet $sheet)
    {
        // Make header rows look nice
        $sheet->getRowDimension(1)->setRowHeight(40);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(5)->setRowHeight(18);
        $sheet->getRowDimension(6)->setRowHeight(18);

        // Center the header text across columns A-G
        foreach ([2, 3, 4, 5] as $row) {
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->getStyle("A{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}")
                ->getFont()
                ->setBold(true);
        }

        $sheet->mergeCells("A6:G6");
        $sheet->getStyle("A6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A6")->getFont()->setBold(true);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Table starts at row 8
                $startRow = 8;
                $endRow = $startRow + (count($this->rows) - 1);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(32);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(16);
                $sheet->getColumnDimension('G')->setWidth(28);

                // Header row styling (row 8)
                $headerRange = "A{$startRow}:G{$startRow}";
                $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF008000');

                // Borders for full table
                $tableRange = "A{$startRow}:G{$endRow}";
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Right align amount column
                $sheet->getStyle("F" . ($startRow + 1) . ":F{$endRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}

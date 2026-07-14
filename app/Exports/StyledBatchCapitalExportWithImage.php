<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class StyledBatchCapitalExport implements 
    FromCollection, 
    WithEvents, 
    ShouldAutoSize, 
    WithCustomStartCell,
    WithDrawings
{
    protected $transactionID;
    protected $total = 0;
    protected $accNo;
    protected $bankAddr;
    protected $date;

    public function __construct($transactionID, $accNo, $bankAddr, $date)
    {
        $this->transactionID = $transactionID;
        $this->accNo = $accNo;
        $this->bankAddr = $bankAddr;
        $this->date = $date;
    }

    public function startCell(): string
    {
        return 'A11';
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOS (LEFT & RIGHT)
    |--------------------------------------------------------------------------
    */
    public function drawings()
    {
        $logoLeft = new Drawing();
        $logoLeft->setName('SCN Logo');
        $logoLeft->setPath(public_path('Images/scn_logo.png'));
        $logoLeft->setHeight(80);
        $logoLeft->setCoordinates('A1');

        $logoRight = new Drawing();
        $logoRight->setName('Coat of Arms');
        $logoRight->setPath(public_path('Images/coat.png'));
        $logoRight->setHeight(80);
        $logoRight->setCoordinates('H1');

        return [$logoLeft, $logoRight];
    }

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */
    public function collection()
    {
        $data = DB::table('tblepayment')
            ->where('batch', $this->transactionID)
            ->get();

        $rows = collect();

        foreach ($data as $row) {

            $sn = $rows->count() + 1;

            // MAIN PAYMENT
            $this->total += $row->amount;

            $rows->push([
                $sn,
                $row->bank,
                $row->bank_sortcode,
                $row->contractor,
                $row->accountNo,
                $row->amount,
                "CR",
                $row->purpose
            ]);

            // VAT
            if (!empty($row->VATValue) && $row->VATValue > 0) {
                $sn++;
                $this->total += $row->VATValue;

                $rows->push([
                    $sn,
                    $row->vat_bank ?? 'ZENITH BANK PLC',
                    $row->vat_sortcode ?? '057',
                    "FIRSVAT",
                    $row->vat_accountNo ?? '1130089499',
                    $row->VATValue,
                    "CR",
                    "VAT"
                ]);
            }

            // WHT
            if (!empty($row->WHTValue) && $row->WHTValue > 0) {
                $sn++;
                $this->total += $row->WHTValue;

                $rows->push([
                    $sn,
                    $row->wht_bank ?? 'ZENITH BANK PLC',
                    $row->wht_sortcode ?? '057',
                    "FIRSWHT",
                    $row->wht_accountNo ?? '1130089499',
                    $row->WHTValue,
                    "CR",
                    "WHT"
                ]);
            }

            // STAMP DUTY
            if (!empty($row->stampduty) && $row->stampduty > 0) {
                $sn++;
                $this->total += $row->stampduty;

                $rows->push([
                    $sn,
                    'ZENITH BANK PLC',
                    '057',
                    "FIRS (STAMP DUTY)",
                    '1130089499',
                    $row->stampduty,
                    "CR",
                    "STAMP DUTY"
                ]);
            }
        }

        return $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | STYLING & HEADER
    |--------------------------------------------------------------------------
    */
    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | HEADER SECTION
                |--------------------------------------------------------------------------
                */

                $sheet->getRowDimension(1)->setRowHeight(70);

                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('A3:H3');
                $sheet->mergeCells('A4:H4');

                $sheet->setCellValue('A1', 'ISALU HOSPITAL LIMITED');
                $sheet->setCellValue('A2', 'Three Arms Zone');
                $sheet->setCellValue('A3', 'Account Number: ' . $this->accNo);
                $sheet->setCellValue('A4', 'E-Payment Schedule');

                $sheet->getStyle('A1:A4')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16]
                ]);

                $sheet->getStyle('A2:A4')->applyFromArray([
                    'font' => ['size' => 12]
                ]);

                /*
                |--------------------------------------------------------------------------
                | TABLE HEADER
                |--------------------------------------------------------------------------
                */

                $headerRow = 10;

                $sheet->fromArray(
                    [['S/N', 'Bank', 'Sort Code', 'Beneficiary', 'Account Number', 'Amount (₦)', 'CR/DR', 'Purpose']],
                    null,
                    'A' . $headerRow
                );

                $sheet->getStyle('A' . $headerRow . ':H' . $headerRow)
                    ->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '6AA84F']
                        ]
                    ]);

                /*
                |--------------------------------------------------------------------------
                | FORMAT ACCOUNT NUMBERS (COLUMN E AS TEXT)
                |--------------------------------------------------------------------------
                */

                $highestRow = $sheet->getHighestRow();

                for ($i = $headerRow + 1; $i <= $highestRow; $i++) {

                    $value = $sheet->getCell('E' . $i)->getValue();

                    $sheet->setCellValueExplicit(
                        'E' . $i,
                        $value,
                        DataType::TYPE_STRING
                    );

                    $sheet->getStyle('E' . $i)
                        ->getNumberFormat()
                        ->setFormatCode('@');
                }

                /*
                |--------------------------------------------------------------------------
                | FORMAT AMOUNT COLUMN (F)
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('F' . ($headerRow + 1) . ':F' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                /*
                |--------------------------------------------------------------------------
                | TOTAL ROW
                |--------------------------------------------------------------------------
                */

                $totalRow = $highestRow + 2;

                $sheet->setCellValue('E' . $totalRow, 'TOTAL');
                $sheet->setCellValue('F' . $totalRow, $this->total);
                $sheet->setCellValue('G' . $totalRow, 'DR');

                $sheet->getStyle('E' . $totalRow . ':G' . $totalRow)
                    ->applyFromArray([
                        'font' => ['bold' => true]
                    ]);

                $sheet->getStyle('F' . $totalRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }
        ];
    }
}
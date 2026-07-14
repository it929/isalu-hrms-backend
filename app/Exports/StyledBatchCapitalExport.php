<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class StyledBatchCapitalExport implements FromCollection, WithEvents, ShouldAutoSize, WithCustomStartCell
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

    public function collection()
    {
        $data = DB::table('tblepayment')
            ->where('batch', $this->transactionID)
            ->get();

        $rows = collect();

        foreach ($data as $key => $row) {

            $sn = $rows->count() + 1;

            // ================= MAIN PAYMENT =================
            $amount = $row->amount;
            $this->total += $amount;

            $rows->push([
                $sn,
                $row->bank,
                $row->bank_sortcode,
                $row->contractor,
                $row->accountNo,
                $amount,
                "CR",
                $row->purpose
            ]);

            // ================= VAT ROW =================
            if (!empty($row->VATValue) && $row->VATValue > 0) {

                $sn++;

                $rows->push([
                    $sn,
                    $row->vat_bank ?? ' ',
                    $row->vat_sortcode ?? '057',
                    "FIRSVAT",
                    $row->vat_accountNo ?? '1130089499',
                    $row->VATValue,
                    "CR",
                    "VAT"
                ]);

                $this->total += $row->VATValue;
            }

            // ================= WHT ROW =================
            if (!empty($row->WHTValue) && $row->WHTValue > 0) {

                $sn++;

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

                $this->total += $row->WHTValue;
            }

            // ================= STAMP DUTY ROW =================
            if (!empty($row->stampduty) && $row->stampduty > 0) {

                $sn++;

                $rows->push([
                    $sn,
                    'ZENITH BANK PLC',
                    '057',
                    "FIRS (STAMP DUTY)",
                    '1130089499',
                    $row->stampduty,
                    'CR',
                    'STAMP DUTY'
                ]);

                $this->total += $row->stampduty;
            }
        }

        $sn = $rows->count();

        return $rows;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // ================= HEADER SECTION =================

                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'ISALU HOSPITAL LIMITED');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '00CFCF']
                    ]
                ]);

                $sheet->setCellValue('A3', 'The Branch Manager');
                $sheet->setCellValue('A4', $this->bankAddr);
                $sheet->setCellValue('A5', 'Abuja');

                $sheet->mergeCells('A7:E7');
                $sheet->setCellValue(
                    'A7',
                    "Please credit the account(s) of the under-listed beneficiaries and debit our Account Number $this->accNo with the total sum of ₦" . number_format($this->total, 2) . " for the purpose stated below."
                );

                $sheet->setCellValue('F7', 'No:');
                $sheet->setCellValue('G7', $this->transactionID);

                $sheet->setCellValue('F8', 'Date:');
                $sheet->setCellValue('G8', $this->date);

                // ================= TABLE HEADER =================

                $headerRow = 10;

                $sheet->getDelegate()->fromArray(
                    [
                        ['S/N', 'Bank', 'Sort Code', 'Beneficiary',  'Account Number', 'Amount (₦)', 'CR/DR', 'Purpose of Payment']
                    ],
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

                // Move data below header
                // $event->sheet->getDelegate()->insertNewRowBefore($headerRow+1, 0);

                // ================= FORMAT ACCOUNT NUMBER AS TEXT =================

                $highestRow = $sheet->getHighestRow();

                for ($i = $headerRow + 1; $i <= $highestRow; $i++) {

                    $value = $sheet->getCell('E' . $i)->getValue();

                    // Force as explicit string
                    $sheet->setCellValueExplicit(
                        'E' . $i,
                        $value,
                        DataType::TYPE_STRING
                    );

                    // Set format as Text
                    $sheet->getStyle('E' . $i)
                        ->getNumberFormat()
                        ->setFormatCode('@');
                }

                // ================= CURRENCY FORMAT =================

                $sheet->getStyle('G' . ($headerRow + 1) . ':G' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // ================= TOTAL ROW =================

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

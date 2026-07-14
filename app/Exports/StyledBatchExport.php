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

class StyledBatchExport implements FromCollection, WithEvents, ShouldAutoSize, WithCustomStartCell
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

        $vatTotal = 0;
        $whtTotal = 0;
        $stampTotal = 0;

        foreach ($data as $key => $row) {

            // Main beneficiary amount (NO VAT/WHT added here)
            $amount = $row->amount;
            $this->total += $amount;

            $rows->push([
                $key + 1,
                $row->fileNo,
                $row->contractor,
                $row->accountName,
                $row->bank,
                $row->bank_branch,
                $row->accountNo,
                $amount,
                $row->purpose
            ]);

            // Accumulate VAT
            if (!empty($row->VATValue) && $row->VATValue > 0) {
                $vatTotal += $row->VATValue;
            }

            // Accumulate WHT
            if (!empty($row->WHTValue) && $row->WHTValue > 0) {
                $whtTotal += $row->WHTValue;
            }

            // Accumulate Stamp Duty
            if (!empty($row->stampduty) && $row->stampduty > 0) {
                $stampTotal += $row->stampduty;
            }
        }

        $sn = $rows->count();

        // ================= ADD VAT ROW =================
        if ($vatTotal > 0) {
            $rows->push([
                ++$sn,
                '-',
                'FIRS',
                $row->vat_payee ?? 'FIRSVAT',
                $row->vat_bank ?? 'ZENITH BANK PLC',
                'ABUJA',
                $row->vat_accountNo ?? '1130089499',
                $vatTotal,
                'VAT'
            ]);

            $this->total += $vatTotal;
        }

        // ================= ADD WHT ROW =================
        if ($whtTotal > 0) {
            $rows->push([
                ++$sn,
                '-',
                'FIRS',
                $row->wht_payee ?? 'FIRSWHT',
                $row->wht_bank ?? 'ZENITH BANK PLC',
                'ABUJA',
                $row->wht_accountNo ?? '1130089499',
                $whtTotal,
                'WHT'
            ]);

            $this->total += $whtTotal;
        }

        // ================= ADD STAMP DUTY ROW =================
        if ($stampTotal > 0) {
            $rows->push([
                ++$sn,
                '-',
                'FIRS',
                'FIRS (STAMP DUTY)',
                'ZENITH BANK PLC',
                'ABUJA',
                '1130089499',
                $stampTotal,
                'FIRS (STAMP DUTY)'
            ]);

            $this->total += $stampTotal;
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // ================= HEADER SECTION =================

                $sheet->mergeCells('A1:I1');
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
                        ['S/N', 'FileNo', 'Beneficiary', 'Account Name', 'Bank', 'Branch', 'Account Number', 'Amount (₦)', 'Purpose of Payment']
                    ],
                    null,
                    'A' . $headerRow
                );

                $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)
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
                    $sheet->setCellValueExplicit(
                        'G' . $i,
                        $sheet->getCell('G' . $i)->getValue(),
                        DataType::TYPE_STRING
                    );
                }

                // ================= CURRENCY FORMAT =================

                $sheet->getStyle('H' . ($headerRow + 1) . ':H' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // ================= TOTAL ROW =================

                $totalRow = $highestRow + 2;

                $sheet->setCellValue('G' . $totalRow, 'TOTAL');
                $sheet->setCellValue('H' . $totalRow, $this->total);

                $sheet->getStyle('G' . $totalRow . ':H' . $totalRow)
                    ->applyFromArray([
                        'font' => ['bold' => true]
                    ]);
            }
        ];
    }
}

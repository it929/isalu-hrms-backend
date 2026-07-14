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

class ConsolidatedMandateSheet implements
    FromCollection,
    WithEvents,
    ShouldAutoSize,
    WithCustomStartCell
{
    protected $ids;
    protected $total = 0;
    protected $accNo;
    protected $bankName;
    protected $date;

    public function __construct($ids)
    {
        $this->ids = $ids;
        $this->date = date('d/m/Y');

        // Fetch bank + account details using first transaction
        $accDetails = DB::table('tblepayment')
            ->join('tblmandate_address_account', 'tblmandate_address_account.id', '=', 'tblepayment.NJCAccount')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            // ->where('tblepayment.transactionID', '=', $ids[0])
            ->where('tblepayment.batch', '=', $ids[0])
            ->select('tblmandate_address_account.account_no', 'tblbanklist.bank')
            ->first();

        $this->accNo = $accDetails->account_no ?? '';
        $this->bankName = $accDetails->bank ?? '';
    }

    public function startCell(): string
    {
        return 'A11';
    }

    public function collection()
    {
        $data = DB::table('tblepayment')
            // ->whereIn('transactionID', $this->ids)
            ->whereIn('batch', $this->ids)
            ->orderBy('batch')
            ->get();

        $rows = collect();
        $sn = 1;

        foreach ($data as $row) {

            // ================= MAIN CONTRACTOR ROW =================
            $amount = $row->amount;
            $this->total += $amount;

            $rows->push([
                $sn++,
                $row->fileNo ?? "-",
                $row->contractor,
                $row->accountName,
                $row->bank,
                $row->bank_branch,
                $row->accountNo,
                $amount,
                $row->purpose
            ]);

            // ================= VAT ROW (UNDER CONTRACTOR) =================
            if (!empty($row->VATValue) && $row->VATValue > 0) {

                $this->total += $row->VATValue;

                $rows->push([
                    $sn++,
                    '-',
                    'FIRS TAX PROMAX VAT',
                    $row->vat_payee ?? 'FIRSVAT',
                    $row->vat_bank ?? 'ZENITH BANK PLC',
                    'ABUJA',
                    $row->vat_accountNo ?? '1130089499',
                    $row->VATValue,
                    'VAT'
                ]);
            }

            // ================= WHT ROW (UNDER CONTRACTOR) =================
            if (!empty($row->WHTValue) && $row->WHTValue > 0) {

                $this->total += $row->WHTValue;

                $rows->push([
                    $sn++,
                    '-',
                    'FIRS TAX PROMAX WHT',
                    $row->wht_payee ?? 'FIRSWHT',
                    $row->wht_bank ?? 'ZENITH BANK PLC',
                    'ABUJA',
                    $row->wht_accountNo ?? '1130089499',
                    $row->WHTValue,
                    'WHT'
                ]);
            }

            // ================= STAMP DUTY ROW (UNDER CONTRACTOR) =================
            if (!empty($row->stampduty) && $row->stampduty > 0) {

                $this->total += $row->stampduty;

                $rows->push([
                    $sn++,
                    '-',
                    'FIRS TAX PROMAX STAMP DUTY',
                    'FIRS (STAMP DUTY)',
                    'ZENITH BANK PLC',
                    'ABUJA',
                    '1130089499',
                    $row->stampduty,
                    'Stamp Duty'
                ]);
            }
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // ================= HEADER =================

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
                $sheet->setCellValue('A4', $this->bankName);
                $sheet->setCellValue('A5', 'Abuja');

                $sheet->mergeCells('A7:F7');
                $sheet->setCellValue(
                    'A7',
                    "Please debit our Account Number {$this->accNo} and credit the under-listed beneficiaries with the total sum of ₦" . number_format($this->total, 2) . " for the purpose stated below."
                );

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
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF']
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '6AA84F']
                        ]
                    ]);

                // ================= FREEZE HEADER =================

                $sheet->freezePane('A11');

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

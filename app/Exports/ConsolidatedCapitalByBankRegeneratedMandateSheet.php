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

class ConsolidatedCapitalByBankRegeneratedMandateSheet implements
    FromCollection,
    WithEvents,
    ShouldAutoSize,
    WithCustomStartCell,
    WithDrawings
{
    protected $ids;
    protected $totalAmount = 0;
    protected $totalItems = 0;
    protected $accNo;
    protected $bankName;
    protected $date;
    protected $totalRow;

    public function __construct($ids)
    {
        $this->ids = $ids;
        $this->date = date('d/m/Y');

        // Fetch bank + account details using first transaction
        $accDetails = DB::table('tblepayment_bank_paid')
            ->join('tblmandate_address_account', 'tblmandate_address_account.id', '=', 'tblepayment_bank_paid.NJCAccount')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblepayment_bank_paid.batch', '=', $ids)
            ->select('tblmandate_address_account.account_no', 'tblbanklist.bank')
            ->first();

        $this->accNo = $accDetails->account_no ?? '';
        $this->bankName = $accDetails->bank ?? '';
    }

    public function startCell(): string
    {
        return 'A12';
    }

    public function collection()
    {

        $data = DB::table('tblepayment_bank_paid')
            ->whereIn('batch', $this->ids)
            ->get();

        $summary = [];

        foreach ($data as $row) {

            // ================= CONTRACTOR =================
            if ($row->amount > 0) {

                $bank = $row->bank;

                if (!isset($summary[$bank])) {
                    $summary[$bank] = [
                        'bank' => $bank,
                        'items' => 0,
                        'amount' => 0
                    ];
                }

                $summary[$bank]['items'] += 1;
                $summary[$bank]['amount'] += $row->amount;
            }

        }

        $rows = collect();
        $sn = 1;

        foreach ($summary as $bank => $values) {

            $rows->push([
                $sn++,
                $bank,
                $values['items'],
                $values['amount'],
                'Contractor/Taxes'
            ]);

            $this->totalItems += $values['items'];
            $this->totalAmount += $values['amount'];
        }

        return $rows;
    }


    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // ================= ROW HEIGHT =================
                $sheet->getRowDimension('1')->setRowHeight(50);
                $sheet->getRowDimension('2')->setRowHeight(35);
                $sheet->getRowDimension('3')->setRowHeight(35);

                // ================= TITLE =================

                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'ISALU HOSPITAL LIMITED');

                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', 'Three Arms Zone, Abuja');

                $sheet->mergeCells('A3:G3');
                $sheet->setCellValue('A3', 'Account Number: ' . $this->accNo);

                $sheet->mergeCells('A4:G4');
                $sheet->setCellValue('A4', 'E-Payment Mandate');

                $sheet->getStyle('A1:G4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);

                $sheet->setCellValue('A6', 'The Branch Controller');
                $sheet->setCellValue('A7', 'Central Bank of Nigeria');
                $sheet->setCellValue('A8', 'Garki, Abuja');

                $sheet->setCellValue('F6', 'Reference No:');
                $sheet->setCellValue('G6', '-');

                $sheet->setCellValue('F7', 'Code No:');
                $sheet->setCellValue('G7', '-');

                $sheet->setCellValue('F8', 'Date:');
                $sheet->setCellValue('G8', $this->date);

                $sheet->getStyle('F6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '6AA84F']
                    ]
                ]);

                $sheet->getStyle('F7')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '6AA84F']
                    ]
                ]);

                $sheet->getStyle('F8')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '6AA84F']
                    ]
                ]);

                $sheet->mergeCells('A10:F10');
                $sheet->setCellValue(
                    'A10',
                    "Please credit the account(s) of the under-listed beneficiaries and debit our Account Number above with the total sum of ₦" . number_format($this->totalAmount, 2) . " (" . $this->amountInWords($this->totalAmount) . ")"
                );

                $sheet->getStyle('A10')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

                // ================= HEADER =================

                $sheet->fromArray(
                    [['S/N', 'Bank', 'Items', 'Amount (₦)', 'Purpose']],
                    null,
                    'A11'
                );

                $sheet->getStyle('A11:G11')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '6AA84F']
                    ]
                ]);


                // ================= FORMAT AMOUNT =================

                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('D5:D' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                // ================= TOTAL =================

                $this->totalRow = $highestRow + 1;

                $sheet->setCellValue('B' . $this->totalRow, 'TOTAL');
                $sheet->setCellValue('C' . $this->totalRow, $this->totalItems);
                $sheet->setCellValue('D' . $this->totalRow, $this->totalAmount);

                $sheet->getStyle('B' . $this->totalRow . ':D' . $this->totalRow)->applyFromArray([
                    'font' => ['bold' => true]
                ]);

                $sheet->getStyle('D' . $this->totalRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $signatureRow = $this->totalRow + 2;

                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('CBN Authorised Signatory');
                $drawing->setDescription('Signature');
                $drawing->setPath(public_path('Images/cbnAuthorisedSignatory.png'));
                $drawing->setHeight(280);
                $drawing->setCoordinates('A' . $signatureRow);
                $drawing->setWorksheet($sheet);

            }
        ];
    }


    // ================= CONVERT AMOUNT TO WORDS =================

    private function amountInWords($amount)
    {
        // Separate Naira and Kobo
        $parts = explode('.', (string)$amount);
        $naira = (int)$parts[0];
        $kobo = isset($parts[1]) ? (int)substr($parts[1] . '0', 0, 2) : 0;

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $scales = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];

        $convertToWords = function ($num) use ($ones, $teens, $tens, $scales) {
            if ($num == 0) {
                return 'Zero';
            }

            $words = '';
            $scaleIndex = 0;

            while ($num > 0) {
                $chunk = $num % 1000;

                if ($chunk > 0) {
                    $chunkWords = '';

                    // Hundreds
                    $hundreds = intdiv($chunk, 100);
                    if ($hundreds > 0) {
                        $chunkWords .= $ones[$hundreds] . ' Hundred ';
                    }

                    // Tens and ones
                    $remainder = $chunk % 100;
                    if ($remainder >= 20) {
                        $chunkWords .= $tens[intdiv($remainder, 10)];
                        if ($remainder % 10 > 0) {
                            $chunkWords .= ' ' . $ones[$remainder % 10];
                        }
                    } elseif ($remainder >= 10) {
                        $chunkWords .= $teens[$remainder - 10];
                    } elseif ($remainder > 0) {
                        $chunkWords .= $ones[$remainder];
                    }

                    if ($scaleIndex > 0) {
                        $chunkWords .= ' ' . $scales[$scaleIndex];
                    }

                    $words = trim($chunkWords) . ' ' . $words;
                }

                $num = intdiv($num, 1000);
                $scaleIndex++;
            }

            return trim($words);
        };

        $result = $convertToWords($naira) . ' Naira';

        if ($kobo > 0) {
            $result .= ' and ' . $convertToWords($kobo) . ' Kobo';
        }

        return $result . ' only';
    }

    // ================= IMAGES =================

    public function drawings()
    {

        $logoLeft = new Drawing();
        $logoLeft->setName('SCN Logo');
        $logoLeft->setDescription('SCN Logo');
        $logoLeft->setPath(public_path('Images/scn_logo.png'));
        $logoLeft->setHeight(70);
        $logoLeft->setCoordinates('A1');

        $logoRight = new Drawing();
        $logoRight->setName('Coat');
        $logoRight->setDescription('Coat of Arms');
        $logoRight->setPath(public_path('Images/coat.png'));
        $logoRight->setHeight(70);
        $logoRight->setCoordinates('G1');

        // $signature = new Drawing();
        // $signature->setName('CBN Authorised Signatory');
        // $signature->setDescription('Signature');
        // $signature->setPath(public_path('Images/cbnAuthorisedSignatory.png'));
        // $signature->setHeight(270);
        // $row = $this->totalRow + 2;
        // $signature->setCoordinates('A' . $row);

        return [$logoLeft, $logoRight];
    }
}
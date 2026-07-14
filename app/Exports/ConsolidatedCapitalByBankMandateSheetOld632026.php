<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ConsolidatedCapitalByBankMandateSheet implements
    FromCollection,
    WithEvents,
    ShouldAutoSize,
    WithCustomStartCell
{
    protected $ids;
    protected $totalAmount = 0;
    protected $totalItems = 0;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function collection()
    {
        $data = DB::table('tblepayment')
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

            // ================= VAT =================
            if (!empty($row->VATValue) && $row->VATValue > 0) {

                $bank = $row->vat_bank ?? 'UNKNOWN';

                if (!isset($summary[$bank])) {
                    $summary[$bank] = [
                        'bank' => $bank,
                        'items' => 0,
                        'amount' => 0
                    ];
                }

                $summary[$bank]['items'] += 1;
                $summary[$bank]['amount'] += $row->VATValue;
            }

            // ================= WHT =================
            if (!empty($row->WHTValue) && $row->WHTValue > 0) {

                $bank = $row->wht_bank ?? 'UNKNOWN';

                if (!isset($summary[$bank])) {
                    $summary[$bank] = [
                        'bank' => $bank,
                        'items' => 0,
                        'amount' => 0
                    ];
                }

                $summary[$bank]['items'] += 1;
                $summary[$bank]['amount'] += $row->WHTValue;
            }

            // ================= STAMP DUTY =================
            if (!empty($row->stampduty) && $row->stampduty > 0) {

                $bank = 'UBA PLC'; // stamp duty bank fixed

                if (!isset($summary[$bank])) {
                    $summary[$bank] = [
                        'bank' => $bank,
                        'items' => 0,
                        'amount' => 0
                    ];
                }

                $summary[$bank]['items'] += 1;
                $summary[$bank]['amount'] += $row->stampduty;
            }
        }

        $rows = collect();

        foreach ($summary as $bank => $values) {

            $rows->push([
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

                // ================= TITLE =================
                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A1', 'ISALU HOSPITAL LIMITED');
                $sheet->mergeCells('A2:D2');
                $sheet->setCellValue('A2', 'Three Arms Zone, Abuja');
                $sheet->mergeCells('A3:D3');
                $sheet->setCellValue('A3', 'E-Payment Mandate');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '00CFCF']
                    ]
                ]);

                // ================= HEADER =================
                $sheet->fromArray(
                    [['Bank', 'Items', 'Amount (₦)', 'Purpose']],
                    null,
                    'A4'
                );

                $sheet->getStyle('A4:D4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '6AA84F']
                    ]
                ]);

                // ================= FORMAT CURRENCY =================
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('C5:C' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // ================= TOTAL ROW =================
                $totalRow = $highestRow + 2;

                $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                $sheet->setCellValue('B' . $totalRow, $this->totalItems);
                $sheet->setCellValue('C' . $totalRow, $this->totalAmount);

                $sheet->getStyle('A' . $totalRow . ':C' . $totalRow)
                    ->applyFromArray([
                        'font' => ['bold' => true]
                    ]);

                $sheet->getStyle('C' . $totalRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }
        ];
    }
}

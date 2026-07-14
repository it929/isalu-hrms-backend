<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use DB;

class NHFReportExport implements FromCollection, WithHeadings, WithTitle
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $data = DB::table('tblpayment_consolidated')
            ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->select(
                'tblper.surname',
                'tblper.first_name', 
                'tblper.othernames',
                'tblper.fileNo',
                'tblper.nhfNo',
                'tblbanklist.bank as bank_name',
                'tblper.AccNo',
                'tblper.phone',
                'tblper.email',
                'tblpayment_consolidated.Bs as basic_salary',
                'tblpayment_consolidated.NHF as amount'
            )
            ->where('tblpayment_consolidated.month', '=', $this->month)
            ->where('tblpayment_consolidated.year', '=', $this->year)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->where('tblpayment_consolidated.NHF', '!=', 0)
            ->orderBy('tblpayment_consolidated.NHF', 'DESC')
            ->get();

        // Add serial numbers
        $data = $data->map(function ($item, $key) {
            $item->sn = $key + 1;
            $item->organization = 'ISALU HOSPITAL LIMITED';
            $item->remark = $this->month . ' ' . $this->year . ' NHF Contribution';
            return $item;
        });

        return $data;
    }

    public function headings(): array
    {
        return [
            'S/N',
            'NAME',
            'FILE NO.',
            'ORGANIZATION',
            'NHF NO.',
            'BANK',
            'ACCOUNT NO.',
            'MOBILE NUMBER',
            'EMAIL ADDRESS',
            'BASIC SALARY',
            'AMOUNT',
            'REMARK'
        ];
    }

    public function title(): string
    {
        return 'NHF Report';
    }
}
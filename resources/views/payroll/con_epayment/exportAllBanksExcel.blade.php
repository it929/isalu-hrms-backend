<table>
    <thead>
        <tr>
            <th>S/N</th>
            <th style="width: 220px">BENEFICIARY</th>
            <th style="width: 220px">BANK</th>
            <th>BRANCH</th>
            <th style="width: 200px">ACC NUMBER</th>
            <th style="width: 200px">AMOUNT (₦)</th>
            <th style="width: 220px">PURPOSE OF PAYMENT</th>
        </tr>
    </thead>

    <tbody>
        @php
            $counter = 1;
            $sum = 0;
            $salaryByBank = $epayment_detail->groupBy('bank');
            $deductionByBank = $staffDeductionElement->groupBy('bank_name');
        @endphp

        @foreach ($salaryByBank as $bank => $salaries)
            @php $subTotal = 0; @endphp

            {{-- Staff Salaries --}}
            @foreach ($salaries as $report)
                @php
                    $subTotal += $report->NetPay;
                    $sum += $report->NetPay;
                @endphp

                <tr>
                    <td>{{ $counter }}</td>
                    <td>{{ $report->name }}</td>
                    <td>{{ $report->bank }}</td>
                    <td>{{ $report->bank_branch }}</td>
                    <td> {{ $report->AccNo }}</td>
                    <td>{{ $report->NetPay }}</td>
                    @if ($report->rank == 2)
                        <td>{{ session('month') }} {{ session('year') }} Justice Allowance</td>
                    @else
                        <td>{{ session('month') }} {{ session('year') }} Staff Salary</td>
                    @endif

                </tr>

                @php $counter++; @endphp
            @endforeach

            {{-- Deductions --}}
            @if (isset($deductionByBank[$bank]))
                @foreach ($deductionByBank[$bank] as $deduction)
                    @php
                        $subTotal += $deduction->totalDeduction;
                        $sum += $deduction->totalDeduction;
                    @endphp

                    <tr>
                        <td>{{ $counter }}</td>
                        @if ($deduction->beneficiary_name == 'OVERPAYMENT')
                           <td>CHIEF REGISTRAR</td>
                        @else
                            <td>{{ $deduction->beneficiary_name }}</td>
                        @endif

                        <td>{{ $deduction->bank_name }}</td>
                        <td>ABJ</td>
                        <td> {{ $deduction->account_number }}</td>
                        <td>{{ $deduction->totalDeduction }}</td>

                        <td style="white-space: normal; word-wrap: break-word;">
                            @if (in_array(strtoupper($deduction->beneficiary_name), ['NASARAWA STATE TAX', 'NIGER STATE TAX', 'UNION DUES']))
                                {{ $deduction->purpose }}
                            @else

                                {{ session('month') }} {{ session('year') }} PAYROLL DEDUCTION FOR
                                {{ strtoupper($deduction->beneficiary_name) }}
                            @endif
                        </td>
                    </tr>

                    @php $counter++; @endphp
                @endforeach
            @endif

            {{-- Sub Total (MUST HAVE 7 COLUMNS) --}}
            <tr style="font-weight:bold;">
                <td></td> {{-- S/N empty to maintain structure --}}
                <td colspan="4">Sub Total ({{ $bank }})</td>
                <td> {{ number_format($subTotal, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        {{-- Grand Total (MUST HAVE 7 COLUMNS) --}}
        <tr style="font-weight:bold;">
            <td></td> {{-- S/N empty --}}
            <td colspan="4">Total</td>
            <td> {{ number_format($sum, 2) }}</td>
            <td></td>
        </tr>

    </tbody>
</table>

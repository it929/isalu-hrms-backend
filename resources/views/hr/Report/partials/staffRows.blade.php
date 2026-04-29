@php $serial = 1; @endphp

@foreach ($staff as $b)
<tr style="{{ $b->staff_status == 0 ? 'background-color: red; color: white;' : '' }}">

    <td>{{ $serial++ }}</td>

    <td>
        {{ $b->title }} {{ $b->surname }} {{ $b->othernames }} {{ $b->first_name }}
    </td>

    <td>{{ $b->dob ? date('d-M-Y', strtotime($b->dob)) : 'N/A' }}</td>
    <td>{{ $b->gender }}</td>
    <td>{{ $b->maritalstatus }}</td>
    <td>{{ $b->lga }}</td>
    <td>{{ $b->State }}</td>
    <td>{{ $b->doj ? date('d-M-Y', strtotime($b->doj)) : 'N/A' }}</td>
    <td>{{ $b->designation }}</td>

    @if ($b->progress_regID < 18)
        <td>
            <a class="btn btn-primary btn-sm" href="/continue-staff-documentation/{{ $b->ID }}">
                Documentation
            </a>
        </td>
    @else
        <td>
            <a class="btn btn-success btn-sm" href="javascript: LoadSummary('{{ $b->ID }}')">
                Staff Record
            </a>
        </td>
    @endif

</tr>
@endforeach

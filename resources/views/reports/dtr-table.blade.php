@php
    $dataset = isset($adapter) ? $adapter['dataset'] : [];
@endphp

<table>
    <!-- Your first table content goes here -->
    <thead>
        <tr>
            <th rowspan="2" class="th-day">Day</th>
            <th colspan="2">A.M.</th>
            <th colspan="2">P.M.</th>
            <th colspan="2">Undertime</th>
        </tr>
        <tr class="th-normal">
            <th class="th-time">In</th>
            <th class="th-time">Out</th>
            <th class="th-time">In</th>
            <th class="th-time">Out</th>
            <th>Hrs</th>
            <th>Min</th>
        </tr>
    </thead>
    <tbody>
        {{-- @php
        dump($dataset)
        @endphp --}}
        @foreach ($dataset as $row)
        <tr>
            @if ($row->am_in == 'Sat' || $row->am_in == 'Sun')
            <td class="text-danger">{{ $row->day_number }}</td>
            <td class="text-danger">{{ $row->am_in }}</td>

            @elseif ($row->am_in == $statLeave)
            <td>{{ $row->day_number }}</td>
            <td class="text-primary">{{ $row->am_in }}</td>

            @else
            <td>{{ $row->day_number }}</td>
            <td>{{ $row->am_in }}</td>
            @endif

            <td>{{ $row->am_out }}</td>
            <td>{{ $row->pm_in }}</td>
            <td>{{ $row->pm_out }}</td>
            <td>{{ $row->undertime_hours }}</td>
            <td>{{ $row->undertime_minutes }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <th colspan="5">Total</th>
        <th colspan="2">{{ $adapter['undertime'] }}</th>
    </tfoot>
</table>
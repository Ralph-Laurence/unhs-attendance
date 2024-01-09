@php
    $start = new DateTime('2024-01-01');
    $end = new DateTime(); // Get the current date
    $interval = new DateInterval('P1W');
    $dateRange = new DatePeriod($start, $interval, $end);
    $weeks = iterator_to_array($dateRange);
    $weeks = array_reverse($weeks); // Reverse the array to make the latest week the first
@endphp

<select name="week">
    @foreach($weeks as $date)
        @php
            $weekNumber = $date->format("W");
            $weekStart = $date->format("M j");
            $weekEnd = date("M j", strtotime("{$weekStart} +6 days"));
        @endphp
        <option value="{{ $weekNumber }}" {{ $loop->first ? 'selected' : '' }}>Week {{ $weekNumber }}: {{ $weekStart }} - {{ $weekEnd }}</option>
    @endforeach
</select>
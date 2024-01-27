@extends('layouts.base')

@section('content')

<x-moment-picker as="input-date-picker" />
@push('scripts')
<script src="{{ asset('js/lib/momentjs/moment-with-locales.js') }}"></script>
<script>
// function to_date_picker(selector) 
// {
//     var date            = moment();
//     var months          = moment.months();
//     var years           = Array.from({length: 6}, (_, i) => date.year() + i);
//     var $input          = $(selector);
//     var $picker         = $('<div>').addClass('date-picker');
//     var $monthSelect    = $('<select>').addClass('month-select');
//     var $yearSelect     = $('<select>').addClass('year-select');
//     var $table          = $('<table>').addClass('day-table');
//     var $thead          = $('<thead>');
//     var $tbody          = $('<tbody>');

//     months.forEach(function(month) {
//         $('<option>').text(month).appendTo($monthSelect);
//     });

//     years.forEach(function(year) {
//         $('<option>').text(year).appendTo($yearSelect);
//     });

//     'Sun Mon Tue Wed Thu Fri Sat'.split(' ').forEach(function(day) {
//         $('<th>').text(day).appendTo($thead);
//     });

//     $picker.append($monthSelect, $yearSelect, $table.append($thead, $tbody));
//     $input.after($picker);

//     function updateTable() 
//     {
//         $tbody.empty();
//         var month       = $monthSelect.prop('selectedIndex');
//         var year        = parseInt($yearSelect.val(), 10);
//         var firstDay    = moment([year, month]);
//         var lastDay     = moment(firstDay).endOf('month');
//         var day         = moment(firstDay).startOf('week');

//         while (day <= lastDay || day.day() !== 0) 
//         {
//             var $tr = $('<tr>');

//             for (var i = 0; i < 7; i++) 
//             {
//                 var $td = $('<td>').text(day.date());

//                 if (day.month() !== month)
//                     $td.addClass('disabled');
                
//                 $tr.append($td);
//                 day.add(1, 'day');
//             }

//             $tbody.append($tr);
//         }
//     }

//     $monthSelect.add($yearSelect).on('change', updateTable);
//     updateTable();
// }

// $(document).ready(function () {
//     to_date_picker("#input-date-picker");
// });

$(document).ready(function () {
    to_date_picker("#input-date-picker");
});
</script>
@endpush

@endsection
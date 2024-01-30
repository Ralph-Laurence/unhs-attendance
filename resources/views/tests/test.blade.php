@extends('layouts.base')

@section('content')
@php
    $leaveTypes = [];
@endphp
<x-moment-picker as="input-date-start" />
<x-moment-picker as="input-date-end" />
<x-drop-list :items="$leaveTypes" button-classes="w-100"/>
@push('scripts')
<script src="{{ asset('js/lib/momentjs/moment-with-locales.js') }}"></script>
<script>
$(document).ready(function () {
    to_date_picker("#input-date-start");
    to_date_picker("#input-date-end");
});
</script>
@endpush

@endsection
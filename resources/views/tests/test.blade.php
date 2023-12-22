@extends('layouts.base')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/scanner-overrides.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/main/scanner-page.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/effects/slide-text.css') }}"/>
@endpush

@section('content')
<div class="d-flex">
    <div id="small">test</div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/lib/momentjs/moment-with-locales.js') }}"></script>
    <script src="{{ asset('js/effects/slide-text.js') }}"></script>
    <script>
        $(() => {
            var slideText = new SlideText('#small');
            var date = moment().format('MMM. D, YYYY');
            var day  = moment().format('dddd');
            slideText.items = [date, day];
            slideText.slideDelay = 2000;
            slideText.start();
        });
    </script>
@endpush
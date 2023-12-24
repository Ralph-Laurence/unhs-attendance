@extends('layouts.base')
@section('content')
    <img src="{{ $qrcode }}" alt="" srcset=""/>
    <h6>{{ $decode }}</h6>
@endsection
@extends('layouts.portal-base')

@section('title')
{{'Attendances'}}
@endsection

@section('content')

<div class="card content-card">
    <div class="card-body">

        {{-- TABLE TITLE HEADER --}}
        <div class="d-flex align-items-center gap-1">
            <h6 class="card-title me-auto">
                <span>My Attendances</span>
                {{-- <i class="fas fa-caret-right mx-2 opacity-60"></i>
                <span class="opacity-90 lbl-attendance-range text-14 text-primary-dark"></span>
                <i class="fas fa-caret-right mx-2 opacity-60"></i>
                <span class="opacity-90 lbl-employee-filter text-14 text-primary-dark"></span> --}}
            </h6>

            {{-- RECORD DATE RANGE FILTERS --}}
            @include('components.record-range-filters')


        </div>

        <div class="page-length-controls">
            <x-table-length-pager as="table-page-len"/>
        </div>
        
        {{-- DATASET TABLE --}}
        <table class="table table-striped table-sm table-hover dataset-table"
            data-src-default="{{-- $routes['ajax_get_all'] --}}">
            <thead class="user-select-none">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Am In</th>
                    <th>Am Out</th>
                    <th>Pm In</th>
                    <th>Pm Out</th>
                </tr>
            </thead>
            <tbody>{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
        </table>
    </div>
</div>

@endsection
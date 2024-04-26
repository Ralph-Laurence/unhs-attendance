@extends('layouts.portal-base')

@section('title')
{{'Attendances'}}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('js/lib/datatables/Responsive-2.5.0/css/responsive.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/main/portal/employee-attendance.css') }}">
@endpush

@section('content')

<div class="card content-card mx-lg-4">
    <div class="card-body">

        {{-- TABLE TITLE HEADER --}}
        <div class="d-flex align-items-center gap-1">
            <h6 class="card-title me-auto">
                <span>My Attendances</span>
                <i class="fas fa-caret-right mx-2 opacity-60"></i>
                <span class="opacity-90 lbl-attendance-range text-14 text-primary-dark"></span>
            </h6>

            <x-drop-list as="input-month-filter" text="{{ date('F') }}" default="{{ date('n') }}"
                :items="$monthFilters" />
        </div>

        <div class="page-length-controls">
            <x-table-length-pager as="table-page-len" />
        </div>

        @push('styles')
        <style>
            @media only screen and (min-width: 800px) {

                #dataset-table {
                    table-layout: fixed;
                }
            }
        </style>
        @endpush
        {{-- DATASET TABLE --}}
        <div class="w-100 position-relative overflow-hidden">
            <table class="table table-striped table-sm table-hover display dataset-table nowrap" width="100%"
                data-src="{{ $routes['getAttendances'] }}" id="dataset-table">
                <thead class="user-select-none">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>AM In</th>
                        <th>AM Out</th>
                        <th>PM In</th>
                        <th>PM Out</th>
                        <th>Remarks</th>
                        {{-- <th style="min-width: 10%;">#</th>
                        <th style="min-width: 15%">Date</th>
                        <th style="min-width: 10%">AM In</th>
                        <th style="min-width: 10%">AM Out</th>
                        <th style="min-width: 10%">PM In</th>
                        <th style="min-width: 10%">PM Out</th>
                        <th style="min-width: 15%">Remarks</th> --}}
                        <th>Duration</th>
                        <th>Late</th>
                        <th>Undertime</th>
                        <th>Overtime</th>
                    </tr>
                </thead>
                <tbody class="overflow-x-scroll">{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
            </table>
        </div>
    </div>
</div>

@endsection

<x-gijgo-driver type="date" />

@push('scripts')
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/lib/datatables/Responsive-2.5.0/js/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
<script src="{{ asset('js/main/portal/employee-attendance.js') }}"></script>
@endpush
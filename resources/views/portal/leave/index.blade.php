@extends('layouts.portal-base')

@section('title')
{{'Leave'}}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('js/lib/datatables/Responsive-2.5.0/css/responsive.bootstrap5.min.css') }}">
@endpush

@section('content')

<div class="card content-card mx-lg-4">
    <div class="card-body">

        {{-- TABLE TITLE HEADER --}}
        <div class="d-flex align-items-center gap-1">
            <h6 class="card-title me-auto">
                <span>My Leaves</span>
            </h6>

            {{-- RECORD DATE RANGE FILTERS --}}
            @include('components.record-range-filters')

            {{-- ADD BUTTON --}}
            <button class="btn btn-primary flat-button shadow-0 d-none d-md-block" id="btn-request-leave"
                data-mdb-toggle="modal" data-mdb-target="#leaveRequestModal">
                <i class="fas fa-plus"></i>
                <span class="ms-1">Request</span>
            </button>

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
            data-src="{{ $routes['getLeaves'] }}" 
            data-on-cancel="{{ $routes['cancelLeave'] }}"
            id="dataset-table">
            
            </colgroup>
            <thead class="user-select-none">
                <tr>
                    <th style="min-width: 10%;"">#</th>
                    <th style="min-width: 15%">Date From</th>
                    <th style="min-width: 15%">Date To</th>
                    <th style="min-width: 15%">Duration</th>
                    <th style="min-width: 20%">Reason</th>
                    <th style="min-width: 10%">Status</th>
                    <th style="min-width: 15%">Requested On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="overflow-x-scroll">{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('dialogs')
@include('modals.portal.leave-request-modal', ['post' => $routes['requestNew']])
<x-fab as="fab-request-leave" icon="fa-plus" class="d-md-none d-flex" toggle-modal="#leaveRequestModal"/>
@endpush
<x-gijgo-driver type="date" />

@push('scripts')
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/lib/datatables/Responsive-2.5.0/js/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
<script src="{{ asset('js/main/portal/employee-leave.js') }}"></script>
@endpush
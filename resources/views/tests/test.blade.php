@extends('layouts.base')

@section('content')
<x-type-ahead as="input-emp-no" leading-icon="fa-fingerprint" />
@endsection

@push('scripts')
<script>
    $(document).ready(function()
    {
        let autosgx = to_typeahead('#input-emp-no');
        autosgx.setAdapter([
            {label: 'One', value: 1},
            {label: 'Two', value: 2},
            {label: 'Three', value: 3},
        ]);
    });
</script>
   
@endpush

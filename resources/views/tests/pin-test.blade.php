@extends('layouts.base')

@section('content')
<div class="d-flex gap-4">
    <h6>EMP NO.</h6>
    <h6>PIN CODE</h6>
</div>
    @foreach ($dataset as $row)
        <div class="d-flex gap-4">
            <h6 class="text-primary-dark">{{ $row['empno'] }}</h6>
            <h6>{{ decrypt($row['pin']) }}</h6>
        </div>
    @endforeach
@endsection

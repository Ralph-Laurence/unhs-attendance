@php
    $today = today();
@endphp
<div class="{{ $as }}-wrapper month-day-picker d-flex align-items-center gap-1">
    <input type="text" name="{{ $as }}" id="{{ $as }}" class="d-none" value="{{ date('Y-m-d') }}" />
    <div class="dropdown month-dropdown">
        <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" 
            id="{{ $as }}-month-dropbutton" data-mdb-toggle="dropdown" aria-expanded="false">
            <span class="me-1 button-text">{{ date('F') }}</span>
            <i class="fas fa-chevron-down opacity-65"></i>
        </button>
        <div class="dropdown-menu h-190p overflow-x-hidden" aria-labelledby="{{ $as }}-month-dropbutton" data-simplebar>
            <ul class="list-unstyled">
                @for ($i = 1; $i <= 12; $i++)
                    <li>
                        <a class="dropdown-item" role="button" data-month="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </a>
                    </li>
                @endfor
            </ul>
        </div>
    </div>
    <div class="dropdown day-dropdown">
        <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" 
            id="{{ $as }}-day-dropbutton" data-mdb-toggle="dropdown" aria-expanded="false">
            <span class="me-1 button-text">{{ date('d') }}</span>
            <i class="fas fa-chevron-down opacity-65"></i>
        </button>
        <ul class="dropdown-menu h-190p overflow-x-hidden" aria-labelledby="{{ $as }}-day-dropbutton" data-simplebar>
            @for ($i = 1; $i <= $today->daysInMonth; ++$i)
                <li>
                    <a class="dropdown-item" role="button" data-day="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                        <span class="d-inline-block w-24p">{{ $i }}</span>
                        <span class="d-inline-block mx-1 opacity-55">-</span>
                        <span class="d-inline-block opacity-55">{{ $today->startOfMonth()->addDays($i-1)->format('D') }}</span>
                    </a>
                </li>
            @endfor
        </ul>
    </div>
</div>
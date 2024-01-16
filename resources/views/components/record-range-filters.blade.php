@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/main/components/month-select.css') }}" />
    @endpush 
@endonce

<div class="dropdown record-range-dropdown">
    <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" id="record-date-dropdown-button"
        data-mdb-toggle="dropdown" aria-expanded="false" data-mdb-auto-close="outside">
        <span class="me-1 button-text">Today</span>
        <i class="fas fa-chevron-down opacity-65"></i>
    </button>
    <ul class="dropdown-menu record-range-filter" aria-labelledby="options-dropdown-button">
        <li><a class="dropdown-item daily" role="button" data-button-text="Today">Today</a></li>
        <li><a class="dropdown-item weekly" role="button" data-button-text="This Week">This Week</a></li>
        <li class="dropstart month-range-dropstart">
            <a class="dropdown-item dropdown-togglex with-submenu" id="month-range-dropdown-button"
                data-mdb-toggle="dropdown" role="button" data-button-text="By Month">By Month</a>
            <div class="dropdown-menu month-select-dropmenu overflow-hidden" aria-labelledby="month-range-dropdown-button" >
                <div class="bg-color-primary p-2 flex-center mb-2">
                    <h6 class="text-sm text-uppercase fw-bold text-center text-white m-0">Select Month</h6>
                </div>
                @php
                    $counter = 0;
                    $currentMonthIndex = date('n');
                @endphp

                <div class="month-select">
                    @for ($i = 1; $i <= 12; $i++)
                        @if ($counter % 4 == 0)
                            <div class="d-flex align-items-center gap-2 justify-content-center">
                        @endif
                            
                            @if ($i == $currentMonthIndex)
                                <button class="btn btn-sm btn-secondary flat-button mb-2 month-item current-month" 
                            @else
                                <button class="btn btn-sm btn-secondary flat-button mb-2 month-item" 
                            @endif
                                data-month="{{ $i }}">
                                {{ date('M', mktime(0, 0, 0, $i, 1)) }}
                            </button>

                        @if ($counter % 4 == 3)
                            </div>
                        @endif
                        @php
                            $counter ++;
                        @endphp
                    @endfor

                @if ($counter % 4 != 0)
                    </div>
                @endif     
                </div>

                <input type="hidden" id="selected-month-index" value="{{ $currentMonthIndex }}">
            </div>
        </li>
    </ul>
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/components/record-range-filter.js') }}"></script>
    @endpush
@endonce
@php
    use \Carbon\Carbon;

    $parentClasses = '';

    if ($attributes->has('parent-classes'))
        $parentClasses = $attributes->get('parent-classes');

    $attrRequired = $attributes->has('required') ? 'required' : '';

    $MIN_YEAR_OFFSET = 2;
    $MAX_YEARS_ALLOWED = 16;

    $currentMonth = date('M');
    $currentMonthIndex = Carbon::now()->month - 1;

    $currentYear = date("Y");
    $startYear = $currentYear - $MIN_YEAR_OFFSET;
    $years = range($startYear, $startYear + $MAX_YEARS_ALLOWED - 1);
    
@endphp

@once
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/components/textbox.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main/components/momentpicker.css') }}">
    @endpush
@endonce

<div class="textbox dropdown moment-picker-textbox {{ $parentClasses }} {{ $errors->has($as) ? ' has-error' : '' }} {{ $attrRequired }}">

    <div class="input-wrapper dropdown-toggle" data-mdb-toggle="dropdown" aria-expanded="false" data-mdb-auto-close="outside">

        <i class="fas fa-calendar-days leading-icon text-sm ms-2 opacity-80"></i>

        <input type="text" name="{{ $as }}" id="{{ $as }}" {{ $attributes->merge(['class' => "main-control"]) }} 
        value="{{ old($as, date('Y-m-d')) }}" readonly/>

        @if ($attributes->has('trailing-icon'))
        <i class="fas trailing-icon {{ $attributes->get('trailing-icon') }}"></i>
        @endif

        <i class="fas fa-circle-xmark error-icon"></i>
    </div>

    {{-- ERROR LABEL --}}
    <h6 class="px-2 my-1 text-danger text-sm error-label">{{ $errors->first($as) }}</h6>

    <div class="moment-picker-popover dropdown-menu bg-white shadow shadow-4-strong rounded-3 overflow-hidden">
        <div class="control-ribbon p-2 gap-2 flex-center bg-color-primary">
            <button type="button" class="btn-sm btn-secondary flat-button shadow-0 px-2 btn-month-picker">
                <span class="me-1 btn-text">{{ $currentMonth }}</span>
                <i class="fas fa-chevron-down text-sm"></i>
            </button>
            <button type="button" class="btn-sm btn-secondary flat-button shadow-0 px-2 btn-year-picker">
                <span class="me-1 btn-text">{{ $currentYear }}</span>
                <i class="fas fa-chevron-down text-sm"></i>
            </button>
        </div>
        <div class="month-picker d-hidden">
            <input type="text" name="month-select" class="d-none month-select" value="{{ $currentMonthIndex }}">
            <table class="months-table">
                <tbody>
                    @for ($i = 1; $i <= 12; $i++)
                        @php
                            $monthStr = date('M', mktime(0, 0, 0, $i, 1)); // 'M' for short month name
                            $isSelected = ($monthStr == $currentMonth) ? 'current-month' : '';
                        @endphp
                        @if ($i % 4 == 1) <!-- start a new row every 4 month items -->
                            <tr>
                        @endif
                        <td>
                            <button type="button"
                            class="btn btn-sm btn-link text-primary-dark flat-button mb-2 month-item {{ $isSelected }}" 
                            data-month="{{ $i-1 }}" data-month-name="{{ $monthStr }}">
                                {{ $monthStr }}
                            </button>
                        </td>
                        @if ($i % 4 == 0 || $i == 12) <!-- end the row after 4 month items or at the end of the year -->
                            </tr>
                        @endif
                    @endfor
                </tbody>
            </table>            
        </div>
        <div class="year-picker d-hidden">
            <input type="text" name="year-select" class="d-none year-select" value="{{ $currentYear }}">
            <table class="years-table">
                <tbody>
                @foreach ($years as $i => $year)
                    @php
                        $isSelected = ($year == $currentYear) ? 'current-year' : '';
                    @endphp
                    @if ($i % 4 == 0) <!-- start a new row every 4 year items -->
                        <tr>
                    @endif
                    <td>
                        <button class="btn btn-sm btn-link flat-button text-primary-dark text-14 mb-2 year-item {{ $isSelected }}" 
                        data-year="{{ $year }}" type="button">
                            {{ $year }}
                        </button>
                    </td>
                    @if (($i + 1) % 4 == 0 || $i == count($years) - 1) <!-- end the row after 4 year items or at the end of the year -->
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>            
        </div>
        <div class="day-picker">
            <table class="day-table">
                <thead>
                    <tr>
                        <th>Sun</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
       
    </div>
</div>

@once
    @push('scripts')
    <script src="{{ asset('js/components/momentpicker.js') }}"></script>
    @endpush
@endonce
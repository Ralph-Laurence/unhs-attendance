<div class="d-flex flex-column gap-2 text-center">
    <div class="row">
        <div class="col-2 g-0 pt-2">
            <img src="{{ asset('images/internal/icons/logo-sm.png') }}" alt="logo" width="50" height="50" />
        </div>
        <div class="col-8 g-0 text-sm flex-center flex-column">
            <p class="m-0 text-uppercase">Uddiawan National High School</p>
            <small>Solano, Nueva Vizcaya</small>
        </div>
        <div class="col-2 g-0"></div>
    </div>
    {{-- <table class="logo-wrapper w-100 table-fixed">
        <thead>
            <tr>
                <th style="width: 2%; background: orange;">
                    <img src="{{ asset('images/internal/icons/logo-sm.png') }}" alt="logo" width="60" height="60" />
                </th>
                <th class="text-sm text-uppercase opacity-75" style="width: 92%;">
                    <p class="m-0">Uddiawan National High School</p>
                    <small>Solano, Nueva Vizcaya</small>
                </th>
                <th style="width: 5%; background: orange;"></th>
            </tr>
        </thead>
    </table> --}}
    <h6 class="text-uppercase text-13 fw-bold my-0">Daily Time Record</h6>
    <p class="my-0 text-uppercase text-decoration-underline fw-bold text-13 dtr-empname">Employee Name</p>
    <p class="my-0">
        <i class="doc-bullet-diams"></i>
        <small class="dtr-month-range">For the month of <span class="month-of"></span></small>
        <i class="doc-bullet-diams"></i>
    </p>
</div>
<div class="table-wrapper">
    <table class="table-unstyled dtr-summary">
        <!-- Your first table content goes here -->
        <thead>
            <tr>
                <th rowspan="2" class="th-day">Day</th>
                <th colspan="2">A.M.</th>
                <th colspan="2">P.M.</th>
                <th colspan="2">Undertime</th>
            </tr>
            <tr class="th-normal">
                <th class="th-time">In</th>
                <th class="th-time">Out</th>
                <th class="th-time">In</th>
                <th class="th-time">Out</th>
                <th>Hrs</th>
                <th>Min</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <th colspan="5" class="fw-bold">Total</th>
            <th colspan="2" class="fw-bold th-undertime"></th>
        </tfoot>
    </table>
</div>
<div class="agreement-wrapper">
    <p class="agreement mt-2">
        I CERTIFY, on my honor, that the above is a true and correct report of the hours of work performed. The record was made daily at the time of arrival and departure from the office.
    </p>
    <table>
        <tbody>
            <tr>
                <td></td>
                <td class="text-center">
                    <hr class="mt-2 mb-0">
                    <small>Signature</small>
                </td>
            </tr>
        </tbody>
    </table>
    <hr class="mt-2 mb-0">
    <small>Verified as to the prescribed office hours</small>
</div>
@php
use App\Http\Utils\RouteNames;

$type = 'Employee';

if (!empty($role))
$type = $role;

$icons = [
'Employee' => 'modal_icon_employee.png',
'Staff' => 'modal_icon_staff.png',
'Teacher' => 'modal_icon_teacher.png',
];
@endphp
@push('styles')
    <style>
        #employeeDetailsModal .photo-frame {
            background: #D1D5DA;
            width: 100px;
            height: 100px;
            overflow: hidden;
            border-radius: 50%;
        }
        #employeeDetailsModal .photo-frame img {
            width: 100%;
            height: 100%;
        }

        #employeeDetailsModal #employee-details-position {
            background-color: var(--primary-lighter);
            font-size: 14px;
            padding-top: 4px;
            padding-bottom: 4px;
        }

        #employeeDetailsModal .detail-tag {
            /* width: 60px; */
            min-width: 60px;
        }
    </style>
@endpush
<div class="modal fade" id="employeeDetailsModal" tabindex="-1" aria-labelledby="employeeDetailsModalLabel"
    aria-hidden="true" data-mdb-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/' . $icons[$type]) }}" width="24" height="24" alt="icon"
                        class="modal-icon" />
                    <h6 class="modal-title mb-0" id="employeeDetailsModalLabel">Employee Details</h6>
                </div>
                <button type="button" class="btn-close" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex px-4">
                
                <div class="d-flex flex-column me-4">
                    <div class="photo-frame flex-center mb-2">
                        <img id="employee-details-photo" src="{{ asset('images/internal/placeholders/profile.png') }}">
                    </div>
                    <div class="rounded-8 text-center" id="employee-details-position">
                        {{ $type }}
                    </div>
                </div>
                <div class="right-contents">
                    <div class="text-sm text-primary-dark mb-1">
                        <i class="fas fa-user me-1"></i> Basic Information
                    </div>
                    <div class="d-flex text-14 gap-2 mb-1 text-break">
                        <div class="opacity-65 detail-tag">Name:</div>
                        <div id="employee-details-name"></div>
                    </div>
                    <div class="d-flex text-14 gap-2 mb-1">
                        <div class="opacity-65 detail-tag">ID #:</div>
                        <div id="employee-details-idno"></div>
                    </div>
                    <div class="d-flex text-14 gap-2">
                        <div class="opacity-65 detail-tag">Status:</div>
                        <div id="employee-details-status"></div>
                    </div>
                    <hr>
                    <div class="text-sm text-primary-dark mb-1">
                        <i class="fas fa-phone me-1"></i> Contact Details
                    </div>
                    <div class="d-flex text-14 gap-2 mb-1">
                        <div class="opacity-65 detail-tag">Mobile:</div>
                        <div id="employee-details-contact"></div>
                    </div>
                    <div class="d-flex text-14 gap-2">
                        <div class="opacity-65 detail-tag">Email:</div>
                        <div id="employee-details-email"></div>
                    </div>
                    <hr>
                    <div class="text-sm text-primary-dark mb-3">
                        <i class="fas fa-calendar-days me-1"></i> Attendance Trail
                    </div>
                    <button class="btn btn-sm btn-primary flat-button shadow-0 btn-view-trail">
                        View all
                    </button>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary flat-button" data-mdb-ripple-init
                    data-mdb-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    <form action="{{ route(RouteNames::DailyTimeRecord['index']) }}" method="post" class="attendance-trail-form d-none">
        @csrf
        <input type="hidden" name="employee-key" id="employee-key">
    </form>
</div>

@push('scripts')
    <script>
        let attendanceTrailForm = undefined;
        let trailsEmployeeKey   = undefined;

        $(document).ready(function() 
        {
            attendanceTrailForm = $('.attendance-trail-form');
            trailsEmployeeKey   = attendanceTrailForm.find('#employee-key');

            $('.btn-view-trail').on('click', () => viewEmployeeTrail());
        });

        function bindTrailsData(data) 
        {
            if (!data)
                return;

            $('#employee-details-name').text([
                data.fname, data.mname, data.lname
            ].join(' '));

            $('#employee-details-idno').text(data.idNo);
            $('#employee-details-contact').text(data.contact);
            $('#employee-details-email').text(data.email);
            $('#employee-details-status').text(data.status);

            trailsEmployeeKey.val(data.rowKey);
        }

        function viewEmployeeTrail() 
        {
            if (trailsEmployeeKey.val())
                attendanceTrailForm.trigger('submit');
        }
    </script>
@endpush
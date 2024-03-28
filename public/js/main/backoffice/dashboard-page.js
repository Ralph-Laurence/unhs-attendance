const chartColors = {
    'primary': '#4300D4',
    'success': '#1FC9A4',
    'warning': '#FFAC34',
    'danger' : '#FF2641',

    'primary_alphabg': '#4300D425',
    'success_alphabg': '#1FC9A435',
    'warning_alphabg': '#FFAC3485',
    'danger_alphabg' : '#FF264125'
};

let dashboardPage = (function() 
{
    const attxStatsModalSelector = '#statistics-modal';
    let statsModal;
    let statsTablePageLen;
    let statsTableLeavePageLen;

    const leaveStatsModalSelector = '.statistics-leave-modal';
    let leaveStatModal;

    let init = function() 
    {
        getEmployeeGraphings();
        getAttendanceGraphings();

        statsModal     = new mdb.Modal($(attxStatsModalSelector));
        leaveStatModal = new mdb.Modal($(leaveStatsModalSelector));
    };

    let bindEvents = function() 
    {
        $('.leave-count-wrapper').on('click', function()
        {
            handleLeaveStatsAdapter( $(this) );
        });
    };

    function getEmployeeGraphings()
    {
        $.ajax({
            url: $("#employees-diff").data('src'),
            data: {
                '_token' : getCsrfToken()
            },
            type: 'post',
            success: function (response) 
            {
                handleEmployeeDiff(response);
                handleEmpStatusDiff(response);
                handleLeaveReqDiff(response);
            },
            error: function (xhr, status, error) {  

            }
        });
    }

    function handleEmployeeDiff(response)
    {
        let roles = [];
        let count = [];
        let total = 0;

        let employeeDifference = response.employeeDifference.counts;
        let segmentUrls = response.employeeDifference.segments;
        
        for (let key in employeeDifference) 
        {
            roles.push(key);
            count.push(employeeDifference[key]);
            total += employeeDifference[key];
        }
        
        $('#employee-count').text(total);

        const employeesDiffCtx = document.getElementById("employees-diff");

        const _data = {
            labels: roles,
            datasets: [{
                label: 'Difference',
                data: count,
                backgroundColor: [
                    chartColors['primary'],
                    chartColors['success']
                ]
            }]
        };

        const _options = {
            onClick: function(event, segments)
            {
                if (segments.length > 0)
                {
                    const clickedSegment = segments[0];
                    const label = _data.labels[clickedSegment.index];
                    const value = _data.datasets[0].data[clickedSegment.index];
                    console.log(`Clicked: ${label} (${value}%)`);
    
                    // Get the URL based on the clicked segment label
                    const url = segmentUrls[label];
    
                    // Redirect to the URL
                    window.location.href = url;
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '80%'
        };

        new Chart(employeesDiffCtx, {
            type: 'doughnut',
            data: _data,
            options: _options
        });

    }

    function handleEmpStatusDiff(response)
    {
        let diff = response.empStatusDifference;

        $('#count-on-duty').text(diff['On Duty']);
        $('#count-on-leave').text(diff['Leave']);
        $('#emp-stat-total').text(`Total : ${diff['Total']}`);
    }

    function handleLeaveReqDiff(response)
    {
        let diff = response.leaveStatusDifference;

        $('.leave-count-wrapper .leave-count-pending') .text(diff['Pending']);
        $('.leave-count-wrapper .leave-count-approved').text(diff['Approved']);
        $('.leave-count-wrapper .leave-count-rejected').text(diff['Rejected']);
        $('.total-leave-reqs').text(`Total : ${diff['Total']}`);
    }

    function handleAttendanceStatistics(response)
    {
        const dailyAttendances = document.getElementById("attendance-statistics");
        let datasetValues = Object.values(response.attendanceStats);

        const _data = {
            labels: Object.keys(response.attendanceStats),
            datasets: [{
                label: 'No. of Attendances',
                data: datasetValues,
                backgroundColor: [
                    chartColors['primary'],
                    chartColors['success'],
                    chartColors['primary'],
                    chartColors['success'],
                    chartColors['primary'],
                    chartColors['primary'],
                ],
                barThickness    : 16,
            }]
        };

        let maxValue = Math.max(...datasetValues);

        const _options = {
            onClick: function(event, segments)
            {
                onAttendanceStatsSegmentClick(response, _data, segments);
            },
            scales: {
                y: {
                    beginAtZero: true,
                    // add +2 to the highest value in the dataset to
                    // simulate drawing extra spaces above the bar graph.
                    suggestedMax: maxValue + 2
                }
            }
        };

        new Chart(dailyAttendances, {
            type:     'bar',
            data:     _data,
            options:  _options
        });
    }

    function handleAttendanceComparison(response)
    {
        const monthlySummary = document.getElementById("monthly-totals");

        let months = [];
        let totals = [];

        Object.keys(response.monthlyComparison).forEach(k => {
            months.push(response.monthlyComparison[k].month);
            totals.push(response.monthlyComparison[k].total);
        });

        let highestTotal = Math.max(...totals);

        new Chart(monthlySummary, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Total Attendances',
                    data: totals,
                    backgroundColor: chartColors['primary_alphabg'],
                    borderColor: chartColors['primary'],
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBorderColor: totals.map(value => {
                        if (value < 1)
                            return 'red';
        
                        if (value === highestTotal)
                            return '#FF840C'; 
                        
                        return chartColors['primary'];
                    }),
                    // Set the point color to 'red' for the highest value and '#00D1A4' for others
                    pointBackgroundColor: totals.map(value => {
                        if (value < 1)
                            return 'red';
        
                        if (value === highestTotal)
                            return '#FF840C'; 
                        
                        return '#00D1A4';
                    }),
                    //pointBackgroundColor: totals.map(value => value === highestTotal ? '#FF840C' : '#00D1A4'),
                     // Set the point style to 'triangle' for the highest value and 'circle' for others
                    pointStyle: totals.map(value => {
                        if (value < 1)
                            return 'crossRot';

                        if (value === highestTotal)
                            return 'triangle'; 
                        
                        return 'circle';
                    })
                }]
            }
        });
    }

    function getAttendanceGraphings()
    {
        $.ajax({
            url: $("#attendance-statistics").data('src'),
            data: {
                '_token' : getCsrfToken()
            },
            type: 'post',
            success: function (response) 
            {
               handleAttendanceStatistics(response);
               handleAttendanceComparison(response);
            },
            error: function (xhr, status, error) {  

            }
        });
    }

    function onAttendanceStatsSegmentClick(response, data, segments)
    {
        // segmentFilters
        if (segments.length > 0)
        {
            const clickedSegment = segments[0];
            const label = data.labels[clickedSegment.index];
            //const value = data.datasets[0].data[clickedSegment.index];
            //console.log(`Clicked: ${label} (${value}%)`);
            // alert(`Clicked: ${label} (${value}%)`);
            // Get the URL based on the clicked segment label
            const segmentColor = data.datasets[0].backgroundColor[clickedSegment.index];

            $.ajax({
                url  : response.segmentAction,
                type : 'post',
                data : {
                    '_token' : getCsrfToken(),
                    'filter' : response.segmentFilters[label],
                },
                success: function(response)
                {
                    bindAttendanceStatsAdapter(response, segmentColor);
                },
                error: function(xhr, status, error) {

                }
            });
        }
    }

    function bindAttendanceStatsAdapter(response, segmentColor)
    {
        let tableId = '#stats-table';
        let pageLengthContainer = $('#stats-table-page-container');

        let columnDefinitions = [
            {data: 'empno',   title: 'ID No',    width: '20%', class: 'text-truncate' },
            {data: 'empname', title: 'Name',     width: '35%', class: 'text-truncate'},
            {data: 'rank',    title: 'Position', width: '20%', class: 'text-truncate' }
        ];

        if (response.dynamic == 'timein')
        {
            let obj = {
                data: 'timein',
                title: 'Time In',
                width: '20%', 
                class: 'text-truncate'
            };

            columnDefinitions.push(obj);
        }

        if (response.dynamic == 'duration')
        {
            let obj = {
                data: 'duration',
                title: 'Duration',
                width: '20%',
                class: 'text-truncate'
            };

            columnDefinitions.push(obj);
        }

        // Check if the DataTable exists and if so, destroy it
        if ($.fn.DataTable.isDataTable(tableId))
        {
            $(tableId).DataTable().destroy();
        }

        // Clear its contents
        $(`${tableId} tbody`).empty();

        // Reinitialize the DataTable with new data and columns
        let dt = $(tableId).DataTable({
            'searching': false,
            'ordering': false,
            'autoWidth': true,
            "deferRender": true,
            'columns': columnDefinitions,
            'drawCallback' : function (settings) 
            {  
                $(attxStatsModalSelector).find('.statistic-context')
                                     .text(response.segment)
                                     .css('background', segmentColor);
                statsModal.show();
            },
            'initComplete': function() 
            {
                pageLengthContainer.empty();
                $('#stats-table_wrapper #stats-table_length').detach().prependTo(pageLengthContainer);
            },
            'data' : response.dataset
        });

        if (statsTablePageLen)
            statsTablePageLen = null;

        statsTablePageLen = to_lengthpager('#stats-table-page-len', dt);
    }

    function handleLeaveStatsAdapter(sender) 
    {  
        $.ajax({
            url: sender.data('action'),
            type: 'POST',
            data: {
                '_token'  : getCsrfToken(),
                'segment' : sender.data('segment') 
            },
            success : bindLeaveStatsAdapter,
            error : function (xhr, error, status) 
            {  
                alertModal.showDanger(GenericMessages.XHR_FAIL_ERROR);
            }
        });
    }

    function bindLeaveStatsAdapter(response)
    {
        if (typeof response.dataset === 'object' && response.dataset.length < 1)
        {
            alertModal.showInfo("No records to show.");
            return;
        }

        let tableId = `${leaveStatsModalSelector} #leave-stats-table`;

        let columnDefinitions = [
            {data: 'empno',    title: 'ID No',      width: '20%', class: 'text-truncate' },
            {data: 'empname',  title: 'Name',       width: '30%', class: 'text-truncate' },
            {data: 'type',     title: 'Leave Type', width: '25%', class: 'text-truncate' },
            {data: 'duration', title: 'Duration',   width: '20%', class: 'text-truncate' },
            {data: 'status',   title: 'Status',     width: '20%', class: 'text-truncate' },
        ];

        // Check if the DataTable exists and if so, destroy it
        if ($.fn.DataTable.isDataTable(tableId))
        {
            $(tableId).DataTable().destroy();
        }

        // Clear its contents
        $(`${tableId} tbody`).empty();

        // Reinitialize the DataTable with new data and columns
        let dt = $(tableId).DataTable({
            'searching': false,
            'ordering': false,
            'autoWidth': true,
            "deferRender": true,
            'columns': columnDefinitions,
            'drawCallback' : function (settings) 
            {  
                $(leaveStatsModalSelector).find('.statistic-context')
                                     .text(response.segment)
                                     .css('background', response.segmentColor);

                leaveStatModal.show();
            },
            'data' : response.dataset
        });

        if (statsTableLeavePageLen)
            statsTableLeavePageLen = null;

        statsTableLeavePageLen = to_lengthpager('#stats-leave-table-page-len', dt);
    }

    return {
        'init'   : init,
        'handle' : bindEvents
    }
})();

$(document).ready(function () 
{
    dashboardPage.init();
    dashboardPage.handle();
});
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
    let statsModal;

    let init = function() 
    {
        getEmployeeGraphings();
        getAttendanceGraphings();

        statsModal = new mdb.Modal(document.querySelector('#statistics-modal'))
    };

    let bindEvents = function() {

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
    }

    function handleLeaveReqDiff(response)
    {
        let diff = response.leaveStatusDifference;

        $('.leave-count-wrapper .leave-count-pending') .text(diff['Pending']);
        $('.leave-count-wrapper .leave-count-approved').text(diff['Approved']);
        $('.leave-count-wrapper .leave-count-rejected').text(diff['Rejected']);
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

    function onAttendanceStatsSegmentClick(response, data, segments)
    {
        // segmentFilters
        if (segments.length > 0)
        {
            const clickedSegment = segments[0];
            const label = data.labels[clickedSegment.index];
            const value = data.datasets[0].data[clickedSegment.index];
            console.log(`Clicked: ${label} (${value}%)`);
            // alert(`Clicked: ${label} (${value}%)`);
            // Get the URL based on the clicked segment label
            
            $.ajax({
                url  : response.segmentAction,
                type : 'post',
                data : {
                    '_token' : getCsrfToken(),
                    'filter' : response.segmentFilters[label],
                },
                success: function(response)
                {
                    console.warn(response);
                    bindTableAdapter(response.dataset, response.dynamic);
                },
                error: function(xhr, status, error) {

                }
            });
        }
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

        new Chart(monthlySummary, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Hours',
                    data: totals,
                    backgroundColor: chartColors['primary_alphabg'],
                    borderColor: chartColors['primary'],
                    fill: true,
                    tension: 0.1,
                }]
            }
        });
    }

    function bindTableAdapter(dataSource, dynamicCol)
    {
        let tableId = '#stats-table';

        let columnDefinitions = [
            {data: 'empno',   title: 'ID No',    width: '20%', class: 'text-truncate' },
            {data: 'empname', title: 'Name',     width: '35%', class: 'text-truncate'},
            {data: 'rank',    title: 'Position', width: '20%', class: 'text-truncate' }
        ];

        if (dynamicCol == 'timein')
        {
            let obj = {
                data: 'timein',
                title: 'Time In',
                width: '20%', 
                class: 'text-truncate'
            };

            columnDefinitions.push(obj);
        }

        if (dynamicCol == 'duration')
        {
            let obj = {
                data: 'duration',
                title: 'Duration',
                width: '20%',
                class: 'text-truncate'
            };

            columnDefinitions.push(obj);
        }

        console.warn(columnDefinitions);

        // Check if the DataTable exists and if so, destroy it
        if ($.fn.DataTable.isDataTable(tableId))
        {
            $(tableId).DataTable().destroy();
        }

        // Clear its contents
        $(`${tableId} tbody`).empty();

        // Reinitialize the DataTable with new data and columns
        $(tableId).DataTable({
            'searching': false,
            'ordering': false,
            'autoWidth': true,
            "deferRender": true,
            'columns': columnDefinitions,
            'drawCallback' : function (settings) {  
                statsModal.show();
            },
            'data' : dataSource
        });
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
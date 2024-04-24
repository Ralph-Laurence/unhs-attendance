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

// Mapping of three-letter month abbreviations to full month names
const monthMappings = {
    "Jan": {name: "January"   , number: 1},
    "Feb": {name: "February"  , number: 2},
    "Mar": {name: "March"     , number: 3},
    "Apr": {name: "April"     , number: 4},
    "May": {name: "May"       , number: 5},
    "Jun": {name: "June"      , number: 6},
    "Jul": {name: "July"      , number: 7},
    "Aug": {name: "August"    , number: 8},
    "Sep": {name: "September" , number: 9},
    "Oct": {name: "October"   , number: 10},
    "Nov": {name: "November"  , number: 11},
    "Dec": {name: "December"  , number: 12}
};

let dashboardPage = (function() 
{
    const attxStatsModalSelector = '#statistics-modal';
    let statsModal;
    let statsTablePageLen;
    let statsTableLeavePageLen;

    const leaveStatsModalSelector = '.statistics-leave-modal';
    let leaveStatModal;

    const monthlyAttendanceModalSelector = '#statistics-monthly-atx-modal';
    let monthlyStatModal;

    const empStatsModalSelector = '#statistics-emp-status-modal';
    let empStatsModal;
    let empStatsTablePageLen;

    let init = function() 
    {
        getEmployeeGraphings();
        getAttendanceGraphings();

        statsModal       = new mdb.Modal($(attxStatsModalSelector));
        leaveStatModal   = new mdb.Modal($(leaveStatsModalSelector));
        monthlyStatModal = new mdb.Modal($(monthlyAttendanceModalSelector));
        empStatsModal    = new mdb.Modal($(empStatsModalSelector));
    };

    let bindEvents = function() 
    {
        $('.leave-count-indicator').on('click', function()
        {
            $(this).css('pointer-events', 'none');
            handleLeaveStatsAdapter( $(this) );
        });

        $('.emp-stat-count').on('click', function()
        {
            $(this).css('pointer-events', 'none');
            handleEmpStatsAdapter( $(this) );
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
                    chartColors['success'],
                    chartColors['warning'],
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

        $('#count-active-stat').text(diff['Active']);
        $('#count-on-leave').text(diff['Leave']);

        $('#count-on-duty').text(diff['ClockedIn']);
        $('#count-out').text(diff['ClockedOut']);
    }

    function handleLeaveReqDiff(response)
    {
        let diff = response.leaveStatusDifference;

        $('.leave-count-indicator .leave-count-pending') .text(diff['Pending']);
        $('.leave-count-indicator .leave-count-approved').text(diff['Approved']);
        $('.leave-count-indicator .leave-count-rejected').text(diff['Rejected']);
        $('.leave-count-indicator .leave-count-unnoticed').text(diff['Unnoticed']);
        // $('.total-leave-reqs').text(`Total : ${diff['Total']}`);
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

        let dataSource = response.monthlyComparison.chartDatasource;

        Object.keys(dataSource).forEach(k => {
            months.push(dataSource[k].month);
            totals.push(dataSource[k].total);
        });

        let highestTotal = Math.max(...totals);

        let _data = {
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
        };

        new Chart(monthlySummary, {
            type: 'line',
            data: _data,
            options: {
                onClick: function (event, segments)
                {
                    if (segments.length > 0)
                    {
                        const clickedSegment = segments[0];
                        const label = _data.labels[clickedSegment.index];
                        const value = _data.datasets[0].data[clickedSegment.index];

                        if (value < 1)
                        {
                            alertModal.showWarn(`No records to show from the month of ${monthMappings[label].name}.`);
                            return;
                        }

                        onMonthlySegmentsClick({
                            'monthIndex' : monthMappings[label].number,
                            'actionUrl'  : response.monthlyComparison.segmentRoute,
                            'monthName'  : monthMappings[label].name
                        });
                    }
                },
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

    function onMonthlySegmentsClick(settings)
    {
        $.ajax({
            url  : settings.actionUrl,
            type : 'post',
            data : {
                '_token' : getCsrfToken(),
                'monthIndex' : settings.monthIndex
            },
            success: function(response)
            {
                if (!response)
                {
                    alertModal.showWarn("No records to show.");
                    return;
                }

                response = JSON.parse(response);
                bindFindMonthlyAttendanceAdapter(response, settings.monthName);
            },
            error: function(xhr, status, error) 
            {
                alertModal.showDanger("There was a problem reading the attendances");
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

    function bindFindMonthlyAttendanceAdapter(response, monthName)
    {
        let tableId = '#monthly-atx-table';

        let columnDefinitions = [
            {data: 'created_at',  title: 'Date',     width: '10%', class: 'text-truncate',
                render: function(data, type, row)
                {
                    if (data == '' || data == undefined)
                        return '';

                    var date = extractDate(data);
                    return `${date.month} ${date.day}`;
                }
            },
            {data: 'empname',  title: 'Name',     width: '30%', class: 'text-truncate text-capitalize'},
            {data: 'timein',   title: 'Time In',  width: '20%', class: 'text-truncate', 
                render: function(data, type, row) 
                {
                    return data ? format12Hour(data) : ''
                },
            },
            {data: 'timeout',  title: 'Time Out', width: '20%', class: 'text-truncate', 
                render: function(data, type, row) 
                {
                    return data ? format12Hour(data) : ''
                },
            },
            {data: 'duration', title: 'Duration', width: '20%', class: 'text-truncate'},
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
                $(monthlyAttendanceModalSelector).find('.statistic-context').text(monthName);
            },
            'data' : response.data
        });

        if (statsTablePageLen)
            statsTablePageLen = null;

        statsTablePageLen = to_lengthpager('#stats-monthly-table-page-len', dt);

        monthlyStatModal.show();
    }

    function bindAttendanceStatsAdapter(response, segmentColor)
    {
        let tableId = '#stats-table';
        let pageLengthContainer = $('#stats-table-page-container');

        let columnDefinitions = [
            {data: 'empno',   title: 'ID No',    width: '20%', class: 'text-truncate' },
            {data: 'empname', title: 'Name',     width: '35%', class: 'text-truncate text-capitalize'},
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
            },
            complete: () => sender.css('pointer-events', 'auto')
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
            // First Column -> Record Counter
            {
                width: '50px',
                className: 'record-counter text-truncate',
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {data: 'empno',     title: 'ID No',         width: '20%', class: 'text-truncate' },
            {data: 'empname',   title: 'Name',          width: '40%', class: 'text-truncate text-capitalize' },
            {data: 'type',      title: 'Leave Type',    width: '15%', class: 'text-truncate' },
            {data: 'date_from', title: 'Date From',     width: '20%', class: 'text-truncate' },
            {data: 'date_to',   title: 'Date To',       width: '20%', class: 'text-truncate' },
            {data: 'duration',  title: 'Duration',      width: '20%', class: 'text-truncate' },
            // {data: 'status',   title: 'Status',     width: '20%', class: 'text-truncate' },
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
    //=================================
    // Employee Status Modal
    //=================================
    function handleEmpStatsAdapter(sender) 
    {
        $.ajax({
            url: sender.data('action'),
            type: 'POST',
            data: {
                '_token'  : getCsrfToken(),
                'status'  : sender.data('segment') 
            },
            success : (response) => bindEmpStatsAdapter(response, sender),
            error   : function (xhr, error, status) 
            {  
                alertModal.showDanger(GenericMessages.XHR_FAIL_ERROR);
            },
            complete: () => sender.css('pointer-events', 'auto')
        });
    }

    function bindEmpStatsAdapter(response, sender)
    {
        if (typeof response.dataset === 'object' && response.dataset.length < 1)
        {
            alertModal.showInfo("No records to show.");
            return;
        }

        let tableId = `${empStatsModalSelector} #emp-stats-table`;

        let columnDefinitions = [
            // First Column -> Record Counter
            {
                width: '15%',
                className: 'record-counter text-truncate',
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {data: 'empno',     title: 'ID No',         width: '20%', class: 'text-truncate' },
            {data: 'empname',   title: 'Name',          width: '40%', class: 'text-truncate text-capitalize' },
            {data: 'rank',      title: 'Position',      width: '30%', class: 'text-truncate text-capitalize' },            
        ];

        if ('dynamic' in response && response.dynamic === 'timein')
        {
            let obj = {
                data: 'timein',
                title: 'Time In',
                width: '20%', 
                class: 'text-truncate'
            };

            columnDefinitions.push(obj);
        }

        if ('dynamic' in response && response.dynamic === 'timeout')
        {
            let obj = {
                data: 'timeout',
                title: 'Time Out',
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
        $(`${tableId} thead`).empty();

        // Reinitialize the DataTable with new data and columns
        let dt = $(tableId).DataTable({
            'searching': false,
            'ordering': false,
            'autoWidth': true,
            "deferRender": true,
            'columns': columnDefinitions,
            'drawCallback' : function (settings) 
            {  
                $(empStatsModalSelector).find('#statistic-context')
                                     .text(response.segment)
                                     .css('background', sender.data('segment-color'));

                empStatsModal.show();
            },
            'data' : response.dataset
        });

        if (empStatsTablePageLen)
            empStatsTablePageLen = null;

        empStatsTablePageLen = to_lengthpager('#emp-stats-table-page-len', dt);
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


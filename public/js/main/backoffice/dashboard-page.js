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

let dashboardPage = (function() {

    let init = function() 
    {
        getEmployeeGraphings();
        getAttendanceGraphings();
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

        let employeeDifference = response.employeeDifference;

        for (let key in employeeDifference) 
        {
            roles.push(key);
            count.push(employeeDifference[key]);
            total += employeeDifference[key];
        }
        
        $('#employee-count').text(total);

        const employeesDiffCtx = document.getElementById("employees-diff");

        new Chart(employeesDiffCtx, {
            type: 'doughnut',
            data: {
                labels: roles,
                datasets: [{
                    label: 'Difference',
                    data: count,
                    backgroundColor: [
                        chartColors['primary'],
                        chartColors['success']
                    ]
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '80%'
            }
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

        let maxValue = Math.max(...datasetValues);

        new Chart(dailyAttendances, {
            type: 'bar',
            data: {
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
                    //backgroundColor : chartColors['warning_alphabg'],
                    //borderColor     : chartColors['warning'],
                    barThickness    : 16,
                    //borderWidth     : 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        // add +2 to the highest value in the dataset to
                        // simulate drawing extra spaces above the bar graph.
                        suggestedMax: maxValue + 2
                    }
                }
            }
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

    // function handleAttendanceComparison(response)
    // {
    //     const monthlySummary = document.getElementById("monthly-totals");

    //     let months = [];
    //     let totals = [];

    //     Object.keys(response.monthlyComparison).forEach(k =>
    //     {
    //         months.push(response.monthlyComparison[k].month);
    //         totals.push(response.monthlyComparison[k].total);
    //     });

    //     new Chart(monthlySummary, {
    //         type: 'bar', // Change the chart type to bar
    //         data: {
    //             labels: months,
    //             datasets: [
    //                 {
    //                     label: 'Hours',
    //                     data: totals,
    //                     backgroundColor: chartColors['primary_alphabg'],
    //                     borderColor: chartColors['primary'],
    //                     borderWidth: 1, // Add a border to the bars
    //                 },
    //                 {
    //                     type: 'line', // Add a line dataset
    //                     label: 'Green Line',
    //                     data: totals, // Use the same data as the bars
    //                     borderColor: 'green', // Set the line color to green
    //                     fill: false, // Don't fill the area under the line
    //                     tension: 0.1,
    //                 },
    //             ],
    //         },
    //         options: {
    //             scales: {
    //                 y: {
    //                     beginAtZero: true,
    //                 },
    //             },
    //         },
    //     });
    // }

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
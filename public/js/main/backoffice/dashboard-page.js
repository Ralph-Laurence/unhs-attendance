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

const dailyAttendances = document.getElementById("employee-status");

new Chart(dailyAttendances, {
    type: 'bar',
    data: {
        labels: ['Early Entry', 'On Time', 'Late', 'Overtime', 'Undertime',],
        datasets: [{
            label: 'No. of Attendances',
            data: [12, 19, 3, 5, 2],
            // backgroundColor: [
            //     chartColors['primary'],
            //     chartColors['success']
            // ],
            backgroundColor : chartColors['warning_alphabg'],
            borderColor     : chartColors['warning'],
            barThickness    : 16,
            borderWidth     : 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

const dailyWorkHrs = document.getElementById("daily-work-hrs");

new Chart(dailyWorkHrs, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri',],
        datasets: [{
            label: 'Hours',
            data: [12, 19, 13, 15, 12],
            // backgroundColor: [
            //     chartColors['primary'],
            //     chartColors['success']
            // ],
            backgroundColor : chartColors['primary_alphabg'],
            borderColor     : chartColors['primary'],
            fill            : true,
            tension         : 0.1,
        }]
    }
});

let dashboardPage = (function() {

    let init = function() 
    {
        getEmployeeGraphings();
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
                cutout: '70%'
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
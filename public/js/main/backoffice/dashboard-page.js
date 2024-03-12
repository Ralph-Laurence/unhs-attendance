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

const employeesDiffCtx = document.getElementById("employees-diff");

new Chart(employeesDiffCtx, {
    type: 'doughnut',
    data: {
        labels: ['Faculty', 'Staff'],
        datasets: [{
            label: 'Count Diff.',
            data: [50, 100],
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
import datetime
import random
import calendar
import pytz

def seed(month):
    employeeIds = [i for i in range(1, 17)]
    attendanceTable = 'attendances'
    attendanceFields = ["emp_fk_id", "time_in", "lunch_start", "lunch_end", "time_out", "status", "duration", "undertime", "overtime", "late", "week_no", "created_at", "updated_at"]
    values = []

    year = 2024
    tz = pytz.timezone('Asia/Manila')
    for day in range(1, calendar.monthrange(year, month)[1] + 1):
        weekday = calendar.weekday(year, month, day)
        if weekday < 5:  # 0 is Monday, 6 is Sunday
            for emp_id in employeeIds:

                # Generate a random number of minutes from 390 (6:30 AM) to 480 (8:10 AM)
                minutes = random.randint(390, 490)
                
                # Convert minutes to hours and minutes
                hours, minutes = divmod(minutes, 60)
                
                # Create the datetime object
                time_in_naive = datetime.datetime(year, month, day, hours, minutes, random.randint(0, 59))
                time_in = tz.localize(time_in_naive)

                am_out_naive = datetime.datetime(year, month, day, 12, random.randint(0, 10), random.randint(0, 59))
                am_out     = tz.localize(am_out_naive)

                pm_in_naive = datetime.datetime(year, month, day, 13, 0, 0)
                pm_in       = tz.localize(pm_in_naive)

                time_out_naive  = datetime.datetime(year, month, day, random.randint(16, 17), random.randint(30, 59), random.randint(0, 59))
                time_out        = tz.localize(time_out_naive)

                duration        = str(time_out - time_in - (pm_in - am_out))
                late            = str(time_in - time_in.replace(hour=8, minute=0)) if time_in.hour > 8 or (time_in.hour == 8 and time_in.minute > 0) else '00:00:00'
                undertime       = str(time_in.replace(hour=16, minute=30) - time_out) if time_out.hour < 16 or (time_out.hour == 16 and time_out.minute < 30) else '00:00:00'
                overtime        = str(time_out - time_in.replace(hour=17, minute=30)) if time_out.hour > 17 or (time_out.hour == 17 and time_out.minute > 30) else '00:00:00'
                week_no         = time_in.isocalendar()[1]  # Get the week number of the year
                created_at      = updated_at = time_in.strftime('%Y-%m-%d %H:%M:%S')
                values.append((emp_id, created_at, am_out.strftime('%Y-%m-%d %H:%M:%S'), pm_in.strftime('%Y-%m-%d %H:%M:%S'), time_out.strftime('%Y-%m-%d %H:%M:%S'), 'Present', duration, undertime, overtime, late, week_no, created_at, updated_at))

    sql = f"INSERT INTO {attendanceTable} ({', '.join(attendanceFields)}) VALUES {', '.join(str(value) for value in values)}"
   
    #translate the month index into 3-letter month name
    monthName = calendar.month_abbr[month]

    with open(f"attendance_seeder_{monthName}.sql", 'w') as f:
        f.write(sql)


# Create the attendances
monthIndex = int(input("Attendance Month [1-12] >> "))
seed(monthIndex)
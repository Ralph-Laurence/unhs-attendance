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
                time_in_naive   = datetime.datetime(year, month, day, random.randint(7, 8), random.randint(0, 59), random.randint(0, 59))
                time_in         = tz.localize(time_in_naive)

                lunch_start_naive = datetime.datetime(year, month, day, 12, random.randint(0, 10), random.randint(0, 59))
                lunch_start     = tz.localize(lunch_start_naive)

                lunch_end_naive = datetime.datetime(year, month, day, 13, 0, 0)
                lunch_end       = tz.localize(lunch_end_naive)

                time_out_naive  = datetime.datetime(year, month, day, random.randint(16, 17), random.randint(30, 59), random.randint(0, 59))
                time_out        = tz.localize(time_out_naive)

                duration        = str(time_out - time_in - (lunch_end - lunch_start))
                late            = str(time_in - time_in.replace(hour=7, minute=30)) if time_in.hour > 7 or (time_in.hour == 7 and time_in.minute > 30) else '00:00:00'
                undertime       = str(time_in.replace(hour=16, minute=50) - time_out) if time_out.hour < 16 or (time_out.hour == 16 and time_out.minute < 50) else '00:00:00'
                overtime        = str(time_out - time_in.replace(hour=17, minute=30)) if time_out.hour > 17 or (time_out.hour == 17 and time_out.minute > 30) else '00:00:00'
                week_no         = (day - 1) // 7 + 1
                created_at      = updated_at = time_in.strftime('%Y-%m-%d %H:%M:%S')
                values.append((emp_id, created_at, lunch_start.strftime('%Y-%m-%d %H:%M:%S'), lunch_end.strftime('%Y-%m-%d %H:%M:%S'), time_out.strftime('%Y-%m-%d %H:%M:%S'), 'On Duty', duration, undertime, overtime, late, week_no, created_at, updated_at))

    sql = f"INSERT INTO {attendanceTable} ({', '.join(attendanceFields)}) VALUES {', '.join(str(value) for value in values)}"
   
    #translate the month index into 3-letter month name
    monthName = calendar.month_abbr[month]

    with open(f"attendance_seeder_{monthName}.sql", 'w') as f:
        f.write(sql)


# Create the attendances
monthIndex = int(input("Attendance Month [1-12] >> "))
seed(monthIndex)
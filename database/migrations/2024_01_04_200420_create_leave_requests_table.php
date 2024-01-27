<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLeaveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId(LeaveRequest::f_Emp_FK_ID)
                ->constrained(Employee::getTableName())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->date(LeaveRequest::f_StartDate);
            $table->date(LeaveRequest::f_EndDate);
            $table->string(LeaveRequest::f_Duration, 24);
            $table->string(LeaveRequest::f_LeaveType);
            $table->string(LeaveRequest::f_LeaveStatus, 16)->default(LeaveRequest::LEAVE_STATUS_PENDING);    // Approved | Rejected | Pending
            
            $defaultTimestamp = DB::raw('CURRENT_TIMESTAMP');

            $table->timestamp('created_at')->nullableTimestamps()->default($defaultTimestamp)->index();
            $table->timestamp('updated_at')->nullableTimestamps()->default($defaultTimestamp);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_requests');
    }
}

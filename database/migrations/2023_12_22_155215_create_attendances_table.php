<?php

use App\Models\Attendance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::create('attendances', function (Blueprint $table) 
        {
            $table->id();
            $table->integer(Attendance::f_Emp_FK_ID);
            $table->string(Attendance::f_TimeIn     ,24);
            $table->string(Attendance::f_LunchStart ,24)->nullable();
            $table->string(Attendance::f_LunchEnd   ,24)->nullable();
            $table->string(Attendance::f_TimeOut    ,24)->nullable();
            $table->string(Attendance::f_Status     ,24);
            $table->string(Attendance::f_Duration   ,24)->nullable();
            $table->string(Attendance::f_UnderTime  ,24)->nullable();
            $table->string(Attendance::f_OverTime   ,24)->nullable();
            $table->string(Attendance::f_Late       ,24)->nullable();

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
        Schema::dropIfExists('attendances');
    }
}

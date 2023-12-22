<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string(Employee::f_EmpNo,       32);
            $table->string(Employee::f_FirstName,   32);
            $table->string(Employee::f_MiddleName,  32);
            $table->string(Employee::f_LastName,    32);
            $table->string(Employee::f_Contact,     16);
            $table->string(Employee::f_Email,       64)->nullable();
            $table->tinyInteger(Employee::f_Position);
            $table->string(Employee::f_Photo);
            $table->string(Employee::f_QrData);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}

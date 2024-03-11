<?php

use App\Http\Utils\Constants;
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
        Schema::create(Employee::getTableName(), function (Blueprint $table) 
        {
            $table->id();
            $table->string(Employee::f_EmpNo,       32)->unique();
            $table->string(Employee::f_FirstName,   32);
            $table->string(Employee::f_MiddleName,  32);
            $table->string(Employee::f_LastName,    32);
            $table->string(Employee::f_Contact,     16)->nullable();
            $table->string(Employee::f_Email,       64)->nullable();
            $table->tinyInteger(Employee::f_Role);
            $table->integer(Employee::f_Rank          )->default(0);
            $table->string(Employee::f_Status,      16)->default(Employee::ON_STATUS_DUTY);
            $table->string(Employee::f_Photo          )->nullable();

            // Ideal PIN code length is between 4-12
            // PIN FLAG OFF -> PIN Disabled
            // PIN FLAG ON  -> PIN Enabled
            // PIN FLAG 2FA -> PIN Enabled for 2-Factor Auth (Scanner)

            $table->string(Employee::f_PINFlags,     8)->default(Constants::FLAG_OFF);
            $table->string(Employee::f_PINCode);

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

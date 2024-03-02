<?php

namespace Database\Seeders;

use App\Models\Employee;
use Hashids\Hashids;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeesSeeder extends Seeder
{
    private function generatePin($common = false) {

        if ($common === true)
        {
            return encrypt('1234');
        }

        return encrypt(random_int(1000, 9999));
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hash = new Hashids();

        $seed = [
            //
            //  TEACHERS
            //
            [
                Employee::f_EmpNo       => '00001',
                Employee::f_FirstName   => 'Volodymyr',
                Employee::f_MiddleName  => 'Oleksandrovych',
                Employee::f_LastName    => 'Zelenskyy',
                Employee::f_Contact     => '09100000001',
                Employee::f_Email       => 'zelenskyy@ukraini.ur',
                Employee::f_Position    => Employee::RoleTeacher,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
            [
                Employee::f_EmpNo       => '00002',
                Employee::f_FirstName   => 'Barrack',
                Employee::f_MiddleName  => 'Hussein',
                Employee::f_LastName    => 'Obama',
                Employee::f_Contact     => '09100000002',
                Employee::f_Email       => 'obama@washington.usa',
                Employee::f_Position    => Employee::RoleTeacher,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
            [
                Employee::f_EmpNo       => '00003',
                Employee::f_FirstName   => 'Kim',
                Employee::f_MiddleName  => 'Jong',
                Employee::f_LastName    => 'Un',
                Employee::f_Contact     => '09100000003',
                Employee::f_Email       => 'nuke@facility.nk',
                Employee::f_Position    => Employee::RoleTeacher,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
            [
                Employee::f_EmpNo       => '00004',
                Employee::f_FirstName   => 'Donald',
                Employee::f_MiddleName  => 'J',
                Employee::f_LastName    => 'Trump',
                Employee::f_Contact     => '09100000004',
                Employee::f_Email       => 'political@snowman.usa',
                Employee::f_Position    => Employee::RoleTeacher,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
            [
                Employee::f_EmpNo       => '00005',
                Employee::f_FirstName   => 'Vladimir',
                Employee::f_MiddleName  => 'V',
                Employee::f_LastName    => 'Putin',
                Employee::f_Contact     => '09100000005',
                Employee::f_Email       => 'ruskie@rusland.ru',
                Employee::f_Position    => Employee::RoleTeacher,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
            //
            // STAFFS
            //
            [
                Employee::f_EmpNo       => '00006',
                Employee::f_FirstName   => 'Jeff',
                Employee::f_MiddleName  => 'Preston',
                Employee::f_LastName    => 'Bezos',
                Employee::f_Contact     => '09100000006',
                Employee::f_Email       => 'jeff@amazon.com',
                Employee::f_Position    => Employee::RoleStaff,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
            [
                Employee::f_EmpNo       => '00007',
                Employee::f_FirstName   => 'Elon',
                Employee::f_MiddleName  => 'Reeves',
                Employee::f_LastName    => 'Musk',
                Employee::f_Contact     => '09100000007',
                Employee::f_Email       => 'elon@tesla.motors',
                Employee::f_Position    => Employee::RoleStaff,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
            [
                Employee::f_EmpNo       => '00008',
                Employee::f_FirstName   => 'Steve',
                Employee::f_MiddleName  => 'Paul',
                Employee::f_LastName    => 'Jobs',
                Employee::f_Contact     => '09100000008',
                Employee::f_Email       => 'apple@ios.os',
                Employee::f_Position    => Employee::RoleStaff,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
            [
                Employee::f_EmpNo       => '00009',
                Employee::f_FirstName   => 'Bill',
                Employee::f_MiddleName  => 'Henry',
                Employee::f_LastName    => 'Gates',
                Employee::f_Contact     => '09100000009',
                Employee::f_Email       => 'windows@microsoft.net',
                Employee::f_Position    => Employee::RoleStaff,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
            [
                Employee::f_EmpNo       => '00010',
                Employee::f_FirstName   => 'Mark',
                Employee::f_MiddleName  => 'Elliott',
                Employee::f_LastName    => 'Zuckerburg',
                Employee::f_Contact     => '09100000010',
                Employee::f_Email       => 'mark@fb.com',
                Employee::f_Position    => Employee::RoleStaff,
                Employee::f_Photo       => '',
                Employee::f_PINCode     => encrypt(random_int(1000, 9999)),
                'created_at'            => now(),
                'updated_at'            => now()
            ],
        ];

        DB::table(Employee::getTableName())->insert($seed);
    }
}

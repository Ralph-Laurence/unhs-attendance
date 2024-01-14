<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = base_path('database/seeders/sql/attendance-seed.sql'); // Adjust this path to match where your SQL file is located
        $sql = file_get_contents($path);
        DB::unprepared($sql);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'firstname'     => 'Paule',
                'lastname'      => 'Maglente',
                'username'      => 'sudo',
                'email'         => 'ptlangsakalam@gmail.com',
                'password'      => Hash::make('root'),
            ],
            [
                'firstname'     => 'Mark',
                'lastname'      => 'Cortes',
                'username'      => 'mark',
                'email'         => 'laramailer.dev@gmail.com',
                'password'      => Hash::make('1234'),
            ]
        ]);
    }
}

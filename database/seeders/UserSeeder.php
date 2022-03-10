<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $user = User::create([
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'firstname' => 'nirattasai',
            'lastname' => 'haree',
            'id_number' => '1499900266409',
            'telephone_number' => '0969587481',
            'email' => 'nirattasai@gmail.com',
        ]);
    }
}

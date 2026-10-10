<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@itsc.edu.do'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin1234'),
                'rol' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}

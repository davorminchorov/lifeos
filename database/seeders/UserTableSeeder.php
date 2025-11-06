<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'davor@lifeos.test'],
            [
                'name' => 'Davor Minchorov',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
    }
}

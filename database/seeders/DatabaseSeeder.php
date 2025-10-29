<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Carlos Vásquez',
            'email' => 'cvasquez857@gmail.com',
            'password' => Hash::make('12345678'),
            'telefono' => '12345678',
            'rol' => 'admin',
            'photo' => null,
        ]);

    }
}

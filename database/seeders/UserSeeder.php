<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@ekskulio.test',
            'username' => 'admin',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // ganti di production
            'role' => 'admin',
            'phone' => '080000000000',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);

        // Guru Kesiswaan
        User::create([
            'name' => 'Guru Kesiswaan',
            'email' => 'kesiswaan@ekskulio.test',
            'username' => 'kesiswaan',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'kesiswaan',
            'phone' => '081200000001',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);

        // Pembina Ekstrakurikuler
        User::create([
            'name' => 'Pembina',
            'email' => 'pembina@ekskulio.test',
            'username' => 'pembina',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'pembina',
            'phone' => '081200000002',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);
    }
}

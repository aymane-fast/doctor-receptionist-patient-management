<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a doctor
        User::create([
            'name' => 'Dr. John Smith',
            'email' => 'doctor@example.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '+1234567890',
            'address' => '123 Medical Center Dr, City, State 12345',
        ]);

        // Create a receptionist
        User::create([
            'name' => 'Sarah Johnson',
            'email' => 'receptionist@example.com',
            'password' => Hash::make('password'),
            'role' => 'receptionist',
            'phone' => '+1234567891',
            'address' => '456 Front Desk Ave, City, State 12345',
        ]);

        // Create another doctor
        User::create([
            'name' => 'Dr. Emily Davis',
            'email' => 'doctor2@example.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '+1234567892',
            'address' => '789 Healthcare Blvd, City, State 12345',
        ]);
    }
}

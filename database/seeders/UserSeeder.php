<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@barbershop.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create Barber Users
        User::create([
            'name' => 'Ahmad Rizki',
            'email' => 'ahmad@barbershop.com',
            'password' => Hash::make('password123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@barbershop.com',
            'password' => Hash::make('password123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Roni Wilianto',
            'email' => 'roni@barbershop.com',
            'password' => Hash::make('password123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        // Create Customer Users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Mike Johnson',
            'email' => 'mike@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
    }
}

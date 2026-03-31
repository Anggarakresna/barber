<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;

class BarberSeeder extends Seeder
{
    public function run(): void
    {
        $barbers = [
            ['email' => 'ahmad@barbershop.com', 'phone' => '081234567890', 'experience' => 5],
            ['email' => 'budi@barbershop.com',  'phone' => '081234567891', 'experience' => 3],
            ['email' => 'roni@barbershop.com',  'phone' => '081234567892', 'experience' => 4],
        ];

        foreach ($barbers as $data) {
            $user = User::where('email', $data['email'])->first();
            if ($user) {
                Barber::create([
                    'user_id' => $user->id,
                    'phone' => $data['phone'],
                    'experience' => $data['experience'],
                ]);
            }
        }
    }
}

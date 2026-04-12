<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Classic Haircut',      'price' => 50000, 'duration' => 30],
            ['name' => 'Fade Haircut',         'price' => 60000, 'duration' => 35],
            ['name' => 'Beard Grooming',       'price' => 40000, 'duration' => 25],
            ['name' => 'Hair Coloring',        'price' => 100000, 'duration' => 60],
            ['name' => 'Hair Wash & Massage',  'price' => 35000, 'duration' => 20],
            ['name' => 'Combo Package',        'price' => 120000, 'duration' => 80],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                [
                    'price' => $service['price'],
                    'duration' => $service['duration'],
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Comptes de démo
        User::updateOrCreate(
            ['email' => 'commercant@demo.fr'],
            [
                'name' => 'Sophie Martin',
                'password' => Hash::make('password'),
                'role' => 'merchant',
            ]
        );

        User::updateOrCreate(
            ['email' => 'client@demo.fr'],
            [
                'name' => 'Lucas Bernard',
                'password' => Hash::make('password'),
                'role' => 'client',
            ]
        );

        // Services
        $services = [
            ['name' => 'Coupe homme',      'description' => 'Coupe et coiffage',          'duration_minutes' => 30, 'price_cents' => 2200],
            ['name' => 'Coupe femme',      'description' => 'Shampoing, coupe, brushing', 'duration_minutes' => 60, 'price_cents' => 4500],
            ['name' => 'Coloration',       'description' => 'Coloration complète',        'duration_minutes' => 90, 'price_cents' => 6500],
            ['name' => 'Barbe',            'description' => 'Taille et soin de la barbe', 'duration_minutes' => 20, 'price_cents' => 1500],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['name' => $service['name']], $service);
        }

        // Horaires : mardi à samedi, dimanche et lundi fermés
        $hours = [
            1 => ['closed' => true],
            2 => ['open' => '09:00', 'close' => '18:30'],
            3 => ['open' => '09:00', 'close' => '18:30'],
            4 => ['open' => '09:00', 'close' => '18:30'],
            5 => ['open' => '09:00', 'close' => '19:00'],
            6 => ['open' => '09:00', 'close' => '17:00'],
            7 => ['closed' => true],
        ];

        foreach ($hours as $day => $h) {
            BusinessHour::updateOrCreate(
                ['day_of_week' => $day],
                [
                    'open_time'  => $h['open'] ?? null,
                    'close_time' => $h['close'] ?? null,
                    'is_closed'  => $h['closed'] ?? false,
                ]
            );
        }
    }
}

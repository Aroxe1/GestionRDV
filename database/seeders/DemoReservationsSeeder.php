<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use App\Services\SlotGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoReservationsSeeder extends Seeder
{
    public function run(): void
    {
        $generator = app(SlotGenerator::class);

        // Clients de démo (client@demo.fr existe déjà dans DemoSeeder)
        $clients = collect([
            ['Lucas Bernard', 'client@demo.fr'],
            ['Emma Dubois',   'emma@demo.fr'],
            ['Hugo Lefèvre',  'hugo@demo.fr'],
            ['Chloé Moreau',  'chloe@demo.fr'],
            ['Nathan Petit',  'nathan@demo.fr'],
        ])->map(fn ($c) => User::updateOrCreate(
            ['email' => $c[1]],
            ['name' => $c[0], 'password' => Hash::make('password'), 'role' => 'client']
        ));

        $services = Service::all()->keyBy('name');

        // Modèle de semaine : jour ISO (2 = mardi ... 6 = samedi)
        // [heure, service, index du client]
        $template = [
            2 => [['09:30', 'Coupe femme', 1], ['11:00', 'Coupe homme', 0], ['14:00', 'Coloration', 2]],
            3 => [['10:00', 'Coupe homme', 3], ['15:00', 'Coupe femme', 1], ['16:30', 'Barbe', 4]],
            4 => [['09:00', 'Coloration', 2],  ['11:30', 'Coupe homme', 0], ['14:30', 'Coupe femme', 3]],
            5 => [['10:00', 'Coupe femme', 4], ['13:30', 'Coupe homme', 1], ['17:00', 'Barbe', 0]],
            6 => [['09:30', 'Coupe homme', 2], ['11:00', 'Coupe femme', 3], ['14:00', 'Coloration', 1]],
        ];

        // De 7 jours dans le passé à 13 jours dans le futur
        for ($i = -7; $i <= 13; $i++) {
            $day = now()->startOfDay()->addDays($i);

            foreach ($template[$day->dayOfWeekIso] ?? [] as [$time, $serviceName, $clientIndex]) {
                $service = $services[$serviceName];
                $start   = $day->copy()->setTimeFromTimeString($time);
                $end     = $start->copy()->addMinutes($service->duration_minutes);

                // On n'écrase jamais une réservation existante
                if (! $generator->isSlotFree($start, $end)) {
                    continue;
                }

                Reservation::create([
                    'user_id'    => $clients[$clientIndex]->id,
                    'service_id' => $service->id,
                    'starts_at'  => $start,
                    'ends_at'    => $end,
                    'status'     => 'confirmed',
                ]);
            }
        }
    }
}

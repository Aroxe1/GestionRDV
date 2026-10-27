<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use App\Services\SlotGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private Service $coupe;   // 30 min
    private Service $femme;   // 60 min
    private User $client;

    // Exécutée avant chaque test : prépare un petit salon
    protected function setUp(): void
    {
        parent::setUp();

        $this->coupe = Service::create(['name' => 'Coupe homme', 'duration_minutes' => 30, 'price_cents' => 2200]);
        $this->femme = Service::create(['name' => 'Coupe femme', 'duration_minutes' => 60, 'price_cents' => 4500]);

        BusinessHour::create(['day_of_week' => 1, 'is_closed' => true]);
        BusinessHour::create(['day_of_week' => 2, 'open_time' => '09:00', 'close_time' => '12:00']);

        $this->client = User::factory()->create();
    }

    private function book(Service $service, Carbon $start, string $status = 'confirmed'): Reservation
    {
        return Reservation::create([
            'user_id'    => $this->client->id,
            'service_id' => $service->id,
            'starts_at'  => $start,
            'ends_at'    => $start->copy()->addMinutes($service->duration_minutes),
            'status'     => $status,
        ]);
    }

    public function test_un_jour_ferme_ne_renvoie_aucun_creneau(): void
    {
        $lundi = Carbon::now()->next(Carbon::MONDAY);

        $slots = app(SlotGenerator::class)->availableSlots($this->coupe, $lundi);

        $this->assertSame([], $slots);
    }

    public function test_un_service_d_une_heure_est_bloque_par_un_rendez_vous_voisin(): void
    {
        $mardi = Carbon::now()->next(Carbon::TUESDAY);
        $this->book($this->coupe, $mardi->copy()->setTime(10, 0)); // 10:00 - 10:30

        $slots = app(SlotGenerator::class)->availableSlots($this->femme, $mardi);

        $this->assertContains('09:00', $slots);     // 09:00-10:00 : finit pile au début du rendez-vous
        $this->assertNotContains('09:30', $slots);  // 09:30-10:30 : chevauche
        $this->assertNotContains('10:00', $slots);  // pris
        $this->assertContains('10:30', $slots);     // 10:30-11:30 : libre
    }

    public function test_une_reservation_annulee_libere_le_creneau(): void
    {
        $mardi = Carbon::now()->next(Carbon::TUESDAY);
        $this->book($this->coupe, $mardi->copy()->setTime(10, 0), 'cancelled');

        $slots = app(SlotGenerator::class)->availableSlots($this->coupe, $mardi);

        $this->assertContains('10:00', $slots);
    }
}

<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Service::create(['name' => 'Coupe homme', 'duration_minutes' => 30, 'price_cents' => 2200]);
        BusinessHour::create(['day_of_week' => 2, 'open_time' => '09:00', 'close_time' => '12:00']);
    }

    private function payload(): array
    {
        return [
            'service_id' => $this->service->id,
            'date'       => Carbon::now()->next(Carbon::TUESDAY)->toDateString(),
            'time'       => '10:00',
        ];
    }

    public function test_un_client_connecte_peut_reserver_un_creneau(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)->post('/reserver', $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reservations', [
            'user_id'    => $client->id,
            'service_id' => $this->service->id,
            'status'     => 'confirmed',
        ]);
    }

    public function test_un_creneau_deja_pris_ne_peut_pas_etre_reserve_deux_fois(): void
    {
        $premier = User::factory()->create();
        $second  = User::factory()->create();

        $this->actingAs($premier)->post('/reserver', $this->payload());

        $this->actingAs($second)->post('/reserver', $this->payload())
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_un_client_ne_peut_pas_annuler_la_reservation_d_un_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus       = User::factory()->create();
        $start        = Carbon::now()->next(Carbon::TUESDAY)->setTime(10, 0);

        $reservation = Reservation::create([
            'user_id'    => $proprietaire->id,
            'service_id' => $this->service->id,
            'starts_at'  => $start,
            'ends_at'    => $start->copy()->addMinutes(30),
            'status'     => 'confirmed',
        ]);

        $this->actingAs($intrus)->patch(route('booking.cancel', $reservation))
            ->assertForbidden();

        $this->assertSame('confirmed', $reservation->fresh()->status);
    }

    public function test_le_planning_est_reserve_au_commercant(): void
    {
        $this->withoutVite(); // évite de dépendre des fichiers CSS/JS compilés

        $client   = User::factory()->create();
        $merchant = User::factory()->create(['role' => 'merchant']);

        $this->actingAs($client)->get('/commercant/planning')->assertForbidden();
        $this->actingAs($merchant)->get('/commercant/planning')->assertOk();
    }
}

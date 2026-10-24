<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MerchantPlanningController extends Controller
{
    public function index()
    {
        return view('merchant.planning');
    }

    // Appelé par le calendrier pour charger les rendez-vous de la période affichée
    public function events(Request $request)
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date'],
        ]);

        $start = Carbon::parse($request->query('start'));
        $end   = Carbon::parse($request->query('end'));

        $reservations = Reservation::with(['user', 'service'])
            ->where('status', 'confirmed')
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->get();

        return response()->json($reservations->map(fn ($r) => [
            'id'    => $r->id,
            'title' => $r->service->name . ' · ' . $r->user->name,
            'start' => $r->starts_at->toIso8601String(),
            'end'   => $r->ends_at->toIso8601String(),
            'extendedProps' => [
                'cancel_url' => route('merchant.cancel', $r),
            ],
        ]));
    }

    public function cancel(Reservation $reservation)
    {
        $reservation->update(['status' => 'cancelled']);

        return response()->json(['ok' => true]);
    }
}

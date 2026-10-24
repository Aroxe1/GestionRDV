<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Service;
use App\Services\SlotGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function create(Request $request, SlotGenerator $generator)
    {
        $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'date'       => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $services = Service::where('is_active', true)->orderBy('name')->get();
        $service  = $services->firstWhere('id', (int) $request->query('service_id'));
        $date     = $request->query('date');
        $slots    = [];

        if ($service && $date) {
            $slots = $generator->availableSlots($service, Carbon::parse($date));
        }

        return view('booking.create', compact('services', 'service', 'date', 'slots'));
    }

    public function store(Request $request, SlotGenerator $generator)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'date'       => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time'       => ['required', 'date_format:H:i'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $start   = Carbon::parse($data['date'] . ' ' . $data['time']);
        $end     = $start->copy()->addMinutes($service->duration_minutes);

        $reservation = DB::transaction(function () use ($generator, $service, $start, $end, $request, $data) {
            // 1. Le créneau doit faire partie de ceux proposés (horaires, jour ouvert, futur)
            if (! in_array($data['time'], $generator->availableSlots($service, $start), true)) {
                return null;
            }

            // 2. Dernière vérification juste avant d'enregistrer
            if (! $generator->isSlotFree($start, $end)) {
                return null;
            }

            return Reservation::create([
                'user_id'    => $request->user()->id,
                'service_id' => $service->id,
                'starts_at'  => $start,
                'ends_at'    => $end,
                'status'     => 'confirmed',
            ]);
        });

        if (! $reservation) {
            return back()
                ->withErrors(['time' => "Ce créneau n'est plus disponible, merci d'en choisir un autre."])
                ->withInput();
        }

        return redirect()
            ->route('booking.index')
            ->with('success', "Réservation confirmée : {$service->name} le {$start->translatedFormat('l j F à H:i')}.");
    }

    public function index(Request $request){
        $now = now();

        $upcoming = $request->user()->reservations()
            ->with('service')
            ->where('status', 'confirmed')
            ->where('starts_at', '>=', $now)
            ->orderBy('starts_at')
            ->get();

        $past = $request->user()->reservations()
            ->with('service')
            ->where(fn ($q) => $q->where('status', '!=', 'confirmed')->orWhere('starts_at', '<', $now))
            ->orderByDesc('starts_at')
            ->limit(10)
            ->get();

        return view('booking.index', compact('upcoming', 'past'));
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        // Un client ne peut annuler que ses propres réservations
        abort_unless($reservation->user_id === $request->user()->id, 403);

        if ($reservation->status !== 'confirmed' || $reservation->starts_at->isPast()) {
            return back()->withErrors(['cancel' => "Cette réservation ne peut plus être annulée."]);
        }

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Réservation annulée.');
    }
}

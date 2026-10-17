<?php

namespace App\Services;

use App\Models\BusinessHour;
use App\Models\Reservation;
use App\Models\Service;
use Carbon\CarbonInterface;

class SlotGenerator
{
    // Un créneau est proposé toutes les 30 minutes
    private const STEP_MINUTES = 30;

    /**
     * Retourne la liste des heures de début disponibles ("09:00", "09:30"...)
     */
    public function availableSlots(Service $service, CarbonInterface $date): array
    {
        $hours = BusinessHour::where('day_of_week', $date->dayOfWeekIso)->first();

        if (! $hours || $hours->is_closed || ! $hours->open_time || ! $hours->close_time) {
            return [];
        }

        $day   = $date->copy()->startOfDay();
        $open  = $day->copy()->setTimeFromTimeString($hours->open_time);
        $close = $day->copy()->setTimeFromTimeString($hours->close_time);

        // Réservations confirmées qui touchent cette journée d'ouverture
        $reservations = Reservation::where('status', 'confirmed')
            ->where('starts_at', '<', $close)
            ->where('ends_at', '>', $open)
            ->get(['starts_at', 'ends_at']);

        $slots  = [];
        $cursor = $open->copy();

        while ($cursor->copy()->addMinutes($service->duration_minutes)->lte($close)) {
            $end = $cursor->copy()->addMinutes($service->duration_minutes);

            $isFuture = $cursor->isFuture();
            $isFree   = $reservations->every(
                fn ($r) => ! ($r->starts_at < $end && $r->ends_at > $cursor)
            );

            if ($isFuture && $isFree) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes(self::STEP_MINUTES);
        }

        return $slots;
    }

    /**
     * Vérifie qu'une plage est libre (servira au moment de réserver)
     */
    public function isSlotFree(CarbonInterface $start, CarbonInterface $end): bool
    {
        return !Reservation::where('status', 'confirmed')
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();
    }
}

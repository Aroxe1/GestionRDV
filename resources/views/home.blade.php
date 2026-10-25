<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · Réservation en ligne</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-stone-800 antialiased">

{{-- En-tête --}}
<header class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
    <span class="font-semibold text-lg">{{ config('app.name') }}</span>
    <nav class="flex items-center gap-4 text-sm">
        @auth
            <a href="{{ route('booking.index') }}" class="hover:underline">Mes réservations</a>
            <a href="{{ route('booking.create') }}" class="px-4 py-2 bg-stone-900 text-white rounded-md">Réserver</a>
        @else
            <a href="{{ route('login') }}" class="hover:underline">Connexion</a>
            <a href="{{ route('register') }}" class="px-4 py-2 bg-stone-900 text-white rounded-md">Créer un compte</a>
        @endauth
    </nav>
</header>

{{-- Présentation --}}
<section class="max-w-5xl mx-auto px-4 py-12 sm:py-20 text-center">
    <h1 class="text-3xl sm:text-5xl font-bold tracking-tight">Réservez votre coiffure en ligne</h1>
    <p class="mt-4 text-lg text-stone-600 max-w-2xl mx-auto">
        Choisissez votre prestation, voyez les créneaux libres et confirmez en moins d'une minute, à toute heure.
    </p>
    <a href="{{ route('booking.create') }}"
       class="inline-block mt-8 px-6 py-3 bg-stone-900 text-white rounded-md text-lg hover:bg-stone-700">
        Réserver un rendez-vous
    </a>
</section>

{{-- Services --}}
<section class="max-w-5xl mx-auto px-4 py-8">
    <h2 class="text-2xl font-semibold mb-6">Nos prestations</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($services as $service)
            <div class="bg-white p-5 rounded-lg shadow-sm border border-stone-200">
                <h3 class="font-semibold">{{ $service->name }}</h3>
                <p class="mt-1 text-sm text-stone-600">{{ $service->description }}</p>
                <p class="mt-4 text-sm text-stone-500">{{ $service->duration_minutes }} min</p>
                <p class="text-xl font-semibold">{{ number_format($service->price_cents / 100, 2, ',', ' ') }} €</p>
            </div>
        @endforeach
    </div>
</section>

{{-- Horaires --}}
@php
    $days = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
@endphp
<section class="max-w-5xl mx-auto px-4 py-12">
    <h2 class="text-2xl font-semibold mb-6">Horaires d'ouverture</h2>
    <div class="bg-white rounded-lg shadow-sm border border-stone-200 max-w-md divide-y">
        @foreach ($hours as $h)
            <div class="flex justify-between px-5 py-3">
                <span>{{ $days[$h->day_of_week] }}</span>
                <span class="text-stone-600">
                        @if ($h->is_closed)
                        Fermé
                    @else
                        {{ substr($h->open_time, 0, 5) }} – {{ substr($h->close_time, 0, 5) }}
                    @endif
                    </span>
            </div>
        @endforeach
    </div>
</section>

<footer class="max-w-5xl mx-auto px-4 py-8 text-sm text-stone-500">
    © {{ date('Y') }} {{ config('app.name') }} · Application de démonstration
</footer>
</body>
</html>

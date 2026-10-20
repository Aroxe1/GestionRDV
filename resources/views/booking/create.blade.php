<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Réserver un rendez-vous</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            {{-- Étape 1 : prestation et date --}}
            <form method="GET" action="{{ route('booking.create') }}" class="bg-white p-6 shadow sm:rounded-lg space-y-4">
                <div>
                    <label for="service_id" class="block text-sm font-medium text-gray-700">Prestation</label>
                    <select name="service_id" id="service_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Choisir…</option>
                        @foreach ($services as $s)
                            <option value="{{ $s->id }}" @selected($service?->id === $s->id)>
                                {{ $s->name }} · {{ $s->duration_minutes }} min · {{ number_format($s->price_cents / 100, 2, ',', ' ') }} €
                            </option>
                        @endforeach
                    </select>
                    @error('service_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" name="date" id="date" required min="{{ now()->toDateString() }}"
                           value="{{ $date }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                    Voir les créneaux
                </button>
            </form>

            {{-- Étape 2 : choix du créneau --}}
            @if ($service && $date)
                <form method="POST" action="{{ route('booking.store') }}" class="bg-white p-6 shadow sm:rounded-lg space-y-4">
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                    <input type="hidden" name="date" value="{{ $date }}">

                    <h3 class="font-medium text-gray-900">
                        Créneaux disponibles le {{ \Carbon\Carbon::parse($date)->translatedFormat('l j F') }}
                    </h3>

                    @if (count($slots))
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                            @foreach ($slots as $slot)
                                <label class="cursor-pointer">
                                    <input type="radio" name="time" value="{{ $slot }}" required class="peer sr-only">
                                    <span class="block text-center py-2 border rounded-md peer-checked:bg-gray-800 peer-checked:text-white hover:bg-gray-100">
                                        {{ $slot }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('time') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-500">
                            Confirmer la réservation
                        </button>
                    @else
                        <p class="text-gray-600">Aucun créneau disponible ce jour-là (salon fermé ou complet). Essaie une autre date.</p>
                    @endif
                </form>
            @endif
        </div>
    </div>
</x-app-layout>

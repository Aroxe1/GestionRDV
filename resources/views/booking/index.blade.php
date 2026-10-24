<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes réservations</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @error('cancel')
            <div class="p-4 bg-red-100 text-red-800 rounded-lg">{{ $message }}</div>
            @enderror

            <div class="bg-white p-6 shadow sm:rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900">À venir</h3>
                    <a href="{{ route('booking.create') }}" class="text-sm text-gray-600 underline">Nouvelle réservation</a>
                </div>

                @forelse ($upcoming as $r)
                    <div class="flex items-center justify-between py-3 border-t first:border-t-0">
                        <div>
                            <p class="font-medium">{{ $r->service->name }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $r->starts_at->translatedFormat('l j F Y, H:i') }}
                                · {{ number_format($r->service->price_cents / 100, 2, ',', ' ') }} €
                            </p>
                        </div>
                        <form method="POST" action="{{ route('booking.cancel', $r) }}"
                              onsubmit="return confirm('Annuler ce rendez-vous ?')">
                            @csrf
                            @method('PATCH')
                            <button class="px-3 py-1 text-sm text-red-700 border border-red-300 rounded-md hover:bg-red-50">
                                Annuler
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-600">Aucun rendez-vous à venir.</p>
                @endforelse
            </div>

            @if ($past->isNotEmpty())
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-4">Historique</h3>
                    @foreach ($past as $r)
                        <div class="flex items-center justify-between py-3 border-t first:border-t-0 text-gray-500">
                            <div>
                                <p>{{ $r->service->name }}</p>
                                <p class="text-sm">{{ $r->starts_at->translatedFormat('j F Y, H:i') }}</p>
                            </div>
                            <span class="text-sm">{{ $r->status === 'cancelled' ? 'Annulée' : 'Passée' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

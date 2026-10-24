<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Planning du salon</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg">
                <div id="calendar"></div>
            </div>
            <p class="mt-3 text-sm text-gray-500">Clique sur un rendez-vous pour l'annuler.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                locale: 'fr',
                initialView: 'timeGridWeek',
                firstDay: 1,
                hiddenDays: [0, 1],            // dimanche et lundi : salon fermé
                slotMinTime: '09:00:00',
                slotMaxTime: '19:30:00',
                allDaySlot: false,
                nowIndicator: true,
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay,listWeek',
                },
                events: @json(route('merchant.events')),
                eventClick: async (info) => {
                    const e = info.event;
                    if (!confirm(`Annuler « ${e.title} » ?`)) return;

                    const response = await fetch(e.extendedProps.cancel_url, {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    });

                    if (response.ok) {
                        calendar.refetchEvents();
                    } else {
                        alert("L'annulation a échoué.");
                    }
                },
            });

            calendar.render();
        });
    </script>
</x-app-layout>

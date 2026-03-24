<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $this->monthLabel }}</h2>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ $this->previousMonthUrl }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    ← Mes anterior
                </a>
                <a href="{{ $this->currentMonthUrl }}" class="inline-flex items-center rounded-lg border border-primary-500 px-3 py-2 text-sm font-medium text-primary-600 transition hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-950/30">
                    Mes actual
                </a>
                <a href="{{ $this->nextMonthUrl }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    Mes siguiente →
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->days as $day)
                <div @class([
                    'rounded-2xl border p-4 shadow-sm transition',
                    'border-primary-500 ring-1 ring-primary-400/30' => $day['is_today'],
                    'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' => ! $day['is_today'],
                ])>
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $day['day'] }}</span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $day['weekday'] }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $day['full_label'] }}</p>
                        </div>

                        @if ($day['is_today'])
                            <span class="rounded-full bg-primary-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-primary-700 dark:bg-primary-950/40 dark:text-primary-300">
                                Hoy
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2">
                        @foreach ($day['doctors'] as $doctor)
                            <div class="rounded-xl border border-gray-200/80 bg-gray-50/80 p-3 dark:border-gray-800 dark:bg-gray-950/50">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $doctor['name'] }}</p>
                                        @if ($doctor['schedule'])
                                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">Disponible: {{ $doctor['schedule'] }}</p>
                                        @else
                                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Sin horario</p>
                                        @endif
                                    </div>
                                    <span @class([
                                        'rounded-full px-2 py-1 text-[10px] font-semibold whitespace-nowrap',
                                        'bg-success-50 text-success-700 dark:bg-success-950/30 dark:text-success-300' => $doctor['appointments'] === 0,
                                        'bg-warning-50 text-warning-700 dark:bg-warning-950/30 dark:text-warning-300' => $doctor['appointments'] > 0,
                                    ])>
                                        {{ $doctor['appointments'] }} cita{{ $doctor['appointments'] === 1 ? '' : 's' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>

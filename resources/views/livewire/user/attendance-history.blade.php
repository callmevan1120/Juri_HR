<div class="space-y-4">
    <div class="user-page-toolbar">
        <div class="min-w-0">
            <p class="text-[0.68rem] font-bold uppercase tracking-[0.24em] text-primary-600 dark:text-primary-300">
                {{ __('Calendar') }}
            </p>
            <h3 class="mt-1 text-xl font-semibold tracking-tight text-slate-950 dark:text-white">
                {{ $displayMonth->translatedFormat('F Y') }}
            </h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                {{ __('Working Days') }}: {{ $workingDaysCount }}
            </p>
        </div>

        <div class="flex items-center gap-2">
                <div class="w-24 sm:w-28">
                    <x-forms.tom-select id="selectedMonth" wire:model.live="selectedMonth" placeholder="{{ __('Month') }}"
                        :options="collect(range(1, 12))->map(fn($m) => ['id' => sprintf('%02d', $m), 'name' => Carbon\Carbon::create()->month($m)->translatedFormat('F')])->values()->toArray()" />
                </div>
                <div class="w-20">
                    <x-forms.tom-select id="selectedYear" wire:model.live="selectedYear" placeholder="{{ __('Year') }}"
                        :options="collect(range(date('Y') - 5, date('Y') + 1))->map(fn($y) => ['id' => $y, 'name' => $y])->values()->toArray()" />
                </div>
        </div>
    </div>

    <div class="user-list-card">
            {{-- Days Header --}}
            <div class="mb-2 grid grid-cols-[repeat(7,minmax(0,1fr))]">
                @foreach ([__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')] as $index => $day)
                    <div class="py-1 text-center text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500 {{ $index === 0 ? 'text-rose-500' : ($index === 5 ? 'text-emerald-600 dark:text-emerald-500' : '') }}">
                        {{ $day }}
                    </div>
                @endforeach
            </div>

            {{-- Calendar Dates --}}
            <div class="grid grid-cols-[repeat(7,minmax(0,1fr))] gap-1 sm:gap-2">
                @foreach ($dates as $date)
                    @php
                        $isCurrentMonth = $date->month == $currentMonth;
                        $isToday = $date->isToday();
                        $isWeekend = $date->isWeekend();
                        
                        // Check if this date is a holiday
                        $dateKey = $date->format('Y-m-d');
                        $holiday = $holidays[$dateKey] ?? null;
                        $isHoliday = !is_null($holiday);
                        
                        // Find attendance
                        $attendance = $attendances->firstWhere(fn($v, $k) => $v->date->isSameDay($date));
                        $status = ($attendance ?? [
                            'status' => $isWeekend || $isHoliday || !$date->isPast() ? '-' : 'absent',
                        ])['status'];

                        // Styles (Clean)
                        $bgClass = $isCurrentMonth ? 'bg-white/68 dark:bg-slate-950/34' : 'bg-slate-100/48 dark:bg-slate-950/20 opacity-45';
                        $textClass = $isCurrentMonth ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600';
                        $borderClass = $isToday ? 'ring-2 ring-primary-500 z-10 border border-primary-200 dark:border-primary-800' : ($isCurrentMonth ? 'border border-slate-200/70 dark:border-slate-800/80' : 'border border-transparent');
                        
                        // Holiday styling
                        if ($isHoliday && $isCurrentMonth) {
                            $bgClass = 'bg-rose-50/80 dark:bg-rose-950/18';
                            $textClass = 'text-rose-600 dark:text-rose-400';
                            $borderClass = $isToday ? 'ring-2 ring-primary-500 z-10 border border-primary-200 dark:border-primary-800' : 'border border-rose-100 dark:border-rose-900/30';
                        } elseif ($date->isSunday() && $isCurrentMonth) {
                            $textClass = 'text-rose-500 dark:text-rose-400';
                        } elseif ($date->isFriday() && $isCurrentMonth) {
                            $textClass = 'text-emerald-600 dark:text-emerald-400';
                        }

                        // Status Marker
                        $markerColor = match($status) {
                            'present' => 'bg-emerald-500',
                            'late' => 'bg-amber-500',
                            'excused', 'sick', 'permission', 'leave' => match($attendance['approval_status'] ?? 'approved') {
                                'pending' => 'bg-amber-300',
                                'rejected' => 'bg-rose-500',
                                default => match($status) {
                                    'excused' => 'bg-sky-500',
                                    'sick' => 'bg-violet-500',
                                    'permission' => 'bg-teal-500',
                                    'leave' => 'bg-indigo-500',
                                    default => 'bg-gray-400'
                                }
                            },
                            'rejected' => 'bg-rose-500',
                            'absent' => 'bg-red-700', // Dark Red
                            default => $isToday ? 'bg-primary-500' : null
                        };
                    @endphp

                    <div class="aspect-[1/1] sm:aspect-[4/3] group relative">
                        <button type="button"
                            @if($attendance) wire:click="show({{ $attendance['id'] }})" @endif
                            class="flex h-full w-full flex-col items-center justify-between rounded-[0.9rem] p-1 transition duration-200 {{ $bgClass }} {{ $textClass }} {{ $borderClass }} hover:bg-white/80 dark:hover:bg-slate-900/70 {{ $attendance ? 'cursor-pointer' : 'cursor-default' }}">
                            
                            {{-- Holiday Indicator --}}
                            @if($isHoliday && $isCurrentMonth)
                                <span class="absolute top-1 right-1 text-[6px] text-rose-500">●</span>
                            @endif
                            
                            {{-- Date Number --}}
                            <span class="text-xs font-bold leading-none mt-1">
                                {{ $date->day }}
                            </span>
                            
                            {{-- Holiday Name (visible on desktop) --}}
                            {{-- Removed as per simplification --}}

                            {{-- Status Indicator --}}
                            <div class="mb-1 h-3 flex items-center justify-center">
                                @if($markerColor && $status !== '-')
                                    <span class="inline-flex h-1.5 w-1.5 rounded-full {{ $markerColor }}"></span>
                                @elseif($isHoliday && $isCurrentMonth)
                                     <span class="text-[8px] leading-none text-rose-500">{{ __('Holiday initial') }}</span>
                                @endif
                                
                                @if($attendance && isset($attendance['time_in']) && !$isHoliday)
                                    <span class="hidden sm:inline-block ml-1 text-[8px] text-gray-400 font-mono leading-none">
                                        {{ \Carbon\Carbon::parse($attendance['time_in'])->format('H:i') }}
                                    </span>
                                @endif
                            </div>
                        </button>
                    </div>
                @endforeach
            </div>
    </div>
        
        {{-- Holidays List --}}
        @if($holidays->isNotEmpty())
        <div class="user-soft-panel">
            <h4 class="mb-2 text-[10px] font-bold uppercase tracking-[0.16em] text-red-600 dark:text-red-400">
                {{ __('Holidays this Month') }}
            </h4>
            <div class="flex flex-wrap gap-2">
                @foreach($holidays->sortBy(fn($h) => $h->date->day) as $holiday)
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-red-100 bg-red-50/70 px-2.5 py-1 text-[10px] text-red-700 shadow-none dark:border-rose-900/40 dark:bg-rose-950/24 dark:text-red-300">
                        <span class="font-bold">{{ $holiday->date->day }}</span>
                        <span class="opacity-75">{{ $holiday->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
        {{-- Stats Grid (Compact) --}}
        <div>
            <h4 class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                {{ __('Summary') }}
            </h4>
            <div class="user-stat-strip grid grid-cols-2 gap-1.5 sm:grid-cols-5">
                @foreach([
                    ['label' => 'Present', 'key' => 'present', 'color' => 'text-emerald-600'],
                    ['label' => 'Late', 'key' => 'late', 'color' => 'text-amber-600'],
                    ['label' => 'Excused', 'key' => 'excused', 'color' => 'text-sky-600'],
                    ['label' => 'Sick', 'key' => 'sick', 'color' => 'text-violet-600'],
                    ['label' => 'Absent', 'key' => 'absent', 'color' => 'text-red-700']
                ] as $stat)
                <div class="user-stat-pill {{ $stat['key'] === 'absent' ? 'col-span-2 sm:col-span-1' : '' }}">
                    <p class="text-xl font-bold {{ $stat['color'] }} dark:text-white">{{ $counts[$stat['key']] ?? 0 }}</p>
                    <p class="text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400 mt-1">{{ __($stat['label']) }}</p>
                </div>
                @endforeach
            </div>
        </div>
        
        {{-- Legenda Modern --}}
        <div class="user-soft-panel">
            <div class="flex flex-wrap justify-center gap-4 text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-amber-400"></span> {{ __('Pending') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span> {{ __('Rejected') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-primary-500"></span> {{ __('Today') }}
                </span>
            </div>
        </div>

    {{-- Include Modal Component --}}
    <x-shared.attendance-detail-modal :current-attendance="$currentAttendance" />

    @stack('attendance-detail-scripts')
</div>

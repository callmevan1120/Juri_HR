<div class="user-content-panel">

    {{-- Header --}}
    <div class="user-content-panel__header">
        <h3 class="user-content-panel__title">
            <span class="user-content-panel__mark">
                <x-heroicon-o-calendar-days class="h-4 w-4" />
            </span>
            {{ __('Upcoming Events') }}
        </h3>
    </div>

    <div class="p-4 relative z-10 min-h-[100px]">
        @if(!$hasEvents)
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800">
                    <x-heroicon-o-calendar-days class="h-6 w-6 text-gray-300 dark:text-gray-600" />
                </div>
                <p class="text-xs text-gray-400">{{ __('No upcoming events.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                
                {{-- Announcements --}}
                @if($announcements->isNotEmpty())
                    @foreach($announcements as $announcement)
                         <div class="flex items-center gap-2.5 rounded-xl border border-blue-100 bg-blue-50/50 p-2 dark:border-blue-800/30 dark:bg-blue-900/20 sm:col-span-2">
                            <div class="flex h-8 w-8 shrink-0 flex-col items-center justify-center rounded-full border border-blue-100 bg-white text-blue-500 dark:border-blue-800/30 dark:bg-gray-800">
                                <x-heroicon-o-megaphone class="h-4 w-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $announcement->title }}</p>
                                    @if($announcement->priority === 'high')
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                                    @endif
                                </div>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ Str::limit(strip_tags($announcement->content), 40) }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Holidays --}}
                @if($holidays->isNotEmpty())
                    @foreach($holidays as $holiday)
                        <div class="flex items-center gap-2.5 p-2 bg-rose-50/50 dark:bg-rose-900/20 rounded-xl border border-rose-100 dark:border-rose-800/30">
                            <div class="flex h-8 w-8 shrink-0 flex-col items-center justify-center rounded-full border border-rose-100 bg-white dark:border-rose-800/30 dark:bg-gray-800">
                                <span class="text-[8px] font-bold text-rose-500 uppercase tracking-tighter leading-none mb-0.5">{{ $holiday->date->shortMonthName }}</span>
                                <span class="text-xs font-black text-gray-900 dark:text-white leading-none">{{ $holiday->date->day }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $holiday->name }}</p>
                                <span class="text-[9px] font-medium text-rose-500 bg-rose-100 dark:bg-rose-900/50 px-1.5 py-0.5 rounded">{{ __('Holiday') }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Birthdays --}}
                @if($birthdays->isNotEmpty())
                    @foreach($birthdays as $user)
                        <div class="flex items-center gap-2.5 p-2 bg-amber-50/50 dark:bg-amber-900/20 rounded-xl border border-amber-100 dark:border-amber-800/30">
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-lg object-cover border border-amber-100 dark:border-amber-800/30">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate max-w-[140px]">{{ $user->name }}</p>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <x-heroicon-o-cake class="h-3.5 w-3.5 text-amber-500" />
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($user->birth_date)->format('d M') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif
    </div>
</div>

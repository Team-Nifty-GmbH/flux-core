<div class="flex flex-col h-full">
    <div class="bg-white border-b px-6 py-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">{{ __('Attendance Overview') }}</h1>
            
            <div class="flex items-center gap-4">
                <button 
                    wire:click="previousMonth"
                    class="p-2 hover:bg-gray-100 rounded-lg transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                
                <div class="text-lg font-medium min-w-[150px] text-center">
                    {{ $monthName }} {{ $year }}
                </div>
                
                <button 
                    wire:click="nextMonth"
                    class="p-2 hover:bg-gray-100 rounded-lg transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-auto">
        <table class="w-full border-collapse">
            <thead class="sticky top-0 bg-gray-50 z-10">
                <tr>
                    <th class="sticky left-0 z-20 bg-gray-50 px-4 py-2 text-left font-medium text-gray-700 border-b border-r min-w-[200px]">
                        {{ __('Employee') }}
                    </th>
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $date = \Carbon\Carbon::create($year, $month, $day);
                            $isWeekend = $date->isWeekend();
                            $dateString = $date->format('Y-m-d');
                            $isHoliday = in_array($dateString, $holidays);
                        @endphp
                        <th class="px-1 py-2 text-center text-xs font-medium border-b {{ $isWeekend || $isHoliday ? 'bg-gray-100' : 'bg-gray-50' }}">
                            <div>{{ $day }}</div>
                            <div class="text-gray-500">{{ substr(__($date->format('D')), 0, 2) }}</div>
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceData as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="sticky left-0 bg-white px-4 py-2 border-b border-r font-medium">
                            {{ $user['name'] }}
                        </td>
                        @foreach($user['days'] as $day)
                            <td class="px-1 py-1 border-b text-center {{ $day['isWeekend'] || $day['isHoliday'] ? 'bg-gray-100' : '' }}">
                                @if($day['status'] === 'absence_approved' && $day['absence'])
                                    <div 
                                        class="w-8 h-8 mx-auto rounded"
                                        style="background-color: {{ $day['absence']['color'] }}"
                                        title="{{ $day['absence']['name'] }}"
                                    ></div>
                                @elseif($day['status'] === 'present')
                                    <div class="w-8 h-8 mx-auto rounded bg-green-400" title="{{ __('Present') }} - {{ $day['workTime']['started_at'] ?? '' }} - {{ $day['workTime']['ended_at'] ?? '' }}"></div>
                                @elseif($day['status'] === 'absent')
                                    <div class="w-8 h-8 mx-auto rounded bg-red-400" title="{{ __('Absent without notice') }}"></div>
                                @elseif($day['isHoliday'])
                                    <div class="w-8 h-8 mx-auto rounded bg-blue-200" title="{{ __('Holiday') }}"></div>
                                @elseif($day['isWeekend'])
                                    <div class="w-8 h-8 mx-auto"></div>
                                @else
                                    <div class="w-8 h-8 mx-auto"></div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                
                @if(count($attendanceData) > 0)
                    <tr class="border-t-2 border-gray-400 bg-gray-50 font-semibold">
                        <td class="sticky left-0 bg-gray-50 px-4 py-2 border-b border-r">
                            {{ __('Total Employees') }}
                        </td>
                        @foreach($dailySummary as $summary)
                            <td class="px-1 py-2 border-b text-center">
                                {{ $summary['total'] }}
                            </td>
                        @endforeach
                    </tr>
                    
                    @php
                        $usedAbsenceTypes = [];
                        foreach($dailySummary as $summary) {
                            foreach($summary['absences'] as $typeId => $count) {
                                if($count > 0 && !isset($usedAbsenceTypes[$typeId])) {
                                    $usedAbsenceTypes[$typeId] = true;
                                }
                            }
                        }
                    @endphp
                    
                    @foreach($absenceTypes as $type)
                        @if(isset($usedAbsenceTypes[$type['id']]))
                            <tr class="bg-gray-50">
                                <td class="sticky left-0 bg-gray-50 px-4 py-2 border-b border-r">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 rounded" style="background-color: {{ $type['color'] }}"></div>
                                        {{ $type['name'] }}
                                    </div>
                                </td>
                                @foreach($dailySummary as $summary)
                                    <td class="px-1 py-2 border-b text-center">
                                        {{ $summary['absences'][$type['id']] ?? 0 }}
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                    
                    <tr class="bg-green-50 font-semibold">
                        <td class="sticky left-0 bg-green-50 px-4 py-2 border-b border-r">
                            {{ __('Present') }}
                        </td>
                        @foreach($dailySummary as $summary)
                            <td class="px-1 py-2 border-b text-center">
                                {{ $summary['present'] }}
                            </td>
                        @endforeach
                    </tr>
                    
                    <tr class="bg-red-50 font-semibold">
                        <td class="sticky left-0 bg-red-50 px-4 py-2 border-b border-r">
                            {{ __('Absent without notice') }}
                        </td>
                        @foreach($dailySummary as $summary)
                            <td class="px-1 py-2 border-b text-center">
                                {{ $summary['absent'] }}
                            </td>
                        @endforeach
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="bg-white border-t px-6 py-4">
        <div class="flex flex-wrap gap-4 items-center">
            <span class="font-medium text-gray-700">{{ __('Legend') }}:</span>
            
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-green-400"></div>
                <span class="text-sm">{{ __('Present') }}</span>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-red-400"></div>
                <span class="text-sm">{{ __('Absent without notice') }}</span>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-blue-200"></div>
                <span class="text-sm">{{ __('Holiday') }}</span>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-100"></div>
                <span class="text-sm">{{ __('Weekend') }}</span>
            </div>
            
            @foreach($absenceTypes as $type)
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded" style="background-color: {{ $type['color'] }}"></div>
                    <span class="text-sm">{{ $type['name'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
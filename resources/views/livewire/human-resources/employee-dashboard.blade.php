<div class="space-y-6">
    @if($employee)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Vacation Balance --}}
            <x-card :header="__('Vacation Balance')">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Current Year') }}:</span>
                        <span class="font-semibold">{{ $employee->yearly_vacation_days ?? 0 }} {{ __('days') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Carried Over') }}:</span>
                        <span class="font-semibold">{{ $employee->previous_year_vacation_days ?? 0 }} {{ __('days') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Additional') }}:</span>
                        <span class="font-semibold">{{ $employee->additional_vacation_days ?? 0 }} {{ __('days') }}</span>
                    </div>
                    <hr class="my-2 dark:border-gray-600">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400 font-semibold">{{ __('Total Available') }}:</span>
                        <span class="font-bold text-green-600 dark:text-green-400">
                            {{ ($employee->yearly_vacation_days ?? 0) + ($employee->previous_year_vacation_days ?? 0) + ($employee->additional_vacation_days ?? 0) }} {{ __('days') }}
                        </span>
                    </div>
                </div>
            </x-card>

            {{-- Work Time Summary --}}
            <x-card :header="__('Work Time This Month')">
                @php
                    $currentMonth = now()->startOfMonth();
                    $workTimes = $employee->workTimes()
                        ->whereBetween('started_at', [$currentMonth, now()])
                        ->where('is_daily_work_time', true)
                        ->get();
                    $totalHours = $workTimes->sum('total_time_ms') / 3600000;
                @endphp
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Days Worked') }}:</span>
                        <span class="font-semibold">{{ $workTimes->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Total Hours') }}:</span>
                        <span class="font-semibold">{{ number_format($totalHours, 2) }} h</span>
                    </div>
                </div>
            </x-card>

            {{-- Department & Supervisor --}}
            <x-card :header="__('Organization')">
                <div class="space-y-2">
                    @if($employee->employeeDepartment)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Department') }}:</span>
                            <span class="font-semibold">{{ $employee->employeeDepartment->name }}</span>
                        </div>
                    @endif
                    @if($employee->supervisor)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Supervisor') }}:</span>
                            <span class="font-semibold">{{ $employee->supervisor->name }}</span>
                        </div>
                    @endif
                    @if($employee->location)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Location') }}:</span>
                            <span class="font-semibold">{{ $employee->location->name }}</span>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Recent Absences --}}
        <x-card :header="__('Recent Absence Requests')">
            @php
                $recentAbsences = $employee->absenceRequests()
                    ->with('absenceType')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            @endphp
            @if($recentAbsences->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('Type') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('From') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('To') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($recentAbsences as $absence)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $absence->absenceType->name }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $absence->start_date->format('d.m.Y') }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $absence->end_date->format('d.m.Y') }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($absence->status === 'approved') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                            @elseif($absence->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                            @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                            @endif">
                                            {{ __($absence->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">{{ __('No absence requests found') }}</p>
            @endif
        </x-card>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">
                {{ __('No employee selected') }}
            </p>
        </div>
    @endif
</div>
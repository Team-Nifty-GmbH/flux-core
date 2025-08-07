<div>
    <x-slot:title>
        {{ __('HR Dashboard') }}
    </x-slot:title>

    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <x-card>
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">{{ __('Total Employees') }}</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->employeeStats['total'] }}</p>
                    </div>
                    <x-icon name="users" class="h-8 w-8 text-indigo-500" />
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">{{ __('Available Vacation Days') }}</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($this->vacationStatistics['total_available'], 0) }}</p>
                        <p class="text-xs text-gray-500">{{ __('Avg') }}: {{ $this->vacationStatistics['average_per_employee'] }}</p>
                    </div>
                    <x-icon name="calendar" class="h-8 w-8 text-green-500" />
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">{{ __('Total Overtime Hours') }}</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($this->overtimeStatistics['total_hours'], 0) }}</p>
                        <p class="text-xs text-gray-500">{{ __('Avg') }}: {{ $this->overtimeStatistics['average_per_employee'] }}h</p>
                    </div>
                    <x-icon name="clock" class="h-8 w-8 text-orange-500" />
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">{{ __('Absences Today') }}</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->todaysAbsences->count() }}</p>
                    </div>
                    <x-icon name="user-minus" class="h-8 w-8 text-red-500" />
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-medium">{{ __('Upcoming Holidays') }}</h3>
            </x-slot:header>
            
            <div class="p-6">
                @if($this->upcomingHolidays->count() > 0)
                    <ul class="space-y-3">
                        @foreach($this->upcomingHolidays as $holiday)
                            <li class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium">{{ $holiday->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $holiday->date->format('d.m.Y') }}
                                        @if($holiday->day_part !== 'full')
                                            ({{ __($holiday->day_part) }})
                                        @endif
                                    </p>
                                </div>
                                <span class="text-sm text-gray-500">
                                    {{ $holiday->date->diffForHumans() }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">{{ __('No upcoming holidays') }}</p>
                @endif
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-medium">{{ __('Active Vacation Blackouts') }}</h3>
            </x-slot:header>
            
            <div class="p-6">
                @if($this->activeVacationBlackouts->count() > 0)
                    <ul class="space-y-3">
                        @foreach($this->activeVacationBlackouts as $blackout)
                            <li>
                                <p class="font-medium">{{ $blackout->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $blackout->start_date->format('d.m.Y') }} - {{ $blackout->end_date->format('d.m.Y') }}
                                </p>
                                @if($blackout->roles->count() > 0)
                                    <p class="text-xs text-gray-400">
                                        {{ __('Roles') }}: {{ $blackout->roles->pluck('name')->join(', ') }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">{{ __('No active vacation blackouts') }}</p>
                @endif
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-medium">{{ __('Employee Distribution') }}</h3>
            </x-slot:header>
            
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-2">{{ __('By Location') }}</p>
                        @foreach($this->employeeStats['by_location'] as $locationId => $count)
                            <div class="flex justify-between text-sm">
                                <span>
                                    @if($locationId)
                                        {{ \FluxErp\Models\Location::find($locationId)?->name ?? __('Unknown') }}
                                    @else
                                        {{ __('No Location') }}
                                    @endif
                                </span>
                                <span class="font-medium">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                    
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-2">{{ __('By Work Time Model') }}</p>
                        @foreach($this->employeeStats['by_work_model'] as $modelId => $count)
                            <div class="flex justify-between text-sm">
                                <span>
                                    @if($modelId)
                                        {{ \FluxErp\Models\WorkTimeModel::find($modelId)?->name ?? __('Unknown') }}
                                    @else
                                        {{ __('No Model') }}
                                    @endif
                                </span>
                                <span class="font-medium">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-medium">{{ __('Vacation & Overtime Alerts') }}</h3>
            </x-slot:header>
            
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <div class="flex items-center">
                            <x-icon name="exclamation-triangle" class="h-5 w-5 text-yellow-600 mr-2" />
                            <span class="text-sm">{{ __('High vacation balance') }}</span>
                        </div>
                        <span class="text-sm font-medium">{{ $this->vacationStatistics['employees_with_high_balance'] }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                        <div class="flex items-center">
                            <x-icon name="clock" class="h-5 w-5 text-orange-600 mr-2" />
                            <span class="text-sm">{{ __('High overtime') }} (>40h)</span>
                        </div>
                        <span class="text-sm font-medium">{{ $this->overtimeStatistics['employees_with_high_overtime'] }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div class="flex items-center">
                            <x-icon name="arrow-path" class="h-5 w-5 text-blue-600 mr-2" />
                            <span class="text-sm">{{ __('Carried vacation days') }}</span>
                        </div>
                        <span class="text-sm font-medium">{{ number_format($this->vacationStatistics['total_carried'], 0) }}</span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <div class="mt-6 flex gap-4">
        <x-button
            :text="__('Manage Employees')"
            :href="route('settings.users')"
            icon="users"
        />
        <x-button
            :text="__('Work Time Models')"
            :href="route('settings.work-time-models')"
            icon="clock"
            color="secondary"
        />
        <x-button
            :text="__('Vacation Settings')"
            :href="route('settings.vacation-carryover-rules')"
            icon="calendar"
            color="secondary"
        />
    </div>
</div>
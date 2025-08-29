<div class="p-6 space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">{{ __('HR Dashboard') }}</h1>

    {{-- Key Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-gray-500 text-sm">{{ __('Total Employees') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_employees'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-gray-500 text-sm">{{ __('Present Today') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['present_today'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-gray-500 text-sm">{{ __('Pending Requests') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_absence_requests'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-gray-500 text-sm">{{ __('On Vacation') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['on_vacation'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pending Absence Requests --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Pending Absence Requests') }}</h2>
            </div>
            <div class="p-6">
                @if(count($pendingRequests) > 0)
                    <div class="space-y-4">
                        @foreach($pendingRequests as $request)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer" 
                                 wire:click="goToAbsenceRequest({{ $request['id'] }})">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $request['user_name'] }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $request['type'] }} • {{ $request['start_date'] }} - {{ $request['end_date'] }} ({{ $request['days'] }} {{ __('days') }})
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $request['created_at'] }}</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">{{ __('No pending requests') }}</p>
                @endif
            </div>
        </div>

        {{-- Today's Absences --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ __("Today's Absences") }}</h2>
            </div>
            <div class="p-6">
                @if(count($todayAbsences) > 0)
                    <div class="space-y-3">
                        @foreach($todayAbsences as $absence)
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 rounded-full" style="background-color: {{ $absence['color'] }}"></div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $absence['user_name'] }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $absence['department'] ?? __('No Department') }} • {{ $absence['type'] }} • {{ __('Until') }} {{ $absence['until'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">{{ __('No absences today') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Department Statistics --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Department Overview') }}</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Department') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Total') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Present') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Absent') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Attendance Rate') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($departmentStats as $dept)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $dept['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ $dept['total'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <span class="text-green-600 font-medium">{{ $dept['present'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <span class="text-red-600 font-medium">{{ $dept['absent'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $dept['attendance_rate'] >= 80 ? 'bg-green-100 text-green-800' : ($dept['attendance_rate'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $dept['attendance_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Absence Type Statistics --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Absence Types This Month') }}</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($absenceTypeStats as $type)
                    <div class="flex items-center space-x-3">
                        <div class="w-4 h-4 rounded" style="background-color: {{ $type['color'] }}"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $type['name'] }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $type['count'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>


</div>
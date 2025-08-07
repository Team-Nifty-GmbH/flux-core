<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium mb-4">{{ __('HR Reports') }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-select.styled
                wire:model.live="reportType"
                :label="__('Report Type')"
                :options="[
                    ['value' => 'vacation_overview', 'label' => __('Vacation Overview')],
                    ['value' => 'absence_report', 'label' => __('Absence Report')],
                    ['value' => 'worktime_report', 'label' => __('Work Time Report')],
                    ['value' => 'overtime_report', 'label' => __('Overtime Report')],
                ]"
                select="label:label|value:value"
            />
            
            <x-date
                wire:model.live="dateFrom"
                :label="__('From Date')"
            />
            
            <x-date
                wire:model.live="dateTo"
                :label="__('To Date')"
            />
            
            <div class="flex items-end">
                <x-button
                    color="indigo"
                    :text="__('Export CSV')"
                    icon="arrow-down-tray"
                    wire:click="exportCsv"
                />
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <x-select.styled
                wire:model.live="locationId"
                :label="__('Filter by Location')"
                :options="$this->locations"
                select="label:name|value:id"
                :placeholder="__('All Locations')"
            />
            
            <x-select.styled
                wire:model.live="workTimeModelId"
                :label="__('Filter by Work Time Model')"
                :options="$this->workTimeModels"
                select="label:name|value:id"
                :placeholder="__('All Work Models')"
            />
        </div>
    </div>
    
    <div class="bg-white shadow rounded-lg p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @if(! empty($this->reportData))
                            @foreach(array_keys($this->reportData[0]) as $header)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __(str_replace('_', ' ', $header)) }}
                                </th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->reportData as $row)
                        <tr>
                            @foreach($row as $key => $value)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if(str_contains($key, 'percentage'))
                                        {{ $value }}%
                                    @elseif(str_contains($key, 'hours') || str_contains($key, 'days'))
                                        {{ number_format($value, 2) }}
                                    @elseif($key === 'status')
                                        <x-badge :color="match($value) {
                                            'pending' => 'yellow',
                                            'approved' => 'green',
                                            'rejected' => 'red',
                                            default => 'gray'
                                        }">
                                            {{ __($value) }}
                                        </x-badge>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="px-6 py-4 text-center text-sm text-gray-500">
                                {{ __('No data available') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($reportType === 'vacation_overview' && ! empty($this->reportData))
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-sm text-blue-600">{{ __('Total Vacation Days') }}</div>
                    <div class="text-2xl font-bold text-blue-900">
                        {{ array_sum(array_column($this->reportData, 'total_days')) }}
                    </div>
                </div>
                
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm text-green-600">{{ __('Used Days') }}</div>
                    <div class="text-2xl font-bold text-green-900">
                        {{ array_sum(array_column($this->reportData, 'used_days')) }}
                    </div>
                </div>
                
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="text-sm text-yellow-600">{{ __('Pending Days') }}</div>
                    <div class="text-2xl font-bold text-yellow-900">
                        {{ array_sum(array_column($this->reportData, 'pending_days')) }}
                    </div>
                </div>
            </div>
        @endif
        
        @if($reportType === 'overtime_report' && ! empty($this->reportData))
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-orange-50 rounded-lg p-4">
                    <div class="text-sm text-orange-600">{{ __('Total Overtime Hours') }}</div>
                    <div class="text-2xl font-bold text-orange-900">
                        {{ number_format(array_sum(array_column($this->reportData, 'overtime_hours')), 2) }}
                    </div>
                </div>
                
                <div class="bg-red-50 rounded-lg p-4">
                    <div class="text-sm text-red-600">{{ __('Employees with Overtime') }}</div>
                    <div class="text-2xl font-bold text-red-900">
                        {{ count($this->reportData) }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
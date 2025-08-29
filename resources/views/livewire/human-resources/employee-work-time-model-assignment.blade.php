<div>
    <div class="space-y-4">
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-select.styled
                    :label="__('Work Time Model')"
                    wire:model.live="employeeWorkTimeModelForm.work_time_model_id"
                    :options="$workTimeModels"
                />
                <x-date
                    :label="__('Valid From')"
                    wire:model="employeeWorkTimeModelForm.valid_from"
                />
                <x-number
                    :label="__('Annual Vacation Days')"
                    wire:model="employeeWorkTimeModelForm.annual_vacation_days"
                    :hint="__('Leave empty to use work time model default')"
                    step="0.5"
                    min="0"
                    max="365"
                />
                <div></div>
                <div class="sm:col-span-2">
                    <x-textarea
                        :label="__('Note')"
                        wire:model="employeeWorkTimeModelForm.note"
                        rows="2"
                    />
                </div>
            </div>
            <div class="mt-4">
                <x-button
                    :text="__('Assign')"
                    color="primary"
                    wire:click="assignWorkTimeModel"
                />
            </div>
        </div>

        @if(count($assignments) > 0)
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Work Time Model') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Valid From') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Valid Until') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Vacation Days') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Note') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200">
                        @foreach($assignments as $assignment)
                            <tr @if($assignment['is_current']) class="bg-green-50 dark:bg-green-900/20" @endif>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $assignment['work_time_model'] }}
                                    @if($assignment['is_current'])
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('Current') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $assignment['valid_from'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $assignment['valid_until'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $assignment['annual_vacation_days'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $assignment['note'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($assignment['is_current'])
                                        <x-button
                                            :text="__('End')"
                                            color="warning"
                                            size="xs"
                                            wire:click="endAssignment({{ $assignment['id'] }})"
                                            wire:flux-confirm.type.warning="{{ __('Are you sure you want to end this assignment?') }}"
                                        />
                                    @else
                                        <x-button
                                            :text="__('Delete')"
                                            color="red"
                                            size="xs"
                                            wire:click="deleteAssignment({{ $assignment['id'] }})"
                                            wire:flux-confirm.type.error="{{ __('Are you sure you want to delete this assignment?') }}"
                                        />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                {{ __('No work time model assignments yet') }}
            </div>
        @endif
    </div>
</div>

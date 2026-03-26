<div>
    <div class="space-y-4">
        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-select.styled
                    :label="__('Work Time Model')"
                    wire:model="employeeWorkTimeModelForm.work_time_model_id"
                    x-on:select="$wire.employeeWorkTimeModelForm.annual_vacation_days = parseFloat($event.detail.select?.annual_vacation_days ?? 0)"
                    x-on:remove="$wire.employeeWorkTimeModelForm.annual_vacation_days = null"
                    select="label:name|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\WorkTimeModel::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => ['name'],
                            'fields' => [
                                'id',
                                'name',
                                'annual_vacation_days',
                            ],
                            'where' => [
                                [
                                    'is_active',
                                    '=',
                                    true
                                ]
                            ],
                        ]
                    ]"
                />
                <x-date
                    :label="__('Valid From')"
                    wire:model="employeeWorkTimeModelForm.valid_from"
                />
                <x-number
                    :label="__('Annual Vacation Days')"
                    wire:model="employeeWorkTimeModelForm.annual_vacation_days"
                    step="1"
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

        <div
            class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
        >
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                        >
                            {{ __('Work Time Model') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                        >
                            {{ __('Valid From') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                        >
                            {{ __('Valid Until') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                        >
                            {{ __('Vacation Days') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                        >
                            {{ __('Note') }}
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-gray-200 bg-white dark:bg-gray-900"
                >
                    <template x-for="assignment in $wire.assignments">
                        <tr
                            x-bind:class="assignment.is_current ? 'bg-green-50 dark:bg-green-900/20' : ''"
                        >
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                <span
                                    x-text="assignment.work_time_model"
                                ></span>
                                <template x-if="assignment.is_current">
                                    <span
                                        class="ml-2 inline-flex items-center rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800"
                                    >
                                        {{ __('Current') }}
                                    </span>
                                </template>
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    x-text="assignment.valid_from ? window.formatters.date(assignment.valid_from) : '-'"
                                ></span>
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    x-text="assignment.valid_until ? window.formatters.date(assignment.valid_until) : '-'"
                                ></span>
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    x-text="
                                        assignment.annual_vacation_days
                                            ? window.formatters.int(assignment.annual_vacation_days)
                                            : '-'
                                    "
                                ></span>
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <span x-text="assignment.note || '-'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

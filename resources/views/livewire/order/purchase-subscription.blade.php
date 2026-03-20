@extends('flux::livewire.order.purchase')
@section('modals')
    @parent
    @use(FluxErp\Enums\OrderTypeEnum)
    <x-modal id="edit-schedule" :title="__('Edit Schedule')">
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                :label="__('Order type')"
                wire:model="schedule.parameters.orderTypeId"
                required
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\OrderType::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => [
                            'name',
                        ],
                        'select' => [
                            'name',
                            'id',
                        ],
                        'where' => [
                            [
                                'is_active',
                                '=',
                                true,
                            ],
                            [
                                'is_hidden',
                                '=',
                                false,
                            ],
                        ],
                        'whereIn' => [
                            [
                                'order_type_enum',
                                collect(OrderTypeEnum::cases())
                                    ->filter(fn(OrderTypeEnum $type) => $type->isPurchase() && ! $type->isSubscription())
                                    ->toArray(),
                            ],
                        ],
                    ],
                ]"
            />
            <x-select.styled
                :label="__('Repeat')"
                autocomplete="off"
                required
                searchable
                wire:model="schedule.cron.methods.basic"
                x-on:select="$wire.previewSchedule()"
                :options="$frequencies"
            />
            <div
                x-cloak
                x-show="
                    ['dailyAt', 'lastDayOfMonth'].indexOf(
                        $wire.schedule.cron.methods.basic,
                    ) >= 0
                "
            >
                <x-time
                    :label="__('Time')"
                    format="24"
                    wire:model="schedule.cron.parameters.basic.0"
                    x-on:change="$wire.previewSchedule()"
                />
            </div>
            <div
                x-cloak
                x-show="$wire.schedule.cron.methods.basic === 'weeklyOn'"
                class="flex flex-col gap-4"
            >
                <x-select.styled
                    :label="__('Weekday')"
                    wire:model="schedule.cron.parameters.basic.0"
                    x-on:select="$wire.previewSchedule()"
                    select="label:name|value:id"
                    :options="[
                        ['id' => 1, 'name' => __('Mondays')],
                        ['id' => 2, 'name' => __('Tuesdays')],
                        ['id' => 3, 'name' => __('Wednesdays')],
                        ['id' => 4, 'name' => __('Thursdays')],
                        ['id' => 5, 'name' => __('Fridays')],
                        ['id' => 6, 'name' => __('Saturdays')],
                        ['id' => 0, 'name' => __('Sundays')],
                    ]"
                />
                <x-time
                    :label="__('Time')"
                    format="24"
                    wire:model="schedule.cron.parameters.basic.1"
                    x-on:change="$wire.previewSchedule()"
                />
            </div>
            <div
                x-cloak
                x-show="
                    ['monthlyOn', 'quarterlyOn'].indexOf(
                        $wire.schedule.cron.methods.basic,
                    ) >= 0
                "
                class="flex flex-col gap-4"
            >
                <x-number
                    :max="31"
                    :min="0"
                    wire:model="schedule.cron.parameters.basic.0"
                    x-on:change="$wire.previewSchedule()"
                    :label="__('Day')"
                />
                <x-time
                    :label="__('Time')"
                    format="24"
                    wire:model="schedule.cron.parameters.basic.1"
                    x-on:change="$wire.previewSchedule()"
                />
            </div>
            <div
                x-cloak
                x-show="$wire.schedule.cron.methods.basic === 'twiceMonthly'"
                class="flex flex-col gap-4"
            >
                <x-number
                    :max="31"
                    :min="0"
                    wire:model="schedule.cron.parameters.basic.0"
                    x-on:change="$wire.previewSchedule()"
                    :label="__('Day')"
                />
                <div class="mt-4">
                    <x-number
                        :max="31"
                        :min="0"
                        wire:model="schedule.cron.parameters.basic.1"
                        x-on:change="$wire.previewSchedule()"
                        :label="__('Day')"
                    />
                </div>
                <x-time
                    :label="__('Time')"
                    format="24"
                    wire:model="schedule.cron.parameters.basic.2"
                    x-on:change="$wire.previewSchedule()"
                />
            </div>
            <div
                x-cloak
                x-show="$wire.schedule.cron.methods.basic === 'yearlyOn'"
                class="flex flex-col gap-4"
            >
                <x-select.styled
                    :label="__('Month')"
                    wire:model="schedule.cron.parameters.basic.0"
                    x-on:select="
                        document.getElementById('month-day-input').max =
                            $event.detail.select.days;
                        $wire.schedule.cron.parameters.basic[1] = Math.min(
                            $wire.schedule.cron.parameters.basic[1],
                            $event.detail.select.days,
                        );
                        $wire.previewSchedule();
                    "
                    select="label:name|value:id"
                    :options="[
                        ['id' => 1, 'name' => __('January'), 'days' => 31],
                        ['id' => 2, 'name' => __('February'), 'days' => 28],
                        ['id' => 3, 'name' => __('March'), 'days' => 31],
                        ['id' => 4, 'name' => __('April'), 'days' => 30],
                        ['id' => 5, 'name' => __('May'), 'days' => 31],
                        ['id' => 6, 'name' => __('June'), 'days' => 30],
                        ['id' => 7, 'name' => __('July'), 'days' => 31],
                        ['id' => 8, 'name' => __('August'), 'days' => 31],
                        ['id' => 9, 'name' => __('September'), 'days' => 30],
                        ['id' => 10, 'name' => __('October'), 'days' => 31],
                        ['id' => 11, 'name' => __('November'), 'days' => 30],
                        ['id' => 12, 'name' => __('December'), 'days' => 31],
                    ]"
                />
                <x-number
                    id="month-day-input"
                    :max="31"
                    :min="0"
                    wire:model.blur="schedule.cron.parameters.basic.1"
                    x-on:change="$wire.previewSchedule()"
                    :label="__('Day')"
                />
                <x-time
                    :label="__('Time')"
                    format="24"
                    wire:model="schedule.cron.parameters.basic.2"
                    x-on:change="$wire.previewSchedule()"
                />
            </div>
            <x-date
                wire:model.live="schedule.due_at"
                :label="__('Next Execution')"
                timezone="UTC"
            />
            <div
                x-cloak
                x-show="$wire.schedule.due_at && new Date($wire.schedule.due_at) <= new Date()"
                class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20"
            >
                <div class="flex items-center gap-2">
                    <x-icon
                        name="exclamation-triangle"
                        class="h-5 w-5 text-amber-600 dark:text-amber-400"
                    />
                    <p
                        class="text-sm font-medium text-amber-800 dark:text-amber-200"
                    >
                        {{ __('The schedule will be executed immediately on the next run.') }}
                    </p>
                </div>
            </div>
            <x-toggle
                wire:model="schedule.is_active"
                :label="__('Is Active')"
            />
            <div
                x-cloak
                x-show="$wire.schedule.nextExecutionDates.length > 0"
                class="border-t pt-4"
            >
                <x-label :label="__('Preview next executions')" />
                <ul class="mt-1 space-y-1">
                    <template
                        x-for="date in $wire.schedule.nextExecutionDates"
                        x-bind:key="date"
                    >
                        <li
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <x-icon name="chevron-right" class="h-3 w-3" />
                            <span
                                x-text="
                                    new Date(date + 'Z').toLocaleString('{{ app()->getLocale() }}', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                    })
                                "
                            ></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                x-on:click="$tsui.close.modal('edit-schedule')"
                :text="__('Cancel')"
            />
            <x-button
                color="indigo"
                x-on:click="
                    $wire.saveSchedule().then((success) => {
                        if (success) $tsui.close.modal('edit-schedule');
                    })
                "
                primary
                :text="__('Save')"
            />
        </x-slot:footer>
    </x-modal>
@endsection

@section('actions')
    @parent
    <x-button
        color="indigo"
        class="w-full"
        icon="clock"
        x-on:click="$tsui.open.modal('edit-schedule')"
        :text="__('Schedule')"
    />
@endsection

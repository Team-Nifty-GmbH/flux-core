@extends('flux::livewire.order.order')
@section('modals')
    @parent
    <x-modal id="edit-schedule" :title="__('Edit Schedule')" persistent>
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                :label="__('Order type')"
                wire:model.live="schedule.parameters.orderTypeId"
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
                    ],
                ]"
            />
            <x-select.styled
                :label="__('Repeat')"
                autocomplete="off"
                required
                wire:model="schedule.cron.methods.basic"
                :options="$frequencies"
            />
            <div
                x-cloak
                x-show="['dailyAt', 'lastDayOfMonth'].indexOf($wire.schedule.cron.methods.basic) >= 0"
            >
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model="schedule.cron.parameters.basic.0"
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
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model="schedule.cron.parameters.basic.1"
                />
            </div>
            <div
                x-cloak
                x-show="['monthlyOn', 'quarterlyOn'].indexOf($wire.schedule.cron.methods.basic) >= 0"
                class="flex flex-col gap-4"
            >
                <x-number
                    :max="31"
                    :min="0"
                    wire:model="schedule.cron.parameters.basic.0"
                    :label="__('Day')"
                />
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model="schedule.cron.parameters.basic.1"
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
                    :label="__('Day')"
                />
                <div class="mt-4">
                    <x-number
                        :max="31"
                        :min="0"
                        wire:model="schedule.cron.parameters.basic.1"
                        :label="__('Day')"
                    />
                </div>
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model="schedule.cron.parameters.basic.2"
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
                    x-on:select="document.getElementById('month-day-input').max = $event.detail.select.days; $wire.schedule.cron.parameters.basic[1] = Math.min($wire.schedule.cron.parameters.basic[1], $event.detail.select.days);"
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
                    :label="__('Day')"
                />
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model="schedule.cron.parameters.basic.2"
                />
            </div>
            <x-date
                wire:model="schedule.due_at"
                :label="__('Due At')"
                timezone="UTC"
            />
            <div
                x-data="{
                    get latestCancellationDate() {
                        const dueAt = $wire.schedule.due_at
                        if (! dueAt) return null

                        const noticeValue =
                            $wire.schedule.parameters?.cancellationNoticeValue ||
                            {{ app(\FluxErp\Settings\SubscriptionSettings::class)->default_cancellation_notice_value }}
                        const noticeUnit =
                            $wire.schedule.parameters?.cancellationNoticeUnit ||
                            '{{ app(\FluxErp\Settings\SubscriptionSettings::class)->default_cancellation_notice_unit }}'

                        if (! noticeValue) return null

                        const date = new Date(dueAt)
                        switch (noticeUnit) {
                            case 'days':
                                date.setDate(date.getDate() - noticeValue)
                                break
                            case 'weeks':
                                date.setDate(date.getDate() - noticeValue * 7)
                                break
                            case 'months':
                                date.setMonth(date.getMonth() - noticeValue)
                                break
                            case 'years':
                                date.setFullYear(date.getFullYear() - noticeValue)
                                break
                        }
                        return date.toLocaleDateString('{{ app()->getLocale() }}', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                        })
                    },
                }"
                x-cloak
                x-show="latestCancellationDate"
                class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20"
            >
                <div class="flex items-center gap-2">
                    <x-icon
                        name="exclamation-triangle"
                        class="h-5 w-5 text-amber-600 dark:text-amber-400"
                    />
                    <div>
                        <p
                            class="text-sm font-medium text-amber-800 dark:text-amber-200"
                        >
                            {{ __('Latest Cancellation Date') }}
                        </p>
                        <p
                            class="text-sm text-amber-700 dark:text-amber-300"
                            x-text="latestCancellationDate"
                        ></p>
                    </div>
                </div>
            </div>
            <x-label :label="__('End')" />
            <x-radio
                id="schedule-end-never-radio"
                name="schedule-end-radio"
                :label="__('Never')"
                value="never"
                wire:model="schedule.end_radio"
            />
            <div class="grid grid-cols-2 items-center gap-1.5">
                <x-radio
                    id="schedule-end-date-radio"
                    name="schedule-end-radio"
                    :label="__('Ends At')"
                    value="ends_at"
                    wire:model="schedule.end_radio"
                />
                <x-date
                    wire:model="schedule.ends_at"
                    timezone="UTC"
                    x-bind:disabled="$wire.schedule.end_radio !== 'ends_at'"
                />
                <x-radio
                    id="schedule-end-recurrences-radio"
                    name="schedule-end-radio"
                    :label="__('After number of recurrences')"
                    value="recurrences"
                    wire:model="schedule.end_radio"
                />
                <x-number
                    wire:model="schedule.recurrences"
                    :min="1"
                    x-bind:disabled="$wire.schedule.end_radio !== 'recurrences'"
                />
            </div>
            <div
                class="mb-2 grid grid-cols-2 items-center gap-1.5"
                x-cloak
                x-show="$wire.schedule.id && $wire.schedule.end_radio === 'recurrences'"
            >
                <x-label :label="__('Current Recurrence')" />
                <span class="flex justify-center">
                    {{ $schedule->current_recurrence ?? 0 }}
                </span>
            </div>
            <x-toggle
                wire:model="schedule.is_active"
                :label="__('Is Active')"
            />
            <div>
                <x-label :label="__('Minimum Contract Duration')" />
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Leave empty to use the default from settings.') }}
                </p>
                <div class="mt-2 grid grid-cols-2 gap-4">
                    <x-number
                        wire:model="schedule.parameters.minimumDurationValue"
                        :label="__('Value')"
                        :min="0"
                    />
                    <x-select.styled
                        wire:model="schedule.parameters.minimumDurationUnit"
                        :label="__('Unit')"
                        select="label:label|value:value"
                        :options="[
                            ['value' => 'days', 'label' => __('Days')],
                            ['value' => 'weeks', 'label' => __('Weeks')],
                            ['value' => 'months', 'label' => __('Months')],
                            ['value' => 'years', 'label' => __('Years')],
                        ]"
                    />
                </div>
            </div>
            <div>
                <x-label :label="__('Cancellation Notice Period')" />
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Leave empty to use the default from settings.') }}
                </p>
                <div class="mt-2 grid grid-cols-2 gap-4">
                    <x-number
                        wire:model="schedule.parameters.cancellationNoticeValue"
                        :label="__('Value')"
                        :min="0"
                    />
                    <x-select.styled
                        wire:model="schedule.parameters.cancellationNoticeUnit"
                        :label="__('Unit')"
                        select="label:label|value:value"
                        :options="[
                            ['value' => 'days', 'label' => __('Days')],
                            ['value' => 'weeks', 'label' => __('Weeks')],
                            ['value' => 'months', 'label' => __('Months')],
                            ['value' => 'years', 'label' => __('Years')],
                        ]"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-1.5 border-t pt-4">
                <x-toggle
                    wire:model="schedule.parameters.autoPrint"
                    :label="__('Auto Print')"
                    :hint="__('Automatically generate PDFs when the schedule runs')"
                />

                <div
                    x-cloak
                    x-show="$wire.schedule.parameters.autoPrint || $wire.schedule.parameters.autoSend"
                    class="flex flex-col gap-1.5"
                >
                    <div id="schedule-print-layouts">
                        <x-select.styled
                            wire:model="schedule.parameters.printLayouts"
                            :label="__('Print Layouts')"
                            :hint="__('Select one or more print layouts to generate when the schedule runs')"
                            multiple
                            select="label:label|value:value"
                            :options="$this->getPrintLayoutOptions()"
                        />
                    </div>
                </div>

                <x-toggle
                    wire:model="schedule.parameters.autoSend"
                    :label="__('Auto Send')"
                    :hint="__('Automatically send generated PDFs via email')"
                />

                <div
                    x-cloak
                    x-show="$wire.schedule.parameters.autoSend"
                    class="flex flex-col gap-1.5"
                >
                    <x-select.styled
                        wire:model="schedule.parameters.emailTemplateId"
                        :label="__('Email Template')"
                        :hint="__('Select the email template to use for sending')"
                        select="value:id"
                        unfiltered
                        :request="[
                            'url' => route('search', \FluxErp\Models\EmailTemplate::class),
                            'method' => 'POST',
                            'params' => [
                                'searchFields' => [
                                    'name',
                                    'subject',
                                ],
                                'where' => [
                                    [
                                        'model_type',
                                        '=',
                                        morph_alias(\FluxErp\Models\Order::class),
                                    ],
                                ],
                            ],
                        ]"
                    />
                </div>
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                x-on:click="$modalClose('edit-schedule')"
                :text="__('Cancel')"
            />
            <x-button
                color="indigo"
                wire:click="saveSchedule().then((success) => { if(success) $modalClose('edit-schedule'); })"
                primary
                :text="__('Save')"
            />
        </x-slot>
    </x-modal>
    <x-modal
        id="cancel-subscription"
        :title="__('Cancel Subscription')"
        x-on:close="cancellationType = '{{ \FluxErp\Enums\SubscriptionCancellationTypeEnum::NextPeriod->value }}'; generateDocument = true; sendEmail = false"
    >
        <div
            x-data="{
                cancellationType:
                    '{{ \FluxErp\Enums\SubscriptionCancellationTypeEnum::NextPeriod->value }}',
                generateDocument: true,
                sendEmail: false,
                get effectiveEndDate() {
                    const schedule = $wire.schedule
                    if (! schedule?.due_at) return null

                    const formatDate = (date) =>
                        date.toLocaleDateString('{{ app()->getLocale() }}', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                        })

                    if (
                        this.cancellationType ===
                        '{{ \FluxErp\Enums\SubscriptionCancellationTypeEnum::Immediate->value }}'
                    ) {
                        return formatDate(new Date())
                    }

                    let endFromNotice = new Date(schedule.due_at)

                    const minDurationValue =
                        schedule.parameters?.minimumDurationValue ||
                        {{ app(\FluxErp\Settings\SubscriptionSettings::class)->default_minimum_duration_value }}
                    const minDurationUnit =
                        schedule.parameters?.minimumDurationUnit ||
                        '{{ app(\FluxErp\Settings\SubscriptionSettings::class)->default_minimum_duration_unit }}'

                    let endFromMinDuration = new Date()
                    if (minDurationValue > 0) {
                        const orderDate = new Date($wire.order.order_date)
                        switch (minDurationUnit) {
                            case 'days':
                                orderDate.setDate(orderDate.getDate() + minDurationValue)
                                break
                            case 'weeks':
                                orderDate.setDate(
                                    orderDate.getDate() + minDurationValue * 7,
                                )
                                break
                            case 'months':
                                orderDate.setMonth(orderDate.getMonth() + minDurationValue)
                                break
                            case 'years':
                                orderDate.setFullYear(
                                    orderDate.getFullYear() + minDurationValue,
                                )
                                break
                        }
                        endFromMinDuration = orderDate
                    }

                    const effectiveEnd =
                        endFromNotice > endFromMinDuration
                            ? endFromNotice
                            : endFromMinDuration

                    return formatDate(effectiveEnd)
                },
                get isWithinNoticePeriod() {
                    const schedule = $wire.schedule
                    if (! schedule?.due_at) return true

                    const noticeValue =
                        schedule.parameters?.cancellationNoticeValue ||
                        {{ app(\FluxErp\Settings\SubscriptionSettings::class)->default_cancellation_notice_value }}
                    const noticeUnit =
                        schedule.parameters?.cancellationNoticeUnit ||
                        '{{ app(\FluxErp\Settings\SubscriptionSettings::class)->default_cancellation_notice_unit ?? 'months' }}'

                    if (! noticeValue || ! noticeUnit) return true

                    const deadline = new Date(schedule.due_at)
                    switch (noticeUnit) {
                        case 'days':
                            deadline.setDate(deadline.getDate() - noticeValue)
                            break
                        case 'weeks':
                            deadline.setDate(deadline.getDate() - noticeValue * 7)
                            break
                        case 'months':
                            deadline.setMonth(deadline.getMonth() - noticeValue)
                            break
                        case 'years':
                            deadline.setFullYear(deadline.getFullYear() - noticeValue)
                            break
                    }

                    return new Date() <= deadline
                },
            }"
            class="flex flex-col gap-4"
        >
            <div
                x-cloak
                x-show="!isWithinNoticePeriod"
                class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20"
            >
                <div class="flex items-center gap-2">
                    <x-icon
                        name="exclamation-triangle"
                        class="h-5 w-5 text-red-600 dark:text-red-400"
                    />
                    <p
                        class="text-sm font-medium text-red-800 dark:text-red-200"
                    >
                        {{ __('The cancellation notice period has already passed. The subscription will end after the next renewal period.') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <x-label :label="__('Cancellation Type')" />
                <x-radio
                    id="cancel-next-period"
                    name="cancellation-type"
                    :label="__('Cancel at next renewal date')"
                    :value="\FluxErp\Enums\SubscriptionCancellationTypeEnum::NextPeriod->value"
                    x-model="cancellationType"
                />
                <x-radio
                    id="cancel-immediate"
                    name="cancellation-type"
                    :label="__('Cancel immediately')"
                    :value="\FluxErp\Enums\SubscriptionCancellationTypeEnum::Immediate->value"
                    x-model="cancellationType"
                />
            </div>

            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Effective end date') }}:
                    <span
                        class="font-semibold"
                        x-text="effectiveEndDate"
                    ></span>
                </p>
            </div>

            <div class="flex flex-col gap-2 border-t pt-4">
                <x-label :label="__('Options')" />
                <x-toggle
                    x-model="generateDocument"
                    :label="__('Generate cancellation confirmation')"
                />
                <div x-cloak x-show="generateDocument" class="ml-6">
                    <x-toggle
                        x-model="sendEmail"
                        :label="__('Send confirmation by email')"
                    />
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t pt-4">
                <x-button
                    color="secondary"
                    light
                    x-on:click="$modalClose('cancel-subscription')"
                    :text="__('Cancel')"
                />
                <x-button
                    color="amber"
                    loading
                    x-on:click="$wire.cancelSubscription(cancellationType, generateDocument, sendEmail).then((success) => { if(success) $modalClose('cancel-subscription'); })"
                    :text="__('Confirm Cancellation')"
                />
            </div>
        </div>
    </x-modal>
@endsection

@section('actions')
    @parent
    <x-button
        color="indigo"
        class="w-full"
        icon="clock"
        x-on:click="$modalOpen('edit-schedule')"
        :text="__('Schedule')"
    />
    <x-button
        color="amber"
        class="w-full"
        icon="x-circle"
        x-on:click="$modalOpen('cancel-subscription')"
        :text="__('Cancellation')"
    />
@endsection

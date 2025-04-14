<div>
    <x-card>
        <div class="grid grid-cols-1 space-y-2.5">
            @section('event-edit.content')
            @section('event-edit.calendar-select')
            <div
                x-show="!$wire.event.calendar_type"
                id="calendar-select"
                wire:ignore
            >
                <x-select.styled
                    wire:model="event.calendar_id"
                    :label="__('Calendar')"
                    required
                    x-on:select="$wire.event.is_repeatable = await $wire.isCalendarEventRepeatable($event.detail.select.id);"
                    select="label:label|value:id"
                    :request="[
                        'url' => route('calendar-search'),
                        'method' => 'POST',
                        'params' => [
                            'where' => [['is_group', '=', false]],
                        ],
                    ]"
                />
            </div>
            @show
            @section('event-edit.input-fields')
            <x-input
                x-ref="autofocus"
                :label="__('Title') . '*'"
                required
                wire:model="event.title"
                x-bind:readonly="! $wire.event.is_editable ?? false"
            />
            <x-textarea
                :label="__('Description')"
                wire:model="event.description"
                x-bind:readonly="! $wire.event.is_editable ?? false"
            />
            <x-checkbox
                :label="__('all-day')"
                wire:model="event.is_all_day"
                x-bind:disabled="! $wire.event.is_editable ?? false"
            />
            @show
            @section('event-edit.timespan')
            <div class="grid grid-cols-3 items-center gap-1.5">
                <x-label>
                    {{ __('Start') }}
                </x-label>
                <div>
                    <x-input
                        id="calendar-event-start-date"
                        type="date"
                        x-bind:disabled="! $wire.event.is_editable ?? false"
                        x-bind:value="dayjs($wire.event.start).format('YYYY-MM-DD')"
                        x-on:change="setDateTime('start', $event)"
                    />
                </div>
                <div x-cloak x-show="! $wire.event.is_all_day">
                    <x-input
                        id="calendar-event-start-time"
                        type="time"
                        x-bind:disabled="! $wire.event.is_editable ?? false"
                        x-on:change="setDateTime('start', $event)"
                        x-bind:value="dayjs($wire.event.start).format('HH:mm')"
                    />
                </div>
            </div>
            <div class="grid grid-cols-3 items-center gap-1.5">
                <x-label :label="__('End')" />
                <x-input
                    id="calendar-event-end-date"
                    type="date"
                    x-bind:disabled="! $wire.event.is_editable ?? false"
                    x-bind:value="dayjs($wire.event.end).format('YYYY-MM-DD')"
                    x-on:change="setDateTime('end', $event)"
                />
                <div x-cloak x-show="! $wire.event.is_all_day">
                    <x-input
                        id="calendar-event-end-time"
                        type="time"
                        x-bind:disabled="! $wire.event.is_editable ?? false"
                        x-on:change="setDateTime('end', $event)"
                        x-bind:value="dayjs($wire.event.end).format('HH:mm')"
                    />
                </div>
            </div>
            @show
            @section('event-edit.custom-properties')
            <template
                x-for="customProperty in $wire.event.extended_props ?? []"
            >
                <div>
                    <div
                        x-cloak
                        x-show="customProperty.field_type === 'text'"
                    >
                        <div class="mb-1">
                            <x-label x-bind:for="customProperty.name">
                                <x-slot:word>
                                    <span
                                        x-text="customProperty.name"
                                    ></span>
                                </x-slot>
                            </x-label>
                        </div>
                        <x-input
                            x-model="customProperty.value"
                            x-bind:disabled="! $wire.event.is_editable ?? false"
                            x-bind:id="customProperty.name"
                        />
                    </div>
                    <div
                        x-cloak
                        x-show="customProperty.field_type === 'textarea'"
                    >
                        <div class="mb-1">
                            <x-label x-bind:for="customProperty.name">
                                <x-slot:word>
                                    <span
                                        x-text="customProperty.name"
                                    ></span>
                                </x-slot>
                            </x-label>
                        </div>
                        <x-textarea
                            x-model="customProperty.value"
                            x-bind:disabled="! $wire.event.is_editable ?? false"
                            x-bind:id="customProperty.name"
                        />
                    </div>
                    <div
                        x-cloak
                        x-show="customProperty.field_type === 'checkbox'"
                        class="flex gap-x-2"
                    >
                        <x-checkbox
                            x-model="customProperty.value"
                            x-bind:disabled="! $wire.event.is_editable ?? false"
                            x-bind:id="customProperty.name"
                        />
                        <x-label x-bind:for="customProperty.name">
                            <x-slot:word>
                                <span x-text="customProperty.name"></span>
                            </x-slot>
                        </x-label>
                    </div>
                    <div
                        x-cloak
                        x-show="customProperty.field_type === 'date'"
                    >
                        <div class="mb-1">
                            <x-label x-bind:for="customProperty.name">
                                <x-slot:word>
                                    <span
                                        x-text="customProperty.name"
                                    ></span>
                                </x-slot>
                            </x-label>
                        </div>
                        <x-input
                            x-model="customProperty.value"
                            x-bind:disabled="! $wire.event.is_editable ?? false"
                            x-bind:id="customProperty.name"
                            type="date"
                        />
                    </div>
                </div>
            </template>
            @show
            @section('event-edit.repeatable')
            <div class="mb-2" x-show="$wire.event.is_repeatable">
                <x-checkbox
                    :label="__('Repeatable')"
                    wire:model="event.has_repeats"
                    x-bind:disabled="! $wire.event.is_editable ?? false"
                />
            </div>
            <div
                x-show="$wire.event.has_repeats && $wire.event.is_repeatable"
            >
                <div class="grid grid-cols-3 items-center gap-1.5">
                    <x-label :label="__('Repeat every')" />
                    <x-number
                        wire:model="event.repeat.interval"
                        :min="1"
                        x-bind:disabled="! $wire.event.is_editable ?? false"
                    />
                    <x-select.styled
                        wire:model="event.repeat.unit"
                        x-init="$wire.$watch('event.unit', (value) => {
                            const option = options.find(option => option.value === value);
                            if (option) {
                                select(option);
                            }
                        })"
                        required
                        :options="[
                            ['label' => __('Day(s)'), 'value' => 'days'],
                            ['label' => __('Week(s)'), 'value' => 'weeks'],
                            ['label' => __('Month(s)'), 'value' => 'months'],
                            ['label' => __('Year(s)'), 'value' => 'years'],
                        ]"
                        x-bind:disabled="! $wire.event.is_editable ?? false"
                    />
                </div>

                <template x-if="$wire.event.repeat.unit === 'weeks'">
                    <div
                        class="mt-4 grid grid-cols-7 items-center gap-1.5"
                        x-data="{
                            updateWeekdays(weekday) {
                                if ($wire.event.repeat.weekdays.indexOf(weekday) !== -1) {
                                    $wire.event.repeat.weekdays = $wire.event.repeat.weekdays.filter(
                                        (day) => day !== weekday,
                                    )
                                } else {
                                    $wire.event.repeat.weekdays.push(weekday)
                                }
                            },
                            weekdaySelected(weekday) {
                                return $wire.event.repeat.weekdays.indexOf(weekday) !== -1
                                    ? 'bg-indigo-500 text-white'
                                    : ''
                            },
                        }"
                    >
                        <x-button
                            rounded
                            color="indigo"
                            flat
                            xs
                            :text="__('Mon')"
                            x-on:click="updateWeekdays('Mon')"
                            x-bind:class="weekdaySelected('Mon')"
                        />
                        <x-button
                            rounded
                            color="indigo"
                            flat
                            xs
                            :text="__('Tue')"
                            x-on:click="updateWeekdays('Tue')"
                            x-bind:class="weekdaySelected('Tue')"
                        />
                        <x-button
                            rounded
                            color="indigo"
                            flat
                            xs
                            :text="__('Wed')"
                            x-on:click="updateWeekdays('Wed')"
                            x-bind:class="weekdaySelected('Wed')"
                        />
                        <x-button
                            rounded
                            color="indigo"
                            flat
                            xs
                            :text="__('Thu')"
                            x-on:click="updateWeekdays('Thu')"
                            x-bind:class="weekdaySelected('Thu')"
                        />
                        <x-button
                            rounded
                            color="indigo"
                            flat
                            xs
                            :text="__('Fri')"
                            x-on:click="updateWeekdays('Fri')"
                            x-bind:class="weekdaySelected('Fri')"
                        />
                        <x-button
                            rounded
                            color="indigo"
                            flat
                            xs
                            :text="__('Sat')"
                            x-on:click="updateWeekdays('Sat')"
                            x-bind:class="weekdaySelected('Sat')"
                        />
                        <x-button
                            rounded
                            color="indigo"
                            flat
                            xs
                            :text="__('Sun')"
                            x-on:click="updateWeekdays('Sun')"
                            x-bind:class="weekdaySelected('Sun')"
                        />
                    </div>
                </template>
                <template x-if="$wire.event.repeat.unit === 'months'">
                    <div
                        x-data="{
                            selectedOption: null,
                            selectOption(option) {
                                $wire.event.repeat.monthly = option.value
                                this.selectedOption = option
                            },
                            options: [
                                {
                                    value: 'day',
                                    label:
                                        '{{ __('Monthly on') }} ' +
                                        dayjs($wire.event.start).format('DD') +
                                        '.',
                                },
                                {
                                    value: 'first',
                                    label:
                                        '{{ __('Monthly on first') }} ' +
                                        dayjs($wire.event.start).format('dddd'),
                                },
                                {
                                    value: 'second',
                                    label:
                                        '{{ __('Monthly on second') }} ' +
                                        dayjs($wire.event.start).format('dddd'),
                                },
                                {
                                    value: 'third',
                                    label:
                                        '{{ __('Monthly on third') }} ' +
                                        dayjs($wire.event.start).format('dddd'),
                                },
                                {
                                    value: 'fourth',
                                    label:
                                        '{{ __('Monthly on fourth') }} ' +
                                        dayjs($wire.event.start).format('dddd'),
                                },
                                {
                                    value: 'last',
                                    label:
                                        '{{ __('Monthly on last') }} ' +
                                        dayjs($wire.event.start).format('dddd'),
                                },
                            ],
                        }"
                    >
                        <x-dropdown position="bottom" scope="calendar">
                            <x-slot:action>
                                <x-button
                                    class="mt-2 w-full"
                                    x-on:click="show = ! show"
                                    color="secondary"
                                    flat
                                >
                                    <span
                                        x-text="selectedOption?.label ?? '{{ __('Please select') }}'"
                                    ></span>
                                </x-button>
                            </x-slot>
                            <template x-for="option in options">
                                <x-dropdown.items
                                    x-on:click="selectOption(option); show = false;"
                                >
                                    <x-slot:text>
                                        <div class="flex gap-1.5">
                                            <span
                                                x-text="option.label"
                                            ></span>
                                            <x-icon
                                                name="check"
                                                x-cloak
                                                x-show="selectedOption === option"
                                            />
                                        </div>
                                    </x-slot>
                                </x-dropdown.items>
                            </template>
                        </x-dropdown>
                    </div>
                </template>
                <div class="mb-2 mt-4">
                    <x-label :label="__('Repeat end')" />
                </div>
                <x-radio
                    :label="__('Never')"
                    :value="null"
                    x-model="$wire.event.repeat.repeat_radio"
                    x-bind:disabled="! $wire.event.is_editable ?? false"
                />
                <div class="grid grid-cols-2 items-center gap-1.5">
                    <x-radio
                        :label="__('Date At')"
                        value="repeat_end"
                        x-model="$wire.event.repeat.repeat_radio"
                        x-bind:disabled="! $wire.event.is_editable ?? false"
                    />
                    <x-input
                        id="calendar-event-repeat-end-date"
                        type="date"
                        x-bind:disabled="(! $wire.event.is_editable ?? false) || $wire.event.repeat.repeat_radio !== 'repeat_end'"
                        x-bind:value="dayjs($wire.event.repeat_end).format('YYYY-MM-DD')"
                        x-on:change="$wire.event.repeat_end = dayjs($event.target.value).format('YYYY-MM-DD')"
                    />
                    <x-radio
                        :label="__('After amount of events')"
                        value="recurrences"
                        x-model="$wire.event.repeat.repeat_radio"
                        x-bind:disabled="! $wire.event.is_editable ?? false"
                    />
                    <x-number
                        x-model="$wire.event.recurrences"
                        x-bind:disabled="(! $wire.event.is_editable ?? false) || $wire.event.repeat.repeat_radio !== 'recurrences'"
                    />
                </div>
            </div>
            @show
            @section('event-edit.has-taken-place')
            <div class="mb-2">
                <x-checkbox
                    :label="__('Has taken place')"
                    wire:model="event.has_taken_place"
                    x-bind:disabled="! $wire.event.is_editable ?? false"
                />
            </div>
            @show
            @section('event-edit.invites')
            <div x-cloak x-show="$wire.event.is_invited">
                <x-select.styled
                    wire:model="event.status"
                    :label="__('My status')"
                    required
                >
                    <calendar-option value="accepted">
                        <div>
                            <x-button.circle
                                disabled
                                color="emerald"
                                xs
                                icon="check-circle"
                            />
                            {{ __('Accepted') }}
                        </div>
                    </calendar-option>
                    <calendar-option :label="__('Declined')" value="declined">
                        <div>
                            <x-button.circle
                                disabled
                                color="red"
                                xs
                                icon="x-mark"
                            />
                            {{ __('Declined') }}
                        </div>
                    </calendar-option>
                    <calendar-option :label="__('Maybe')" value="maybe">
                        <div>
                            <x-button.circle
                                disabled
                                color="amber"
                                xs
                                label="?"
                            />
                            {{ __('Maybe') }}
                        </div>
                    </calendar-option>
                </x-select.styled>
            </div>
            <div>
                <div
                    class="grid grid-cols-1 gap-1.5"
                    x-show="$wire.event.is_editable ?? false"
                    x-on:click.outside="search = false"
                >
                    <x-label for="invite" :text="__('Invites')" />
                    <template x-for="invited in $wire.event.invited">
                        <div class="flex gap-1.5">
                            <x-button.circle
                                color="red"
                                xs
                                icon="trash"
                                x-bind:disabled="! $wire.event.is_editable ?? false"
                                x-on:click="$wire.event.invited.splice($wire.event.invited.indexOf(invited), 1)"
                            />
                            <x-button.circle
                                x-show="invited.pivot?.status === 'accepted'"
                                disabled
                                color="emerald"
                                xs
                                icon="check-circle"
                            />
                            <x-button.circle
                                x-show="invited.pivot?.status === 'declined'"
                                disabled
                                color="red"
                                xs
                                icon="x-mark"
                            />
                            <x-button.circle
                                x-show="invited.pivot?.status === 'maybe'"
                                disabled
                                color="amber"
                                xs
                                label="?"
                            />
                            <x-button.circle
                                x-show="invited.pivot?.status !== 'accepted' && invited.pivot?.status !== 'declined' && invited.pivot?.status !== 'maybe'"
                                disabled
                                color="gray"
                                xs
                                label="?"
                            />
                            <x-badge
                                md
                                x-text="invited.label ?? invited.name"
                            />
                        </div>
                    </template>
                    <div class="w-full" id="invitee-search">
                        <x-select.styled
                            id="invite"
                            :placeholder="__('Add invite')"
                            x-on:select="$wire.event.invited.push($event.detail.select); clear(); $tallstackuiSelect('invitee-search').mergeRequestParams({
                                where: [['id', '!=', $event.detail.select.value]],
                            })"
                            select="label:label|value:id"
                            :request="[
                                'url' => route('search', \FluxErp\Models\User::class),
                                'method' => 'POST',
                                'params' => [
                                    'with' => 'media',
                                    'where' => [
                                        [
                                            'id',
                                            '!=',
                                            auth()->id(),
                                        ],
                                    ],
                                ],
                            ]"
                        />
                    </div>
                </div>
            </div>
            @show
            @show
        </div>
        <x-slot:footer>
            <div class="flex w-full justify-between gap-2">
                <x-button
                    color="red"
                    flat
                    :text="__('Delete')"
                    x-show="$wire.event.id"
                    x-cloak
                    x-on:click="dialogType = 'delete'; $modalOpen('confirm-dialog')"
                />
                <div class="flex w-full justify-end gap-2">
                    <x-button
                        color="secondary"
                        light
                        flat
                        :text="__('Cancel')"
                        x-on:click="$modalClose('edit-event-modal')"
                    />
                    <x-button
                        primary
                        :text="__('Save')"
                        x-on:click="dialogType = 'save'; $wire.event.confirm_option = 'future'; $wire.event.was_repeatable ? $modalOpen('confirm-dialog') : $wire.save()"
                    />
                </div>
            </div>
        </x-slot>
    </x-card>
</div>

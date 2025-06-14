<div
    x-on:start-time-tracking.window="
        relatedSelected($event.detail.trackable_type)
        $wire.start($event.detail)
    "
    x-data="workTime($wire, '{{ route('search', '') }}')"
    x-init.once="load()"
>
    <x-modal id="work-time-modal" persistent x-on:close="$wire.resetWorkTime()">
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                :label="__('Work Time Type')"
                wire:model="workTime.work_time_type_id"
                x-on:select="$wire.workTime.is_billable = $event.detail.select.is_billable"
                select="label:name|value:id"
                :options="$workTimeTypes"
            />
            <div class="mt-2">
                <x-toggle
                    :label="__('Is Billable')"
                    wire:model="workTime.is_billable"
                />
            </div>
            <x-select.styled
                :label="__('Contact')"
                wire:model="workTime.contact_id"
                select="label:label|value:contact_id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Address::class),
                    'method' => 'POST',
                    'params' => [
                        'where' => [
                            [
                                'is_main_address',
                                '=',
                                true,
                            ],
                        ],
                        'option-value' => 'contact_id',
                        'fields' => [
                            'contact_id',
                            'name',
                        ],
                        'with' => 'contact.media',
                    ],
                ]"
            />
            <x-select.styled
                :label="__('Model')"
                wire:model="workTime.trackable_type"
                x-on:select="relatedSelected($event.detail.select?.value)"
                :options="$trackableTypes"
            />
            <div
                id="trackable-id"
                x-cloak
                x-show="$wire.workTime.trackable_type"
            >
                <x-select.styled
                    :label="__('Record')"
                    x-on:select="recordSelected($event.detail.select)"
                    wire:model="workTime.trackable_id"
                    select="label:label|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', '__model__'),
                        'method' => 'POST',
                        'params' => [
                            'appends' => [
                                'contact_id',
                            ],
                        ],
                    ]"
                />
            </div>
            <x-input :label="__('Name')" wire:model="workTime.name" />
            <x-textarea
                :label="__('Description')"
                wire:model="workTime.description"
            />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('work-time-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Start')"
                loading
                x-on:click="$wire.save().then((success) => { if (success) $modalClose('work-time-modal'); })"
            >
                <x-slot:label>
                    <span
                        x-text="$wire.workTime.id ? '{{ __('Save') }}' : '{{ __('Start') }}'"
                    ></span>
                </x-slot>
            </x-button>
        </x-slot>
    </x-modal>
    <x-button
        rounded
        color="indigo"
        x-on:click="open = ! open"
        x-ref="button"
        x-bind:class="$wire.workTime.is_pause && 'ring-amber-500 text-white bg-amber-500 hover:bg-amber-600 hover:ring-amber-600 dark:ring-offset-slate-800 dark:bg-amber-700 dark:ring-amber-700 dark:hover:bg-amber-600 dark:hover:ring-amber-600'"
        icon="clock"
    >
        <div x-text="msTimeToString(time)"></div>
    </x-button>
    <div
        x-cloak
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition duration-75 ease-in"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        x-show="open"
        x-anchor.bottom-end.offset.5="$refs.button"
        class="z-10"
    >
        <x-card id="active-work-times" class="flex max-w-md flex-col gap-4">
            <x-slot:header>
                <div class="flex w-full items-center justify-between gap-4">
                    <div>{{ __('Active Work Times') }}</div>
                    <x-button.circle
                        color="secondary"
                        light
                        sm
                        x-on:click="open = false"
                        icon="x-mark"
                    />
                </div>
            </x-slot>
            <div class="flex w-full gap-1.5">
                <x-button
                    class="w-full"
                    x-show="! $wire.dailyWorkTime.id"
                    color="emerald"
                    :text="__('Start Workday')"
                    x-on:click="$wire.toggleWorkDay(true)"
                />
                <x-button
                    class="w-1/2"
                    x-show="$wire.dailyWorkTime.id"
                    color="red"
                    :text="__('End Workday')"
                    x-on:click="stopWorkDay()"
                />
                <x-button
                    class="w-1/2"
                    x-show="$wire.dailyWorkTime.id && ! $wire.dailyWorkTimePause.id"
                    color="amber"
                    :text="__('Pause')"
                    x-on:click="$wire.togglePauseWorkDay(true)"
                />
                <x-button
                    class="w-1/2"
                    x-show="$wire.dailyWorkTime.id && $wire.dailyWorkTimePause.id"
                    color="emerald"
                    :text="__('Continue')"
                    x-on:click="$wire.togglePauseWorkDay(false)"
                />
            </div>
            <x-button
                x-show="$wire.dailyWorkTime.id"
                color="emerald"
                :text="__('Record new working hours')"
                x-on:click="$modalOpen('work-time-modal')"
            />
            <template x-for="workTime in activeWorkTimes">
                <div class="flex flex-col gap-1.5 rounded-md p-1.5">
                    <div class="flex w-full justify-between">
                        <div class="flex w-full flex-col">
                            <div
                                class="truncate text-gray-500 dark:text-gray-400"
                                x-text="workTime.name"
                            ></div>
                            <div
                                class="text-xs text-gray-500 dark:text-gray-400"
                                x-text="workTime.work_time_type?.name"
                            ></div>
                            <div
                                class="text-xs text-gray-500 dark:text-gray-400"
                                x-text="formatters.datetime(workTime.started_at)"
                            ></div>
                            <x-badge color="indigo">
                                <div
                                    x-bind:data-id="workTime.id"
                                    x-init="$el.innerText = msTimeToString(calculateTime(workTime))"
                                ></div>
                            </x-badge>
                        </div>
                    </div>
                    <div class="flex justify-end gap-x-4">
                        <x-button
                            class="w-1/2"
                            x-show="! workTime.ended_at"
                            color="amber"
                            icon="pause"
                            :text="__('Pause')"
                            x-on:click="pauseWorkTime(workTime)"
                        />
                        <x-button
                            class="w-1/2"
                            x-show="workTime.ended_at"
                            color="emerald"
                            icon="play"
                            :text="__('Continue')"
                            x-on:click="continueWorkTime(workTime)"
                        />
                        <x-button
                            class="w-1/2"
                            color="red"
                            icon="stop"
                            :text="__('Stop')"
                            x-on:click="stopWorkTime(workTime)"
                        />
                        <x-button
                            class="flex-none"
                            color="indigo"
                            icon="pencil"
                            wire:click="edit(workTime.id)"
                        />
                    </div>
                </div>
            </template>
        </x-card>
    </div>
</div>

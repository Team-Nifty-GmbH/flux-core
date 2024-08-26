<div
    x-on:start-time-tracking.window="relatedSelected($event.detail.trackable_type); $wire.start($event.detail);"
    x-data="workTime($wire, '{{ route('search', '') }}')"
    x-init.once="load()"
>
    <x-modal name="work-time" persistent="true" x-on:close="$wire.resetWorkTime()">
        <x-card class="flex flex-col gap-4">
            <x-select :label="__('Work Time Type')"
                      :options="$workTimeTypes"
                      wire:model="workTime.work_time_type_id"
                      option-value="id"
                      option-label="name"
                      x-on:selected="$wire.workTime.is_billable = $event.detail.is_billable"
            />
            <x-toggle :label="__('Is Billable')" wire:model="workTime.is_billable" />
            <x-select :label="__('Contact')"
                wire:model="workTime.contact_id"
                option-value="contact_id"
                option-label="label"
                template="user-option"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Address::class),
                    'method' => 'POST',
                    'params' => [
                        'where' => [
                            [
                                'is_main_address',
                                '=',
                                true,
                            ]
                        ],
                        'option-value' => 'contact_id',
                        'fields' => [
                            'contact_id',
                            'name',
                        ],
                        'with' => 'contact.media',
                    ]
                ]"
            />
            <x-select
                :label="__('Model')"
                wire:model="workTime.trackable_type"
                :options="$trackableTypes"
                option-label="label"
                option-value="value"
            />
            <div id="trackable-id" x-cloak x-show="$wire.workTime.trackable_type">
                <x-select :label="__('Record')"
                    x-on:selected="recordSelected($event.detail)"
                    option-value="id"
                    option-label="label"
                    :async-data="[
                        'api' => route('search', '__model__'),
                        'method' => 'POST',
                        'params' => [
                            'appends' => [
                                'contact_id',
                            ],
                        ]
                    ]"
                    wire:model="workTime.trackable_id"
                />
            </div>
            <x-input :label="__('Name')" wire:model="workTime.name" />
            <x-textarea :label="__('Description')" wire:model="workTime.description" />
            <x-slot:footer>
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button primary spinner x-on:click="$wire.save().then((success) => { if (success) close(); })">
                            <x-slot:label>
                                <span x-text="$wire.workTime.id ? '{{ __('Save') }}' : '{{ __('Start') }}'">
                            </x-slot:label>
                        </x-button>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <x-button
        rounded
        primary
        x-on:click="open = ! open"
        x-ref="button"
        x-bind:class="$wire.workTime.is_pause && 'ring-warning-500 text-white bg-warning-500 hover:bg-warning-600 hover:ring-warning-600 dark:ring-offset-slate-800 dark:bg-warning-700 dark:ring-warning-700 dark:hover:bg-warning-600 dark:hover:ring-warning-600'"
        icon="clock"
    >
        <div x-text="msTimeToString(time)"></div>
    </x-button>
    <div x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-show="open"
         x-anchor.bottom-end.offset.5="$refs.button"
         class="z-10"
    >
        <x-card id="active-work-times" class="flex flex-col gap-4 max-w-md" :title="__('Active Work Times')">
            <x-slot:action>
                <x-button.circle xs x-on:click="open = false" icon="x" />
            </x-slot:action>
            <div class="flex w-full gap-1.5">
                <x-button class="w-full" x-show="! $wire.dailyWorkTime.id" positive :label="__('Start Workday')" x-on:click="$wire.toggleWorkDay(true)" />
                <x-button class="w-1/2" x-show="$wire.dailyWorkTime.id" negative :label="__('End Workday')" x-on:click="stopWorkDay()" />
                <x-button class="w-1/2" x-show="$wire.dailyWorkTime.id && ! $wire.dailyWorkTimePause.id" warning :label="__('Pause')" x-on:click="$wire.togglePauseWorkDay(true)" />
                <x-button class="w-1/2" x-show="$wire.dailyWorkTime.id && $wire.dailyWorkTimePause.id" positive :label="__('Continue')" x-on:click="$wire.togglePauseWorkDay(false)" />
            </div>
            <x-button x-show="$wire.dailyWorkTime.id" positive :label="__('Record new working hours')" x-on:click="$openModal('work-time')" />
            <template x-for="workTime in activeWorkTimes">
                <div class="rounded-md p-1.5 flex flex-col gap-1.5">
                    <div class="flex justify-between w-full">
                        <div class="flex flex-col w-full">
                            <div class="text-gray-500 dark:text-gray-400 truncate" x-text="workTime.name"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="workTime.work_time_type?.name"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="formatters.datetime(workTime.started_at)"></div>
                            <x-badge primary>
                                <div x-bind:data-id="workTime.id" x-init="$el.innerText = msTimeToString(calculateTime(workTime))">
                                </div>
                            </x-badge>
                        </div>
                    </div>
                    <div class="flex justify-end gap-x-4">
                        <x-button class="w-1/2" x-show="! workTime.ended_at" warning icon="pause" :label="__('Pause')" x-on:click="pauseWorkTime(workTime)" />
                        <x-button class="w-1/2" x-show="workTime.ended_at" positive icon="play" :label="__('Continue')" x-on:click="continueWorkTime(workTime)" />
                        <x-button class="w-1/2" negative icon="stop" :label="__('Stop')" x-on:click="stopWorkTime(workTime)" />
                        <x-button class="flex-none" primary icon="pencil" wire:click="edit(workTime.id)" />
                    </div>
                </div>
            </template>
        </x-card>
    </div>
</div>

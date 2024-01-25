<div x-data="{
    trackable_type: $wire.entangle('workTime.trackable_type'),
    init() {
        $watch('trackable_type', () => {
            $wire.workTime.trackable_id = null;
            let searchRoute = {{  '\'' . route('search', '__model__') . '\'' }}
            searchRoute = searchRoute.replace('__model__', this.trackable_type);
            Alpine.$data(document.getElementById('trackable-id-edit').querySelector('[x-data]')).asyncData.api = searchRoute;
        });
    }
}">
    <x-modal name="edit-work-time">
        <x-card class="flex flex-col gap-4">
            <div class="flex flex-col gap-1.5" x-cloak x-show="! $wire.workTime.is_daily_work_time">
                <x-select
                    :label="__('Work Time Type')"
                    :options="$workTimeTypes"
                    wire:model="workTime.work_time_type_id"
                    option-value="id"
                    option-label="name"
                    x-on:selected="$wire.workTime.is_billable = $event.detail.is_billable"
                />
                <x-toggle :label="__('Is Billable')" wire:model="workTime.is_billable" />
                <x-select
                    :label="__('User')"
                    option-value="id"
                    option-label="label"
                    autocomplete="off"
                    wire:model="workTime.user_id"
                    :template="[
                        'name'   => 'user-option',
                    ]"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\User::class),
                        'method' => 'POST',
                        'params' => [
                            'with' => 'media',
                        ]
                    ]"
                />
            </div>
            <x-datetime-picker time-format="24"
                :label="__('Started At')"
                display-format="DD.MM.YYYY HH:mm"
                parse-format="YYYY-MM-DD HH:mm:ss"
                wire:model="workTime.started_at"
            />
            <x-datetime-picker time-format="24"
               :label="__('Ended At')"
               display-format="DD.MM.YYYY HH:mm"
               parse-format="YYYY-MM-DD HH:mm:ss"
               wire:model="workTime.ended_at"
            />
            <x-input :label="__('Paused Time')"
                     wire:model.blur="workTime.paused_time"
                     :corner-hint="__('Hours:Minutes')"
            />
            <div class="flex flex-col gap-1.5" x-cloak x-show="! $wire.workTime.is_daily_work_time">
                <x-select :label="__('Contact')"
                    wire:model="workTime.contact_id"
                    option-value="contact_id"
                    option-label="label"
                    template="user-option"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\Address::class),
                        'method' => 'POST',
                        'params' => [
                            'option-value' => 'contact_id',
                            'where' => [
                                [
                                    'is_main_address',
                                    '=',
                                    true,
                                ]
                            ],
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
                    :options="$trackableTypes"
                    wire:model="workTime.trackable_type"
                />
                <div id="trackable-id-edit" x-show="$wire.workTime.trackable_type">
                    <x-select
                        :label="__('Record')"
                        x-on:selected="$event.detail.contact_id ? $wire.workTime.contact_id = $event.detail.contact_id : null"
                        option-value="id"
                        option-label="label"
                        :async-data="[
                            'api' => route('search', '__model__'),
                            'method' => 'POST',
                            'params' => [
                                'appends' => [
                                    'contact_id',
                                ],
                            ],
                        ]"
                        wire:model="workTime.trackable_id"
                    />
                </div>
                <x-input :label="__('Name')" wire:model="workTime.name" />
                <x-textarea :label="__('Description')" wire:model="workTime.description" />
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button primary spinner x-on:click="$wire.save().then((success) => { if (success) close(); })" :label="__('Save')" />
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>

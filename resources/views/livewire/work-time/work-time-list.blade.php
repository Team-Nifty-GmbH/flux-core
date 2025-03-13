<div
    x-data="{
        trackable_type: $wire.entangle('workTime.trackable_type'),
        init() {
            $watch('trackable_type', () => {
                $wire.workTime.trackable_id = null
                let searchRoute = {{ '\'' . route("search", "__model__") . '\'' }}
                searchRoute = searchRoute.replace('__model__', this.trackable_type)
                $tallstackuiSelect('invoice-address-id').setRequestUrl(searchRoute)
            })
        },
    }"
>
    <x-modal id="edit-work-time-modal">
        <div class="flex flex-col gap-1.5">
            <div
                class="flex flex-col gap-1.5"
                x-cloak
                x-show="! $wire.workTime.is_daily_work_time"
            >
                <x-select.styled
                    :label="__('Work Time Type')"
                    wire:model="workTime.work_time_type_id"
                    x-on:select="$wire.workTime.is_billable = $event.detail.select.is_billable"
                    select="label:name|value:id"
                    :options="$workTimeTypes"
                />
                <div class="mb-2 mt-2">
                    <x-toggle
                        :label="__('Is Billable')"
                        wire:model="workTime.is_billable"
                    />
                </div>
                <x-select.styled
                    :label="__('User')"
                    autocomplete="off"
                    wire:model="workTime.user_id"
                    select="label:label|value:id"
                    :request="[
                        'url' => route('search', \FluxErp\Models\User::class),
                        'method' => 'POST',
                        'params' => [
                            'where'=> [
                                'is_active' => true,
                            ],
                            'with' => 'media',
                        ],
                    ]"
                />
            </div>
            <x-date
                time-format="24"
                :label="__('Started At')"
                display-format="DD.MM.YYYY HH:mm"
                parse-format="YYYY-MM-DD HH:mm:ss"
                wire:model="workTime.started_at"
            />
            <x-date
                time-format="24"
                :label="__('Ended At')"
                display-format="DD.MM.YYYY HH:mm"
                parse-format="YYYY-MM-DD HH:mm:ss"
                wire:model="workTime.ended_at"
            />
            <x-input
                :label="__('Paused Time')"
                wire:model.blur="workTime.paused_time"
                :corner-hint="__('Hours:Minutes')"
            />
            <div
                class="flex flex-col gap-1.5"
                x-cloak
                x-show="! $wire.workTime.is_daily_work_time"
            >
                <x-select.styled
                    :label="__('Contact')"
                    wire:model="workTime.contact_id"
                    select="label:label|value:contact_id"
                    :request="[
                        'url' => route('search', \FluxErp\Models\Address::class),
                        'method' => 'POST',
                        'params' => [
                            'option-value' => 'contact_id',
                            'where' => [
                                [
                                    'is_main_address',
                                    '=',
                                    true,
                                ],
                            ],
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
                    select="label:value|value:label"
                    :options="$trackableTypes"
                />
                <div
                    id="trackable-id-edit"
                    x-show="$wire.workTime.trackable_type"
                >
                    <x-select.styled
                        :label="__('Record')"
                        wire:model="workTime.trackable_id"
                        x-on:select="$event.detail.select.contact_id ? $wire.workTime.contact_id = $event.detail.select.contact_id : null"
                        select="label:label|value:id"
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
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('edit-work-time-modal')"
            />
            <x-button
                color="indigo"
                loading
                x-on:click="$wire.save().then((success) => { if (success) $modalClose('edit-work-time-modal'); })"
                :text="__('Save')"
            />
        </x-slot>
    </x-modal>
    <x-modal id="create-orders-modal">
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                :label="__('Order Type')"
                :options="$orderTypes"
                select="label:name|value:id"
                wire:model="createOrdersFromWorkTimes.order_type_id"
            />
            <x-select.styled
                :label="__('Product')"
                wire:model="createOrdersFromWorkTimes.product_id"
                select="label:label|value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\Product::class),
                    'method' => 'POST',
                    'params' => [
                        'where' => [
                            [
                                'is_service',
                                '=',
                                true,
                            ],
                        ],
                    ],
                ]"
            />
            <hr />
            <x-radio
                value="round"
                :label="__('Do not round')"
                wire:model="createOrdersFromWorkTimes.round"
            />
            <x-radio
                value="ceil"
                :label="__('Round up')"
                wire:model="createOrdersFromWorkTimes.round"
            />
            <x-radio
                value="floor"
                :label="__('Round down')"
                wire:model="createOrdersFromWorkTimes.round"
            />
            <div
                x-show="$wire.createOrdersFromWorkTimes.round !== 'round'"
                x-cloak
                x-transition
            >
                <x-number
                    :label="__('Round to nearest minute')"
                    wire:model="createOrdersFromWorkTimes.round_to_minute"
                />
            </div>
            <x-toggle
                :label="__('Add non billable times')"
                wire:model="createOrdersFromWorkTimes.add_non_billable_work_times"
            />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('create-orders-modal')"
            />
            <x-button
                color="indigo"
                loading
                x-on:click="$wire.createOrders().then(() => { $modalClose('create-orders-modal'); })"
                :text="__('Create Orders')"
            />
        </x-slot>
    </x-modal>
    <div x-data="{ isBillable: true }">
        <x-modal id="toggle-is-billable-modal">
            <div class="flex flex-col gap-1.5">
                <x-toggle x-model="isBillable" :label="__('Is Billable')" />
            </div>
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    flat
                    :text="__('Cancel')"
                    x-on:click="$modalClose('toggle-is-billable-modal')"
                />
                <x-button
                    color="indigo"
                    loading
                    wire:click="toggleIsBillable(isBillable).then(() => { $modalClose('toggle-is-billable-modal'); })"
                    :text="__('Apply')"
                />
            </x-slot>
        </x-modal>
    </div>
</div>

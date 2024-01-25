<x-modal name="create-orders">
    <x-card>
        <div class="flex flex-col gap-4">
            <x-select
                :label="__('Order Type')"
                :options="$orderTypes"
                option-key-value
                wire:model="createOrdersFromWorkTimes.order_type_id"
            />
            <x-select
                :label="__('Product')"
                option-value="id"
                option-label="label"
                option-description="description"
                wire:model="createOrdersFromWorkTimes.product_id"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Product::class),
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
            <hr/>
            <x-radio value="round" :label="__('Do not round')" wire:model="createOrdersFromWorkTimes.round"/>
            <x-radio value="ceil" :label="__('Round up')" wire:model="createOrdersFromWorkTimes.round"/>
            <x-radio value="floor" :label="__('Round down')" wire:model="createOrdersFromWorkTimes.round"/>
            <div x-show="$wire.createOrdersFromWorkTimes.round !== 'round'" x-cloak x-transition>
                <x-inputs.number :label="__('Round to nearest minute')" wire:model="createOrdersFromWorkTimes.round_to_minute"/>
            </div>
            <x-toggle :label="__('Add non billable times')" wire:model="createOrdersFromWorkTimes.add_non_billable_work_times" />
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-x-4">
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    <x-button primary spinner x-on:click="$wire.createOrders().then(() => { close(); })" :label="__('Create Orders')" />
                </div>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>

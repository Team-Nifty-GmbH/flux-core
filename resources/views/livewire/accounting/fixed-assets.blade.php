<x-modal id="edit-fixed-asset-modal" :title="__('Fixed Asset')" size="xl">
    <div class="flex flex-col gap-1.5">
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
            <x-select.styled
                :label="__('Purchase Order')"
                wire:model.live.number="fixedAsset.order_id"
                x-on:select="$wire.fixedAsset.order_position_id = null"
                x-on:remove="$wire.fixedAsset.order_position_id = null"
                select="label:label|value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\Order::class),
                    'method' => 'POST',
                ]"
            />
            <div
                wire:key="order-position-{{ $this->fixedAsset->order_id }}"
                x-bind:class="
                    !$wire.fixedAsset.order_id &&
                    'pointer-events-none opacity-60'
                "
            >
                <x-select.styled
                    :label="__('Order Position')"
                    wire:model.number="fixedAsset.order_position_id"
                    x-on:select="
                        $wire.changedOrderPosition($event.detail.select.id)
                    "
                    select="label:name|value:id|description:total_net_price"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\OrderPosition::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => ['name', 'product_number'],
                            'where' => [['order_id', '=', $this->fixedAsset->order_id]],
                        ],
                    ]"
                />
            </div>
        </div>
        <x-flux::fixed-asset.fields />
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Leave the depreciation start empty to start in the month of acquisition.') }}
        </p>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-fixed-asset-modal')"
        />
        <x-button
            color="indigo"
            loading="save"
            :text="__('Save')"
            wire:click="save()"
        />
    </x-slot:footer>
</x-modal>

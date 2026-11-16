<x-modal id="edit-fixed-asset-modal" :title="__('Fixed Asset')" size="xl">
    <div
        class="flex flex-col gap-1.5"
        x-init="
            $watch('$wire.fixedAsset.order_id', (orderId) => {
                $wire.fixedAsset.order_position_id = null;
                $wire.existingFixedAssets = [];
                $tallstackuiSelect(
                    'fixed-asset-order-position',
                ).mergeRequestParams({
                    where: [['order_id', '=', orderId ?? null]],
                });
            })
        "
    >
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
            <x-select.styled
                :label="__('Purchase Order')"
                wire:model.number="fixedAsset.order_id"
                select="label:label|value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\Order::class),
                    'method' => 'POST',
                    'params' => [
                        'whereRelation' => [
                            'orderType',
                            'order_type_enum',
                            '=',
                            \FluxErp\Enums\OrderTypeEnum::Purchase->value,
                        ],
                    ],
                ]"
            />
            <div
                id="fixed-asset-order-position"
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
                    select="label:label|value:id|description:description"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\OrderPosition::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => ['name', 'product_number'],
                            'where' => [['order_id', '=', null]],
                        ],
                    ]"
                />
            </div>
        </div>
        <div x-cloak x-show="$wire.existingFixedAssets.length">
            <x-alert color="amber" light>
                {{ __('This position already has fixed assets. Only create another one if the position covers several assets.') }}
                <template x-for="asset in $wire.existingFixedAssets">
                    <a
                        class="block underline"
                        x-bind:href="asset.url"
                        x-text="asset.name"
                        wire:navigate
                    ></a>
                </template>
            </x-alert>
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

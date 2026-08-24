<x-modal id="edit-warehouse-modal" :title="__('Warehouse')">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="warehouse.name" :label="__('Name')" />
        <div class="mt-2">
            <x-toggle
                wire:model.boolean="warehouse.is_default"
                :label="__('Is Default')"
            />
        </div>
        <div class="mt-2">
            <x-toggle
                wire:model.boolean="warehouse.requires_bin_location"
                :label="__('Requires Bin Location')"
            />
        </div>
        <x-select.styled
            wire:model="warehouse.stock_removal_strategy_enum"
            :label="__('Stock Removal Strategy')"
            required
            select="label:label|value:value"
            :options="\FluxErp\Enums\StockRemovalStrategyEnum::valuesLocalized()"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-warehouse-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.save().then((success) => {
                    if (success) $tsui.close.modal('edit-warehouse-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>

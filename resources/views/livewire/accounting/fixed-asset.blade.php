<div
    x-data="{
        isEditing: false,
    }"
>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            @section('fixed-asset.title')
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-gray-50"
                    >
                        <span x-text="$wire.fixedAsset.name"></span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span x-text="$wire.fixedAsset.number"></span>
                    </p>
                </div>
            @show
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            @canAction(\FluxErp\Actions\FixedAsset\UpdateFixedAsset::class)
                <x-button
                    color="indigo"
                    x-cloak
                    x-show="!isEditing && !$wire.fixedAsset.disposed_at"
                    class="w-full"
                    x-on:click="isEditing = true"
                    :text="__('Edit')"
                />
                <x-button
                    color="indigo"
                    loading="save"
                    x-cloak
                    x-show="isEditing"
                    class="w-full"
                    x-on:click="
                        $wire.save().then((success) => {
                            if (success) isEditing = false;
                        })
                    "
                    :text="__('Save')"
                />
                <x-button
                    color="secondary"
                    light
                    flat
                    :text="__('Cancel')"
                    x-cloak
                    x-show="isEditing"
                    class="w-full"
                    x-on:click="
                        isEditing = false;
                        $wire.resetForm();
                    "
                />
            @endcanAction
            @canAction(\FluxErp\Actions\FixedAsset\BookFixedAssetDepreciations::class)
                <x-button
                    color="emerald"
                    loading="book"
                    :text="__('Book Until Today')"
                    x-cloak
                    x-show="!isEditing"
                    class="w-full"
                    wire:click="book()"
                />
            @endcanAction
            @canAction(\FluxErp\Actions\FixedAsset\ReverseFixedAssetDepreciations::class)
                <x-button
                    color="amber"
                    :text="__('Reverse Bookings')"
                    x-cloak
                    x-show="!isEditing && !$wire.fixedAsset.disposed_at"
                    class="w-full"
                    x-on:click="$tsui.open.modal('reverse-depreciations')"
                />
            @endcanAction
            @canAction(\FluxErp\Actions\FixedAsset\DisposeFixedAsset::class)
                <x-button
                    color="secondary"
                    :text="__('Dispose')"
                    x-cloak
                    x-show="!isEditing && !$wire.fixedAsset.disposed_at"
                    class="w-full"
                    x-on:click="$tsui.open.modal('dispose-fixed-asset')"
                />
            @endcanAction
            @canAction(\FluxErp\Actions\FixedAsset\RevertFixedAssetDisposal::class)
                <x-button
                    color="secondary"
                    loading="revertDisposal"
                    :text="__('Revert Disposal')"
                    x-cloak
                    x-show="!isEditing && $wire.fixedAsset.disposed_at"
                    class="w-full"
                    wire:click="revertDisposal()"
                    wire:flux-confirm.type.warning="{{ __('Revert the disposal and remove its booking?') }}"
                />
            @endcanAction
            @canAction(\FluxErp\Actions\FixedAsset\DeleteFixedAsset::class)
                <x-button
                    color="red"
                    loading="delete"
                    :text="__('Delete')"
                    x-cloak
                    x-show="!isEditing"
                    class="w-full"
                    wire:click="delete()"
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Fixed Asset')]) }}"
                />
            @endcanAction
            @stack('fixed-asset-detail-header-actions')
        </div>
    </div>
    <x-flux::tabs wire:model.live="tab" :$tabs />
    @stack('fixed-asset-detail-after-tabs')
    <x-modal id="dispose-fixed-asset" :title="__('Dispose')">
        <div class="flex flex-col gap-1.5">
            <x-date
                wire:model="disposedAt"
                :label="__('Disposed At')"
                required
            />
            <x-select.styled
                :label="__('Disposal Account')"
                wire:model.number="disposalLedgerAccountId"
                select="label:name|value:id|description:number"
                required
                unfiltered
                :hint="__('The remaining book value is booked to this account.')"
                :request="[
                    'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                    'method' => 'POST',
                    'params' => [
                        'where' => [
                            ['ledger_account_type_enum', '=', \FluxErp\Enums\LedgerAccountTypeEnum::Expense->value],
                            ['tenant_id', '=', $fixedAsset->tenant_id],
                        ],
                    ],
                ]"
            />
        </div>
        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                light
                flat
                x-on:click="$tsui.close.modal('dispose-fixed-asset')"
            />
            <x-button
                :text="__('Dispose')"
                color="indigo"
                loading="dispose"
                x-on:click="
                    $wire.dispose().then((success) => {
                        if (success) $tsui.close.modal('dispose-fixed-asset');
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
    <x-modal id="reverse-depreciations" :title="__('Reverse Bookings')">
        <x-date
            wire:model="reverseFrom"
            :label="__('From')"
            :hint="__('Removes the depreciation bookings from this date on.')"
        />
        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                light
                flat
                x-on:click="$tsui.close.modal('reverse-depreciations')"
            />
            <x-button
                :text="__('Reverse')"
                color="amber"
                loading="reverse"
                x-on:click="
                    $wire.reverse().then((success) => {
                        if (success) $tsui.close.modal('reverse-depreciations');
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
</div>

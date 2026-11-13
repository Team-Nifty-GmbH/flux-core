<div class="flex flex-col gap-1.5">
    <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
        <x-input wire:model="fixedAsset.name" :label="__('Name')" required />
        <x-input wire:model="fixedAsset.number" :label="__('Number')" />
    </div>
    <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
        <x-select.styled
            :label="__('Fixed Asset Account')"
            wire:model.number="fixedAsset.ledger_account_id"
            select="label:name|value:id|description:number"
            required
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                'method' => 'POST',
                'params' => [
                    'where' => [
                        ['ledger_account_type_enum', '=', \FluxErp\Enums\LedgerAccountTypeEnum::Asset->value],
                    ],
                ],
            ]"
        />
        <x-select.styled
            :label="__('Depreciation Account')"
            wire:model.number="fixedAsset.depreciation_ledger_account_id"
            select="label:name|value:id|description:number"
            required
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                'method' => 'POST',
                'params' => [
                    'where' => [
                        ['ledger_account_type_enum', '=', \FluxErp\Enums\LedgerAccountTypeEnum::Expense->value],
                    ],
                ],
            ]"
        />
    </div>
    <x-select.styled
        :label="__('Supplier')"
        wire:model.number="fixedAsset.contact_id"
        select="label:label|value:id"
        :request="[
            'url' => route('search', \FluxErp\Models\Contact::class),
            'method' => 'POST',
        ]"
    />
    <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
        <x-date
            wire:model="fixedAsset.acquired_at"
            :label="__('Acquired At')"
            required
        />
        <x-date
            wire:model="fixedAsset.depreciation_starts_at"
            :label="__('Depreciation Starts At')"
        />
    </div>
    <div class="grid grid-cols-1 gap-1.5 md:grid-cols-3">
        <x-number
            wire:model="fixedAsset.acquisition_cost"
            :label="__('Acquisition Cost')"
            step="0.01"
            placeholder="0.00"
            :hint="__('Net amount')"
            required
        />
        <x-number
            wire:model="fixedAsset.residual_value"
            :label="__('Residual Value')"
            step="0.01"
            min="0"
            placeholder="0.00"
        />
        <x-number
            wire:model="fixedAsset.useful_life_months"
            :label="__('Useful Life In Months')"
            step="1"
            min="1"
            required
        />
    </div>
</div>

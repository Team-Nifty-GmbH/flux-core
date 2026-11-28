<div class="flex flex-col gap-2">
    <x-input
        :label="__('Creditor Identifier')"
        wire:model="tenant.creditor_identifier"
    />
    <x-textarea
        :label="__('Sepa Text Basic')"
        wire:model="tenant.sepa_text_basic"
    />
    <x-textarea
        :label="__('Sepa Text B2B')"
        wire:model="tenant.sepa_text_b2b"
    />
    <div x-cloak x-show="$wire.tenant.id">
        <x-select.styled
            :label="__('Clearing Ledger Account')"
            wire:model="tenant.clearing_ledger_account_id"
            select="label:name|value:id|description:number"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                'method' => 'POST',
            ]"
        />
    </div>
</div>

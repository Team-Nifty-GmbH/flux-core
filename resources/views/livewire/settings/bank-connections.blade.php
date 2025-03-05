<x-modal id="bank-connection-modal" class="flex flex-col gap-4">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="bankConnection.name" :label="__('Name')"/>
        <x-select.styled
            :label="__('Currency')"
            wire:model="bankConnection.currency_id"
            required
            select="label:name|value:id"
            :options="$currencies"
        />
        <x-select.styled
            :label="__('Ledger Account')"
            wire:model="bankConnection.ledger_account_id"
            select="label:name|value:id"
            :options="$ledgerAccounts"
        />
        <x-input wire:model="bankConnection.account_holder" :label="__('Account Holder')"/>
        <x-input x-bind:disabled="$wire.bankConnection.id" wire:model="bankConnection.iban" :label="__('IBAN')"/>
        <x-input wire:model="bankConnection.bic" :label="__('BIC')"/>
        <x-input wire:model="bankConnection.bank_name" :label="__('Bank Name')"/>
        <x-number wire:model="bankConnection.credit_limit" :label="__('Credit Limit')" :min="0" step="1"/>
        <div class="mt-2">
            <x-toggle wire:model="bankConnection.is_active" :label="__('Active')"/>
        </div>
    </div>
    <x-slot:footer>
        <x-button color="secondary" light :text="__('Cancel')" x-on:click="$modalClose('bank-connection-modal')"/>
        <x-button :text="__('Save')" color="indigo" wire:click="save().then((success) => {if(success) $modalClose('bank-connection-modal');});"/>
    </x-slot:footer>
</x-modal>

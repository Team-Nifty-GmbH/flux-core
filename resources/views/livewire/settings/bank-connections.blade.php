<x-modal name="bank-connection-modal">
    <x-card class="flex flex-col gap-4">
        <x-input wire:model="bankConnection.name" :label="__('Name')"/>
        <x-select wire:model="bankConnection.currency_id"  :clearable="false" option-value="id" option-label="name" :options="$currencies" :label="__('Currency')"/>
        <x-select wire:model="bankConnection.ledger_account_id" option-value="id" option-label="name" :options="$ledgerAccounts" :label="__('Ledger Account')"/>
        <x-input wire:model="bankConnection.account_holder" :label="__('Account Holder')"/>
        <x-input x-bind:disabled="$wire.bankConnection.id" wire:model="bankConnection.iban" :label="__('IBAN')"/>
        <x-input wire:model="bankConnection.bic" :label="__('BIC')"/>
        <x-input wire:model="bankConnection.bank_name" :label="__('Bank Name')"/>
        <x-number wire:model="bankConnection.credit_limit" :label="__('Credit Limit')" :min="0" step="1"/>
        <x-toggle wire:model="bankConnection.is_active" :label="__('Active')"/>
        <x-slot:footer>
            <div class="w-full flex justify-end gap-4">
                <x-button :label="__('Cancel')" x-on:click="close"/>
                <x-button :label="__('Save')" primary wire:click="save().then((success) => {if(success) close();});"/>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>

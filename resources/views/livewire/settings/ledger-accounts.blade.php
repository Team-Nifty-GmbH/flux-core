<x-modal id="edit-ledger-account-modal">
    <div class="flex flex-col gap-4">
        <x-select.styled
            x-bind:readonly="!edit"
            :label="__('Client')"
            wire:model="ledgerAccount.client_id"
            select="label:name|value:id"
            :request="[
                'url' => route('search', \FluxErp\Models\Client::class),
                'method' => 'POST',
            ]"
        />
        <x-input wire:model="ledgerAccount.name" :label="__('Name')" />
        <x-input wire:model="ledgerAccount.number" :label="__('Number')" />
        <x-select.styled
            wire:model="ledgerAccount.ledger_account_type_enum"
            required
            :options="$ledgerAccountTypes"
            :label="__('Ledger Account Type')"
        ></x-select.styled>
        <x-toggle wire:model.boolean="ledgerAccount.is_automatic" :label="__('Is Automatic')" />
        <x-textarea wire:model="ledgerAccount.description" :label="__('Description')" />
    </div>
    <x-slot:footer>
        <div class="flex justify-end gap-1.5">
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-ledger-account-modal')"/>
            <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-ledger-account-modal')})"/>
        </div>
    </x-slot:footer>
</x-modal>

<x-modal id="edit-ledger-account-modal" :title="__('Ledger Account')">
    <div class="flex flex-col gap-1.5">
        <x-select.styled
            x-bind:readonly="!edit"
            :label="__('Client')"
            wire:model="ledgerAccount.client_id"
            select="label:name|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Client::class),
                'method' => 'POST',
            ]"
        />
        <x-input wire:model="ledgerAccount.name" :label="__('Name')" />
        <x-input wire:model="ledgerAccount.number" :label="__('Number')" />
        <x-select.styled
            :label="__('Ledger Account Type')"
            wire:model="ledgerAccount.ledger_account_type_enum"
            required
            :options="$ledgerAccountTypes"
        />
        <div class="mt-2">
            <x-toggle
                wire:model.boolean="ledgerAccount.is_automatic"
                :label="__('Is Automatic')"
            />
        </div>
        <x-textarea
            wire:model="ledgerAccount.description"
            :label="__('Description')"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-ledger-account-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-ledger-account-modal')})"
        />
    </x-slot>
</x-modal>

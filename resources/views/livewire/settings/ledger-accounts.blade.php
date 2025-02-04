<x-modal name="edit-ledger-account">
    <x-card>
        <div class="flex flex-col gap-4">
            <x-select
                x-bind:readonly="!edit"
                :label="__('Client')"
                wire:model="ledgerAccount.client_id"
                option-value="id"
                option-label="name"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Client::class),
                    'method' => 'POST',
                ]"
            />
            <x-input wire:model="ledgerAccount.name" :label="__('Name')" />
            <x-input wire:model="ledgerAccount.number" :label="__('Number')" />
            <x-select
                wire:model="ledgerAccount.ledger_account_type_enum"
                :clearable="false"
                :options="$ledgerAccountTypes"
                :label="__('Ledger Account Type')"
            ></x-select>
            <x-toggle wire:model.boolean="ledgerAccount.is_automatic" :label="__('Is Automatic')" />
            <x-textarea wire:model="ledgerAccount.description" :label="__('Description')" />
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-1.5">
                <x-button flat :label="__('Cancel')" x-on:click="close"/>
                <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>

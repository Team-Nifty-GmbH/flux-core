<x-modal :id="$tokenForm->modalName()">
    <div class="flex flex-col gap-4">
        <x-input wire:model="tokenForm.name" :label="__('Name')" />
        <x-textarea
            wire:model="tokenForm.description"
            :label="__('Description')"
            :placeholder="__('Description')"
            :rows="3"
        />
        <x-flux::checkbox-tree
            wire:model="$entangle('tokenForm.permissions')"
            selectable="true"
            tree="$wire.permissions"
            name-attribute="label"
            :with-search="true"
            :search-attributes="['path', 'label']"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('{{ $this->modalName() }}')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('{{ $this->modalName() }}')})"
        />
    </x-slot>
</x-modal>
<x-modal persistent id="copy-token-modal" :title="__('Copy token')">
    <div class="flex flex-col gap-4">
        <div class="text-sm text-gray-500">
            <p>
                {{ __('Copy the token to your clipboard.') }}
            </p>
            <span class="font-semibold text-red-500">
                {{ __('The token will not be shown again after closing the modal.') }}
            </span>
        </div>
        <x-input
            id="token"
            wire:model.defer="tokenForm.plain_text_token"
            readonly
            :label="__('Token')"
        />
    </div>

    <x-slot:footer>
        <x-button
            :text="__('Close')"
            wire:click="modalClose('copy-token-modal')"
            wire:flux-confirm.type.warning="{{ __('Token saved|Confirm that you copied the token.') }}"
        />
    </x-slot>
</x-modal>

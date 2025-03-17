<x-modal id="edit-language-line-modal">
    <div class="flex flex-col gap-4">
        <x-input label="{{ __('Group') }}" placeholder="{{ __('Group') }}" wire:model="languageLineForm.group"/>
        <x-select.styled
            label="{{ __('Locale') }}"
            wire:model="languageLineForm.locale"
            :options="$locales"
        />
        <x-input label="{{ __('Key') }}" placeholder="{{ __('Key') }}" wire:model="languageLineForm.key"/>
        <x-input label="{{ __('Translation') }}" placeholder="{{ __('Translation') }}" wire:model="languageLineForm.translation"/>
    </div>
    <x-slot:footer>
        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-language-line-modal')"/>
        <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => {if(success) $modalClose('edit-language-line-modal');});"/>
    </x-slot:footer>
</x-modal>

<div>
    <div class="grid grid-cols-1 gap-1.5 sm:grid-cols-6">
        <div class="sm:col-span-6">
            <x-input label="{{ __('Group') }}"
                     placeholder="{{ __('Group') }}"
                     wire:model="translation.group"/>
        </div>
        <div class="sm:col-span-6">
            <x-input label="{{ __('Key') }}"
                     placeholder="{{ __('Key') }}"
                     wire:model="translation.key"/>
        </div>
        <div class="sm:col-span-6">
            <x-input label="{{ __('Translation') }}"
                     placeholder="{{ __('Translation') }}"
                     wire:model="translation.translation"/>
        </div>
    </div>
</div>

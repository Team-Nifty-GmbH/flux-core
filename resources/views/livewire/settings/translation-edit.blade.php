<div>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
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
        </div>
    </div>
</div>

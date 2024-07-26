<x-modal name="create-documents">
    <x-card :title="__('Create Documents')">
        <div class="overflow-hidden w-full overflow-x-auto">
            <div class="w-full grid grid-cols-4 gap-4 text-left text-sm">
                <div class="font-bold text-ellipsis overflow-hidden whitespace-nowrap">{{ __('Print') }}</div>
                <div class="font-bold text-ellipsis overflow-hidden whitespace-nowrap">{{ __('Email') }}</div>
                <div class="font-bold text-ellipsis overflow-hidden whitespace-nowrap">{{ __('Download') }}</div>
                <div class="font-bold text-ellipsis overflow-hidden whitespace-nowrap">{{ __('Force Create') }}</div>
            </div>
            <div class="w-full divide-y divide-slate-300 dark:divide-slate-700">
                <template x-for="printLayout in $wire.printLayouts">
                    <div class="w-full grid grid-cols-4 gap-4 py-2">
                        <div class="text-ellipsis overflow-hidden whitespace-nowrap">
                            <x-checkbox x-bind:value="printLayout.layout" wire:model="selectedPrintLayouts.print">
                                <x-slot:label>
                                    <div x-text="printLayout.label"></div>
                                </x-slot:label>
                            </x-checkbox>
                        </div>
                        <div class="text-ellipsis overflow-hidden whitespace-nowrap">
                            <x-checkbox class="truncate" x-bind:value="printLayout.layout" wire:model="selectedPrintLayouts.email">
                                <x-slot:label>
                                    <div x-text="printLayout.label"></div>
                                </x-slot:label>
                            </x-checkbox>
                        </div>
                        <div class="text-ellipsis overflow-hidden whitespace-nowrap">
                            <x-checkbox class="truncate" x-bind:value="printLayout.layout" wire:model="selectedPrintLayouts.download">
                                <x-slot:label>
                                    <div x-text="printLayout.label"></div>
                                </x-slot:label>
                            </x-checkbox>
                        </div>
                        <div class="text-ellipsis overflow-hidden whitespace-nowrap">
                            <x-checkbox class="truncate" x-bind:value="printLayout.layout" wire:model="selectedPrintLayouts.force">
                                <x-slot:label>
                                    <div x-text="printLayout.label"></div>
                                </x-slot:label>
                            </x-checkbox>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-x-4">
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    <x-button primary :label="__('Continue')" spinner wire:click="createDocuments().then(() => { close(); });" />
                </div>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>

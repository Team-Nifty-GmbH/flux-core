<div class="flex h-full flex-col justify-between gap-4 p-4">
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2">
            <div
                class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900"
            >
                <x-icon
                    name="magnifying-glass"
                    class="h-4 w-4 text-orange-600 dark:text-orange-400"
                />
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white">
                {{ __('Scout') }}
            </h3>
        </div>
    </div>
    <div class="flex flex-col gap-2">
        <x-button
            wire:click="deleteAllIndexes"
            wire:flux-confirm.type.warning="{{ __('wire:confirm.delete-all-indexes.scout') }}"
            color="red"
            loading="deleteAllIndexes"
            icon="trash"
            :text="__('Delete All Indexes')"
            class="w-full"
        />
        <x-button
            wire:click="flush"
            wire:flux-confirm.type.warning="{{ __('wire:confirm.flush.scout') }}"
            loading="flush"
            color="red"
            icon="trash"
            :text="__('Flush Models')"
            class="w-full"
        />
        <x-button
            wire:click="import"
            loading="import"
            :text="__('Import Models')"
            class="w-full"
        />
        <x-button
            wire:click="index"
            loading="index"
            :text="__('Create Indexes')"
            class="w-full"
        />
        <x-button
            wire:click="syncIndexSettings"
            loading="syncIndexSettings"
            :text="__('Sync Index Settings')"
            class="w-full"
        />
    </div>
</div>

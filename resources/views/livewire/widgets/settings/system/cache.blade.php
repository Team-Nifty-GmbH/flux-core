<div class="flex h-full flex-col justify-between p-4">
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2">
            <div
                class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900"
            >
                <x-icon
                    name="bolt"
                    class="h-4 w-4 text-indigo-600 dark:text-indigo-400"
                />
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white">
                {{ __('Cache') }}
            </h3>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Cache Driver') }}
                </span>
                <x-badge color="indigo">
                    <x-slot:text>
                        <span x-text="$wire.driver"></span>
                    </x-slot>
                </x-badge>
            </div>
        </div>
    </div>
    <x-button
        wire:click="clearCache"
        color="indigo"
        size="sm"
        icon="trash"
        :text="__('Clear Cache')"
    />
</div>

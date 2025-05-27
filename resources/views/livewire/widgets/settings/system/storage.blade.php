<div class="flex h-full flex-col justify-between p-4" wire:poll.30s="getData">
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2">
            <div
                class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900"
            >
                <x-icon
                    name="arrow-down-on-square-stack"
                    class="h-4 w-4 text-orange-600 dark:text-orange-400"
                />
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white">
                {{ __('Storage') }}
            </h3>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Free Space') }}
                </span>
                <span
                    x-text="$wire.disk_free_space"
                    class="text-sm font-medium text-green-600 dark:text-green-400"
                ></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Total Space') }}
                </span>
                <span
                    x-text="$wire.disk_total_space"
                    class="text-sm font-medium text-gray-900 dark:text-white"
                ></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('View Cache Space') }}
                </span>
                <span
                    x-text="$wire.view_cache_space"
                    class="text-sm font-medium text-gray-900 dark:text-white"
                ></span>
            </div>
        </div>
    </div>
    <x-button
        wire:click="clearViewCache"
        loading="clearViewCache"
        icon="trash"
        :text="__('Clear View Cache')"
    />
</div>

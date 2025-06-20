<div
    class="flex h-full flex-col justify-between gap-4 p-4"
    wire:poll.30s="getData"
>
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
                {{ __('Queue') }}
            </h3>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Queue Connection') }}
                </span>
                <x-badge color="indigo">
                    <x-slot:text>
                        <span x-text="$wire.connection"></span>
                    </x-slot>
                </x-badge>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Queue Driver') }}
                </span>
                <x-badge color="indigo">
                    <x-slot:text>
                        <span x-text="$wire.driver"></span>
                    </x-slot>
                </x-badge>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Queue') }}
                </span>
                <span
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    x-text="$wire.queue"
                ></span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Queue Size') }}
                </span>
                <span
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    x-text="$wire.size"
                ></span>
            </div>
        </div>
    </div>
    <div class="flex flex-col gap-2">
        <x-button
            wire:click="clearQueue"
            wire:flux-confirm.type.warning="{{ __('wire:confirm.clear.queue') }}"
            color="red"
            loading="clearQueue"
            icon="trash"
            :text="__('Clear Queue')"
            class="w-full"
        />
        <x-button
            wire:click="restartQueue"
            loading="restartQueue"
            icon="arrow-path"
            :text="__('Restart Queue')"
            class="w-full"
        />
    </div>
</div>

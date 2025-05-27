<div class="flex flex-col gap-4 p-4">
    <div class="flex items-center gap-2">
        <div
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900"
        >
            <x-icon
                name="command-line"
                class="h-4 w-4 text-red-600 dark:text-red-400"
            />
        </div>
        <h3 class="font-semibold text-gray-900 dark:text-white">
            {{ __('Laravel Framework') }}
        </h3>
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Version') }}
            </span>
            <x-badge color="indigo">
                <x-slot:text>
                    <span x-text="$wire.version"></span>
                </x-slot>
            </x-badge>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Environment') }}
            </span>
            <x-badge
                color="green"
                x-cloak
                x-show="$wire.environment === 'production'"
            >
                <x-slot:text>
                    <span x-text="$wire.environment"></span>
                </x-slot>
            </x-badge>
            <x-badge
                color="amber"
                x-cloak
                x-show="$wire.environment !== 'production'"
            >
                <x-slot:text>
                    <span x-text="$wire.environment"></span>
                </x-slot>
            </x-badge>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Debug Mode') }}
            </span>
            <x-badge
                color="amber"
                x-cloak
                x-show="$wire.debug_mode"
                :text="__('Enabled')"
            />
            <x-badge
                color="green"
                x-cloak
                x-show="! $wire.debug_mode"
                :text="__('Disabled')"
            />
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Timezone') }}
            </span>
            <span
                class="text-sm font-medium text-gray-900 dark:text-white"
                x-text="$wire.timezone"
            ></span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Locale') }}
            </span>
            <span
                class="text-sm font-medium text-gray-900 dark:text-white"
                x-text="$wire.locale"
            ></span>
        </div>
    </div>
</div>

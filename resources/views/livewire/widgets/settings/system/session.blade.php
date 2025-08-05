<div class="flex h-full flex-col justify-between p-4">
    <div class="flex flex-col gap-4">
        <div
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900"
        >
            <x-icon
                name="bolt"
                class="h-4 w-4 text-indigo-600 dark:text-indigo-400"
            />
        </div>
        <h3 class="font-semibold text-gray-900 dark:text-white">
            {{ __('Sessions') }}
        </h3>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Session Driver') }}
                </span>
                <x-badge color="indigo">
                    <x-slot:text>
                        <span x-text="$wire.driver"></span>
                    </x-slot>
                </x-badge>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Session Lifetime') }}
                </span>
                <span
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    x-text="$wire.lifetime + 'min'"
                ></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Secure Sessions') }}
                </span>
                <x-badge
                    color="green"
                    x-cloak
                    x-show="$wire.secure"
                    :text="__('Yes')"
                />
                <x-badge
                    color="red"
                    x-cloak
                    x-show="! $wire.secure"
                    :text="__('No')"
                />
            </div>
        </div>
    </div>
</div>

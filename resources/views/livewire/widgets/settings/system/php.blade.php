<div class="flex flex-col gap-4 p-4">
    <div class="flex items-center gap-2">
        <div
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900"
        >
            <x-icon
                name="code-bracket"
                class="h-4 w-4 text-blue-600 dark:text-blue-400"
            />
        </div>
        <h3 class="font-semibold text-gray-900 dark:text-white">
            {{ __('PHP Configuration') }}
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
                {{ __('Memory Limit') }}
            </span>
            <span
                class="text-sm font-medium text-gray-900 dark:text-white"
                x-text="$wire.memory_limit"
            ></span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Max Execution') }}
            </span>
            <span
                class="text-sm font-medium text-gray-900 dark:text-white"
                x-text="$wire.max_execution_time + 's'"
            ></span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Upload Max') }}
            </span>
            <span
                class="text-sm font-medium text-gray-900 dark:text-white"
                x-text="$wire.upload_max_filesize"
            ></span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Post Max Size') }}
            </span>
            <span
                class="text-sm font-medium text-gray-900 dark:text-white"
                x-text="$wire.post_max_size"
            ></span>
        </div>
    </div>
</div>

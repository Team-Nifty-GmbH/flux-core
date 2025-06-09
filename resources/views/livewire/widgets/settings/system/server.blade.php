<div class="flex flex-col gap-4 p-4">
    <div class="flex items-center gap-2">
        <div
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900"
        >
            <x-icon
                name="server"
                class="h-4 w-4 text-green-600 dark:text-green-400"
            />
        </div>
        <h3 class="font-semibold text-gray-900 dark:text-white">
            {{ __('Server Details') }}
        </h3>
    </div>

    <div class="space-y-3">
        <div>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Software') }}
            </span>
            <span
                x-text="$wire.software"
                class="text-sm font-medium break-words text-gray-900 dark:text-white"
            ></span>
        </div>
        <div>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Operating System') }}
            </span>
            <span
                x-text="$wire.os"
                class="text-sm font-medium break-words text-gray-900 dark:text-white"
            ></span>
        </div>
        <div>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Server Name') }}
            </span>
            <span
                x-text="$wire.server_name"
                class="text-sm font-medium break-words text-gray-900 dark:text-white"
            ></span>
        </div>
        <div>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Document root') }}
            </span>
            <span
                x-text="$wire.document_root"
                class="text-sm font-medium break-words text-gray-900 dark:text-white"
            ></span>
        </div>
    </div>
</div>

<div class="flex h-full flex-col justify-between p-4" wire:poll.30s="getData">
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2">
            <div
                class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900"
            >
                <x-icon
                    name="circle-stack"
                    class="h-4 w-4 text-purple-600 dark:text-purple-400"
                />
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white">
                {{ __('Database') }}
            </h3>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Connection') }}
                </span>
                <x-badge color="purple">
                    <x-slot:text>
                        <span x-text="$wire.connection"></span>
                    </x-slot>
                </x-badge>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Driver') }}
                </span>
                <x-badge color="purple">
                    <x-slot:text>
                        <span x-text="$wire.driver"></span>
                    </x-slot>
                </x-badge>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Host') }}
                </span>
                <span
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    x-text="$wire.host"
                ></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Port') }}
                </span>
                <span
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    x-text="$wire.port"
                ></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Open Connections') }}
                </span>
                <span
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    x-text="$wire.platform.open_connections"
                ></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Version') }}
                </span>
                <x-badge color="blue">
                    <x-slot:text>
                        <span x-text="$wire.platform.version"></span>
                    </x-slot>
                </x-badge>
            </div>
        </div>
        <div class="flex flex-col overflow-auto">
            <template x-for="table in $wire.tables ?? []">
                <div class="grid grid-cols-3">
                    <span
                        class="text-sm text-gray-600 dark:text-gray-400"
                        x-text="table.table"
                    ></span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                        x-text="table.rows"
                    ></span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                        x-text="table.size"
                    ></span>
                </div>
            </template>
        </div>
    </div>
    <x-button
        wire:click="pruneDatabase"
        color="indigo"
        size="sm"
        icon="trash"
        :text="__('Prune Database')"
    />
</div>

@php
    use Illuminate\Support\Number;
@endphp

<div wire:poll.30s class="space-y-6">
    <div class="flex items-center justify-end">
        <x-button
            wire:click="refreshSystemInfo"
            color="emerald"
            size="sm"
            icon="arrow-path"
            :text="__('Refresh')"
        />
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        <x-card>
            <x-slot:header>
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
            </x-slot>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Version') }}
                    </span>
                    <x-badge
                        color="blue"
                        :text="data_get($systemData, 'php.version')"
                    />
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Memory Limit') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'php.memory_limit') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Max Execution') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'php.max_execution_time') }}s
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Upload Max') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'php.upload_max_filesize') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Post Max Size') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'php.post_max_size') }}
                    </span>
                </div>
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
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
            </x-slot>

            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Software') }}
                    </span>
                    <p
                        class="break-words text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'server.software') }}
                    </p>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Operating System') }}
                    </span>
                    <p
                        class="break-words text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'server.os') }}
                    </p>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Server Name') }}
                    </span>
                    <p
                        class="break-words text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'server.server_name') }}
                    </p>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Document root') }}
                    </span>
                    <p
                        class="break-words text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'server.document_root') }}
                    </p>
                </div>
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
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
            </x-slot>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Version') }}
                    </span>
                    <x-badge
                        color="red"
                        :text="data_get($systemData, 'laravel.version')"
                    />
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Environment') }}
                    </span>
                    <x-badge
                        :color="data_get($systemData, 'laravel.environment') === 'production' ? 'green' : 'amber'"
                        :text="ucfirst(data_get($systemData, 'laravel.environment'))"
                    />
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Debug Mode') }}
                    </span>
                    <x-badge
                        :color="data_get($systemData, 'laravel.debug_mode') ? 'amber' : 'green'"
                        :text="data_get($systemData, 'laravel.debug_mode') ? __('Enabled') : __('Disabled')"
                    />
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Timezone') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'laravel.timezone') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Locale') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'laravel.locale') }}
                    </span>
                </div>
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
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
                        {{ __('Cache & Sessions') }}
                    </h3>
                </div>
            </x-slot>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Cache Driver') }}
                    </span>
                    <x-badge
                        color="indigo"
                        :text="data_get($systemData, 'cache.driver')"
                    />
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Session Driver') }}
                    </span>
                    <x-badge
                        color="indigo"
                        :text="data_get($systemData, 'session.driver')"
                    />
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Session Lifetime') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'session.lifetime') }}
                        {{ __('min') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Secure Sessions') }}
                    </span>
                    <x-badge
                        :color="data_get($systemData, 'session.secure') ? 'green' : 'red'"
                        :text="data_get($systemData, 'session.secure') ? __('Yes') : __('No')"
                    />
                </div>
            </div>
            <x-slot:footer>
                <x-button
                    wire:click="clearCache"
                    color="indigo"
                    size="sm"
                    icon="trash"
                    :text="__('Clear Cache')"
                />
            </x-slot>
        </x-card>

        <x-card>
            <x-slot:header>
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
            </x-slot>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Queue Connection') }}
                    </span>
                    <x-badge
                        color="indigo"
                        :text="data_get($systemData, 'queue.connection')"
                    />
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Queue Driver') }}
                    </span>
                    <x-badge
                        color="indigo"
                        :text="data_get($systemData, 'queue.connection')"
                    />
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Queue') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'queue.queue') }}
                    </span>
                </div>
            </div>
            <x-slot:footer>
                <x-button
                    wire:click="clearQueue"
                    color="indigo"
                    size="sm"
                    icon="trash"
                    :text="__('Clear Queue')"
                />
                <x-button
                    wire:click="restartQueue"
                    color="indigo"
                    size="sm"
                    icon="arrow-path"
                    :text="__('Restart Queue')"
                />
            </x-slot>
        </x-card>

        <x-card>
            <x-slot:header>
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
            </x-slot>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Free Space') }}
                    </span>
                    <span
                        class="text-sm font-medium text-green-600 dark:text-green-400"
                    >
                        {{ data_get($systemData, 'storage.disk_free_space') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Total Space') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'storage.disk_total_space') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('View Cache Space') }}
                    </span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ data_get($systemData, 'storage.view_cache_space') }}
                    </span>
                </div>
            </div>

            <x-slot:footer>
                <x-button
                    wire:click="clearViewCache"
                    color="indigo"
                    size="sm"
                    icon="trash"
                    :text="__('Clear View Cache')"
                />
            </x-slot>
        </x-card>
    </div>

    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900"
                    >
                        <x-icon
                            name="cpu-chip"
                            class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                        />
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        {{ __('PHP Extensions') }}
                    </h3>
                </div>

                <x-button
                    wire:click="toggleDetails"
                    color="gray"
                    size="sm"
                    outline
                >
                    {{ $showDetails ? __('Hide Details') : __('Show Details') }}
                </x-button>
            </div>
        </x-slot>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($systemData['extensions'] as $extension => $loaded)
                <div
                    class="{{ $loaded ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' }} flex items-center justify-between rounded-lg border p-3"
                >
                    <span
                        class="{{ $loaded ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100' }} text-sm font-medium"
                    >
                        {{ $extension }}
                    </span>
                    <div
                        class="{{ $loaded ? 'bg-green-500' : 'bg-red-500' }} h-2 w-2 rounded-full"
                    ></div>
                </div>
            @endforeach
        </div>
    </x-card>
    <x-card>
        <x-slot:header>
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
        </x-slot>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Connection') }}
                </span>
                <x-badge
                    color="purple"
                    :text="data_get($systemData, 'database.connection')"
                />
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Driver') }}
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ data_get($systemData, 'database.driver') }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Host') }}
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ data_get($systemData, 'database.host') }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Port') }}
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ data_get($systemData, 'database.port') }}
                </span>
            </div>
        </div>
        <div class="grid grid-cols-3">
            @foreach (data_get($systemData, 'database.details.tables') as $table)
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ data_get($table, 'table') }}
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ Number::format(data_get($table, 'rows')) }}
                    {{ __('rows') }}
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ Number::fileSize(data_get($table, 'size')) }}
                </span>
            @endforeach
        </div>
        <x-slot:footer>
            <x-button
                wire:click="pruneDatabase"
                color="indigo"
                size="sm"
                icon="trash"
                :text="__('Prune Database')"
            />
        </x-slot>
    </x-card>
</div>

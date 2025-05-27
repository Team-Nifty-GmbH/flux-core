<div
    class="grid h-full grid-rows-[auto_auto_auto_1fr_auto] gap-4 p-4"
    wire:poll.30s="getData"
    x-data="{
        sortBy: 'table',
        sortDirection: 'asc',
        sortedTables() {
            if (! $wire.tables) return []

            const tables = [...$wire.tables]
            return tables.sort((a, b) => {
                let aVal, bVal

                if (this.sortBy === 'size') {
                    // Use raw size in bytes
                    aVal = parseInt(a.size)
                    bVal = parseInt(b.size)
                } else if (this.sortBy === 'rows') {
                    aVal = parseInt(a.rows)
                    bVal = parseInt(b.rows)
                } else if (this.sortBy === 'database') {
                    aVal = a.database.toLowerCase()
                    bVal = b.database.toLowerCase()
                } else {
                    aVal = a.table.toLowerCase()
                    bVal = b.table.toLowerCase()
                }

                if (this.sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1
                } else {
                    return aVal < bVal ? 1 : -1
                }
            })
        },
        sort(column) {
            if (this.sortBy === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc'
            } else {
                this.sortBy = column
                this.sortDirection = 'asc'
            }
        },
    }"
>
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
            <x-badge color="indigo">
                <x-slot:text>
                    <span x-text="$wire.connection"></span>
                </x-slot>
            </x-badge>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Driver') }}
            </span>
            <x-badge color="indigo">
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
            <x-badge color="indigo">
                <x-slot:text>
                    <span x-text="$wire.platform.version"></span>
                </x-slot>
            </x-badge>
        </div>
    </div>

    <div class="overflow-auto">
        <table class="w-full text-sm">
            <thead
                class="sticky top-0 border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900"
            >
                <tr>
                    <th class="px-2 py-2 text-left">
                        <button
                            x-on:click="sort('database')"
                            class="flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        >
                            {{ __('Database') }}
                            <i
                                class="ph ph-caret-down text-xs transition-transform duration-200"
                                x-bind:class="
                                    sortBy === 'database' && sortDirection === 'desc'
                                        ? 'rotate-0 opacity-100'
                                        : sortBy === 'database' && sortDirection === 'asc'
                                          ? 'rotate-180 opacity-100'
                                          : 'opacity-50'
                                "
                            ></i>
                        </button>
                    </th>
                    <th class="px-2 py-2 text-left">
                        <button
                            x-on:click="sort('table')"
                            class="flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        >
                            {{ __('Table') }}
                            <i
                                class="ph ph-caret-down text-xs transition-transform duration-200"
                                x-bind:class="
                                    sortBy === 'table' && sortDirection === 'desc'
                                        ? 'rotate-0 opacity-100'
                                        : sortBy === 'table' && sortDirection === 'asc'
                                          ? 'rotate-180 opacity-100'
                                          : 'opacity-50'
                                "
                            ></i>
                        </button>
                    </th>
                    <th class="px-2 py-2 text-left">
                        <button
                            x-on:click="sort('rows')"
                            class="flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        >
                            {{ __('Rows') }}
                            <i
                                class="ph ph-caret-down text-xs transition-transform duration-200"
                                x-bind:class="
                                    sortBy === 'rows' && sortDirection === 'desc'
                                        ? 'rotate-0 opacity-100'
                                        : sortBy === 'rows' && sortDirection === 'asc'
                                          ? 'rotate-180 opacity-100'
                                          : 'opacity-50'
                                "
                            ></i>
                        </button>
                    </th>
                    <th class="px-2 py-2 text-left">
                        <button
                            x-on:click="sort('size')"
                            class="flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        >
                            {{ __('Size') }}
                            <i
                                class="ph ph-caret-down text-xs transition-transform duration-200"
                                x-bind:class="
                                    sortBy === 'size' && sortDirection === 'desc'
                                        ? 'rotate-0 opacity-100'
                                        : sortBy === 'size' && sortDirection === 'asc'
                                          ? 'rotate-180 opacity-100'
                                          : 'opacity-50'
                                "
                            ></i>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template
                    x-for="(table, index) in sortedTables()"
                    :key="table.schema_qualified_name"
                >
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td
                            class="px-2 py-1 text-gray-600 dark:text-gray-400"
                            x-text="table.database"
                        ></td>
                        <td
                            class="px-2 py-1 text-gray-600 dark:text-gray-400"
                            x-text="table.table"
                        ></td>
                        <td
                            class="px-2 py-1 font-medium text-gray-900 dark:text-white"
                            x-text="table.rows_human"
                        ></td>
                        <td
                            class="px-2 py-1 font-medium text-gray-900 dark:text-white"
                            x-text="table.size_human"
                        ></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <x-button
        wire:click="pruneDatabase"
        loading="pruneDatabase"
        size="sm"
        icon="trash"
        :text="__('Prune Database')"
    />
</div>

<div class="flex flex-col gap-4">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ __('Generate a dashboard widget from the current filter configuration.') }}
    </p>

    <div
        x-cloak
        x-show="($wire.userFilters || []).length > 0"
    >
        <p class="mb-2 text-xs font-medium text-gray-500 dark:text-gray-400">
            {{ __('Active filters') }}
        </p>
        <div class="flex flex-wrap gap-1.5">
            <template x-for="(orFilters, orIndex) in ($wire.userFilters || [])">
                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-if="orIndex > 0">
                        <x-badge flat color="emerald" :text="__('or')" />
                    </template>
                    <template x-for="(filter, index) in orFilters">
                        <div class="flex items-center gap-1">
                            <x-badge flat color="indigo">
                                <x-slot:text>
                                    <span x-text="filterBadge(filter)"></span>
                                </x-slot>
                            </x-badge>
                            <template x-if="orFilters.length - 1 !== index">
                                <x-badge flat color="red" :text="__('and')" />
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <div
        x-cloak
        x-show="($wire.userFilters || []).length === 0"
    >
        <p class="text-xs italic text-gray-400 dark:text-gray-500">
            {{ __('No filters set — all data will be included.') }}
        </p>
    </div>

    <x-button
        color="primary"
        sm
        icon="chart-bar"
        :text="__('Save as Widget')"
        wire:click="openWidgetWizard"
    />
</div>

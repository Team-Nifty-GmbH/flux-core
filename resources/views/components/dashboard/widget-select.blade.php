@teleport('body')
    <x-modal id="widget-list" scrollable>
        <div class="h-full overflow-auto p-2.5">
            <div class="flex items-center justify-between pb-6">
                <h2
                    class="flex-1 truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
                >
                    {{ __('Available Widgets') }}
                </h2>
                <div
                    x-cloak
                    x-show="isLoading"
                    class="h-6 w-6 animate-spin rounded-full border-4 border-primary-200 border-t-primary-500 dark:border-white dark:border-t-gray-400"
                ></div>
            </div>
            <x-flux::checkbox-tree
                tree="$wire.availableWidgetsTree"
                name-attribute="label"
                :with-search="true"
                :hide-icon="true"
                x-on:folder-tree-select="if (! $event.detail.children) { isLoading ? null : selectWidget($event.detail.component_name); }"
            />
        </div>
        <x-slot:footer>
            <div class="flex justify-end">
                <x-button
                    color="secondary"
                    :text="__('Close')"
                    x-on:click="$modalClose('widget-list')"
                />
            </div>
        </x-slot>
    </x-modal>
@endteleport

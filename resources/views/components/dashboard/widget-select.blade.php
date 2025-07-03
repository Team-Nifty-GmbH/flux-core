@teleport('body')
    <x-modal id="widget-list">
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
                    class="border-primary-200 border-t-primary-500 h-6 w-6 animate-spin rounded-full border-4 dark:border-white dark:border-t-gray-400"
                ></div>
            </div>
            @forelse ($this->availableWidgets as $widget)
                <div
                    x-on:click="isLoading ? null : selectWidget('{{ $widget['component_name'] }}')"
                    class="mb-2 w-full cursor-pointer rounded border p-2"
                    :class="isLoading ? 'bg-gray-200 dark:bg-secondary-800 cursor-wait' : 'dark:hover:bg-secondary-900 hover:bg-gray-100'"
                >
                    {{ __($widget['label']) }}
                </div>
            @empty
                <div
                    class="mx-auto flex h-full flex-col items-center justify-center"
                >
                    <h2 class="text-2xl font-medium">
                        {{ __('No widgets available') }}
                    </h2>
                </div>
            @endforelse
        </div>
    </x-modal>
@endteleport

<x-modal name="widget-list">
    <x-card>
        <div class="h-full p-2.5 overflow-auto">
            <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400 pb-6">{{ __('Available Widgets') }}</h2>
            @forelse($this->availableWidgets as $widget)
                <div
                    x-on:click="selectWidget('{{ $widget['component_name'] }}')"
                    class="w-full cursor-pointer mb-2 p-2 border rounded hover:bg-gray-100 dark:hover:bg-secondary-900"
                >
                    {{ __($widget['label']) }}
                </div>
            @empty
                <div class="h-full mx-auto flex flex-col justify-center items-center">
                    <h2 class="text-2xl font-medium">{{ __('No widgets available') }}</h2>
                </div>
            @endforelse
        </div>
    </x-card>
</x-modal>

@teleport("body")
    <x-modal id="widget-list">
        <div class="h-full overflow-auto p-2.5">
            <h2
                class="truncate pb-6 text-lg font-semibold text-gray-700 dark:text-gray-400"
            >
                {{ __("Available Widgets") }}
            </h2>
            @forelse ($this->availableWidgets as $widget)
                <div
                    x-on:click="selectWidget('{{ $widget["component_name"] }}')"
                    class="mb-2 w-full cursor-pointer rounded border p-2 hover:bg-gray-100 dark:hover:bg-secondary-900"
                >
                    {{ __($widget["label"]) }}
                </div>
            @empty
                <div
                    class="mx-auto flex h-full flex-col items-center justify-center"
                >
                    <h2 class="text-2xl font-medium">
                        {{ __("No widgets available") }}
                    </h2>
                </div>
            @endforelse
        </div>
    </x-modal>
@endteleport

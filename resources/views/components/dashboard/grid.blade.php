<div class="grid-stack">
    @forelse($this->widgets as $widget)
        <div class="grid-stack-item rounded-lg relative z-0"
             gs-id="{{$widget['id']}}"
             gs-w="{{$widget['width']}}"
             gs-h="{{$widget['height']}}"
             gs-x="{{$widget['order_column']}}"
             gs-y="{{$widget['order_row']}}"
        >
            <div class="grid-stack-item-content"
                 x-bind:class="editGrid ? 'border-4 border-primary-500' : ''"
            >
                <div class="absolute top-2 right-2 z-10">
                    <x-mini-button
                        x-cloak
                        x-show="editGrid"
                        x-on:click="isLoading ? pendingMessage : removeWidget('{{$widget['id']}}')"
                        class="shadow-md w-4 h-4 text-gray-400 cursor-pointer" icon="trash" negative />
                </div>
                <div
                    class="w-full"
                    x-bind:class="editGrid ? 'pointer-events-none' : ''">
                    <livewire:is
                        lazy
                        :id="$widget['id']"
                        :component="$widget['component_name'] ?? $widget['class']"
                        wire:model="params"
                        wire:key="{{ uniqid() }}"
                    />
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-12 h-96"></div>
    @endforelse
</div>

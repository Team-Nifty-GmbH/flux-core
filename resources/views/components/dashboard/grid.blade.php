<div class="grid-stack" wire:ignore>
    @forelse ($this->widgets as $widget)
        <div
            class="grid-stack-item relative z-0 rounded-lg"
            gs-id="{{ $widget['id'] }}"
            gs-w="{{ $widget['width'] }}"
            gs-h="{{ $widget['height'] }}"
            gs-x="{{ $widget['order_column'] }}"
            gs-y="{{ $widget['order_row'] }}"
        >
            <div
                class="grid-stack-item-content"
                x-bind:class="editGrid ? 'border-4 border-primary-500' : ''"
            >
                <div class="absolute right-2 top-2 z-10">
                    <x-button.circle
                        x-cloak
                        x-show="editGrid"
                        x-on:click="isLoading ? pendingMessage : removeWidget('{{$widget['id']}}')"
                        class="h-4 w-4 cursor-pointer text-gray-400 shadow-md"
                        icon="trash"
                        color="red"
                    />
                </div>
                <div
                    class="w-full"
                    x-bind:class="editGrid ? 'pointer-events-none' : ''"
                >
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

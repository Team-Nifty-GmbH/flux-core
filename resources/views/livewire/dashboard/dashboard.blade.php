<div
    wire:ignore.self
     x-data="dashboard($wire)">
    <div wire:ignore class="mx-auto py-6 flex justify-between items-center">
        <div class="pb-6 md:flex md:items-center md:justify-between md:space-x-5">
            <div class="flex items-start space-x-5">
                <div class="flex-shrink-0">
                    <x-avatar :src="auth()->user()->getAvatarUrl()" />
                </div>
                <div class="pt-1.5">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ __('Hello') }} {{ Auth::user()->name }}</h1>
                </div>
            </div>
        </div>
        <div>
            <x-button x-on:click="addPlaceHolder" class="flex-shrink-0">{{ __('Save') }}</x-button>
            <x-button x-on:click="syncGrid" class="flex-shrink-0">Snapshot</x-button>
        </div>
    </div>
    <div class="grid-stack">
        @forelse($widgets as $widget)
            <div class="grid-stack-item"
                 gs-id="{{$widget['id']}}"
                 gs-w="{{$widget['width']}}"
                 gs-h="{{$widget['height']}}"
                 gs-x="{{$widget['order_column']}}"
                 gs-y="{{$widget['order_row']}}"
            >
                <div class="grid-stack-item-content p-1.5 rounded flex place-content-center relative col-span-full"
                            >
                                <div class="absolute top-2 right-2 z-10">
                                    <x-button.circle class="shadow-md w-4 h-4 text-gray-400 cursor-pointer" icon="trash" negative/>
                                </div>
                                <div class="z-0 w-full">
                                    <livewire:is :id="$widget['id']" lazy :component="$widget['component_name'] ?? $widget['class']" wire:key="{{ uniqid() }}" />
                                </div>
                            </div>
            </div>
                        @empty
                            <div class="col-span-12 h-96"></div>
                        @endforelse
    </div>
</div>

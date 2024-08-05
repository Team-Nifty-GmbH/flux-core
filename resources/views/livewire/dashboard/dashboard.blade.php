<div
    x-data="dashboard($wire)">
    <x-modal name="confirm" persistent>
        <x-card
            class="flex justify-end gap-2"
            title="{{__('All changes will be lost. Are you sure?')}}"
        >
            <x-button primary x-on:click="cancelDashboard().then((_)=>close())">{{__('Confirm')}}</x-button>
            <x-button x-on:click="close" negative>{{__('Cancel')}}</x-button>
        </x-card>
    </x-modal>
    <div class="mx-auto py-6 flex justify-between items-center">
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
        <div x-cloak x-show="!editGrid">
            <x-button
                x-on:click="isLoading ? pendingMessage : editGridMode(true)"
                class="flex-shrink-0">{{ __('Edit Dashboard') }}</x-button>
        </div>
        <div x-cloak x-show="editGrid">
            <x-button x-on:click="isLoading ? pendingMessage : addPlaceHolder" class="flex-shrink-0">{{ __('Add') }}</x-button>
            <x-button
                primary
                x-cloak
                x-show="openGridItems"
                x-on:click="isLoading ? pendingMessage : save"
                class="flex-shrink-0">{{__('Save')}}</x-button>
            <x-button
                negative
                x-on:click="isLoading ? pendingMessage : $openModal('confirm')"
                wire:flux-confirm.icon.error="cancelDashboard"
                class="flex-shrink-0">{{__('Cancel')}}</x-button>
        </div>
    </div>
    <div class="grid-stack">
        @forelse($widgets as $widget)
            <div class="grid-stack-item rounded-lg relative"
                 gs-id="{{$widget['id']}}"
                 gs-w="{{$widget['width']}}"
                 gs-h="{{$widget['height']}}"
                 gs-x="{{$widget['order_column']}}"
                 gs-y="{{$widget['order_row']}}"
            >
                <div class="grid-stack-item-content flex border-white place-content-center col-span-full border border-4"
                     x-bind:class="editGrid ? 'dark:border-primary-500' : 'dark:border-secondary-800'"
                >
                    <div class="absolute top-2 right-2 z-10">
                        <x-button.circle
                            x-cloak
                            x-show="editGrid"
                            x-on:click="isLoading ? pendingMessage : removeWidget('{{$widget['id']}}')"
                            class="shadow-md w-4 h-4 text-gray-400 cursor-pointer" icon="trash" negative />
                    </div>
                    <div
                        class="w-full"
                        x-bind:class="editGrid && !isWidgetList('{{$widget['id']}}') ? 'pointer-events-none' : ''">
                        <livewire:is lazy  :id="$widget['id']" :component="$widget['component_name'] ?? $widget['class']"
                                     wire:key="{{ uniqid() }}" />
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-12 h-96"></div>
        @endforelse
    </div>
</div>

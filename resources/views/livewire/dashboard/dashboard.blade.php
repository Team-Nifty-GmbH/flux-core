<div x-data="dashboard($wire)">
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
            <x-button
                x-on:click="isLoading ? $wire.showFlashMessage() : addPlaceHolder()"
                class="flex-shrink-0"
                :label="__('Add')"
            />
            <x-button
                primary
                x-cloak
                x-show="openGridItems"
                x-on:click="isLoading ? pendingMessage : save"
                :label="__('Save')"
                class="flex-shrink-0"
            />
            <x-button
                negative
                wire:flux-confirm.icon.error="{{ __('wire:confirm.cancel.dashboard-edit') }}"
                wire:click="cancelDashboard().then(() => {reInit().disable(); isLoading = false; editGridMode(false);})"
                class="flex-shrink-0"
                :label="__('Cancel')"
            />
        </div>
    </div>
    <div class="grid-stack">
        @forelse($widgets as $widget)
            <div class="grid-stack-item rounded-lg relative z-0"
                 gs-id="{{$widget['id']}}"
                 gs-w="{{$widget['width']}}"
                 gs-h="{{$widget['height']}}"
                 gs-x="{{$widget['order_column']}}"
                 gs-y="{{$widget['order_row']}}"
            >
                <div class="grid-stack-item-content"
                     x-bind:class="editGrid ? 'border border-4 border-primary-500' : ''"
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
                        x-bind:class="editGrid && ! isWidgetList('{{$widget['id']}}') ? 'pointer-events-none' : ''">
                        <livewire:is
                            lazy
                            :id="$widget['id']"
                            :component="$widget['component_name'] ?? $widget['class']"
                            wire:key="{{ uniqid() }}"
                        />
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-12 h-96"></div>
        @endforelse
    </div>
</div>

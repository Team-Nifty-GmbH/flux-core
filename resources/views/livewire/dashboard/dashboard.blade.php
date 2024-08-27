<div x-data="dashboard($wire)">
    <x-modal name="widget-list">
        <x-card>
            <div class="h-full p-2.5 overflow-auto">
                <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400 pb-6">{{ __('Available Widgets') }}</h2>
                @forelse($availableWidgets as $widget)
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
    <div class="mx-auto py-6 md:flex justify-between items-center">
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
        <div class="flex flex-col md:flex-row gap-1.5">
            <div x-cloak x-show="!editGrid" class="flex flex-col md:flex-row gap-1.5 items-center text-sm">
                <x-select
                    class="p-2"
                    :options="$timeFrames"
                    option-value="value"
                    option-label="label"
                    wire:model.live="timeFrame"
                    :clearable="false"
                />
                <div class="flex flex-col md:flex-row gap-1.5 items-center min-w-96" x-cloak x-show="$wire.timeFrame === 'Custom'">
                    <x-datetime-picker wire:model.live="start" :without-time="true"/>
                    <div>
                        <span class="px-1.5">{{ __('Till') }}</span>
                    </div>
                    <x-datetime-picker wire:model.live="end" :without-time="true"/>
                </div>
            </div>
            <div class="flex flex-col md:flex-row gap-1.5 items-center">
                <x-button
                    x-cloak
                    x-show="!editGrid"
                    x-on:click="isLoading ? pendingMessage : editGridMode(true)"
                    icon="pencil"
                    class="flex-shrink-0"
                />
                <div x-cloak x-show="editGrid" class="flex gap-1.5">
                    <x-button
                        x-on:click="$openModal('widget-list')"
                        class="flex-shrink-0"
                        :label="__('Add')"
                    />
                    <x-button
                        primary
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
                        x-bind:class="editGrid ? 'pointer-events-none' : ''">
                        <livewire:is
                            lazy
                            :id="$widget['id']"
                            :component="$widget['component_name'] ?? $widget['class']"
                            wire:model="timeFrame"
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

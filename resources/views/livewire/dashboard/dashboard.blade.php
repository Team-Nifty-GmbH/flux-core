<div
    wire:ignore.self
     x-data="{
        widgets: $wire.entangle('widgets').defer,
        widget: {},
        editWidgets() {
            this.widgetsSortable = new Sortable(document.getElementById('widgets'), {
                swapThreshold: 1,
                animation: 150,
                group: 'widgets',
                delay: 100
            });
            this.availableWidgetsSortable = new Sortable(document.getElementById('available-widgets'), {
                swapThreshold: 1,
                animation: 150,
                group: {
                    name: 'widgets',
                    pull: 'clone',
                    put: false
                }
            });
            this.editMode = true;
        },
        save() {
            this.editMode = false;
            $wire.saveWidgets(this.widgetsSortable.toArray());
        },
        edit(widget) {
            this.widget = this.widgets.find(w => w.id == widget);
            Alpine.$data(this.$refs.modal.querySelector('[wireui-modal]')).open();
        },
        widgetsSortable: {},
        availableWidgetsSortable: {},
        editMode: false
    }">
    <div wire:ignore class="mx-auto px-4 py-6 sm:px-6 lg:px-8 flex justify-between">
        <div class="pb-6 md:flex md:items-center md:justify-between md:space-x-5">
            <div class="flex items-start space-x-5">
                <div class="flex-shrink-0">
                    <x-avatar :src="auth()->user()->getAvatarUrl()" />
                </div>
                <div class="pt-1.5">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ __('Hello') }} {{ Auth::user()->name }}</h1>
                </div>
            </div>
            <div x-ref="modal">
                <x-modal>
                    <x-card class="flex flex-col gap-4">
                        <x-input :label="__('Name')" x-model="widget.name" />
                        <x-inputs.number :label="__('Width')" max="12" min="1" step="1" x-model="widget.width" />
                        <x-inputs.number :label="__('Height')" min="1" step="1" x-model="widget.height" />
                        <x-slot:footer>
                            <div class="flex justify-end w-full">
                                <x-button primary x-on:click="$wire.updateWidget(widget); close()" :label="__('Save')" />
                            </div>
                        </x-slot:footer>
                    </x-card>
                </x-modal>
            </div>
        </div>
        <div class="flex gap-4">
            <div x-show="! editMode">
                <x-button x-on:click="editWidgets()" :label="__('Edit Dashboard')" class="mb-4" />
            </div>
            <div x-cloak x-transition x-show="editMode">
                <x-button spinner primary :label="__('Save')" x-on:click="save()" />
            </div>
        </div>
    </div>
    <div class="flex gap-4 divide-x">
        <div class="flex-initial w-full">
            <div id="widgets" class="grid grid-cols-1 lg:grid-cols-4 xl:grid-cols-8 2xl:grid-cols-12 auto-cols-fr grid-flow-dense gap-4">
                @forelse($widgets as $widget)
                    <div data-id="{{ $widget['id'] }}" x-bind:class="editMode && 'outline-offset-3 bg-primary-100 outline-2 outline-dashed outline-indigo-500'" class="rounded flex place-content-center relative {{ 'col-span-' . $widget['width'] . ' row-span-' . $widget['height'] }}">
                        <div class="absolute top-2 right-2" x-cloak x-show="editMode">
                            <x-button.circle class="shadow-md w-4 h-4 text-gray-400 cursor-pointer" x-on:click="edit($el.parentNode.parentNode.dataset.id)" primary icon="pencil"/>
                            <x-button.circle class="shadow-md w-4 h-4 text-gray-400 cursor-pointer" icon="trash" negative x-on:click="$el.parentNode.parentNode.remove()"/>
                        </div>
                        <livewire:is :component="$widget['component_name']" wire:key="{{ uniqid() }}" />
                    </div>
                @empty
                    <div class="col-span-12 h-96"></div>
                @endforelse
            </div>
        </div>
        <div class="pl-3 flex-none min-h-[85px]" x-cloak x-transition x-show="editMode" wire:ignore>
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">
                {{ __('Available Widgets') }}
            </h3>
            <div id="available-widgets" class="grid grid-cols-1 gap-4">
                @foreach($availableWidgets as $availableWidget)
                    <div class="widget flex-1" data-id="new-{{ $availableWidget['name'] }}">
                        <x-card>
                            <span>
                                {{ $availableWidget['name'] }}
                            </span>
                        </x-card>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

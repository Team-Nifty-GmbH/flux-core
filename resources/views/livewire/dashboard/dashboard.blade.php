<div
    wire:ignore.self
     x-data="{
        widget: {},
        ...dashboard(),
        editWidgets() {
            this.widgetsSortable = new Sortable(document.getElementById('widgets'), {
                swapThreshold: 1,
                animation: 150,
                group: 'widgets',
                delay: 100,
                onAdd: (event) => {
                    const widget = JSON.parse(event.item.dataset.widget);
                    widget.width = 1;
                    widget.height = 1;
                    widget.name = widget.label;
                    widget.id = event.item.dataset.id;
                    $wire.widgets.push(widget);

                    $wire.$refresh();
                }
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
            $wire.saveDashboard(this.widgetsSortable.toArray());
        },
        edit(widget) {
            this.widget = $wire.widgets.find(w => w.id == widget);
            $openModal('edit-widget');
        },
        removeWidget(widget, wireId) {
            const widgetId = widget.parentNode.parentNode.dataset.id;
            const index = $wire.widgets.findIndex(w => w.id == widgetId);
            if (index > -1) {
                $wire.widgets.splice(index, 1);
                widget.parentNode.parentNode.style.display = 'none';
            }
        },
        widgetsSortable: {},
        availableWidgetsSortable: {},
        editMode: false
    }">
    <div wire:ignore class="mx-auto py-6 flex justify-between">
        <div class="pb-6 md:flex md:items-center md:justify-between md:space-x-5">
            <div class="flex items-start space-x-5">
                <div class="flex-shrink-0">
                    <x-avatar :src="auth()->user()->getAvatarUrl()" />
                </div>
                <div class="pt-1.5">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ __('Hello') }} {{ Auth::user()->name }}</h1>
                </div>
            </div>
            <x-modal name="edit-widget">
                <x-card class="flex flex-col gap-4">
                    <x-input :label="__('Name')" x-model="widget.name" />
                    <x-inputs.number :label="__('Width')" max="12" min="1" step="1" x-model.number="widget.width" />
                    <x-inputs.number :label="__('Height')" min="1" step="1" x-model.number="widget.height" />
                    <x-slot:footer>
                        <div class="flex justify-end w-full">
                            <x-button x-on:click="close()" :label="__('Close')" />
                        </div>
                    </x-slot:footer>
                </x-card>
            </x-modal>
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
{{--    <div class="flex gap-4 divide-x">--}}
{{--        <div class="flex-initial w-full">--}}
{{--            <div id="widgets" class="grid grid-cols-1 lg:grid-cols-4 xl:grid-cols-8 2xl:grid-cols-12 auto-cols-fr grid-flow-dense gap-4">--}}
{{--                @forelse($widgets as $widget)--}}
{{--                    <div--}}
{{--                        x-data="{widgetModel: $wire.widgets[{{ $loop->index }}]}"--}}
{{--                        data-id="{{ $widget['id'] ?? 'new-' . uniqid() }}"--}}
{{--                        class="p-1.5 rounded flex place-content-center relative col-span-full"--}}
{{--                        x-bind:class="(editMode ? 'outline-offset-3 bg-primary-100 outline-2 outline-dashed outline-indigo-500 select-none' : '') + ' md:col-span-' + widgetModel.width + ' row-span-' + widgetModel.height"--}}
{{--                    >--}}
{{--                        <div x-cloak x-show="editMode" x-transition class="w-full absolute top-0 bottom-0 bg-primary-100 opacity-25 z-10 handle"></div>--}}
{{--                        <div class="absolute top-2 right-2 z-10" x-cloak x-show="editMode">--}}
{{--                            <x-button.circle class="shadow-md w-4 h-4 text-gray-400 cursor-pointer" x-on:click="edit($el.parentNode.parentNode.dataset.id)" primary icon="pencil"/>--}}
{{--                            <x-button.circle class="shadow-md w-4 h-4 text-gray-400 cursor-pointer" icon="trash" negative x-on:click="removeWidget($el, $wire.id)"/>--}}
{{--                        </div>--}}
{{--                        <div class="z-0 w-full">--}}
{{--                            <livewire:is lazy :component="$widget['component_name'] ?? $widget['class']" wire:key="{{ uniqid() }}" />--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                @empty--}}
{{--                    <div class="col-span-12 h-96"></div>--}}
{{--                @endforelse--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="pl-3 flex-none min-h-[85px]" x-cloak x-transition x-show="editMode" wire:ignore>--}}
{{--            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">--}}
{{--                {{ __('Available Widgets') }}--}}
{{--            </h3>--}}
{{--            <div id="available-widgets" class="grid grid-cols-1 gap-4">--}}
{{--                @foreach($availableWidgets as $availableWidget)--}}
{{--                    <div class="widget flex-1" data-id="new-{{ uniqid() }}" data-widget="{{ json_encode($availableWidget) }}">--}}
{{--                        <x-card>--}}
{{--                            <span>--}}
{{--                                {{ __($availableWidget['label']) }}--}}
{{--                            </span>--}}
{{--                        </x-card>--}}
{{--                    </div>--}}
{{--                @endforeach--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
    <div class="grid-stack">
                        @forelse($widgets as $widget)
            <div class="grid-stack-item">
                            <div
                                gs-w="1"
                                gs-h="1"
                                gs-id="{{ $widget['id'] }}"
                                gs-x="{{ $loop->index }}"
                                gs-y="0"
                                x-data="{widgetModel: $wire.widgets[{{ $loop->index }}]}"
                                data-id="{{ $widget['id'] ?? 'new-' . uniqid() }}"
                                class="grid-stack-item-content p-1.5 rounded flex place-content-center relative col-span-full"
                                x-bind:class="(editMode ? 'outline-offset-3 bg-primary-100 outline-2 outline-dashed outline-indigo-500 select-none' : '') + ' md:col-span-' + widgetModel.width + ' row-span-' + widgetModel.height"
                            >
                                <div x-cloak x-show="editMode" x-transition class="w-full absolute top-0 bottom-0 bg-primary-100 opacity-25 z-10 handle"></div>
                                <div class="grid-stack-item-content absolute top-2 right-2 z-10" x-cloak x-show="editMode">
                                    <x-button.circle class="shadow-md w-4 h-4 text-gray-400 cursor-pointer" x-on:click="edit($el.parentNode.parentNode.dataset.id)" primary icon="pencil"/>
                                    <x-button.circle class="shadow-md w-4 h-4 text-gray-400 cursor-pointer" icon="trash" negative x-on:click="removeWidget($el, $wire.id)"/>
                                </div>
                                <div class="z-0 w-full">
                                    <livewire:is lazy :component="$widget['component_name'] ?? $widget['class']" wire:key="{{ uniqid() }}" />
                                </div>
                            </div>
            </div>
                        @empty
                            <div class="col-span-12 h-96"></div>
                        @endforelse
                    </div>
</div>

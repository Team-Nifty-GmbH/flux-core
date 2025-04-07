<div x-data="{
    dialogType: null,
}">
    @teleport('body')
        <x-modal id="edit-event-modal" scope="headless" persistent>
            <div>
                <livewire:dynamic-component
                    wire:model="event"
                    :calendars="$calendars"
                    :is="$event->edit_component ?? 'features.calendar.calendar-event'"
                />
            </div>
        </x-modal>
    @endteleport

    @teleport('body')
        <x-modal id="confirm-dialog" scope="headless" persistent center>
            <x-card>
                <div
                    x-show="$wire.event.was_repeatable"
                    x-cloak
                    class="space-y-2"
                >
                    <div
                        x-show="! $wire.event.has_repeats || dialogType === 'delete'"
                        x-cloak
                    >
                        <x-radio
                            :label="__('This event')"
                            value="this"
                            wire:model="event.confirm_option"
                        />
                    </div>
                    <div>
                        <x-radio
                            :label="__('This event and following')"
                            value="future"
                            wire:model="event.confirm_option"
                        />
                    </div>
                    <div>
                        <x-radio
                            :label="__('All events')"
                            value="all"
                            wire:model="event.confirm_option"
                        />
                    </div>
                </div>
                <div x-show="!$wire.event.was_repeatable" x-cloak>
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full"
                    >
                        <x-dynamic-component
                            :component="TallStackUi::prefix('icon')"
                            :icon="TallStackUi::icon('x-circle')"
                            class="h-8 w-8"
                            color="red"
                            outline
                            internal
                        />
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3
                            class="dark:text-dark-200 text-lg font-semibold leading-6 text-gray-700"
                            x-html="'{{ __('Delete :model', ['model' => __('Calendar Event')]) }}'"
                        ></h3>
                        <div class="mt-2">
                            <p
                                class="dark:text-dark-300 text-sm text-gray-500"
                                x-html="'{{ __('Do you really want to delete this :model?', ['model' => __('Calendar Event')]) }}'"
                            ></p>
                        </div>
                    </div>
                </div>
                <x-slot:footer>
                    <x-button
                        :text="__('Cancel')"
                        color="secondary"
                        light
                        x-on:click="$modalClose('confirm-dialog')"
                    />
                    <div x-show="dialogType === 'delete'">
                        <x-button
                            :text="__('Delete')"
                            color="red"
                            x-on:click="$dispatch('delete-calendar-event')"
                        />
                    </div>
                    <div x-show="dialogType === 'save'">
                        <x-button
                            :text="__('Save')"
                            color="indigo"
                            x-on:click="$dispatch('save-calendar-event')"
                        />
                    </div>
                </x-slot>
            </x-card>
        </x-modal>
    @endteleport
</div>

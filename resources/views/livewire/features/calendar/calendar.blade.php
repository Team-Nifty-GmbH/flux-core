<div class="overflow-auto lg:flex lg:h-full lg:flex-col" x-data="{calendarEvent: {}}"
{{--     x-on:calendar-event-click="console.log($event.detail.event.id)"--}}
     x-on:calendar-event-click="calendarEvent = $event.detail.event; Alpine.$data(document.getElementById('calendar-event-edit').querySelector('[wireui-modal]')).show = true;"
     x-on:calendar-day-click="$wire.onDayClick($event.detail.dateStr)"
     x-on:edit-calendar="alert('aa')"
>
    <div id="calendar-event-edit">
        <x-modal.card
            x-bind:id="'calendar-event-edit'"
            :title="__('Edit event')"
            x-on:open="$nextTick(()=>{$refs.autofocus.focus()})"
            z-index="z-30"
        >
            <div class="space-y-3">
                <x-dynamic-component :component="$editEventComponent" />
            </div>
            <x-slot name="footer">
                <div class="flex justify-between gap-x-4">
                    <div x-data>
                        <x-button negative :label="__('Delete')"
                                  x-show="! calendarEvent.disabled"
                                  x-on:confirm="{
                                        title: '{{__('Delete event')}}',
                                            description: '{{__('Do you really want to delete this event? This action cannot be undone')}}',
                                            icon: 'error',
                                            accept: {
                                                label: '{{__('Delete')}}',
                                                method: 'delete'
                                            }
                                        }
                        "/>
                    </div>
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button primary :label="__('Save')" wire:click="save" x-show="! calendarEvent.disabled"/>
                    </div>
                </div>
            </x-slot>
        </x-modal.card>
    </div>
    <div>
        <div class="flex flex-col-reverse md:flex-row">
            <div class="flex flex-col gap-4 pr-2">
                <x-card>
                    <div class="flex justify-between pb-1.5 font-semibold dark:text-gray-50">
                        <div>{{ __('Invites') }}</div>
                    </div>
                    <div x-data="{tab: 'new'}">
                        <div class="pb-2.5">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                    <div x-on:click="tab = 'new', $wire.updateInvites()" x-bind:class="{'border-indigo-500 text-indigo-600' : tab === 'new'}" class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-xs text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-50">{{ __('New') }}</div>
                                    <div x-on:click="tab = 'accepted', $wire.updateInvites(['accepted', 'maybe'])" x-bind:class="{'border-indigo-500 text-indigo-600' : tab === 'accepted'}" class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-xs text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-50">{{ __('Accepted') }}</div>
                                    <div x-on:click="tab = 'declined', $wire.updateInvites('declined')" x-bind:class="{'border-indigo-500 text-indigo-600' : tab === 'declined'}" class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-xs text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-50">{{ __('Declined') }}</div>
                                </nav>
                            </div>
                        </div>
                    </div>
{{--                    <div x-show="invites.length">--}}
{{--                        <div class="space-y-3">--}}
{{--                            <template x-for="invite in invites">--}}
{{--                                <div class="rounded-md bg-gray-100 p-2 shadow-md">--}}
{{--                                    <div>--}}
{{--                                        <div x-text="invite.title"></div>--}}
{{--                                        <div x-text="invite.starts_at"></div>--}}
{{--                                    </div>--}}
{{--                                    <div class="pt-1.5">--}}
{{--                                        <x-button x-show="invite.pivot?.status !== 'declined'" x-on:click="$wire.inviteStatus(invite.id, 'declined')" 2xs negative :label="__('Decline')"></x-button>--}}
{{--                                        <x-button x-show="invite.pivot?.status !== 'maybe'" x-on:click="$wire.inviteStatus(invite.id, 'maybe')" 2xs warning :label="__('Maybe')"></x-button>--}}
{{--                                        <x-button x-show="invite.pivot?.status !== 'accepted'" x-on:click="$wire.inviteStatus(invite.id, 'accepted')" 2xs positive :label="__('Accept')"></x-button>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </template>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </x-card>
            </div>
            <div wire:ignore >
                <livewire:calendar-component />
            </div>
        </div>
    </div>
</div>

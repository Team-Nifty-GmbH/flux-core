<div class="overflow-auto lg:flex lg:h-full lg:flex-col" x-data="{calendarEvent: @entangle('calendarEvent')}">
    @pushonce('scripts')
        @vite('resources/js/fullcalendar.js', 'flux/build')
    @endpushonce
    <script type="module">
        function goToPrev() {
            calendar.prev();
            document.getElementById('{{$this->id}}-view-title').innerText = calendar.currentData.viewTitle;
        }

        function goToNext() {
            calendar.next();
            document.getElementById('{{$this->id}}-view-title').innerText = calendar.currentData.viewTitle;
        }

        function goToToday() {
            calendar.today();
            @this.refreshCalendarEvents();
            document.getElementById('{{$this->id}}-view-title').innerText = calendar.currentData.viewTitle;
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderCalendar();
        });

        function renderCalendar() {
            var calendarEl = document.getElementById('{{'calendar-' . $this->id}}');
            window.calendar = new Calendar(calendarEl, {
                plugins: [ dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin ],
                locales: allLocales,
                height: 'auto',
                initialView: '{{$initialView}}',
                editable: @js($editable),
                eventResizableFromStart: true,
                locale: '{{str_replace('_', '-', app()->getLocale())}}',
                initialDate: '{{$initialDate ?? \Illuminate\Support\Carbon::now()}}',
                headerToolbar: false,
                events: function (info, successCallback, failureCallback) {
                    @this.getEvents(info.start, info.end);
                    successCallback(@this.events)
                },
                eventReceive: info => @this.eventReceive(info.event),
                @if(user_can('api.calendar-events.put'))
                eventDrop: info => @this.onEventDropped(info.event.id, info.event),
                eventResize: info => @this.onEventDropped(info.event.id, info.event),
                @endif
                eventClick: info => @this.onEventClick(info.event.id),
                @if(user_can('api.calendar-events.post'))
                dateClick: info => @this.onDayClick(info.dateStr),
                @endif
                viewDidMount: () => document.getElementById('{{$this->id}}-view-title').innerText = calendar.currentData.viewTitle
            });
            window.calendar.render();
            @this.refreshCalendarEvents();

            document.getElementById('{{$this->id}}-view-title').innerText = calendar.currentData.viewTitle;
            @this.on('refreshCalendar', () => {
                calendar.refetchEvents();
            });
        }
    </script>
    <x-modal.card
        blur
        wire:model.defer="eventModal"
        id="{{$this->id}}-edit-event"
        :title="__('New event')"
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
    <div
        x-data="{
            calendars: $wire.entangle('calendars').defer,
            activeCalendars: $wire.entangle('activeCalendars'),
            personalCalendars: $wire.entangle('personalCalendars').defer,
            invites: $wire.entangle('invites')
        }"
    >
        <div class="flex flex-col-reverse md:flex-row">
            <div class="flex flex-col gap-4 pr-2">
                <x-card x-show="calendars.length > 0">
                    <div class="overflow-hidden text-ellipsis whitespace-nowrap">
                        <template x-for="calendar in calendars">
                            <div x-bind:style="calendar.slug ? 'padding-left: ' + ('str1-str2-str3-str4'.match(/-/g) || []).length * 8 + 'px' : '' " class="rounded-md p-1.5" x-bind:class="{'bg-primary-500 text-white': calendar.id === calendarEvent.calendar_id}">
                                <div class="relative flex w-full items-start">
                                    <div class="flex h-5 items-center">
                                        <input
                                            x-bind:id="calendar.id"
                                            x-bind:value="calendar.id"
                                            type="checkbox"
                                            x-bind:style="'background-color: ' + calendar.color"
                                            class="form-checkbox border-secondary-300 text-primary-600 focus:ring-primary-600 focus:border-primary-400 dark:border-secondary-500 dark:checked:border-secondary-600 dark:focus:ring-secondary-600 dark:focus:border-secondary-500 dark:bg-secondary-600 dark:text-secondary-600 dark:focus:ring-offset-secondary-800 rounded transition duration-100 ease-in-out"
                                            x-model="activeCalendars"
                                        >
                                    </div>
                                    <div
                                        class="ml-2 w-full"
                                        @if(auth()->user() instanceof \FluxErp\Models\User)
                                            x-on:click="$wire.set('calendarEvent.calendar_id', calendar.id)"
                                        @endif
                                    >
                                        <div x-text="calendar.name" class="block cursor-pointer text-sm font-medium dark:text-gray-50" >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-card>
                @if(user_can('api.calendars.post') || $personalCalendars)
                <x-card>
                        <livewire:calendar-edit :modal="true" :blue-print="['user_id' => auth()->id(), 'module' => null]" />
                        <div class="flex justify-between pb-1.5 font-semibold dark:text-gray-50">
                            <div>{{ __('My calendars') }}</div>
                            @if(user_can('api.calendars.post'))
                                <x-button.circle 2xs primary label="+" wire:click="editPersonalCalendar" />
                            @endif
                        </div>
                        <template x-for="calendar in personalCalendars">
                            <div x-bind:style="calendar.slug ? 'padding-left: ' + ('str1-str2-str3-str4'.match(/-/g) || []).length * 8 + 'px' : '' " class="rounded-md p-1.5" x-bind:class="{'bg-primary-500 text-white': calendar.id === calendarEvent.calendar_id}">
                                <div class="relative flex w-full items-center">
                                    <div class="flex h-5 items-center">
                                        <input
                                            x-bind:id="calendar.id"
                                            x-bind:value="calendar.id"
                                            type="checkbox"
                                            x-bind:style="'background-color: ' + calendar.color"
                                            class="form-checkbox border-secondary-300 text-primary-600 focus:ring-primary-600 focus:border-primary-400 dark:border-secondary-500 dark:checked:border-secondary-600 dark:focus:ring-secondary-600 dark:focus:border-secondary-500 dark:bg-secondary-600 dark:text-secondary-600 dark:focus:ring-offset-secondary-800 rounded transition duration-100 ease-in-out"
                                            x-model="activeCalendars"
                                        >
                                    </div>
                                    <div class="ml-2 flex w-full items-center justify-between text-sm" x-on:click="$wire.set('calendarEvent.calendar_id', calendar.id)">
                                        <div x-text="calendar.name" class="block cursor-pointer pr-1.5 text-sm font-medium dark:text-gray-50" >
                                        </div>
                                        <x-button.circle x-on:click="$wire.editPersonalCalendar(calendar.id)" secondary xs icon="pencil"></x-button.circle>
                                    </div>
                                </div>
                            </div>
                        </template>
                </x-card>
                @endif
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
                    <div x-show="invites.length">
                        <div class="space-y-3">
                            <template x-for="invite in invites">
                                <div class="rounded-md bg-gray-100 p-2 shadow-md">
                                    <div>
                                        <div x-text="invite.title"></div>
                                        <div x-text="invite.starts_at"></div>
                                    </div>
                                    <div class="pt-1.5">
                                        <x-button x-show="invite.pivot?.status !== 'declined'" x-on:click="$wire.inviteStatus(invite.id, 'declined')" 2xs negative :label="__('Decline')"></x-button>
                                        <x-button x-show="invite.pivot?.status !== 'maybe'" x-on:click="$wire.inviteStatus(invite.id, 'maybe')" 2xs warning :label="__('Maybe')"></x-button>
                                        <x-button x-show="invite.pivot?.status !== 'accepted'" x-on:click="$wire.inviteStatus(invite.id, 'accepted')" 2xs positive :label="__('Accept')"></x-button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-card>
            </div>
            <div wire:ignore >
                <header class="relative flex flex-none items-center justify-between py-4 px-6">
                    <h1 class="text-lg font-semibold text-gray-900">
                        <time id="{{$this->id}}-view-title"></time>
                    </h1>
                    <div class="flex items-center">
                        <div class="flex items-center rounded-md shadow-sm md:items-stretch">
                            <button wire:loading.attr="disabled" onclick="goToPrev()" id="testbutton" type="button" class="flex items-center justify-center rounded-l-md border border-r-0 border-gray-300 bg-white py-2 pl-3 pr-4 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:px-2 md:hover:bg-gray-50">
                                <span class="sr-only">Previous month</span>
                                <!-- Heroicon name: solid/chevron-left -->
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button wire:loading.attr="disabled" onclick="goToToday()" type="button" class="hidden border-t border-b border-gray-300 bg-white px-3.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:relative md:block">{{__('Today')}}</button>
                            <span class="relative -mx-px h-5 w-px bg-gray-300 md:hidden"></span>
                            <button wire:loading.attr="disabled" onclick="goToNext()" type="button" class="flex items-center justify-center rounded-r-md border border-l-0 border-gray-300 bg-white py-2 pl-4 pr-3 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:px-2 md:hover:bg-gray-50">
                                <span class="sr-only">Next month</span>
                                <!-- Heroicon name: solid/chevron-right -->
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="hidden md:ml-4 md:flex md:items-center">
                            <x-dropdown>
                                <x-slot name="trigger">
                                    <x-button :label="__('View')" secondary />
                                </x-slot>
                                <x-dropdown.item :label="__('Month')" onclick="calendar.changeView('dayGridMonth')" />
                                <x-dropdown.item :label="__('Eventlist week')" onclick="calendar.changeView('listWeek')" />
                                <x-dropdown.item :label="__('Week')" onclick="calendar.changeView('timeGridWeek')" />
                                <x-dropdown.item :label="__('Day')" onclick="calendar.changeView('timeGridDay')" />
                                <x-dropdown.item :label="__('DayGridWeek')" onclick="calendar.changeView('dayGridWeek')" />
                            </x-dropdown>
                            @if(user_can('api.calendar-events.post'))
                                <div class="ml-6 h-6 w-px bg-gray-300"></div>
                                <x-button primary wire:click="onDayClick" :label="__('Add event')"/>
                            @endif
                        </div>
                        <div class="ml-4 md:hidden">
                            <x-button.circle primary icon="plus" wire:click="onDayClick"  />
                        </div>
                    </div>
                </header>
                <x-card>
                    <div id="{{'calendar-' . $this->id}}" />
                </x-card>
            </div>
        </div>
    </div>
</div>

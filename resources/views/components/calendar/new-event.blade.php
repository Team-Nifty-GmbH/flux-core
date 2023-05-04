<x-input x-ref="autofocus" :label="__('Title') . '*'" x-model="calendarEvent.title" x-bind:readonly="calendarEvent.disabled ?? false"/>
<x-textarea :label="__('Description')" x-model="calendarEvent.description" x-bind:readonly="calendarEvent.disabled ?? false"/>
<x-checkbox :label="__('all-day')" x-defer="calendarEvent.allDay" x-bind:disabled="calendarEvent.disabled ?? false"/>
<x-label for="starts_at" :label="__('starts:')" />
<input type="datetime-local"
       id="starts_at"
       x-bind:disabled="calendarEvent.disabled ?? false"
       x-bind:max="calendarEvent.end"
       display-format="DD.MM.YYYY HH:mm"
       parse-format="YYYY-MM-DD HH:mm:ss"
       x-model="calendarEvent.start"
       class="placeholder-secondary-400 dark:bg-secondary-800 dark:placeholder-secondary-500 border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block w-full rounded-md border shadow-sm transition duration-100 ease-in-out focus:outline-none dark:text-gray-50 sm:text-sm"
       min="2018-06-07T00:00">
<x-label for="ends_at" :label="__('ends:')" />
<input type="datetime-local"
       id="ends_at"
       x-bind:disabled="calendarEvent.disabled ?? false"
       display-format="DD.MM.YYYY HH:mm"
       parse-format="YYYY-MM-DD HH:mm:ss"
       x-model="calendarEvent.end"
       class="placeholder-secondary-400 dark:bg-secondary-800 dark:placeholder-secondary-500 border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block w-full rounded-md border shadow-sm transition duration-100 ease-in-out focus:outline-none dark:text-gray-50 sm:text-sm"
       x-bind:min="calendarEvent.start"
>
<div x-show="calendarEvent.status">
    <x-select wire:model="calendarEvent.status" :label="__('My status')" :clearable="false">
        <x-select.option value="accepted">
            <div>
                <x-button.circle
                    disabled
                    positive
                    xs
                    icon="check"
                />{{__('Accepted')}}
            </div>
        </x-select.option>
        <x-select.option :label="__('Declined')" value="declined">
            <div>
                <x-button.circle
                    disabled
                    negative
                    xs
                    icon="x"
                />{{__('Declined')}}
            </div>
        </x-select.option>
        <x-select.option :label="__('Maybe')" value="maybe">
            <div>
                <x-button.circle
                    disabled
                    warning
                    xs
                    label="?"
                />{{__('Maybe')}}
            </div>
        </x-select.option>
    </x-select>
</div>

<div x-show="@this.calendarEvent.id">
    <livewire:folder-tree wire:key="{{ uniqid() }}" :model-type="\FluxErp\Models\CalendarEvent::class" :model-id="$this->calendarEvent['id'] ?? null" />
</div>

@if(auth()->user() instanceof \FluxErp\Models\Address)
    <div x-show="! calendarEvent.status">
        <x-button x-on:click="$wire.attendEvent(calendarEvent.id)" positive :label="__('Attend')"></x-button>
    </div>
@endif


@if(auth()->user() instanceof \FluxErp\Models\User)
    <div x-data="{tab: @entangle('tab'), searchResults: @entangle('searchResults'), search: false, searchPhrase: @entangle('search')}">
        <div class="pb-2.5">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <div x-on:click="tab = 'users'" x-bind:class="{'border-indigo-500 text-indigo-600' : tab === 'users'}" class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">{{ __('Users') }}</div>
                    <div x-on:click="tab = 'addresses'" x-bind:class="{'border-indigo-500 text-indigo-600' : tab === 'addresses'}" class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">{{ __('Addresses') }}</div>
                </nav>
            </div>
        </div>
        <div x-show="! calendarEvent.disabled ?? false" x-on:click.outside="search = false">
            <x-input x-on:focus="search = true" icon="search" x-model.debounce.500ms="searchPhrase" />
            <div x-cloak x-show="search" class="absolute z-20 w-full pt-1">
                <x-card>
                    <div x-show="! searchResults.length > 0" x-text="searchPhrase === '' ? '{{ __('Enter your search term…') }}' : '{{ __('No Results…') }}'">
                    </div>
                    <ul>
                        <template x-for="record in searchResults">
                            <li x-on:click="$wire.addInvitedRecord(record.id); search = false">
                                <div class="all-colors text-secondary-600 focus:bg-primary-100 focus:text-primary-800 dark:focus:bg-secondary-700 hover:bg-primary-500 dark:hover:bg-secondary-700 group relative flex cursor-pointer items-center justify-between py-2 px-3 duration-150 ease-in-out hover:text-white focus:outline-none dark:text-gray-50" >
                                    <div x-text="record.name">
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </x-card>
            </div>
        </div>
        <div x-show="tab === 'users'" x-cloak>
            <div class="max-h-72 space-y-1.5 overflow-auto">
                <template x-for="user in calendarEvent.invited_users">
                    <div class="flex justify-between text-sm">
                        <div class="flex items-center">
                            <x-button.circle
                                disabled
                                positive
                                xs
                                icon="check"
                                x-show="user.pivot?.status === 'accepted'"
                            />
                            <x-button.circle
                                disabled
                                negative
                                xs
                                icon="x"
                                x-show="user.pivot?.status === 'declined'"
                            />
                            <x-button.circle
                                disabled
                                warning
                                xs
                                label="?"
                                x-show="user.pivot?.status === 'maybe'"
                            />
                            <x-button.circle
                                disabled
                                secondary
                                xs
                                label="?"
                                x-show="user.pivot?.status !== 'accepted' && user.pivot?.status !== 'declined' && user.pivot?.status !== 'maybe'"
                            />
                            <div class="pl-1.5" x-text="user.name"></div>
                        </div>
                        <x-button.circle x-show="! calendarEvent.disabled ?? false" xs negative icon="x" x-on:click="calendarEvent.invited_users.splice(calendarEvent.invited_users.indexOf(user), 1);" />
                    </div>
                </template>
            </div>
        </div>
        <div x-show="tab === 'addresses'" x-cloak>
            <div class="max-h-72 space-y-1.5 overflow-auto">
                <template x-for="address in calendarEvent.invited_addresses">
                    <div class="flex justify-between text-sm">
                        <div class="flex items-center">
                            <x-button.circle
                                disabled
                                positive
                                xs
                                icon="check"
                                x-show="address.pivot?.status === 'accepted'"
                            />
                            <x-button.circle
                                disabled
                                negative
                                xs
                                icon="x"
                                x-show="address.pivot?.status === 'declined'"
                            />
                            <x-button.circle
                                disabled
                                warning
                                xs
                                label="?"
                                x-show="address.pivot?.status === 'maybe'"
                            />
                            <x-button.circle
                                disabled
                                secondary
                                xs
                                label="?"
                                x-show="address.pivot?.status !== 'accepted' && address.pivot?.status !== 'declined' && address.pivot?.status !== 'maybe'"
                            />
                            <div class="pl-1.5" x-text="address.name"></div>
                        </div>
                        <x-button.circle x-show="! calendarEvent.disabled ?? false" xs negative icon="x" x-on:click="calendarEvent.invited_addresses.splice(calendarEvent.invited_addresses.indexOf(address), 1);" />
                    </div>
                </template>
            </div>
        </div>
        <x-errors />
    </div>
@endif

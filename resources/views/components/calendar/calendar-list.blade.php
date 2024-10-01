<div class="whitespace-nowrap">
    <template x-for="calendarLoopItem in calendars.filter(calendar => calendar.group === '{{ $group }}');">
        <div class="rounded-md p-1 bg-primary-500 text-white dark:text-white"
             x-bind:class="{
                'bg-primary-500 text-white': calendarLoopItem.id === calendarId,
                'pl-7': calendarLoopItem.parentId
             }"
        >
            <div class="flex h-5 items-center justify-end"
                 x-on:mouseover="calendarLoopItem.hover = true"
                 x-on:mouseover.away="calendarLoopItem.hover = false"
            >
                <input
                    x-bind:value="calendarLoopItem.id"
                    x-bind:style="'background-color: ' + calendarLoopItem.color"
                    x-on:change="toggleEventSource(calendarLoopItem)"
                    type="checkbox"
                    checked
                    class="form-checkbox border-secondary-300 text-primary-600 focus:ring-primary-600
                            focus:border-primary-400 dark:border-secondary-500 dark:checked:border-secondary-600
                            dark:focus:ring-secondary-600 dark:focus:border-secondary-500 dark:bg-secondary-600
                            dark:text-secondary-600 dark:focus:ring-offset-secondary-800 rounded transition
                            duration-100 ease-in-out"
                >
                <div class="w-5 h-5 pl-1.5" x-show="calendarLoopItem.isPublic && calendarLoopItem.group !== 'public'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 19.5v-.75a7.5 7.5 0 00-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                </div>
                <div class="px-2 w-full" x-on:click="calendarClick(calendarLoopItem)">
                    <div x-text="calendarLoopItem.name" class="block cursor-default text-sm font-medium dark:text-gray-50"></div>
                </div>
                <template x-if="calendarLoopItem.resourceEditable === true && '{{ resolve_static(\FluxErp\Actions\Calendar\UpdateCalendar::class, 'canPerformAction', [false]) }}'">
                    <div class="cursor-pointer flex items-center">
                        <div class="w-5 h-5" x-on:click="">
                            <svg x-on:click="calendarItem = calendarLoopItem; $wire.editCalendar(calendarLoopItem);" x-cloak x-show="calendarLoopItem.hover" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </div>
                        <div class="w-5 h-5">
                            <svg x-show="calendarLoopItem.isShared || calendarLoopItem.hover" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-5.5-2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 12a5.99 5.99 0 00-4.793 2.39A6.483 6.483 0 0010 16.5a6.483 6.483 0 004.793-2.11A5.99 5.99 0 0010 12z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
    @if($group === 'my' && resolve_static(\FluxErp\Actions\Calendar\CreateCalendar::class, 'canPerformAction', [false]))
        <x-button icon="plus" class="w-full" x-on:click="calendarItem = {}; $wire.editCalendar();">
            {{ __('Create Calendar') }}
        </x-button>
    @endif
</div>

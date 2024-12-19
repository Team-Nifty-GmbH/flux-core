<div class="whitespace-nowrap">
    @if($group === 'my' && resolve_static(\FluxErp\Actions\Calendar\CreateCalendar::class, 'canPerformAction', [false]))
        <x-button icon="plus" class="w-full" x-on:click="calendarItem = {}; $wire.editCalendar();">
            {{ __('Create Calendar') }}
        </x-button>
    @endif
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
                    x-bind:checked="calendar.getEventSourceById(calendarLoopItem.id)"
                    class="form-checkbox border-secondary-300 text-primary-600 focus:ring-primary-600
                            focus:border-primary-400 dark:border-secondary-500 dark:checked:border-secondary-600
                            dark:focus:ring-secondary-600 dark:focus:border-secondary-500 dark:bg-secondary-600
                            dark:text-secondary-600 dark:focus:ring-offset-secondary-800 rounded transition
                            duration-100 ease-in-out"
                >
                <div class="w-5 h-5 pl-1.5" x-show="calendarLoopItem.isPublic && calendarLoopItem.group !== 'public'">
                    <svg
                        class="size-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 19.5v-.75a7.5 7.5 0 00-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                </div>
                <div class="px-2 w-full" x-on:click="calendarClick(calendarLoopItem)">
                    <div x-text="calendarLoopItem.name" class="block cursor-default text-sm font-medium dark:text-gray-50"></div>
                </div>
                <div x-cloak x-show="calendarLoopItem.isLoading">
                    <svg class="mr-2 inline size-8 animate-spin fill-blue-600 p-1.5 text-gray-200 dark:text-gray-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                </div>
                <template x-if="calendarLoopItem.resourceEditable === true && '{{ resolve_static(\FluxErp\Actions\Calendar\UpdateCalendar::class, 'canPerformAction', [false]) }}'">
                    <div class="cursor-pointer flex items-center">
                        <div class="size-5">
                            <svg
                                class="size-5"
                                x-cloak
                                x-show="calendarLoopItem.hover"
                                x-on:click="calendarItem = calendarLoopItem; $wire.editCalendar(calendarLoopItem);"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </div>
                        <div class="size-5">
                            <svg
                                class="size-5"
                                x-cloak
                                x-show="calendarLoopItem.isShared || calendarLoopItem.hover"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-5.5-2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 12a5.99 5.99 0 00-4.793 2.39A6.483 6.483 0 0010 16.5a6.483 6.483 0 004.793-2.11A5.99 5.99 0 0010 12z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

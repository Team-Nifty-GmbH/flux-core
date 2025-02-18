<div class="whitespace-nowrap">
    @if($group === 'my' && resolve_static(\FluxErp\Actions\Calendar\CreateCalendar::class, 'canPerformAction', [false]))
        <x-button icon="plus" class="w-full" x-on:click="calendarItem = {}; $wire.editCalendar();">
            {{ __('Create Calendar') }}
        </x-button>
    @endif
    <div x-data="{
            groupCalendars: () => calendars.filter(calendar => calendar.group === '{{ $group }}'),
            checkedCallback: function(calendarItem) {
                return this.isLeaf(calendarItem) ? calendar?.getEventSourceById(calendarItem.id) !== null : (calendarItem.children || []).every(child => this.isChecked(child))
            },
            storeSettings: () => {
                $wire.$parent.toggleEventSource(calendar.getEventSources().map(source => source.internalEventSource))
            },
        }"
         x-on:folder-tree-uncheck="(event) => {hideEventSource(event.detail); storeSettings();}"
         x-on:folder-tree-check="(event) => {showEventSource(event.detail); storeSettings();}"
         x-on:folder-tree-select="(event) => calendarItem = event.detail"
         x-on:folder-tree-unselect="(event) => calendarItem = null"
         class="w-full pt-2"
    >
        <x-flux::checkbox-tree
            tree="groupCalendars"
            name-attribute="name"
            selectable="true"
            hide-icon="true"
            x-on:calendar-initialized.window="(event) => checked = event.detail.getEventSources().map(source => source.internalEventSource.publicId)"
            checked-callback="checkedCallback"
        >
            <x-slot:checkbox>
                <x-checkbox
                    xs
                    x-on:folder-tree-uncheck.window="$el.checked = isChecked(node); $el.indeterminate = isIndeterminate(node);"
                    x-on:folder-tree-check.window="$el.checked = isChecked(node); $el.indeterminate = isIndeterminate(node);"
                    x-effect="$el.indeterminate = isIndeterminate(node)"
                    x-bind:checked="isChecked(node)"
                    x-on:change="toggleCheck(node, $event.target.checked)"
                    x-bind:value="node.id"
                    x-bind:style="'background-color: ' + node.color"
                    class="form-checkbox"
                />
            </x-slot:checkbox>
            <x-slot:suffix>
                <div x-cloak x-show="node.isLoading">
                    <svg class="mr-2 inline size-6 animate-spin fill-blue-600 p-1.5 text-gray-200 dark:text-gray-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                </div>
                @canAction(\FluxErp\Actions\Calendar\UpdateCalendar::class)
                    <div class="cursor-pointer flex items-center" x-cloak x-show="node.resourceEditable === true">
                        <i x-on:click="calendarItem = node; $wire.editCalendar(node);" class="ph ph-note-pencil size-" x-cloak x-show="node.resourceEditable === true"></i>
                        <i class="ph ph-rss size-6" x-cloak x-show="node.isShared"></i>
                    </div>
                @endCanAction
            </x-slot:suffix>
        </x-flux::checkbox-tree>
    </div>
</div>

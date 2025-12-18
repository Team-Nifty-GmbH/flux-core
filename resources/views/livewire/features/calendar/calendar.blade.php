<div
    wire:ignore
    calendar-root
    x-data="calendar()"
    x-resize.document="changedHeight()"
    x-bind:style="{ height: height + 'px' }"
    class="flex h-full"
>
    <livewire:features.calendar.calendar-event-edit wire:model="event" />
    @section('calendar-modal')
    @teleport('body')
        <x-modal id="calendar-modal" :title="__('Edit Calendar')">
            @section('calendar-edit')
            <div class="flex flex-col gap-4">
                <div id="parent-calendar-select">
                    <x-select.styled
                        wire:model="calendar.parent_id"
                        :label="__('Parent Calendar')"
                        required
                        select="label:label|value:id"
                        :request="[
                            'url' => route('calendar-search'),
                            'method' => 'POST',
                            'params' => [
                                'onlyGroups' => true,
                            ]
                        ]"
                    />
                </div>
                <x-input
                    wire:model="calendar.name"
                    :label="__('Calendar Name')"
                />
                <x-input
                    class="p-0"
                    type="color"
                    :label="__('Color')"
                    wire:model="calendar.color"
                />
                <div x-show="!$wire.calendar.id" x-cloak>
                    <x-checkbox
                        wire:model="calendar.is_group"
                        :label="__('Is Group')"
                    />
                </div>
                <div x-show="!$wire.calendar.is_group" x-cloak>
                    <x-checkbox
                        wire:model="calendar.has_repeatable_events"
                        :label="__('Has repeatable events')"
                    />
                </div>
                @canAction(\FluxErp\Actions\Calendar\CreatePublicCalendar::class)
                    <x-checkbox
                        wire:model="calendar.is_public"
                        :label="__('Public')"
                    />
                @endcanAction

                <div x-show="!$wire.calendar.is_group" x-cloak>
                    <x-card :header="__('Custom Properties')">
                        <div class="flex flex-col gap-4">
                            <x-button.circle
                                class="mr-2"
                                color="indigo"
                                icon="plus"
                                wire:click="addCustomProperty"
                            />
                            <template
                                x-for="(customProperty, index) in $wire.calendar.custom_properties ?? []"
                            >
                                <div class="flex gap-x-4">
                                    <div class="pt-6">
                                        <x-button.circle
                                            color="red"
                                            icon="trash"
                                            wire:click="removeCustomProperty(index)"
                                        />
                                    </div>
                                    <div class="max-w-sm">
                                        <x-select.styled
                                            x-model="customProperty.field_type"
                                            :label="__('Field Type')"
                                            required
                                            :options="$this->fieldTypes"
                                        />
                                    </div>
                                    <div class="w-full">
                                        <x-input
                                            x-model="customProperty.name"
                                            :label="__('Name')"
                                        />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </x-card>
                </div>
            </div>
            @show
            <x-slot:footer>
                <div class="flex w-full justify-between gap-2">
                    <div>
                        <x-button
                            x-show="$wire.calendar.is_editable && '{{ resolve_static(\FluxErp\Actions\Calendar\DeleteCalendar::class, 'canPerformAction', [false]) }}'"
                            flat
                            color="red"
                            :text="__('Delete')"
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Calendar')]) }}"
                            wire:click="deleteCalendar().then((deletedId) => {if(deletedId !== false) deleteCalendar(deletedId);})"
                        />
                    </div>
                    <div class="flex gap-2">
                        <x-button
                            color="secondary"
                            light
                            flat
                            :text="__('Cancel')"
                            x-on:click="$modalClose('calendar-modal');"
                        />
                        <x-button
                            color="indigo"
                            :text="__('Save')"
                            x-on:click="saveCalendar().then((success) => {if(success) $modalClose('calendar-modal');})"
                        />
                    </div>
                </div>
            </x-slot>
        </x-modal>
    @endteleport

    @show
    @if ($showCalendars)
        <div class="whitespace-nowrap" wire:ignore>
            <div
                x-data="{
                    checkedCallback: function (calendarItem) {
                        return this.isLeaf(calendarItem)
                            ? calendar?.getEventSourceById(calendarItem.id) !== null
                            : (calendarItem.children || []).every((child) =>
                                  this.isChecked(child),
                              )
                    },
                    storeSettings: () => {
                        $wire.toggleEventSource(
                            calendar
                                .getEventSources()
                                .map((source) => source.internalEventSource),
                        )
                    },
                }"
                x-on:folder-tree-uncheck="
                    (event) => {
                        hideEventSource(event.detail)
                        storeSettings()
                    }
                "
                x-on:folder-tree-check="
                    (event) => {
                        showEventSource(event.detail)
                        storeSettings()
                    }
                "
                x-on:folder-tree-select="(event) => ($wire.calendarObject = event.detail)"
                x-on:folder-tree-unselect="() => ($wire.calendarObject = null)"
                class="w-full pt-2"
            >
                <x-flux::checkbox-tree
                    tree="calendars"
                    name-attribute="name"
                    parent-id-attribute="parentId"
                    selectable="true"
                    hide-icon="true"
                    checked-callback="checkedCallback"
                    x-on:calendar-initialized.window="(event) => checked = event.detail.getEventSources().map(source => source.internalEventSource.publicId)"
                >
                    @canAction(\FluxErp\Actions\Calendar\CreateCalendar::class)
                        <x-slot:beforeTree>
                            <x-button
                                class="mb-2"
                                :text="__('Create Calendar')"
                                wire:click="editCalendar()"
                            />
                        </x-slot>
                    @endcanAction

                    <x-slot:checkbox>
                        @section('checkbox-slot')
                        <x-checkbox
                            sm
                            x-on:folder-tree-uncheck.window="$el.checked = isChecked(node); $el.indeterminate = isIndeterminate(node);"
                            x-on:folder-tree-check.window="$el.checked = isChecked(node); $el.indeterminate = isIndeterminate(node);"
                            x-effect="$el.indeterminate = isIndeterminate(node)"
                            x-bind:checked="isChecked(node)"
                            x-on:change="toggleCheck(node, $event.target.checked)"
                            x-bind:value="node.id"
                            x-bind:style="'background-color: ' + node.color"
                            class="form-checkbox"
                        />
                        @show
                    </x-slot>
                    <x-slot:suffix>
                        <div class="size-6">
                            <svg
                                x-cloak
                                x-show="node.isLoading"
                                class="mr-2 inline size-6 animate-spin fill-blue-600 p-1.5 text-gray-200 dark:text-gray-600"
                                viewBox="0 0 100 101"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                    fill="currentColor"
                                />
                                <path
                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                    fill="currentFill"
                                />
                            </svg>
                        </div>
                        @canAction(\FluxErp\Actions\Calendar\UpdateCalendar::class)
                            <div
                                class="flex cursor-pointer items-center gap-1"
                                x-cloak
                                x-show="node.resourceEditable === true"
                            >
                                <i
                                    x-on:click="
                                        calendarItem = node
                                        $wire.editCalendar(node.id)
                                    "
                                    class="ph ph-note-pencil"
                                    x-cloak
                                    x-show="node.resourceEditable === true"
                                ></i>
                                <i
                                    class="ph ph-share-fat"
                                    x-cloak
                                    x-show="node.isShared"
                                ></i>
                                <i
                                    class="ph ph-rss"
                                    x-cloak
                                    x-show="node.isPublic"
                                ></i>
                            </div>
                        @endcanAction
                    </x-slot>
                </x-flux::checkbox-tree>
            </div>
        </div>
    @endif

    <div class="h-full w-full">
        <div
            calendar
            class="!h-full border-l dark:border-secondary-600 dark:text-gray-50"
        ></div>
    </div>
</div>

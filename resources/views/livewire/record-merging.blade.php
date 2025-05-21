<div>
    <x-modal
        id="merge-records-modal"
        :title="__('Record merging')"
        size="7xl"
        persistent
        x-on:close="$wire.clear()"
    >
        <div class="pl-4 pr-4">
            <x-flux::table>
                <x-slot:header>
                    <x-flux::table.row>
                        <th></th>
                        <th
                            class="text-center"
                            style="
                                border-width: 1px;
                                border-top: none;
                                border-left: none;
                            "
                        >
                            <span>{{ __('Id') }}</span>
                        </th>
                        <template x-for="column in $wire.columns">
                            <th
                                class="text-center"
                                style="
                                    border-width: 1px;
                                    border-top: none;
                                    border-right: none;
                                "
                            >
                                <span x-text="column.label"></span>
                            </th>
                        </template>
                    </x-flux::table.row>
                </x-slot>
                <x-flux::table.row
                    class="h-16"
                    x-show="$wire.mergeRecords.main_record.id"
                    x-cloak
                >
                    <td style="border-width: 1px; border-left: none">
                        <span class="font-bold">{{ __('Main Record') }}</span>
                    </td>
                    <td style="border-width: 1px">
                        <div class="flex gap-x-2">
                            <div class="flex items-center">
                                <x-checkbox
                                    x-bind:checked="$wire.mergeRecords.main_record.id"
                                    x-bind:disabled="!$wire.mergeRecords.main_record.id"
                                    wire:click="toggleRecord($wire.mergeRecords.main_record.id)"
                                />
                            </div>
                            <span
                                x-text="$wire.mergeRecords.main_record.id"
                            ></span>
                        </div>
                    </td>
                    <template x-for="column in $wire.columns">
                        <td
                            class="text-center"
                            style="border-width: 1px; border-right: none"
                        >
                            <template
                                x-if="$wire.mainRecord[column.name] === true"
                            >
                                <span
                                    class="group inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white outline-none dark:bg-emerald-700"
                                >
                                    <svg
                                        class="h-3 w-3 shrink-0"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        ></path>
                                    </svg>
                                </span>
                            </template>
                            <template
                                x-if="$wire.mainRecord[column.name] === false"
                            >
                                <span
                                    class="group inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white outline-none dark:bg-red-700"
                                >
                                    <svg
                                        class="h-3 w-3 shrink-0"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        ></path>
                                    </svg>
                                </span>
                            </template>
                            <div
                                x-show="typeof $wire.mainRecord[column.name] !== 'boolean'"
                                x-cloak
                            >
                                <span
                                    x-text="$wire.mainRecord[column.name]"
                                ></span>
                            </div>
                        </td>
                    </template>
                </x-flux::table.row>
                <template x-for="record in $wire.records">
                    <x-flux::table.row
                        class="h-16"
                        x-show="record.id !== $wire.mergeRecords.main_record.id"
                        x-cloak
                    >
                        <td></td>
                        <td
                            style="
                                border-width: 1px;
                                border-left: none;
                                border-bottom: none;
                            "
                        >
                            <div class="flex gap-2">
                                <div class="flex items-center">
                                    <x-checkbox
                                        x-bind:value="record.id === $wire.mergeRecords.main_record.id"
                                        wire:click="toggleRecord(record.id)"
                                    />
                                </div>
                                <span x-text="record.id"></span>
                            </div>
                        </td>
                        <template x-for="column in $wire.columns">
                            <td
                                style="
                                    border-width: 1px;
                                    border-right: none;
                                    border-bottom: none;
                                "
                            >
                                <div class="flex justify-center gap-x-2">
                                    <div class="flex items-center">
                                        <x-checkbox
                                            x-bind:value="$wire.mergeRecords.merge_records.find((element) => element.id === record.id)?.columns.includes(column.name)"
                                            wire:click="toggleColumn(record.id, column.name)"
                                        />
                                    </div>
                                    <template
                                        x-if="record[column.name] === true"
                                    >
                                        <span
                                            class="group inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white outline-none dark:bg-emerald-700"
                                        >
                                            <svg
                                                class="h-3 w-3 shrink-0"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 13l4 4L19 7"
                                                ></path>
                                            </svg>
                                        </span>
                                    </template>
                                    <template
                                        x-if="record[column.name] === false"
                                    >
                                        <span
                                            class="group inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white outline-none dark:bg-red-700"
                                        >
                                            <svg
                                                class="h-3 w-3 shrink-0"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                ></path>
                                            </svg>
                                        </span>
                                    </template>
                                    <div
                                        x-show="typeof record[column.name] !== 'boolean'"
                                        x-cloak
                                    >
                                        <span
                                            x-text="record[column.name]"
                                        ></span>
                                    </div>
                                </div>
                            </td>
                        </template>
                    </x-flux::table.row>
                </template>
            </x-flux::table>
        </div>
        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                light
                x-on:click="$modalClose('merge-records-modal')"
            />
            <x-button
                :text="__('Merge')"
                color="indigo"
                wire:click="merge().then((success) => {if(success) $modalClose('merge-records-modal');})"
            />
        </x-slot>
    </x-modal>
</div>

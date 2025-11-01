<div class="py-6">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">
                    {{ __('Ticket Types') }}
                </h1>
                <div class="mt-2 text-sm text-gray-300">
                    {{ __('Here you can manage all ticket types...') }}
                </div>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <x-button
                    color="indigo"
                    :text="__('Create')"
                    wire:click="show()"
                />
            </div>
        </div>
        <div class="mt-8 flex flex-col">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div
                    class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
                >
                    <div
                        class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
                    >
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr class="divide-x divide-gray-200">
                                    <th
                                        scope="col"
                                        class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                    >
                                        {{ __('Name') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900"
                                    >
                                        {{ __('Model') . ' / ' . __('Field type') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900"
                                    >
                                        {{ __('Label') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900"
                                    >
                                        {{ __('Validations') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900"
                                    >
                                        {{ __('Values') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="py-2 pl-2 pr-2 text-left text-sm font-semibold text-gray-900"
                                    ></th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 bg-white"
                                x-data="{ ticketTypes: @entangle('ticketTypes') }"
                            >
                                <template
                                    x-for="(ticketType, index) in ticketTypes"
                                >
                                    <tr class="divide-x divide-gray-200">
                                        <td
                                            x-text="ticketType.field_type ? '&emsp;' + ticketType.name : ticketType.name"
                                            class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6"
                                        />
                                        <td
                                            x-text="ticketType.field_type ? ticketType.field_type : ticketType.model_type"
                                            class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6"
                                        />
                                        <td
                                            x-text="ticketType.label"
                                            class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6"
                                        />
                                        <td
                                            x-text="ticketType.validations"
                                            class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6"
                                        />
                                        <td
                                            x-text="ticketType.values"
                                            class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6"
                                        />
                                        <td
                                            class="whitespace-nowrap py-2 pl-2 pr-2 text-center text-sm text-gray-500"
                                        >
                                            <div
                                                class="flex justify-center gap-1"
                                            >
                                                <div>
                                                    <x-button
                                                        x-on:click="$wire.show(index)"
                                                        color="gray"
                                                        icon="pencil"
                                                    />
                                                </div>
                                                <div
                                                    x-show="!ticketType.field_type"
                                                    x-transition
                                                    x-cloak
                                                >
                                                    <x-button
                                                        x-on:click="$wire.show(index, true)"
                                                        color="indigo"
                                                        icon="plus"
                                                    />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal
        id="edit-ticket-type-modal"
        z-index="z-30"
        wire="showTicketTypeModal"
        :title="__('Ticket Type')"
    >
        <livewire:settings.ticket-type-edit />
        <x-slot:footer>
            <div
                x-data="{ ticketTypeIndex: @entangle('ticketTypeIndex') }"
                class="w-full"
            >
                <div class="flex justify-between gap-x-4">
                    @if (user_can('action.ticket-type.delete'))
                        <x-button
                            color="red"
                            light
                            x-bind:class="ticketTypeIndex > -1 || 'invisible'"
                            flat
                            :text="__('Delete')"
                            wire:click="delete()"
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Ticket Type')]) }}"
                        />
                    @endif

                    <div class="flex gap-x-2">
                        <x-button
                            color="secondary"
                            light
                            flat
                            :text="__('Cancel')"
                            x-on:click="$modalClose('edit-ticket-type-modal')"
                        />
                        <x-button
                            color="indigo"
                            :text="__('Save')"
                            wire:click="$dispatchTo('settings.ticket-type-edit', 'save')"
                        />
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal>

    <x-modal
        id="edit-additional-column-modal"
        z-index="z-30"
        wire="showAdditionalColumnModal"
        :title="$additionalColumnIndex === -1 ? __('Create Additional Column') : __('Edit Additional Column')"
    >
        <livewire:settings.additional-column-edit />
        <x-slot:footer>
            <div
                x-data="{ additionalColumnIndex: @entangle('additionalColumnIndex') }"
                class="w-full"
            >
                <div class="flex justify-between gap-x-4">
                    @if (user_can('action.additional-column.delete'))
                        <x-button
                            color="red"
                            light
                            x-bind:class="additionalColumnIndex > -1 || 'invisible'"
                            flat
                            :text="__('Delete')"
                            wire:click="delete()"
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Additional Column')]) }}"
                        />
                    @endif

                    <div class="flex gap-x-2">
                        <x-button
                            color="secondary"
                            light
                            flat
                            :text="__('Cancel')"
                            x-on:click="$modalClose('edit-additional-column-modal')"
                        />
                        <x-button
                            color="indigo"
                            :text="__('Save')"
                            wire:click="$dispatchTo('settings.additional-column-edit', 'save')"
                        />
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal>
</div>

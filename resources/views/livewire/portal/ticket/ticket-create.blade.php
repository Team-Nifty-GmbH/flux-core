<div>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200">
            <div x-data="{
                selectedAdditionalColumns: @entangle('selectedAdditionalColumns'),
                additionalColumns: @entangle('additionalColumns'),
                ticket: @entangle('ticket'),
                save() {
                    this.$wire.save().then((result) => {
                        if (result === true) {
                            close()
                        }
                    });
                }
            }">
                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                        <x-input :label="__('Title')"
                                 :placeholder="__('What is it about?')"
                                 wire:model="ticket.title"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-textarea :label="__('Description')"
                                    :placeholder="__('Your subject')"
                                    wire:model="ticket.description"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-select
                            :label="__('Ticket Type')"
                            :placeholder="__('Ticket Type')"
                            wire:model.live="ticketTypeId"
                            :options="$ticketTypes"
                            option-label="name"
                            option-value="id"
                        />
                    </div>
                    <template x-for="ticketTypeAdditionalColumn in selectedAdditionalColumns">
                        <div class="sm:col-span-6">
                            <x-label
                                x-html="ticketTypeAdditionalColumn.label ? ticketTypeAdditionalColumn.label : ticketTypeAdditionalColumn.name"
                                x-bind:for="ticketTypeAdditionalColumn.name"
                            />
                            <template x-if="ticketTypeAdditionalColumn.field_type === 'select'">
                                <x-native-select
                                    x-model="ticket[ticketTypeAdditionalColumn.name]"
                                    x-bind:options="ticketTypeAdditionalColumn.values"
                                >
                                    <option value="" disabled selected>
                                        {{ __('Please select') }}
                                    </option>
                                    <template x-for="value in ticketTypeAdditionalColumn.values">
                                        <option x-bind:value="value" x-text="value"></option>
                                    </template>
                                </x-native-select>
                            </template>
                            <template x-if="ticketTypeAdditionalColumn.field_type !== 'select'">
                                <x-input x-bind:type="ticketTypeAdditionalColumn.field_type" x-model="ticket[ticketTypeAdditionalColumn.name]"/>
                            </template>
                        </div>
                    </template>
                    <template x-for="additionalColumn in additionalColumns">
                        <div class="sm:col-span-6">
                            <x-label x-html="additionalColumn.label ? additionalColumn.label : additionalColumn.name" x-bind:for="additionalColumn.name" />
                            <x-input x-bind:type="additionalColumn.field_type" x-model="ticket[additionalColumn.name]"/>
                        </div>
                    </template>
                    <h2 class="text-base font-bold uppercase sm:col-span-6">{{ __('Attachments') }}</h2>
                    <div
                        class="text-portal-font-color sm:col-span-6">{{ __('Photos and videos help us analyze the errors') }}
                    </div>
                    <div class="sm:col-span-6">
                        <x-features.media.upload wire:model.live="attachments"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

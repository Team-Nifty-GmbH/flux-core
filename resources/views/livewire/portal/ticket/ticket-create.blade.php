<div>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200">
            <div x-data="{
                selectedAdditionalColumns: @entangle('selectedAdditionalColumns').defer,
                additionalColumns: @entangle('additionalColumns').defer,
                ticket: @entangle('ticket').defer,
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
                                 wire:model.defer="ticket.title"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-textarea :label="__('Description')"
                                    :placeholder="__('Your subject')"
                                    wire:model.defer="ticket.description"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-select
                            :label="__('Ticket Type')"
                            :placeholder="__('Ticket Type')"
                            wire:model="ticketTypeId"
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
                            <x-input x-bind:type="ticketTypeAdditionalColumn.field_type" x-model="ticket[ticketTypeAdditionalColumn.name]"/>
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
                        <x-features.media.upload wire:model="attachments"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-modal
    x-on:open="$focusOn('ticket-title')"
    id="new-ticket-modal"
    z-index="z-30"
    wire="showTicketModal"
    :title="__('New Ticket')"
>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200">
            <div
                x-data="{
                    ticket: $wire.entangle('ticket'),
                    save() {
                        this.$wire.save().then((result) => {
                            if (result === true) {
                                close()
                            }
                        })
                    },
                }"
            >
                <div class="mt-6 grid grid-cols-1 gap-1.5 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                        <x-input
                            id="ticket-title"
                            :label="__('Title')"
                            :placeholder="__('What is it about?')"
                            wire:model="ticket.title"
                        />
                    </div>
                    <div class="sm:col-span-6">
                        <x-textarea
                            :label="__('Description')"
                            :placeholder="__('Your subject')"
                            wire:model="ticket.description"
                        />
                    </div>
                    <div class="sm:col-span-6">
                        <x-select.styled
                            :label="__('Ticket Type')"
                            :placeholder="__('Ticket Type')"
                            wire:model="ticket.ticket_type_id"
                            select="label:name|value:id"
                            :options="$ticketTypes"
                        />
                    </div>
                    <h2 class="text-base font-bold uppercase sm:col-span-6">
                        {{ __('Attachments') }}
                    </h2>
                    <div class="text-portal-font-color sm:col-span-6">
                        {{ __('Photos and videos help us analyze the errors') }}
                    </div>
                    <div class="sm:col-span-6">
                        <x-flux::features.media.upload
                            wire:model.live="attachments"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('new-ticket-modal')"
        />
        <x-button color="indigo" :text="__('Save')" wire:click="save" />
    </x-slot>
</x-modal>

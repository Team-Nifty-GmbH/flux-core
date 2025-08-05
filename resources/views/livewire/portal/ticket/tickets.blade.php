<div>
    <x-modal
        x-on:open="$focusOn('ticket-title')"
        id="new-ticket-modal"
        z-index="z-30"
        wire="showTicketModal"
        :title="__('New Ticket')"
    >
        <livewire:portal.ticket.ticket-create />
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('new-ticket-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                wire:click="$dispatchTo('portal.ticket.ticket-create', 'save')"
            />
        </x-slot>
    </x-modal>
    <div class="dark:text-white">
        <h2 class="text-base font-bold uppercase">
            {{ __('Welcome') }}
        </h2>
        <h1 class="pb-10 pt-5 text-5xl font-bold">
            {{ __('My Tickets') }}
        </h1>
        <div class="mt-4 justify-end pb-2 pr-2 sm:ml-16 sm:mt-0 sm:flex">
            <x-button
                color="indigo"
                :text="__('New Ticket')"
                wire:click="show"
            />
        </div>
        <livewire:portal.data-tables.ticket-list />
    </div>
</div>

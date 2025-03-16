<div>
    <x-modal id="new-ticket-modal" z-index="z-30" wire="showTicketModal" :title="__('New Ticket')">
        <livewire:portal.ticket.ticket-create/>
        <x-slot:footer>
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('new-ticket-modal')"/>
            <x-button color="indigo" :text="__('Save')" wire:click="$dispatchTo('portal.ticket.ticket-create', 'save')"/>
        </x-slot:footer>
    </x-modal>
    <div class="dark:text-white">
        <h2 class="text-base font-bold uppercase">
            {{ __('Welcome') }}
        </h2>
        <h1 class="pt-5 pb-10 text-5xl font-bold">
            {{ __('My Tickets') }}
        </h1>
        <div class="mt-4 justify-end pr-2 pb-2 sm:mt-0 sm:ml-16 sm:flex">
            <x-button color="indigo" :text="__('New Ticket')" wire:click="show" />
        </div>
        <livewire:portal.data-tables.ticket-list />
    </div>
</div>

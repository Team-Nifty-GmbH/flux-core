<div>
    <x-modal-card z-index="z-30" wire:model="showTicketModal" :title="__('New Ticket')">
        <livewire:portal.ticket.ticket-create/>
        <x-slot name="footer">
            <div class="w-full">
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="$dispatchTo('portal.ticket.ticket-create', 'save')"/>
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal-card>
    <div class="dark:text-white">
        <h2 class="text-base font-bold uppercase">
            {{ __('Welcome') }}
        </h2>
        <h1 class="pt-5 pb-10 text-5xl font-bold">
            {{ __('My Tickets') }}
        </h1>
        <div class="mt-4 justify-end pr-2 pb-2 sm:mt-0 sm:ml-16 sm:flex">
            <x-button primary :label="__('New Ticket')" wire:click="show" />
        </div>
        <livewire:portal.data-tables.ticket-list />
    </div>
</div>

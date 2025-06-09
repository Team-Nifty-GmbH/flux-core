<div>
    <div class="pt-5 pr-6 pl-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="pt-5 text-3xl font-bold dark:text-gray-50">
                    {{ __('Ticket') . ': ' . $ticket['ticket_number'] }}
                </h1>
                @if ($ticket['state'] === \FluxErp\States\Ticket\Escalated::$name)
                    <x-badge :text="__('Escalated')" color="red" />
                @endif
            </div>
            <div class="flex gap-2">
                @if ($ticket['state'] !== \FluxErp\States\Ticket\Closed::$name)
                    <x-button
                        :text="__('Close')"
                        color="emerald"
                        wire:click="closeTicket"
                    />
                @endif

                @if ($ticket['state'] !== \FluxErp\States\Ticket\Escalated::$name)
                    <x-button
                        wire:flux-confirm.type.error="{{ __('Escalate ticket|Do you really want to escalate this ticket?|Cancel|Escalate') }}"
                        :text="__('Escalate')"
                        color="red"
                        wire:click="escalateTicket"
                    />
                @endif
            </div>
        </div>
        <div>
            @foreach (data_get($ticket, 'users', []) as $user)
                <x-badge :text="data_get($user, 'name')" />
            @endforeach
        </div>
        <h2 class="pt-10 pb-8 text-base font-bold uppercase dark:text-gray-50">
            {{ __('Information') }}
        </h2>
        <div
            class="md:flex md:space-x-12"
            x-data="{ additionalColumns: @entangle('additionalColumns'), ticket: @entangle('ticket') }"
        >
            <div class="flex-1">
                <div class="space-y-5 dark:text-gray-50">
                    <x-input wire:model="ticket.title" :disabled="true" />
                    <x-textarea
                        wire:model="ticket.description"
                        :disabled="true"
                    />
                    @if (

                        $ticket['model_type'] &&
                        ($widgetComponent = resolve_static(
                            morphed_model($ticket['model_type']),
                            'getLivewireComponentWidget'
                        ))                    )
                        <x-card>
                            <livewire:is
                                :component="$widgetComponent"
                                :modelId="$ticket['model_id']"
                            />
                        </x-card>
                    @endif

                    @section('additional-columns')
                    <template x-for="additionalColumn in additionalColumns">
                        <div>
                            <x-label>
                                <span
                                    x-html="additionalColumn.label ? additionalColumn.label : additionalColumn.name"
                                    x-bind:for="additionalColumn.name"
                                />
                            </x-label>
                            <x-input
                                x-bind:type="additionalColumn.field_type"
                                x-model="ticket[additionalColumn.name]"
                                :disabled="true"
                            />
                        </div>
                    </template>
                    @show
                    <h2
                        class="pt-10 pb-8 text-base font-bold uppercase dark:text-gray-50"
                    >
                        {{ __('Attachments') }}
                    </h2>
                    <livewire:portal.ticket.media
                        :model-id="data_get($ticket, 'id')"
                    />
                </div>
            </div>
        </div>
        <x-flux::tabs wire:model.live="tab" :$tabs wire:ignore />
    </div>
</div>

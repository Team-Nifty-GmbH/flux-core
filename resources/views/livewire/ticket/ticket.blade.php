<div class="min-h-full"
     x-data="{
        formatter: @js(\FluxErp\Models\Ticket::typeScriptAttributes()),
        additionalColumns: $wire.entangle('additionalColumns').defer,
        ticket: $wire.entangle('ticket')
    }"
>
    <div class="flex items-center space-x-5">
        <x-avatar xl x-bind:src="ticket.authenticatable.avatar_url" src="#"></x-avatar>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                <div class="opacity-40 transition-opacity hover:opacity-100">
                    <span x-text="ticket.ticket_type?.name">
                    </span>
                    <span x-text="ticket.ticket_number">
                    </span>
                    <span x-text="ticket.authenticatable.name"></span>
                </div>
                <span x-text="ticket.authenticatable.name"></span>
            </h1>
        </div>
    </div>
    <div class="w-full pt-6 lg:col-start-1 xl:col-span-2 xl:flex xl:space-x-6">
        <section class="relative basis-10/12">
            <div class="pr-6 md:flex md:space-x-12">
                <div class="flex-1">
                    <div class="space-y-5 dark:text-gray-50">
                        <x-card class="space-y-4">
                            <x-input :label="__('Title')" wire:model="ticket.title" :disabled="true"/>
                            <x-textarea :label="__('Description')" wire:model="ticket.description" :disabled="true"/>
                        </x-card>
                        @if($ticket['model_type'] && $ticket['model_type']::getLivewireComponentWidget())
                            <x-card>
                                <livewire:is :component="$ticket['model_type']::getLivewireComponentWidget()" :modelId="$ticket['model_id']" />
                            </x-card>
                        @endif
                        <x-card>
                            <x-slot:header>
                                <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                    <x-label>
                                        {{ __('Attachments') }}
                                    </x-label>
                                </div>
                            </x-slot:header>
                            <livewire:folder-tree :model-type="\FluxErp\Models\Ticket::class" :model-id="$ticket['id']" />
                        </x-card>
                        <x-card class="!px-0 !py-0">
                            <livewire:features.comments.comments :is-public="true" :model-type="\FluxErp\Models\Ticket::class" :model-id="$ticket['id']" />
                        </x-card>
                    </div>
                </div>
            </div>
        </section>
        <section class="basis-2/12">
            <div class="sticky top-6 space-y-6">
                <x-card>
                    <x-slot:header>
                        <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                            <x-label>
                                {{ __('Details') }}
                            </x-label>
                        </div>
                    </x-slot:header>
                    <div class="space-y-4">
                        <x-state wire:model="ticketState" formatters="formatter.state" avialable="availableStates"/>
                        <livewire:features.custom-events :model="\FluxErp\Models\Ticket::class" :id="$ticket['id']" />
                        <x-select
                            :disabled="! user_can('api.tickets.update')"
                            :label="__('Ticket Type')"
                            wire:model="ticket.ticket_type_id"
                            option-value="id"
                            option-label="name"
                            :options="$ticketTypes"
                        />
                        <x-select
                            :disabled="! user_can('api.tickets.update')"
                            multiselect
                            :label="__('Assigned')"
                            wire:model="ticket.users"
                            option-value="id"
                            option-label="label"
                            :template="[
                                'name'   => 'user-option',
                            ]"
                            :async-data="[
                                'api' => route('search', \FluxErp\Models\User::class),
                                'method' => 'POST',
                                'params' => [
                                    'with' => 'media',
                                ]
                            ]"
                        />
                        <div>
                            <div class="mb-1 flex items-center justify-between">
                                <x-label>
                                    {{ __('Author') }}
                                </x-label>
                                <div class="pl-2">
                                    <x-button href="#" xs outline icon="eye" x-bind:href="'{{ route('contacts.id?', ':id') }}'.replace(':id', ticket.authenticatable.contact_id)">
                                    </x-button>
                                </div>
                            </div>
                            <x-select
                                :disabled="! user_can('api.tickets.update')"
                                x-on:selected="$wire.changeAuthor($event.detail.value)"
                                class="pb-4"
                                wire:model.defer="ticket.authenticatable_id"
                                option-value="id"
                                option-label="label"
                                option-description="description"
                                :clearable="false"
                                :template="[
                                    'name'   => 'user-option',
                                ]"
                                :async-data="[
                                    'api' => route('search', $ticket['authenticatable_type'] ?: \FluxErp\Models\Address::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'with' => 'contact.media',
                                    ]
                                ]"
                            />
                        </div>
                    </div>
                </x-card>
                <x-card>
                    <x-slot:header>
                        <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                            <x-label>
                                {{ __('Additional columns') }}
                            </x-label>
                        </div>
                    </x-slot:header>
                    <template x-for="additionalColumn in additionalColumns">
                        <div>
                            <x-label
                                x-html="additionalColumn.label ? additionalColumn.label : additionalColumn.name"
                                x-bind:for="additionalColumn.name"
                            />
                            <x-input x-bind:type="additionalColumn.field_type" x-model="ticket[additionalColumn.name]" :disabled="true"/>
                        </div>
                    </template>
                </x-card>
            </div>
        </section>
    </div>
</div>

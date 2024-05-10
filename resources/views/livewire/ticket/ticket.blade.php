<div class="min-h-full"
     x-data="{
        formatter: @js(resolve_static(\FluxErp\Models\Ticket::class, 'typeScriptAttributes')),
        additionalColumns: $wire.entangle('additionalColumns'),
        ticket: $wire.entangle('ticket')
    }"
>
    @section('header')
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
    @show
    <div class="justify-end mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
        @section('buttons')
            @if(resolve_static(\FluxErp\Actions\Ticket\DeleteTicket::class, 'canPerformAction', [false]) && $ticket['id'])
                <x-button negative label="{{ __('Delete') }}" x-on:click="
                            window.$wireui.confirmDialog({
                                title: '{{ __('Delete ticket') }}',
                                description: '{{ __('Do you really want to delete this ticket?') }}',
                                icon: 'error',
                                accept: {
                                    label: '{{ __('Delete') }}',
                                    method: 'delete',
                                },
                                reject: {
                                    label: '{{ __('Cancel') }}',
                                }
                            }, $wire.__instance.id)
                        "/>
            @endif
            <x-button primary :label="__('Save')" wire:click="save"/>
        @show
    </div>
    <div class="w-full pt-6 lg:col-start-1 xl:col-span-2 xl:flex xl:space-x-6">
        <section class="relative basis-10/12">
            @section('content')
                <div class="pr-6 md:flex md:space-x-12">
                    <div class="flex-1">
                        <div class="space-y-5 dark:text-gray-50">
                            <x-card class="space-y-4">
                                <x-input :label="__('Title')" wire:model="ticket.title" :disabled="true"/>
                                <x-textarea :label="__('Description')" wire:model="ticket.description" :disabled="true"/>
                            </x-card>
                            <div x-cloak x-show="additionalColumns?.length > 0">
                                <x-card>
                                    <x-slot:header>
                                        <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                            <x-label>
                                                {{ __('Additional columns') }}
                                            </x-label>
                                        </div>
                                    </x-slot:header>
                                    @section('content.additional-columns')
                                        <div class="flex flex-col gap-4">
                                            <template x-for="additionalColumn in additionalColumns">
                                                <div>
                                                    <x-label
                                                        x-html="additionalColumn.label ? additionalColumn.label : additionalColumn.name"
                                                        x-bind:for="additionalColumn.name"
                                                    />
                                                    <x-input x-bind:class="(additionalColumn.field_type === 'color' || additionalColumn.field_type === 'checkbox') && '!w-auto'" x-bind:type="additionalColumn.field_type" x-model="ticket[additionalColumn.name]" :disabled="true"/>
                                                </div>
                                            </template>
                                        </div>
                                    @show
                                </x-card>
                            </div>
                            @section('content.widget')
                                @if($ticket['model_type']
                                    && $widgetComponent = resolve_static(\Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($ticket['model_type']), 'getLivewireComponentWidget')
                                )
                                    <x-card>
                                        <livewire:is :component="$widgetComponent" :modelId="$ticket['model_id']" />
                                    </x-card>
                                @endif
                            @show
                            <x-card>
                                <x-slot:header>
                                    <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                        <x-label>
                                            {{ __('Attachments') }}
                                        </x-label>
                                    </div>
                                </x-slot:header>
                                @section('content.attachments')
                                    <livewire:folder-tree :model-type="\FluxErp\Models\Ticket::class" :model-id="$ticket['id']" />
                                @show
                            </x-card>
                            <x-card>
                                <x-tabs
                                    wire:model.live="tab"
                                    :$tabs
                                >
                                    <livewire:is
                                        wire:key="{{ uniqid() }}"
                                        :component="$tab"
                                        :model-id="$ticket['id']"
                                    />
                                </x-tabs>
                            </x-card>
                        </div>
                    </div>
                </div>
            @show
        </section>
        <section class="basis-2/12">
            <div class="sticky top-6 space-y-6">
                @section('details')
                    <x-card>
                        <x-slot:header>
                            <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                <x-label>
                                    {{ __('Details') }}
                                </x-label>
                            </div>
                        </x-slot:header>
                        <div class="space-y-4">
                            <x-state wire:model="ticket.state" formatters="formatter.state" available="availableStates"/>
                            <x-select
                                :disabled="! resolve_static(\FluxErp\Actions\Ticket\UpdateTicket::class, 'canPerformAction', [false])"
                                x-on:selected="$wire.updateAdditionalColumns($event.detail.value)"
                                :label="__('Ticket Type')"
                                wire:model.live="ticket.ticket_type_id"
                                option-value="id"
                                option-label="name"
                                :options="$ticketTypes"
                            />
                            <x-select
                                :disabled="$ticket['id'] && ! resolve_static(\FluxErp\Actions\Ticket\UpdateTicket::class, 'canPerformAction', [false])"
                                multiselect
                                :label="__('Assigned')"
                                wire:model.live="ticket.users"
                                option-value="id"
                                option-label="label"
                                :template="[
                                    'name' => 'user-option',
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
                                    <x-toggle :left-label=" __('User') " :label=" __('Contact') " wire:model.live="authorTypeContact" />
                                    <div class="pl-2">
                                        <x-button href="#" xs outline icon="eye"
                                                  x-bind:class="($wire.get('authorTypeContact') !== true || ! ticket.authenticatable_id) && 'cursor-not-allowed'"
                                                  x-bind:href="($wire.get('authorTypeContact') === true && ticket.authenticatable.contact_id) && '{{ route('contacts.id?', ':id') }}'.replace(':id', ticket.authenticatable.contact_id) + '?address=' + ticket.authenticatable_id" >
                                        </x-button>
                                    </div>
                                </div>
                                <x-select
                                    :disabled="! resolve_static(\FluxErp\Actions\Ticket\UpdateTicket::class, 'canPerformAction', [false])"
                                    class="pb-4"
                                    wire:model="ticket.authenticatable_id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    :template="[
                                        'name'   => 'user-option',
                                    ]"
                                    :async-data="[
                                        'api' => route('search', $ticket['authenticatable_type'] ?
                                            \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($ticket['authenticatable_type']) :
                                            \FluxErp\Models\Address::class
                                        ),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => $ticket['authenticatable_type'] === app(\FluxErp\Models\Address::class)->getMorphClass() ? 'contact.media' : 'media',
                                        ]
                                    ]"
                                />
                            </div>
                        </div>
                    </x-card>
                @show
            </div>
        </section>
    </div>
</div>

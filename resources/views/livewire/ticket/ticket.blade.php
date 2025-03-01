<div class="min-h-full"
     x-data="{
        formatter: @js(resolve_static(\FluxErp\Models\Ticket::class, 'typeScriptAttributes')),
    }"
>
    @section('header')
        <div class="flex items-center space-x-5">
            <div x-init="$nextTick(() => { $el.querySelector('img').src = $wire.ticket.authenticatable.avatar_url })">
                <x-avatar image="{{ route('icons', ['name' => 'user']) }}" xl />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="opacity-40 transition-opacity hover:opacity-100">
                        <span x-text="$wire.ticket.ticket_type?.name">
                        </span>
                        <span x-text="$wire.ticket.ticket_number">
                        </span>
                        <span x-text="$wire.ticket.authenticatable.name"></span>
                    </div>
                    <span x-text="$wire.ticket.authenticatable.name"></span>
                </h1>
            </div>
        </div>
    @show
    <div class="justify-end mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
        @section('buttons')
            @if(resolve_static(\FluxErp\Actions\Ticket\DeleteTicket::class, 'canPerformAction', [false]) && $ticket->id)
                <x-button
                    color="red"
                    :text="__('Delete') "
                    wire:click="delete()"
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Ticket')]) }}"
                />
            @endif
            <x-button color="indigo" :text="__('Save')" wire:click="save"/>
        @show
    </div>
    <div class="w-full pt-6 lg:col-start-1 xl:col-span-2 xl:flex xl:space-x-6">
        <section class="relative basis-10/12">
            @section('content')
                <div class="pr-6 md:flex md:space-x-12">
                    <div class="flex-1">
                        <div class="space-y-5 dark:text-gray-50">
                            <x-card class="space-y-4">
                                <x-input :text="__('Title')" wire:model="ticket.title" />
                                <x-flux::editor
                                    :label="__('Description')"
                                    wire:model="ticket.description"
                                />
                            </x-card>
                            <div x-cloak x-show="Object.keys($wire.ticket.availableAdditionalColumns)?.length > 0">
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
                                            <template x-for="(additionalColumn, name) in $wire.ticket.availableAdditionalColumns">
                                                <div>
                                                    <x-checkbox
                                                        x-cloak
                                                        x-show="additionalColumn.field_type === 'checkbox'"
                                                        x-model="$wire.ticket.additional_columns[name].value"
                                                    >
                                                        <x-slot:label>
                                                            <span x-text="additionalColumn.label ? additionalColumn.label : additionalColumn.name"></span>
                                                        </x-slot:label>
                                                    </x-checkbox>
                                                    <x-input
                                                        x-cloak
                                                        x-show="additionalColumn.field_type !== 'checkbox' && additionalColumn.field_type !== 'select'"
                                                        x-model="$wire.ticket.additional_columns[name].value"
                                                        x-bind:class="(additionalColumn.field_type === 'color') && '!w-auto'"
                                                        x-bind:type="additionalColumn.field_type"
                                                    >
                                                    </x-input>
                                                    <div x-cloak x-show="additionalColumn.field_type === 'select'">
                                                        <x-select.native
                                                            x-model="$wire.ticket.additional_columns[name].value"
                                                            x-bind:type="additionalColumn.field_type"
                                                        >
                                                            <option selected>{{ __('Please select') }}</option>
                                                            <template x-for="value in additionalColumn.values">
                                                                <option x-bind:value="value" x-text="value"></option>
                                                            </template>
                                                        </x-select.native>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    @show
                                </x-card>
                            </div>
                            @section('content.widget')
                                @if($ticket->model_type
                                    && $widgetComponent = resolve_static(morphed_model($ticket->model_type), 'getLivewireComponentWidget')
                                )
                                    <x-card>
                                        <livewire:is :component="$widgetComponent" :modelId="$ticket->model_id" />
                                    </x-card>
                                @endif
                            @show
                            <x-card>
                                <x-slot:header>
                                    <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                        <x-label :label="__('Attachments')" />
                                    </div>
                                </x-slot:header>
                                @section('content.attachments')
                                    <livewire:folder-tree :model-type="\FluxErp\Models\Ticket::class" :model-id="$ticket->id" />
                                @show
                            </x-card>
                            <x-card>
                                <x-flux::tabs
                                    wire:model.live="tab"
                                    :$tabs
                                >
                                    <livewire:is
                                        wire:key="{{ uniqid() }}"
                                        :component="$tab"
                                        :model-id="$ticket->id"
                                    />
                                </x-flux::tabs>
                            </x-card>
                        </div>
                    </div>
                </div>
            @show
        </section>
        <section class="basis-2/12">
            <div class="sticky top-6 space-y-6">
                @section('details')
                    <x-card :header="__('Details')">
                        <div class="space-y-4">
                            <x-flux::state wire:model="ticket.state" formatters="formatter.state" available="availableStates"/>
                            <x-select.styled
                                :disabled="! resolve_static(\FluxErp\Actions\Ticket\UpdateTicket::class, 'canPerformAction', [false])"
                                x-on:select="$wire.updateAdditionalColumns($event.detail.select.value)"
                                :label="__('Ticket Type')"
                                wire:model.live="ticket.ticket_type_id"
                                select="label:name|value:id"
                                :options="$ticketTypes"
                            />
                            <x-select.styled
                                :disabled="$ticket->id && ! resolve_static(\FluxErp\Actions\Ticket\UpdateTicket::class, 'canPerformAction', [false])"
                                multiple
                                :label="__('Assigned')"
                                wire:model.live="ticket.users"
                                :request="[
                                    'url' => route('search', \FluxErp\Models\User::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'with' => 'media',
                                    ],
                                ]"
                            />
                            <div>
                                <div class="mb-1 flex items-center justify-between">
                                    <x-label>
                                        {{ __('Author') }}
                                    </x-label>
                                    <x-toggle :left-label=" __('User') " :label=" __('Contact') " wire:model.live="authorTypeContact" />
                                    <div class="pl-2">
                                        <x-button color="secondary" light
                                            href="#"
                                            xs
                                            outline icon="eye"
                                            x-bind:class="($wire.get('authorTypeContact') !== true || ! $wire.ticket.authenticatable_id) && 'cursor-not-allowed'"
                                            x-bind:href="($wire.get('authorTypeContact') === true && $wire.ticket.authenticatable.contact_id) && '{{ route('contacts.id?', ':id') }}'.replace(':id', $wire.ticket.authenticatable.contact_id) + '?address=' + $wire.ticket.authenticatable_id" >
                                        </x-button>
                                    </div>
                                </div>
                                <div id="author-select">
                                    <x-select.styled
                                        :disabled="! resolve_static(\FluxErp\Actions\Ticket\UpdateTicket::class, 'canPerformAction', [false])"
                                        class="pb-4"
                                        wire:model="ticket.authenticatable_id"
                                        option-description="description"
                                        required
                                        :request="[
                                            'url' => route('search', $ticket->authenticatable_type ?? morph_alias(\FluxErp\Models\User::class)),
                                            'method' => 'POST',
                                            'params' => [
                                                'with' => $ticket->authenticatable_type === morph_alias(\FluxErp\Models\Address::class) ? 'contact.media' : 'media',
                                            ],
                                        ]"
                                    />
                                </div>
                            </div>
                        </div>
                    </x-card>
                @show
                @section('attributes')
                    <x-card>
                        <div class="text-sm whitespace-nowrap overflow-hidden text-ellipsis">
                            <div class="flex gap-0.5">
                                <div class="">{{ __('Created At') }}:</div>
                                <div x-text="window.formatters.datetime($wire.ticket.created_at)"></div>
                                <div x-text="$wire.ticket.created_by || '{{ __('Unknown') }}'"></div>
                            </div>
                            <div class="flex gap-0.5">
                                <div class="">{{ __('Updated At') }}:</div>
                                <div x-text="window.formatters.datetime($wire.ticket.updated_at)"></div>
                                <div x-text="$wire.ticket.updated_by || '{{ __('Unknown') }}'"></div>
                            </div>
                        </div>
                    </x-card>
                @show
            </div>
        </section>
    </div>
</div>

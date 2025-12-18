@extends('flux::livewire.contact.contact-list')

@section('modals')
@parent
{{ $this->renderCreateDocumentsModal() }}
@canAction(\FluxErp\Actions\Lead\CreateLead::class)
    <x-modal
        :id="$leadForm->modalName()"
        x-on:open="$focusOn('lead-name')"
        persistent
    >
        <div class="flex flex-col gap-4">
            <x-input
                id="lead-name"
                :label="__('Name')"
                wire:model="leadForm.name"
            />
            <x-textarea
                :label="__('Description')"
                wire:model="leadForm.description"
            />
            <x-rating
                wire:model.number="leadForm.score"
                :text="__('Score')"
                :quantity="5"
                position="right"
            />
            @if (is_null(resolve_static(\FluxErp\Models\LeadState::class, 'default')?->probability_percentage))
                <x-range
                    wire:model.number="leadForm.probability_percentage"
                    :hint="__('Probability to win this lead…')"
                >
                    <x-slot:label>
                        <span
                            x-cloak
                            x-show="$wire.leadForm.probability_percentage !== null"
                            x-text="$wire.leadForm.probability_percentage + '%'"
                        ></span>
                    </x-slot>
                </x-range>
            @endif

            <hr />
            <div class="flex flex-col gap-2">
                <x-toggle
                    :label="__('Assign to Agent')"
                    wire:model="assignToAgent"
                />
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Assign leads to the agent stored in the contact. If no agent is set, leads will be assigned to you.') }}
                </div>
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                :text="__('Cancel')"
                x-on:click="$modalClose('{{ $leadForm->modalName() }}')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                wire:click="createLeads().then((success) => {if(success) $modalClose('{{ $leadForm->modalName() }}');})"
            />
        </x-slot>
    </x-modal>
@endcanAction

@section('map')
    <div>
        <div
            x-on:load-map.window="$nextTick(() => onChange())"
            class="z-0 py-4"
            x-data="addressMap($wire, 'loadMap', false, '{{ auth()->user() ?->getAvatarUrl() }}')"
            x-cloak
            x-show="$wire.showMap"
            x-collapse
        >
            <x-card class="w-full">
                <x-slot:header>
                    <x-button.circle
                        color="secondary"
                        light
                        wire:click="$set('showMap', false, true)"
                        icon="x-mark"
                    />
                </x-slot>
                <div x-intersect.once="onChange()">
                    <div id="map" class="h-96 min-w-96"></div>
                </div>
            </x-card>
        </div>
    </div>
    @show
@endsection

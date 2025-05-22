<div>
    <x-modal
        persistent
        id="{{ $leadForm->modalName() }}"
        x-on:open="$focusOn('lead-name')"
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
            <x-select.styled
                :label="__('Commission Agent')"
                wire:model="leadForm.user_id"
                required
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'media',
                    ],
                ]"
            />
            <x-select.styled
                :label="__('Address')"
                wire:model="leadForm.address_id"
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Address::class),
                    'method' => 'POST',
                    'params' => [
                        'fields' => [
                          'contact_id',
                          'name',
                        ],
                        'with' => 'contact.media',
                    ],
                ]"
            />
            <x-select.styled
                x-bind:readonly="!edit"
                :label="__('Recommended by')"
                wire:model="leadForm.recommended_by_address_id"
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Address::class),
                    'method' => 'POST',
                    'params' => [
                        'fields' => [
                          'contact_id',
                          'name',
                        ],
                        'with' => 'contact.media',
                    ],
                ]"
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
                wire:click="save().then((success) => {if(success) $modalClose('{{ $leadForm->modalName() }}');})"
            />
        </x-slot>
    </x-modal>
</div>

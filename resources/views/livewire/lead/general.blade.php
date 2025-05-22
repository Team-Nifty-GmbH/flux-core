<div
    class="flex flex-col gap-4"
    x-data="{
        formatter: @js(resolve_static(\FluxErp\Models\Lead::class, 'typeScriptAttributes')),
    }"
>
    <x-card>
        <div class="flex flex-col gap-4">
            <x-input
                x-bind:readonly="!edit"
                :label="__('Name')"
                wire:model="leadForm.name"
            />
            <x-textarea
                x-bind:readonly="!edit"
                :label="__('Description')"
                wire:model="leadForm.description"
            />
            <div x-bind:class="! edit && 'pointer-events-none'">
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
            </div>
            <div x-bind:class="! edit && 'pointer-events-none'">
                <x-select.styled
                    x-bind:readonly="!edit"
                    :label="__('Address')"
                    wire:model="leadForm.address_id"
                    select="label:label|value:id"
                    unfiltered
                    required
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
            </div>
            <div x-bind:class="! edit && 'pointer-events-none'">
                <x-select.styled
                    x-on:select="$wire.isLoss = $event.detail.select.is_loss"
                    wire:model="leadForm.lead_state_id"
                    select="label:name|value:id"
                    :label="__('Lead State')"
                    x-bind:readonly="!edit"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\LeadState::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => [
                                'name',
                            ],
                        ],
                    ]"
                />
            </div>
            <div x-cloak x-show="$wire.isLoss">
                <x-textarea
                    x-bind:readonly="!edit"
                    wire:model="leadForm.loss_reason"
                    :label="__('Loss Reason')"
                />
            </div>
            <div x-bind:class="! edit && 'pointer-events-none'">
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
            </div>
            <x-flux::currency
                x-bind:readonly="!edit"
                :label="__('Expected Revenue')"
                :suffix="resolve_static(\FluxErp\Models\Currency::class, 'default')?->symbol"
                wire:model.blur="leadForm.expected_revenue"
            />
            <x-flux::currency
                x-bind:readonly="!edit"
                :label="__('Expected Gross Profit')"
                :suffix="resolve_static(\FluxErp\Models\Currency::class, 'default')?->symbol"
                wire:model.blur="leadForm.expected_gross_profit"
            />
            <div x-bind:class="! edit && 'pointer-events-none'">
                <x-rating
                    x-bind:readonly="!edit"
                    wire:model.number="leadForm.score"
                    :text="__('Score')"
                    :quantity="5"
                    position="right"
                />
            </div>
            <div x-bind:class="! edit && 'pointer-events-none'">
                <x-range
                    x-bind:readonly="!edit"
                    wire:model.number="leadForm.probability_percentage"
                    :hint="__('Probability to win this lead…')"
                >
                    <x-slot:label>
                        <span
                            x-cloak
                            x-show="$wire.leadForm.probability_percentage !== null"
                            x-text="window.formatters.percentage($wire.leadForm.probability_percentage / 100)"
                        ></span>
                    </x-slot>
                </x-range>
            </div>
            <div
                x-bind:class="! edit && 'pointer-events-none'"
                class="flex gap-4"
            >
                <x-date
                    wire:model="leadForm.start"
                    :label="__('Start Date')"
                    x-bind:readonly="!edit"
                />
                <x-date
                    wire:model="leadForm.end"
                    :label="__('End Date')"
                    x-bind:readonly="!edit"
                />
            </div>
            <div x-bind:class="!edit && 'pointer-events-none'">
                <x-select.styled
                    :label="__('Categories')"
                    wire:model="leadForm.categories"
                    x-bind:readonly="!edit"
                    multiple
                    select="label:label|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\Category::class),
                        'method' => 'POST',
                        'params' => [
                            'where' => [
                                [
                                    'model_type',
                                    '=',
                                    morph_alias(\FluxErp\Models\Lead::class),
                                ],
                            ],
                        ],
                    ]"
                />
            </div>
            <div x-bind:class="!edit && 'pointer-events-none'">
                <x-select.styled
                    multiple
                    x-bind:disabled="! $wire.edit"
                    wire:model.number="leadForm.tags"
                    select="label:label|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\Tag::class),
                        'method' => 'POST',
                        'params' => [
                            'option-value' => 'id',
                            'where' => [
                                [
                                    'type',
                                    '=',
                                    morph_alias(\FluxErp\Models\Lead::class),
                                ],
                            ],
                        ],
                    ]"
                >
                    <x-slot:label>
                        <div class="flex items-center gap-2">
                            <x-label :label="__('Tags')" />
                            @canAction(\FluxErp\Actions\Tag\CreateTag::class)
                                <x-button.circle
                                    sm
                                    icon="plus"
                                    color="emerald"
                                    wire:click="addTag($promptValue())"
                                    wire:flux-confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}"
                                />
                            @endcanAction
                        </div>
                    </x-slot>
                </x-select.styled>
            </div>
        </div>
    </x-card>
    <livewire:lead.orders wire:model="leadForm" />
</div>

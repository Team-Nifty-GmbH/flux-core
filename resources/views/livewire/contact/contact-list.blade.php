<div>
    @section('modals')
    @canAction(\FluxErp\Actions\Contact\UpdateContact::class)
        <x-modal id="assign-agent-modal" persistent>
            <x-select.styled
                :label="__('Commission Agent')"
                wire:model="agentId"
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'media',
                        'where' => [
                            [
                                'is_active',
                                '=',
                                true,
                            ],
                        ],
                    ],
                ]"
            />
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    flat
                    :text="__('Cancel') "
                    x-on:click="$modalClose('assign-agent-modal')"
                />
                <x-button
                    color="indigo"
                    :text="__('Assign')"
                    loading="assignToAgent"
                    x-bind:disabled="! $wire.agentId"
                    wire:flux-confirm.type.warning="{{ __('wire:confirm.contact.assign-agent') }}"
                    wire:click="assignToAgent().then((success) => {if(success) $modalClose('assign-agent-modal');})"
                />
            </x-slot>
        </x-modal>
    @endcanAction

    {!! $createContactForm->autoRender($__data) !!}

    @show
</div>

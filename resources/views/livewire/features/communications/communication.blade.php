<div x-data="{
    init() {
        this.$watch('modelType', (value) => {
            this.modelId = null;
            this.relatedSelected(value);
        });
        this.$watch('modelId', () => {
            if (this.modelType && this.modelId) {
                $wire.addCommunicatable(this.modelType, this.modelId);
                this.modelType = null;
                this.modelId = null;
                Alpine.$data(document.getElementById('communicatable-id').querySelector('[x-data]')).clear();
                Alpine.$data(document.getElementById('communicatable-type').querySelector('[x-data]')).clear();
            }
        });
    },
    addReceiver($event, type) {
        let value = $event.target.value;
        if ($event instanceof KeyboardEvent) {
            value = value.slice(0, -1);
        }

        value = value.trim();

        if (value && ($event instanceof FocusEvent || ($event.code === 'Comma' || $event.code === 'Enter' || $event.code === 'Space'))) {
            const email = value.match(/<([^>]*)>/);
            if (email && email[1]) {
                value = email[1];
            }

            $wire.communication[type].push(value);
            $event.target.value = null;
        }
    },
    relatedSelected(type) {
        let searchRoute = '{{  route('search', '') }}';
        searchRoute = searchRoute + '/' + type;
        $tallstackuiSelect('communicatable-id').setRequestUrl(searchRoute);
    },
    modelType: null,
    modelId: null
}">
    {!! $this->renderCreateDocumentsModal() !!}
    <x-modal id="edit-communication" size="5xl" :title="__('Edit Communication')">
        <div class="flex flex-col gap-1.5">
            <div>
                <x-select.styled
                    required
                    x-on:select="$event.detail.select.value === 'mail' ? $wire.communication.to = [] : null"
                    wire:model="communication.communication_type_enum"
                    :label="__('Communication Type')"
                    select="label:label|value:name"
                    :options="$communicationTypes"
                />
            </div>
            <div class="flex gap-1.5">
                <template x-for="(model, index) in $wire.communication.communicatables">
                    <x-badge flat color="indigo" cl>
                        <x-slot name="left" class="p-0.5">
                            <x-button.circle
                                x-cloak
                                x-show="model.href"
                                xs
                                color="gray"
                                href="#"
                                x-bind:href="model.href"
                                icon="eye"
                                class="size-4"
                            />
                        </x-slot>
                        <x-slot:text>
                            <span x-text="model.label"></span>
                        </x-slot:text>
                        <x-slot
                            name="right"
                            class="relative flex items-center size-2"
                        >
                            <button
                                type="button"
                                x-on:click="$wire.communication.communicatables.splice(index, 1)"
                            >
                                <x-icon
                                    name="x-mark"
                                    class="size-4"
                                />
                            </button>
                        </x-slot>
                    </x-badge>
                </template>
            </div>
            <div id="communicatable-type">
                <x-select.styled
                    :label="__('Model')"
                    x-on:select="modelType = $event.detail?.select.value"
                    :options="$this->communicatables"
                />
            </div>
            <div id="communicatable-id" x-show="modelType" x-cloak>
                <x-select.styled
                    :label="__('Record')"
                    x-on:select="modelId = $event.detail?.select.value;"
                    x-model="modelId"
                    select="label:label|value:id"
                    :request="[
                        'url' => route('search', ''),
                        'method' => 'POST',
                    ]"
                />
            </div>
            <div class="m-2">
                <hr>
            </div>
            <div class="flex flex-col gap-4" x-show="$wire.communication.communication_type_enum === 'phone-call' || $wire.communication.communication_type_enum === 'letter'">
                <x-select.styled
                    :label="__('Address')"
                    x-on:select="$wire.setTo($event.detail.select)"
                    select="label:label|value:id"
                    :request="[
                        'url' => route('search', \FluxErp\Models\Address::class),
                        'method' => 'POST',
                        'params' => [
                            'fields' => [
                                'id',
                                'name',
                                'zip',
                                'city',
                                'street',
                            ],
                            'where' => $this->modelType === morph_alias(\FluxErp\Models\Contact::class)
                                ? [
                                    [
                                        'contact_id',
                                        '=',
                                        $contactId,
                                    ],
                                ]
                                : [],
                        ],
                    ]"
                />
                <x-textarea :label="__('To')" wire:model="communication.to.0"/>
            </div>
            <div class="flex flex-col gap-1.5" x-show="$wire.communication.communication_type_enum === 'mail'">
                <x-label>{{ __('To') }}</x-label>
                <div class="flex gap-1">
                    <template x-for="to in $wire.communication.to">
                        <x-badge flat color="indigo" cl>
                            <x-slot:text>
                                <span x-text="to"></span>
                            </x-slot:text>
                            <x-slot
                                name="right"
                                class="relative flex items-center w-2 h-2"
                            >
                                <button
                                    type="button"
                                    x-bind:disabled="$wire.communication.id && $wire.communication.communication_type_enum === 'mail'"
                                    x-on:click="$wire.communication.to.splice($wire.communication.to.indexOf(to), 1)"
                                >
                                    <x-icon
                                        name="x-mark"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </x-slot>
                        </x-badge>
                    </template>
                </div>
                <x-input :placeholder="__('Add a new to')"
                         x-on:blur="addReceiver($event, 'to')"
                         x-on:keyup="addReceiver($event, 'to')"
                         class="w-full"
                         x-bind:disabled="$wire.communication.id && $wire.communication.communication_type_enum === 'mail'"
                />
            </div>
            <div class="flex flex-col gap-1.5" x-cloak x-show="$wire.communication.communication_type_enum === 'mail'">
                <x-label :label="__('CC')" />
                <div class="flex gap-1">
                    <template x-for="cc in $wire.communication.cc">
                        <x-badge flat color="indigo" cl>
                            <x-slot:text>
                                <span x-text="cc"></span>
                            </x-slot:text>
                            <x-slot
                                name="right"
                                class="relative flex items-center w-2 h-2"
                            >
                                <button
                                    type="button"
                                    x-bind:disabled="$wire.communication.id && $wire.communication.communication_type_enum === 'mail'"
                                    x-on:click="$wire.communication.cc.splice($wire.communication.cc.indexOf(to), 1)"
                                >
                                    <x-icon
                                        name="x-mark"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </x-slot>
                        </x-badge>
                    </template>
                </div>
                <x-input :placeholder="__('Add a new cc')"
                         x-on:blur="addReceiver($event, 'cc')"
                         x-on:keyup="addReceiver($event, 'cc')"
                         class="w-full"
                         x-bind:disabled="$wire.communication.id && $wire.communication.communication_type_enum === 'mail'"
                />
            </div>
            <div class="flex flex-col gap-1.5" x-cloak x-show="$wire.communication.communication_type_enum === 'mail'">
                <x-label :label=" __('BCC')" />
                <div class="flex gap-1">
                    <template x-for="bcc in $wire.communication.bcc">
                        <x-badge flat color="indigo" cl>
                            <x-slot:text>
                                <span x-text="bcc"></span>
                            </x-slot:text>
                            <x-slot
                                name="right"
                                class="relative flex items-center w-2 h-2"
                            >
                                <button
                                    type="button"
                                    x-bind:disabled="$wire.communication.id && $wire.communication.communication_type_enum === 'mail'"
                                    x-on:click="$wire.communication.bcc.splice($wire.communication.bcc.indexOf(to), 1)"
                                >
                                    <x-icon
                                        name="x-mark"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </x-slot>
                        </x-badge>
                    </template>
                </div>
                <x-input :placeholder="__('Add a new bcc')"
                         x-on:blur="addReceiver($event, 'bcc')"
                         x-on:keyup="addReceiver($event, 'bcc')"
                         class="w-full"
                         x-bind:disabled="$wire.communication.id && $wire.communication.communication_type_enum === 'mail'"
                />
            </div>
            <div class="grow">
                <x-input wire:model="communication.subject"
                         class="w-full"
                         :label="__('Subject')"
                         x-bind:disabled="$wire.communication.id && $wire.communication.communication_type_enum === 'mail'"
                />
            </div>
            <x-flux::editor wire:model="communication.html_body" :label="__('Content')"/>
            <x-select.styled
                :label="__('Tags')"
                multiple
                wire:model.number="communication.tags"
                x-bind:disabled="$wire.communication.id && $wire.communication.communication_type_enum === 'mail'"
                select="label:name|value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\Tag::class),
                    'method' => 'POST',
                    'params' => [
                        'option-value' => 'id',
                        'where' => [
                            [
                                'type',
                                '=',
                                app(\FluxErp\Models\Communication::class)->getMorphClass(),
                            ],
                        ],
                    ],
                ]"
            >
                <x-slot:after>
                    @canAction(\FluxErp\Actions\Tag\CreateTag::class)
                    <div class="px-1">
                        <x-button color="emerald" full :text="__('Add')" wire:click="addTag($promptValue())" wire:flux-confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}" />
                    </div>
                    @endCanAction
                </x-slot:after>
            </x-select.styled>
            <x-flux::features.media.upload-form-object :text="__('Attachments')" wire:model="attachments" :multiple="true" x-bind:disabled="$wire.communication.id && $wire.communication.communication_type_enum === 'mail'"/>
        </div>

        <x-slot:footer>
            <x-button color="secondary" light
                x-on:click="$modalClose('edit-communication')"
                :text="__('Cancel')"
            />
            <x-button
                color="indigo"
                wire:click="save().then((success) => { if(success) $modalClose('edit-communication'); })"
                primary
                :text="__('Save')"
            />
            <div x-show="$wire.communication.communication_type_enum === 'mail' && !$wire.communication.id">
                <x-button
                    color="indigo"
                    wire:click="send().then((success) => { if(success) $modalClose('edit-communication'); })"
                    primary
                    :text="__('Send')"
                />
            </div>
        </x-slot:footer>
    </x-modal>
    <x-modal id="create-preview" :title="__('Create Preview')">
        <div class="grid grid-cols-3 gap-1.5">
            <div class="font-semibold text-sm">{{ __('Print') }}</div>
            <div class="font-semibold text-sm">{{ __('Email') }}</div>
            <div class="font-semibold text-sm">{{ __('Download') }}</div>
            @foreach($printLayouts as $printLayout)
                <x-checkbox wire:model.boolean="selectedPrintLayouts.print.{{ $printLayout }}" :label="__($printLayout)" />
                <x-checkbox wire:model.boolean="selectedPrintLayouts.email.{{ $printLayout }}" :label="__($printLayout)" />
                <x-checkbox wire:model.boolean="selectedPrintLayouts.download.{{ $printLayout }}" :label="__($printLayout)" />
            @endforeach
        </div>
        <x-slot:footer>
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('create-preview')" />
            <x-button color="indigo" :text="__('Continue')" loading="createDocuments" wire:click="createDocuments().then(() => { $modalClose('create-preview'); });" />
        </x-slot:footer>
    </x-modal>
</div>

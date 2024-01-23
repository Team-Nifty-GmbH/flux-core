<div x-data="{
    addReceiver($event, type) {
        let value = $event.target.value;
        if ($event instanceof KeyboardEvent) {
            value = value.slice(0, -1);
        }

        value = value.trim();

        if (value && ($event instanceof FocusEvent || ($event.code === 'Comma' || $event.code === 'Enter' || $event.code === 'Space'))) {
            $wire.communication[type].push(value);
            $event.target.value = null;
        }
    }
}">
    <x-modal name="edit-communication" max-width="5xl">
        <x-card :title="__('Edit Communication')" class="flex flex-col gap-4">
            <x-select
                :clearable="false"
                :options="$communicationTypes"
                wire:model="communication.communication_type"
                :label="__('Communication Type')"
                option-value="name"
                option-label="label"
            />
            <div class="grow">
                <x-input wire:model="communication.from" class="w-full" :label="__('From')"/>
            </div>
            <div class="flex flex-col gap-1.5">
                <x-label>{{ __('To') }}</x-label>
                <div class="flex gap-1">
                    <template x-for="to in $wire.communication.to">
                        <x-badge flat primary cl>
                            <x-slot:label>
                                <span x-text="to"></span>
                            </x-slot:label>
                            <x-slot
                                name="append"
                                class="relative flex items-center w-2 h-2"
                            >
                                <button
                                    type="button"
                                    x-on:click="$wire.communication.to.splice($wire.communication.to.indexOf(to), 1)"
                                >
                                    <x-icon
                                        name="x"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </x-slot>
                        </x-badge>
                    </template>
                </div>
                <x-input :placeholder="__('Add a new to')" x-on:blur="addReceiver($event, 'to')" x-on:keyup="addReceiver($event, 'to')" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5" x-cloak x-show="$wire.communication.communication_type === 'mail'">
                <x-label>{{ __('CC') }}</x-label>
                <div class="flex gap-1">
                    <template x-for="cc in $wire.communication.cc">
                        <x-badge flat primary cl>
                            <x-slot:label>
                                <span x-text="cc"></span>
                            </x-slot:label>
                            <x-slot
                                name="append"
                                class="relative flex items-center w-2 h-2"
                            >
                                <button
                                    type="button"
                                    x-on:click="$wire.communication.cc.splice($wire.communication.cc.indexOf(to), 1)"
                                >
                                    <x-icon
                                        name="x"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </x-slot>
                        </x-badge>
                    </template>
                </div>
                <x-input :placeholder="__('Add a new cc')" x-on:blur="addReceiver($event, 'cc')" x-on:keyup="addReceiver($event, 'cc')" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5" x-cloak x-show="$wire.communication.communication_type === 'mail'">
                <x-label>{{ __('BCC') }}</x-label>
                <div class="flex gap-1">
                    <template x-for="bcc in $wire.communication.bcc">
                        <x-badge flat primary cl>
                            <x-slot:label>
                                <span x-text="bcc"></span>
                            </x-slot:label>
                            <x-slot
                                name="append"
                                class="relative flex items-center w-2 h-2"
                            >
                                <button
                                    type="button"
                                    x-on:click="$wire.communication.bcc.splice($wire.communication.bcc.indexOf(to), 1)"
                                >
                                    <x-icon
                                        name="x"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </x-slot>
                        </x-badge>
                    </template>
                </div>
                <x-input :placeholder="__('Add a new bcc')" x-on:blur="addReceiver($event, 'bcc')" x-on:keyup="addReceiver($event, 'bcc')" class="w-full" />
            </div>
            <div class="grow">
                <x-input wire:model="communication.subject" class="w-full" :label="__('Subject')"/>
            </div>
            <x-editor wire:model="communication.html_body" :label="__('Content')" />
            <x-select
                :label="__('Tags')"
                multiselect
                wire:model.number="communication.tags"
                option-value="id"
                option-label="label"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Tag::class),
                    'method' => 'POST',
                    'params' => [
                        'option-value' => 'id',
                        'where' => [
                            [
                                'type',
                                '=',
                                \FluxErp\Models\Communication::class,
                            ],
                        ],
                    ],
                ]"
            >
                <x-slot:beforeOptions>
                    <div class="px-1">
                        <x-button positive full :label="__('Add')" wire:click="addTag($promptValue())" wire:confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}" />
                    </div>
                </x-slot:beforeOptions>
            </x-select>
            <x-features.media.upload-form-object :label="__('Attachments')" wire:model="attachments" :multiple="true"/>
            <x-slot:footer>
                <div class="flex gap-1.5 justify-end">
                    <x-button
                        x-on:click="close()"
                        :label="__('Cancel')"
                    />
                    <x-button
                        wire:click="save().then((success) => { if(success) close(); })"
                        primary
                        :label="__('Save')"
                    />
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <x-modal name="create-preview">
        <x-card :title="__('Create Preview')">
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
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button primary :label="__('Continue')" spinner wire:click="createDocuments().then(() => { close(); });" />
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>

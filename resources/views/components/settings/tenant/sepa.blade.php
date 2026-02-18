<div
    class="flex flex-col gap-2">
    <x-input
        :label="__('Creditor Identifier')"
        wire:model="tenant.creditor_identifier"
    />
    <x-textarea
        :label="__('Sepa Text Basic')"
        wire:model="tenant.sepa_text_basic"
    />
    <x-textarea
        :label="__('Sepa Text B2B')"
        wire:model="tenant.sepa_text_b2b"
    />
    <div
        x-data="sepaPreview($wire,$refs)"
        x-init="onInit(@json($this->tenant->id))"
        class="flex flex-col w-full gap-4 min-h-[600px] max-h-[800px]">
            <div>{{ __('Preview') }}</div>
            <div
                x-cloak
                x-show="!route"
                class="flex-1 flex items-center justify-center text-gray-500">
                <div>{{ __('Sepa Mandate not existing, please create one') }}</div>
            </div>
            <iframe
                x-cloak
                x-show="route"
                x-ref="frame"
                class="flex-1"
                loading="lazy"
            ></iframe>
            <x-button
                x-cloak
                x-show="route"
                class="w-32"
                loading
                color="indigo"
                :text="__('Download')"
            />
    </div>
</div>

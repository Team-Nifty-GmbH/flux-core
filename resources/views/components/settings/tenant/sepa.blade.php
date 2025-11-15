<div class="flex flex-col gap-2">
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
</div>

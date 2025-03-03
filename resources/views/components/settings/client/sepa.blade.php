<div class="flex flex-col gap-2">
    <x-input :label="__('Creditor Identifier')" wire:model="client.creditor_identifier"/>
    <x-textarea :label="__('Sepa Text')" wire:model="client.sepa_text"/>
</div>

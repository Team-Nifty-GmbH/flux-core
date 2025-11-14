<x-input
    autofocus
    wire:model="tenantForm.name"
    placeholder="e.g. Team Nifty GmbH…"
    :label="__('Name')"
/>
<x-input
    wire:model="tenantForm.client_code"
    placeholder="e.g. TN"
    :label="__('Tenant Code')"
/>
<x-input
    wire:model="tenantForm.ceo"
    placeholder="e.g. John Doe…"
    :label="__('CEO')"
/>
<x-input
    wire:model="tenantForm.street"
    placeholder="e.g. Example street 1…"
    :label="__('Street')"
/>
<x-input
    wire:model="tenantForm.city"
    placeholder="e.g. New York…"
    :label="__('City')"
/>
<x-input
    wire:model="tenantForm.postcode"
    placeholder="e.g. 10017…"
    :label="__('Zip')"
/>
<x-input
    wire:model="tenantForm.phone"
    placeholder="e.g. +01 12563 4589…"
    :label="__('Phone')"
/>
<x-input
    wire:model="tenantForm.email"
    placeholder="e.g. info@team-nifty.com"
    :label="__('Email')"
/>
<x-input
    wire:model="tenantForm.website"
    placeholder="e.g. https://team-nifty.com"
    :label="__('URL')"
/>

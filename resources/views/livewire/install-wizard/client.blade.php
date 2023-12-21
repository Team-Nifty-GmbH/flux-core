<x-input autofocus wire:model="clientForm.name" placeholder="e.g. Team Nifty GmbH…" :label="__('Name')" />
<x-input wire:model="clientForm.client_code" placeholder="e.g. TN" :label="__('Client Code')" />
<x-input wire:model="clientForm.ceo"  placeholder="e.g. John Doe…" :label="__('CEO')" />
<x-input wire:model="clientForm.street"  placeholder="e.g. Example street 1…" :label="__('Street')" />
<x-input wire:model="clientForm.city"  placeholder="e.g. New York…" :label="__('City')" />
<x-input wire:model="clientForm.postcode"  placeholder="e.g. 10017…" :label="__('Zip')" />
<x-input wire:model="clientForm.phone"  placeholder="e.g. +01 12563 4589…" :label="__('Phone')" />
<x-input wire:model="clientForm.email" placeholder="e.g. info@team-nifty.com" :label="__('Email')" />
<x-input wire:model="clientForm.website" placeholder="e.g. https://team-nifty.com" :label="__('URL')" />

<form wire:submit="addVatRate(); $refs.name.focus();" class="flex flex-col gap-4">
    <x-input x-ref="name" autofocus :label="__('Name')" placeholder="e.g. Standard…" wire:model="vatRateForm.name"/>
    <x-number wire:model="vatRateForm.rate_percentage_frontend" :label="__('Rate Percentage')" />
    <x-button color="indigo" :text="__('Add')" type="submit"/>
</form>
<x-error name="vatRates" />
<x-table>
    <x-slot:header>
        <x-table.head-cell>
            {{ __('Name') }}
        </x-table.head-cell>
        <x-table.head-cell>
            {{ __('Vat rate') }}
        </x-table.head-cell>
        <x-table.head-cell>
        </x-table.head-cell>
    </x-slot:header>
    @foreach($vatRates as $index => $vatRate)
        <x-table.row>
            <x-table.cell>{{ $vatRate['name'] }}</x-table.cell>
            <x-table.cell>{{ $vatRate['rate_percentage_frontend'] }}%</x-table.cell>
            <x-table.cell>
                <x-button wire:click="removeVatRate({{ $index }})" color="red" icon="trash" />
            </x-table.cell>
        </x-table.row>
    @endforeach
</x-table>

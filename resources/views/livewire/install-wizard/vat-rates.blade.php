<form wire:submit="addVatRate(); $refs.name.focus();" class="flex flex-col gap-4">
    <x-input x-ref="name" autofocus :label="__('Name')" placeholder="e.g. Standard…" wire:model="vatRateForm.name"/>
    <x-inputs.number wire:model="vatRateForm.rate_percentage_frontend" :label="__('Rate Percentage')" />
    <x-button primary :label="__('Add')" type="submit"/>
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
                <x-button wire:click="removeVatRate({{ $index }})" negative icon="trash" />
            </x-table.cell>
        </x-table.row>
    @endforeach
</x-table>

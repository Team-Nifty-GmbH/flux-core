<form
    wire:submit="addVatRate(); $refs.name.focus();"
    class="flex flex-col gap-4"
>
    <x-input
        x-ref="name"
        autofocus
        :label="__('Name')"
        placeholder="e.g. Standard…"
        wire:model="vatRateForm.name"
    />
    <x-number
        wire:model="vatRateForm.rate_percentage_frontend"
        :label="__('Rate Percentage')"
    />
    <x-button color="indigo" :text="__('Add')" type="submit" />
</form>
<x-error name="vatRates" />
<x-flux::table>
    <x-slot:header>
        <x-flux::table.head-cell>
            {{ __("Name") }}
        </x-flux::table.head-cell>
        <x-flux::table.head-cell>
            {{ __("Vat rate") }}
        </x-flux::table.head-cell>
        <x-flux::table.head-cell></x-flux::table.head-cell>
    </x-slot>
    @foreach ($vatRates as $index => $vatRate)
        <x-flux::table.row>
            <x-flux::table.cell>{{ $vatRate["name"] }}</x-flux::table.cell>
            <x-flux::table.cell>
                {{ $vatRate["rate_percentage_frontend"] }}%
            </x-flux::table.cell>
            <x-flux::table.cell>
                <x-button
                    wire:click="removeVatRate({{ $index }})"
                    color="red"
                    icon="trash"
                />
            </x-flux::table.cell>
        </x-flux::table.row>
    @endforeach
</x-flux::table>

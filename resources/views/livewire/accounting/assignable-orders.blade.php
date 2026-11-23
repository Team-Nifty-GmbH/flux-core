<div>
    @if ($this->orders)
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                :label="__('Book Onto Existing Order')"
                :hint="__('The invoice takes over the chosen order instead of creating a new one.')"
                wire:model.live.number="orderId"
                select="label:label|value:value|description:description"
                :options="$this->orders"
            />
            <div x-cloak x-show="$wire.hasDeviation">
                <x-alert color="amber" light>
                    {{ __('The invoice amount differs from the planned amount of the chosen order. The order will be overwritten with the invoice amount.') }}
                </x-alert>
            </div>
        </div>
    @endif
</div>

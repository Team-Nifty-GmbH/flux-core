<div>
    <div class="flex items-center justify-end pb-5">
        <div class="mt-3 sm:mt-0 sm:ml-4">
            @can('api.serial-numbers.post')
                <x-button :href="route('products.serial-numbers.id?', ['id' => 0, 'addressId' => $this->address['id']])" primary>{{ __('Assign serial number') }}</x-button>
            @endcan
        </div>
    </div>
    <livewire:data-tables.serial-number-list cache-key="address.serial-number-list" wire:key="{{ uniqid() }}" :filters="[['address_id', '=', $this->address['id']]]" />
</div>

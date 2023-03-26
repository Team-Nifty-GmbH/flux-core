<div>
    <x-additional-columns
        wire="address"
        :model="\FluxErp\Models\Address::class"
        :model-id="$this->address['id']"
        table
    />
</div>

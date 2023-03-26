<div>
    <livewire:data-tables.ticket-list
        :filters="[
            [
                'authenticatable_type',
                '=',
                \FluxErp\Models\Address::class
            ],
            'whereIn' => [
                'authenticatable_id',
                \Illuminate\Support\Arr::pluck($this->contact['addresses'], 'id')
            ]
        ]"
    />
</div>

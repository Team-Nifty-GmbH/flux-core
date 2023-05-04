<div>
    <livewire:data-tables.order-list
        wire:key="{{ uniqid() }}"
        :filters="[['column' => 'contact_id', 'value' => $this->contact['id']]]"
    />
</div>

<div>
    <livewire:order.order-list
        wire:key="{{ uniqid() }}"
        :filters="[['column' => 'contact_id', 'value' => $this->contact['id']]]"
    />
</div>

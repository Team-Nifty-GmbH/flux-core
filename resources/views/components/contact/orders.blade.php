<div>
    <livewire:data-tables.order-list wire:key="{{ uniqid() }} " :filters="[['contact_id' => $this->contact['id']]]" />
</div>

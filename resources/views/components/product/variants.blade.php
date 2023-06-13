<div>
    <livewire:data-tables.product-list
        :is-searchable="false"
        :filters="[['parent_id', '=', $this->product['id']]]"
        cache-key="product.variants.product-list"
    />
</div>

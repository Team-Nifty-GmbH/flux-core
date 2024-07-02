<?php

namespace FluxErp\Livewire\Forms\Portal;

use Livewire\Attributes\Locked;
use Livewire\Form;

class ProductForm extends Form
{
    #[Locked]
    public ?int $id = null;

    #[Locked]
    public ?int $parent_id = null;

    public ?string $product_number = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $buy_price = null;

    public ?string $root_price_flat = null;

    public ?string $root_discount_percentage = null;

    public ?string $cover_url = null;

    public bool $is_highlight = false;

    public int $children_count = 0;

    public int $amount = 1;

    public ?array $productOptionGroups = null;

    public ?array $media = null;

    public ?array $meta = null;

    public ?array $product_cross_sellings = null;

    public function fill($values): void
    {
        parent::fill($values);

        $this->buy_price ??= data_get($values, 'price.price');
        $this->root_price_flat ??= data_get($values, 'price.root_price_flat');
        $this->root_discount_percentage ??= data_get($values, 'price.root_discount_percentage');
    }
}

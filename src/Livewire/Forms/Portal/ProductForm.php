<?php

namespace FluxErp\Livewire\Forms\Portal;

use Livewire\Attributes\Locked;
use Livewire\Form;

class ProductForm extends Form
{
    public ?array $additionalMedia = null;

    public int $amount = 1;

    public ?array $bundle_products = null;

    public ?string $buy_price = null;

    public int $children_count = 0;

    public ?string $cover_url = null;

    public ?string $description = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_highlight = false;

    public ?array $media = null;

    public ?array $meta = null;

    public ?string $name = null;

    #[Locked]
    public ?int $parent_id = null;

    public ?array $product_cross_sellings = null;

    public ?string $product_number = null;

    public ?array $productOptionGroups = null;

    public ?string $root_discount_percentage = null;

    public ?string $root_price_flat = null;

    public function fill($values): void
    {
        parent::fill($values);

        $this->buy_price ??= data_get($values, 'price.price');
        $this->root_price_flat ??= data_get($values, 'price.root_price_flat');
        $this->root_discount_percentage ??= data_get($values, 'price.root_discount_percentage');
    }
}

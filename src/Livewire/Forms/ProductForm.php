<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\UpdateProduct;
use Livewire\Attributes\Locked;
use Livewire\Form;

class ProductForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?int $client_id = null;

    public ?int $vat_rate_id = null;

    public ?int $unit_id = null;

    public ?int $purchase_unit_id = null;

    public ?int $reference_unit_id = null;

    public ?string $product_number = null;

    public ?string $description = null;

    public ?float $weight_gram = null;

    public ?float $dimension_height_mm = null;

    public ?float $dimension_width_mm = null;

    public ?float $dimension_length_mm = null;

    public ?string $ean = null;

    public ?int $stock = null;

    public ?int $min_delivery_time = null;

    public ?int $max_delivery_time = null;

    public ?int $restock_time = null;

    public ?float $purchase_steps = null;

    public ?float $min_purchase = null;

    public ?float $max_purchase = null;

    public ?string $seo_keywords = null;

    public ?string $manufacturer_product_number = null;

    public ?string $posting_account = null;

    public ?float $warning_stock_amount = null;

    public ?bool $is_active = true;

    public ?bool $is_highlight = false;

    public ?bool $is_bundle = false;

    public ?bool $is_shipping_free = false;

    public ?bool $is_required_product_serial_number = false;

    public ?bool $is_required_manufacturer_serial_number = false;

    public ?bool $is_auto_create_serial_number = false;

    public ?bool $is_product_serial_number = false;

    public ?bool $is_nos = false;

    public ?bool $is_active_export_to_web_shop = false;

    public function save(): void
    {
        $action = $this->id
            ? UpdateProduct::make($this->toArray())
            : CreateProduct::make($this->toArray());

        $response = $action
            ->checkPermission()
            ->validate()
            ->execute();

        $this->fill($response);
    }
}

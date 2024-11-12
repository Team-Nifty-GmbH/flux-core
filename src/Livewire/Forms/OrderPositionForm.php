<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;
use Livewire\Attributes\Locked;

class OrderPositionForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?int $ledger_account_id = null;

    public ?int $order_id = null;

    public ?int $origin_position_id = null;

    public ?int $parent_id = null;

    public ?int $price_id = null;

    public ?int $price_list_id = null;

    public ?int $product_id = null;

    public ?int $vat_rate_id = null;

    public ?int $warehouse_id = null;

    public null|float|string $amount = 1;

    public ?string $amount_bundle = null;

    public ?string $discount_percentage = null;

    public ?string $margin = null;

    public ?string $provision = null;

    public null|string|float $purchase_price = 0;

    public null|string|float $total_base_gross_price = null;

    public null|string|float $total_base_net_price = null;

    public null|string|float $total_gross_price = null;

    public null|string|float $total_net_price = null;

    public null|string|float $vat_price = null;

    public null|string|float $unit_price = null;

    public null|string|float $unit_net_price = null;

    public null|string|float $unit_gross_price = null;

    public ?float $vat_rate_percentage = null;

    public ?string $amount_packed_products = null;

    public ?string $customer_delivery_date = null;

    public ?string $ean_code = null;

    public ?string $possible_delivery_date = null;

    public ?string $unit_gram_weight = null;

    public ?string $description = null;

    public ?string $name = null;

    public ?string $product_number = null;

    public ?int $sort_number = null;

    public ?bool $is_alternative = false;

    public bool $is_net = true;

    public bool $is_free_text = false;

    public bool $is_bundle_position = false;

    // Virtual attributes
    public ?string $slug_position = null;

    public ?int $contact_id = null;

    public ?string $indentation = null;

    public ?string $alternative_tag = null;

    protected Product $product;

    protected function getActions(): array
    {
        return [
            'create' => CreateOrderPosition::class,
            'update' => UpdateOrderPosition::class,
            'delete' => DeleteOrderPosition::class,
        ];
    }

    public function fillFromProduct(?Product $product = null): void
    {
        if ($product instanceof Product) {
            $this->product_id = $product->id;
        }

        $product ??= resolve_static(Product::class, 'query')
            ->whereKey($this->product_id)
            ->first();
        $this->product = $product;

        $this->vat_rate_id = $this->product->vat_rate_id;
        $this->name = $this->product->name;
        $this->product_number = $this->product->product_number;
        $this->ean_code = $this->product->ean;
        $this->unit_gram_weight = $this->product->weight_gram;
        $this->purchase_price = $this->product->purchasePrice($this->amount)?->price ?? 0;

        $this->warehouse_id ??= Warehouse::default()?->id;
        $this->description ??= $this->product->description;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }
}

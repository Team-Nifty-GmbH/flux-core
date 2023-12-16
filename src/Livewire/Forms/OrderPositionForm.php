<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\PriceCalculation;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;
use Livewire\Attributes\Locked;
use Livewire\Form;

class OrderPositionForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?int $ledger_account_id = null;

    public ?int $order_id = null;

    public ?int $origin_position_id = null;

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

    public ?float $vat_rate_percentage = 0;

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

    public function save(): void
    {
        $action = $this->id
            ? UpdateOrderPosition::make($this->toArray())
            : CreateOrderPosition::make($this->toArray());

        $response = $action->checkPermission()->validate()->execute();

        $this->fill($response);
    }

    public function fillFormProduct(?Product $product = null): void
    {
        if ($product instanceof Product) {
            $this->product_id = $product->id;
        }

        $product = $product ?: Product::query()
            ->with('vatRate:id,rate_percentage')
            ->whereKey($this->product_id)
            ->first();
        $this->vat_rate_id = $product->vat_rate_id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->product_number = $product->product_number;
        $this->ean_code = $product->ean;
        $this->unit_gram_weight = $product->weight_gram;

        $this->calculate();

        $this->warehouse_id = $this->warehouse_id ?: Warehouse::query()->first()->id;
    }

    public function validate($rules = null, $messages = [], $attributes = []): void
    {
        $action = $this->id
            ? UpdateOrderPosition::make($this->toArray())
            : CreateOrderPosition::make($this->toArray());

        $action->validate();
    }

    public function calculate(): void
    {
        PriceCalculation::fill($this, [
            'vat_rate_percentage' => $this->vat_rate_percentage,
            'discount_percentage' => $this->discount_percentage,
        ]);
    }
}

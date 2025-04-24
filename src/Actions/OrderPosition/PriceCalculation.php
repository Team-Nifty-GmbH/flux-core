<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Helpers\PriceHelper;
use FluxErp\Livewire\Forms\OrderPositionForm;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Makeable;
use Illuminate\Database\Eloquent\Model;

class PriceCalculation
{
    use Makeable;

    public function __construct(public OrderPosition $orderPosition, public array $data) {}

    public function fill(): void
    {
        // Return if no price could be calculated
        $price = $this->data['unit_price'] ?? $this->orderPosition->unit_price ?? null;

        // A subproduct, aka part of a bundle does not have its own prices.
        if ($this->orderPosition->is_bundle_position) {
            $this->orderPosition->fill([
                'unit_gross_price' => null,
                'unit_net_price' => null,
                'total_gross_price' => null,
                'total_net_price' => null,
                'total_base_gross_price' => null,
                'total_base_net_price' => null,
                'discount_percentage' => null,
                'margin' => null,
                'vat_rate_percentage' => null,
                'vat_rate_id' => null,
            ]);

            return;
        }

        $product = $this->orderPosition instanceof Model
            ? $this->orderPosition->product
            : resolve_static(Product::class, 'query')
                ->whereKey($this->orderPosition->product_id)
                ->first();

        $order = $this->orderPosition instanceof Model
            ? $this->orderPosition->order
            : resolve_static(Order::class, 'query')
                ->whereKey($this->orderPosition->order_id)
                ->first();

        $this->orderPosition->vat_rate_percentage = data_get($this->data, 'vat_rate_percentage')
            ?? resolve_static(VatRate::class, 'query')
                ->whereKey($this->orderPosition->vat_rate_id)
                ->value('rate_percentage')
            ?? $product?->vatRate?->rate_percentage;

        if (is_null($price) && $product) {
            $priceHelper = PriceHelper::make($product);

            if ($contactId = data_get($this->data, 'contact_id')) {
                $priceHelper->setContact(resolve_static(Contact::class, 'query')->whereKey($contactId)->first());
            }

            if ($priceListId = data_get(
                $this->data,
                'price_list_id',
                $this->orderPosition->price_list_id ?? $order->price_list_id
            )) {
                $priceHelper->setPriceList(resolve_static(PriceList::class, 'query')->whereKey($priceListId)->first());
            }

            $productPrice = $priceHelper->price();

            $price = $this->orderPosition->is_net
                ? $productPrice?->getNet($this->orderPosition->vat_rate_percentage)
                : $productPrice?->getGross($this->orderPosition->vat_rate_percentage);
        }

        if (is_null($price)) {
            return;
        }

        $this->orderPosition->unit_price = $price;

        // Collect & set missing data
        if ($product) {
            $this->orderPosition->product_prices = $product->prices()
                ->get([
                    'id',
                    'price_list_id',
                    'price',
                ]);
        }

        // Calculate net and gross unit prices
        if ($this->orderPosition->is_net) {
            $this->orderPosition->unit_net_price = $price;
            $this->orderPosition->unit_gross_price = net_to_gross($price, $this->orderPosition->vat_rate_percentage);
        } else {
            $this->orderPosition->unit_gross_price = $price;
            $this->orderPosition->unit_net_price = gross_to_net($price, $this->orderPosition->vat_rate_percentage);
        }

        // calculate net and gross base prices
        $this->orderPosition->total_base_gross_price = bcmul($this->orderPosition->unit_gross_price, $this->orderPosition->amount);
        $this->orderPosition->total_base_net_price = bcmul($this->orderPosition->unit_net_price, $this->orderPosition->amount);
        $this->orderPosition->total_gross_price = $this->orderPosition->total_base_gross_price;
        $this->orderPosition->total_net_price = $this->orderPosition->total_base_net_price;

        // Purchase-price dependent on stock-bookings.
        if (! $this->orderPosition->purchase_price) {
            $stockPosting = resolve_static(StockPosting::class, 'query')
                ->where('product_id', $this->orderPosition->product_id)
                ->where('warehouse_id', $this->orderPosition->warehouse_id)
                ->orderByDesc('id')
                ->first();

            if (! $stockPosting || bcmul(1, $stockPosting->posting) == 0) {
                $this->orderPosition->purchase_price = 0;
            } else {
                $this->orderPosition->purchase_price = bcdiv($stockPosting->purchase_price, $stockPosting->posting);
            }
        }

        $discounts = $this->data['discounts'] ?? [];
        // Finished collecting, start calculating

        // 1. Calculate sum before tax.
        $preDiscountedPrice = $this->orderPosition->is_net
            ? $this->orderPosition->total_base_net_price
            : $this->orderPosition->total_base_gross_price;

        if ($preDiscountedPrice == 0) {
            $this->orderPosition->vat_price = 0;

            return;
        }

        // 2. Add any discounts.
        $discountedPrice = $preDiscountedPrice;
        foreach ($discounts as $discount) {
            if ($discount['is_percentage']) {
                $discountedPrice = discount($discountedPrice, $discount['discount']);
            } else {
                $discountedPrice = bcsub($discountedPrice, $discount['discount']);
            }
        }

        if (! $discounts && ! is_null(data_get($this->data, 'discount_percentage'))) {
            $discountedPrice = discount($discountedPrice, $this->data['discount_percentage']);
        }

        $discountedNetPrice = $this->orderPosition->is_net
            ? $discountedPrice
            : gross_to_net($discountedPrice, $this->orderPosition->vat_rate_percentage);

        $discountedGrossPrice = $this->orderPosition->is_net
            ? net_to_gross($discountedPrice, $this->orderPosition->vat_rate_percentage)
            : $discountedPrice;

        $totalDiscountPercentage = diff_percentage($preDiscountedPrice, $discountedPrice);
        $margin = bcsub($discountedNetPrice, bcmul($this->orderPosition->purchase_price, $this->orderPosition->amount));

        $multiplier = $order->orderType->order_type_enum->multiplier();
        $this->orderPosition->margin = bcmul($margin, $multiplier);
        $this->orderPosition->discount_percentage = $totalDiscountPercentage == 0
            ? null
            : $totalDiscountPercentage;

        $this->orderPosition->total_net_price = bcmul($discountedNetPrice, $multiplier);
        $this->orderPosition->total_gross_price = bcmul($discountedGrossPrice, $multiplier);

        if (! is_null($this->orderPosition->vat_rate_percentage)) {
            $this->orderPosition->vat_price = bcsub($this->orderPosition->total_gross_price, $this->orderPosition->total_net_price);
        }

        if ($multiplier !== 1) {
            $this->orderPosition->total_base_gross_price = bcmul($this->orderPosition->total_base_gross_price, $multiplier);
            $this->orderPosition->total_base_net_price = bcmul($this->orderPosition->total_base_net_price, $multiplier);
        }
    }
}

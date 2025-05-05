<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Contact;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\StockPosting;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Makeable;

class PriceCalculation
{
    use Makeable;

    public Price|float|string|null $price = null;

    public function __construct(public OrderPosition $orderPosition, public array $data)
    {
        $this->orderPosition->loadMissing([
            'product:id,vat_rate_id',
            'product.vatRate:id,rate_percentage',
            'product.prices:id,price_list_id,price',
            'product.prices.priceList:id,is_net',
        ]);
    }

    public function calculate(): void
    {
        $this->price = data_get($this->data, 'unit_price') ?? $this->orderPosition->unit_price;

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

        $this->orderPosition->vat_rate_percentage = data_get($this->data, 'vat_rate_percentage')
            ?? resolve_static(VatRate::class, 'query')
                ->whereKey($this->orderPosition->vat_rate_id)
                ->value('rate_percentage')
            ?? $this->orderPosition->product?->vatRate?->rate_percentage;

        if (is_null($this->price) && $this->orderPosition->product) {
            $this->price = $this->fillFromPriceHelper();
        }

        if (is_null($this->price)) {
            return;
        }

        $this->calculateUnitPrice();
        $this->calculatePurchasePrice();
        $this->calculateTotalPrices();
        $this->calculateDiscounts();
    }

    protected function calculateDiscounts(): void
    {
        // 1. Calculate sum before tax.
        $preDiscountedPrice = $this->orderPosition->is_net
            ? $this->orderPosition->total_base_net_price
            : $this->orderPosition->total_base_gross_price;

        if (bccomp($preDiscountedPrice, 0) === 0) {
            $this->orderPosition->vat_price = 0;

            return;
        }

        // 2. Add any discounts.
        $discountedPrice = $this->getDiscountedPrice($preDiscountedPrice);

        $discountedNetPrice = $this->orderPosition->is_net
            ? $discountedPrice
            : gross_to_net($discountedPrice, $this->orderPosition->vat_rate_percentage);

        $discountedGrossPrice = $this->orderPosition->is_net
            ? net_to_gross($discountedPrice, $this->orderPosition->vat_rate_percentage)
            : $discountedPrice;

        $totalDiscountPercentage = diff_percentage($preDiscountedPrice, $discountedPrice);
        $margin = bcsub($discountedNetPrice, bcmul($this->orderPosition->purchase_price, $this->orderPosition->amount));

        $multiplier = $this->orderPosition->order->orderType->order_type_enum->multiplier();
        $this->orderPosition->margin = bcmul($margin, $multiplier);
        $this->orderPosition->discount_percentage = $totalDiscountPercentage == 0
            ? null
            : $totalDiscountPercentage;

        $this->orderPosition->total_net_price = bcmul($discountedNetPrice, $multiplier);
        $this->orderPosition->total_gross_price = bcmul($discountedGrossPrice, $multiplier);

        if (! is_null($this->orderPosition->vat_rate_percentage)) {
            $this->orderPosition->vat_price = bcsub(
                $this->orderPosition->total_gross_price,
                $this->orderPosition->total_net_price
            );
        }

        if (bccomp($multiplier, 1) !== 0) {
            $this->orderPosition->total_base_gross_price = bcmul(
                $this->orderPosition->total_base_gross_price,
                $multiplier
            );
            $this->orderPosition->total_base_net_price = bcmul($this->orderPosition->total_base_net_price, $multiplier);
        }
    }

    protected function calculatePurchasePrice(): void
    {
        if (! is_null($this->orderPosition->purchase_price)) {
            return;
        }

        $stockPosting = resolve_static(StockPosting::class, 'query')
            ->where('product_id', $this->orderPosition->product_id)
            ->where('warehouse_id', $this->orderPosition->warehouse_id)
            ->whereNot('posting', 0)
            ->orderByDesc('id')
            ->first();

        if ($stockPosting) {
            $purchasePrice = $this->orderPosition->product?->prices()
                ->whereRelation('priceList', 'is_purchase')
                ->first()
                ?->getNet($this->orderPosition->vat_rate_percentage);
        } else {
            $purchasePrice = bcdiv($stockPosting->purchase_price, $stockPosting->posting);
        }

        $this->orderPosition->purchase_price = bcmul(
            $purchasePrice ?? 0,
            $this->orderPosition->amount ?? 0
        );
    }

    protected function calculateTotalPrices(): void
    {
        // calculate net and gross base prices
        $this->orderPosition->total_base_gross_price = bcmul(
            $this->orderPosition->unit_gross_price,
            $this->orderPosition->amount
        );
        $this->orderPosition->total_base_net_price = bcmul(
            $this->orderPosition->unit_net_price,
            $this->orderPosition->amount
        );
        $this->orderPosition->total_gross_price = $this->orderPosition->total_base_gross_price;
        $this->orderPosition->total_net_price = $this->orderPosition->total_base_net_price;
    }

    protected function calculateUnitPrice(): void
    {
        $price = $this->getUnitPrice();

        // Collect & set missing data
        if ($this->orderPosition->product) {
            $this->orderPosition->product_prices = $this->orderPosition->product->prices()
                ->get([
                    'id',
                    'price_list_id',
                    'price',
                ]);
        }

        $this->orderPosition->unit_price = $price;

        // Calculate net and gross unit prices
        if ($this->orderPosition->is_net) {
            $this->orderPosition->unit_net_price = $price;
            $this->orderPosition->unit_gross_price = net_to_gross($price, $this->orderPosition->vat_rate_percentage);
        } else {
            $this->orderPosition->unit_gross_price = $price;
            $this->orderPosition->unit_net_price = gross_to_net($price, $this->orderPosition->vat_rate_percentage);
        }
    }

    protected function fillFromPriceHelper(): ?Price
    {
        $priceHelper = PriceHelper::make($this->orderPosition->product);

        if ($contactId = data_get($this->data, 'contact_id')) {
            $priceHelper->setContact(
                resolve_static(Contact::class, 'query')
                    ->whereKey($contactId)
                    ->first()
            );
        }

        if ($priceListId = data_get(
            $this->data,
            'price_list_id',
            $this->orderPosition->price_list_id ?? $this->orderPosition->order->price_list_id
        )) {
            $priceHelper->setPriceList(
                resolve_static(PriceList::class, 'query')
                    ->whereKey($priceListId)
                    ->first()
            );
        }

        return $priceHelper->price();
    }

    protected function getDiscountedPrice(string|float $preDiscounted): string|float
    {
        $discountedPrice = $preDiscounted;
        $discounts = data_get($this->data, 'discounts') ?? [];

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

        return $discountedPrice;
    }

    protected function getUnitPrice(): float|string
    {
        if ($this->price instanceof Price) {
            return $this->orderPosition->is_net
                ? $this->price->getNet($this->orderPosition->vat_rate_percentage)
                : $this->price->getGross($this->orderPosition->vat_rate_percentage);
        } else {
            return $this->price ?? 0;
        }
    }
}

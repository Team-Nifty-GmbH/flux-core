<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Models\Price;
use FluxErp\Models\StockPosting;
use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\Model;

class PriceCalculation
{
    public static function fill(Model $orderPosition, array $data): void
    {
        // Return if no price could be calculated
        $price = $data['unit_price'] ?? null;
        $price = is_null($price) ? $orderPosition->price?->price : $price;
        $price = is_null($price)
            ? Price::query()
                ->where('product_id', $orderPosition->product_id)
                ->where('price_list_id', $orderPosition->price_list_id)
                ->first()
                ?->price
            : $price;

        if (is_null($price)) {
            return;
        }

        // A subproduct, aka part of a bundle does not have its own prices.
        if ($orderPosition->is_bundle_position) {
            $orderPosition->fill([
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

        // Collect & set missing data
        $orderPosition->vat_rate_percentage = ($data['vat_rate_percentage'] ?? false)
            ?: VatRate::query()
                ->whereKey($orderPosition->vat_rate_id)
                ->first()
                ?->rate_percentage;

        $product = $orderPosition->product;

        if (! $orderPosition->price && $product) {
            $orderPosition->price_id = Price::query()
                ->where('product_id', $product->id)
                ->where('price_list_id', $orderPosition->price_list_id)
                ->first()
                ?->id;
        }

        if ($product) {
            $orderPosition->product_prices = $product->prices()
                ->get([
                    'id',
                    'price_list_id',
                    'price',
                ]);
        }

        // Calculate net and gross unit prices
        if ($orderPosition->is_net) {
            $orderPosition->unit_net_price = $price;
            $orderPosition->unit_gross_price = net_to_gross($price, $orderPosition->vat_rate_percentage);
        } else {
            $orderPosition->unit_gross_price = $price;
            $orderPosition->unit_net_price = gross_to_net($price, $orderPosition->vat_rate_percentage);
        }

        // calculate net and gross base prices
        $orderPosition->total_base_gross_price = bcmul($orderPosition->unit_gross_price, $orderPosition->amount);
        $orderPosition->total_base_net_price = bcmul($orderPosition->unit_net_price, $orderPosition->amount);
        $orderPosition->total_gross_price = $orderPosition->total_base_gross_price;
        $orderPosition->total_net_price = $orderPosition->total_base_net_price;

        // Purchase-price dependent on stock-bookings.
        if (! $orderPosition->purchase_price) {
            $stockPosting = StockPosting::query()
                ->where('product_id', $orderPosition->product_id)
                ->where('warehouse_id', $orderPosition->warehouse_id)
                ->orderByDesc('id')
                ->first();

            if (! $stockPosting || bcmul(1, $stockPosting->posting) == 0) {
                $orderPosition->purchase_price = 0;
            } else {
                $orderPosition->purchase_price = bcdiv($stockPosting->purchase_price, $stockPosting->posting);
            }
        }

        $discounts = $data['discounts'] ?? [];
        // Finished collecting, start calculating

        // 1. Calculate sum before tax.
        $preDiscountedPrice = $orderPosition->is_net
            ? $orderPosition->total_base_net_price
            : $orderPosition->total_base_gross_price;

        if ($preDiscountedPrice == 0) {
            $orderPosition->vat_price = 0;

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

        if (! $discounts && ($data['discount_percentage'] ?? false)) {
            $discountedPrice = discount($discountedPrice, $data['discount_percentage']);
        }

        $discountedNetPrice = $orderPosition->is_net
            ? $discountedPrice
            : gross_to_net($discountedPrice, $orderPosition->vat_rate_percentage);

        $discountedGrossPrice = $orderPosition->is_net
            ? net_to_gross($discountedPrice, $orderPosition->vat_rate_percentage)
            : $discountedPrice;

        $totalDiscountPercentage = diff_percentage($preDiscountedPrice, $discountedPrice);
        $margin = bcsub($discountedNetPrice, bcmul($orderPosition->purchase_price, $orderPosition->amount));

        $orderPosition->margin = $margin;
        $orderPosition->discount_percentage = $totalDiscountPercentage == 0
            ? null
            : $totalDiscountPercentage;

        $orderPosition->total_net_price = $discountedNetPrice;
        $orderPosition->total_gross_price = $discountedGrossPrice;

        if ($orderPosition->vat_rate_percentage) {
            $orderPosition->vat_price = bcsub($orderPosition->total_gross_price, $orderPosition->total_net_price);
        }
    }
}

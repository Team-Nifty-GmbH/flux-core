<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Helpers\PriceHelper;
use FluxErp\Livewire\Forms\OrderPositionForm;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\Model;

class PriceCalculation
{
    public static function fill(Model|OrderPositionForm $orderPosition, array $data): void
    {
        // Return if no price could be calculated
        $price = $data['unit_price'] ?? $orderPosition->unit_price ?? null;

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

        $product = $orderPosition instanceof Model
            ? $orderPosition->product
            : resolve_static(Product::class, 'query')
                ->whereKey($orderPosition->product_id)
                ->first();

        $order = $orderPosition instanceof Model
            ? $orderPosition->order
            : resolve_static(Order::class, 'query')
                ->whereKey($orderPosition->order_id)
                ->first();

        $orderPosition->vat_rate_percentage = data_get($data, 'vat_rate_percentage')
            ?? resolve_static(VatRate::class, 'query')
                ->whereKey($orderPosition->vat_rate_id)
                ->value('rate_percentage')
            ?? $product->vatRate->rate_percentage;

        if (is_null($price) && $product) {
            $priceHelper = PriceHelper::make($product);

            if ($contactId = data_get($data, 'contact_id')) {
                $priceHelper->setContact(resolve_static(Contact::class, 'query')->whereKey($contactId)->first());
            }

            if ($priceListId = data_get(
                $data,
                'price_list_id',
                $orderPosition->price_list_id ?? $order->price_list_id
            )) {
                $priceHelper->setPriceList(resolve_static(PriceList::class, 'query')->whereKey($priceListId)->first());
            }

            $productPrice = $priceHelper->price();

            $price = $orderPosition->is_net
                ? $productPrice?->getNet($orderPosition->vat_rate_percentage)
                : $productPrice?->getGross($orderPosition->vat_rate_percentage);
        }

        if (is_null($price)) {
            return;
        }

        $orderPosition->unit_price = $price;

        // Collect & set missing data
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
            $stockPosting = resolve_static(StockPosting::class, 'query')
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

        $multiplier = $order->orderType->order_type_enum->multiplier();
        $orderPosition->margin = bcmul($margin, $multiplier);
        $orderPosition->discount_percentage = $totalDiscountPercentage == 0
            ? null
            : $totalDiscountPercentage;

        $orderPosition->total_net_price = bcmul($discountedNetPrice, $multiplier);
        $orderPosition->total_gross_price = bcmul($discountedGrossPrice, $multiplier);

        if (! is_null($orderPosition->vat_rate_percentage)) {
            $orderPosition->vat_price = bcsub($orderPosition->total_gross_price, $orderPosition->total_net_price);
        }

        if ($multiplier !== 1) {
            $orderPosition->total_base_gross_price = bcmul($orderPosition->total_base_gross_price, $multiplier);
            $orderPosition->total_base_net_price = bcmul($orderPosition->total_base_net_price, $multiplier);
        }
    }
}

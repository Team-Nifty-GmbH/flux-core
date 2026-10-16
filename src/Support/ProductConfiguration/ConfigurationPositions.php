<?php

namespace FluxErp\Support\ProductConfiguration;

use FluxErp\Actions\OrderPosition\PriceCalculation;
use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Contact;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Makeable;
use Illuminate\Support\Collection;

class ConfigurationPositions
{
    use Makeable;

    protected bool $isNet;

    public function __construct(
        protected PriceList $priceList,
        protected ?Contact $contact = null,
        ?bool $isNet = null
    ) {
        $this->isNet = $isNet ?? (bool) $priceList->is_net;
    }

    public static function forOrderPosition(OrderPosition $parent): static
    {
        $parent->loadMissing('order:id,contact_id,price_list_id');

        return static::make(
            resolve_static(PriceList::class, 'query')
                ->whereKey($parent->price_list_id ?? $parent->order->price_list_id)
                ->first()
                ?? resolve_static(PriceList::class, 'default'),
            resolve_static(Contact::class, 'query')
                ->whereKey($parent->order->contact_id)
                ->first(),
            (bool) $parent->is_net
        );
    }

    public function itemUnitPrice(ProductConfigurationGroupItem $item, ?int $vatRateId = null): string
    {
        $vatRateId ??= $item->product->vat_rate_id;

        if ($item->productConfigurationGroup->price_mode_enum === ConfigurationPriceModeEnum::ProductPrice) {
            return $this->productUnitPrice($item->product, $vatRateId);
        }

        $surcharge = (string) ($item->surcharge ?? 0);

        return $this->isNet ? $surcharge : net_to_gross($surcharge, $this->vatRatePercentage($vatRateId));
    }

    public function makeChildren(OrderPosition $parent, Collection $selection): Collection
    {
        $parent->loadMissing('order:id,language_id,vat_rate_id');

        return $selection
            ->map(function (array $entry) use ($parent): OrderPosition {
                $item = $entry['item'];
                $product = $item->product;

                if ($parent->order->language_id) {
                    $product->localize($parent->order->language_id);
                }

                $vatRateId = $parent->order->vat_rate_id ?? $product->vat_rate_id;
                $data = [
                    'order_id' => $parent->order_id,
                    'parent_id' => $parent->getKey(),
                    'product_id' => $product->getKey(),
                    'product_configuration_group_item_id' => $item->getKey(),
                    'tenant_id' => $parent->tenant_id,
                    'price_list_id' => $parent->price_list_id,
                    'warehouse_id' => $parent->warehouse_id,
                    'vat_rate_id' => $vatRateId,
                    'amount_bundle' => $entry['amount'],
                    'amount' => bcmul($entry['amount'], $parent->amount),
                    'name' => $product->name,
                    'description' => $product->description,
                    'product_number' => $product->product_number,
                    'ean_code' => $product->ean,
                    'unit_gram_weight' => $product->weight_gram,
                    'is_net' => $parent->is_net,
                    'is_free_text' => false,
                    'unit_price' => $this->itemUnitPrice($item, $vatRateId),
                ];

                $child = app(OrderPosition::class, ['attributes' => $data]);
                PriceCalculation::make($child, $data)->calculate();
                unset($child->discounts, $child->unit_price);

                return $child;
            })
            ->values();
    }

    public function productUnitPrice(Product $product, ?int $vatRateId = null): string
    {
        $priceHelper = PriceHelper::make($product)->setPriceList($this->priceList);

        if ($this->contact) {
            $priceHelper->setContact($this->contact);
        }

        $price = $priceHelper->price();

        if (! $price) {
            return '0';
        }

        $vatRatePercentage = $this->vatRatePercentage($vatRateId ?? $product->vat_rate_id);

        return (string) ($this->isNet ? $price->getNet($vatRatePercentage) : $price->getGross($vatRatePercentage));
    }

    protected function vatRatePercentage(?int $vatRateId): ?string
    {
        return resolve_static(VatRate::class, 'query')
            ->whereKey($vatRateId)
            ->value('rate_percentage');
    }
}

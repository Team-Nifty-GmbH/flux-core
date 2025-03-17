<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Price\UpdatePrice;
use FluxErp\Enums\RoundingMethodEnum;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Discount;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ProductPricesUpdateRuleset;
use Illuminate\Database\Eloquent\Collection;

class ProductPricesUpdate extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return ProductPricesUpdateRuleset::class;
    }

    public function performAction(): Collection
    {
        $products = resolve_static(Product::class, 'query')
            ->whereIntegerInRaw('id', $this->getData('products'))
            ->get();
        $priceList = resolve_static(PriceList::class, 'query')
            ->whereKey($this->getData('price_list_id'))
            ->first();
        $basePriceList = resolve_static(PriceList::class, 'query')
            ->whereKey($this->getData('base_price_list_id'))
            ->first();
        $discount = app(Discount::class, ['attributes' => [
            'is_percentage' => $this->getData('is_percent'),
            'discount' => bcmul(
                $this->getData('is_percent')
                    ? bcdiv($this->getData('alteration'), 100)
                    : $this->getData('alteration'),
                -1
            ),
        ]]);

        foreach ($products as $product) {
            $price = PriceHelper::make($product)
                ->addDiscount($discount)
                ->setPriceList($basePriceList ?? $priceList)
                ->price();
            $priceId = $price?->getKey();

            if ($basePriceList) {
                $priceId = $product->prices()
                    ->where('price_list_id', $priceList->getKey())
                    ->value('id');
            }

            if (! $priceId) {
                continue;
            }

            if ($this->getData('rounding_method_enum')) {
                $price->price = RoundingMethodEnum::from($this->getData('rounding_method_enum'))
                    ->apply(
                        $price->price,
                        $this->getData('rounding_precision'),
                        $this->getData('rounding_number'),
                        $this->getData('rounding_mode')
                    );
            }

            UpdatePrice::make([
                'id' => $priceId,
                'product_id' => $product->id,
                'price_list_id' => $priceList->id,
                'price' => $price->price,
            ])
                ->validate()
                ->execute();
        }

        return $products->fresh();
    }
}

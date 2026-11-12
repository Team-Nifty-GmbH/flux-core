<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\CalculateProductConfigurationPriceRuleset;
use FluxErp\Support\ProductConfiguration\ConfigurationPositions;
use FluxErp\Support\ProductConfiguration\ConfigurationValidator;
use Illuminate\Validation\ValidationException;

class CalculateProductConfigurationPrice extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return CalculateProductConfigurationPriceRuleset::class;
    }

    public function performAction(): array
    {
        $product = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('product_id'))
            ->first();
        $priceList = resolve_static(PriceList::class, 'query')
            ->whereKey($this->getData('price_list_id'))
            ->first()
            ?? resolve_static(PriceList::class, 'default');
        $pricing = ConfigurationPositions::make(
            $priceList,
            resolve_static(Contact::class, 'query')
                ->whereKey($this->getData('contact_id'))
                ->first()
        );

        $unitPrice = $pricing->productUnitPrice($product);
        $positions = ConfigurationValidator::make($product)
            ->selection($this->getData('configuration'))
            ->map(fn (array $entry) => [
                'product_configuration_group_item_id' => $entry['item']->getKey(),
                'amount' => $entry['amount'],
                'unit_price' => $pricing->itemUnitPrice($entry['item']),
            ])
            ->all();

        $piecePrice = array_reduce(
            $positions,
            fn (string $carry, array $position) => bcadd($carry, bcmul($position['unit_price'], $position['amount'])),
            $unitPrice
        );

        if (bccomp($piecePrice, 0) === -1) {
            throw ValidationException::withMessages([
                'configuration' => [__('The configuration total must not be negative.')],
            ])
                ->errorBag('calculateProductConfigurationPrice');
        }

        return [
            'is_net' => (bool) $priceList->is_net,
            'unit_price' => $unitPrice,
            'positions' => $positions,
            'total_price' => bcmul($piecePrice, $this->getData('amount') ?? 1),
            'configuration' => $this->getData('configuration'),
        ];
    }

    protected function validateData(): void
    {
        parent::validateData();

        $product = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('product_id'))
            ->first();

        $this->data['configuration'] = ConfigurationValidator::make($product)
            ->validate($this->getData('configuration') ?? $product->defaultConfiguration());
    }
}

<?php

use FluxErp\Actions\Product\CalculateProductConfigurationPrice;
use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use Illuminate\Validation\ValidationException;

require_once __DIR__ . '/Helpers.php';

beforeEach(function (): void {
    $this->vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $this->netList = PriceList::factory()->create(['is_net' => true]);
    $this->grossList = PriceList::factory()->create(['is_net' => false]);

    $this->server = configurationProduct(['is_configurable' => true, 'vat_rate_id' => $this->vatRate->getKey()]);
    configurationPrice($this->server, $this->netList, '100');
    configurationPrice($this->server, $this->grossList, '119');

    $this->storage = configurationGroup(['name' => 'Storage']);
    $this->ssdProduct = configurationProduct(['name' => 'SSD', 'vat_rate_id' => $this->vatRate->getKey()]);
    configurationPrice($this->ssdProduct, $this->netList, '999');
    $this->ssd = configurationItem($this->storage, $this->ssdProduct, ['surcharge' => 10]);

    $this->extras = configurationGroup([
        'name' => 'Extras',
        'selection_type_enum' => ConfigurationSelectionTypeEnum::Multiple,
        'price_mode_enum' => ConfigurationPriceModeEnum::ProductPrice,
    ]);
    $this->fanProduct = configurationProduct(['name' => 'Fan', 'vat_rate_id' => $this->vatRate->getKey()]);
    configurationPrice($this->fanProduct, $this->netList, '20');
    configurationPrice($this->fanProduct, $this->grossList, '23.80');
    $this->fan = configurationItem($this->extras, $this->fanProduct, ['surcharge' => 5, 'max_amount' => 3]);

    attachConfigurationGroup($this->server, $this->storage);
    attachConfigurationGroup($this->server, $this->extras);

    $this->calculate = fn (array $data) => CalculateProductConfigurationPrice::make(array_merge([
        'product_id' => $this->server->getKey(),
    ], $data))->validate()->execute();
    $this->configuration = [
        ['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1],
        ['product_configuration_group_item_id' => $this->fan->getKey(), 'amount' => 2],
    ];
});

test('surcharge and product price modes on a net price list', function (): void {
    $result = ($this->calculate)([
        'configuration' => $this->configuration,
        'price_list_id' => $this->netList->getKey(),
        'amount' => 2,
    ]);

    expect($result['is_net'])->toBeTrue()
        ->and(bccomp($result['unit_price'], '100', 2))->toBe(0)
        ->and(bccomp($result['positions'][0]['unit_price'], '10', 2))->toBe(0)
        ->and(bccomp($result['positions'][1]['unit_price'], '20', 2))->toBe(0)
        ->and(bccomp($result['total_price'], '300', 2))->toBe(0);
});

test('a gross price list adds the child product tax to the surcharge', function (): void {
    $result = ($this->calculate)([
        'configuration' => $this->configuration,
        'price_list_id' => $this->grossList->getKey(),
    ]);

    expect($result['is_net'])->toBeFalse()
        ->and(bccomp($result['unit_price'], '119', 2))->toBe(0)
        ->and(bccomp($result['positions'][0]['unit_price'], '11.90', 2))->toBe(0)
        ->and(bccomp($result['positions'][1]['unit_price'], '23.80', 2))->toBe(0)
        ->and(bccomp($result['total_price'], '178.50', 2))->toBe(0);
});

test('contact discounts reach product prices but never the surcharge', function (): void {
    $contact = Contact::factory()->create();
    foreach ([$this->server, $this->ssdProduct, $this->fanProduct] as $product) {
        $discount = Discount::factory()->create([
            'model_type' => morph_alias(Product::class),
            'model_id' => $product->getKey(),
            'discount' => 0.1,
            'is_percentage' => true,
        ]);
        $contact->discounts()->attach($discount->getKey());
    }

    $result = ($this->calculate)([
        'configuration' => $this->configuration,
        'price_list_id' => $this->netList->getKey(),
        'contact_id' => $contact->getKey(),
    ]);

    expect(bccomp($result['unit_price'], '90', 2))->toBe(0)
        ->and(bccomp($result['positions'][0]['unit_price'], '10', 2))->toBe(0)
        ->and(bccomp($result['positions'][1]['unit_price'], '18', 2))->toBe(0);
});

test('without configuration the defaults are priced', function (): void {
    $this->server->productConfigurationDefaults()->create([
        'product_configuration_group_item_id' => $this->ssd->getKey(),
        'amount' => 1,
    ]);

    $result = ($this->calculate)(['price_list_id' => $this->netList->getKey()]);

    expect($result['configuration'])
        ->toBe([['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]])
        ->and(bccomp($result['total_price'], '110', 2))->toBe(0);
});

test('a negative surcharge lowers the total', function (): void {
    $this->ssd->update(['surcharge' => -30]);

    $result = ($this->calculate)([
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]],
        'price_list_id' => $this->netList->getKey(),
    ]);

    expect(bccomp($result['total_price'], '70', 2))->toBe(0);
});

test('a configuration whose total drops below zero is rejected', function (): void {
    $this->ssd->update(['surcharge' => -130]);

    expect(fn () => ($this->calculate)([
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]],
        'price_list_id' => $this->netList->getKey(),
    ]))->toThrow(ValidationException::class, 'The configuration total must not be negative.');
});

test('the preview only accepts configurable products and valid configurations', function (): void {
    CalculateProductConfigurationPrice::assertValidationErrors(
        ['product_id' => $this->ssdProduct->getKey()],
        'product_id'
    );

    CalculateProductConfigurationPrice::assertValidationErrors([
        'product_id' => $this->server->getKey(),
        'configuration' => [['product_configuration_group_item_id' => $this->fan->getKey(), 'amount' => 4]],
    ], 'configuration');
});

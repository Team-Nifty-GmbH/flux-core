<?php

use FluxErp\Actions\CountryRegion\CreateCountryRegion;
use FluxErp\Actions\CountryRegion\DeleteCountryRegion;
use FluxErp\Actions\CountryRegion\UpdateCountryRegion;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Currency;

beforeEach(function (): void {
    $currency = Currency::factory()->create();
    $this->country = Country::factory()->create([
        'language_id' => $this->defaultLanguage->getKey(),
        'currency_id' => $currency->getKey(),
    ]);
});

test('create country region', function (): void {
    $region = CreateCountryRegion::make([
        'country_id' => $this->country->getKey(),
        'name' => 'Bavaria',
    ])->validate()->execute();

    expect($region)->toBeInstanceOf(CountryRegion::class)
        ->name->toBe('Bavaria')
        ->country_id->toBe($this->country->getKey());
});

test('create country region requires country_id and name', function (): void {
    CreateCountryRegion::assertValidationErrors([], ['country_id', 'name']);
});

test('update country region', function (): void {
    $region = CountryRegion::factory()->create(['country_id' => $this->country->getKey()]);

    $updated = UpdateCountryRegion::make([
        'id' => $region->getKey(),
        'name' => 'Saxony',
    ])->validate()->execute();

    expect($updated->name)->toBe('Saxony');
});

test('delete country region', function (): void {
    $region = CountryRegion::factory()->create(['country_id' => $this->country->getKey()]);

    expect(DeleteCountryRegion::make(['id' => $region->getKey()])
        ->validate()->execute())->toBeTrue();
});

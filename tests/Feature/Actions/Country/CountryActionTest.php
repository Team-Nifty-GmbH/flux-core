<?php

use FluxErp\Actions\Country\CreateCountry;
use FluxErp\Actions\Country\DeleteCountry;
use FluxErp\Actions\Country\UpdateCountry;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;

test('create country', function (): void {
    $currency = Currency::factory()->create();

    $country = CreateCountry::make([
        'name' => 'Germany',
        'iso_alpha2' => 'DE',
        'iso_alpha3' => 'DEU',
        'iso_numeric' => '276',
        'language_id' => $this->defaultLanguage->getKey(),
        'currency_id' => $currency->getKey(),
        'is_eu_country' => true,
    ])->validate()->execute();

    expect($country)
        ->toBeInstanceOf(Country::class)
        ->name->toBe('Germany')
        ->iso_alpha2->toBe('DE')
        ->iso_numeric->toBe('276');
});

test('create country pads iso_numeric to 3 digits', function (): void {
    $currency = Currency::factory()->create();

    $country = CreateCountry::make([
        'name' => 'Testland',
        'iso_alpha2' => 'TL',
        'iso_numeric' => '4',
        'language_id' => $this->defaultLanguage->getKey(),
        'currency_id' => $currency->getKey(),
    ])->validate()->execute();

    expect($country->iso_numeric)->toBe('004');
});

test('create country rejects decimal iso_numeric', function (): void {
    $currency = Currency::factory()->create();

    expect(fn () => CreateCountry::make([
        'name' => 'Testland',
        'iso_alpha2' => 'TL',
        'iso_numeric' => '1.5',
        'language_id' => $this->defaultLanguage->getKey(),
        'currency_id' => $currency->getKey(),
    ])->validate())->toThrow(Illuminate\Validation\ValidationException::class);
});

test('create country requires name', function (): void {
    $currency = Currency::factory()->create();

    CreateCountry::assertValidationErrors(
        ['iso_alpha2' => 'XX', 'language_id' => $this->defaultLanguage->getKey(), 'currency_id' => $currency->getKey()],
        'name'
    );
});

test('create country requires unique iso_alpha2', function (): void {
    $currency = Currency::factory()->create();
    Country::factory()->create([
        'iso_alpha2' => 'DE',
        'language_id' => $this->defaultLanguage->getKey(),
        'currency_id' => $currency->getKey(),
    ]);

    CreateCountry::assertValidationErrors(
        ['name' => 'Duplicate', 'iso_alpha2' => 'DE', 'language_id' => $this->defaultLanguage->getKey(), 'currency_id' => $currency->getKey()],
        'iso_alpha2'
    );
});

test('update country', function (): void {
    $currency = Currency::factory()->create();
    $country = Country::factory()->create([
        'language_id' => $this->defaultLanguage->getKey(),
        'currency_id' => $currency->getKey(),
    ]);

    $updated = UpdateCountry::make([
        'id' => $country->getKey(),
        'name' => 'Updated Name',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Name');
});

test('delete country', function (): void {
    $currency = Currency::factory()->create();
    $country = Country::factory()->create([
        'language_id' => $this->defaultLanguage->getKey(),
        'currency_id' => $currency->getKey(),
    ]);

    $result = DeleteCountry::make([
        'id' => $country->getKey(),
    ])->validate()->execute();

    expect($result)->toBeTrue();
    expect(Country::query()->whereKey($country->getKey())->exists())->toBeFalse();
});

test('delete country also deletes regions', function (): void {
    $currency = Currency::factory()->create();
    $country = Country::factory()->create([
        'language_id' => $this->defaultLanguage->getKey(),
        'currency_id' => $currency->getKey(),
    ]);
    $country->regions()->create(['name' => 'Bavaria']);

    DeleteCountry::make(['id' => $country->getKey()])
        ->validate()->execute();

    expect($country->regions()->count())->toBe(0);
});

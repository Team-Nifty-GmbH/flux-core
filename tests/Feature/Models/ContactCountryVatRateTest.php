<?php

use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Actions\Country\UpdateCountry;
use FluxErp\Livewire\Settings\Countries;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\VatRate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->euRate = VatRate::factory()->create(['rate_percentage' => 0, 'is_tax_exemption' => true]);
    $this->nonEuRate = VatRate::factory()->create(['rate_percentage' => 0, 'is_tax_exemption' => true]);
    $this->manualRate = VatRate::factory()->create(['rate_percentage' => 0, 'is_tax_exemption' => true]);

    $countryDefaults = [
        'language_id' => Language::factory(),
        'currency_id' => Currency::factory(),
    ];

    $this->domestic = Country::factory()->create($countryDefaults + ['is_eu_country' => true]);
    $this->eu = Country::factory()->create(
        $countryDefaults + ['is_eu_country' => true, 'tax_exemption_id' => $this->euRate->getKey()]
    );
    $this->thirdCountry = Country::factory()->create(
        $countryDefaults + ['is_eu_country' => false, 'tax_exemption_id' => $this->nonEuRate->getKey()]
    );
});

function contactInCountry(Country $country, array $contact = []): Contact
{
    $contact = Contact::factory()->create($contact);

    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'country_id' => $country->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);

    return $contact->refresh();
}

test('eu contact with vat id gets the rate of its country', function (): void {
    $contact = contactInCountry($this->eu, ['vat_id' => 'ATU12345678']);

    expect($contact->vat_rate_id)->toBe($this->euRate->getKey());
});

test('eu contact without vat id gets no rate', function (): void {
    $contact = contactInCountry($this->eu, ['vat_id' => null]);

    expect($contact->vat_rate_id)->toBeNull();
});

test('third country contact gets the rate without vat id', function (): void {
    $contact = contactInCountry($this->thirdCountry, ['vat_id' => null]);

    expect($contact->vat_rate_id)->toBe($this->nonEuRate->getKey());
});

test('domestic contact keeps a manually set rate', function (): void {
    $contact = contactInCountry($this->domestic, ['vat_rate_id' => $this->manualRate->getKey()]);

    expect($contact->vat_rate_id)->toBe($this->manualRate->getKey());
});

test('eu contact without vat id keeps a manually set rate', function (): void {
    $contact = contactInCountry($this->eu, ['vat_id' => null, 'vat_rate_id' => $this->manualRate->getKey()]);

    expect($contact->vat_rate_id)->toBe($this->manualRate->getKey());
});

test('the country rate overrides a manually set rate', function (): void {
    $contact = contactInCountry($this->thirdCountry, ['vat_rate_id' => $this->manualRate->getKey()]);

    expect($contact->vat_rate_id)->toBe($this->nonEuRate->getKey());
});

test('moving the address to the home country clears the country rate', function (): void {
    $contact = contactInCountry($this->thirdCountry);
    expect($contact->vat_rate_id)->toBe($this->nonEuRate->getKey());

    $contact->mainAddress->update(['country_id' => $this->domestic->getKey()]);

    expect($contact->refresh()->vat_rate_id)->toBeNull();
});

test('adding and removing the vat id switches the eu rate', function (): void {
    $contact = contactInCountry($this->eu, ['vat_id' => null]);

    UpdateContact::make(['id' => $contact->getKey(), 'vat_id' => 'ATU12345678'])->validate()->execute();
    expect($contact->refresh()->vat_rate_id)->toBe($this->euRate->getKey());

    UpdateContact::make(['id' => $contact->getKey(), 'vat_id' => null])->validate()->execute();
    expect($contact->refresh()->vat_rate_id)->toBeNull();
});

test('the delivery address decides over the main address', function (): void {
    $contact = contactInCountry($this->domestic);
    expect($contact->vat_rate_id)->toBeNull();

    $delivery = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'country_id' => $this->thirdCountry->getKey(),
        'is_main_address' => false,
        'is_invoice_address' => false,
        'is_delivery_address' => true,
    ]);

    expect($contact->refresh())
        ->delivery_address_id->toBe($delivery->getKey())
        ->vat_rate_id->toBe($this->nonEuRate->getKey());
});

test('changing the rate of a country updates its contacts', function (): void {
    // CI runs a real queue, the job has to run inside the test.
    config()->set('queue.default', 'sync');

    $domesticContact = contactInCountry($this->domestic);
    $thirdCountryContact = contactInCountry($this->thirdCountry);
    $otherContact = contactInCountry($this->eu, ['vat_id' => null, 'vat_rate_id' => $this->manualRate->getKey()]);

    UpdateCountry::make([
        'id' => $this->domestic->getKey(),
        'tax_exemption_id' => $this->manualRate->getKey(),
    ])->validate()->execute();
    UpdateCountry::make(['id' => $this->thirdCountry->getKey(), 'tax_exemption_id' => null])->validate()->execute();

    expect($domesticContact->refresh()->vat_rate_id)->toBeNull()
        ->and($thirdCountryContact->refresh()->vat_rate_id)->toBeNull()
        ->and($otherContact->refresh()->vat_rate_id)->toBeNull();

    UpdateContact::make(['id' => $domesticContact->getKey(), 'vat_id' => 'DE123456789'])->validate()->execute();
    expect($domesticContact->refresh()->vat_rate_id)->toBe($this->manualRate->getKey());
});

test('a country only accepts a tax exemption rate', function (): void {
    $standardRate = VatRate::factory()->create(['rate_percentage' => 0.19, 'is_tax_exemption' => false]);

    try {
        UpdateCountry::make([
            'id' => $this->domestic->getKey(),
            'tax_exemption_id' => $standardRate->getKey(),
        ])->validate()->execute();
        $this->fail('The standard rate was accepted.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('tax_exemption_id');
    }

    expect($this->domestic->refresh()->tax_exemption_id)->toBeNull();
});

test('the rate can be set in the country form', function (): void {
    Livewire::test(Countries::class)
        ->call('edit', $this->domestic->getKey())
        ->set('country.tax_exemption_id', $this->manualRate->getKey())
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($this->domestic->refresh()->tax_exemption_id)->toBe($this->manualRate->getKey());
});

test('the sync command reports in dry run and fixes otherwise', function (): void {
    $contact = contactInCountry($this->thirdCountry);
    Contact::query()->whereKey($contact->getKey())->update(['vat_rate_id' => null]);

    $this->artisan('flux:sync-country-vat-rates', ['--dry-run' => true])
        ->expectsOutputToContain($contact->customer_number)
        ->assertSuccessful();
    expect($contact->refresh()->vat_rate_id)->toBeNull();

    $this->artisan('flux:sync-country-vat-rates')->assertSuccessful();
    expect($contact->refresh()->vat_rate_id)->toBe($this->nonEuRate->getKey());
});

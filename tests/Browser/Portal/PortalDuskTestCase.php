<?php

namespace FluxErp\Tests\Browser\Portal;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentType;
use FluxErp\Tests\DuskTestCase;

class PortalDuskTestCase extends DuskTestCase
{
    public Client $dbClient;

    protected static string $guard = 'address';

    public function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('auth.defaults.guard', 'address');
    }

    protected function baseUrl(): string
    {
        return config('flux.portal_domain') . ':' . static::getBaseServePort();
    }

    public function createLoginUser(): void
    {
        $this->dbClient = Client::factory()->create();

        $language = Language::query()->where('language_code', 'en')->first();
        $currency = Currency::factory()->create();

        $country = Country::factory()->create([
            'language_id' => $language->id,
            'currency_id' => $currency->id,
        ]);

        $paymentType = PaymentType::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
            'payment_type_id' => $paymentType->id,
        ]);

        $this->user = Address::factory()->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $contact->id,
            'language_id' => $language->id,
            'country_id' => $country->id,
            'can_login' => true,
            'login_password' => $this->password,
            'is_main_address' => true,
        ]);
    }
}

<?php

namespace FluxErp\Tests\Browser\Portal;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;

class OrdersTest extends PortalDuskTestCase
{
    use DatabaseTruncation;

    public function setUp(): void
    {
        parent::setUp();

        $this->priceLists = PriceList::factory()->count(2)->create();

        $contacts = Contact::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
        ]);

        $currencies = Currency::factory()->count(2)->create();
        Currency::query()->first()->update(['is_default' => true]);

        $this->languages = Language::factory()->count(2)->create();

        $this->orderTypes = OrderType::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $this->paymentTypes = PaymentType::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
        ]);

        $priceLists = PriceList::factory()->count(2)->create();

        $addresses = Address::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $contacts->random()->id,
        ]);

        Order::factory()->count(3)->create([
            'client_id' => $this->dbClient->id,
            'language_id' => $this->languages[0]->id,
            'order_type_id' => $this->orderTypes[0]->id,
            'payment_type_id' => $this->paymentTypes[0]->id,
            'price_list_id' => $priceLists[0]->id,
            'currency_id' => $currencies[0]->id,
            'contact_id' => $contacts->random()->id,
            'address_invoice_id' => $addresses->random()->id,
            'address_delivery_id' => $addresses->random()->id,
            'is_locked' => true,
        ]);

        $this->orders = Order::factory()->count(3)->create([
            'client_id' => $this->dbClient->id,
            'language_id' => $this->languages[0]->id,
            'order_type_id' => $this->orderTypes[0]->id,
            'payment_type_id' => $this->paymentTypes[0]->id,
            'price_list_id' => $priceLists[0]->id,
            'currency_id' => $currencies[0]->id,
            'contact_id' => $this->user->contact_id,
            'address_invoice_id' => $this->user->id,
            'address_delivery_id' => $this->user->id,
            'is_locked' => true,
        ]);
    }

    public function test_can_see_orders()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $this->openMenu();

            $browser->click('nav [href="/orders"]')
                ->waitForRoute('portal.orders')
                ->assertRouteIs('portal.orders')
                ->waitForText('My orders')
                ->waitForText('ORDER NUMBER')
                ->waitForText('ORDER TYPE -> NAME')
                ->waitForText('COMMISSION')
                ->waitForText('PAYMENT STATE')
                ->waitForText('TOTAL GROSS PRICE')
                ->assertSee('ORDER NUMBER')
                ->assertSee('ORDER TYPE -> NAME')
                ->assertSee('COMMISSION')
                ->assertSee('PAYMENT STATE')
                ->assertSee('TOTAL GROSS PRICE');

            $rows = $browser->elements('[tall-datatable] tbody [data-id]');

            $this->assertCount(3, $rows);
        });
    }

    public function test_can_see_order_details()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $this->openMenu();
            $browser
                ->click('nav [href="/orders"]')
                ->waitForRoute('portal.orders');

            $browser->waitFor('[tall-datatable] tbody [data-id]');

            $rows = $browser->elements('[tall-datatable] tbody [data-id]');

            $rows[0]->click();

            $browser->waitForRoute('portal.orders.id', ['id' => $this->orders[2]->id])
                ->assertRouteIs('portal.orders.id', ['id' => $this->orders[2]->id]);
        });
    }
}

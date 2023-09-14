<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\Comments;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class CommentsTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $addresses;

    private Collection $clients;

    private Collection $languages;

    private Collection $orderTypes;

    private Collection $paymentTypes;

    private Collection $priceLists;

    private Collection $orders;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->clients = Client::factory()->count(2)->create();

        $contacts = Contact::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
        ]);
        $this->addresses = Address::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
            'contact_id' => $contacts[0]->id,
        ]);

        $this->priceLists = PriceList::factory()->count(2)->create();

        $currencies = Currency::factory()->count(2)->create();

        $this->languages = Language::factory()->count(2)->create();

        $this->orderTypes = OrderType::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $this->paymentTypes = PaymentType::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
        ]);

        $priceLists = PriceList::factory()->count(2)->create();

        $addresses = Address::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
            'contact_id' => $contacts->random()->id,
        ]);

        $this->orders = Order::factory()->count(3)->create([
            'client_id' => $this->clients[0]->id,
            'language_id' => $this->languages[0]->id,
            'order_type_id' => $this->orderTypes[0]->id,
            'payment_type_id' => $this->paymentTypes[0]->id,
            'price_list_id' => $priceLists[0]->id,
            'currency_id' => $currencies[0]->id,
            'address_invoice_id' => $addresses->random()->id,
            'address_delivery_id' => $addresses->random()->id,
            'is_locked' => false,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Comments::class, ['orderId' => $this->orders->first()->id])
            ->assertStatus(200);
    }
}

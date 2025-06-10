<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\Comments;
use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CommentsTest extends BaseSetup
{
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $addresses = Address::factory()->count(2)->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
        ]);

        $currency = Currency::factory()->create();

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $priceList = PriceList::factory()->create();

        $this->order = Order::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $addresses->random()->id,
            'address_delivery_id' => $addresses->random()->id,
            'is_locked' => false,
        ]);

        $comments = Comment::factory()
            ->count(3)
            ->create([
                'model_type' => morph_alias(Order::class),
                'model_id' => $this->order->id,
            ]);

        $comments->first()->update(['is_sticky' => true]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::withoutLazyLoading()
            ->test(Comments::class, ['modelId' => $this->order->id])
            ->assertStatus(200)
            ->call('loadComments')
            ->assertReturned(function (array $comments): true {
                $this->assertCount(3, data_get($comments, 'data'));
                $this->assertEquals(1, data_get($comments, 'current_page'));
                $this->assertEquals(3, data_get($comments, 'total'));

                return true;
            })
            ->call('loadStickyComments')
            ->assertReturned(function (array $stickyComments): true {
                $this->assertCount(1, $stickyComments, 'data');

                return true;
            });
    }
}

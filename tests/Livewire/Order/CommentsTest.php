<?php

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
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $addresses = Address::factory()->count(2)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->id,
    ]);

    $currency = Currency::factory()->create();

    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $priceList = PriceList::factory()->create();

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
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
});

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->order->id])
        ->assertOk()
        ->call('loadComments')
        ->assertReturned(function (array $comments): true {
            expect(data_get($comments, 'data'))->toHaveCount(3);
            expect(data_get($comments, 'current_page'))->toEqual(1);
            expect(data_get($comments, 'total'))->toEqual(3);

            return true;
        })
        ->call('loadStickyComments')
        ->assertReturned(function (array $stickyComments): true {
            expect($stickyComments)->toHaveCount(1, 'data');

            return true;
        });
});

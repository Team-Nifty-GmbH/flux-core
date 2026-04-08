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
    $contact = Contact::factory()->create();

    $addresses = Address::factory()->count(2)->create([
        'contact_id' => $contact->id,
    ]);

    $currency = Currency::factory()->create();

    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
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

test('loadComments returns is_internal as boolean', function (): void {
    // Create one internal and one non-internal comment
    Comment::query()->where('model_id', $this->order->id)->delete();
    Comment::factory()->create([
        'model_type' => morph_alias(Order::class),
        'model_id' => $this->order->id,
        'is_internal' => true,
    ]);
    Comment::factory()->create([
        'model_type' => morph_alias(Order::class),
        'model_id' => $this->order->id,
        'is_internal' => false,
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->order->id])
        ->call('loadComments')
        ->assertReturned(function (array $comments): true {
            $data = data_get($comments, 'data');
            expect($data)->toHaveCount(2);

            foreach ($data as $comment) {
                // Must be actual boolean, not string "0"/"1"
                // JS treats "0" as truthy which breaks x-bind:class
                expect($comment['is_internal'])->toBeIn([true, false]);
            }

            $internal = collect($data)->where('is_internal', true);
            $public = collect($data)->where('is_internal', false);
            expect($internal)->toHaveCount(1)
                ->and($public)->toHaveCount(1);

            return true;
        });
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

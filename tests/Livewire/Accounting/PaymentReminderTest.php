<?php

namespace FluxErp\Tests\Livewire\Accounting;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\PaymentReminder;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Str;
use Livewire\Livewire;

class PaymentReminderTest extends BaseSetup
{
    public function test_mark_selected_as_paid(): void
    {
        $contact = Contact::factory()
            ->state(['client_id' => $this->dbClient->getKey()])
            ->create();
        $address = Address::factory()
            ->state([
                'client_id' => $this->dbClient->getKey(),
                'contact_id' => $contact->id,
            ])
            ->for($contact, 'contact')
            ->create();

        $orders = Order::factory()
            ->for(Currency::factory(), 'currency')
            ->for(Language::factory(), 'language')
            ->for(PriceList::factory(), 'priceList')
            ->for(PaymentType::factory()->state(['is_direct_debit' => false]), 'paymentType')
            ->for(
                OrderType::factory()
                    ->state([
                        'order_type_enum' => OrderTypeEnum::Order,
                        'is_active' => true,
                    ])
                    ->for(factory: $this->dbClient, relationship: 'client'),
                'orderType'
            )
            ->state([
                'client_id' => $this->dbClient->getKey(),
                'contact_id' => $contact->id,
                'address_invoice_id' => $address->id,
                'payment_state' => Open::$name,
                'invoice_number' => fn () => Str::uuid(),
            ])
            ->count(3)
            ->create()
            ->each(fn (Order $order) => $order->update(['balance' => faker()->randomFloat(2, 1, 100)]));

        Livewire::test(PaymentReminder::class)
            ->call('loadData')
            ->assertCount('data.data', 3)
            ->set('selected', [$orders[0]->id, $orders[1]->id])
            ->call('markAsPaid')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertCount('data.data', 1);

        $this->assertDatabaseHas('orders', ['id' => $orders[0]->id, 'payment_state' => Paid::$name]);
        $this->assertDatabaseHas('orders', ['id' => $orders[1]->id, 'payment_state' => Paid::$name]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(PaymentReminder::class)
            ->assertStatus(200);
    }
}

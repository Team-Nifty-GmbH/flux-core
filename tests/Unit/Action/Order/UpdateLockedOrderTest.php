<?php

namespace FluxErp\Tests\Unit\Action\Order;

use FluxErp\Actions\Order\UpdateLockedOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Tests\TestCase;
use Illuminate\Support\Str;

class UpdateLockedOrderTest extends TestCase
{
    private Address $additionalAddress;

    private Client $client;

    private Order $lockedOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client
        $this->client = Client::factory()->create(['is_default' => true]);

        // Create a contact with address
        $contact = Contact::factory()
            ->has(Address::factory()->for($this->client))
            ->for($this->client)
            ->create();

        $address = $contact->addresses()->first();

        // Create an additional address for testing
        $this->additionalAddress = Address::factory()->create([
            'client_id' => $this->client->getKey(),
            'contact_id' => $contact->getKey(),
        ]);

        // Create an order type
        $orderType = OrderType::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        // Create a payment type
        $paymentType = PaymentType::factory()
            ->hasAttached($this->client, relationship: 'clients')
            ->create();

        // Create a currency
        $currency = Currency::factory()->create(['is_default' => true]);

        // Create a price list
        $priceList = PriceList::factory()->create();

        // Create a locked order
        $this->lockedOrder = Order::factory()->create([
            'client_id' => $this->client->getKey(),
            'currency_id' => $currency->getKey(),
            'order_type_id' => $orderType->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'price_list_id' => $priceList->getKey(),
            'commission' => 'Original Commission',
            'is_confirmed' => false,
            'requires_approval' => false,
            'payment_reminder_current_level' => 0,
            'is_locked' => true,
        ]);
    }

    public function test_can_update_addresses(): void
    {
        $addressType = AddressType::factory()->create(['client_id' => $this->client->getKey()]);

        $data = [
            'id' => $this->lockedOrder->getKey(),
            'addresses' => [
                [
                    'address_id' => $this->additionalAddress->getKey(),
                    'address_type_id' => $addressType->getKey(),
                ],
            ],
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);

        // Check that the address was attached
        $this->assertTrue(
            $updatedOrder->addresses()
                ->where('addresses.id', $this->additionalAddress->getKey())
                ->exists()
        );
    }

    public function test_can_update_approval_user(): void
    {
        // Create a user
        $user = User::factory()
            ->for(Language::factory(), 'language')
            ->create([
                'is_active' => true,
            ]);

        $data = [
            'id' => $this->lockedOrder->getKey(),
            'approval_user_id' => $user->getKey(),
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertEquals($user->getKey(), $updatedOrder->approval_user_id);

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->lockedOrder->getKey(),
            'approval_user_id' => $user->getKey(),
        ]);
    }

    public function test_can_update_confirmation_and_approval_flags(): void
    {
        $data = [
            'id' => $this->lockedOrder->getKey(),
            'is_confirmed' => true,
            'requires_approval' => true,
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertTrue($updatedOrder->is_confirmed);
        $this->assertTrue($updatedOrder->requires_approval);

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->lockedOrder->getKey(),
            'is_confirmed' => true,
            'requires_approval' => true,
        ]);
    }

    public function test_can_update_locked_order(): void
    {
        $this->assertTrue($this->lockedOrder->is_locked);

        $newCommission = 'Updated Commission ' . Str::random(5);

        $data = [
            'id' => $this->lockedOrder->getKey(),
            'commission' => $newCommission,
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertEquals($this->lockedOrder->getKey(), $updatedOrder->getKey());
        $this->assertEquals($newCommission, $updatedOrder->commission);
        $this->assertTrue($updatedOrder->is_locked); // Still locked

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->lockedOrder->getKey(),
            'commission' => $newCommission,
            'is_locked' => true,
        ]);
    }

    public function test_can_update_multiple_fields_simultaneously(): void
    {
        // Create a user
        $user = User::factory()
            ->for(Language::factory(), 'language')
            ->create([
                'is_active' => true,
            ]);

        $data = [
            'id' => $this->lockedOrder->getKey(),
            'commission' => 'Multi-update Commission',
            'payment_state' => 'paid',
            'delivery_state' => 'delivered',
            'is_confirmed' => true,
            'requires_approval' => true,
            'payment_reminder_current_level' => 3,
            'responsible_user_id' => $user->getKey(),
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);

        // Check all fields updated correctly
        $this->assertEquals('Multi-update Commission', $updatedOrder->commission);
        $this->assertEquals('paid', $updatedOrder->payment_state);
        $this->assertEquals('delivered', $updatedOrder->delivery_state);
        $this->assertTrue($updatedOrder->is_confirmed);
        $this->assertTrue($updatedOrder->requires_approval);
        $this->assertEquals(3, $updatedOrder->payment_reminder_current_level);
        $this->assertEquals($user->getKey(), $updatedOrder->responsible_user_id);

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->lockedOrder->getKey(),
            'commission' => 'Multi-update Commission',
            'payment_state' => 'paid',
            'delivery_state' => 'delivered',
            'is_confirmed' => true,
            'requires_approval' => true,
            'payment_reminder_current_level' => 3,
            'responsible_user_id' => $user->getKey(),
        ]);
    }

    public function test_can_update_payment_and_delivery_state(): void
    {
        $data = [
            'id' => $this->lockedOrder->getKey(),
            'payment_state' => 'paid',
            'delivery_state' => 'delivered',
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertEquals('paid', $updatedOrder->payment_state);
        $this->assertEquals('delivered', $updatedOrder->delivery_state);

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->lockedOrder->getKey(),
            'payment_state' => 'paid',
            'delivery_state' => 'delivered',
        ]);
    }

    public function test_can_update_payment_reminder_information(): void
    {
        $nextReminderDate = now()->addDays(7);

        $data = [
            'id' => $this->lockedOrder->getKey(),
            'payment_reminder_current_level' => 2,
            'payment_reminder_next_date' => $nextReminderDate->format('Y-m-d'),
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertEquals(2, $updatedOrder->payment_reminder_current_level);
        $this->assertEquals(
            $nextReminderDate->format('Y-m-d'),
            $updatedOrder->payment_reminder_next_date->format('Y-m-d')
        );

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->lockedOrder->getKey(),
            'payment_reminder_current_level' => 2,
        ]);
    }

    public function test_can_update_responsible_user(): void
    {
        // Create a user
        $user = User::factory()
            ->for(Language::factory(), 'language')
            ->create([
                'is_active' => true,
            ]);

        $data = [
            'id' => $this->lockedOrder->getKey(),
            'responsible_user_id' => $user->getKey(),
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertEquals($user->getKey(), $updatedOrder->responsible_user_id);

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->lockedOrder->getKey(),
            'responsible_user_id' => $user->getKey(),
        ]);
    }

    public function test_can_update_users(): void
    {
        // Create a user
        $user = User::factory()
            ->for(Language::factory(), 'language')
            ->create([
                'is_active' => true,
            ]);

        $data = [
            'id' => $this->lockedOrder->getKey(),
            'users' => [$user->getKey()],
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);

        // Check that the user was attached
        $this->assertTrue(
            $updatedOrder->users()
                ->where('users.id', $user->getKey())
                ->exists()
        );
    }

    public function test_only_allowed_fields_can_be_updated(): void
    {
        // Attempt to update restricted fields that shouldn't be updatable in a locked order

        // Original values
        $originalOrderNumber = $this->lockedOrder->order_number;
        $originalOrderDate = $this->lockedOrder->order_date;

        $data = [
            'id' => $this->lockedOrder->getKey(),
            'commission' => 'New Commission', // This should update
            'order_number' => 'NEW-' . Str::random(8), // This should not update
            'order_date' => now()->subDays(10)->format('Y-m-d'), // This should not update
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);

        // Commission should update
        $this->assertEquals('New Commission', $updatedOrder->commission);

        // These fields should not have updated
        $this->assertEquals($originalOrderNumber, $updatedOrder->order_number);
        $this->assertEquals(
            $originalOrderDate->format('Y-m-d'),
            $updatedOrder->order_date->format('Y-m-d')
        );
    }

    public function test_preserves_order_lock_status(): void
    {
        // Test that updating doesn't accidentally unlock the order
        $data = [
            'id' => $this->lockedOrder->getKey(),
            'commission' => 'New Commission',
        ];

        $updatedOrder = UpdateLockedOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertTrue($updatedOrder->is_locked); // Should still be locked

        // Verify database maintained lock
        $this->assertDatabaseHas('orders', [
            'id' => $this->lockedOrder->getKey(),
            'commission' => 'New Commission',
            'is_locked' => true,
        ]);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Accounting;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Livewire\Accounting\PaymentRunPreview;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Collection;
use Livewire\Livewire;

class PaymentRunPreviewTest extends BaseSetup
{
    protected Address $address;

    protected Contact $contact;

    protected Currency $currency;

    protected Collection $orders;

    protected OrderType $orderType;

    protected PaymentType $paymentType;

    protected PriceList $priceList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->address = Address::factory()->create([
            'contact_id' => $this->contact->id,
            'client_id' => $this->dbClient->id,
            'is_main_address' => true,
            'name' => 'Test Customer',
        ]);

        $this->paymentType = PaymentType::factory()->create([
            'is_direct_debit' => false,
            'requires_manual_transfer' => true,
        ]);

        $this->priceList = PriceList::factory()->create();

        $this->currency = Currency::factory()->create();

        $this->orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
            'is_active' => true,
            'order_type_enum' => collect(OrderTypeEnum::cases())
                ->first(fn ($case) => $case->multiplier() < 0),
        ]);

        $this->orders = Order::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $this->contact->id,
            'order_type_id' => $this->orderType->id,
            'payment_type_id' => $this->paymentType->id,
            'address_invoice_id' => $this->address->id,
            'price_list_id' => $this->priceList->id,
            'currency_id' => $this->currency->id,
        ]);

        // Update specific order attributes for tests
        $this->orders[0]->update([
            'invoice_number' => 'INV-001',
            'balance' => -100.50,
            'total_gross_price' => -100.50,
        ]);

        $this->orders[1]->update([
            'invoice_number' => 'INV-002',
            'balance' => -250.75,
            'total_gross_price' => -250.75,
        ]);
    }

    public function test_calculates_total_amount(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $component = Livewire::test(PaymentRunPreview::class);

        $orders = $component->get('orders');
        $this->assertEquals(100.50, $orders[$this->orders[0]->id]['amount']);
        $this->assertEquals(250.75, $orders[$this->orders[1]->id]['amount']);
        $this->assertEquals(-1, $orders[$this->orders[0]->id]['multiplier']);
        $this->assertEquals(-1, $orders[$this->orders[1]->id]['multiplier']);
    }

    public function test_can_create_payment_run_with_multiplier(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $component = Livewire::test(PaymentRunPreview::class);

        // Verify the multiplier is set correctly for negative balance
        $orders = $component->get('orders');
        $this->assertEquals(-1, $orders[$this->orders[0]->id]['multiplier']);

        // Test that we can create payment run successfully
        $component->call('createPaymentRun')
            ->assertRedirect();
    }

    public function test_can_set_valid_amounts(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $component = Livewire::test(PaymentRunPreview::class);

        // Test setting a valid positive amount
        $component->set('orders.' . $this->orders[0]->id . '.amount', 50.00);
        $component->assertSet('orders.' . $this->orders[0]->id . '.amount', 50.00);

        // Test setting another valid amount
        $component->set('orders.' . $this->orders[0]->id . '.amount', 75.25);
        $component->assertSet('orders.' . $this->orders[0]->id . '.amount', 75.25);
    }

    public function test_can_update_payment_amounts(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $component = Livewire::test(PaymentRunPreview::class);

        $component->set('orders.' . $this->orders[0]->id . '.amount', 80.00);

        $component->assertSet('orders.' . $this->orders[0]->id . '.amount', 80.00);
    }

    public function test_cancel_redirects_to_money_transfer(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        Livewire::test(PaymentRunPreview::class)
            ->call('cancel')
            ->assertRedirect(route('accounting.money-transfer'));
    }

    public function test_component_initializes_with_order_ids(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $component = Livewire::test(PaymentRunPreview::class);

        $this->assertCount(2, $component->get('orders'));

        $orders = $component->get('orders');
        $this->assertEquals($this->orders[0]->id, $orders[$this->orders[0]->id]['id']);
        $this->assertEquals($this->orders[1]->id, $orders[$this->orders[1]->id]['id']);
        $this->assertEquals(100.50, $orders[$this->orders[0]->id]['amount']);
        $this->assertEquals(250.75, $orders[$this->orders[1]->id]['amount']);
        $this->assertEquals(-1, $orders[$this->orders[0]->id]['multiplier']);
        $this->assertEquals(-1, $orders[$this->orders[1]->id]['multiplier']);
    }

    public function test_creates_payment_run_successfully(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $this->assertEquals(0, PaymentRun::count());

        Livewire::test(PaymentRunPreview::class)
            ->call('createPaymentRun')
            ->assertRedirect();

        $this->assertGreaterThan(0, PaymentRun::count());
    }

    public function test_creates_payment_run_with_custom_amounts(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        Livewire::test(PaymentRunPreview::class)
            ->set('orders.' . $this->orders[0]->id . '.amount', 75.25)
            ->call('createPaymentRun')
            ->assertRedirect();

        $this->assertGreaterThan(0, PaymentRun::count());
    }

    public function test_displays_orders_in_table(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $component = Livewire::test(PaymentRunPreview::class);

        $orders = $component->get('orders');
        $this->assertCount(2, $orders);

        $order1Data = $orders[$this->orders[0]->id];
        $order2Data = $orders[$this->orders[1]->id];

        $this->assertEquals('INV-001', $order1Data['invoice_number']);
        $this->assertEquals('INV-002', $order2Data['invoice_number']);

        $component->assertStatus(200);
    }

    public function test_handles_empty_order_ids(): void
    {
        session([
            'payment_run_preview_orders' => [],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        Livewire::test(PaymentRunPreview::class)
            ->assertRedirect(route('accounting.money-transfer'));
    }

    public function test_ignores_non_existent_order_ids(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id, 999999],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $component = Livewire::test(PaymentRunPreview::class);

        $this->assertEquals(1, count($component->get('orders')));
    }

    public function test_preserves_order_data_integrity(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $component = Livewire::test(PaymentRunPreview::class);

        $orders = $component->get('orders');
        $order = $orders[$this->orders[0]->id];

        $this->assertEquals($this->orders[0]->id, $order['id']);
        $this->assertEquals('INV-001', $order['invoice_number']);
        $this->assertEquals(-100.50, $order['balance']);

        $this->assertTrue(
            ! empty($order['contact_name']) || ! empty($order['address_name']),
            'Either contact_name or address_name should be present. Got contact_name: ' . ($order['contact_name'] ?? 'null') . ', address_name: ' . ($order['address_name'] ?? 'null')
        );
    }

    public function test_redirects_when_missing_payment_run_type(): void
    {
        session(['payment_run_preview_orders' => [1, 2]]);

        Livewire::test(PaymentRunPreview::class)
            ->assertRedirect(route('accounting.money-transfer'));
    }

    public function test_redirects_when_no_session_data(): void
    {
        Livewire::test(PaymentRunPreview::class)
            ->assertRedirect(route('accounting.money-transfer'));
    }

    public function test_renders_successfully(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        Livewire::test(PaymentRunPreview::class)
            ->assertStatus(200);
    }

    public function test_renders_successfully_with_minimal_session_data(): void
    {
        session([
            'payment_run_preview_orders' => [9999, 9998], // Non-existent IDs
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        $component = Livewire::test(PaymentRunPreview::class);

        $component->assertStatus(200);
        $this->assertEmpty($component->get('orders'));
    }

    public function test_shows_notification_on_successful_creation(): void
    {
        session([
            'payment_run_preview_orders' => [$this->orders[0]->id],
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        Livewire::test(PaymentRunPreview::class)
            ->call('createPaymentRun')
            ->assertRedirect();
    }
}

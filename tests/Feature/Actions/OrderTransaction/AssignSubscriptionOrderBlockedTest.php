<?php

use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use Illuminate\Validation\ValidationException;

test('transactions cannot be assigned to subscription orders', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::PurchaseSubscription, 'is_active' => true]);
    $paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
    $transaction = Transaction::factory()->create();

    try {
        CreateOrderTransaction::make([
            'transaction_id' => $transaction->getKey(),
            'order_id' => $order->getKey(),
            'amount' => 100,
        ])
            ->validate()
            ->execute();

        $this->fail('Expected the subscription order assignment to be rejected.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('order_id')
            ->and($e->errors()['order_id'])
            ->toContain('Transactions cannot be assigned to subscription orders.');
    }
});

<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Account;
use FluxErp\Models\Address;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TransactionTest extends BaseSetup
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $contact->id,
        ]);

        $priceList = PriceList::factory()->create();

        $currency = Currency::factory()->create([
            'is_default' => true,
        ]);

        $currencies = Currency::factory(5)->create();

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $bankConnections = BankConnection::factory(3)->create();

        $accounts = Account::factory(3)->create([
            'bank_connection_id' => $bankConnections->random()->id,
            'currency_id' => $currencies->random()->id,
        ]);

        $orders = Order::factory(10)->create([
            'client_id' => $this->dbClient->id,
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $address->id,
            'address_delivery_id' => $address->id,
            'is_locked' => false,
        ]);

        Transaction::factory(50)->create([
            'account_id' => $accounts->random()->id,
            'currency_id' => $currencies->random()->id,
            'order_id' => $orders->random()->id,
        ]);
    }

    public function test_transactions_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('accounting.transactions.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/accounting/transactions')
            ->assertStatus(200);
    }

    public function test_transactions_no_user()
    {
        $this->get('/accounting/transactions')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_transactions_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/accounting/transactions')
            ->assertStatus(403);
    }
}

<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Permission;
use FluxErp\Models\Transaction;

class TransactionTest extends BaseSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $currencies = Currency::factory(5)->create();

        $bankConnections = BankConnection::factory()->count(3)->create([
            'currency_id' => $currencies->random()->id,
        ]);

        Transaction::factory()->count(3)->create([
            'bank_connection_id' => $bankConnections->random()->id,
            'currency_id' => $currencies->random()->id,
        ]);
    }

    public function test_transactions_no_user(): void
    {
        $this->get('/accounting/transactions')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_transactions_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('accounting.transactions.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/accounting/transactions')
            ->assertStatus(200);
    }

    public function test_transactions_without_permission(): void
    {
        Permission::findOrCreate('accounting.transactions.get', 'web');

        $this->actingAs($this->user, 'web')->get('/accounting/transactions')
            ->assertStatus(403);
    }
}

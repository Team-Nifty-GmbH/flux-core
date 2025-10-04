<?php

use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Permission;
use FluxErp\Models\Transaction;

beforeEach(function (): void {
    $currencies = Currency::factory(5)->create();

    $bankConnections = BankConnection::factory()->count(3)->create([
        'currency_id' => $currencies->random()->id,
    ]);

    Transaction::factory()->count(3)->create([
        'bank_connection_id' => $bankConnections->random()->id,
        'currency_id' => $currencies->random()->id,
    ]);
});

test('transactions no user', function (): void {
    $this->actingAsGuest();

    $this->get('/accounting/transactions')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('transactions page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('accounting.transactions.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/accounting/transactions')
        ->assertOk();
});

test('transactions without permission', function (): void {
    Permission::findOrCreate('accounting.transactions.get', 'web');

    $this->actingAs($this->user, 'web')->get('/accounting/transactions')
        ->assertForbidden();
});

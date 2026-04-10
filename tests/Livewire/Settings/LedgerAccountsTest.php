<?php

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Livewire\Settings\LedgerAccounts;
use FluxErp\Models\LedgerAccount;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LedgerAccounts::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(LedgerAccounts::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('ledgerAccount.id', null)
        ->assertSet('ledgerAccount.name', null)
        ->assertSet('ledgerAccount.number', null)
        ->assertSet('ledgerAccount.ledger_account_type_enum', null)
        ->assertOpensModal('edit-ledger-account-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $ledgerAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    Livewire::test(LedgerAccounts::class)
        ->call('edit', $ledgerAccount->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('ledgerAccount.id', $ledgerAccount->getKey())
        ->assertSet('ledgerAccount.name', $ledgerAccount->name)
        ->assertSet('ledgerAccount.number', $ledgerAccount->number)
        ->assertOpensModal('edit-ledger-account-modal');
});

test('can create ledger account', function (): void {
    Livewire::test(LedgerAccounts::class)
        ->assertOk()
        ->call('edit')
        ->set('ledgerAccount.name', $name = Str::uuid()->toString())
        ->set('ledgerAccount.number', '4711')
        ->set('ledgerAccount.ledger_account_type_enum', LedgerAccountTypeEnum::Revenue->value)
        ->set('ledgerAccount.tenant_id', $this->dbTenant->getKey())
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ledger_accounts', [
        'name' => $name,
        'number' => '4711',
    ]);
});

test('can update ledger account', function (): void {
    $ledgerAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    Livewire::test(LedgerAccounts::class)
        ->assertOk()
        ->call('edit', $ledgerAccount->getKey())
        ->assertSet('ledgerAccount.id', $ledgerAccount->getKey())
        ->set('ledgerAccount.name', 'Updated LedgerAccount Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($ledgerAccount->refresh()->name)->toEqual('Updated LedgerAccount Name');
});

test('can delete ledger account', function (): void {
    $ledgerAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    Livewire::test(LedgerAccounts::class)
        ->assertOk()
        ->call('delete', $ledgerAccount->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('ledger_accounts', [
        'id' => $ledgerAccount->getKey(),
    ]);
});

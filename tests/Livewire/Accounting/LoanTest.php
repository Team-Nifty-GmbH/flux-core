<?php

use FluxErp\Livewire\Accounting\Loan;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan as LoanModel;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

beforeEach(function (): void {
    // the amounts are asserted with two decimals, so the currency must not be a
    // random one from the factory
    Currency::query()->update(['is_default' => false]);
    ($euro = Currency::query()->firstWhere('iso', 'EUR'))
        ? $euro->update(['is_default' => true])
        : Currency::factory()->create(['iso' => 'EUR', 'name' => 'Euro', 'is_default' => true]);

    $this->contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->ledgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $this->loan = LoanModel::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'name' => 'Machine financing',
        'amount' => 12000,
        'interest_rate' => 0.02299,
        'number_of_installments' => 2,
    ]);
    $this->loan->installments()->create([
        'sequence' => 1,
        'due_date' => '2026-02-01',
        'principal_amount' => 6000,
        'interest_amount' => 60,
    ]);
    $this->loan->installments()->create([
        'sequence' => 2,
        'due_date' => '2026-03-01',
        'principal_amount' => 6000,
        'interest_amount' => 30,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->assertOk()
        ->assertSet('loan.id', $this->loan->getKey())
        ->assertCount('installments', 2)
        // stored as a factor, shown as a percentage
        ->assertSet('loan.interest_rate', 2.299);
});

test('every tab renders', function (string $tab): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('tab', $tab)
        ->assertOk()
        ->assertNoRedirect();
})->with(['loan.general', 'loan.installments', 'loan.documents']);

test('the schedule tab formats amounts and dates for the locale', function (): void {
    app()->setLocale('de');

    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('tab', 'loan.installments')
        ->assertOk()
        ->assertSee('01.02.2026')
        ->assertSee('6.000,00')
        ->assertSet('totals.principal_amount', fn (string $value) => str_contains($value, '12.000,00'))
        ->assertSet('totals.interest_amount', fn (string $value) => str_contains($value, '90,00'))
        ->assertSet('totals.total', fn (string $value) => str_contains($value, '12.090,00'));
});

test('the totals show how much of the loan is settled', function (): void {
    app()->setLocale('de');

    $this->loan->installments()->orderBy('sequence')->first()->update(['is_paid' => true]);

    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->assertOk()
        ->assertSet('totals.paid_principal_amount', fn (string $value) => str_contains($value, '6.000,00'))
        ->assertSet('totals.paid_interest_amount', fn (string $value) => str_contains($value, '60,00'))
        ->assertSet('totals.paid_total', fn (string $value) => str_contains($value, '6.060,00'))
        ->assertSet('totals.paid_principal_share', fn (string $value) => str_contains($value, '50'))
        ->assertSet('totals.paid_total_share', fn (string $value) => str_contains($value, '50,1'));
});

test('an unknown loan is not found', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey() + 1]);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

test('can save the loan', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('loan.name', 'Renamed loan')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('loans', [
        'id' => $this->loan->getKey(),
        'name' => 'Renamed loan',
    ]);
});

test('can attach a contract', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('contract.file', [UploadedFile::fake()->image('contract.jpg')])
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($this->loan->refresh()->getMedia('contract'))->toHaveCount(1);
});

test('can delete the loan', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->call('delete')
        ->assertOk()
        ->assertRedirect(route('accounting.loans'));

    $this->assertSoftDeleted('loans', ['id' => $this->loan->getKey()]);
});

<?php

use FluxErp\Actions\Loan\FinanceOrder;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\States\Order\PaymentState\PartialPaid;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->priceList = PriceList::factory()->create();
    $this->currency = Currency::factory()->create();

    $this->creditorAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Liability,
    ]);
    $this->loanAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Liability,
    ]);

    $this->createOrder = function (OrderTypeEnum $orderTypeEnum, float $totalGrossPrice): Order {
        $orderType = OrderType::factory()->create([
            'order_type_enum' => $orderTypeEnum,
            'is_active' => true,
        ]);

        return Order::factory()->create([
            'order_type_id' => $orderType->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'contact_id' => $this->contact->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'price_list_id' => $this->priceList->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
            'currency_id' => $this->currency->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'total_gross_price' => $totalGrossPrice,
            'balance' => $totalGrossPrice,
            'is_locked' => false,
        ]);
    };

    $this->createLoan = fn (): Loan => Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => Contact::factory()->create()->getKey(),
        'ledger_account_id' => $this->loanAccount->getKey(),
        'amount' => 10000,
        'interest_rate' => 0,
        'number_of_installments' => 10,
        'repayment_type_enum' => RepaymentTypeEnum::Linear,
        'starts_at' => '2026-09-01',
        'order_id' => null,
    ]);
});

test('financing an order books creditor against the loan account', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -10000);
    $loan = ($this->createLoan)();

    $financed = FinanceOrder::make([
        'loan_id' => $loan->getKey(),
        'order_id' => $order->getKey(),
        'debit_ledger_account_id' => $this->creditorAccount->getKey(),
        'amount' => 10000,
        'booking_date' => '2026-09-01',
        'booking_text' => 'Umbuchung Kreditor an Darlehen',
    ])
        ->validate()
        ->execute();

    $booking = LedgerBooking::query()
        ->where('source_type', $order->getMorphClass())
        ->where('source_id', $order->getKey())
        ->sole();

    expect($financed->order_id)->toBe($order->getKey())
        ->and($booking->debit_ledger_account_id)->toBe($this->creditorAccount->getKey())
        ->and($booking->credit_ledger_account_id)->toBe($this->loanAccount->getKey())
        ->and((float) $booking->amount)->toBe(10000.0)
        ->and($order->fresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('a partial amount leaves the order partially paid', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -10000);
    $loan = ($this->createLoan)();

    FinanceOrder::make([
        'loan_id' => $loan->getKey(),
        'order_id' => $order->getKey(),
        'debit_ledger_account_id' => $this->creditorAccount->getKey(),
        'amount' => 4000,
        'booking_date' => '2026-09-01',
    ])
        ->validate()
        ->execute();

    expect($order->fresh()->payment_state)->toBeInstanceOf(PartialPaid::class);
});

test('a loan finances only one order', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -10000);
    $second = ($this->createOrder)(OrderTypeEnum::Purchase, -500);
    $loan = ($this->createLoan)();

    $data = [
        'loan_id' => $loan->getKey(),
        'order_id' => $order->getKey(),
        'debit_ledger_account_id' => $this->creditorAccount->getKey(),
        'amount' => 10000,
        'booking_date' => '2026-09-01',
    ];

    FinanceOrder::make($data)->validate()->execute();

    expect(fn () => FinanceOrder::make(
        array_merge($data, ['order_id' => $second->getKey(), 'amount' => 500])
    )->validate()->execute())->toThrow(ValidationException::class);
});

test('the amount must not exceed the open amount', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -1000);
    $loan = ($this->createLoan)();

    expect(fn () => FinanceOrder::make([
        'loan_id' => $loan->getKey(),
        'order_id' => $order->getKey(),
        'debit_ledger_account_id' => $this->creditorAccount->getKey(),
        'amount' => 1000.01,
        'booking_date' => '2026-09-01',
    ])->validate()->execute())->toThrow(ValidationException::class);
});

test('a sales order cannot be financed', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Order, 1000);
    $loan = ($this->createLoan)();

    expect(fn () => FinanceOrder::make([
        'loan_id' => $loan->getKey(),
        'order_id' => $order->getKey(),
        'debit_ledger_account_id' => $this->creditorAccount->getKey(),
        'amount' => 1000,
        'booking_date' => '2026-09-01',
    ])->validate()->execute())->toThrow(ValidationException::class);
});

test('an order of a foreign tenant cannot be financed', function (): void {
    $foreignTenant = Tenant::factory()->create();
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -1000);
    $order->tenant_id = $foreignTenant->getKey();
    $order->saveQuietly();

    $loan = ($this->createLoan)();

    expect(fn () => FinanceOrder::make([
        'loan_id' => $loan->getKey(),
        'order_id' => $order->getKey(),
        'debit_ledger_account_id' => $this->creditorAccount->getKey(),
        'amount' => 1000,
        'booking_date' => '2026-09-01',
    ])->validate()->execute())->toThrow(ValidationException::class);
});

test('the debit account must differ from the loan account', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -1000);
    $loan = ($this->createLoan)();

    expect(fn () => FinanceOrder::make([
        'loan_id' => $loan->getKey(),
        'order_id' => $order->getKey(),
        'debit_ledger_account_id' => $this->loanAccount->getKey(),
        'amount' => 1000,
        'booking_date' => '2026-09-01',
    ])->validate()->execute())->toThrow(ValidationException::class);
});

<?php

test('transaction list loads', function (): void {
    visit(route('accounting.transactions'))
        ->assertNoSmoke();
});

test('transaction assignments page loads', function (): void {
    visit(route('accounting.transaction-assignments'))
        ->assertNoSmoke();
});

test('purchase invoices list loads', function (): void {
    visit(route('accounting.purchase-invoices'))
        ->assertNoSmoke();
});

test('payment runs list loads', function (): void {
    visit(route('accounting.payment-runs'))
        ->assertNoSmoke();
});

test('payment reminders list loads', function (): void {
    visit(route('accounting.payment-reminders'))
        ->assertNoSmoke();
});

test('commissions list loads', function (): void {
    visit(route('accounting.commissions'))
        ->assertNoSmoke();
});

test('money transfer page loads', function (): void {
    visit(route('accounting.money-transfer'))
        ->assertNoSmoke();
});

test('direct debit page loads', function (): void {
    visit(route('accounting.direct-debit'))
        ->assertNoSmoke();
});

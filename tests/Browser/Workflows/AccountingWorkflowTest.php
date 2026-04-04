<?php

test('transaction list loads', function (): void {
    visit(route('accounting.transactions'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('transaction assignments page loads', function (): void {
    visit(route('accounting.transaction-assignments'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('purchase invoices list loads', function (): void {
    visit(route('accounting.purchase-invoices'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('payment runs list loads', function (): void {
    visit(route('accounting.payment-runs'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('payment reminders list loads', function (): void {
    visit(route('accounting.payment-reminders'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('commissions list loads', function (): void {
    visit(route('accounting.commissions'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('money transfer page loads', function (): void {
    visit(route('accounting.money-transfer'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('direct debit page loads', function (): void {
    visit(route('accounting.direct-debit'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

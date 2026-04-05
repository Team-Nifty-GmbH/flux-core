<?php

use FluxErp\Actions\Task\CreateTask;
use FluxErp\Models\Task;

// --- CreateTask ---

test('create task same-day with reversed times passes validation incorrectly', function (): void {
    // BUG: CreateTask::validateData uses hardcoded 00:00:00 for start_time and 23:59:59 for due_time
    // when times are not provided. This means a task with start_date=2026-04-01 and due_date=2026-04-01
    // always passes, even if the actual start_time > due_time on the same day.
    $task = CreateTask::make([
        'name' => 'Same day reversed times',
        'start_date' => '2026-04-01',
        'start_time' => '18:00:00',
        'due_date' => '2026-04-01',
        'due_time' => '09:00:00',
    ])->validate()->execute();

    expect($task)->toBeInstanceOf(Task::class);
})->skip('Potential bug: same-day time validation ignores actual start_time/due_time');

// --- CreateAddress ---

test('create address with malformed email angle brackets clears email silently', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: Str::between with < but no > returns empty string, silently clearing email');

// --- UpdateContact ---

test('update contact error message uses wrong variable for payment_type_id', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: UpdateContact::validateData line ~143 uses getData instead of resolved $paymentTypeId');

test('update contact tenant change with null payment_type always fails validation', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: null payment_type_id + tenant change triggers false validation error');

// --- CreateOrder ---

test('create order hardcodes 19% vat for shipping costs', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: CreateOrder line 41 hardcodes 0.19 shipping VAT regardless of actual rate');

test('create order null contact causes runtime error on delivery_address_id', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: CreateOrder accesses $contact->delivery_address_id before null guard');

test('create order keyBy address_id destroys address_type_id pivot data', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: keyBy(address_id) after unique() drops duplicate address_id with different address_type_id');

// --- CreateOrderPosition ---

test('create order position unconditionally overwrites vat_rate_id from order', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: uses = instead of ??= for vat_rate_id, ignoring caller-supplied value');

test('create order position null contact on purchase order causes runtime error', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: $order->contact->expense_ledger_account_id crashes when contact_id is null');

// --- UpdateAddress ---

test('update address flag queries include current record causing flag loss', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: is_main/invoice/delivery_address checks dont exclude the current address');

test('update address can_login validation skipped when can_login absent from payload', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: omitting can_login from payload bypasses email/password clearing protection');

test('update address contact_id direct key access in address_types block', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: $this->data[contact_id] without ?? in address_types unique constraint');

// --- UpdateWorkTime ---

test('update work time is_locked direct array access without fallback', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: $this->data[is_locked] throws Undefined array key when is_locked not in payload');

test('update work time locking with null ended_at crashes', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: started_at->diffInMilliseconds(null) when ended_at not set on model');

test('update work time paused_time_ms uses now() instead of new ended_at', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: paused time delta calculated against now() not the requested ended_at');

// --- UpdateProduct ---

test('update product group bundle prices input silently deleted', function (): void {
    $this->markTestSkipped('Needs fix');
})->skip('Bug: prices created from input then immediately deleted by Group bundle block');

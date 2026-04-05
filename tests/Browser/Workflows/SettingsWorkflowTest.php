<?php

// --- Absence ---

test('settings.absence-policies loads without js errors', function (): void {
    visit(route('settings.absence-policies'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('settings.absence-types loads without js errors', function (): void {
    visit(route('settings.absence-types'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Accounting ---

test('settings.accounting-settings loads without js errors', function (): void {
    visit(route('settings.accounting-settings'))
        ->assertNoSmoke();
});

// --- Activity Logs ---

test('settings.activity-logs loads without js errors', function (): void {
    visit(route('settings.activity-logs'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Address Types ---

test('settings.address-types loads without js errors', function (): void {
    visit(route('settings.address-types'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Bank Connections ---

test('settings.bank-connections loads without js errors', function (): void {
    visit(route('settings.bank-connections'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Categories ---

test('settings.categories loads without js errors', function (): void {
    visit(route('settings.categories'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Core Settings ---

test('settings.core-settings loads without js errors', function (): void {
    visit(route('settings.core-settings'))
        ->assertNoSmoke();
});

// --- Countries ---

test('settings.countries loads without js errors', function (): void {
    visit(route('settings.countries'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Country Regions ---

test('settings.country-regions loads without js errors', function (): void {
    visit(route('settings.country-regions'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Currencies ---

test('settings.currencies loads without js errors', function (): void {
    visit(route('settings.currencies'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Discount Groups ---

test('settings.discount-groups loads without js errors', function (): void {
    visit(route('settings.discount-groups'))
        ->assertNoSmoke();
});

// --- Email Templates ---

test('settings.email-templates loads without js errors', function (): void {
    visit(route('settings.email-templates'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Employee Departments ---

test('settings.employee-departments loads without js errors', function (): void {
    visit(route('settings.employee-departments'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Failed Jobs ---

test('settings.failed-jobs loads without js errors', function (): void {
    visit(route('settings.failed-jobs'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Holidays ---

test('settings.holidays loads without js errors', function (): void {
    visit(route('settings.holidays'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Industries ---

test('settings.industries loads without js errors', function (): void {
    visit(route('settings.industries'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Languages ---

test('settings.languages loads without js errors', function (): void {
    visit(route('settings.languages'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Lead Loss Reasons ---

test('settings.lead-loss-reasons loads without js errors', function (): void {
    visit(route('settings.lead-loss-reasons'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Lead States ---

test('settings.lead-states loads without js errors', function (): void {
    visit(route('settings.lead-states'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Ledger Accounts ---

test('settings.ledger-accounts loads without js errors', function (): void {
    visit(route('settings.ledger-accounts'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Locations ---

test('settings.locations loads without js errors', function (): void {
    visit(route('settings.locations'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Logs ---

test('settings.logs loads without js errors', function (): void {
    visit(route('settings.logs'))
        ->assertNoSmoke();
});

// --- Mail Accounts ---

test('settings.mail-accounts loads without js errors', function (): void {
    visit(route('settings.mail-accounts'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Notifications ---

test('settings.notifications loads without js errors', function (): void {
    visit(route('settings.notifications'))
        ->assertNoSmoke();
});

// --- Order Types ---

test('settings.order-types loads without js errors', function (): void {
    visit(route('settings.order-types'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Payment Reminder Texts ---

test('settings.payment-reminder-texts loads without js errors', function (): void {
    visit(route('settings.payment-reminder-texts'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Payment Types ---

test('settings.payment-types loads without js errors', function (): void {
    visit(route('settings.payment-types'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Permissions ---

test('settings.permissions loads without js errors', function (): void {
    visit(route('settings.permissions'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Plugins ---

test('settings.plugins loads without js errors', function (): void {
    visit(route('settings.plugins'))
        ->assertNoSmoke();
});

// --- Price Lists ---

test('settings.price-lists loads without js errors', function (): void {
    visit(route('settings.price-lists'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Product Option Groups ---

test('settings.product-option-groups loads without js errors', function (): void {
    visit(route('settings.product-option-groups'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Product Properties ---

test('settings.product-properties loads without js errors', function (): void {
    visit(route('settings.product-properties'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Queue Monitor ---

test('settings.queue-monitor loads without js errors', function (): void {
    visit(route('settings.queue-monitor'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Record Origins ---

test('settings.record-origins loads without js errors', function (): void {
    visit(route('settings.record-origins'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Reminder Settings ---

test('settings.reminder-settings loads without js errors', function (): void {
    visit(route('settings.reminder-settings'))
        ->assertNoSmoke();
});

// --- Scheduling ---

test('settings.scheduling loads without js errors', function (): void {
    visit(route('settings.scheduling'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Serial Number Ranges ---

test('settings.serial-number-ranges loads without js errors', function (): void {
    visit(route('settings.serial-number-ranges'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Subscription Settings ---

test('settings.subscription-settings loads without js errors', function (): void {
    visit(route('settings.subscription-settings'))
        ->assertNoSmoke();
});

// --- System ---

test('settings.system loads without js errors', function (): void {
    visit(route('settings.system'))
        ->assertNoSmoke();
});

// --- Tags ---

test('settings.tags loads without js errors', function (): void {
    visit(route('settings.tags'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Targets ---

test('settings.targets loads without js errors', function (): void {
    visit(route('settings.targets'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Tenants ---

test('settings.tenants loads without js errors', function (): void {
    visit(route('settings.tenants'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Ticket Settings ---

test('settings.ticket-settings loads without js errors', function (): void {
    visit(route('settings.ticket-settings'))
        ->assertNoSmoke();
});

// --- Ticket Types ---

test('settings.ticket-types loads without js errors', function (): void {
    visit(route('settings.ticket-types'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Tokens ---

test('settings.tokens loads without js errors', function (): void {
    visit(route('settings.tokens'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Units ---

test('settings.units loads without js errors', function (): void {
    visit(route('settings.units'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Users ---

test('settings.users loads without js errors', function (): void {
    visit(route('settings.users'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Vacation Blackouts ---

test('settings.vacation-blackouts loads without js errors', function (): void {
    visit(route('settings.vacation-blackouts'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Vacation Carryover Rules ---

test('settings.vacation-carryover-rules loads without js errors', function (): void {
    visit(route('settings.vacation-carryover-rules'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- VAT Rates ---

test('settings.vat-rates loads without js errors', function (): void {
    visit(route('settings.vat-rates'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Warehouses ---

test('settings.warehouses loads without js errors', function (): void {
    visit(route('settings.warehouses'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Work Time Models ---

test('settings.work-time-models loads without js errors', function (): void {
    visit(route('settings.work-time-models'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

// --- Work Time Types ---

test('settings.work-time-types loads without js errors', function (): void {
    visit(route('settings.work-time-types'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

<?php

use FluxErp\Models\PaymentType;
use FluxErp\Models\Tenant;

test('getTenant returns default tenant when no tenants attached', function (): void {
    $paymentType = PaymentType::factory()->create();

    expect($paymentType->getTenant())->not->toBeNull();
});

test('getTenant returns attached tenant', function (): void {
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    expect($paymentType->getTenant()->getKey())->toBe($this->dbTenant->getKey());
});

test('getTenantId returns id', function (): void {
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    expect($paymentType->getTenantId())->toBe($this->dbTenant->getKey());
});

test('getTenants returns all tenants or defaults', function (): void {
    $paymentType = PaymentType::factory()->create();

    $tenants = $paymentType->getTenants();

    expect($tenants)->not->toBeEmpty();
});

test('whereHasTenant scope filters by tenant', function (): void {
    $otherTenant = Tenant::factory()->create();

    $attached = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $otherAttached = PaymentType::factory()
        ->hasAttached($otherTenant, relationship: 'tenants')
        ->create();

    $results = PaymentType::query()
        ->whereHasTenant($this->dbTenant->getKey())
        ->pluck('id');

    expect($results)->toContain($attached->getKey())
        ->not->toContain($otherAttached->getKey());
});

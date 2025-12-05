<?php

use Carbon\Carbon;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->paymentTypes = PaymentType::factory()
        ->count(2)
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.payment-types.{id}.get'),
        'index' => Permission::findOrCreate('api.payment-types.get'),
        'create' => Permission::findOrCreate('api.payment-types.post'),
        'update' => Permission::findOrCreate('api.payment-types.put'),
        'delete' => Permission::findOrCreate('api.payment-types.{id}.delete'),
    ];
});

test('create payment type', function (): void {
    $paymentType = [
        'tenant_id' => $this->dbTenant->getKey(),
        'name' => 'Payment Type Name',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/payment-types', $paymentType);
    $response->assertCreated();

    $responsePaymentType = json_decode($response->getContent())->data;
    $dbPaymentType = PaymentType::query()
        ->whereKey($responsePaymentType->id)
        ->first();

    expect($dbPaymentType)->not->toBeEmpty();
    expect($dbPaymentType->tenants()->pluck('id')->toArray())->toEqual([$paymentType['tenant_id']]);
    expect($dbPaymentType->name)->toEqual($paymentType['name']);
    expect($dbPaymentType->description)->toBeNull();
    expect($dbPaymentType->payment_reminder_days_1)->toBeNull();
    expect($dbPaymentType->payment_reminder_days_2)->toBeNull();
    expect($dbPaymentType->payment_reminder_days_3)->toBeNull();
    expect($dbPaymentType->payment_target)->toBeNull();
    expect($dbPaymentType->payment_discount_target)->toBeNull();
    expect($dbPaymentType->payment_discount_percentage)->toBeNull();
    expect($dbPaymentType->is_active)->toBeTrue();
    expect($this->user->is($dbPaymentType->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbPaymentType->getUpdatedBy()))->toBeTrue();
});

test('create payment type maximum', function (): void {
    $paymentType = [
        'tenant_id' => $this->dbTenant->getKey(),
        'name' => 'Payment Type Name',
        'description' => 'New description text for further information',
        'payment_reminder_days_1' => 42,
        'payment_reminder_days_2' => 42,
        'payment_reminder_days_3' => 42,
        'payment_target' => 42,
        'payment_discount_target' => 42,
        'payment_discount_percentage' => 42 / 100,
        'is_active' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/payment-types', $paymentType);
    $response->assertCreated();

    $responsePaymentType = json_decode($response->getContent())->data;
    $dbPaymentType = PaymentType::query()
        ->whereKey($responsePaymentType->id)
        ->first();

    expect($dbPaymentType)->not->toBeEmpty();
    expect($dbPaymentType->tenants()->pluck('id')->toArray())->toEqual([$paymentType['tenant_id']]);
    expect($dbPaymentType->name)->toEqual($paymentType['name']);
    expect($dbPaymentType->description)->toEqual($paymentType['description']);
    expect($dbPaymentType->payment_reminder_days_1)->toEqual($paymentType['payment_reminder_days_1']);
    expect($dbPaymentType->payment_reminder_days_2)->toEqual($paymentType['payment_reminder_days_2']);
    expect($dbPaymentType->payment_reminder_days_3)->toEqual($paymentType['payment_reminder_days_3']);
    expect($dbPaymentType->payment_target)->toEqual($paymentType['payment_target']);
    expect($dbPaymentType->payment_discount_target)->toEqual($paymentType['payment_discount_target']);
    expect($dbPaymentType->payment_discount_percentage)->toEqual($paymentType['payment_discount_percentage']);
    expect($dbPaymentType->is_active)->toEqual($paymentType['is_active']);
    expect($this->user->is($dbPaymentType->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbPaymentType->getUpdatedBy()))->toBeTrue();
});

test('create payment type validation fails', function (): void {
    $paymentType = [
        'name' => 'Payment Type Name',
        'tenants' => 'tenant_id',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/payment-types', $paymentType);
    $response->assertUnprocessable();
});

test('delete payment type', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/payment-types/' . $this->paymentTypes[1]->id);
    $response->assertNoContent();

    $paymentType = $this->paymentTypes[1]->fresh();
    expect($paymentType->deleted_at)->not->toBeNull();
    expect($this->user->is($paymentType->getDeletedBy()))->toBeTrue();
});

test('delete payment type payment type not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/payment-types/' . ++$this->paymentTypes[1]->id);
    $response->assertNotFound();
});

test('get payment type', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/payment-types/' . $this->paymentTypes[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonPaymentType = $json->data;

    // Check if controller returns the test payment type.
    expect($jsonPaymentType)->not->toBeEmpty();
    expect($jsonPaymentType->id)->toEqual($this->paymentTypes[0]->id);
    expect($jsonPaymentType->name)->toEqual($this->paymentTypes[0]->name);
    expect($jsonPaymentType->description)->toEqual($this->paymentTypes[0]->description);
    expect($jsonPaymentType->payment_reminder_days_1)->toEqual($this->paymentTypes[0]->payment_reminder_days_1);
    expect($jsonPaymentType->payment_reminder_days_2)->toEqual($this->paymentTypes[0]->payment_reminder_days_2);
    expect($jsonPaymentType->payment_reminder_days_3)->toEqual($this->paymentTypes[0]->payment_reminder_days_3);
    expect($jsonPaymentType->payment_target)->toEqual($this->paymentTypes[0]->payment_target);
    expect($jsonPaymentType->payment_discount_target)->toEqual($this->paymentTypes[0]->payment_discount_target);
    expect($jsonPaymentType->payment_discount_percentage)->toEqual($this->paymentTypes[0]->payment_discount_percentage);
    expect($jsonPaymentType->is_active)->toEqual($this->paymentTypes[0]->is_active);
});

test('get payment type include not allowed', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/payment-types/' . $this->paymentTypes[1]->id . '?include=test');
    $response->assertUnprocessable();
});

test('get payment type payment type not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/payment-types/' . ++$this->paymentTypes[1]->id);
    $response->assertNotFound();
});

test('get payment types', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/payment-types');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonPaymentTypes = collect($json->data->data);

    // Check the amount of test payment types.
    expect(count($jsonPaymentTypes))->toBeGreaterThanOrEqual(2);

    // Check if controller returns the test payment types.
    foreach ($this->paymentTypes as $paymentType) {
        $jsonPaymentTypes->contains(function ($jsonPaymentType) use ($paymentType) {
            return $jsonPaymentType->id === $paymentType->id &&
                $jsonPaymentType->name === $paymentType->name &&
                $jsonPaymentType->description === $paymentType->description &&
                $jsonPaymentType->payment_reminder_days_1 === $paymentType->payment_reminder_days_1 &&
                $jsonPaymentType->payment_reminder_days_2 === $paymentType->payment_reminder_days_2 &&
                $jsonPaymentType->payment_reminder_days_3 === $paymentType->payment_reminder_days_3 &&
                $jsonPaymentType->payment_target === $paymentType->payment_target &&
                $jsonPaymentType->payment_discount_target === $paymentType->payment_discount_target &&
                $jsonPaymentType->payment_discount_percentage === $paymentType->payment_discount_percentage &&
                $jsonPaymentType->is_active === $paymentType->is_active &&
                Carbon::parse($jsonPaymentType->created_at)->toDateTimeString() ===
                    Carbon::parse($paymentType->created_at)->toDateTimeString() &&
                Carbon::parse($jsonPaymentType->updated_at)->toDateTimeString() ===
                    Carbon::parse($paymentType->updated_at)->toDateTimeString();
        });
    }
});

test('update payment type', function (): void {
    $paymentType = [
        'id' => $this->paymentTypes[0]->id,
        'name' => 'Payment Type Name',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/payment-types', $paymentType);
    $response->assertOk();

    $responsePaymentType = json_decode($response->getContent())->data;
    $dbPaymentType = PaymentType::query()
        ->whereKey($responsePaymentType->id)
        ->first();

    expect($dbPaymentType)->not->toBeEmpty();
    expect($dbPaymentType->id)->toEqual($paymentType['id']);
    expect($dbPaymentType->name)->toEqual($paymentType['name']);
    expect($dbPaymentType->description)->toEqual($this->paymentTypes[0]->description);
    expect($dbPaymentType->payment_reminder_days_1)->toEqual($this->paymentTypes[0]->payment_reminder_days_1);
    expect($dbPaymentType->payment_reminder_days_2)->toEqual($this->paymentTypes[0]->payment_reminder_days_2);
    expect($dbPaymentType->payment_reminder_days_3)->toEqual($this->paymentTypes[0]->payment_reminder_days_3);
    expect($dbPaymentType->payment_target)->toEqual($this->paymentTypes[0]->payment_target);
    expect($dbPaymentType->payment_discount_target)->toEqual($this->paymentTypes[0]->payment_discount_target);
    expect($dbPaymentType->payment_discount_percentage)->toEqual($this->paymentTypes[0]->payment_discount_percentage);
    expect($dbPaymentType->is_active)->toEqual($this->paymentTypes[0]->is_active);
    expect($this->user->is($dbPaymentType->getUpdatedBy()))->toBeTrue();
});

test('update payment type maximum', function (): void {
    $paymentType = [
        'id' => $this->paymentTypes[0]->id,
        'name' => 'Payment Type Name',
        'description' => 'New description text for further information',
        'payment_reminder_days_1' => 42,
        'payment_reminder_days_2' => 42,
        'payment_reminder_days_3' => 42,
        'payment_target' => 42,
        'payment_discount_target' => 42,
        'payment_discount_percentage' => 42 / 100,
        'is_active' => true,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/payment-types', $paymentType);
    $response->assertOk();

    $responsePaymentType = json_decode($response->getContent())->data;
    $dbPaymentType = PaymentType::query()
        ->whereKey($responsePaymentType->id)
        ->first();

    expect($dbPaymentType)->not->toBeEmpty();
    expect($dbPaymentType->id)->toEqual($paymentType['id']);
    expect($dbPaymentType->name)->toEqual($paymentType['name']);
    expect($dbPaymentType->description)->toEqual($paymentType['description']);
    expect($dbPaymentType->payment_reminder_days_1)->toEqual($paymentType['payment_reminder_days_1']);
    expect($dbPaymentType->payment_reminder_days_2)->toEqual($paymentType['payment_reminder_days_2']);
    expect($dbPaymentType->payment_reminder_days_3)->toEqual($paymentType['payment_reminder_days_3']);
    expect($dbPaymentType->payment_target)->toEqual($paymentType['payment_target']);
    expect($dbPaymentType->payment_discount_target)->toEqual($paymentType['payment_discount_target']);
    expect($dbPaymentType->payment_discount_percentage)->toEqual($paymentType['payment_discount_percentage']);
    expect($dbPaymentType->is_active)->toEqual($paymentType['is_active']);
    expect($this->user->is($dbPaymentType->getUpdatedBy()))->toBeTrue();
});

test('update payment type validation fails', function (): void {
    $paymentType = [
        'id' => $this->paymentTypes[0]->id,
        'name' => 'Payment Type Name',
        'tenants' => 'tenant_id',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/payment-types', $paymentType);
    $response->assertUnprocessable();
});

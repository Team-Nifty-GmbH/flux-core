<?php

use FluxErp\Livewire\Contact\Accounting\Discounts;
use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use FluxErp\Models\Product;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

test('edit with model fills form and opens modal', function (): void {
    $contact = Contact::factory()->create();
    $product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $discount = Discount::factory()->create([
        'model_type' => morph_alias(Product::class),
        'model_id' => $product->getKey(),
        'is_percentage' => true,
        'discount' => 0.1,
    ]);
    $contact->discounts()->attach($discount->getKey());

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->call('edit', $discount->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('discountForm.id', $discount->getKey())
        ->assertOpensModal('edit-discount-modal');
});

test('reset discount clears form', function (): void {
    $contact = Contact::factory()->create();
    $product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $discount = Discount::factory()->create([
        'model_type' => morph_alias(Product::class),
        'model_id' => $product->getKey(),
        'is_percentage' => true,
        'discount' => 0.1,
    ]);
    $contact->discounts()->attach($discount->getKey());

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->call('edit', $discount->getKey())
        ->assertSet('discountForm.id', $discount->getKey())
        ->call('resetDiscount')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('discountForm.id', null)
        ->assertSet('discountForm.discount', null)
        ->assertSet('discountForm.name', null);
});

test('can create discount', function (): void {
    $contact = Contact::factory()->create();
    $product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->set('discountForm.model_type', morph_alias(Product::class))
        ->set('discountForm.model_id', $product->getKey())
        ->set('discountForm.discount', 10)
        ->set('discountForm.is_percentage', true)
        ->set('discountForm.name', 'Test Discount')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('discounts', [
        'name' => 'Test Discount',
        'is_percentage' => true,
    ]);

    $discount = Discount::query()->where('name', 'Test Discount')->first();
    expect($contact->discounts()->whereKey($discount->getKey())->exists())->toBeTrue();
});

test('can update discount', function (): void {
    $contact = Contact::factory()->create();
    $product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $discount = Discount::factory()->create([
        'model_type' => morph_alias(Product::class),
        'model_id' => $product->getKey(),
        'is_percentage' => false,
        'discount' => 5,
        'name' => 'Old Name',
    ]);
    $contact->discounts()->attach($discount->getKey());

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->call('edit', $discount->getKey())
        ->set('discountForm.name', 'Updated Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($discount->refresh()->name)->toEqual('Updated Name');
});

test('can delete discount', function (): void {
    $contact = Contact::factory()->create();
    $product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $discount = Discount::factory()->create([
        'model_type' => morph_alias(Product::class),
        'model_id' => $product->getKey(),
        'is_percentage' => true,
        'discount' => 0.1,
    ]);
    $contact->discounts()->attach($discount->getKey());

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->call('delete', $discount->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('discounts', [
        'id' => $discount->getKey(),
    ]);
});

test('save validation fails with missing required fields', function (): void {
    $contact = Contact::factory()->create();
    $product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->set('discountForm.model_type', morph_alias(Product::class))
        ->set('discountForm.model_id', $product->getKey())
        ->set('discountForm.discount', null)
        ->set('discountForm.is_percentage', true)
        ->call('save')
        ->assertOk()
        ->assertReturned(false);
});

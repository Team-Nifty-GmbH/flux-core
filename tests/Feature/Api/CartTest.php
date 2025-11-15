<?php

use FluxErp\Models\Address;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\Contact;
use FluxErp\Models\OrderType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $dbTenant = Tenant::factory()->create();

    $this->contact = Contact::factory()->create([
        'tenant_id' => $dbTenant->id,
    ]);

    Address::factory()->create([
        'tenant_id' => $dbTenant->id,
        'contact_id' => $this->contact->id,
    ]);

    $priceList = PriceList::factory()->create();
    $vatRate = VatRate::factory()->create();

    $this->products = Product::factory()->count(3)->create([
        'tenant_id' => $dbTenant->id,
        'vat_rate_id' => $vatRate->id,
    ]);

    $this->carts = Cart::factory()->count(2)->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $this->user->id,
        'price_list_id' => $priceList->id,
        'is_watchlist' => false,
        'is_portal_public' => false,
        'is_public' => false,
    ]);

    $this->carts->push(
        Cart::factory()->create([
            'authenticatable_type' => morph_alias(User::class),
            'authenticatable_id' => $this->user->id,
            'price_list_id' => $priceList->id,
            'is_watchlist' => true,
            'is_portal_public' => false,
            'is_public' => false,
        ])
    );

    CartItem::factory()->count(2)->create([
        'cart_id' => $this->carts[0]->id,
        'product_id' => $this->products[0]->id,
        'vat_rate_id' => $vatRate->id,
    ]);

    OrderType::factory()->create([
        'order_type_enum' => 'order',
        'tenant_id' => $dbTenant->id,
    ]);

    $this->user->tenants()->attach($dbTenant->id);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.carts.{id}.get'),
        'index' => Permission::findOrCreate('api.carts.get'),
        'create' => Permission::findOrCreate('api.carts.post'),
        'update' => Permission::findOrCreate('api.carts.put'),
        'delete' => Permission::findOrCreate('api.carts.{id}.delete'),
    ];
});

test('create cart', function (): void {
    $cart = [
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $this->user->id,
        'is_watchlist' => false,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/carts', $cart);
    $response->assertCreated();

    $responseCart = json_decode($response->getContent())->data;
    $dbCart = Cart::query()
        ->whereKey($responseCart->id)
        ->first();

    expect($dbCart)->not->toBeEmpty();
    expect($dbCart->authenticatable_type)->toEqual($cart['authenticatable_type']);
    expect($dbCart->authenticatable_id)->toEqual($cart['authenticatable_id']);
    expect($dbCart->is_watchlist)->toEqual($cart['is_watchlist']);

    // Validate all model properties with expected values
    expect($dbCart->id)->not->toBeNull();
    expect($dbCart->created_at)->not->toBeNull();
    expect($dbCart->updated_at)->not->toBeNull();
    expect($dbCart->session_id)->not->toBeEmpty();
    expect($dbCart->is_portal_public)->toBeFalse();
    // Default value
    expect($dbCart->is_public)->toBeFalse();
    // Default value
});

test('create cart validation fails', function (): void {
    $cart = [
        'authenticatable_type' => 'invalid_type',
        'authenticatable_id' => 999999,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/carts', $cart);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'authenticatable_type',
        'authenticatable_id',
    ]);
});

test('create cart with price list', function (): void {
    $priceList = PriceList::factory()->create();

    $cart = [
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $this->user->id,
        'price_list_id' => $priceList->id,
        'is_watchlist' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/carts', $cart);
    $response->assertCreated();

    $responseCart = json_decode($response->getContent())->data;
    $dbCart = Cart::query()
        ->whereKey($responseCart->id)
        ->first();

    expect($dbCart)->not->toBeEmpty();
    expect($dbCart->price_list_id)->toEqual($cart['price_list_id']);
    expect($dbCart->is_watchlist)->toEqual($cart['is_watchlist']);

    // Validate all model properties with expected values
    expect($dbCart->id)->not->toBeNull();
    expect($dbCart->created_at)->not->toBeNull();
    expect($dbCart->updated_at)->not->toBeNull();
    expect($dbCart->session_id)->not->toBeEmpty();
    expect($dbCart->is_portal_public)->toBeFalse();
    // Default value
    expect($dbCart->is_public)->toBeFalse();
    // Default value
    expect($dbCart->authenticatable_type)->toEqual($cart['authenticatable_type']);
    expect($dbCart->authenticatable_id)->toEqual($cart['authenticatable_id']);
});

test('delete cart', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/carts/' . $this->carts[0]->id);
    $response->assertNoContent();

    $cart = $this->carts[0]->fresh();
    expect($cart->deleted_at)->not->toBeNull();
});

test('delete cart not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/carts/' . (Cart::max('id') + 1));
    $response->assertNotFound();
});

test('get cart', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/carts/' . $this->carts[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonCart = $json->data;

    expect($jsonCart)->not->toBeEmpty();
    expect($jsonCart->id)->toEqual($this->carts[0]->id);
    expect($jsonCart->authenticatable_type)->toEqual($this->carts[0]->authenticatable_type);
    expect($jsonCart->authenticatable_id)->toEqual($this->carts[0]->authenticatable_id);
    expect($jsonCart->is_watchlist)->toEqual($this->carts[0]->is_watchlist);
});

test('get cart not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/carts/' . (Cart::max('id') + 1));
    $response->assertNotFound();
});

test('get carts', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/carts');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonCarts = collect($json->data->data);

    expect(count($jsonCarts))->toBeGreaterThanOrEqual(3);

    foreach ($this->carts as $cart) {
        $jsonCarts->contains(function ($jsonCart) use ($cart) {
            return $jsonCart->id === $cart->id &&
                $jsonCart->authenticatable_type === $cart->authenticatable_type &&
                $jsonCart->authenticatable_id === $cart->authenticatable_id &&
                $jsonCart->is_watchlist === $cart->is_watchlist;
        });
    }
});

test('update cart', function (): void {
    $cart = [
        'id' => $this->carts[0]->id,
        'is_watchlist' => true,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/carts', $cart);
    $response->assertOk();

    $responseCart = json_decode($response->getContent())->data;
    $dbCart = Cart::query()
        ->whereKey($responseCart->id)
        ->first();

    expect($dbCart)->not->toBeEmpty();
    expect($dbCart->id)->toEqual($cart['id']);
    expect($dbCart->is_watchlist)->toEqual($cart['is_watchlist']);

    // Validate all model properties with expected values
    expect($dbCart->id)->not->toBeNull();
    expect($dbCart->created_at)->not->toBeNull();
    expect($dbCart->updated_at)->not->toBeNull();
    expect($dbCart->session_id)->not->toBeEmpty();
    expect($dbCart->is_portal_public)->toBeFalse();
    // Default value
    expect($dbCart->is_public)->toBeFalse();
    // Default value
    expect($dbCart->authenticatable_type)->not->toBeNull();
    expect($dbCart->authenticatable_id)->not->toBeNull();
});

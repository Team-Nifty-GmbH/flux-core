<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $address = Address::factory()->create([
        'contact_id' => $contact->id,
        'client_id' => $this->dbClient->getKey(),
        'is_main_address' => false,
    ]);

    $this->warehouses = Warehouse::factory()->count(3)->create([
        'address_id' => $address->id,
    ]);

    $this->products = Product::factory()
        ->count(3)
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    $this->stockPostings = StockPosting::factory()->count(3)->create([
        'warehouse_id' => $this->warehouses[0]->id,
        'product_id' => $this->products[0]->id,
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.stock-postings.{id}.get'),
        'index' => Permission::findOrCreate('api.stock-postings.get'),
        'create' => Permission::findOrCreate('api.stock-postings.post'),
        'delete' => Permission::findOrCreate('api.stock-postings.{id}.delete'),
    ];
});

test('create stock posting', function (): void {
    $stockPosting = [
        'warehouse_id' => $this->warehouses[0]->id,
        'product_id' => $this->products[0]->id,
        'purchase_price' => rand(1, 99),
        'posting' => rand(1, 99),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/stock-postings', $stockPosting);
    $response->assertStatus(201);

    $responseStockPosting = json_decode($response->getContent())->data;

    $dbStockPosting = StockPosting::query()
        ->whereKey($responseStockPosting->id)
        ->first();

    $stock = StockPosting::query()
        ->where('warehouse_id', $stockPosting['warehouse_id'])
        ->where('product_id', $stockPosting['product_id'])
        ->where('id', '<', $dbStockPosting->id)
        ->latest('id')
        ->first()
        ->stock + $stockPosting['posting'];

    expect($dbStockPosting->warehouse_id)->toEqual($stockPosting['warehouse_id']);
    expect($dbStockPosting->product_id)->toEqual($stockPosting['product_id']);
    expect($dbStockPosting->posting)->toEqual($stockPosting['posting']);
    expect($dbStockPosting->stock)->toEqual($stock);
    expect($dbStockPosting->description)->toEqual($stockPosting['description']);
});

test('create stock posting empty stock', function (): void {
    $latestPosting = StockPosting::query()
        ->where('warehouse_id', '=', $this->warehouses[0]->id)
        ->where('product_id', '=', $this->products[0]->id)
        ->latest('id')
        ->first();

    $latestPosting->stock = null;
    $latestPosting->save();

    $stockPosting = [
        'warehouse_id' => $this->warehouses[0]->id,
        'product_id' => $this->products[0]->id,
        'purchase_price' => rand(1, 99),
        'posting' => rand(1, 99),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/stock-postings', $stockPosting);
    $response->assertStatus(201);

    $responseStockPosting = json_decode($response->getContent())->data;

    $dbStockPosting = StockPosting::query()
        ->whereKey($responseStockPosting->id)
        ->first();

    expect($dbStockPosting->warehouse_id)->toEqual($stockPosting['warehouse_id']);
    expect($dbStockPosting->product_id)->toEqual($stockPosting['product_id']);
    expect($dbStockPosting->posting)->toEqual($stockPosting['posting']);
    expect($dbStockPosting->description)->toEqual($stockPosting['description']);
});

test('create stock posting validation fails', function (): void {
    $stockPosting = [
        'product_id' => $this->products[0]->id,
        'purchase_price' => rand(1, 99),
        'posting' => rand(1, 99),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/stock-postings', $stockPosting);
    $response->assertStatus(422);
});

test('delete stock posting', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/stock-postings/' . $this->stockPostings[0]->id);
    $response->assertStatus(204);

    expect(StockPosting::query()->whereKey($this->stockPostings[0]->id)->exists())->toBeFalse();
});

test('delete stock posting stock posting not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/stock-postings/' . ++$this->stockPostings[2]->id);
    $response->assertStatus(404);
});

test('get stock posting', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/stock-postings/' . $this->stockPostings[0]->id);
    $response->assertStatus(200);

    $stockPosting = json_decode($response->getContent())->data;

    expect($stockPosting->id)->toEqual($this->stockPostings[0]->id);
    expect($stockPosting->stock)->toEqual($this->stockPostings[0]->stock);
    expect($stockPosting->posting)->toEqual($this->stockPostings[0]->posting);
    expect($stockPosting->product_id)->toEqual($this->stockPostings[0]->product_id);
    expect($stockPosting->warehouse_id)->toEqual($this->stockPostings[0]->warehouse_id);
    expect($stockPosting->description)->toEqual($this->stockPostings[0]->description);
});

test('get stock posting stock posting not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/stock-postings/' . $this->stockPostings[2]->id + 10000);
    $response->assertStatus(404);
});

test('get stock postings', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/stock-postings');
    $response->assertStatus(200);

    $stockPostings = json_decode($response->getContent())->data->data;

    expect($stockPostings[0]->id)->toEqual($this->stockPostings[0]->id);
    expect($stockPostings[0]->stock)->toEqual($this->stockPostings[0]->stock);
    expect($stockPostings[0]->posting)->toEqual($this->stockPostings[0]->posting);
    expect($stockPostings[0]->product_id)->toEqual($this->stockPostings[0]->product_id);
    expect($stockPostings[0]->warehouse_id)->toEqual($this->stockPostings[0]->warehouse_id);
    expect($stockPostings[0]->description)->toEqual($this->stockPostings[0]->description);
});

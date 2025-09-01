<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Enums\BundleTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $productOptionGroup = ProductOptionGroup::factory()->create();

    $this->clients = Client::factory()->count(3)->create();

    $this->products = Product::factory()
        ->count(3)
        ->hasAttached($this->clients, relationship: 'clients')
        ->create();

    $this->vatRates = VatRate::factory()->count(3)->create();

    $this->units = Unit::factory()->count(3)->create();

    $this->productOptions = ProductOption::factory()->count(3)->create([
        'product_option_group_id' => $productOptionGroup->id,
    ]);

    $this->productProperties = ProductProperty::factory()->count(3)->create();

    $this->user->clients()->attach($this->clients->pluck('id')->toArray());

    $this->permissions = [
        'show' => Permission::findOrCreate('api.products.{id}.get'),
        'index' => Permission::findOrCreate('api.products.get'),
        'create' => Permission::findOrCreate('api.products.post'),
        'update' => Permission::findOrCreate('api.products.put'),
        'delete' => Permission::findOrCreate('api.products.{id}.delete'),
    ];
});

test('create product', function (): void {
    $defaultVatRate = VatRate::factory()
        ->create([
            'is_default' => true,
        ]);

    $product = [
        'name' => Str::random(),
        'clients' => [$this->clients[0]->id],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/products', $product);
    $response->assertStatus(201);

    $responseProduct = json_decode($response->getContent())->data;

    $dbProduct = Product::query()
        ->whereKey($responseProduct->id)
        ->first();

    expect($dbProduct->name)->toEqual($product['name']);
    expect($dbProduct->clients->pluck('id')->toArray())->toEqual($product['clients']);
    expect($dbProduct->parent_id)->toBeNull();
    expect($dbProduct->vat_rate_id)->toEqual($defaultVatRate->id);
    expect($dbProduct->unit_id)->toBeNull();
    expect($dbProduct->purchase_unit_id)->toBeNull();
    expect($dbProduct->reference_unit_id)->toBeNull();
    expect($dbProduct->product_number)->not->toBeNull();
    expect($dbProduct->description)->toBeEmpty();
    expect($dbProduct->weight_gram)->toBeEmpty();
    expect($dbProduct->dimension_length_mm)->toBeEmpty();
    expect($dbProduct->dimension_width_mm)->toBeEmpty();
    expect($dbProduct->dimension_height_mm)->toBeEmpty();
    expect($dbProduct->ean)->toBeNull();
    expect($dbProduct->min_devlivery_time)->toBeNull();
    expect($dbProduct->max_devlivery_time)->toBeNull();
    expect($dbProduct->restock_time)->toBeNull();
    expect($dbProduct->purchase_steps)->toBeNull();
    expect($dbProduct->min_purchase)->toBeNull();
    expect($dbProduct->max_purchase)->toBeNull();
    expect($dbProduct->seo_keywords)->toBeNull();
    expect($dbProduct->posting_account)->toBeNull();
    expect($dbProduct->warning_stock_amount)->toBeNull();
    expect($dbProduct->is_active)->toBeTrue();
    expect($dbProduct->is_highlight)->toBeFalse();
    expect($dbProduct->is_bundle)->toBeFalse();
    expect($dbProduct->is_service)->toBeFalse();
    expect($dbProduct->is_shipping_free)->toBeFalse();
    expect($dbProduct->has_serial_numbers)->toBeFalse();
    expect($dbProduct->is_nos)->toBeFalse();
    expect($dbProduct->is_active_export_to_web_shop)->toBeFalse();
});

test('create product maximum', function (): void {
    $product = [
        'name' => Str::random(),
        'parent_id' => $this->products[0]->id,
        'vat_rate_id' => $this->vatRates[0]->id,
        'unit_id' => $this->units[0]->id,
        'purchase_unit_id' => $this->units[1]->id,
        'reference_unit_id' => $this->units[2]->id,
        'product_number' => Str::random(),
        'bundle_type_enum' => BundleTypeEnum::Standard->value,
        'description' => Str::random(),
        'weight_gram' => rand(1, 1000),
        'dimension_length_mm' => rand(1, 1000),
        'dimension_width_mm' => rand(1, 1000),
        'dimension_height_mm' => rand(1, 1000),
        'ean' => Str::random(),
        'min_delivery_time' => rand(1, 999),
        'max_delivery_time' => rand(1, 999),
        'restock_time' => rand(1, 999),
        'purchase_steps' => rand(1, 999),
        'min_purchase' => rand(1, 999),
        'max_purchase' => rand(1, 999),
        'seo_keywords' => Str::random(),
        'posting_account' => Str::random(),
        'warning_stock_amount' => rand(1, 999),
        'is_active' => (bool) rand(0, 1),
        'is_highlight' => (bool) rand(0, 1),
        'is_bundle' => true,
        'is_service' => false,
        'is_shipping_free' => (bool) rand(0, 1),
        'has_serial_numbers' => (bool) rand(0, 1),
        'is_nos' => (bool) rand(0, 1),
        'is_active_export_to_web_shop' => (bool) rand(0, 1),
        'clients' => [$this->clients[0]->id],
        'bundle_products' => [
            [
                'id' => $this->products[0]->id,
                'count' => rand(),
            ],
            [
                'id' => $this->products[1]->id,
                'count' => rand(),
            ],
        ],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/products', $product);
    $response->assertStatus(201);

    $responseProduct = json_decode($response->getContent())->data;

    $dbProduct = Product::query()
        ->whereKey($responseProduct->id)
        ->first();

    expect($dbProduct->parent_id)->toEqual($product['parent_id']);
    expect($dbProduct->vat_rate_id)->toEqual($product['vat_rate_id']);
    expect($dbProduct->unit_id)->toEqual($product['unit_id']);
    expect($dbProduct->purchase_unit_id)->toEqual($product['purchase_unit_id']);
    expect($dbProduct->reference_unit_id)->toEqual($product['reference_unit_id']);
    expect($dbProduct->product_number)->toEqual($product['product_number']);
    expect($dbProduct->description)->toEqual($product['description']);
    expect($dbProduct->weight_gram)->toEqual($product['weight_gram']);
    expect($dbProduct->dimension_length_mm)->toEqual($product['dimension_length_mm']);
    expect($dbProduct->dimension_width_mm)->toEqual($product['dimension_width_mm']);
    expect($dbProduct->dimension_height_mm)->toEqual($product['dimension_height_mm']);
    expect($dbProduct->ean)->toEqual($product['ean']);
    expect($dbProduct->min_delivery_time)->toEqual($product['min_delivery_time']);
    expect($dbProduct->max_delivery_time)->toEqual($product['max_delivery_time']);
    expect($dbProduct->restock_time)->toEqual($product['restock_time']);
    expect($dbProduct->purchase_steps)->toEqual($product['purchase_steps']);
    expect($dbProduct->min_purchase)->toEqual($product['min_purchase']);
    expect($dbProduct->max_purchase)->toEqual($product['max_purchase']);
    expect($dbProduct->seo_keywords)->toEqual($product['seo_keywords']);
    expect($dbProduct->posting_account)->toEqual($product['posting_account']);
    expect($dbProduct->warning_stock_amount)->toEqual($product['warning_stock_amount']);
    expect($dbProduct->is_active)->toEqual($product['is_active']);
    expect($dbProduct->is_highlight)->toEqual($product['is_highlight']);
    expect($dbProduct->is_bundle)->toEqual($product['is_bundle']);
    expect($dbProduct->is_service)->toEqual($product['is_service']);
    expect($dbProduct->is_shipping_free)->toEqual($product['is_shipping_free']);
    expect($dbProduct->has_serial_numbers)->toEqual($product['has_serial_numbers']);
    expect($dbProduct->is_nos)->toEqual($product['is_nos']);
    expect($dbProduct->is_active_export_to_web_shop)->toEqual($product['is_active_export_to_web_shop']);

    expect($dbProduct->clients->pluck('id')->toArray())->toEqual($product['clients']);
    expect($dbProduct->bundleProducts()
        ->wherePivot('bundle_product_id', $product['bundle_products'][0]['id'])
        ->wherePivot('count', $product['bundle_products'][0]['count'])
        ->exists())->toBeTrue();
    expect($dbProduct->bundleProducts()
        ->wherePivot('bundle_product_id', $product['bundle_products'][1]['id'])
        ->wherePivot('count', $product['bundle_products'][1]['count'])
        ->exists())->toBeTrue();
});

test('create product validation fails', function (): void {
    $product = [
        'name' => rand(1, 999),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/products', $product);
    $response->assertStatus(422);
});

test('delete product', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/products/' . $this->products[0]->id);
    $response->assertStatus(204);

    expect(Product::query()->whereKey($this->products[0]->id)->exists())->toBeFalse();
});

test('delete product product has children', function (): void {
    $this->products[1]->parent_id = $this->products[2]->id;
    $this->products[1]->save();

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/products/' . $this->products[2]->id);
    $response->assertStatus(423);
});

test('delete product product not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/products/' . ++$this->products[2]->id);
    $response->assertStatus(404);
});

test('get product', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/products/' . $this->products[0]->id);
    $response->assertStatus(200);

    $product = json_decode($response->getContent())->data;

    expect($product->id)->toEqual($this->products[0]->id);
    expect($product->name)->toEqual($this->products[0]->name);
    expect($product->description)->toEqual($this->products[0]->description);
    expect($product->vat_rate_id)->toEqual($this->products[0]->vat_rate_id);
    expect($product->unit_id)->toEqual($this->products[0]->unit_id);
});

test('get product product not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/products/' . $this->products[2]->id + 10000);
    $response->assertStatus(404);
});

test('get products', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/products');
    $response->assertStatus(200);

    $products = json_decode($response->getContent())->data;

    expect($this->products[0]->id)->toEqual($products->data[0]->id);
    expect($this->products[0]->name)->toEqual($products->data[0]->name);
    expect($this->products[0]->description)->toEqual($products->data[0]->description);
    expect($this->products[0]->vat_rate_id)->toEqual($products->data[0]->vat_rate_id);
    expect($this->products[0]->unit_id)->toEqual($products->data[0]->unit_id);
});

test('update product', function (): void {
    $this->products[0]->bundleProducts()->sync([$this->products[1]->id => ['count' => rand()]]);
    $this->products[0]->is_bundle = true;
    $this->products[0]->save();

    $product = [
        'id' => $this->products[0]->id,
        'name' => Str::random(),
        'vat_rate_id' => $this->vatRates[0]->id,
        'unit_id' => $this->units[0]->id,
        'purchase_unit_id' => $this->units[1]->id,
        'reference_unit_id' => $this->units[2]->id,
        'product_number' => Str::random(),
        'description' => Str::random(),
        'weight_gram' => rand(1, 1000),
        'dimension_length_mm' => rand(1, 1000),
        'dimension_width_mm' => rand(1, 1000),
        'dimension_height_mm' => rand(1, 1000),
        'ean' => Str::random(),
        'min_delivery_time' => rand(1, 999),
        'max_delivery_time' => rand(1, 999),
        'restock_time' => rand(1, 999),
        'purchase_steps' => rand(1, 999),
        'min_purchase' => rand(1, 999),
        'max_purchase' => rand(1, 999),
        'seo_keywords' => Str::random(),
        'posting_account' => Str::random(),
        'warning_stock_amount' => rand(1, 999),
        'is_active' => rand(0, 1),
        'is_highlight' => rand(0, 1),
        'is_bundle' => false,
        'is_service' => false,
        'is_shipping_free' => rand(0, 1),
        'has_serial_numbers' => rand(0, 1),
        'is_nos' => rand(0, 1),
        'is_active_export_to_web_shop' => rand(0, 1),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/products', $product);
    $response->assertStatus(200);

    $responseProduct = json_decode($response->getContent())->data;
    $dbProduct = Product::query()
        ->whereKey($responseProduct->id)
        ->first();

    expect($dbProduct->id)->toEqual($product['id']);
    expect($dbProduct->name)->toEqual($product['name']);
    expect($dbProduct->vat_rate_id)->toEqual($product['vat_rate_id']);
    expect($dbProduct->unit_id)->toEqual($product['unit_id']);
    expect($dbProduct->purchase_unit_id)->toEqual($product['purchase_unit_id']);
    expect($dbProduct->reference_unit_id)->toEqual($product['reference_unit_id']);
    expect($dbProduct->product_number)->toEqual($product['product_number']);
    expect($dbProduct->description)->toEqual($product['description']);
    expect($dbProduct->weight_gram)->toEqual($product['weight_gram']);
    expect($dbProduct->dimension_length_mm)->toEqual($product['dimension_length_mm']);
    expect($dbProduct->dimension_width_mm)->toEqual($product['dimension_width_mm']);
    expect($dbProduct->dimension_height_mm)->toEqual($product['dimension_height_mm']);
    expect($dbProduct->ean)->toEqual($product['ean']);
    expect($dbProduct->min_delivery_time)->toEqual($product['min_delivery_time']);
    expect($dbProduct->max_delivery_time)->toEqual($product['max_delivery_time']);
    expect($dbProduct->restock_time)->toEqual($product['restock_time']);
    expect($dbProduct->purchase_steps)->toEqual($product['purchase_steps']);
    expect($dbProduct->min_purchase)->toEqual($product['min_purchase']);
    expect($dbProduct->max_purchase)->toEqual($product['max_purchase']);
    expect($dbProduct->seo_keywords)->toEqual($product['seo_keywords']);
    expect($dbProduct->posting_account)->toEqual($product['posting_account']);
    expect($dbProduct->warning_stock_amount)->toEqual($product['warning_stock_amount']);
    expect($dbProduct->is_active)->toEqual($product['is_active']);
    expect($dbProduct->is_highlight)->toEqual($product['is_highlight']);
    expect($dbProduct->is_bundle)->toEqual($product['is_bundle']);
    expect($dbProduct->is_service)->toEqual($product['is_service']);
    expect($dbProduct->is_shipping_free)->toEqual($product['is_shipping_free']);
    expect($dbProduct->has_serial_numbers)->toEqual($product['has_serial_numbers']);
    expect($dbProduct->is_nos)->toEqual($product['is_nos']);
    expect($dbProduct->is_active_export_to_web_shop)->toEqual($product['is_active_export_to_web_shop']);

    expect($dbProduct->bundleProducts()->exists())->toBeFalse();
});

test('update products', function (): void {
    $products = [
        [
            'id' => $this->products[0]->id,
            'name' => Str::random(),
        ],
        [
            'id' => $this->products[1]->id,
            'name' => Str::random(),
        ],
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/products', $products);
    $response->assertStatus(200);

    $responses = json_decode($response->getContent())->data->items;

    expect($responses[0]->data->id)->toEqual($products[0]['id']);
    expect($responses[0]->data->name)->toEqual($products[0]['name']);
    expect($responses[0]->data->parent_id)->toEqual($this->products[0]->parent_id);
    expect($responses[0]->data->vat_rate_id)->toEqual($this->products[0]->vat_rate_id);
    expect($responses[0]->data->unit_id)->toEqual($this->products[0]->unit_id);
    expect($responses[0]->data->purchase_unit_id)->toEqual($this->products[0]->purchase_unit_id);
    expect($responses[0]->data->reference_unit_id)->toEqual($this->products[0]->reference_unit_id);
    expect($responses[0]->data->product_number)->toEqual($this->products[0]->product_number);
    expect($responses[0]->data->description)->toEqual($this->products[0]->description);
    expect($responses[0]->data->ean)->toEqual($this->products[0]->ean);
    expect($responses[0]->data->min_delivery_time)->toEqual($this->products[0]->min_delivery_time);
    expect($responses[0]->data->max_delivery_time)->toEqual($this->products[0]->max_delivery_time);
    expect($responses[0]->data->restock_time)->toEqual($this->products[0]->restock_time);
    expect($responses[0]->data->purchase_steps)->toEqual($this->products[0]->purchase_steps);
    expect($responses[0]->data->min_purchase)->toEqual($this->products[0]->min_purchase);
    expect($responses[0]->data->max_purchase)->toEqual($this->products[0]->max_purchase);
    expect($responses[0]->data->seo_keywords)->toEqual($this->products[0]->seo_keywords);
    expect($responses[0]->data->posting_account)->toEqual($this->products[0]->posting_account);
    expect($responses[0]->data->warning_stock_amount)->toEqual($this->products[0]->warning_stock_amount);
    expect($responses[0]->data->is_highlight)->toEqual($this->products[0]->is_highlight);
    expect($responses[0]->data->is_bundle)->toEqual($this->products[0]->is_bundle);
    expect($responses[0]->data->is_service)->toEqual($this->products[0]->is_service);
    expect($responses[0]->data->is_shipping_free)->toEqual($this->products[0]->is_shipping_free);
    expect($responses[0]->data->has_serial_numbers)->toEqual($this->products[0]->has_serial_numbers);
    expect($responses[0]->data->is_nos)->toEqual($this->products[0]->is_nos);
    expect($responses[0]->data->is_active_export_to_web_shop)->toEqual($this->products[0]->is_active_export_to_web_shop);
    expect($responses[1]->data->id)->toEqual($products[1]['id']);
    expect($responses[1]->data->name)->toEqual($products[1]['name']);
    expect($responses[1]->data->parent_id)->toEqual($this->products[1]->parent_id);
    expect($responses[1]->data->vat_rate_id)->toEqual($this->products[1]->vat_rate_id);
    expect($responses[1]->data->unit_id)->toEqual($this->products[1]->unit_id);
    expect($responses[1]->data->purchase_unit_id)->toEqual($this->products[1]->purchase_unit_id);
    expect($responses[1]->data->reference_unit_id)->toEqual($this->products[1]->reference_unit_id);
    expect($responses[1]->data->product_number)->toEqual($this->products[1]->product_number);
    expect($responses[1]->data->description)->toEqual($this->products[1]->description);
    expect($responses[1]->data->ean)->toEqual($this->products[1]->ean);
    expect($responses[1]->data->min_delivery_time)->toEqual($this->products[1]->min_delivery_time);
    expect($responses[1]->data->max_delivery_time)->toEqual($this->products[1]->max_delivery_time);
    expect($responses[1]->data->restock_time)->toEqual($this->products[1]->restock_time);
    expect($responses[1]->data->purchase_steps)->toEqual($this->products[1]->purchase_steps);
    expect($responses[1]->data->min_purchase)->toEqual($this->products[1]->min_purchase);
    expect($responses[1]->data->max_purchase)->toEqual($this->products[1]->max_purchase);
    expect($responses[1]->data->seo_keywords)->toEqual($this->products[1]->seo_keywords);
    expect($responses[1]->data->posting_account)->toEqual($this->products[1]->posting_account);
    expect($responses[1]->data->warning_stock_amount)->toEqual($this->products[1]->warning_stock_amount);
    expect($responses[1]->data->is_highlight)->toEqual($this->products[1]->is_highlight);
    expect($responses[1]->data->is_bundle)->toEqual($this->products[1]->is_bundle);
    expect($responses[1]->data->is_service)->toEqual($this->products[1]->is_service);
    expect($responses[1]->data->is_shipping_free)->toEqual($this->products[1]->is_shipping_free);
    expect($responses[1]->data->has_serial_numbers)->toEqual($this->products[1]->has_serial_numbers);
    expect($responses[1]->data->is_nos)->toEqual($this->products[1]->is_nos);
    expect($responses[1]->data->is_active_export_to_web_shop)->toEqual($this->products[1]->is_active_export_to_web_shop);
});

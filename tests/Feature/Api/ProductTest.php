<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Client;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class ProductTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $clients;

    private Collection $products;

    private Collection $vatRates;

    private Collection $units;

    private Collection $productProperties;

    private Collection $productOptions;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_get_product()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/products/' . $this->products[0]->id);
        $response->assertStatus(200);

        $product = json_decode($response->getContent())->data;

        $this->assertEquals($this->products[0]->id, $product->id);
        $this->assertEquals($this->products[0]->name, $product->name);
        $this->assertEquals($this->products[0]->description, $product->description);
        $this->assertEquals($this->products[0]->vat_rate_id, $product->vat_rate_id);
        $this->assertEquals($this->products[0]->unit_id, $product->unit_id);
    }

    public function test_get_product_product_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/products/' . $this->products[2]->id + 10000);
        $response->assertStatus(404);
    }

    public function test_get_products()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/products');
        $response->assertStatus(200);

        $products = json_decode($response->getContent())->data;

        $this->assertEquals($products->data[0]->id, $this->products[0]->id);
        $this->assertEquals($products->data[0]->name, $this->products[0]->name);
        $this->assertEquals($products->data[0]->description, $this->products[0]->description);
        $this->assertEquals($products->data[0]->vat_rate_id, $this->products[0]->vat_rate_id);
        $this->assertEquals($products->data[0]->unit_id, $this->products[0]->unit_id);
    }

    public function test_create_product()
    {
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

        $this->assertEquals($product['name'], $dbProduct->name);
        $this->assertEquals($product['clients'], $dbProduct->clients->pluck('id')->toArray());
        $this->assertNull($dbProduct->parent_id);
        $this->assertNull($dbProduct->vat_rate_id);
        $this->assertNull($dbProduct->unit_id);
        $this->assertNull($dbProduct->purchase_unit_id);
        $this->assertNull($dbProduct->reference_unit_id);
        $this->assertNotNull($dbProduct->product_number);
        $this->assertEmpty($dbProduct->description);
        $this->assertEmpty($dbProduct->weight_gram);
        $this->assertEmpty($dbProduct->dimension_length_mm);
        $this->assertEmpty($dbProduct->dimension_width_mm);
        $this->assertEmpty($dbProduct->dimension_height_mm);
        $this->assertNull($dbProduct->ean);
        $this->assertNull($dbProduct->min_devlivery_time);
        $this->assertNull($dbProduct->max_devlivery_time);
        $this->assertNull($dbProduct->restock_time);
        $this->assertNull($dbProduct->purchase_steps);
        $this->assertNull($dbProduct->min_purchase);
        $this->assertNull($dbProduct->max_purchase);
        $this->assertNull($dbProduct->seo_keywords);
        $this->assertNull($dbProduct->posting_account);
        $this->assertNull($dbProduct->warning_stock_amount);
        $this->assertTrue($dbProduct->is_active);
        $this->assertFalse($dbProduct->is_highlight);
        $this->assertFalse($dbProduct->is_bundle);
        $this->assertFalse($dbProduct->is_service);
        $this->assertFalse($dbProduct->is_shipping_free);
        $this->assertFalse($dbProduct->is_required_product_serial_number);
        $this->assertFalse($dbProduct->is_nos);
        $this->assertFalse($dbProduct->is_active_export_to_web_shop);
    }

    public function test_create_product_maximum()
    {
        $product = [
            'name' => Str::random(),
            'parent_id' => $this->products[0]->id,
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
            'is_bundle' => true,
            'is_service' => false,
            'is_shipping_free' => rand(0, 1),
            'is_required_product_serial_number' => rand(0, 1),
            'is_nos' => rand(0, 1),
            'is_active_export_to_web_shop' => rand(0, 1),
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

        $this->assertEquals($product['parent_id'], $dbProduct->parent_id);
        $this->assertEquals($product['vat_rate_id'], $dbProduct->vat_rate_id);
        $this->assertEquals($product['unit_id'], $dbProduct->unit_id);
        $this->assertEquals($product['purchase_unit_id'], $dbProduct->purchase_unit_id);
        $this->assertEquals($product['reference_unit_id'], $dbProduct->reference_unit_id);
        $this->assertEquals($product['product_number'], $dbProduct->product_number);
        $this->assertEquals($product['description'], $dbProduct->description);
        $this->assertEquals($product['weight_gram'], $dbProduct->weight_gram);
        $this->assertEquals($product['dimension_length_mm'], $dbProduct->dimension_length_mm);
        $this->assertEquals($product['dimension_width_mm'], $dbProduct->dimension_width_mm);
        $this->assertEquals($product['dimension_height_mm'], $dbProduct->dimension_height_mm);
        $this->assertEquals($product['ean'], $dbProduct->ean);
        $this->assertEquals($product['min_delivery_time'], $dbProduct->min_delivery_time);
        $this->assertEquals($product['max_delivery_time'], $dbProduct->max_delivery_time);
        $this->assertEquals($product['restock_time'], $dbProduct->restock_time);
        $this->assertEquals($product['purchase_steps'], $dbProduct->purchase_steps);
        $this->assertEquals($product['min_purchase'], $dbProduct->min_purchase);
        $this->assertEquals($product['max_purchase'], $dbProduct->max_purchase);
        $this->assertEquals($product['seo_keywords'], $dbProduct->seo_keywords);
        $this->assertEquals($product['posting_account'], $dbProduct->posting_account);
        $this->assertEquals($product['warning_stock_amount'], $dbProduct->warning_stock_amount);
        $this->assertEquals($product['is_active'], $dbProduct->is_active);
        $this->assertEquals($product['is_highlight'], $dbProduct->is_highlight);
        $this->assertEquals($product['is_bundle'], $dbProduct->is_bundle);
        $this->assertEquals($product['is_service'], $dbProduct->is_service);
        $this->assertEquals($product['is_shipping_free'], $dbProduct->is_shipping_free);
        $this->assertEquals(
            $product['is_required_product_serial_number'],
            $dbProduct->is_required_product_serial_number
        );
        $this->assertEquals($product['is_nos'], $dbProduct->is_nos);
        $this->assertEquals($product['is_active_export_to_web_shop'], $dbProduct->is_active_export_to_web_shop);

        $this->assertEquals($product['clients'], $dbProduct->clients->pluck('id')->toArray());
        $this->assertTrue($dbProduct->bundleProducts()
            ->wherePivot('bundle_product_id', $product['bundle_products'][0]['id'])
            ->wherePivot('count', $product['bundle_products'][0]['count'])
            ->exists()
        );
        $this->assertTrue($dbProduct->bundleProducts()
            ->wherePivot('bundle_product_id', $product['bundle_products'][1]['id'])
            ->wherePivot('count', $product['bundle_products'][1]['count'])
            ->exists()
        );
    }

    public function test_create_product_validation_fails()
    {
        $product = [
            'name' => rand(1, 999),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/products', $product);
        $response->assertStatus(422);
    }

    public function test_update_product()
    {
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
            'is_required_product_serial_number' => rand(0, 1),
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

        $this->assertEquals($product['id'], $dbProduct->id);
        $this->assertEquals($product['name'], $dbProduct->name);
        $this->assertEquals($product['vat_rate_id'], $dbProduct->vat_rate_id);
        $this->assertEquals($product['unit_id'], $dbProduct->unit_id);
        $this->assertEquals($product['purchase_unit_id'], $dbProduct->purchase_unit_id);
        $this->assertEquals($product['reference_unit_id'], $dbProduct->reference_unit_id);
        $this->assertEquals($product['product_number'], $dbProduct->product_number);
        $this->assertEquals($product['description'], $dbProduct->description);
        $this->assertEquals($product['weight_gram'], $dbProduct->weight_gram);
        $this->assertEquals($product['dimension_length_mm'], $dbProduct->dimension_length_mm);
        $this->assertEquals($product['dimension_width_mm'], $dbProduct->dimension_width_mm);
        $this->assertEquals($product['dimension_height_mm'], $dbProduct->dimension_height_mm);
        $this->assertEquals($product['ean'], $dbProduct->ean);
        $this->assertEquals($product['min_delivery_time'], $dbProduct->min_delivery_time);
        $this->assertEquals($product['max_delivery_time'], $dbProduct->max_delivery_time);
        $this->assertEquals($product['restock_time'], $dbProduct->restock_time);
        $this->assertEquals($product['purchase_steps'], $dbProduct->purchase_steps);
        $this->assertEquals($product['min_purchase'], $dbProduct->min_purchase);
        $this->assertEquals($product['max_purchase'], $dbProduct->max_purchase);
        $this->assertEquals($product['seo_keywords'], $dbProduct->seo_keywords);
        $this->assertEquals($product['posting_account'], $dbProduct->posting_account);
        $this->assertEquals($product['warning_stock_amount'], $dbProduct->warning_stock_amount);
        $this->assertEquals($product['is_active'], $dbProduct->is_active);
        $this->assertEquals($product['is_highlight'], $dbProduct->is_highlight);
        $this->assertEquals($product['is_bundle'], $dbProduct->is_bundle);
        $this->assertEquals($product['is_service'], $dbProduct->is_service);
        $this->assertEquals($product['is_shipping_free'], $dbProduct->is_shipping_free);
        $this->assertEquals($product['is_required_product_serial_number'],
            $dbProduct->is_required_product_serial_number);
        $this->assertEquals($product['is_nos'],
            $dbProduct->is_nos);
        $this->assertEquals($product['is_active_export_to_web_shop'], $dbProduct->is_active_export_to_web_shop);

        $this->assertFalse($dbProduct->bundleProducts()->exists());
    }

    public function test_update_products()
    {
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

        $responses = json_decode($response->getContent())->responses;

        $this->assertEquals($products[0]['id'], $responses[0]->data->id);
        $this->assertEquals($products[0]['name'], $responses[0]->data->name);
        $this->assertEquals($this->products[0]->parent_id, $responses[0]->data->parent_id);
        $this->assertEquals($this->products[0]->vat_rate_id, $responses[0]->data->vat_rate_id);
        $this->assertEquals($this->products[0]->unit_id, $responses[0]->data->unit_id);
        $this->assertEquals($this->products[0]->purchase_unit_id, $responses[0]->data->purchase_unit_id);
        $this->assertEquals($this->products[0]->reference_unit_id, $responses[0]->data->reference_unit_id);
        $this->assertEquals($this->products[0]->product_number, $responses[0]->data->product_number);
        $this->assertEquals($this->products[0]->description, $responses[0]->data->description);
        $this->assertEquals($this->products[0]->ean, $responses[0]->data->ean);
        $this->assertEquals($this->products[0]->min_delivery_time, $responses[0]->data->min_delivery_time);
        $this->assertEquals($this->products[0]->max_delivery_time, $responses[0]->data->max_delivery_time);
        $this->assertEquals($this->products[0]->restock_time, $responses[0]->data->restock_time);
        $this->assertEquals($this->products[0]->purchase_steps, $responses[0]->data->purchase_steps);
        $this->assertEquals($this->products[0]->min_purchase, $responses[0]->data->min_purchase);
        $this->assertEquals($this->products[0]->max_purchase, $responses[0]->data->max_purchase);
        $this->assertEquals($this->products[0]->seo_keywords, $responses[0]->data->seo_keywords);
        $this->assertEquals($this->products[0]->posting_account, $responses[0]->data->posting_account);
        $this->assertEquals($this->products[0]->warning_stock_amount, $responses[0]->data->warning_stock_amount);
        $this->assertEquals($this->products[0]->is_highlight, $responses[0]->data->is_highlight);
        $this->assertEquals($this->products[0]->is_bundle, $responses[0]->data->is_bundle);
        $this->assertEquals($this->products[0]->is_service, $responses[0]->data->is_service);
        $this->assertEquals($this->products[0]->is_shipping_free, $responses[0]->data->is_shipping_free);
        $this->assertEquals($this->products[0]->is_required_product_serial_number,
            $responses[0]->data->is_required_product_serial_number);
        $this->assertEquals($this->products[0]->is_nos,
            $responses[0]->data->is_nos);
        $this->assertEquals($this->products[0]->is_active_export_to_web_shop,
            $responses[0]->data->is_active_export_to_web_shop);
        $this->assertEquals($products[1]['id'], $responses[1]->data->id);
        $this->assertEquals($products[1]['name'], $responses[1]->data->name);
        $this->assertEquals($this->products[1]->parent_id, $responses[1]->data->parent_id);
        $this->assertEquals($this->products[1]->vat_rate_id, $responses[1]->data->vat_rate_id);
        $this->assertEquals($this->products[1]->unit_id, $responses[1]->data->unit_id);
        $this->assertEquals($this->products[1]->purchase_unit_id, $responses[1]->data->purchase_unit_id);
        $this->assertEquals($this->products[1]->reference_unit_id, $responses[1]->data->reference_unit_id);
        $this->assertEquals($this->products[1]->product_number, $responses[1]->data->product_number);
        $this->assertEquals($this->products[1]->description, $responses[1]->data->description);
        $this->assertEquals($this->products[1]->ean, $responses[1]->data->ean);
        $this->assertEquals($this->products[1]->min_delivery_time, $responses[1]->data->min_delivery_time);
        $this->assertEquals($this->products[1]->max_delivery_time, $responses[1]->data->max_delivery_time);
        $this->assertEquals($this->products[1]->restock_time, $responses[1]->data->restock_time);
        $this->assertEquals($this->products[1]->purchase_steps, $responses[1]->data->purchase_steps);
        $this->assertEquals($this->products[1]->min_purchase, $responses[1]->data->min_purchase);
        $this->assertEquals($this->products[1]->max_purchase, $responses[1]->data->max_purchase);
        $this->assertEquals($this->products[1]->seo_keywords, $responses[1]->data->seo_keywords);
        $this->assertEquals($this->products[1]->posting_account, $responses[1]->data->posting_account);
        $this->assertEquals($this->products[1]->warning_stock_amount, $responses[1]->data->warning_stock_amount);
        $this->assertEquals($this->products[1]->is_highlight, $responses[1]->data->is_highlight);
        $this->assertEquals($this->products[1]->is_bundle, $responses[1]->data->is_bundle);
        $this->assertEquals($this->products[1]->is_service, $responses[1]->data->is_service);
        $this->assertEquals($this->products[1]->is_shipping_free, $responses[1]->data->is_shipping_free);
        $this->assertEquals($this->products[1]->is_required_product_serial_number,
            $responses[1]->data->is_required_product_serial_number);
        $this->assertEquals($this->products[1]->is_nos,
            $responses[1]->data->is_nos);
        $this->assertEquals($this->products[1]->is_active_export_to_web_shop,
            $responses[1]->data->is_active_export_to_web_shop);
    }

    public function test_delete_product()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/products/' . $this->products[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(Product::query()->whereKey($this->products[0]->id)->exists());
    }

    public function test_delete_product_product_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/products/' . ++$this->products[2]->id);
        $response->assertStatus(404);
    }

    public function test_delete_product_product_has_children()
    {
        $this->products[1]->parent_id = $this->products[2]->id;
        $this->products[1]->save();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/products/' . $this->products[2]->id);
        $response->assertStatus(423);
    }
}

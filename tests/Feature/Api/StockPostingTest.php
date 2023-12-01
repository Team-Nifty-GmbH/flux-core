<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class StockPostingTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $warehouses;

    private Collection $products;

    private Collection $stockPostings;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id,
            'client_id' => $this->dbClient->id,
            'is_main_address' => false,
        ]);

        $this->warehouses = Warehouse::factory()->count(3)->create([
            'address_id' => $address->id,
        ]);

        $this->products = Product::factory()->count(3)->create([
            'client_id' => $this->dbClient->id,
        ]);

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
    }

    public function test_get_stock_posting()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/stock-postings/' . $this->stockPostings[0]->id);
        $response->assertStatus(200);

        $stockPosting = json_decode($response->getContent())->data;

        $this->assertEquals($this->stockPostings[0]->id, $stockPosting->id);
        $this->assertEquals($this->stockPostings[0]->stock, $stockPosting->stock);
        $this->assertEquals($this->stockPostings[0]->posting, $stockPosting->posting);
        $this->assertEquals($this->stockPostings[0]->product_id, $stockPosting->product_id);
        $this->assertEquals($this->stockPostings[0]->warehouse_id, $stockPosting->warehouse_id);
        $this->assertEquals($this->stockPostings[0]->description, $stockPosting->description);
    }

    public function test_get_stock_posting_stock_posting_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/stock-postings/' . Str::uuid());
        $response->assertStatus(404);
    }

    public function test_get_stock_postings()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/stock-postings');
        $response->assertStatus(200);

        $stockPostings = json_decode($response->getContent())->data->data;

        $this->assertEquals($this->stockPostings[0]->id, $stockPostings[0]->id);
        $this->assertEquals($this->stockPostings[0]->stock, $stockPostings[0]->stock);
        $this->assertEquals($this->stockPostings[0]->posting, $stockPostings[0]->posting);
        $this->assertEquals($this->stockPostings[0]->product_id, $stockPostings[0]->product_id);
        $this->assertEquals($this->stockPostings[0]->warehouse_id, $stockPostings[0]->warehouse_id);
        $this->assertEquals($this->stockPostings[0]->description, $stockPostings[0]->description);
    }

    public function test_create_stock_posting()
    {
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

        $this->assertEquals($stockPosting['warehouse_id'], $dbStockPosting->warehouse_id);
        $this->assertEquals($stockPosting['product_id'], $dbStockPosting->product_id);
        $this->assertEquals($stockPosting['posting'], $dbStockPosting->posting);
        $this->assertEquals($stock, $dbStockPosting->stock);
        $this->assertEquals($stockPosting['description'], $dbStockPosting->description);
    }

    public function test_create_stock_posting_empty_stock()
    {
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

        $this->assertEquals($stockPosting['warehouse_id'], $dbStockPosting->warehouse_id);
        $this->assertEquals($stockPosting['product_id'], $dbStockPosting->product_id);
        $this->assertEquals($stockPosting['posting'], $dbStockPosting->posting);
        $this->assertEquals($stockPosting['description'], $dbStockPosting->description);
    }

    public function test_create_stock_posting_validation_fails()
    {
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
    }

    public function test_delete_stock_posting()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/stock-postings/' . $this->stockPostings[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(StockPosting::query()->whereKey($this->stockPostings[0]->id)->exists());
    }

    public function test_delete_stock_posting_stock_posting_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/stock-postings/' . ++$this->stockPostings[2]->id);
        $response->assertStatus(404);
    }
}

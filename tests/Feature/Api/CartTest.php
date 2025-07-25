<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Address;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\OrderType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class CartTest extends BaseSetup
{
    private Collection $carts;

    private Contact $contact;

    private array $permissions;

    private Collection $products;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $this->contact = Contact::factory()->create([
            'client_id' => $dbClient->id,
        ]);

        Address::factory()->create([
            'client_id' => $dbClient->id,
            'contact_id' => $this->contact->id,
        ]);

        $priceList = PriceList::factory()->create();
        $vatRate = VatRate::factory()->create();

        $this->products = Product::factory()->count(3)->create([
            'client_id' => $dbClient->id,
            'vat_rate_id' => $vatRate->id,
        ]);

        $this->carts = Cart::factory()->count(2)->create([
            'authenticatable_type' => morph_alias(User::class),
            'authenticatable_id' => $this->user->id,
            'price_list_id' => $priceList->id,
            'is_watchlist' => false,
        ]);

        $this->carts->push(
            Cart::factory()->create([
                'authenticatable_type' => morph_alias(User::class),
                'authenticatable_id' => $this->user->id,
                'price_list_id' => $priceList->id,
                'is_watchlist' => true,
            ])
        );

        CartItem::factory()->count(2)->create([
            'cart_id' => $this->carts[0]->id,
            'product_id' => $this->products[0]->id,
            'vat_rate_id' => $vatRate->id,
        ]);

        OrderType::factory()->create([
            'order_type_enum' => 'order',
            'client_id' => $dbClient->id,
        ]);

        $this->user->clients()->attach($dbClient->id);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.carts.{id}.get'),
            'index' => Permission::findOrCreate('api.carts.get'),
            'create' => Permission::findOrCreate('api.carts.post'),
            'update' => Permission::findOrCreate('api.carts.put'),
            'delete' => Permission::findOrCreate('api.carts.{id}.delete'),
        ];
    }

    public function test_create_cart(): void
    {
        $cart = [
            'authenticatable_type' => morph_alias(User::class),
            'authenticatable_id' => $this->user->id,
            'is_watchlist' => false,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/carts', $cart);
        $response->assertStatus(201);

        $responseCart = json_decode($response->getContent())->data;
        $dbCart = Cart::query()
            ->whereKey($responseCart->id)
            ->first();

        $this->assertNotEmpty($dbCart);
        $this->assertEquals($cart['authenticatable_type'], $dbCart->authenticatable_type);
        $this->assertEquals($cart['authenticatable_id'], $dbCart->authenticatable_id);
        $this->assertEquals($cart['is_watchlist'], $dbCart->is_watchlist);

        // Validate all model properties with expected values
        $this->assertNotNull($dbCart->id);
        $this->assertNotNull($dbCart->created_at);
        $this->assertNotNull($dbCart->updated_at);
        $this->assertNotEmpty($dbCart->session_id);
        $this->assertFalse($dbCart->is_portal_public); // Default value
        $this->assertFalse($dbCart->is_public); // Default value
    }

    public function test_create_cart_validation_fails(): void
    {
        $cart = [
            'authenticatable_type' => 'invalid_type',
            'authenticatable_id' => 999999,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/carts', $cart);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'authenticatable_type',
            'authenticatable_id',
        ]);
    }

    public function test_create_cart_with_price_list(): void
    {
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
        $response->assertStatus(201);

        $responseCart = json_decode($response->getContent())->data;
        $dbCart = Cart::query()
            ->whereKey($responseCart->id)
            ->first();

        $this->assertNotEmpty($dbCart);
        $this->assertEquals($cart['price_list_id'], $dbCart->price_list_id);
        $this->assertEquals($cart['is_watchlist'], $dbCart->is_watchlist);

        // Validate all model properties with expected values
        $this->assertNotNull($dbCart->id);
        $this->assertNotNull($dbCart->created_at);
        $this->assertNotNull($dbCart->updated_at);
        $this->assertNotEmpty($dbCart->session_id);
        $this->assertFalse($dbCart->is_portal_public); // Default value
        $this->assertFalse($dbCart->is_public); // Default value
        $this->assertEquals($cart['authenticatable_type'], $dbCart->authenticatable_type);
        $this->assertEquals($cart['authenticatable_id'], $dbCart->authenticatable_id);
    }

    public function test_delete_cart(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/carts/' . $this->carts[0]->id);
        $response->assertStatus(204);

        $cart = $this->carts[0]->fresh();
        $this->assertNotNull($cart->deleted_at);
    }

    public function test_delete_cart_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/carts/' . (Cart::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_cart(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/carts/' . $this->carts[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCart = $json->data;

        $this->assertNotEmpty($jsonCart);
        $this->assertEquals($this->carts[0]->id, $jsonCart->id);
        $this->assertEquals($this->carts[0]->authenticatable_type, $jsonCart->authenticatable_type);
        $this->assertEquals($this->carts[0]->authenticatable_id, $jsonCart->authenticatable_id);
        $this->assertEquals($this->carts[0]->is_watchlist, $jsonCart->is_watchlist);
    }

    public function test_get_cart_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/carts/' . (Cart::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_carts(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/carts');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCarts = collect($json->data->data);

        $this->assertGreaterThanOrEqual(3, count($jsonCarts));

        foreach ($this->carts as $cart) {
            $jsonCarts->contains(function ($jsonCart) use ($cart) {
                return $jsonCart->id === $cart->id &&
                    $jsonCart->authenticatable_type === $cart->authenticatable_type &&
                    $jsonCart->authenticatable_id === $cart->authenticatable_id &&
                    $jsonCart->is_watchlist === $cart->is_watchlist;
            });
        }
    }

    public function test_update_cart(): void
    {
        $cart = [
            'id' => $this->carts[0]->id,
            'is_watchlist' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/carts', $cart);
        $response->assertStatus(200);

        $responseCart = json_decode($response->getContent())->data;
        $dbCart = Cart::query()
            ->whereKey($responseCart->id)
            ->first();

        $this->assertNotEmpty($dbCart);
        $this->assertEquals($cart['id'], $dbCart->id);
        $this->assertEquals($cart['is_watchlist'], $dbCart->is_watchlist);

        // Validate all model properties with expected values
        $this->assertNotNull($dbCart->id);
        $this->assertNotNull($dbCart->created_at);
        $this->assertNotNull($dbCart->updated_at);
        $this->assertNotEmpty($dbCart->session_id);
        $this->assertFalse($dbCart->is_portal_public); // Default value
        $this->assertFalse($dbCart->is_public); // Default value
        $this->assertNotNull($dbCart->authenticatable_type);
        $this->assertNotNull($dbCart->authenticatable_id);
    }
}

<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\Client;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;

class ClientTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $clients;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clients = Client::factory()->count(3)->create();

        $this->user->clients()->attach($this->clients->pluck('id')->toArray());

        $this->permissions = [
            'show' => Permission::findOrCreate('api.clients.{id}.get'),
            'index' => Permission::findOrCreate('api.clients.get'),
        ];
    }

    public function test_get_client()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/clients/'.$this->clients[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $client = $json->data;
        $this->assertNotEmpty($client);
        $this->assertEquals($this->clients[0]->id, $client->id);
        $this->assertEquals($this->clients[0]->uuid, $client->uuid);
        $this->assertEquals($this->clients[0]->country_id, $client->country_id);
        $this->assertEquals($this->clients[0]->name, $client->name);
        $this->assertEquals($this->clients[0]->client_code, $client->client_code);
        $this->assertEquals($this->clients[0]->ceo, $client->ceo);
        $this->assertEquals($this->clients[0]->street, $client->street);
        $this->assertEquals($this->clients[0]->city, $client->city);
        $this->assertEquals($this->clients[0]->postcode, $client->postcode);
        $this->assertEquals($this->clients[0]->phone, $client->phone);
        $this->assertEquals($this->clients[0]->fax, $client->fax);
        $this->assertEquals($this->clients[0]->email, $client->email);
        $this->assertEquals($this->clients[0]->website, $client->website);
        $this->assertEquals($this->clients[0]->is_active, $client->is_active);
    }

    public function test_get_client_client_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/clients/'.++$this->clients->last()->id);
        $response->assertStatus(404);
    }

    public function test_get_clients()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/clients');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonClients = collect($json->data->data);

        // Check the amount of test countries.
        $this->assertGreaterThanOrEqual(3, count($jsonClients));

        // Check if controller returns the test countries.
        foreach ($this->clients as $client) {
            $jsonClients->contains(function ($jsonClient) use ($client) {
                return $jsonClient->id === $client->id &&
                    $jsonClient->uuid === $client->uuid &&
                    $jsonClient->country_id === $client->country_id &&
                    $jsonClient->name === $client->name &&
                    $jsonClient->client_code === $client->client_code &&
                    $jsonClient->ceo === $client->ceo &&
                    $jsonClient->street === $client->street &&
                    $jsonClient->city === $client->city &&
                    $jsonClient->postcode === $client->postcode &&
                    $jsonClient->phone === $client->phone &&
                    $jsonClient->fax === $client->fax &&
                    $jsonClient->email === $client->email &&
                    $jsonClient->website === $client->website &&
                    $jsonClient->is_active === $client->is_active &&

                    Carbon::parse($jsonClient->created_at) === Carbon::parse($client->created_at) &&
                    Carbon::parse($jsonClient->updated_at) === Carbon::parse($client->updated_at);
            });
        }
    }
}

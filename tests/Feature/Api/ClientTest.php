<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Models\Client;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->clients = Client::factory()->count(3)->create();

    $this->user->clients()->attach($this->clients->pluck('id')->toArray());

    $this->permissions = [
        'show' => Permission::findOrCreate('api.clients.{id}.get'),
        'index' => Permission::findOrCreate('api.clients.get'),
    ];
});

test('get client', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/clients/' . $this->clients[0]->id);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $client = $json->data;
    expect($client)->not->toBeEmpty();
    expect($client->id)->toEqual($this->clients[0]->id);
    expect($client->uuid)->toEqual($this->clients[0]->uuid);
    expect($client->country_id)->toEqual($this->clients[0]->country_id);
    expect($client->name)->toEqual($this->clients[0]->name);
    expect($client->client_code)->toEqual($this->clients[0]->client_code);
    expect($client->ceo)->toEqual($this->clients[0]->ceo);
    expect($client->street)->toEqual($this->clients[0]->street);
    expect($client->city)->toEqual($this->clients[0]->city);
    expect($client->postcode)->toEqual($this->clients[0]->postcode);
    expect($client->phone)->toEqual($this->clients[0]->phone);
    expect($client->fax)->toEqual($this->clients[0]->fax);
    expect($client->email)->toEqual($this->clients[0]->email);
    expect($client->website)->toEqual($this->clients[0]->website);
    expect($client->is_active)->toEqual($this->clients[0]->is_active);
});

test('get client client not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/clients/' . ++$this->clients->last()->id);
    $response->assertStatus(404);
});

test('get clients', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/clients');
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonClients = collect($json->data->data);

    // Check the amount of test countries.
    expect(count($jsonClients))->toBeGreaterThanOrEqual(3);

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
});

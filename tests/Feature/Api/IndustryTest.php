<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Client;
use FluxErp\Models\Industry;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class IndustryTest extends BaseSetup
{
    private Collection $industries;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $this->industries = Industry::factory()->count(3)->create();

        $this->user->clients()->attach($dbClient->id);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.industries.{id}.get'),
            'index' => Permission::findOrCreate('api.industries.get'),
            'create' => Permission::findOrCreate('api.industries.post'),
            'update' => Permission::findOrCreate('api.industries.put'),
            'delete' => Permission::findOrCreate('api.industries.{id}.delete'),
        ];
    }

    public function test_create_industry(): void
    {
        $industry = [
            'name' => 'Technology',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/industries', $industry);
        $response->assertStatus(201);

        $responseIndustry = json_decode($response->getContent())->data;
        $dbIndustry = Industry::query()
            ->whereKey($responseIndustry->id)
            ->first();

        $this->assertNotEmpty($dbIndustry);
        $this->assertEquals($industry['name'], $dbIndustry->name);
    }

    public function test_create_industry_validation_fails(): void
    {
        $industry = [
            'name' => '',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/industries', $industry);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
        ]);
    }

    public function test_create_industry_with_auto_order(): void
    {
        $industry = [
            'name' => 'Healthcare',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/industries', $industry);
        $response->assertStatus(201);

        $responseIndustry = json_decode($response->getContent())->data;
        $dbIndustry = Industry::query()
            ->whereKey($responseIndustry->id)
            ->first();

        $this->assertNotEmpty($dbIndustry);
        $this->assertEquals($industry['name'], $dbIndustry->name);
        $this->assertIsInt($dbIndustry->order_column);
    }

    public function test_delete_industry(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/industries/' . $this->industries[0]->id);
        $response->assertStatus(204);

        $industry = $this->industries[0]->fresh();
        $this->assertNull($industry);
    }

    public function test_delete_industry_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/industries/' . (Industry::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_industries(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/industries');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonIndustries = collect($json->data->data);

        $this->assertGreaterThanOrEqual(3, count($jsonIndustries));

        foreach ($this->industries as $industry) {
            $jsonIndustries->contains(function ($jsonIndustry) use ($industry) {
                return $jsonIndustry->id === $industry->id &&
                    $jsonIndustry->name === $industry->name;
            });
        }
    }

    public function test_get_industry(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/industries/' . $this->industries[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonIndustry = $json->data;

        $this->assertNotEmpty($jsonIndustry);
        $this->assertEquals($this->industries[0]->id, $jsonIndustry->id);
        $this->assertEquals($this->industries[0]->name, $jsonIndustry->name);
    }

    public function test_get_industry_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/industries/' . (Industry::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_update_industry(): void
    {
        $industry = [
            'id' => $this->industries[0]->id,
            'name' => 'Updated Industry Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/industries', $industry);
        $response->assertStatus(200);

        $responseIndustry = json_decode($response->getContent())->data;
        $dbIndustry = Industry::query()
            ->whereKey($responseIndustry->id)
            ->first();

        $this->assertNotEmpty($dbIndustry);
        $this->assertEquals($industry['id'], $dbIndustry->id);
        $this->assertEquals($industry['name'], $dbIndustry->name);
    }

    public function test_update_industry_maximum(): void
    {
        $industry = [
            'id' => $this->industries[1]->id,
            'name' => 'Fully Updated Industry',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/industries', $industry);
        $response->assertStatus(200);

        $responseIndustry = json_decode($response->getContent())->data;
        $dbIndustry = Industry::query()
            ->whereKey($responseIndustry->id)
            ->first();

        $this->assertNotEmpty($dbIndustry);
        $this->assertEquals($industry['id'], $dbIndustry->id);
        $this->assertEquals($industry['name'], $dbIndustry->name);
        $this->assertIsInt($dbIndustry->order_column);
    }

    public function test_update_industry_validation_fails(): void
    {
        $industry = [
            'id' => $this->industries[0]->id,
            'name' => '',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/industries', $industry);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
        ]);
    }
}

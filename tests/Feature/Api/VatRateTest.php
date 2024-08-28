<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Permission;
use FluxErp\Models\VatRate;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class VatRateTest extends BaseSetup
{
    use DatabaseTransactions, WithFaker;

    private Collection $vatRates;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->vatRates = VatRate::factory()->count(3)->create();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.vat-rates.{id}.get'),
            'index' => Permission::findOrCreate('api.vat-rates.get'),
            'create' => Permission::findOrCreate('api.vat-rates.post'),
            'update' => Permission::findOrCreate('api.vat-rates.put'),
            'delete' => Permission::findOrCreate('api.vat-rates.{id}.delete'),
        ];
    }

    public function test_get_vat_rate()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/vat-rates/' . $this->vatRates[0]->id);
        $response->assertStatus(200);

        $vatRate = json_decode($response->getContent())->data;

        $this->assertEquals($this->vatRates[0]->id, $vatRate->id);
        $this->assertEquals($this->vatRates[0]->name, $vatRate->name);
        $this->assertEquals($this->vatRates[0]->rate_percentage, $vatRate->rate_percentage);
    }

    public function test_get_vat_rate_vat_rate_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/vat-rates/' . $this->vatRates[2]->id + 10000);
        $response->assertStatus(404);
    }

    public function test_get_vat_rates()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/vat-rates');
        $response->assertStatus(200);

        $vatRates = json_decode($response->getContent())->data;

        $this->assertEquals($this->vatRates[0]->id, $vatRates->data[0]->id);
        $this->assertEquals($this->vatRates[0]->name, $vatRates->data[0]->name);
        $this->assertEquals($this->vatRates[0]->rate_percentage, $vatRates->data[0]->rate_percentage);
    }

    public function test_create_vat_rate()
    {
        $vatRate = [
            'name' => Str::random(),
            'rate_percentage' => $this->faker->randomFloat(2, 0.01, 0.99),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/vat-rates', $vatRate);
        $response->assertStatus(201);

        $responseVatRate = json_decode($response->getContent())->data;

        $dbVatRate = VatRate::query()
            ->whereKey($responseVatRate->id)
            ->first();

        $this->assertEquals($vatRate['name'], $dbVatRate->name);
        $this->assertEquals($vatRate['rate_percentage'], $dbVatRate->rate_percentage);
    }

    public function test_create_vat_rate_validation_fails()
    {
        $vatRate = [
            'rate_percentage' => $this->faker->randomFloat(2, 0.01, 0.99),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/vat-rates', $vatRate);
        $response->assertStatus(422);
    }

    public function test_update_vat_rate()
    {
        $vatRate = [
            'id' => $this->vatRates[0]->id,
            'name' => Str::random(),
            'rate_percentage' => $this->faker->randomFloat(2, 0.01, 0.99),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/vat-rates', $vatRate);
        $response->assertStatus(200);

        $responseVatRate = json_decode($response->getContent())->data;

        $dbVatRate = VatRate::query()
            ->whereKey($responseVatRate->id)
            ->first();

        $this->assertEquals($vatRate['id'], $dbVatRate->id);
        $this->assertEquals($vatRate['name'], $dbVatRate->name);
        $this->assertEquals($vatRate['rate_percentage'], $dbVatRate->rate_percentage);
    }

    public function test_update_vat_rate_validation_fails()
    {
        $vatRate = [
            'name' => Str::random(),
            'rate_percentage' => $this->faker->randomFloat(2, 0.01, 0.99),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/vat-rates', $vatRate);
        $response->assertStatus(422);
    }

    public function test_delete_vat_rate()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/vat-rates/' . $this->vatRates[0]->id);

        $response->assertStatus(204);
        $this->assertFalse(VatRate::query()->whereKey($this->vatRates[0]->id)->exists());
    }

    public function test_delete_vat_rate_vat_rate_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/vat-rates/' . ++$this->vatRates[2]->id);
        $response->assertStatus(404);
    }
}

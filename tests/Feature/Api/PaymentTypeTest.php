<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class PaymentTypeTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $paymentTypes;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentTypes = PaymentType::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.payment-types.{id}.get'),
            'index' => Permission::findOrCreate('api.payment-types.get'),
            'create' => Permission::findOrCreate('api.payment-types.post'),
            'update' => Permission::findOrCreate('api.payment-types.put'),
            'delete' => Permission::findOrCreate('api.payment-types.{id}.delete'),
        ];
    }

    public function test_get_payment_type()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/payment-types/' . $this->paymentTypes[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonPaymentType = $json->data;

        // Check if controller returns the test payment type.
        $this->assertNotEmpty($jsonPaymentType);
        $this->assertEquals($this->paymentTypes[0]->id, $jsonPaymentType->id);
        $this->assertEquals($this->paymentTypes[0]->client_id, $jsonPaymentType->client_id);
        $this->assertEquals($this->paymentTypes[0]->name, $jsonPaymentType->name);
        $this->assertEquals($this->paymentTypes[0]->description, $jsonPaymentType->description);
        $this->assertEquals($this->paymentTypes[0]->payment_reminder_days_1, $jsonPaymentType->payment_reminder_days_1);
        $this->assertEquals($this->paymentTypes[0]->payment_reminder_days_2, $jsonPaymentType->payment_reminder_days_2);
        $this->assertEquals($this->paymentTypes[0]->payment_reminder_days_3, $jsonPaymentType->payment_reminder_days_3);
        $this->assertEquals($this->paymentTypes[0]->payment_target, $jsonPaymentType->payment_target);
        $this->assertEquals($this->paymentTypes[0]->payment_discount_target, $jsonPaymentType->payment_discount_target);
        $this->assertEquals($this->paymentTypes[0]->payment_discount_percentage,
            $jsonPaymentType->payment_discount_percentage);
        $this->assertEquals($this->paymentTypes[0]->is_active, $jsonPaymentType->is_active);
        $this->assertEquals(Carbon::parse($this->paymentTypes[0]->created_at),
            Carbon::parse($jsonPaymentType->created_at));
        $this->assertEquals(Carbon::parse($this->paymentTypes[0]->updated_at),
            Carbon::parse($jsonPaymentType->updated_at));
    }

    public function test_get_payment_type_payment_type_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/payment-types/' . ++$this->paymentTypes[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_payment_type_include_not_allowed()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/payment-types/' . $this->paymentTypes[1]->id . '?include=test');
        $response->assertStatus(422);
    }

    public function test_get_payment_types()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/payment-types');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonPaymentTypes = collect($json->data->data);

        // Check the amount of test payment types.
        $this->assertGreaterThanOrEqual(2, count($jsonPaymentTypes));

        // Check if controller returns the test payment types.
        foreach ($this->paymentTypes as $paymentType) {
            $jsonPaymentTypes->contains(function ($jsonPaymentType) use ($paymentType) {
                return $jsonPaymentType->id === $paymentType->id &&
                    $jsonPaymentType->client_id === $paymentType->client_id &&
                    $jsonPaymentType->name === $paymentType->name &&
                    $jsonPaymentType->description === $paymentType->description &&
                    $jsonPaymentType->payment_reminder_days_1 === $paymentType->payment_reminder_days_1 &&
                    $jsonPaymentType->payment_reminder_days_2 === $paymentType->payment_reminder_days_2 &&
                    $jsonPaymentType->payment_reminder_days_3 === $paymentType->payment_reminder_days_3 &&
                    $jsonPaymentType->payment_target === $paymentType->payment_target &&
                    $jsonPaymentType->payment_discount_target === $paymentType->payment_discount_target &&
                    $jsonPaymentType->payment_discount_percentage === $paymentType->payment_discount_percentage &&
                    $jsonPaymentType->is_active === $paymentType->is_active &&
                    Carbon::parse($jsonPaymentType->created_at) === Carbon::parse($paymentType->created_at) &&
                    Carbon::parse($jsonPaymentType->updated_at) === Carbon::parse($paymentType->updated_at);
            });
        }
    }

    public function test_create_payment_type()
    {
        $paymentType = [
            'client_id' => $this->paymentTypes[0]->client_id,
            'name' => 'Payment Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/payment-types', $paymentType);
        $response->assertStatus(201);

        $responsePaymentType = json_decode($response->getContent())->data;
        $dbPaymentType = PaymentType::query()
            ->whereKey($responsePaymentType->id)
            ->first();

        $this->assertNotEmpty($dbPaymentType);
        $this->assertEquals($paymentType['client_id'], $dbPaymentType->client_id);
        $this->assertEquals($paymentType['name'], $dbPaymentType->name);
        $this->assertNull($dbPaymentType->description);
        $this->assertNull($dbPaymentType->payment_reminder_days_1);
        $this->assertNull($dbPaymentType->payment_reminder_days_2);
        $this->assertNull($dbPaymentType->payment_reminder_days_3);
        $this->assertNull($dbPaymentType->payment_target);
        $this->assertNull($dbPaymentType->payment_discount_target);
        $this->assertNull($dbPaymentType->payment_discount_percentage);
        $this->assertTrue($dbPaymentType->is_active);
        $this->assertTrue($this->user->is($dbPaymentType->getCreatedBy()));
        $this->assertTrue($this->user->is($dbPaymentType->getUpdatedBy()));
    }

    public function test_create_payment_type_maximum()
    {
        $paymentType = [
            'client_id' => $this->paymentTypes[0]->client_id,
            'name' => 'Payment Type Name',
            'description' => 'New description text for further information',
            'payment_reminder_days_1' => 42,
            'payment_reminder_days_2' => 42,
            'payment_reminder_days_3' => 42,
            'payment_target' => 42,
            'payment_discount_target' => 42,
            'payment_discount_percentage' => 42 / 100,
            'is_active' => true,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/payment-types', $paymentType);
        $response->assertStatus(201);

        $responsePaymentType = json_decode($response->getContent())->data;
        $dbPaymentType = PaymentType::query()
            ->whereKey($responsePaymentType->id)
            ->first();

        $this->assertNotEmpty($dbPaymentType);
        $this->assertEquals($paymentType['client_id'], $dbPaymentType->client_id);
        $this->assertEquals($paymentType['name'], $dbPaymentType->name);
        $this->assertEquals($paymentType['description'], $dbPaymentType->description);
        $this->assertEquals($paymentType['payment_reminder_days_1'], $dbPaymentType->payment_reminder_days_1);
        $this->assertEquals($paymentType['payment_reminder_days_2'], $dbPaymentType->payment_reminder_days_2);
        $this->assertEquals($paymentType['payment_reminder_days_3'], $dbPaymentType->payment_reminder_days_3);
        $this->assertEquals($paymentType['payment_target'], $dbPaymentType->payment_target);
        $this->assertEquals($paymentType['payment_discount_target'], $dbPaymentType->payment_discount_target);
        $this->assertEquals($paymentType['payment_discount_percentage'], $dbPaymentType->payment_discount_percentage);
        $this->assertEquals($paymentType['is_active'], $dbPaymentType->is_active);
        $this->assertTrue($this->user->is($dbPaymentType->getCreatedBy()));
        $this->assertTrue($this->user->is($dbPaymentType->getUpdatedBy()));
    }

    public function test_create_payment_type_validation_fails()
    {
        $paymentType = [
            'client_id' => 'client_id',
            'name' => 'Payment Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/payment-types', $paymentType);
        $response->assertStatus(422);
    }

    public function test_update_payment_type()
    {
        $paymentType = [
            'id' => $this->paymentTypes[0]->id,
            'name' => 'Payment Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/payment-types', $paymentType);
        $response->assertStatus(200);

        $responsePaymentType = json_decode($response->getContent())->data;
        $dbPaymentType = PaymentType::query()
            ->whereKey($responsePaymentType->id)
            ->first();

        $this->assertNotEmpty($dbPaymentType);
        $this->assertEquals($paymentType['id'], $dbPaymentType->id);
        $this->assertEquals($paymentType['name'], $dbPaymentType->name);
        $this->assertEquals($this->paymentTypes[0]->description, $dbPaymentType->description);
        $this->assertEquals($this->paymentTypes[0]->payment_reminder_days_1, $dbPaymentType->payment_reminder_days_1);
        $this->assertEquals($this->paymentTypes[0]->payment_reminder_days_2, $dbPaymentType->payment_reminder_days_2);
        $this->assertEquals($this->paymentTypes[0]->payment_reminder_days_3, $dbPaymentType->payment_reminder_days_3);
        $this->assertEquals($this->paymentTypes[0]->payment_target, $dbPaymentType->payment_target);
        $this->assertEquals($this->paymentTypes[0]->payment_discount_target, $dbPaymentType->payment_discount_target);
        $this->assertEquals($this->paymentTypes[0]->payment_discount_percentage, $dbPaymentType->payment_discount_percentage);
        $this->assertEquals($this->paymentTypes[0]->is_active, $dbPaymentType->is_active);
        $this->assertTrue($this->user->is($dbPaymentType->getUpdatedBy()));
    }

    public function test_update_payment_type_maximum()
    {
        $paymentType = [
            'id' => $this->paymentTypes[0]->id,
            'name' => 'Payment Type Name',
            'description' => 'New description text for further information',
            'payment_reminder_days_1' => 42,
            'payment_reminder_days_2' => 42,
            'payment_reminder_days_3' => 42,
            'payment_target' => 42,
            'payment_discount_target' => 42,
            'payment_discount_percentage' => 42 / 100,
            'is_active' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/payment-types', $paymentType);
        $response->assertStatus(200);

        $responsePaymentType = json_decode($response->getContent())->data;
        $dbPaymentType = PaymentType::query()
            ->whereKey($responsePaymentType->id)
            ->first();

        $this->assertNotEmpty($dbPaymentType);
        $this->assertEquals($paymentType['id'], $dbPaymentType->id);
        $this->assertEquals($paymentType['name'], $dbPaymentType->name);
        $this->assertEquals($paymentType['description'], $dbPaymentType->description);
        $this->assertEquals($paymentType['payment_reminder_days_1'], $dbPaymentType->payment_reminder_days_1);
        $this->assertEquals($paymentType['payment_reminder_days_2'], $dbPaymentType->payment_reminder_days_2);
        $this->assertEquals($paymentType['payment_reminder_days_3'], $dbPaymentType->payment_reminder_days_3);
        $this->assertEquals($paymentType['payment_target'], $dbPaymentType->payment_target);
        $this->assertEquals($paymentType['payment_discount_target'], $dbPaymentType->payment_discount_target);
        $this->assertEquals($paymentType['payment_discount_percentage'], $dbPaymentType->payment_discount_percentage);
        $this->assertEquals($paymentType['is_active'], $dbPaymentType->is_active);
        $this->assertTrue($this->user->is($dbPaymentType->getUpdatedBy()));
    }

    public function test_update_payment_type_validation_fails()
    {
        $paymentType = [
            'id' => $this->paymentTypes[0]->id,
            'client_id' => 'client_id',
            'name' => 'Payment Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/payment-types', $paymentType);
        $response->assertStatus(422);
    }

    public function test_delete_payment_type()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/payment-types/' . $this->paymentTypes[1]->id);
        $response->assertStatus(204);

        $paymentType = $this->paymentTypes[1]->fresh();
        $this->assertNotNull($paymentType->deleted_at);
        $this->assertTrue($this->user->is($paymentType->getDeletedBy()));
    }

    public function test_delete_payment_type_payment_type_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/payment-types/' . ++$this->paymentTypes[1]->id);
        $response->assertStatus(404);
    }
}

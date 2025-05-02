<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class FormBuilderFormTest extends BaseSetup
{
    use WithFaker;

    private $fromBuilderForms;

    private $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fromBuilderForms = FormBuilderForm::factory()->count(3)->create();

        $this->permissions = [
            'index' => Permission::findOrCreate('api.form-builder.forms.get'),
            'show' => Permission::findOrCreate('api.form-builder.forms.{id}.get'),
            'create' => Permission::findOrCreate('api.form-builder.forms.post'),
            'update' => Permission::findOrCreate('api.form-builder.forms.put'),
            'delete' => Permission::findOrCreate('api.form-builder.forms.{id}.delete'),
        ];
    }

    public function test_create_form_builder_form_success(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $startDate = $this->faker->dateTimeBetween('-10 days', 'now');
        $endDate = Carbon::make($startDate)->addDays(10)->format('Y-m-d H:i:s');

        $payload = [
            'name' => $this->faker->name,
            'description' => $this->faker->text,
            'slug' => $this->faker->slug,
            'is_active' => $this->faker->boolean,
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate,
        ];

        $response = $this->actingAs($this->user)->post('/api/form-builder/forms', $payload);
        $response->assertStatus(201);

        $data = json_decode($response->getContent())->data;

        $db = FormBuilderForm::query()
            ->whereKey($data->id)
            ->first();

        $dbEntry = $payload + ['id' => $data->id];

        $this->assertDatabaseHas('form_builder_forms', $dbEntry);
    }

    public function test_create_form_builder_form_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $startDate = $this->faker->dateTimeBetween('-10 days', 'now');
        $endDate = Carbon::make($startDate)->subDays(10)->toDateTimeString();

        $payload = [
            'description' => $this->faker->text,
            'slug' => $this->faker->slug,
            'is_active' => $this->faker->boolean,
            'start_date' => Carbon::instance($startDate)->toDateTimeString(),
            'end_date' => $endDate,
        ];

        $response = $this->actingAs($this->user)->post('/api/form-builder/forms', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'end_date']);
    }

    public function test_delete_form_builder_form_success(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/form-builder/forms/' . $this->fromBuilderForms[0]->getKey());
        $response->assertStatus(204);

        $this->assertSoftDeleted('form_builder_forms', ['id' => $this->fromBuilderForms[0]->getKey()]);
    }

    public function test_delete_nonexistent_form_builder_form(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->fromBuilderForms->max('id') + 1;

        $response = $this->actingAs($this->user)->delete('/api/form-builder/forms/' . $nonId);
        $response->assertStatus(404);
        $response->assertJson(['exception' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException']);
    }

    public function test_index_form_builder_forms(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/forms');
        $response->assertStatus(200);

        $items = collect($response->json('data.data'));
        $this->assertEquals($this->fromBuilderForms->count(), $items->count());

        foreach ($this->fromBuilderForms as $fromBuilderForm) {
            $this->assertTrue(
                $items->contains(fn ($i) => $i['id'] === $fromBuilderForm->getKey() &&
                    $i['name'] === $fromBuilderForm->name &&
                    $i['description'] === $fromBuilderForm->description &&
                    $i['slug'] === $fromBuilderForm->slug &&
                    $i['is_active'] == $fromBuilderForm->is_active
                )
            );
        }
    }

    public function test_show_form_builder_form(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/forms/' . $this->fromBuilderForms[0]->getKey());
        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals($this->fromBuilderForms[0]->getKey(), $data['id']);
        $this->assertEquals($this->fromBuilderForms[0]->name, $data['name']);
        $this->assertEquals($this->fromBuilderForms[0]->description, $data['description']);
        $this->assertEquals($this->fromBuilderForms[0]->slug, $data['slug']);
        $this->assertEquals($this->fromBuilderForms[0]->is_active, $data['is_active']);
    }

    public function test_show_nonexistent_form_builder_form(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->fromBuilderForms->max('id') + 1;

        $response = $this->actingAs($this->user)->get('/api/form-builder/forms/' . $nonId);
        $response->assertStatus(404);
    }

    public function test_update_form_builder_form_success(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $startDate = $this->faker->dateTimeBetween('-10 days', 'now');
        $endDate = Carbon::make($startDate)->addDays(10)->toDateTimeString();

        $payload = [
            'id' => $this->fromBuilderForms[0]->getKey(),
            'name' => 'New Name',
            'description' => $this->faker->text,
            'slug' => $this->faker->slug,
            'is_active' => $this->faker->boolean,
            'start_date' => Carbon::instance($startDate)->toDateTimeString(),
            'end_date' => $endDate,
        ];

        $response = $this->actingAs($this->user)->put('/api/form-builder/forms', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('form_builder_forms', $payload);
    }

    public function test_update_form_builder_form_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $startDate = $this->faker->dateTimeBetween('-10 days', 'now');
        $endDate = Carbon::make($startDate)->subDays(30)->toDateTimeString();

        $payload = [
            'id' => $this->fromBuilderForms[0]->getKey(),
            'description' => $this->faker->text,
            'slug' => $this->faker->slug,
            'is_active' => $this->faker->boolean,
            'start_date' => Carbon::instance($startDate)->toDateTimeString(),
            'end_date' => $endDate,
        ];

        $response = $this->actingAs($this->user)->put('/api/form-builder/forms', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_date']);
    }
}

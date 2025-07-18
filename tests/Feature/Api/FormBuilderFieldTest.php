<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\FormBuilderTypeEnum;
use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class FormBuilderFieldTest extends BaseSetup
{
    use WithFaker;

    private Collection $formBuilderFields;

    private FormBuilderSection $formBuilderSection;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $formBuilderForm = FormBuilderForm::factory()->create();

        $this->formBuilderSection = FormBuilderSection::factory()->create([
            'form_id' => $formBuilderForm->getKey(),
        ]);

        $this->formBuilderFields = FormBuilderField::factory()->count(3)->create([
            'section_id' => $this->formBuilderSection->getKey(),
            'type' => fn () => $this->faker->randomElement(FormBuilderTypeEnum::values()),
        ]);

        $this->permissions = [
            'index' => Permission::findOrCreate('api.form-builder.fields.get'),
            'show' => Permission::findOrCreate('api.form-builder.fields.{id}.get'),
            'create' => Permission::findOrCreate('api.form-builder.fields.post'),
            'update' => Permission::findOrCreate('api.form-builder.fields.put'),
            'delete' => Permission::findOrCreate('api.form-builder.fields.{id}.delete'),
        ];
    }

    public function test_create_form_builder_field_success(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'section_id' => $this->formBuilderSection->getKey(),
            'name' => $this->faker->name,
            'description' => $this->faker->text,
            'type' => $this->faker->randomElement(FormBuilderTypeEnum::values()),
            'ordering' => $this->faker->numberBetween(1, 10),
        ];

        $response = $this->actingAs($this->user)->post('/api/form-builder/fields', $payload);
        $response->assertStatus(201);

        $data = json_decode($response->getContent())->data;

        $dbEntry = $payload + ['id' => $data->id];

        $this->assertDatabaseHas('form_builder_fields', $dbEntry);
    }

    public function test_create_form_builder_field_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'ordering' => -1,
            'type' => 'not the right type',
        ];

        $response = $this->actingAs($this->user)->post('/api/form-builder/fields', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'ordering', 'type', 'section_id']);
    }

    public function test_delete_form_builder_field_success(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/form-builder/fields/' . $this->formBuilderFields[0]->getKey());
        $response->assertStatus(204);

        $this->assertSoftDeleted('form_builder_fields', ['id' => $this->formBuilderFields[0]->getKey()]);
    }

    public function test_delete_nonexistent_form_builder_field(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->formBuilderFields->max('id') + 1;

        $response = $this->actingAs($this->user)->delete('/api/form-builder/fields/' . $nonId);
        $response->assertStatus(404);
        $response->assertJson(['exception' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException']);
    }

    public function test_index_form_builder_field(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/fields');
        $response->assertStatus(200);

        $items = collect($response->json('data.data'));
        $this->assertEquals($this->formBuilderFields->count(), $items->count());

        foreach ($this->formBuilderFields as $formBuilderField) {
            $this->assertTrue(
                $items->contains(fn ($i) => $i['id'] === $formBuilderField->getKey() &&
                    $i['name'] === $formBuilderField->name &&
                    $i['description'] === $formBuilderField->description &&
                    $i['type'] === $formBuilderField->type &&
                    $i['ordering'] == $formBuilderField->ordering
                )
            );
        }
    }

    public function test_show_form_builder_field(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/fields/' . $this->formBuilderFields[0]->getKey());
        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals($this->formBuilderFields[0]->getKey(), $data['id']);
        $this->assertEquals($this->formBuilderFields[0]->name, $data['name']);
        $this->assertEquals($this->formBuilderFields[0]->description, $data['description']);
        $this->assertEquals($this->formBuilderFields[0]->ordering, $data['ordering']);
        $this->assertEquals($this->formBuilderFields[0]->type, $data['type']);
    }

    public function test_show_nonexistent_form_builder_field(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->formBuilderFields->max('id') + 1;

        $response = $this->actingAs($this->user)->get('/api/form-builder/fields/' . $nonId);
        $response->assertStatus(404);
    }

    public function test_update_form_builder_field_success(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'id' => $this->formBuilderFields[0]->getKey(),
            'name' => 'New Name',
            'description' => 'Test Description',
            'ordering' => 1,
            'type' => FormBuilderTypeEnum::Checkbox->value,
        ];

        $response = $this->actingAs($this->user)->put('/api/form-builder/fields', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('form_builder_fields', $payload);
    }

    public function test_update_form_builder_field_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'id' => $this->formBuilderFields[1]->getKey(),
            'ordering' => -1,
            'type' => 'nonexistent type',
        ];

        $response = $this->actingAs($this->user)->put('/api/form-builder/fields', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ordering', 'type']);
    }
}

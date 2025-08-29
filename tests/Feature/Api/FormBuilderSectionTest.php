<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class FormBuilderSectionTest extends BaseSetup
{
    use WithFaker;

    private Collection $formBuilderSections;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formBuilderForm = FormBuilderForm::factory()->create();

        $this->formBuilderSections = FormBuilderSection::factory()->count(3)->create([
            'form_id' => $this->formBuilderForm->getKey(),
        ]);

        $this->permissions = [
            'index' => Permission::findOrCreate('api.form-builder.sections.get'),
            'show' => Permission::findOrCreate('api.form-builder.sections.{id}.get'),
            'create' => Permission::findOrCreate('api.form-builder.sections.post'),
            'update' => Permission::findOrCreate('api.form-builder.sections.put'),
            'delete' => Permission::findOrCreate('api.form-builder.sections.{id}.delete'),
        ];
    }

    public function test_create_form_builder_section_success(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'form_id' => $this->formBuilderForm->getKey(),
            'name' => $this->faker->name,
            'description' => $this->faker->text,
            'ordering' => $this->faker->numberBetween(1, 10),
            'columns' => $this->faker->numberBetween(1, 12),
        ];

        $response = $this->actingAs($this->user)->post('/api/form-builder/sections', $payload);
        $response->assertStatus(201);

        $data = json_decode($response->getContent())->data;

        $dbEntry = $payload + ['id' => $data->id];

        $this->assertDatabaseHas('form_builder_sections', $dbEntry);
    }

    public function test_create_form_builder_section_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'ordering' => -1,
            'columns' => -1,
        ];

        $response = $this->actingAs($this->user)->post('/api/form-builder/sections', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'form_id', 'ordering', 'columns']);
    }

    public function test_delete_form_builder_section_success(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/form-builder/sections/' . $this->formBuilderSections[0]->getKey());
        $response->assertStatus(204);

        $this->assertSoftDeleted('form_builder_sections', ['id' => $this->formBuilderSections[0]->getKey()]);
    }

    public function test_delete_nonexistent_form_builder_section(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->formBuilderSections->max('id') + 1;

        $response = $this->actingAs($this->user)->delete('/api/form-builder/sections/' . $nonId);
        $response->assertStatus(404);
        $response->assertJson(['exception' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException']);
    }

    public function test_index_form_builder_section(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/sections');
        $response->assertStatus(200);

        $items = collect($response->json('data.data'));
        $this->assertEquals($this->formBuilderSections->count(), $items->count());

        foreach ($this->formBuilderSections as $formBuilderSection) {
            $this->assertTrue(
                $items->contains(fn ($i) => $i['id'] === $formBuilderSection->getKey() &&
                    $i['name'] === $formBuilderSection->name &&
                    $i['description'] === $formBuilderSection->description &&
                    $i['ordering'] === $formBuilderSection->ordering &&
                    $i['columns'] == $formBuilderSection->columns
                )
            );
        }
    }

    public function test_show_form_builder_section(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/sections/' . $this->formBuilderSections[0]->getKey());
        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals($this->formBuilderSections[0]->getKey(), $data['id']);
        $this->assertEquals($this->formBuilderSections[0]->name, $data['name']);
        $this->assertEquals($this->formBuilderSections[0]->description, $data['description']);
        $this->assertEquals($this->formBuilderSections[0]->ordering, $data['ordering']);
        $this->assertEquals($this->formBuilderSections[0]->columns, $data['columns']);
    }

    public function test_show_nonexistent_form_builder_section(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->formBuilderSections->max('id') + 1;

        $response = $this->actingAs($this->user)->get('/api/form-builder/sections/' . $nonId);
        $response->assertStatus(404);
    }

    public function test_update_form_builder_section_success(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'id' => $this->formBuilderSections[0]->getKey(),
            'name' => 'New Name',
            'description' => 'Test Description',
            'ordering' => 1,
            'columns' => 1,
        ];

        $response = $this->actingAs($this->user)->put('/api/form-builder/sections', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('form_builder_sections', $payload);
    }

    public function test_update_form_builder_section_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'id' => $this->formBuilderSections[0]->getKey(),
            'ordering' => -1,
            'columns' => -1,
        ];

        $response = $this->actingAs($this->user)->put('/api/form-builder/sections', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ordering', 'columns']);
    }
}

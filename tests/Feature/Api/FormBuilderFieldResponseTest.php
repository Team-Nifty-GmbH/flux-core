<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\FormBuilderTypeEnum;
use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class FormBuilderFieldResponseTest extends BaseSetup
{
    use WithFaker;

    private FormBuilderField $formBuilderField;

    private Collection $formBuilderFieldResponses;

    private FormBuilderForm $formBuilderForm;

    private FormBuilderResponse $formBuilderResponse;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formBuilderForm = FormBuilderForm::factory()->create();

        $formBuilderSection = FormBuilderSection::factory()->create([
            'form_id' => $this->formBuilderForm->getKey(),
        ]);

        $this->formBuilderResponse = FormBuilderResponse::factory()->create([
            'user_id' => $this->user->getKey(),
            'form_id' => $this->formBuilderForm->getKey(),
        ]);

        $this->formBuilderField = FormBuilderField::factory()->create([
            'section_id' => $formBuilderSection->getKey(),
            'type' => $this->faker->randomElement(FormBuilderTypeEnum::values()),
        ]);

        $this->formBuilderFieldResponses = FormBuilderFieldResponse::factory()->count(3)->create([
            'form_id' => $this->formBuilderForm->getKey(),
            'field_id' => $this->formBuilderField->getKey(),
            'response_id' => $this->formBuilderResponse->getKey(),
            'response' => $this->faker->realText,
        ]);

        $this->permissions = [
            'index' => Permission::findOrCreate('api.form-builder.fields-responses.get'),
            'show' => Permission::findOrCreate('api.form-builder.fields-responses.{id}.get'),
            'create' => Permission::findOrCreate('api.form-builder.fields-responses.post'),
            'update' => Permission::findOrCreate('api.form-builder.fields-responses.put'),
            'delete' => Permission::findOrCreate('api.form-builder.fields-responses.{id}.delete'),
        ];
    }

    public function test_create_form_builder_field_response_success(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'form_id' => $this->formBuilderForm->getKey(),
            'field_id' => $this->formBuilderField->getKey(),
            'response_id' => $this->formBuilderResponse->getKey(),
            'response' => $this->faker->text,
        ];

        $response = $this->actingAs($this->user)->post('/api/form-builder/fields-responses', $payload);
        $response->assertStatus(201);

        $data = json_decode($response->getContent())->data;

        $dbEntry = $payload + ['id' => $data->id];

        $this->assertDatabaseHas('form_builder_field_responses', $dbEntry);
    }

    public function test_create_form_builder_field_response_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [];

        $response = $this->actingAs($this->user)->post('/api/form-builder/fields-responses', $payload);
        $response->assertStatus(422);
        $response = $response['data']['items'][0]['errors'];

        $this->assertArrayHasKey('form_id', $response);
        $this->assertArrayHasKey('field_id', $response);
        $this->assertArrayHasKey('response_id', $response);
        $this->assertArrayHasKey('response', $response);
    }

    public function test_delete_form_builder_field_response_success(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/form-builder/fields-responses/' . $this->formBuilderFieldResponses[0]->getKey());
        $response->assertStatus(204);

        $this->assertSoftDeleted('form_builder_field_responses', ['id' => $this->formBuilderFieldResponses[0]->getKey()]);
    }

    public function test_delete_nonexistent_form_builder_field_response(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->formBuilderFieldResponses->max('id') + 1;

        $response = $this->actingAs($this->user)->delete('/api/form-builder/fields-responses/' . $nonId);
        $response->assertStatus(404);
        $response->assertJson(['exception' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException']);
    }

    public function test_index_form_builder_field_response(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/fields-responses');
        $response->assertStatus(200);

        $items = collect($response->json('data.data'));
        $this->assertEquals($this->formBuilderFieldResponses->count(), $items->count());

        foreach ($this->formBuilderFieldResponses as $formBuilderFieldResponse) {
            $this->assertTrue(
                $items->contains(fn ($i) => $i['id'] === $formBuilderFieldResponse->getKey() &&
                    $i['form_id'] === $formBuilderFieldResponse->form_id &&
                    $i['field_id'] === $formBuilderFieldResponse->field_id &&
                    $i['response_id'] === $formBuilderFieldResponse->response_id &&
                    $i['response'] == $formBuilderFieldResponse->response
                )
            );
        }
    }

    public function test_show_form_builder_field_response(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/fields-responses/' . $this->formBuilderFieldResponses[0]->getKey());
        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals($this->formBuilderFieldResponses[0]->getKey(), $data['id']);
        $this->assertEquals($this->formBuilderFieldResponses[0]->form_id, $data['form_id']);
        $this->assertEquals($this->formBuilderFieldResponses[0]->field_id, $data['field_id']);
        $this->assertEquals($this->formBuilderFieldResponses[0]->response_id, $data['response_id']);
        $this->assertEquals($this->formBuilderFieldResponses[0]->response, $data['response']);
    }

    public function test_show_nonexistent_form_builder_field_response(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->formBuilderFieldResponses->max('id') + 1;

        $response = $this->actingAs($this->user)->get('/api/form-builder/fields-responses/' . $nonId);
        $response->assertStatus(404);
    }

    public function test_update_form_builder_field_response_success(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'id' => $this->formBuilderFieldResponses[0]->getKey(),
            'response' => 'New Response',
        ];

        $response = $this->actingAs($this->user)->put('/api/form-builder/fields-responses', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('form_builder_field_responses', $payload);
    }

    public function test_update_form_builder_field_response_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'id' => $this->formBuilderFieldResponses[1]->getKey(),
        ];

        $response = $this->actingAs($this->user)->put('/api/form-builder/fields-responses', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['response']);
    }
}

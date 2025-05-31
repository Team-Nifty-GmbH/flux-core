<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class FormBuilderResponseTest extends BaseSetup
{
    use WithFaker;

    private FormBuilderForm $formBuilderForm;

    private Collection $formBuilderResponses;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formBuilderForm = FormBuilderForm::factory()->create();

        $this->formBuilderResponses = FormBuilderResponse::factory()->count(3)->create([
            'user_id' => $this->user->getKey(),
            'form_id' => $this->formBuilderForm->getKey(),
        ]);

        $this->permissions = [
            'index' => Permission::findOrCreate('api.form-builder.responses.get'),
            'show' => Permission::findOrCreate('api.form-builder.responses.{id}.get'),
            'create' => Permission::findOrCreate('api.form-builder.responses.post'),
            'update' => Permission::findOrCreate('api.form-builder.responses.put'),
            'delete' => Permission::findOrCreate('api.form-builder.responses.{id}.delete'),
        ];
    }

    public function test_create_form_builder_response_success(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [
            'user_id' => $this->user->getKey(),
            'form_id' => $this->formBuilderForm->getKey(),
        ];

        $response = $this->actingAs($this->user)->post('/api/form-builder/responses', $payload);
        $response->assertStatus(201);

        $data = json_decode($response->getContent())->data;

        $dbEntry = $payload + ['id' => $data->id];

        $this->assertDatabaseHas('form_builder_responses', $dbEntry);
    }

    public function test_create_form_builder_response_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $payload = [];

        $response = $this->actingAs($this->user)->post('/api/form-builder/responses', $payload);
        $response->assertStatus(422);
        $response = $response['data']['items'][0]['errors'];
        $this->assertArrayHasKey('form_id', $response);
        $this->assertArrayHasKey('user_id', $response);
    }

    public function test_delete_form_builder_response_success(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/form-builder/responses/' . $this->formBuilderResponses[0]->getKey());
        $response->assertStatus(204);

        $this->assertSoftDeleted('form_builder_responses', ['id' => $this->formBuilderResponses[0]->getKey()]);
    }

    public function test_delete_nonexistent_form_builder_response(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->formBuilderResponses->max('id') + 1;

        $response = $this->actingAs($this->user)->delete('/api/form-builder/responses/' . $nonId);
        $response->assertStatus(404);
        $response->assertJson(['exception' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException']);
    }

    public function test_index_form_builder_response(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/responses');
        $response->assertStatus(200);

        $items = collect($response->json('data.data'));
        $this->assertEquals($this->formBuilderResponses->count(), $items->count());

        foreach ($this->formBuilderResponses as $formBuilderResponse) {
            $this->assertTrue(
                $items->contains(fn ($i) => $i['id'] === $formBuilderResponse->getKey() &&
                    $i['form_id'] === $formBuilderResponse->form_id &&
                    $i['user_id'] === $formBuilderResponse->user_id
                )
            );
        }
    }

    public function test_show_form_builder_response(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/form-builder/responses/' . $this->formBuilderResponses[0]->getKey());
        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals($this->formBuilderResponses[0]->getKey(), $data['id']);
        $this->assertEquals($this->formBuilderResponses[0]->form_id, $data['form_id']);
        $this->assertEquals($this->formBuilderResponses[0]->user_id, $data['user_id']);
    }

    public function test_show_nonexistent_form_builder_response(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->formBuilderResponses->max('id') + 1;

        $response = $this->actingAs($this->user)->get('/api/form-builder/responses/' . $nonId);
        $response->assertStatus(404);
    }
}

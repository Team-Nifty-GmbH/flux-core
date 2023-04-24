<?php

namespace FluxErp\Tests\Feature;

use Carbon\Carbon;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Presentation;
use FluxErp\Models\PrintData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class PresentationTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $presentations;

    private Model $contact;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contact = Contact::factory()->create([
            'client_id' => Client::query()->first()->id,
        ]);

        $this->presentations = Presentation::factory()->count(3)->create([
            'model_id' => $this->contact->id,
            'model_type' => Contact::class,
            'is_public' => true,
        ]);
        File::makeDirectory(resource_path('views/print'), 0777, true, true);
        File::put(resource_path('views/print/test-print.blade.php'), '<div>{{$test_variable}}</div>');

        $this->permissions = [
            'show' => Permission::findOrCreate('api.presentations.{id}.get'),
            'preview' => Permission::findOrCreate('api.presentations.{id}.get.preview'),
            'pdf' => Permission::findOrCreate('api.presentations.{id}.get.pdf'),
            'index' => Permission::findOrCreate('api.presentations.get'),
            'create' => Permission::findOrCreate('api.presentations.post'),
            'update' => Permission::findOrCreate('api.presentations.put'),
            'delete' => Permission::findOrCreate('api.presentations.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_presentation()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/presentations/' . $this->presentations->first()->id);
        $response->assertStatus(200);

        $presentation = json_decode($response->getContent())->data;

        $this->assertNotEmpty($presentation);
        $this->assertEquals($this->presentations->first()->id, $presentation->id);
        $this->assertEquals($this->presentations->first()->name, $presentation->name);
        $this->assertEquals($this->presentations->first()->notice, $presentation->notice);
        $this->assertEquals($this->presentations->first()->model_id, $presentation->model_id);
        $this->assertEquals($this->presentations->first()->model_type, $presentation->model_type);
        $this->assertEquals(Carbon::parse($this->presentations->first()->created_at),
            Carbon::parse($presentation->created_at));
        $this->assertEquals(Carbon::parse($this->presentations->first()->updated_at),
            Carbon::parse($presentation->updated_at));
    }

    public function test_get_presentation_presentation_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/presentations/' . ++$this->presentations->last()->id);
        $response->assertStatus(404);
    }

    public function test_get_presentations()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/presentations');
        $response->assertStatus(200);

        $presentations = json_decode($response->getContent())->data->data;

        $this->assertNotEmpty($presentations);
        $dbPresentation = Presentation::query()->where('id', $presentations[0]->id)->first();

        $this->assertEquals($dbPresentation->id, $presentations[0]->id);
        $this->assertEquals($dbPresentation->notice, $presentations[0]->notice);
        $this->assertEquals($dbPresentation->name, $presentations[0]->name);
        $this->assertEquals($dbPresentation->model_type, $presentations[0]->model_type);
        $this->assertEquals($dbPresentation->model_id, $presentations[0]->model_id);
    }

    public function test_get_presentations_with_search()
    {
        $queryParams = '?search=' . $this->presentations[0]->id;

        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/presentations' . $queryParams);
        $response->assertStatus(200);

        $presentations = json_decode($response->getContent())->data->data;
        $this->assertNotEmpty($presentations);
        $referencePresentation = Presentation::query()->where('id', $presentations[0]->id)->first();
        $this->assertEquals($referencePresentation->id, $presentations[0]->id);
        $this->assertEquals($referencePresentation->notice, $presentations[0]->notice);
        $this->assertEquals($referencePresentation->name, $presentations[0]->name);
        $this->assertEquals($referencePresentation->model_type, $presentations[0]->model_type);
        $this->assertEquals($referencePresentation->model_id, $presentations[0]->model_id);
        $this->assertEquals(Carbon::parse($referencePresentation->created_at),
            Carbon::parse($presentations[0]->created_at));
        $this->assertEquals(Carbon::parse($referencePresentation->updated_at),
            Carbon::parse($presentations[0]->updated_at));
    }

    public function test_create_presentation()
    {
        $presentation = [
            'name' => 'Random Presentation Name',
            'notice' => 'presentation notice',
            'model_type' => Contact::class,
            'model_id' => $this->contact->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/presentations', $presentation);
        $response->assertStatus(201);

        $responsePresentation = json_decode($response->getContent())->data;
        $dbPresentation = Presentation::query()
            ->whereKey($responsePresentation->id)
            ->first();
        $this->assertNotEmpty($dbPresentation);
        $this->assertEquals($responsePresentation->name, $dbPresentation->name);
        $this->assertEquals($responsePresentation->notice, $dbPresentation->notice);
        $this->assertEquals($responsePresentation->model_id, $dbPresentation->model_id);
        $this->assertEquals($responsePresentation->model_type, $dbPresentation->model_type);
    }

    public function test_create_presentation_with_additional_column()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Presentation::class,
        ]);

        $presentation = [
            'name' => 'Random Presentation Name',
            'notice' => 'presentation notice',
            $additionalColumn->name => 'Testvalue for this column',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/presentations', $presentation);
        $response->assertStatus(201);

        $responsePresentation = json_decode($response->getContent())->data;
        $dbPresentation = Presentation::query()
            ->whereKey($responsePresentation->id)
            ->first();
        $this->assertNotEmpty($dbPresentation);
        $this->assertNull($dbPresentation->model_type);
        $this->assertNull($dbPresentation->model_id);
        $this->assertEquals($responsePresentation->name, $dbPresentation->name);
        $this->assertEquals($responsePresentation->notice, $dbPresentation->notice);

        $this->assertEquals($presentation[$additionalColumn->name], $responsePresentation->{$additionalColumn->name});
        $this->assertEquals($presentation[$additionalColumn->name], $dbPresentation->{$additionalColumn->name});
    }

    public function test_create_presentation_with_additional_column_predefined_values()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Presentation::class,
            'values' => [0, 1, 2, 3, 4, 5],
        ]);

        $presentation = [
            'name' => 'Random Presentation Name',
            'notice' => 'presentation notice',
            $additionalColumn->name => $additionalColumn->values[3],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/presentations', $presentation);
        $response->assertStatus(201);

        $responsePresentation = json_decode($response->getContent())->data;
        $dbPresentation = Presentation::query()
            ->whereKey($responsePresentation->id)
            ->first();
        $this->assertNotEmpty($dbPresentation);
        $this->assertEquals($presentation['name'], $dbPresentation->name);
        $this->assertEquals($presentation['notice'], $dbPresentation->notice);

        $this->assertEquals($presentation[$additionalColumn->name], $responsePresentation->{$additionalColumn->name});
        $this->assertEquals($presentation[$additionalColumn->name], $dbPresentation->{$additionalColumn->name});
    }

    public function test_create_presentation_validation_fails()
    {
        $presentation = [
            'name' => 12345,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/presentations', $presentation);
        $response->assertStatus(422);
    }

    public function test_create_presentation_additional_column_validation_fails()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Presentation::class,
            'values' => [0, 1, 2, 3, 4, 5],
        ]);

        $presentation = [
            'name' => 'Random Category Name',
            'notice' => 'random notice',
            $additionalColumn->name => 23947,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/presentations', $presentation);
        $response->assertStatus(422);
    }

    public function test_create_presentation_model_type_not_found()
    {
        $presentation = [
            'name' => 'Random Presentation Name',
            'notice' => 'presentation notice',
            'model_type' => 'TestModel',
            'model_id' => $this->contact->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/presentations', $presentation);
        $response->assertStatus(404);
    }

    public function test_create_presentation_model_instance_not_found()
    {
        $presentation = [
            'name' => 'Random Presentation Name',
            'notice' => 'presentation notice',
            'model_type' => Contact::class,
            'model_id' => ++$this->contact->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/presentations', $presentation);
        $response->assertStatus(404);
    }

    public function test_update_presentation()
    {
        $presentation = [
            'id' => $this->presentations->first()->id,
            'notice' => 'random notice change',
            'name' => 'Random presentation Name change',
            'model_type' => Contact::class,
            'model_id' => $this->contact->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/presentations', $presentation);
        $response->assertStatus(200);

        $responsePresentation = json_decode($response->getContent())->data;
        $dbPresentation = Presentation::query()
            ->whereKey($responsePresentation->id)
            ->first();
        $this->assertNotEmpty($dbPresentation);
        $this->assertEquals($presentation['id'], $dbPresentation->id);
        $this->assertEquals($presentation['notice'], $dbPresentation->notice);
        $this->assertEquals($presentation['name'], $dbPresentation->name);
        $this->assertEquals($presentation['model_type'], $dbPresentation->model_type);
        $this->assertEquals($presentation['model_id'], $dbPresentation->model_id);
    }

    public function test_update_presentation_with_additional_column()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Presentation::class,
        ]);

        $this->presentations->first()->saveMeta($additionalColumn->name, 'Original Value');

        $presentation = [
            'id' => $this->presentations->first()->id,
            'name' => 'Random Category Name',
            'notice' => 'random presentation notice',
            $additionalColumn->name => 'Testvalue for this column',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/presentations', $presentation);
        $response->assertStatus(200);

        $responsePresentation = json_decode($response->getContent());
        $dbPresentation = Presentation::query()
            ->whereKey($responsePresentation->id)
            ->first();
        $this->assertNotEmpty($dbPresentation);
        $this->assertEquals($presentation['id'], $dbPresentation->id);
        $this->assertEquals($this->presentations->first()->model_type, $dbPresentation->model_type);
        $this->assertEquals($this->presentations->first()->model_id, $dbPresentation->model_id);
        $this->assertEquals($presentation['notice'], $dbPresentation->notice);
        $this->assertEquals($presentation['name'], $dbPresentation->name);

        $this->assertEquals(
            $presentation[$additionalColumn->name],
            $responsePresentation->data->{$additionalColumn->name}
        );
        $this->assertEquals($presentation[$additionalColumn->name], $dbPresentation->{$additionalColumn->name});
    }

    public function test_update_presentation_validation_fails()
    {
        $presentation = [
            'id' => $this->presentations->first()->id,
            'notice' => 'random notice',
            'model_type' => 'notValid',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/presentations', $presentation);
        $response->assertStatus(422);
    }

    public function test_update_presentation_presentation_not_found()
    {
        $presentation = [
            'id' => ++$this->presentations->last()->id,
            'name' => $this->presentations->last()->name,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/presentations', $presentation);
        $response->assertStatus(422);
    }

    public function test_update_presentation_model_type_not_found()
    {
        $presentation = [
            'id' => $this->presentations[0]->id,
            'name' => 'Random Presentation Name',
            'notice' => 'presentation notice',
            'model_type' => 'TestModel',
            'model_id' => $this->contact->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/presentations', $presentation);
        $response->assertStatus(404);
    }

    public function test_update_presentation_model_instance_not_found()
    {
        $presentation = [
            'id' => $this->presentations[0]->id,
            'name' => 'Random Presentation Name',
            'notice' => 'presentation notice',
            'model_type' => Contact::class,
            'model_id' => ++$this->contact->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/presentations', $presentation);
        $response->assertStatus(404);
    }

    public function test_delete_presentation()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/presentations/' . $this->presentations->first()->id);
        $response->assertStatus(204);

        $this->assertFalse(Presentation::query()->whereKey($this->presentations->first()->id)->exists());
    }

    public function test_delete_presentation_presentation_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/presentations/' . ++$this->presentations->last()->id);
        $response->assertStatus(404);
    }

    public function test_delete_presentation_with_additional_columns()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Presentation::class,
        ]);

        $presentation = [
            'name' => 'Random Presentation Name',
            'notice' => 'presentation notice',
            $additionalColumn->name => 'Testvalue for this column',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/presentations', $presentation);
        $response->assertStatus(201);

        $id = json_decode($response->getContent())->data->id;

        $delete = $this->actingAs($this->user)
            ->delete('/api/presentations/' . $id);
        $delete->assertStatus(204);

        $this->assertFalse(Presentation::query()->whereKey($id)->exists());
    }

    public function test_show_html()
    {
        $this->user->givePermissionTo($this->permissions['preview']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/presentations/' . $this->presentations->first()->id . '/preview');

        $response->assertStatus(200);

        $this->assertEquals(
            'flux::print.print',
            $response->getOriginalContent()->getName()
        );
    }

    public function test_show_html_view_not_found()
    {
        $this->user->givePermissionTo($this->permissions['preview']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/presentations/' . ++$this->presentations->last()->id . '/preview');
        $response->assertStatus(404);
    }

    public function test_get_pdf()
    {
        $this->user->givePermissionTo($this->permissions['pdf']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/presentations/' . $this->presentations->first()->id . '/pdf');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_get_pdf_array_key_not_defined()
    {
        $this->user->givePermissionTo($this->permissions['pdf']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/presentations/' . ++$this->presentations->last()->id . '/pdf');
        $response->assertStatus(404);
    }

    public function test_show_html_public_presentation_not_public()
    {
        $presentation = Presentation::factory()->create([
            'model_id' => $this->contact->id,
            'model_type' => Contact::class,
            'is_public' => false,
        ]);

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/presentation/public/' . $presentation->uuid);
        $response->assertStatus(404);
    }

    public function test_show_html_public()
    {
        $presentation = Presentation::factory()->create([
            'model_id' => $this->contact->id,
            'model_type' => Contact::class,
            'is_public' => true,
        ]);

        PrintData::factory()->create([
            'model_id' => $presentation->id,
            'is_public' => true,
            'model_type' => Presentation::class,
            'view' => 'flux::print.test-print',
            'data' => ['test_variable' => 'test-public'],
        ]);

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/presentation/public/' . $presentation->uuid);
        $response->assertStatus(200);

        $this->assertEquals(
            'flux::print.print',
            $response->getOriginalContent()->getName()
        );
    }

    public function test_show_html_public_view_not_found()
    {
        $presentation = Presentation::factory()->create([
            'model_id' => $this->contact->id,
            'model_type' => Contact::class,
            'is_public' => true,
        ]);

        PrintData::factory()->create([
            'model_id' => $presentation->id,
            'is_public' => true,
            'model_type' => Presentation::class,
            'view' => 'print.fake-print',
            'data' => ['test_variable' => 'test-public'],
        ]);

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/presentation/public/' . $presentation->uuid);
        $response->assertStatus(404);
    }
}

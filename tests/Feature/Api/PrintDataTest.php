<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Permission;
use FluxErp\Models\Presentation;
use FluxErp\Models\PrintData;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class PrintDataTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $printData;

    private Model $printDataPublic;

    private Model $presentation;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->printDataPublic = PrintData::factory()->create([
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test-public'],
            'is_public' => true,
        ]);

        $this->printData = PrintData::factory()->count(3)->create([
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
        ]);

        $this->presentation = Presentation::factory()->create();

        File::makeDirectory(resource_path('views/print'), recursive: true, force: true);
        File::put(resource_path('views/print/test-print.blade.php'), '<div>{{$test_variable}}</div>');

        $this->permissions = [
            'show' => Permission::findOrCreate('api.print.{id}.get'),
            'preview' => Permission::findOrCreate('api.print.{id}.preview.get'),
            'pdf' => Permission::findOrCreate('api.print.{id}.pdf.get'),
            'views' => Permission::findOrCreate('api.print.views.{path?}.get'),
            'index' => Permission::findOrCreate('api.print.get'),
            'create' => Permission::findOrCreate('api.print.post'),
            'update' => Permission::findOrCreate('api.print.put'),
            'delete' => Permission::findOrCreate('api.print.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_print_data()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/print/' . $this->printData[0]->id);
        $response->assertStatus(200);

        $responseData = json_decode($response->getContent())->data;

        $this->assertNotEmpty($responseData);
        $this->assertEquals($this->printData[0]->id, $responseData->id);
        $this->assertNull($this->printData[0]->model_type);
        $this->assertNull($this->printData[0]->model_id);
        $this->assertEquals($this->printData[0]->view, $responseData->view);
        $this->assertNull($this->printData[0]->template_name);
        $this->assertEquals($this->printData[0]->is_public, $responseData->is_public);
        $this->assertEquals($this->printData[0]->is_template, $responseData->is_template);
    }

    public function test_get_print_data_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/print/' . ++$this->printData->last()->id);
        $response->assertStatus(404);
    }

    public function test_get_print_data_preview()
    {
        $this->user->givePermissionTo($this->permissions['preview']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/print/' . $this->printData->last()->id . '/preview');
        $response->assertStatus(200);

        $response->assertSee('test');
    }

    public function test_get_print_data_preview_not_found()
    {
        $this->user->givePermissionTo($this->permissions['preview']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/print/' . ++$this->printData->last()->id . '/preview');
        $response->assertStatus(404);
    }

    public function test_get_print_data_pdf()
    {
        $this->user->givePermissionTo($this->permissions['pdf']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/print/' . $this->printData->last()->id . '/pdf');
        $response->assertStatus(200);

        $response->assertDownload();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_get_print_data_pdf_view_not_found()
    {
        $this->printData[2]->view = 'invalid-view';
        $this->printData[2]->save();

        $this->user->givePermissionTo($this->permissions['pdf']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/print/' . $this->printData->last()->id . '/pdf');
        $response->assertStatus(404);
    }

    public function test_get_print_data_pdf_not_found()
    {
        $this->user->givePermissionTo($this->permissions['pdf']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/print/' . ++$this->printData->last()->id . '/pdf');
        $response->assertStatus(404);
    }

    public function test_get_print_views()
    {
        $this->user->givePermissionTo($this->permissions['views']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/print/views');
        $response->assertStatus(200);

        $responseData = json_decode($response->getContent())->data;

        $this->assertIsArray($responseData);

        $lastItem = $responseData[count($responseData) - 1];
        $this->assertEquals(str_replace('print.', '', $this->printData[0]->view), $lastItem->name);
        $this->assertEquals($this->printData[0]->view, $lastItem->view);
    }

    public function test_get_print_views_directory_not_found()
    {
        $response = $this->actingAs($this->user)->get('/api/print/' . Str::random());
        $response->assertStatus(404);
    }

    public function test_get_print_data_public_url()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/print/' . $this->printDataPublic->id);
        $response->assertStatus(200);

        $responseData = json_decode($response->getContent())->data;

        $this->assertNotEmpty($responseData);
        $this->assertTrue(property_exists($responseData, 'url_public'));

        $response = $this->get('/print/public/' . basename($responseData->url_public));
        $response->assertStatus(200);

        $response->assertSee('test-public');
    }

    public function test_get_print_data_public_url_fail()
    {
        $response = $this->actingAs($this->user)->get('/print/public/' . Str::random());
        $response->assertStatus(404);
    }

    public function test_create_print_data()
    {
        $printData = [
            'store' => true,
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(201);

        $responseData = json_decode($response->getContent());
        $this->assertIsObject($responseData->data);
        $this->assertNull($responseData->data->url_public);
        $this->assertEquals($printData['view'], $responseData->data->view);
        $this->assertFalse($responseData->data->is_public);
        $this->assertFalse($responseData->data->is_template);
        $this->assertNull($responseData->data->template_name);
        $this->assertNull($responseData->data->model_id);
        $this->assertNull($responseData->data->model_type);
        $this->assertIsInt($responseData->data->id);
        $this->assertEquals(1, $responseData->data->sort);
    }

    public function test_create_print_data_with_template_ids()
    {
        $printData = [
            'store' => true,
            'template_ids' => [$this->printData[0]->id],
            'data' => ['test_variable' => 'test'],
            'model_type' => 'Presentation',
            'model_id' => $this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(201);

        $responseData = json_decode($response->getContent());
        $this->assertIsObject($responseData->data);
        $this->assertNull($responseData->data->url_public);
        $this->assertFalse($responseData->data->is_public);
        $this->assertFalse($responseData->data->is_template);
        $this->assertNull($responseData->data->template_name);
        $this->assertIsInt($responseData->data->id);
        $this->assertEquals(1, $responseData->data->sort);
    }

    public function test_create_print_data_with_template_ids_validation_fails()
    {
        $printData = [
            'store' => false,
            'template_ids' => [$this->printData[0]->id],
            'model_type' => 'Presentation',
            'model_id' => $this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(422);
    }

    public function test_create_print_data_with_template_ids_print_data_not_found()
    {
        $printData = [
            'store' => true,
            'template_ids' => [++$this->printData[2]->id],
            'data' => ['test_variable' => 'test'],
            'model_type' => 'Presentation',
            'model_id' => $this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(404);
    }

    public function test_create_print_data_validation_fails()
    {
        $printDataArray = [
            [
                'view' => null,
                'data' => ['test_variable' => 'test'],
            ],
            [
                'store' => 'test',
                'view' => 'name',
            ],
            [
                'store' => true,
                'view' => 'name',
                'data' => null,
            ],
            [
                'store' => true,
                'view' => 'print.test-print',
                'data' => ['test_variable' => 'test'],
                'model_type' => 'test',
            ],
            [
                'store' => true,
                'view' => 'print.test-print',
                'data' => ['test_variable' => 'test'],
                'model_id' => 'test',
            ],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        foreach ($printDataArray as $printData) {
            $response = $this->actingAs($this->user)->post('/api/print/', $printData);
            $response->assertStatus(422);
        }
    }

    public function test_create_with_morph()
    {
        $printData = [
            'store' => true,
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
            'model_type' => 'Presentation',
            'model_id' => $this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(201);

        $responseData = json_decode($response->getContent());

        $this->assertEquals(get_class($this->presentation), $responseData->data->model_type);
        $this->assertEquals($printData['model_id'], $responseData->data->model_id);
        $this->assertEquals(1, $responseData->data->sort);
    }

    public function test_create_with_morph_model_type_not_found()
    {
        $printData = [
            'store' => true,
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
            'model_type' => 'FakeModel',
            'model_id' => $this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(404);
    }

    public function test_create_with_morph_model_instance_not_found()
    {
        $printData = [
            'store' => true,
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
            'model_type' => 'Presentation',
            'model_id' => ++$this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(404);
    }

    public function test_create_with_sort_last_item()
    {
        $printData = [
            'store' => true,
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
            'sort' => 999,
            'model_type' => 'Presentation',
            'model_id' => $this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(201);

        $responseData = json_decode($response->getContent());

        $this->assertEquals(get_class($this->presentation), $responseData->data->model_type);
        $this->assertEquals($printData['model_id'], $responseData->data->model_id);
        $this->assertEquals(1, $responseData->data->sort);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);

        $response->assertStatus(201);

        $responseData = json_decode($response->getContent());

        $this->assertEquals(get_class($this->presentation), $responseData->data->model_type);
        $this->assertEquals($printData['model_id'], $responseData->data->model_id);
        $this->assertEquals(2, $responseData->data->sort);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);

        $response->assertStatus(201);

        $responseData = json_decode($response->getContent());

        $this->assertEquals(get_class($this->presentation), $responseData->data->model_type);
        $this->assertEquals($printData['model_id'], $responseData->data->model_id);
        $this->assertEquals(3, $responseData->data->sort);

        $printData['sort'] = 2;
        $response = $this->actingAs($this->user)->post('/api/print/', $printData);

        $response->assertStatus(201);

        $responseData = json_decode($response->getContent());

        $this->assertEquals(get_class($this->presentation), $responseData->data->model_type);
        $this->assertEquals($printData['model_id'], $responseData->data->model_id);
        $this->assertEquals(2, $responseData->data->sort);

        $printData = PrintData::query()
            ->where('model_type', get_class($this->presentation))
            ->where('model_id', $this->presentation->id)
            ->orderBy('sort')
            ->get();
        foreach ($printData as $index => $print) {
            $this->assertEquals((int) $index + 1, $print->sort);
        }
    }

    public function test_create_print_data_pdf_response()
    {
        $printData = [
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(200);

        $response->assertDownload();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_create_print_data_preview()
    {
        $printData = [
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
            'preview' => true,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/print/', $printData);
        $response->assertStatus(200);

        $response->assertSee('test');
    }

    public function test_update_print_data_sort()
    {
        $printData = [
            'id' => null,
            'store' => true,
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
            'model_type' => 'Presentation',
            'model_id' => $this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        for ($i = 1; $i <= 5; $i++) {
            $printData['sort'] = $i + 1;

            $response = $this->actingAs($this->user)->post('/api/print/', $printData);
            $response->assertStatus(201);

            $responseData = json_decode($response->getContent());

            $this->assertEquals($i, $responseData->data->sort);
            $ids[] = $responseData->data->id;
        }

        $printDataDb = PrintData::query()->whereIn('id', $ids)->where('sort', 2)->first();
        $printDataDbMoved = PrintData::query()->whereIn('id', $ids)->where('sort', 4)->first();
        $printData['sort'] = 4;
        $printData['id'] = $printDataDb->id;

        $response = $this->actingAs($this->user)->put('/api/print/', $printData);
        $response->assertStatus(200);

        $responseData = json_decode($response->getContent());

        $sortSum = PrintData::query()->whereIn('id', $ids)->sum('sort');

        $this->assertEquals(4, $responseData->data->sort);
        $this->assertEquals(3, PrintData::query()->whereKey($printDataDbMoved->id)->first()->sort);
        $this->assertEquals(15, $sortSum);
    }

    public function test_update_print_data()
    {
        $printData = [
            'id' => $this->printData[0]->id,
            'is_public' => true,
            'is_template' => true,
            'template_name' => 'template_name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/print', $printData);
        $response->assertStatus(200);

        $responseData = json_decode($response->getContent());

        $this->assertNotNull($responseData->url);
        $this->assertIsObject($responseData->data);
        $this->assertNotNull($responseData->data->url_public);
        $this->assertEquals($printData['is_public'], $responseData->data->is_public);
        $this->assertEquals($printData['is_template'], $responseData->data->is_template);
        $this->assertEquals($printData['template_name'], $responseData->data->template_name);
    }

    public function test_update_print_data_model_type_not_found()
    {
        $printData = [
            'id' => $this->printData[0]->id,
            'store' => true,
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
            'model_type' => 'ModelTypeNotFound',
            'model_id' => $this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/print/', $printData);
        $response->assertStatus(404);
    }

    public function test_update_print_data_model_instance_not_found()
    {
        $printData = [
            'id' => $this->printData[0]->id,
            'store' => true,
            'view' => 'print.test-print',
            'data' => ['test_variable' => 'test'],
            'model_type' => 'Presentation',
            'model_id' => ++$this->presentation->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/print/', $printData);
        $response->assertStatus(404);
    }

    public function test_update_print_data_not_found()
    {
        $printData = [
            'id' => ++$this->printData->last()->id,
            'is_public' => true,
            'is_template' => true,
            'template_name' => 'template_name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/print', $printData);
        $response->assertStatus(422);
    }

    public function test_update_print_data_validation_fails()
    {
        $printDataArray = [
            [
                'id' => $this->printData->last()->id,
                'is_template' => true,
            ],
            [
                'id' => $this->printData->last()->id,
                'model_type' => 'test',
            ],
            [
                'id' => $this->printData->last()->id,
                'model_id' => 5,
            ],
            [
                'id' => $this->printData->last()->id,
                'view' => 'not-existing',
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        foreach ($printDataArray as $printData) {
            $response = $this->actingAs($this->user)->put('/api/print', $printData);
            $response->assertStatus(422);
        }
    }

    public function test_delete_print_data()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/print/' . $this->printData->last()->id);
        $response->assertStatus(204);

        $printData = PrintData::query()->whereKey($this->printData->last()->id)->first();
        $this->assertNull($printData);
        $this->assertEmpty($response->getContent());
    }

    public function test_delete_print_data_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/print/' . ++$this->printData->last()->id);
        $response->assertStatus(404);
    }
}

<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Media;
use FluxErp\Models\Permission;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class MediaTest extends BaseSetup
{
    use DatabaseTransactions;

    private File $file;

    private Model $task;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $project = Project::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->task = Task::factory()->create([
            'project_id' => $project->id,
        ]);
        $this->file = UploadedFile::fake()->image('TestFile.png');

        $this->permissions = [
            'download' => Permission::findOrCreate('api.media.private.{id}.get'),
            'upload' => Permission::findOrCreate('api.media.post'),
            'replace' => Permission::findOrCreate('api.media.{id}.post'),
            'update' => Permission::findOrCreate('api.media.put'),
            'delete' => Permission::findOrCreate('api.media.{id}.delete'),
        ];
    }

    public function test_upload_media_to_task()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'collection_name' => 'files',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getMedia('files');
        $this->assertNotEmpty($uploadedMedia);
        $this->assertEquals(1, count($uploadedMedia));
    }

    public function test_upload_media_public_media()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'collection_name' => 'files',
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getMedia('files');
        $this->assertNotEmpty($uploadedMedia);
        $this->assertEquals(1, count($uploadedMedia));
        $this->assertEquals('public', $uploadedMedia[0]->disk);
    }

    public function test_upload_media_validation_fails()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'disk' => uniqid(),
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_model_type_not_found()
    {
        $media = [
            'model_type' => 'ProjectTak',
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_not_allowed_model_type()
    {
        $media = [
            'model_type' => app(Media::class)->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_file_already_exists()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'is_public' => false,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $reUpload = $this->actingAs($this->user)->post('/api/media', $media);
        $reUpload->assertStatus(422);
    }

    public function test_upload_media_task_not_found()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => ++$this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_media_field_missing()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_invalid_file()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => ' ',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_download_media()
    {
        $modelType = $this->task->getMorphClass();
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->task->id,
            'media' => $this->file,
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();

        $queryParams = '?model_type='.$modelType.'&model_id='.$this->task->id;
        $download = $this->get('/api/media/'.$uploadedMedia->file_name.$queryParams);
        $download->assertStatus(200);
    }

    public function test_download_media_validation_fails()
    {
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/media/notExistingFileName');
        $response->assertStatus(422);
    }

    public function test_download_media_model_type_not_found()
    {
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $queryParams = '?model_type=notExistingModelType'.Str::random().'&model_id='.$this->task->id;
        $response = $this->actingAs($this->user)->get('/api/media/filename'.$queryParams);
        $response->assertStatus(422);
    }

    public function test_download_media_thumbnail_not_generated()
    {
        $modelType = $this->task->getMorphClass();
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();
        Media::query()
            ->whereKey($uploadedMedia->id)
            ->update([
                'generated_conversions' => [],
            ]);

        $queryParams = '?model_type='.$modelType.'&model_id='.$this->task->id.'&conversion=thumb';
        $download = $this->get('/api/media/'.$uploadedMedia->file_name.$queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_private_media()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();

        $download = $this->actingAs($this->user)->get('/api/media/private/'.$uploadedMedia->id);
        $download->assertStatus(200);
    }

    public function test_download_media_file_not_found()
    {
        $modelType = $this->task->getMorphClass();
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();
        $queryParams = '?model_type='.$modelType.'&model_id='.$this->task->id;

        $download = $this->get('/api/media/'.Str::random().$uploadedMedia->file_name.$queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_unauthenticated_private_media()
    {
        $modelType = $this->task->getMorphClass();
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->task->id,
            'media' => $this->file,
            'disk' => 'local',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();
        $queryParams = '?model_type='.$modelType.'&model_id='.$this->task->id;

        $download = $this->get('/api/media/'.$uploadedMedia->file_name.$queryParams);
        $download->assertStatus(404);
    }

    public function test_download_private_media_media_not_found()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();

        $download = $this->get('/api/media/private/'.++$uploadedMedia->id);
        $download->assertStatus(404);
    }

    public function test_download_private_media_thumbnail_not_generated()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();
        Media::query()
            ->whereKey($uploadedMedia->id)
            ->update([
                'generated_conversions' => [],
            ]);

        $queryParams = '?conversion=thumb';
        $download = $this->get('/api/media/private/'.$uploadedMedia->id.$queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_with_categories()
    {
        $modelType = $this->task->getMorphClass();
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->task->id,
            'media' => $this->file,
            'disk' => 'public',
            'categories' => [],
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getMedia()[0];

        $queryParams = '?model_type='.$modelType.'&model_id='.$this->task->id;
        $download = $this->get('/api/media/'.$uploadedMedia->file_name.$queryParams);
        $download->assertStatus(200);
    }

    public function test_replace_media()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();
        $file = UploadedFile::fake()->image('NewNotExistingTestFile.png');

        $replace = $this->actingAs($this->user)->post('/api/media/'.$uploadedMedia->id, [
            'media' => $file,
        ]);
        $replace->assertStatus(200);
    }

    public function test_replace_media_validation_fails()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();

        $replace = $this->actingAs($this->user)->post('/api/media/'.$uploadedMedia->id, [
            'media' => true,
        ]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_invalid_file()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'is_public' => true,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getMedia()[0];

        $replace = $this->actingAs($this->user)->post('/api/media/'.$uploadedMedia->id, [
            'media' => false,
        ]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_media_not_found()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();

        $replace = $this->actingAs($this->user)->post('/api/media/'.++$uploadedMedia->id, [
            'media' => $this->file,
        ]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_file_name_already_exists()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();
        $file = UploadedFile::fake()->image('Replicate.png');

        $replicate = Media::query()->whereKey($uploadedMedia->id)->first()->replicate(['uuid']);
        $replicate->name = 'Replicate.png';
        $replicate->save();

        $replace = $this->actingAs($this->user)->post('/api/media/'.$uploadedMedia->id, [
            'media' => $file,
        ]);
        $replace->assertStatus(422);
    }

    public function test_update_media()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'is_public' => false,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getMedia()[0];

        $data = [
            'id' => $uploadedMedia->id,
            'collection' => Str::random(),
        ];

        $update = $this->actingAs($this->user)->put('/api/media/', $data);
        $update->assertStatus(200);
    }

    public function test_update_media_validation_fails()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
            'is_public' => false,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getMedia()[0];

        $data = [
            'id' => ++$uploadedMedia->id,
        ];

        $update = $this->actingAs($this->user)->put('/api/media/', $data);
        $update->assertStatus(422);
    }

    public function test_delete_media()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        config(['logging.default' => 'database']);

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();

        $delete = $this->actingAs($this->user)->delete('/api/media/'.$uploadedMedia->id);
        $delete->assertStatus(204);

        $this->assertFalse(DB::table('media')->where('id', $uploadedMedia->id)->exists());
        $this->assertTrue(
            DB::table('activity_log')
                ->where('subject_type', app(Media::class)->getMorphClass())
                ->where('subject_id', $uploadedMedia->id)
                ->where('event', 'deleted')
                ->exists()
        );
    }

    public function test_delete_media_media_not_found()
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->task->getFirstMedia();

        $delete = $this->actingAs($this->user)->delete('/api/media/'.++$uploadedMedia->id);
        $delete->assertStatus(404);
    }
}

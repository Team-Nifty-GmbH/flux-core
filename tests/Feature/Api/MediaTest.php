<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Media;
use FluxErp\Models\Permission;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use FluxErp\Models\Setting;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Ramsey\Uuid\Uuid;

class MediaTest extends BaseSetup
{
    use DatabaseTransactions;

    private File $file;

    private Model $projectTask;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $projectCategory = Category::factory()->create(['model_type' => ProjectTask::class]);

        $project = Project::factory()->create(['category_id' => $projectCategory->id]);
        $contact = Contact::factory()->create(['client_id' => $this->dbClient->id]);
        $address = Address::factory()->create(['contact_id' => $contact->id, 'client_id' => $contact->client_id]);

        $this->categories = Category::factory()->create(['model_type' => ProjectTask::class]);

        $this->projectTask = ProjectTask::factory()->create([
            'project_id' => $project->id,
            'address_id' => $address->id,
            'user_id' => $this->user->id,
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

    public function test_upload_media_to_project_task()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'collection_name' => 'files',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getMedia('files');
        $this->assertNotEmpty($uploadedMedia);
        $this->assertEquals(1, count($uploadedMedia));
    }

    public function test_upload_media_public_media()
    {
        $modelType = 'projectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'collection_name' => 'files',
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getMedia('files');
        $this->assertNotEmpty($uploadedMedia);
        $this->assertEquals(1, count($uploadedMedia));
        $this->assertEquals('public', $uploadedMedia[0]->disk);
    }

    public function test_upload_media_with_custom_property()
    {
        Setting::query()
            ->where(column: 'key', value: 'project_tasks.folders')
            ->upsert([
                'uuid' => Uuid::uuid4()->toString(),
                'key' => 'media_custom_paths',
                'settings' => json_encode([
                    (object) [
                        'model' => 'FluxErp\\Models\\ProjectTask',
                        'custom_properties' => [
                            'custom_folder',
                        ],
                        'base_path' => 'projects',
                        'conditional_paths' => [
                            (object) [
                                'conditions' => [
                                    (object) [
                                        'is_paid' => false,
                                    ],
                                ],
                                'path' => 'project-tasks',
                            ],
                            (object) [
                                'conditions' => [
                                    (object) [
                                        'is_paid' => true,
                                    ],
                                ],
                                'path' => 'project-paid-tasks',
                            ],
                            (object) [
                                'conditions' => [
                                    (object) [
                                        'custom_folder' => true,
                                    ],
                                ],
                                'path' => 'custom_folder',
                            ],
                        ],
                    ],
                ]),
            ], 'key');

        $setting = Setting::query()
            ->where('key', 'media_custom_paths')
            ->first();

        $folderName = $setting->settings[0]['custom_properties'][0];

        $media = [
            'model_type' => class_basename($setting->settings[0]['model']),
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'collection_name' => 'files',
            'custom_properties' => [
                $folderName => true,
            ],
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getMedia('files');
        $this->assertNotEmpty($uploadedMedia);
        $this->assertEquals(1, count($uploadedMedia));
        $customProperties = $uploadedMedia[0]->custom_properties;
        $this->assertTrue(($customProperties[$folderName] ?? false));
    }

    public function test_upload_media_validation_fails()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
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
        $modelType = 'ProjectTak';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_not_allowed_model_type()
    {
        $modelType = 'Media';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_file_already_exists()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
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

    public function test_upload_media_project_task_not_found()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => ++$this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_media_field_missing()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_invalid_file()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => ' ',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_download_media()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();

        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->projectTask->id;
        $download = $this->get('/api/media/' . $uploadedMedia->file_name . $queryParams);
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

        $queryParams = '?model_type=notExistingModelType' . Str::random() . '&model_id=' . $this->projectTask->id;
        $response = $this->actingAs($this->user)->get('/api/media/filename' . $queryParams);
        $response->assertStatus(404);
    }

    public function test_download_media_thumbnail_not_generated()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();
        Media::query()
            ->whereKey($uploadedMedia->id)
            ->update([
                'generated_conversions' => [],
            ]);

        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->projectTask->id . '&conversion=thumb';
        $download = $this->get('/api/media/' . $uploadedMedia->file_name . $queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_private_media()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();

        $download = $this->actingAs($this->user)->get('/api/media/private/' . $uploadedMedia->id);
        $download->assertStatus(200);
    }

    public function test_download_media_file_not_found()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();
        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->projectTask->id;

        $download = $this->get('/api/media/' . Str::random() . $uploadedMedia->file_name . $queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_unauthenticated_private_media()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'disk' => 'local',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();
        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->projectTask->id;

        $download = $this->get('/api/media/' . $uploadedMedia->file_name . $queryParams);
        $download->assertStatus(404);
    }

    public function test_download_private_media_media_not_found()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();

        $download = $this->get('/api/media/private/' . ++$uploadedMedia->id);
        $download->assertStatus(404);
    }

    public function test_download_private_media_thumbnail_not_generated()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();
        Media::query()
            ->whereKey($uploadedMedia->id)
            ->update([
                'generated_conversions' => [],
            ]);

        $queryParams = '?conversion=thumb';
        $download = $this->get('/api/media/private/' . $uploadedMedia->id . $queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_with_categories()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'disk' => 'public',
            'categories' => [
            ],
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getMedia()[0];

        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->projectTask->id;
        $download = $this->get('/api/media/' . $uploadedMedia->file_name . $queryParams);
        $download->assertStatus(200);
    }

    public function test_replace_media()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();
        $file = UploadedFile::fake()->image('NewNotExistingTestFile.png');

        $replace = $this->actingAs($this->user)->post('/api/media/' . $uploadedMedia->id, [
            'media' => $file,
        ]);
        $replace->assertStatus(200);
    }

    public function test_replace_media_validation_fails()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();

        $replace = $this->actingAs($this->user)->post('/api/media/' . $uploadedMedia->id, [
            'media' => true,
        ]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_invalid_file()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'is_public' => true,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getMedia()[0];

        $replace = $this->actingAs($this->user)->post('/api/media/' . $uploadedMedia->id, [
            'media' => false,
        ]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_media_not_found()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'disk' => 'public',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();

        $replace = $this->actingAs($this->user)->post('/api/media/' . ++$uploadedMedia->id, [
            'media' => $this->file,
        ]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_file_name_already_exists()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();
        $file = UploadedFile::fake()->image('Replicate.png');

        $replicate = Media::query()->whereKey($uploadedMedia->id)->first()->replicate(['uuid']);
        $replicate->name = 'Replicate.png';
        $replicate->save();

        $replace = $this->actingAs($this->user)->post('/api/media/' . $uploadedMedia->id, [
            'media' => $file,
        ]);
        $replace->assertStatus(422);
    }

    public function test_update_media()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'is_public' => false,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getMedia()[0];

        $data = [
            'id' => $uploadedMedia->id,
            'collection' => Str::random(),
        ];

        $update = $this->actingAs($this->user)->put('/api/media/', $data);
        $update->assertStatus(200);
    }

    public function test_update_media_validation_fails()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
            'is_public' => false,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getMedia()[0];

        $data = [
            'id' => ++$uploadedMedia->id,
        ];

        $update = $this->actingAs($this->user)->put('/api/media/', $data);
        $update->assertStatus(422);
    }

    public function test_delete_media()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        config(['logging.default' => 'database']);

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();

        $delete = $this->actingAs($this->user)->delete('/api/media/' . $uploadedMedia->id);
        $delete->assertStatus(204);

        $this->assertFalse(DB::table('media')->where('id', $uploadedMedia->id)->exists());
        $this->assertTrue(
            DB::table('activity_log')
                ->where('subject_type', Media::class)
                ->where('subject_id', $uploadedMedia->id)
                ->where('event', 'deleted')
                ->exists()
        );
    }

    public function test_delete_media_media_not_found()
    {
        $modelType = 'ProjectTask';
        $media = [
            'model_type' => $modelType,
            'model_id' => $this->projectTask->id,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(201);

        $uploadedMedia = $this->projectTask->getFirstMedia();

        $delete = $this->actingAs($this->user)->delete('/api/media/' . ++$uploadedMedia->id);
        $delete->assertStatus(404);
    }
}

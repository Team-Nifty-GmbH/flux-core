<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class MediaTest extends BaseSetup
{
    private File $file;

    private Media $media;

    private array $permissions;

    private Model $task;

    protected function setUp(): void
    {
        parent::setUp();

        $project = Project::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $this->task = Task::factory()->create([
            'project_id' => $project->getKey(),
        ]);
        $this->file = UploadedFile::fake()->image('TestFile.png');

        $this->permissions = [
            'download' => Permission::findOrCreate('api.media.private.{id}.get'),
            'upload' => Permission::findOrCreate('api.media.post'),
            'replace' => Permission::findOrCreate('api.media.{id}.post'),
            'update' => Permission::findOrCreate('api.media.put'),
            'delete' => Permission::findOrCreate('api.media.{id}.delete'),
            'download-multiple' => Permission::findOrCreate('api.media.download-multiple.get'),
        ];

        $this->media = Media::factory()->create([
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->getKey(),
        ]);
    }

    public function test_delete_media(): void
    {
        config(['logging.default' => 'database']);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $delete = $this->actingAs($this->user)->delete('/api/media/' . $this->media->getKey());
        $delete->assertStatus(204);

        $this->assertFalse(DB::table('media')->where('id', $this->media->getKey())->exists());
        $this->assertTrue(
            DB::table('activity_log')
                ->where('subject_type', app(Media::class)->getMorphClass())
                ->where('subject_id', $this->media->getKey())
                ->where('event', 'deleted')
                ->exists()
        );
    }

    public function test_delete_media_media_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $nonExistentId = $this->media->getKey() + 1;
        $delete = $this->actingAs($this->user)->delete('/api/media/' . $nonExistentId);
        $delete->assertStatus(404);
    }

    public function test_download_media(): void
    {
        $media = $this->createMedia([
            'disk' => 'public',
        ]);
        $modelType = $this->task->getMorphClass();
        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->task->getKey();

        $download = $this->get('/api/media/' . $media->file_name . $queryParams);

        $download->assertStatus(200);
    }

    public function test_download_media_file_not_found(): void
    {
        $modelType = $this->task->getMorphClass();
        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->task->getKey();

        $download = $this->get('/api/media/' . Str::random() . $this->media->file_name . $queryParams);

        $download->assertStatus(404);
    }

    public function test_download_media_model_type_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);
        $queryParams = '?model_type=notExistingModelType' . Str::random() . '&model_id=' . $this->task->getKey();

        $response = $this->actingAs($this->user)->get('/api/media/filename' . $queryParams);

        $response->assertStatus(404);
    }

    public function test_download_media_private_media(): void
    {
        $media = $this->createMedia([
            'disk' => 'local',
        ]);

        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $download = $this->actingAs($this->user)->get('/api/media/private/' . $media->getKey());

        $download->assertStatus(200)->assertDownload($media->file_name);
    }

    public function test_download_media_public_route(): void
    {
        $fileName = Str::uuid()->toString() . '.png';

        $this->createMedia([
            'file_name' => $fileName,
            'disk' => 'public',
        ]);
        $queryParams = $fileName . '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

        $download = $this->get('/api/media/' . $queryParams);

        $download->assertStatus(200);
    }

    public function test_download_media_public_route_file_not_found(): void
    {
        $queryParams = '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

        $download = $this->get('/api/media/' . Str::random() . '.png' . $queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_public_route_with_format_parameters(): void
    {
        $fileName = Str::uuid()->toString() . '.png';

        $this->createMedia([
            'disk' => 'public',
            'file_name' => $fileName,
            'name' => $fileName,
        ]);
        $queryParams = $fileName . '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

        $download = $this->get('/api/media/' . $queryParams . '&as=url');
        $download->assertStatus(200);
        $responseData = json_decode($download->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertStringContainsString('/storage/', $responseData['data']);

        $download = $this->get('/api/media/' . $queryParams . '&as=path');

        $download->assertStatus(200);
        $responseData = json_decode($download->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertStringContainsString('storage', $responseData['data']);
    }

    public function test_download_media_public_route_with_model_parameters(): void
    {
        $fileName = Str::uuid()->toString() . '.png';

        $this->createMedia([
            'disk' => 'public',
            'file_name' => $fileName,
            'name' => $fileName,
        ]);
        $queryParams = $fileName . '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

        $download = $this->get('/api/media/' . $queryParams);
        $download->assertStatus(200);
    }

    public function test_download_media_thumbnail_not_generated(): void
    {
        $media = $this->createMedia([
            'disk' => 'public',
            'generated_conversions' => [],
        ]);
        $queryParams = '?model_type=' . $this->task->getMorphClass()
            . '&model_id=' . $this->task->getKey()
            . '&conversion=thumb';

        $download = $this->get('/api/media/' . $media->file_name . $queryParams);

        $download->assertStatus(404);
    }

    public function test_download_media_unauthenticated_private_media(): void
    {
        $media = $this->createMedia([
            'disk' => 'local',
        ]);
        $queryParams = '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

        $download = $this->get('/api/media/' . $media->file_name . $queryParams);

        $download->assertStatus(403);
    }

    public function test_download_media_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $fileName = Str::uuid()->toString();

        $this->createMedia([
            'disk' => 'local',
            'file_name' => $fileName,
            'name' => $fileName,
        ]);
        $queryParams = $fileName
            . '?model_type=' . $this->task->getMorphClass()
            . '&model_id=' . $this->task->getKey()
            . '&as=someInvalidValue';

        $response = $this->actingAs($this->user)->get('/api/media/' . $queryParams);

        $response->assertStatus(422);
    }

    public function test_download_media_with_categories(): void
    {
        $media = $this->createMedia([
            'disk' => 'public',
            'custom_properties' => ['categories' => []],
        ]);
        $queryParams = '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

        $download = $this->get('/api/media/' . $media->file_name . $queryParams);

        $download->assertStatus(200);
    }

    public function test_download_multiple_media(): void
    {
        $mediaIds = [];

        for ($i = 0; $i < 2; $i++) {
            $media = $this->createMedia();
            $mediaIds[] = $media->getKey();
        }

        $this->user->givePermissionTo($this->permissions['download-multiple']);
        Sanctum::actingAs($this->user, ['user']);
        $queryParams = '?ids[]=' . implode('&ids[]=', $mediaIds);

        $response = $this->actingAs($this->user)->get('/api/media/download-multiple' . $queryParams);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/octet-stream');
    }

    public function test_download_multiple_media_mix_public_private(): void
    {
        $publicMedia = $this->createMedia([
            'disk' => 'public',
        ]);

        $privateMedia = $this->createMedia([
            'disk' => 'local',
            'conversions_disk' => 'local',
        ]);

        $this->user->givePermissionTo($this->permissions['download-multiple']);
        Sanctum::actingAs($this->user, ['user']);
        $queryParams = '?ids[]=' . $publicMedia->getKey() . '&ids[]=' . $privateMedia->getKey();

        $response = $this->actingAs($this->user)->get('/api/media/download-multiple' . $queryParams);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/octet-stream');
    }

    public function test_download_multiple_media_private_permissions(): void
    {
        $media = $this->createMedia([
            'disk' => 'local',
            'conversions_disk' => 'local',
        ]);

        $response = $this->get('/api/media/download-multiple?ids[]=' . $media->getKey());
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $this->user->givePermissionTo($this->permissions['download-multiple']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/media/download-multiple?ids[]=' . $media->getKey());

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/octet-stream');
    }

    public function test_download_multiple_media_validation_fails_no_ids(): void
    {
        $this->user->givePermissionTo($this->permissions['download-multiple']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/media/download-multiple');

        $response->assertStatus(422);
    }

    public function test_download_multiple_media_validation_fails_nonexistent_ids(): void
    {
        $this->user->givePermissionTo($this->permissions['download-multiple']);
        Sanctum::actingAs($this->user, ['user']);

        $nonExistentId = Media::query()->max('id') + 999;

        $response = $this->actingAs($this->user)->get('/api/media/download-multiple?ids[]=' . $nonExistentId);

        $response->assertStatus(422);
    }

    public function test_download_multiple_media_with_custom_filename(): void
    {
        $mediaIds = [];

        for ($i = 0; $i < 2; $i++) {
            $media = $this->createMedia();
            $mediaIds[] = $media->getKey();
        }

        $this->user->givePermissionTo($this->permissions['download-multiple']);
        Sanctum::actingAs($this->user, ['user']);

        $customFileName = 'custom-archive';
        $queryParams = '?file_name=' . $customFileName . '&ids[]=' . implode('&ids[]=', $mediaIds);
        $response = $this->actingAs($this->user)->get('/api/media/download-multiple' . $queryParams);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/octet-stream');
        $response->assertHeader('Content-Disposition', 'attachment; filename="' . $customFileName . '.zip"');
    }

    public function test_download_private_media_media_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $nonExistentId = $this->media->getKey() + 1;
        $download = $this->get('/api/media/private/' . $nonExistentId);
        $download->assertStatus(404);
    }

    public function test_download_private_media_thumbnail_not_generated(): void
    {
        $media = $this->createMedia([
            'generated_conversions' => [],
        ]);

        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $queryParams = '?conversion=thumb';
        $download = $this->get('/api/media/private/' . $media->getKey() . $queryParams);
        $download->assertStatus(404);
    }

    public function test_replace_media(): void
    {
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $file = UploadedFile::fake()->image('NewNotExistingTestFile.png');

        $replace = $this->actingAs($this->user)->post('/api/media/' . $this->media->getKey(), [
            'media' => $file,
        ]);
        $replace->assertStatus(200);
    }

    public function test_replace_media_invalid_file(): void
    {
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $replace = $this->actingAs($this->user)->post('/api/media/' . $this->media->getKey(), [
            'media' => false,
        ]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_media_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $nonExistentId = $this->media->getKey() + 1;

        $replace = $this->actingAs($this->user)->post('/api/media/' . $nonExistentId, ['media' => $this->file]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['replace']);
        Sanctum::actingAs($this->user, ['user']);

        $replace = $this->actingAs($this->user)->post('/api/media/' . $this->media->getKey(), [
            'media' => true,
        ]);
        $replace->assertStatus(422);
    }

    public function test_update_media(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $data = [
            'id' => $this->media->getKey(),
            'collection' => Str::random(),
        ];

        $update = $this->actingAs($this->user)->put('/api/media/', $data);
        $update->assertStatus(200);
    }

    public function test_update_media_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $nonExistentId = $this->media->getKey() + 1;

        $data = [
            'id' => $nonExistentId,
        ];

        $update = $this->actingAs($this->user)->put('/api/media/', $data);
        $update->assertStatus(422);
    }

    public function test_upload_media_collection_read_only(): void
    {
        $language = Language::factory()->create();
        $client = Client::factory()->create();
        $orderType = OrderType::factory()->create([
            'client_id' => $client->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);
        $priceList = PriceList::factory()->create();
        $currency = Currency::factory()->create();

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $client, relationship: 'clients')
            ->create();

        $contact = Contact::factory()->create([
            'client_id' => $client->getKey(),
        ]);
        $addresses = Address::factory()->count(2)->create([
            'client_id' => $client->getKey(),
            'contact_id' => $contact->getKey(),
        ]);

        $order = Order::factory()->create([
            'client_id' => $client->getKey(),
            'language_id' => $language->getKey(),
            'order_type_id' => $orderType->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'currency_id' => $currency->getKey(),
            'address_invoice_id' => $addresses->random()->getKey(),
            'address_delivery_id' => $addresses->random()->getKey(),
            'is_locked' => false,
        ]);

        $media = [
            'model_type' => $order->getMorphClass(),
            'model_id' => $order->getKey(),
            'media' => $this->file,
            'collection_name' => 'invoice',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('collection_name');
    }

    public function test_upload_media_invalid_file(): void
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->getKey(),
            'media' => ' ',
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_model_type_not_found(): void
    {
        $media = [
            'model_type' => 'ProjectTak',
            'model_id' => $this->task->getKey(),
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_not_allowed_model_type(): void
    {
        $media = [
            'model_type' => morph_alias(Media::class),
            'model_id' => $this->task->getKey(),
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_public_media(): void
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->getKey(),
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

    public function test_upload_media_task_not_found(): void
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->getKey() + 1,
            'media' => $this->file,
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);
    }

    public function test_upload_media_to_task(): void
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->getKey(),
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

    public function test_upload_media_validation_fails(): void
    {
        $media = [
            'model_type' => $this->task->getMorphClass(),
            'model_id' => $this->task->getKey(),
            'media' => $this->file,
            'disk' => uniqid(),
        ];

        $this->user->givePermissionTo($this->permissions['upload']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/media', $media);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['disk']);
    }

    private function createMedia(array $attributes = []): Media
    {
        /** @var Media $media */
        $media = Media::factory()
            ->create(
                array_merge(
                    [
                        'model_type' => $this->task->getMorphClass(),
                        'model_id' => $this->task->getKey(),
                    ],
                    $attributes
                )
            );

        Storage::fake($media->disk);
        Storage::disk($media->disk)->makeDirectory(dirname($media->getPathRelativeToRoot()));
        Storage::disk($media->disk)->put($media->getPathRelativeToRoot(), $this->file->getContent());

        return $media;
    }
}

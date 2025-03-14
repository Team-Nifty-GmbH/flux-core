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
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class MediaTest extends BaseSetup
{
    private File $file;

    private array $permissions;

    private Model $task;

    protected function setUp(): void
    {
        parent::setUp();

        $project = Project::factory()->create([
            'client_id' => $this->dbClient->getKey(),
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

    public function test_delete_media(): void
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

        $delete = $this->actingAs($this->user)->delete('/api/media/' . $uploadedMedia->id);
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

    public function test_delete_media_media_not_found(): void
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

        $delete = $this->actingAs($this->user)->delete('/api/media/' . ++$uploadedMedia->id);
        $delete->assertStatus(404);
    }

    public function test_download_media(): void
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

        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->task->id;
        $download = $this->get('/api/media/' . $uploadedMedia->file_name . $queryParams);
        $download->assertStatus(200);
    }

    public function test_download_media_file_not_found(): void
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
        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->task->id;

        $download = $this->get('/api/media/' . Str::random() . $uploadedMedia->file_name . $queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_model_type_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $queryParams = '?model_type=notExistingModelType' . Str::random() . '&model_id=' . $this->task->id;
        $response = $this->actingAs($this->user)->get('/api/media/filename' . $queryParams);
        $response->assertStatus(422);
    }

    public function test_download_media_private_media(): void
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

        $download = $this->actingAs($this->user)->get('/api/media/private/' . $uploadedMedia->id);
        $download->assertStatus(200);
    }

    public function test_download_media_thumbnail_not_generated(): void
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

        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->task->id . '&conversion=thumb';
        $download = $this->get('/api/media/' . $uploadedMedia->file_name . $queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_unauthenticated_private_media(): void
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
        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->task->id;

        $download = $this->get('/api/media/' . $uploadedMedia->file_name . $queryParams);
        $download->assertStatus(404);
    }

    public function test_download_media_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['download']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/media/notExistingFileName');
        $response->assertStatus(422);
    }

    public function test_download_media_with_categories(): void
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

        $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->task->id;
        $download = $this->get('/api/media/' . $uploadedMedia->file_name . $queryParams);
        $download->assertStatus(200);
    }

    public function test_download_private_media_media_not_found(): void
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

        $download = $this->get('/api/media/private/' . ++$uploadedMedia->id);
        $download->assertStatus(404);
    }

    public function test_download_private_media_thumbnail_not_generated(): void
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
        $download = $this->get('/api/media/private/' . $uploadedMedia->id . $queryParams);
        $download->assertStatus(404);
    }

    public function test_replace_media(): void
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

        $replace = $this->actingAs($this->user)->post('/api/media/' . $uploadedMedia->id, [
            'media' => $file,
        ]);
        $replace->assertStatus(200);
    }

    public function test_replace_media_invalid_file(): void
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

        $replace = $this->actingAs($this->user)->post('/api/media/' . $uploadedMedia->id, [
            'media' => false,
        ]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_media_not_found(): void
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

        $replace = $this->actingAs($this->user)->post('/api/media/' . ++$uploadedMedia->id, [
            'media' => $this->file,
        ]);
        $replace->assertStatus(422);
    }

    public function test_replace_media_validation_fails(): void
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

        $replace = $this->actingAs($this->user)->post('/api/media/' . $uploadedMedia->id, [
            'media' => true,
        ]);
        $replace->assertStatus(422);
    }

    public function test_update_media(): void
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

    public function test_update_media_validation_fails(): void
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

    public function test_upload_media_collection_read_only(): void
    {
        $language = Language::factory()->create();
        $client = Client::factory()->create();
        $orderType = OrderType::factory()->create([
            'client_id' => $client->id,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);
        $priceList = PriceList::factory()->create();
        $currency = Currency::factory()->create();

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $client, relationship: 'clients')
            ->create();

        $contact = Contact::factory()->create([
            'client_id' => $client->id,
        ]);
        $addresses = Address::factory()->count(2)->create([
            'client_id' => $client->id,
            'contact_id' => $contact->id,
        ]);

        $order = Order::factory()->create([
            'client_id' => $client->id,
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $addresses->random()->id,
            'address_delivery_id' => $addresses->random()->id,
            'is_locked' => false,
        ]);

        $media = [
            'model_type' => $order->getMorphClass(),
            'model_id' => $order->id,
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
            'model_id' => $this->task->id,
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
            'model_id' => $this->task->id,
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
            'model_id' => $this->task->id,
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

    public function test_upload_media_task_not_found(): void
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

    public function test_upload_media_to_task(): void
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

    public function test_upload_media_validation_fails(): void
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

        $response->assertJsonValidationErrors(['disk']);
    }
}

<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
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
});

test('delete media', function (): void {
    config(['logging.default' => 'database']);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $delete = $this->actingAs($this->user)->delete('/api/media/' . $this->media->getKey());
    $delete->assertStatus(204);

    expect(DB::table('media')->where('id', $this->media->getKey())->exists())->toBeFalse();
    expect(DB::table('activity_log')
        ->where('subject_type', app(Media::class)->getMorphClass())
        ->where('subject_id', $this->media->getKey())
        ->where('event', 'deleted')
        ->exists())->toBeTrue();
});

test('delete media media not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $nonExistentId = $this->media->getKey() + 1;
    $delete = $this->actingAs($this->user)->delete('/api/media/' . $nonExistentId);
    $delete->assertStatus(404);
});

test('download media', function (): void {
    $media = createMedia([
        'disk' => 'public',
    ]);
    $modelType = $this->task->getMorphClass();
    $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->task->getKey();

    $download = $this->get('/api/media/' . $media->file_name . $queryParams);

    $download->assertStatus(200);
});

test('download media file not found', function (): void {
    $modelType = $this->task->getMorphClass();
    $queryParams = '?model_type=' . $modelType . '&model_id=' . $this->task->getKey();

    $download = $this->get('/api/media/' . Str::random() . $this->media->file_name . $queryParams);

    $download->assertStatus(404);
});

test('download media model type not found', function (): void {
    $this->user->givePermissionTo($this->permissions['download']);
    Sanctum::actingAs($this->user, ['user']);
    $queryParams = '?model_type=notExistingModelType' . Str::random() . '&model_id=' . $this->task->getKey();

    $response = $this->actingAs($this->user)->get('/api/media/filename' . $queryParams);

    $response->assertStatus(404);
});

test('download media private media', function (): void {
    $media = createMedia([
        'disk' => 'local',
    ]);

    $this->user->givePermissionTo($this->permissions['download']);
    Sanctum::actingAs($this->user, ['user']);

    $download = $this->actingAs($this->user)->get('/api/media/private/' . $media->getKey());

    $download->assertStatus(200);
    $download->assertHeader('Content-Type', 'image/png');
    $download->assertDownload($media->file_name);
});

test('download media public route', function (): void {
    $fileName = Str::uuid()->toString() . '.png';

    createMedia([
        'file_name' => $fileName,
        'disk' => 'public',
    ]);
    $queryParams = $fileName . '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

    $download = $this->get('/api/media/' . $queryParams);

    $download->assertStatus(200);
    $download->assertHeader('Content-Type', 'image/png');
    $download->assertDownload($fileName);
});

test('download media public route file not found', function (): void {
    $queryParams = '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

    $download = $this->get('/api/media/' . Str::random() . '.png' . $queryParams);
    $download->assertStatus(404);
});

test('download media public route with format parameters', function (): void {
    $fileName = Str::uuid()->toString() . '.png';

    createMedia([
        'disk' => 'public',
        'file_name' => $fileName,
        'name' => $fileName,
    ]);
    $queryParams = $fileName . '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

    $download = $this->get('/api/media/' . $queryParams . '&as=url');
    $download->assertStatus(200);
    $responseData = json_decode($download->getContent(), true);
    expect($responseData)->toHaveKey('data');
    $this->assertStringContainsString('/storage/', $responseData['data']);

    $download = $this->get('/api/media/' . $queryParams . '&as=path');

    $download->assertStatus(200);
    $responseData = json_decode($download->getContent(), true);
    expect($responseData)->toHaveKey('data');
    $this->assertStringContainsString('storage', $responseData['data']);
});

test('download media public route with model parameters', function (): void {
    $fileName = Str::uuid()->toString() . '.png';

    createMedia([
        'disk' => 'public',
        'file_name' => $fileName,
        'name' => $fileName,
    ]);
    $queryParams = $fileName . '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

    $download = $this->get('/api/media/' . $queryParams);
    $download->assertStatus(200);
    $download->assertHeader('Content-Type', 'image/png');
    $download->assertDownload($fileName);
});

test('download media thumbnail not generated', function (): void {
    $media = createMedia([
        'disk' => 'public',
        'generated_conversions' => [],
    ]);
    $queryParams = '?model_type=' . $this->task->getMorphClass()
        . '&model_id=' . $this->task->getKey()
        . '&conversion=thumb';

    $download = $this->get('/api/media/' . $media->file_name . $queryParams);

    $download->assertStatus(404);
});

test('download media unauthenticated private media', function (): void {
    $media = createMedia([
        'disk' => 'local',
    ]);
    $queryParams = '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

    $download = $this->get('/api/media/' . $media->file_name . $queryParams);

    $download->assertStatus(403);
});

test('download media validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['download']);
    Sanctum::actingAs($this->user, ['user']);

    $fileName = Str::uuid()->toString();

    createMedia([
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
});

test('download media with categories', function (): void {
    $media = createMedia([
        'disk' => 'public',
        'custom_properties' => ['categories' => []],
    ]);
    $queryParams = '?model_type=' . $this->task->getMorphClass() . '&model_id=' . $this->task->getKey();

    $download = $this->get('/api/media/' . $media->file_name . $queryParams);

    $download->assertStatus(200);
    $download->assertHeader('Content-Type', 'image/png');
    $download->assertDownload($media->file_name);
});

test('download multiple media', function (): void {
    $mediaIds = [];

    for ($i = 0; $i < 2; $i++) {
        $media = createMedia();
        $mediaIds[] = $media->getKey();
    }

    $this->user->givePermissionTo($this->permissions['download-multiple']);
    Sanctum::actingAs($this->user, ['user']);
    $queryParams = '?ids[]=' . implode('&ids[]=', $mediaIds);

    $response = $this->actingAs($this->user)->get('/api/media/download-multiple' . $queryParams);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/octet-stream');
});

test('download multiple media mix public private', function (): void {
    $publicMedia = createMedia([
        'disk' => 'public',
    ]);

    $privateMedia = createMedia([
        'disk' => 'local',
        'conversions_disk' => 'local',
    ]);

    $this->user->givePermissionTo($this->permissions['download-multiple']);
    Sanctum::actingAs($this->user, ['user']);
    $queryParams = '?ids[]=' . $publicMedia->getKey() . '&ids[]=' . $privateMedia->getKey();

    $response = $this->actingAs($this->user)->get('/api/media/download-multiple' . $queryParams);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/octet-stream');
});

test('download multiple media private permissions', function (): void {
    $media = createMedia([
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
});

test('download multiple media validation fails no ids', function (): void {
    $this->user->givePermissionTo($this->permissions['download-multiple']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/media/download-multiple');

    $response->assertStatus(422);
});

test('download multiple media validation fails nonexistent ids', function (): void {
    $this->user->givePermissionTo($this->permissions['download-multiple']);
    Sanctum::actingAs($this->user, ['user']);

    $nonExistentId = Media::query()->max('id') + 999;

    $response = $this->actingAs($this->user)->get('/api/media/download-multiple?ids[]=' . $nonExistentId);

    $response->assertStatus(422);
});

test('download multiple media with custom filename', function (): void {
    $mediaIds = [];

    for ($i = 0; $i < 2; $i++) {
        $media = createMedia();
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
});

test('download private media media not found', function (): void {
    $this->user->givePermissionTo($this->permissions['download']);
    Sanctum::actingAs($this->user, ['user']);

    $nonExistentId = $this->media->getKey() + 1;
    $download = $this->get('/api/media/private/' . $nonExistentId);
    $download->assertStatus(404);
});

test('download private media thumbnail not generated', function (): void {
    $media = createMedia([
        'generated_conversions' => [],
    ]);

    $this->user->givePermissionTo($this->permissions['download']);
    Sanctum::actingAs($this->user, ['user']);

    $queryParams = '?conversion=thumb';
    $download = $this->get('/api/media/private/' . $media->getKey() . $queryParams);
    $download->assertStatus(404);
});

test('replace media', function (): void {
    $this->user->givePermissionTo($this->permissions['replace']);
    Sanctum::actingAs($this->user, ['user']);

    $file = UploadedFile::fake()->image('NewNotExistingTestFile.png');

    $replace = $this->actingAs($this->user)->post('/api/media/' . $this->media->getKey(), [
        'media' => $file,
    ]);
    $replace->assertStatus(200);
});

test('replace media invalid file', function (): void {
    $this->user->givePermissionTo($this->permissions['replace']);
    Sanctum::actingAs($this->user, ['user']);

    $replace = $this->actingAs($this->user)->post('/api/media/' . $this->media->getKey(), [
        'media' => false,
    ]);
    $replace->assertStatus(422);
});

test('replace media media not found', function (): void {
    $this->user->givePermissionTo($this->permissions['replace']);
    Sanctum::actingAs($this->user, ['user']);

    $nonExistentId = $this->media->getKey() + 1;

    $replace = $this->actingAs($this->user)->post('/api/media/' . $nonExistentId, ['media' => $this->file]);
    $replace->assertStatus(422);
});

test('replace media validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['replace']);
    Sanctum::actingAs($this->user, ['user']);

    $replace = $this->actingAs($this->user)->post('/api/media/' . $this->media->getKey(), [
        'media' => true,
    ]);
    $replace->assertStatus(422);
});

test('update media', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $data = [
        'id' => $this->media->getKey(),
        'collection' => Str::random(),
    ];

    $update = $this->actingAs($this->user)->put('/api/media/', $data);
    $update->assertStatus(200);
});

test('update media validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $nonExistentId = $this->media->getKey() + 1;

    $data = [
        'id' => $nonExistentId,
    ];

    $update = $this->actingAs($this->user)->put('/api/media/', $data);
    $update->assertStatus(422);
});

test('upload media collection read only', function (): void {
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
});

test('upload media invalid file', function (): void {
    $media = [
        'model_type' => $this->task->getMorphClass(),
        'model_id' => $this->task->getKey(),
        'media' => ' ',
    ];

    $this->user->givePermissionTo($this->permissions['upload']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/media', $media);
    $response->assertStatus(422);
});

test('upload media model type not found', function (): void {
    $media = [
        'model_type' => 'ProjectTak',
        'model_id' => $this->task->getKey(),
        'media' => $this->file,
    ];

    $this->user->givePermissionTo($this->permissions['upload']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/media', $media);
    $response->assertStatus(422);
});

test('upload media not allowed model type', function (): void {
    $media = [
        'model_type' => morph_alias(Media::class),
        'model_id' => $this->task->getKey(),
        'media' => $this->file,
    ];

    $this->user->givePermissionTo($this->permissions['upload']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/media', $media);
    $response->assertStatus(422);
});

test('upload media public media', function (): void {
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
    expect($uploadedMedia)->not->toBeEmpty();
    expect(count($uploadedMedia))->toEqual(1);
    expect($uploadedMedia[0]->disk)->toEqual('public');
});

test('upload media task not found', function (): void {
    $media = [
        'model_type' => $this->task->getMorphClass(),
        'model_id' => $this->task->getKey() + 1,
        'media' => $this->file,
    ];

    $this->user->givePermissionTo($this->permissions['upload']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/media', $media);
    $response->assertStatus(422);
});

test('upload media to task', function (): void {
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
    expect($uploadedMedia)->not->toBeEmpty();
    expect(count($uploadedMedia))->toEqual(1);
});

test('upload media validation fails', function (): void {
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
});

function createMedia(array $attributes = []): Media
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

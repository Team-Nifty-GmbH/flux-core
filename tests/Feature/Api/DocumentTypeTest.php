<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class DocumentTypeTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $documentTypes;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentTypes = DocumentType::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.document-types.{id}.get'),
            'index' => Permission::findOrCreate('api.document-types.get'),
            'create' => Permission::findOrCreate('api.document-types.post'),
            'update' => Permission::findOrCreate('api.document-types.put'),
            'delete' => Permission::findOrCreate('api.document-types.{id}.delete'),
        ];
    }

    public function test_get_document_type()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/document-types/' . $this->documentTypes[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonDocumentType = $json->data;

        // Check if controller returns the test document type.
        $this->assertNotEmpty($jsonDocumentType);
        $this->assertEquals($this->documentTypes[0]->id, $jsonDocumentType->id);
        $this->assertEquals($this->documentTypes[0]->client_id, $jsonDocumentType->client_id);
        $this->assertEquals($this->documentTypes[0]->name, $jsonDocumentType->name);
        $this->assertEquals($this->documentTypes[0]->description, $jsonDocumentType->description);
        $this->assertEquals($this->documentTypes[0]->additional_header, $jsonDocumentType->additional_header);
        $this->assertEquals($this->documentTypes[0]->additional_footer, $jsonDocumentType->additional_footer);
        $this->assertEquals($this->documentTypes[0]->is_active, $jsonDocumentType->is_active);
        $this->assertEquals(Carbon::parse($this->documentTypes[0]->created_at),
            Carbon::parse($jsonDocumentType->created_at));
        $this->assertEquals(Carbon::parse($this->documentTypes[0]->updated_at),
            Carbon::parse($jsonDocumentType->updated_at));
    }

    public function test_get_document_type_document_type_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/document-types/' . ++$this->documentTypes[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_document_types()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/document-types');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonDocumentTypes = collect($json->data->data);

        // Check the amount of test document types.
        $this->assertGreaterThanOrEqual(2, count($jsonDocumentTypes));

        // Check if controller returns the test document types.
        foreach ($this->documentTypes as $documentType) {
            $jsonDocumentTypes->contains(function ($jsonDocumentType) use ($documentType) {
                return $jsonDocumentType->id === $documentType->id &&
                    $jsonDocumentType->client_id === $documentType->client_id &&
                    $jsonDocumentType->name === $documentType->name &&
                    $jsonDocumentType->description === $documentType->description &&
                    $jsonDocumentType->additional_header === $documentType->additional_header &&
                    $jsonDocumentType->additional_footer === $documentType->additional_footer &&
                    $jsonDocumentType->is_active === $documentType->is_active &&
                    Carbon::parse($jsonDocumentType->created_at) === Carbon::parse($documentType->created_at) &&
                    Carbon::parse($jsonDocumentType->updated_at) === Carbon::parse($documentType->updated_at);
            });
        }
    }

    public function test_create_document_type()
    {
        $documentType = [
            'client_id' => $this->documentTypes[0]->client_id,
            'name' => 'Document Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/document-types', $documentType);
        $response->assertStatus(201);

        $responseDocumentType = json_decode($response->getContent())->data;
        $dbDocumentType = (object) DocumentType::query()
            ->whereKey($responseDocumentType->id)
            ->first()
            ->append(['created_by', 'updated_by'])
            ->toArray();

        $this->assertNotEmpty($dbDocumentType);
        $this->assertEquals($documentType['client_id'], $dbDocumentType->client_id);
        $this->assertEquals($documentType['name'], $dbDocumentType->name);
        $this->assertNull($dbDocumentType->description);
        $this->assertNull($dbDocumentType->additional_header);
        $this->assertNull($dbDocumentType->additional_footer);
        $this->assertTrue($dbDocumentType->is_active);
        $this->assertEquals($this->user->id, $dbDocumentType->created_by['id']);
        $this->assertEquals($this->user->id, $dbDocumentType->updated_by['id']);
    }

    public function test_create_document_type_maximum()
    {
        $documentType = [
            'client_id' => $this->documentTypes[0]->client_id,
            'name' => 'Document Type Name',
            'description' => 'New description text for further information',
            'additional_header' => 'Header text',
            'additional_footer' => 'Footer text',
            'is_active' => false,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/document-types', $documentType);
        $response->assertStatus(201);

        $responseDocumentType = json_decode($response->getContent())->data;
        $dbDocumentType = DocumentType::query()
            ->whereKey($responseDocumentType->id)
            ->first();

        $this->assertNotEmpty($dbDocumentType);
        $this->assertEquals($documentType['client_id'], $dbDocumentType->client_id);
        $this->assertEquals($documentType['name'], $dbDocumentType->name);
        $this->assertEquals($documentType['description'], $dbDocumentType->description);
        $this->assertEquals($documentType['additional_header'], $dbDocumentType->additional_header);
        $this->assertEquals($documentType['additional_footer'], $dbDocumentType->additional_footer);
        $this->assertEquals($documentType['is_active'], $dbDocumentType->is_active);
        $this->assertEquals($this->user->id, $dbDocumentType->created_by->id);
        $this->assertEquals($this->user->id, $dbDocumentType->updated_by->id);
    }

    public function test_create_document_type_validation_fails()
    {
        $documentType = [
            'client_id' => 'client_id',
            'name' => 'Document Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/document-types', $documentType);
        $response->assertStatus(422);
    }

    public function test_update_document_type()
    {
        $documentType = [
            'id' => $this->documentTypes[0]->id,
            'client_id' => $this->documentTypes[0]->client_id,
            'name' => 'Document Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/document-types', $documentType);
        $response->assertStatus(200);

        $responseDocumentType = json_decode($response->getContent())->data;
        $dbDocumentType = DocumentType::query()
            ->whereKey($responseDocumentType->id)
            ->first();

        $this->assertNotEmpty($dbDocumentType);
        $this->assertEquals($documentType['id'], $dbDocumentType->id);
        $this->assertEquals($documentType['client_id'], $dbDocumentType->client_id);
        $this->assertEquals($documentType['name'], $dbDocumentType->name);
        $this->assertEquals($this->documentTypes[0]->description, $dbDocumentType->description);
        $this->assertEquals($this->documentTypes[0]->additional_header, $dbDocumentType->additional_header);
        $this->assertEquals($this->documentTypes[0]->additional_footer, $dbDocumentType->additional_footer);
        $this->assertEquals($this->user->id, $dbDocumentType->updated_by->id);
    }

    public function test_update_document_type_maximum()
    {
        $documentType = [
            'id' => $this->documentTypes[0]->id,
            'client_id' => $this->documentTypes[0]->client_id,
            'name' => 'Document Type Name',
            'description' => 'New description text for further information',
            'additional_header' => 'Header text',
            'additional_footer' => 'Footer text',
            'is_active' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/document-types', $documentType);
        $response->assertStatus(200);

        $responseDocumentType = json_decode($response->getContent())->data;
        $dbDocumentType = DocumentType::query()
            ->whereKey($responseDocumentType->id)
            ->first();

        $this->assertNotEmpty($dbDocumentType);
        $this->assertEquals($documentType['id'], $dbDocumentType->id);
        $this->assertEquals($documentType['client_id'], $dbDocumentType->client_id);
        $this->assertEquals($documentType['name'], $dbDocumentType->name);
        $this->assertEquals($documentType['description'], $dbDocumentType->description);
        $this->assertEquals($documentType['additional_header'], $dbDocumentType->additional_header);
        $this->assertEquals($documentType['additional_footer'], $dbDocumentType->additional_footer);
        $this->assertEquals($documentType['is_active'], $dbDocumentType->is_active);
        $this->assertEquals($this->user->id, $dbDocumentType->updated_by->id);
    }

    public function test_update_document_type_validation_fails()
    {
        $documentType = [
            'id' => $this->documentTypes[0]->id,
            'client_id' => 'client_id',
            'name' => 'Document Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/document-types', $documentType);
        $response->assertStatus(422);
    }

    public function test_delete_document_type()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/document-types/' . $this->documentTypes[1]->id);
        $response->assertStatus(204);

        $documentType = $this->documentTypes[1]->fresh();
        $this->assertNotNull($documentType->deleted_at);
        $this->assertEquals($this->user->id, $documentType->deleted_by->id);
    }

    public function test_delete_document_type_document_type_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/document-types/' . ++$this->documentTypes[1]->id);
        $response->assertStatus(404);
    }
}
